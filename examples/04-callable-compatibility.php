<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 * 
 * 示例：处理 callable 类型的兼容性写法
 */

namespace PFPMcp\Examples;

/**
 * 兼容的 Callable 处理示例
 * 
 * 展示如何在不使用 ?callable 语法的情况下处理可空的 callable 类型
 */
class CallableCompatibilityExample
{
    /**
     * 消息处理器 - 使用传统写法，避免 ?callable
     */
    private $messageHandler = null;
    
    /**
     * 事件监听器数组
     */
    private array $listeners = [];
    
    /**
     * 设置消息处理器
     * 
     * @param callable $handler 消息处理器
     */
    public function setMessageHandler(callable $handler): void
    {
        $this->messageHandler = $handler;
    }
    
    /**
     * 移除消息处理器
     */
    public function removeMessageHandler(): void
    {
        $this->messageHandler = null;
    }
    
    /**
     * 检查是否有消息处理器
     * 
     * @return bool
     */
    public function hasMessageHandler(): bool
    {
        return $this->messageHandler !== null && is_callable($this->messageHandler);
    }
    
    /**
     * 处理消息
     * 
     * @param string $message 消息内容
     */
    public function handleMessage(string $message): void
    {
        if ($this->hasMessageHandler()) {
            call_user_func($this->messageHandler, $message);
        }
    }
    
    /**
     * 添加事件监听器
     * 
     * @param string $event 事件名称
     * @param callable $listener 监听器
     */
    public function addListener(string $event, callable $listener): void
    {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }
        $this->listeners[$event][] = $listener;
    }
    
    /**
     * 移除事件监听器 - 兼容写法
     * 
     * @param string $event 事件名称
     * @param callable|null $listener 监听器，为 null 时移除所有
     */
    public function removeListener(string $event, callable $listener = null): void
    {
        if (!isset($this->listeners[$event])) {
            return;
        }
        
        if ($listener === null) {
            // 移除所有监听器
            unset($this->listeners[$event]);
        } else {
            // 移除特定监听器
            $key = array_search($listener, $this->listeners[$event], true);
            if ($key !== false) {
                unset($this->listeners[$event][$key]);
                // 重新索引数组
                $this->listeners[$event] = array_values($this->listeners[$event]);
            }
        }
    }
    
    /**
     * 触发事件
     * 
     * @param string $event 事件名称
     * @param array $args 事件参数
     */
    public function emit(string $event, array $args = []): void
    {
        if (!isset($this->listeners[$event])) {
            return;
        }
        
        foreach ($this->listeners[$event] as $listener) {
            if (is_callable($listener)) {
                call_user_func_array($listener, $args);
            }
        }
    }
    
    /**
     * 获取监听器数量
     * 
     * @param string $event 事件名称
     * @return int
     */
    public function getListenerCount(string $event): int
    {
        return isset($this->listeners[$event]) ? count($this->listeners[$event]) : 0;
    }
}

/**
 * 使用示例
 */
function demonstrateCallableCompatibility(): void
{
    $example = new CallableCompatibilityExample();
    
    // 设置消息处理器
    $example->setMessageHandler(function(string $message) {
        echo "处理消息: {$message}\n";
    });
    
    // 检查是否有处理器
    if ($example->hasMessageHandler()) {
        $example->handleMessage("Hello World");
    }
    
    // 添加事件监听器
    $example->addListener('user.login', function(string $username) {
        echo "用户登录: {$username}\n";
    });
    
    $example->addListener('user.logout', function(string $username) {
        echo "用户登出: {$username}\n";
    });
    
    // 触发事件
    $example->emit('user.login', ['admin']);
    $example->emit('user.logout', ['guest']);
    
    // 移除特定监听器
    $listener = function(string $username) {
        echo "用户登出: {$username}\n";
    };
    
    $example->addListener('user.logout', $listener);
    echo "监听器数量: " . $example->getListenerCount('user.logout') . "\n";
    
    $example->removeListener('user.logout', $listener);
    echo "移除后监听器数量: " . $example->getListenerCount('user.logout') . "\n";
    
    // 移除所有监听器
    $example->removeListener('user.login');
    echo "移除所有后监听器数量: " . $example->getListenerCount('user.login') . "\n";
}

// 运行示例
if (php_sapi_name() === 'cli') {
    demonstrateCallableCompatibility();
}
