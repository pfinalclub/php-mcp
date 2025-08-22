<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp\Core;

/**
 * MCP 协议核心组件
 * 
 * 处理 MCP 协议消息格式和通信
 * 
 * @package PFPMcp\Core
 */
class McpProtocol
{
    /**
     * 创建 MCP 请求消息
     * 
     * @param string $method 方法名
     * @param array $params 参数
     * @param string|null $id 请求ID
     * @return array 请求消息
     */
    public static function createRequest(string $method, array $params = [], ?string $id = null): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $id ?? uniqid(),
            'method' => $method,
            'params' => $params
        ];
    }
    
    /**
     * 创建 MCP 响应消息
     * 
     * @param mixed $result 结果
     * @param string $id 请求ID
     * @return array 响应消息
     */
    public static function createResponse(mixed $result, string $id): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => $result
        ];
    }
    
    /**
     * 创建 MCP 错误消息
     * 
     * @param int $code 错误码
     * @param string $message 错误消息
     * @param string $id 请求ID
     * @param mixed $data 错误数据
     * @return array 错误消息
     */
    public static function createError(int $code, string $message, string $id, mixed $data = null): array
    {
        $error = [
            'code' => $code,
            'message' => $message
        ];
        
        if ($data !== null) {
            $error['data'] = $data;
        }
        
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => $error
        ];
    }
    
    /**
     * 解析 MCP 消息
     * 
     * @param string $message 消息内容
     * @return array|null 解析后的消息
     */
    public static function parseMessage(string $message): ?array
    {
        try {
            $data = json_decode($message, true, 512, JSON_THROW_ON_ERROR);
            
            if (!is_array($data)) {
                return null;
            }
            
            // 验证基本格式
            if (!isset($data['jsonrpc']) || $data['jsonrpc'] !== '2.0') {
                return null;
            }
            
            return $data;
            
        } catch (\JsonException $e) {
            return null;
        }
    }
    
    /**
     * 验证 MCP 消息格式
     * 
     * @param array $message 消息
     * @return bool 是否有效
     */
    public static function validateMessage(array $message): bool
    {
        // 检查必需字段
        if (!isset($message['jsonrpc']) || $message['jsonrpc'] !== '2.0') {
            return false;
        }
        
        if (!isset($message['id'])) {
            return false;
        }
        
        // 检查方法调用
        if (isset($message['method'])) {
            if (!is_string($message['method'])) {
                return false;
            }
            
            if (isset($message['params']) && !is_array($message['params'])) {
                return false;
            }
        }
        
        // 检查响应
        if (isset($message['result']) && isset($message['error'])) {
            return false; // 不能同时有 result 和 error
        }
        
        return true;
    }
    
    /**
     * 获取标准错误码
     * 
     * @return array 错误码映射
     */
    public static function getErrorCodes(): array
    {
        return [
            -32700 => 'Parse error',
            -32600 => 'Invalid Request',
            -32601 => 'Method not found',
            -32602 => 'Invalid params',
            -32603 => 'Internal error',
            -32000 => 'Server error',
            -32001 => 'Server error start',
            -32099 => 'Server error end'
        ];
    }
}
