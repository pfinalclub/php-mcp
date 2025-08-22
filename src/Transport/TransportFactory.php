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
     * @return TransportInterface 传输协议实例
     * @throws TransportException 当传输协议类型不支持时
     */
    public static function create(string $type): TransportInterface
    {
        return match ($type) {
            'stdio' => new StdioTransport(),
            'http' => new HttpTransport(),
            'ws' => new WebSocketTransport(),
            'http+sse' => new HttpSseTransport(),
            'streamable-http' => new StreamableHttpTransport(),
            default => throw new TransportException("Unsupported transport type: {$type}")
        };
    }
}
