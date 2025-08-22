<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp\Session;

use PFPMcp\Config\ServerConfig;
use Psr\Log\LoggerInterface;

/**
 * 会话管理器
 * 
 * 负责管理客户端会话状态
 * 
 * @package PFPMcp\Session
 */
class SessionManager
{
    /**
     * 会话存储
     */
    private array $sessions = [];
    
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
     * 创建会话
     * 
     * @param string $sessionId 会话ID
     * @return Session 会话对象
     */
    public function createSession(string $sessionId): Session
    {
        $session = new Session($sessionId, $this->config->getSessionConfig()['ttl']);
        $this->sessions[$sessionId] = $session;
        
        $this->logger->info('Session created', ['session_id' => $sessionId]);
        
        return $session;
    }
    
    /**
     * 获取会话
     * 
     * @param string $sessionId 会话ID
     * @return Session|null 会话对象
     */
    public function getSession(string $sessionId): ?Session
    {
        if (!isset($this->sessions[$sessionId])) {
            return null;
        }
        
        $session = $this->sessions[$sessionId];
        
        // 检查会话是否过期
        if ($session->isExpired()) {
            $this->removeSession($sessionId);
            return null;
        }
        
        return $session;
    }
    
    /**
     * 移除会话
     * 
     * @param string $sessionId 会话ID
     */
    public function removeSession(string $sessionId): void
    {
        if (isset($this->sessions[$sessionId])) {
            unset($this->sessions[$sessionId]);
            $this->logger->info('Session removed', ['session_id' => $sessionId]);
        }
    }
    
    /**
     * 清理过期会话
     */
    public function cleanupExpiredSessions(): void
    {
        $expiredCount = 0;
        
        foreach ($this->sessions as $sessionId => $session) {
            if ($session->isExpired()) {
                unset($this->sessions[$sessionId]);
                $expiredCount++;
            }
        }
        
        if ($expiredCount > 0) {
            $this->logger->info('Expired sessions cleaned up', [
                'expired_count' => $expiredCount,
                'remaining_sessions' => count($this->sessions)
            ]);
        }
    }
    
    /**
     * 获取会话数量
     * 
     * @return int 会话数量
     */
    public function getSessionCount(): int
    {
        return count($this->sessions);
    }
    
    /**
     * 获取所有会话ID
     * 
     * @return array 会话ID列表
     */
    public function getSessionIds(): array
    {
        return array_keys($this->sessions);
    }
}
