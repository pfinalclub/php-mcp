<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp\Protocol;

use Psr\Log\LoggerInterface;

/**
 * 协议管理器
 * 
 * 负责处理 MCP 消息的解析和编码
 * 
 * @package PFPMcp\Protocol
 */
class ProtocolManager
{
    /**
     * 日志记录器
     */
    private LoggerInterface $logger;
    
    /**
     * 构造函数
     * 
     * @param LoggerInterface $logger 日志记录器
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * 解析消息
     * 
     * @param string $data 原始数据
     * @return array 解析后的消息
     * @throws \JsonException 当JSON解析失败时
     */
    public function parseMessage(string $data): array
    {
        try {
            $message = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
            
            if (!is_array($message)) {
                throw new \JsonException('Message must be an object');
            }
            
            // 验证消息格式
            $this->validateMessage($message);
            
            $this->logger->debug('Message parsed successfully', [
                'message_id' => $message['id'] ?? null,
                'method' => $message['method'] ?? null
            ]);
            
            return $message;
            
        } catch (\JsonException $e) {
            $this->logger->error('Failed to parse message', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * 编码消息
     * 
     * @param array $message 消息数据
     * @return string 编码后的消息
     * @throws \JsonException 当JSON编码失败时
     */
    public function encodeMessage(array $message): string
    {
        try {
            $data = json_encode($message, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            
            $this->logger->debug('Message encoded successfully', [
                'message_id' => $message['id'] ?? null,
                'method' => $message['method'] ?? null
            ]);
            
            return $data;
            
        } catch (\JsonException $e) {
            $this->logger->error('Failed to encode message', [
                'message' => $message,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * 验证消息格式
     * 
     * @param array $message 消息数据
     * @throws \InvalidArgumentException 当消息格式无效时
     */
    private function validateMessage(array $message): void
    {
        // 检查必需的字段
        if (!isset($message['jsonrpc'])) {
            throw new \InvalidArgumentException('Missing required field: jsonrpc');
        }
        
        if ($message['jsonrpc'] !== '2.0') {
            throw new \InvalidArgumentException('Invalid jsonrpc version: ' . $message['jsonrpc']);
        }
        
        // 检查是请求还是响应
        if (isset($message['method'])) {
            // 这是一个请求
            $this->validateRequest($message);
        } elseif (isset($message['result']) || isset($message['error'])) {
            // 这是一个响应
            $this->validateResponse($message);
        } else {
            throw new \InvalidArgumentException('Message must be either a request or response');
        }
    }
    
    /**
     * 验证请求消息
     * 
     * @param array $message 消息数据
     * @throws \InvalidArgumentException 当请求格式无效时
     */
    private function validateRequest(array $message): void
    {
        if (!is_string($message['method'])) {
            throw new \InvalidArgumentException('Method must be a string');
        }
        
        if (isset($message['params']) && !is_array($message['params'])) {
            throw new \InvalidArgumentException('Params must be an object');
        }
        
        if (isset($message['id']) && !is_string($message['id']) && !is_int($message['id'])) {
            throw new \InvalidArgumentException('Id must be a string or number');
        }
    }
    
    /**
     * 验证响应消息
     * 
     * @param array $message 消息数据
     * @throws \InvalidArgumentException 当响应格式无效时
     */
    private function validateResponse(array $message): void
    {
        if (isset($message['result']) && isset($message['error'])) {
            throw new \InvalidArgumentException('Response cannot have both result and error');
        }
        
        if (!isset($message['result']) && !isset($message['error'])) {
            throw new \InvalidArgumentException('Response must have either result or error');
        }
        
        if (isset($message['error']) && !is_array($message['error'])) {
            throw new \InvalidArgumentException('Error must be an object');
        }
        
        if (isset($message['id']) && !is_string($message['id']) && !is_int($message['id']) && $message['id'] !== null) {
            throw new \InvalidArgumentException('Id must be a string, number, or null');
        }
    }
    
    /**
     * 创建错误响应
     * 
     * @param int $code 错误代码
     * @param string $message 错误消息
     * @param mixed $data 错误数据
     * @param mixed $id 请求ID
     * @return array 错误响应
     */
    public function createErrorResponse(int $code, string $message, mixed $data = null, mixed $id = null): array
    {
        $response = [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => $code,
                'message' => $message
            ]
        ];
        
        if ($data !== null) {
            $response['error']['data'] = $data;
        }
        
        if ($id !== null) {
            $response['id'] = $id;
        }
        
        return $response;
    }
    
    /**
     * 创建成功响应
     * 
     * @param mixed $result 结果数据
     * @param mixed $id 请求ID
     * @return array 成功响应
     */
    public function createSuccessResponse(mixed $result, mixed $id = null): array
    {
        $response = [
            'jsonrpc' => '2.0',
            'result' => $result
        ];
        
        if ($id !== null) {
            $response['id'] = $id;
        }
        
        return $response;
    }
    
    /**
     * 创建通知消息
     * 
     * @param string $method 方法名
     * @param array $params 参数
     * @return array 通知消息
     */
    public function createNotification(string $method, array $params = []): array
    {
        return [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params
        ];
    }
}
