<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp\Core;

use Workerman\Worker;
use Workerman\Connection\TcpConnection;
use PFPMcp\Tools\ToolManager;
use PFPMcp\Resources\ResourceManager;
use PFPMcp\Prompts\PromptManager;
use Psr\Log\LoggerInterface;

/**
 * 基于 Workerman 的 MCP 服务器核心
 * 
 * @package PFPMcp\Core
 */
class WorkermanMcpServer
{
    /**
     * Worker 实例
     */
    private ?Worker $worker = null;
    
    /**
     * 工具管理器
     */
    private ToolManager $toolManager;
    
    /**
     * 资源管理器
     */
    private ResourceManager $resourceManager;
    
    /**
     * 提示管理器
     */
    private PromptManager $promptManager;
    
    /**
     * 日志记录器
     */
    private LoggerInterface $logger;
    
    /**
     * 服务器配置
     */
    private array $config;
    
    /**
     * 构造函数
     * 
     * @param ToolManager $toolManager 工具管理器
     * @param ResourceManager $resourceManager 资源管理器
     * @param PromptManager $promptManager 提示管理器
     * @param LoggerInterface $logger 日志记录器
     * @param array $config 服务器配置
     */
    public function __construct(
        ToolManager $toolManager,
        ResourceManager $resourceManager,
        PromptManager $promptManager,
        LoggerInterface $logger,
        array $config = []
    ) {
        $this->toolManager = $toolManager;
        $this->resourceManager = $resourceManager;
        $this->promptManager = $promptManager;
        $this->logger = $logger;
        $this->config = array_merge([
            'host' => '0.0.0.0',
            'port' => 8080,
            'transport' => 'http'
        ], $config);
    }
    
    /**
     * 启动服务器
     */
    public function start(): void
    {
        $this->logger->info('Starting Workerman MCP Server', $this->config);
        
        $address = "{$this->config['transport']}://{$this->config['host']}:{$this->config['port']}";
        $this->worker = new Worker($address);
        
        $this->worker->onMessage = function (TcpConnection $connection, $data) {
            $this->handleMessage($connection, $data);
        };
        
        $this->worker->onConnect = function (TcpConnection $connection) {
            $this->logger->info('Client connected', [
                'id' => $connection->id,
                'remote_address' => $connection->getRemoteAddress()
            ]);
        };
        
        $this->worker->onClose = function (TcpConnection $connection) {
            $this->logger->info('Client disconnected', [
                'id' => $connection->id
            ]);
        };
        
        $this->worker->onError = function (TcpConnection $connection, $code, $msg) {
            $this->logger->error('Connection error', [
                'id' => $connection->id,
                'code' => $code,
                'message' => $msg
            ]);
        };
        
        Worker::runAll();
    }
    
    /**
     * 停止服务器
     */
    public function stop(): void
    {
        if ($this->worker !== null) {
            $this->worker->stop();
            $this->logger->info('Workerman MCP Server stopped');
        }
    }
    
    /**
     * 处理消息
     * 
     * @param TcpConnection $connection 连接对象
     * @param mixed $data 消息数据
     */
    private function handleMessage(TcpConnection $connection, $data): void
    {
        try {
            $this->logger->debug('Received message', ['data' => $data]);
            
            // 解析消息
            $message = McpProtocol::parseMessage($data);
            if ($message === null) {
                $response = McpProtocol::createError(-32700, 'Parse error', uniqid());
                $connection->send(json_encode($response));
                return;
            }
            
            // 验证消息
            if (!McpProtocol::validateMessage($message)) {
                $response = McpProtocol::createError(-32600, 'Invalid Request', $message['id'] ?? uniqid());
                $connection->send(json_encode($response));
                return;
            }
            
            // 处理请求
            $response = $this->processRequest($message);
            $connection->send(json_encode($response));
            
        } catch (\Throwable $e) {
            $this->logger->error('Error handling message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $response = McpProtocol::createError(
                -32603,
                'Internal error',
                $message['id'] ?? uniqid(),
                $e->getMessage()
            );
            $connection->send(json_encode($response));
        }
    }
    
    /**
     * 处理请求
     * 
     * @param array $message 请求消息
     * @return array 响应消息
     */
    private function processRequest(array $message): array
    {
        $method = $message['method'] ?? '';
        $params = $message['params'] ?? [];
        $id = $message['id'] ?? uniqid();
        
        return match($method) {
            'tools/list' => $this->handleToolsList($id),
            'tools/call' => $this->handleToolCall($params, $id),
            'resources/list' => $this->handleResourcesList($id),
            'resources/read' => $this->handleResourceRead($params, $id),
            'prompts/list' => $this->handlePromptsList($id),
            'initialize' => $this->handleInitialize($params, $id),
            default => McpProtocol::createError(-32601, 'Method not found', $id)
        };
    }
    
    /**
     * 处理工具列表请求
     * 
     * @param string $id 请求ID
     * @return array 响应
     */
    private function handleToolsList(string $id): array
    {
        $tools = $this->toolManager->listTools();
        return McpProtocol::createResponse($tools, $id);
    }
    
    /**
     * 处理工具调用请求
     * 
     * @param array $params 参数
     * @param string $id 请求ID
     * @return array 响应
     */
    private function handleToolCall(array $params, string $id): array
    {
        $name = $params['name'] ?? '';
        $arguments = $params['arguments'] ?? [];
        
        if (empty($name)) {
            return McpProtocol::createError(-32602, 'Missing tool name', $id);
        }
        
        try {
            $result = $this->toolManager->callTool($name, $arguments);
            return McpProtocol::createResponse($result, $id);
        } catch (\Throwable $e) {
            return McpProtocol::createError(-32603, $e->getMessage(), $id);
        }
    }
    
    /**
     * 处理资源列表请求
     * 
     * @param string $id 请求ID
     * @return array 响应
     */
    private function handleResourcesList(string $id): array
    {
        $resources = $this->resourceManager->listResources();
        return McpProtocol::createResponse($resources, $id);
    }
    
    /**
     * 处理资源读取请求
     * 
     * @param array $params 参数
     * @param string $id 请求ID
     * @return array 响应
     */
    private function handleResourceRead(array $params, string $id): array
    {
        $uri = $params['uri'] ?? '';
        
        if (empty($uri)) {
            return McpProtocol::createError(-32602, 'Missing resource URI', $id);
        }
        
        try {
            $result = $this->resourceManager->readResource($uri);
            return McpProtocol::createResponse($result, $id);
        } catch (\Throwable $e) {
            return McpProtocol::createError(-32603, $e->getMessage(), $id);
        }
    }
    
    /**
     * 处理提示列表请求
     * 
     * @param string $id 请求ID
     * @return array 响应
     */
    private function handlePromptsList(string $id): array
    {
        $prompts = $this->promptManager->listPrompts();
        return McpProtocol::createResponse($prompts, $id);
    }
    
    /**
     * 处理初始化请求
     * 
     * @param array $params 参数
     * @param string $id 请求ID
     * @return array 响应
     */
    private function handleInitialize(array $params, string $id): array
    {
        $result = [
            'protocolVersion' => '2024-11-05',
            'capabilities' => [
                'tools' => ['listChanged' => true],
                'resources' => ['listChanged' => true],
                'prompts' => ['listChanged' => true]
            ],
            'serverInfo' => [
                'name' => 'PFPMcp',
                'version' => '1.0.0'
            ]
        ];
        
        return McpProtocol::createResponse($result, $id);
    }
}
