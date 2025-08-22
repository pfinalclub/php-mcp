<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp\Session;

/**
 * 会话类
 * 
 * 表示一个客户端会话
 * 
 * @package PFPMcp\Session
 */
class Session
{
    /**
     * 会话ID
     */
    private string $id;
    
    /**
     * 会话数据
     */
    private array $data = [];
    
    /**
     * 创建时间
     */
    private int $createdAt;
    
    /**
     * 最后访问时间
     */
    private int $lastAccessedAt;
    
    /**
     * 过期时间（秒）
     */
    private int $ttl;
    
    /**
     * 构造函数
     * 
     * @param string $id 会话ID
     * @param int $ttl 过期时间（秒）
     */
    public function __construct(string $id, int $ttl = 3600)
    {
        $this->id = $id;
        $this->ttl = $ttl;
        $this->createdAt = time();
        $this->lastAccessedAt = time();
    }
    
    /**
     * 获取会话ID
     * 
     * @return string 会话ID
     */
    public function getId(): string
    {
        return $this->id;
    }
    
    /**
     * 设置会话数据
     * 
     * @param string $key 键
     * @param mixed $value 值
     */
    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
        $this->lastAccessedAt = time();
    }
    
    /**
     * 获取会话数据
     * 
     * @param string $key 键
     * @param mixed $default 默认值
     * @return mixed 值
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->lastAccessedAt = time();
        return $this->data[$key] ?? $default;
    }
    
    /**
     * 检查会话数据是否存在
     * 
     * @param string $key 键
     * @return bool 是否存在
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }
    
    /**
     * 移除会话数据
     * 
     * @param string $key 键
     */
    public function remove(string $key): void
    {
        unset($this->data[$key]);
        $this->lastAccessedAt = time();
    }
    
    /**
     * 获取所有会话数据
     * 
     * @return array 会话数据
     */
    public function getAll(): array
    {
        return $this->data;
    }
    
    /**
     * 清空会话数据
     */
    public function clear(): void
    {
        $this->data = [];
        $this->lastAccessedAt = time();
    }
    
    /**
     * 检查会话是否过期
     * 
     * @return bool 是否过期
     */
    public function isExpired(): bool
    {
        return (time() - $this->lastAccessedAt) > $this->ttl;
    }
    
    /**
     * 获取创建时间
     * 
     * @return int 创建时间戳
     */
    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }
    
    /**
     * 获取最后访问时间
     * 
     * @return int 最后访问时间戳
     */
    public function getLastAccessedAt(): int
    {
        return $this->lastAccessedAt;
    }
    
    /**
     * 获取过期时间
     * 
     * @return int 过期时间（秒）
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }
    
    /**
     * 设置过期时间
     * 
     * @param int $ttl 过期时间（秒）
     */
    public function setTtl(int $ttl): void
    {
        $this->ttl = $ttl;
    }
    
    /**
     * 获取剩余生存时间
     * 
     * @return int 剩余生存时间（秒）
     */
    public function getTimeToLive(): int
    {
        $elapsed = time() - $this->lastAccessedAt;
        return max(0, $this->ttl - $elapsed);
    }
    
    /**
     * 刷新会话
     */
    public function refresh(): void
    {
        $this->lastAccessedAt = time();
    }
}
