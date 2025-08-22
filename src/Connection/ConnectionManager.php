<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp\Connection;

use PFPMcp\Config\ServerConfig;
use Workerman\Connection\TcpConnection;
use Psr\Log\LoggerInterface;

/**
 * 连接管理器
 * 
 * 负责管理客户端连接的生命周期
 * 
 * @package PFPMcp\Connection
 */
class ConnectionManager
{
    /**
     * 活跃连接
     */
    private array $connections = [];
    
    /**
     * 服务器配置
     */
    private ServerConfig $config;
    
    /**
     * 日志记录器
     */
    private LoggerInterface $logger;
    
    /**
     * 构造函数
     * 
     * @param ServerConfig $config 服务器配置
     * @param LoggerInterface $logger 日志记录器
     */
    public function __construct(ServerConfig $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }
    
    /**
     * 添加连接
     * 
     * @param TcpConnection $connection 连接对象
     */
    public function addConnection(TcpConnection $connection): void
    {
        $maxConnections = $this->config->getPerformanceConfig()['max_connections'];
        
        if (count($this->connections) >= $maxConnections) {
            $this->logger->warning('Maximum connections reached', [
                'max_connections' => $maxConnections,
                'current_connections' => count($this->connections)
            ]);
            $connection->close();
            return;
        }
        
        $this->connections[$connection->id] = $connection;
        
        $this->logger->info('Connection added', [
            'connection_id' => $connection->id,
            'total_connections' => count($this->connections)
        ]);
    }
    
    /**
     * 移除连接
     * 
     * @param TcpConnection $connection 连接对象
     */
    public function removeConnection(TcpConnection $connection): void
    {
        if (isset($this->connections[$connection->id])) {
            unset($this->connections[$connection->id]);
            
            $this->logger->info('Connection removed', [
                'connection_id' => $connection->id,
                'total_connections' => count($this->connections)
            ]);
        }
    }
    
    /**
     * 获取连接
     * 
     * @param int $connectionId 连接ID
     * @return TcpConnection|null 连接对象
     */
    public function getConnection(int $connectionId): ?TcpConnection
    {
        return $this->connections[$connectionId] ?? null;
    }
    
    /**
     * 获取所有连接
     * 
     * @return array 连接列表
     */
    public function getAllConnections(): array
    {
        return $this->connections;
    }
    
    /**
     * 获取连接数量
     * 
     * @return int 连接数量
     */
    public function getConnectionCount(): int
    {
        return count($this->connections);
    }
    
    /**
     * 关闭所有连接
     */
    public function closeAllConnections(): void
    {
        foreach ($this->connections as $connection) {
            $connection->close();
        }
        
        $this->connections = [];
        
        $this->logger->info('All connections closed');
    }
    
    /**
     * 广播消息
     * 
     * @param string $message 消息内容
     * @param array $excludeIds 排除的连接ID列表
     */
    public function broadcast(string $message, array $excludeIds = []): void
    {
        $sentCount = 0;
        
        foreach ($this->connections as $connectionId => $connection) {
            if (in_array($connectionId, $excludeIds, true)) {
                continue;
            }
            
            try {
                $connection->send($message);
                $sentCount++;
            } catch (\Throwable $e) {
                $this->logger->error('Failed to send broadcast message', [
                    'connection_id' => $connectionId,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->logger->info('Broadcast message sent', [
            'total_connections' => count($this->connections),
            'sent_count' => $sentCount,
            'excluded_count' => count($excludeIds)
        ]);
    }
    
    /**
     * 清理无效连接
     */
    public function cleanupInvalidConnections(): void
    {
        $invalidConnections = [];
        
        foreach ($this->connections as $connectionId => $connection) {
            if (!$connection->isConnected()) {
                $invalidConnections[] = $connectionId;
            }
        }
        
        foreach ($invalidConnections as $connectionId) {
            unset($this->connections[$connectionId]);
        }
        
        if (!empty($invalidConnections)) {
            $this->logger->info('Cleaned up invalid connections', [
                'invalid_count' => count($invalidConnections),
                'remaining_connections' => count($this->connections)
            ]);
        }
    }
}
