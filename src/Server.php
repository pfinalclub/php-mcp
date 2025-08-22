<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp;

use PFPMcp\Config\ServerConfig;
use PFPMcp\Connection\ConnectionManager;
use PFPMcp\EventHandler\EventHandler;
use PFPMcp\Protocol\ProtocolManager;
use PFPMcp\Session\SessionManager;
use PFPMcp\Tools\ToolManager;
use PFPMcp\Resources\ResourceManager;
use PFPMcp\Prompts\PromptManager;
use PFPMcp\Transport\TransportFactory;
use PFPMcp\Transport\TransportInterface;
use PFPMcp\Logging\Logger;
use PFPMcp\Exceptions\ServerException;
use Workerman\Worker;
use Workerman\Connection\TcpConnection;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * MCP 服务器主类
 * 
 * 负责管理 MCP 服务器的生命周期，包括启动、停止、配置管理等功能
 * 
 * @package PFPMcp
 */
class Server
{
    /**
     * 服务器配置
     */
    private ServerConfig $config;
    
    /**
     * 日志记录器
     */
    private LoggerInterface $logger;
    
    /**
     * 传输协议实例
     */
    private TransportInterface $transport;
    
    /**
     * 连接管理器
     */
    private ConnectionManager $connectionManager;
    
    /**
     * 事件处理器
     */
    private EventHandler $eventHandler;
    
    /**
     * 协议管理器
     */
    private ProtocolManager $protocolManager;
    
    /**
     * 会话管理器
     */
    private SessionManager $sessionManager;
    
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
     * 服务器状态
     */
    private bool $isRunning = false;
    
    /**
     * 构造函数
     * 
     * @param ServerConfig|null $config 服务器配置
     * @param LoggerInterface|null $logger 日志记录器
     */
    public function __construct(?ServerConfig $config = null, ?LoggerInterface $logger = null)
    {
        $this->config = $config ?? new ServerConfig();
        $this->logger = $logger ?? new NullLogger();
        
        $this->initialize();
    }
    
    /**
     * 初始化服务器组件
     */
    private function initialize(): void
    {
        try {
            // 创建传输协议实例
            $this->transport = TransportFactory::create($this->config->getTransport());
            
            // 初始化各个管理器
            $this->connectionManager = new ConnectionManager($this->config, $this->logger);
            $this->eventHandler = new EventHandler($this->logger);
            $this->protocolManager = new ProtocolManager($this->logger);
            $this->sessionManager = new SessionManager($this->config, $this->logger);
            $this->toolManager = new ToolManager($this->logger);
            $this->resourceManager = new ResourceManager($this->logger);
            $this->promptManager = new PromptManager($this->logger);
            
            // 设置事件处理器
            $this->setupEventHandlers();
            
            $this->logger->info('MCP Server initialized successfully', [
                'transport' => $this->config->getTransport(),
                'host' => $this->config->getHost(),
                'port' => $this->config->getPort()
            ]);
            
        } catch (\Throwable $e) {
            $this->logger->error('Failed to initialize MCP Server', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new ServerException('Failed to initialize server: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * 设置事件处理器
     */
    private function setupEventHandlers(): void
    {
        // 连接事件
        $this->eventHandler->on('connect', function (TcpConnection $connection) {
            $this->handleConnect($connection);
        });
        
        // 消息事件
        $this->eventHandler->on('message', function (TcpConnection $connection, $data) {
            $this->handleMessage($connection, $data);
        });
        
        // 关闭事件
        $this->eventHandler->on('close', function (TcpConnection $connection) {
            $this->handleClose($connection);
        });
        
        // 错误事件
        $this->eventHandler->on('error', function (TcpConnection $connection, $error) {
            $this->handleError($connection, $error);
        });
    }
    
    /**
     * 启动服务器
     * 
     * @throws ServerException 当服务器启动失败时
     */
    public function start(): void
    {
        if ($this->isRunning) {
            $this->logger->warning('Server is already running');
            return;
        }
        
        try {
            $this->logger->info('Starting MCP Server...', [
                'transport' => $this->config->getTransport(),
                'host' => $this->config->getHost(),
                'port' => $this->config->getPort()
            ]);
            
            // 启动传输协议
            $this->transport->start();
            
            // 启动 Workerman
            Worker::runAll();
            
            $this->isRunning = true;
            
            $this->logger->info('MCP Server started successfully');
            
        } catch (\Throwable $e) {
            $this->logger->error('Failed to start MCP Server', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new ServerException('Failed to start server: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * 停止服务器
     */
    public function stop(): void
    {
        if (!$this->isRunning) {
            $this->logger->warning('Server is not running');
            return;
        }
        
        try {
            $this->logger->info('Stopping MCP Server...');
            
            // 停止传输协议
            $this->transport->stop();
            
            // 停止 Workerman
            Worker::stopAll();
            
            $this->isRunning = false;
            
            $this->logger->info('MCP Server stopped successfully');
            
        } catch (\Throwable $e) {
            $this->logger->error('Failed to stop MCP Server', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * 重启服务器
     */
    public function restart(): void
    {
        $this->logger->info('Restarting MCP Server...');
        $this->stop();
        $this->start();
    }
    
    /**
     * 处理连接事件
     * 
     * @param TcpConnection $connection 连接对象
     */
    private function handleConnect(TcpConnection $connection): void
    {
        try {
            $this->logger->info('New connection established', [
                'connection_id' => $connection->id,
                'remote_address' => $connection->getRemoteAddress()
            ]);
            
            // 创建连接管理器
            $this->connectionManager->addConnection($connection);
            
            // 发送初始化消息
            $this->sendInitializationMessage($connection);
            
        } catch (\Throwable $e) {
            $this->logger->error('Error handling connection', [
                'connection_id' => $connection->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * 处理消息事件
     * 
     * @param TcpConnection $connection 连接对象
     * @param mixed $data 消息数据
     */
    private function handleMessage(TcpConnection $connection, $data): void
    {
        try {
            $this->logger->debug('Received message', [
                'connection_id' => $connection->id,
                'data_length' => strlen($data)
            ]);
            
            // 解析消息
            $message = $this->protocolManager->parseMessage($data);
            
            // 处理消息
            $response = $this->processMessage($message, $connection);
            
            // 发送响应
            if ($response !== null) {
                $this->sendResponse($connection, $response);
            }
            
        } catch (\Throwable $e) {
            $this->logger->error('Error handling message', [
                'connection_id' => $connection->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // 发送错误响应
            $this->sendErrorResponse($connection, $e);
        }
    }
    
    /**
     * 处理关闭事件
     * 
     * @param TcpConnection $connection 连接对象
     */
    private function handleClose(TcpConnection $connection): void
    {
        try {
            $this->logger->info('Connection closed', [
                'connection_id' => $connection->id
            ]);
            
            // 移除连接
            $this->connectionManager->removeConnection($connection);
            
        } catch (\Throwable $e) {
            $this->logger->error('Error handling connection close', [
                'connection_id' => $connection->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * 处理错误事件
     * 
     * @param TcpConnection $connection 连接对象
     * @param mixed $error 错误信息
     */
    private function handleError(TcpConnection $connection, $error): void
    {
        $this->logger->error('Connection error', [
            'connection_id' => $connection->id,
            'error' => $error
        ]);
    }
    
    /**
     * 处理 MCP 消息
     * 
     * @param array $message 消息数据
     * @param TcpConnection $connection 连接对象
     * @return array|null 响应数据
     */
    private function processMessage(array $message, TcpConnection $connection): ?array
    {
        $method = $message['method'] ?? '';
        
        return match ($method) {
            'initialize' => $this->handleInitialize($message, $connection),
            'tools/list' => $this->handleToolsList($message, $connection),
            'tools/call' => $this->handleToolCall($message, $connection),
            'resources/list' => $this->handleResourcesList($message, $connection),
            'resources/read' => $this->handleResourceRead($message, $connection),
            'prompts/list' => $this->handlePromptsList($message, $connection),
            'prompts/get' => $this->handlePromptGet($message, $connection),
            default => $this->handleUnknownMethod($message, $connection)
        };
    }
    
    /**
     * 处理初始化请求
     * 
     * @param array $message 消息数据
     * @param TcpConnection $connection 连接对象
     * @return array 响应数据
     */
    private function handleInitialize(array $message, TcpConnection $connection): array
    {
        $this->logger->info('Handling initialize request', [
            'connection_id' => $connection->id
        ]);
        
        return [
            'jsonrpc' => '2.0',
            'id' => $message['id'] ?? null,
            'result' => [
                'protocolVersion' => '2024-11-05',
                'capabilities' => [
                    'tools' => [],
                    'resources' => [],
                    'prompts' => []
                ],
                'serverInfo' => [
                    'name' => 'PFPMcp',
                    'version' => '1.0.0'
                ]
            ]
        ];
    }
    
    /**
     * 处理工具列表请求
     * 
     * @param array $message 消息数据
     * @param TcpConnection $connection 连接对象
     * @return array 响应数据
     */
    private function handleToolsList(array $message, TcpConnection $connection): array
    {
        $tools = $this->toolManager->listTools();
        
        return [
            'jsonrpc' => '2.0',
            'id' => $message['id'] ?? null,
            'result' => [
                'tools' => $tools
            ]
        ];
    }
    
    /**
     * 处理工具调用请求
     * 
     * @param array $message 消息数据
     * @param TcpConnection $connection 连接对象
     * @return array 响应数据
     */
    private function handleToolCall(array $message, TcpConnection $connection): array
    {
        $params = $message['params'] ?? [];
        $toolName = $params['name'] ?? '';
        $arguments = $params['arguments'] ?? [];
        
        $result = $this->toolManager->callTool($toolName, $arguments);
        
        return [
            'jsonrpc' => '2.0',
            'id' => $message['id'] ?? null,
            'result' => [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => json_encode($result)
                    ]
                ]
            ]
        ];
    }
    
    /**
     * 处理资源列表请求
     * 
     * @param array $message 消息数据
     * @param TcpConnection $connection 连接对象
     * @return array 响应数据
     */
    private function handleResourcesList(array $message, TcpConnection $connection): array
    {
        $resources = $this->resourceManager->listResources();
        
        return [
            'jsonrpc' => '2.0',
            'id' => $message['id'] ?? null,
            'result' => [
                'resources' => $resources
            ]
        ];
    }
    
    /**
     * 处理资源读取请求
     * 
     * @param array $message 消息数据
     * @param TcpConnection $connection 连接对象
     * @return array 响应数据
     */
    private function handleResourceRead(array $message, TcpConnection $connection): array
    {
        $params = $message['params'] ?? [];
        $uri = $params['uri'] ?? '';
        
        $resource = $this->resourceManager->readResource($uri);
        
        return [
            'jsonrpc' => '2.0',
            'id' => $message['id'] ?? null,
            'result' => $resource
        ];
    }
    
    /**
     * 处理提示列表请求
     * 
     * @param array $message 消息数据
     * @param TcpConnection $connection 连接对象
     * @return array 响应数据
     */
    private function handlePromptsList(array $message, TcpConnection $connection): array
    {
        $prompts = $this->promptManager->listPrompts();
        
        return [
            'jsonrpc' => '2.0',
            'id' => $message['id'] ?? null,
            'result' => [
                'prompts' => $prompts
            ]
        ];
    }
    
    /**
     * 处理提示获取请求
     * 
     * @param array $message 消息数据
     * @param TcpConnection $connection 连接对象
     * @return array 响应数据
     */
    private function handlePromptGet(array $message, TcpConnection $connection): array
    {
        $params = $message['params'] ?? [];
        $name = $params['name'] ?? '';
        $arguments = $params['arguments'] ?? [];
        
        $prompt = $this->promptManager->getPrompt($name, $arguments);
        
        return [
            'jsonrpc' => '2.0',
            'id' => $message['id'] ?? null,
            'result' => [
                'prompt' => $prompt
            ]
        ];
    }
    
    /**
     * 处理未知方法请求
     * 
     * @param array $message 消息数据
     * @param TcpConnection $connection 连接对象
     * @return array 响应数据
     */
    private function handleUnknownMethod(array $message, TcpConnection $connection): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $message['id'] ?? null,
            'error' => [
                'code' => -32601,
                'message' => 'Method not found',
                'data' => [
                    'method' => $message['method'] ?? 'unknown'
                ]
            ]
        ];
    }
    
    /**
     * 发送初始化消息
     * 
     * @param TcpConnection $connection 连接对象
     */
    private function sendInitializationMessage(TcpConnection $connection): void
    {
        $message = [
            'jsonrpc' => '2.0',
            'method' => 'notifications/initialized',
            'params' => []
        ];
        
        $this->sendResponse($connection, $message);
    }
    
    /**
     * 发送响应
     * 
     * @param TcpConnection $connection 连接对象
     * @param array $response 响应数据
     */
    private function sendResponse(TcpConnection $connection, array $response): void
    {
        try {
            $data = $this->protocolManager->encodeMessage($response);
            $connection->send($data);
            
            $this->logger->debug('Sent response', [
                'connection_id' => $connection->id,
                'response_length' => strlen($data)
            ]);
            
        } catch (\Throwable $e) {
            $this->logger->error('Failed to send response', [
                'connection_id' => $connection->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * 发送错误响应
     * 
     * @param TcpConnection $connection 连接对象
     * @param \Throwable $error 错误对象
     */
    private function sendErrorResponse(TcpConnection $connection, \Throwable $error): void
    {
        $response = [
            'jsonrpc' => '2.0',
            'id' => null,
            'error' => [
                'code' => -32603,
                'message' => 'Internal error',
                'data' => [
                    'error' => $error->getMessage()
                ]
            ]
        ];
        
        $this->sendResponse($connection, $response);
    }
    
    /**
     * 注册工具
     * 
     * @param object $tool 工具对象
     */
    public function registerTool(object $tool): void
    {
        $this->toolManager->registerTool($tool);
    }
    
    /**
     * 注册资源
     * 
     * @param object $resource 资源对象
     */
    public function registerResource(object $resource): void
    {
        $this->resourceManager->registerResource($resource);
    }
    
    /**
     * 注册提示
     * 
     * @param object $prompt 提示对象
     */
    public function registerPrompt(object $prompt): void
    {
        $this->promptManager->registerPrompt($prompt);
    }
    
    /**
     * 获取服务器状态
     * 
     * @return bool 服务器运行状态
     */
    public function isRunning(): bool
    {
        return $this->isRunning;
    }
    
    /**
     * 获取配置
     * 
     * @return ServerConfig 服务器配置
     */
    public function getConfig(): ServerConfig
    {
        return $this->config;
    }
    
    /**
     * 获取日志记录器
     * 
     * @return LoggerInterface 日志记录器
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
