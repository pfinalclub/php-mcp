<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp\EventHandler;

use Psr\Log\LoggerInterface;

/**
 * 事件处理器
 * 
 * 负责处理服务器事件的分发
 * 
 * @package PFPMcp\EventHandler
 */
class EventHandler
{
    /**
     * 事件监听器
     */
    private array $listeners = [];
    
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
     * 注册事件监听器
     * 
     * @param string $event 事件名称
     * @param callable $listener 监听器回调
     */
    public function on(string $event, callable $listener): void
    {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }
        
        $this->listeners[$event][] = $listener;
        
        $this->logger->debug('Event listener registered', ['event' => $event]);
    }
    
    /**
     * 触发事件
     * 
     * @param string $event 事件名称
     * @param mixed ...$args 事件参数
     */
    public function emit(string $event, ...$args): void
    {
        if (!isset($this->listeners[$event])) {
            return;
        }
        
        $this->logger->debug('Event triggered', ['event' => $event]);
        
        foreach ($this->listeners[$event] as $listener) {
            try {
                call_user_func_array($listener, $args);
            } catch (\Throwable $e) {
                $this->logger->error('Event listener error', [
                    'event' => $event,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }
    
    /**
     * 移除事件监听器
     * 
     * @param string $event 事件名称
     * @param callable|null $listener 监听器回调，为null时移除所有监听器
     */
    public function off(string $event, ?callable $listener = null): void
    {
        if (!isset($this->listeners[$event])) {
            return;
        }
        
        if ($listener === null) {
            unset($this->listeners[$event]);
            $this->logger->debug('All event listeners removed', ['event' => $event]);
        } else {
            $key = array_search($listener, $this->listeners[$event], true);
            if ($key !== false) {
                unset($this->listeners[$event][$key]);
                $this->logger->debug('Event listener removed', ['event' => $event]);
            }
        }
    }
    
    /**
     * 获取事件监听器数量
     * 
     * @param string $event 事件名称
     * @return int 监听器数量
     */
    public function getListenerCount(string $event): int
    {
        return isset($this->listeners[$event]) ? count($this->listeners[$event]) : 0;
    }
    
    /**
     * 检查事件是否有监听器
     * 
     * @param string $event 事件名称
     * @return bool 是否有监听器
     */
    public function hasListeners(string $event): bool
    {
        return isset($this->listeners[$event]) && !empty($this->listeners[$event]);
    }
    
    /**
     * 获取所有事件名称
     * 
     * @return array 事件名称列表
     */
    public function getEventNames(): array
    {
        return array_keys($this->listeners);
    }
    
    /**
     * 清空所有事件监听器
     */
    public function clear(): void
    {
        $this->listeners = [];
        $this->logger->info('All event listeners cleared');
    }
}
