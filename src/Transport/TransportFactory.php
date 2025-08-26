<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp\Transport;

use PFPMcp\Exceptions\TransportException;

/**
 * 传输协议工厂
 * 
 * 负责创建不同类型的传输协议实例
 * 
 * @package PFPMcp\Transport
 */
class TransportFactory
{
    /**
     * 创建传输协议实例
     * 
     * @param string $type 传输协议类型
     * @param array $config 配置参数
     * @return TransportInterface 传输协议实例
     * @throws TransportException 当传输协议类型不支持时
     */
    public static function create(string $type, array $config = []): TransportInterface
    {
        return match ($type) {
            'stdio' => new StdioTransport($config['stdio'] ?? []),
            'http' => new HttpTransport($config['host'] ?? '0.0.0.0', $config['port'] ?? 8080),
            'ws' => new WebSocketTransport($config['host'] ?? '0.0.0.0', $config['port'] ?? 8080),
            'http+sse' => new HttpSseTransport($config['host'] ?? '0.0.0.0', $config['port'] ?? 8080),
            'streamable-http' => new StreamableHttpTransport($config['host'] ?? '0.0.0.0', $config['port'] ?? 8080),
            default => throw new TransportException("Unsupported transport type: {$type}")
        };
    }
    
    /**
     * 获取支持的传输协议类型
     * 
     * @return array 支持的传输协议类型列表
     */
    public static function getSupportedTypes(): array
    {
        return [
            'stdio' => 'Standard Input/Output (with optimization options)',
            'http' => 'HTTP Server',
            'ws' => 'WebSocket Server',
            'http+sse' => 'HTTP Server-Sent Events',
            'streamable-http' => 'Streamable HTTP Server'
        ];
    }
    
    /**
     * 检查传输协议类型是否支持
     * 
     * @param string $type 传输协议类型
     * @return bool 是否支持
     */
    public static function isSupported(string $type): bool
    {
        return in_array($type, array_keys(self::getSupportedTypes()));
    }
}
