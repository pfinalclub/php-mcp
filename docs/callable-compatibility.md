# PHP Callable 类型兼容性指南

## 问题描述

在 PHP 中，`?callable` 语法（可空的 callable 类型）在某些上下文中可能不被支持，特别是在：

1. **PHP 版本兼容性**：某些 PHP 版本对联合类型的支持有限
2. **静态分析工具**：某些静态分析工具可能不完全支持 `?callable`
3. **IDE 支持**：某些 IDE 可能对 `?callable` 语法支持不完善

## 解决方案

### 1. 使用传统写法替代 `?callable`

**不推荐的写法：**
```php
private ?callable $messageHandler = null;

public function off(string $event, ?callable $listener = null): void
```

**推荐的写法：**
```php
private $messageHandler = null;

public function off(string $event, callable $listener = null): void
```

### 2. 使用 `is_callable()` 函数进行类型检查

```php
public function hasMessageHandler(): bool
{
    return $this->messageHandler !== null && is_callable($this->messageHandler);
}

public function handleMessage(string $message): void
{
    if ($this->hasMessageHandler()) {
        call_user_func($this->messageHandler, $message);
    }
}
```

### 3. 使用 PHPDoc 注释提供类型信息

```php
/**
 * 消息处理器
 * 
 * @var callable|null
 */
private $messageHandler = null;

/**
 * 移除事件监听器
 * 
 * @param string $event 事件名称
 * @param callable|null $listener 监听器回调，为null时移除所有监听器
 */
public function off(string $event, callable $listener = null): void
```

## 最佳实践

### 1. 属性声明

```php
class Example
{
    /**
     * 消息处理器
     * 
     * @var callable|null
     */
    private $messageHandler = null;
    
    /**
     * 事件监听器
     * 
     * @var array<string, callable[]>
     */
    private array $listeners = [];
}
```

### 2. 方法参数

```php
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
```

### 3. 事件处理

```php
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
 * 移除事件监听器
 * 
 * @param string $event 事件名称
 * @param callable|null $listener 监听器，为null时移除所有
 */
public function removeListener(string $event, callable $listener = null): void
{
    if (!isset($this->listeners[$event])) {
        return;
    }
    
    if ($listener === null) {
        unset($this->listeners[$event]);
    } else {
        $key = array_search($listener, $this->listeners[$event], true);
        if ($key !== false) {
            unset($this->listeners[$event][$key]);
            $this->listeners[$event] = array_values($this->listeners[$event]);
        }
    }
}
```

## 类型安全建议

### 1. 运行时类型检查

```php
public function callHandler(string $message): void
{
    if (!$this->hasMessageHandler()) {
        throw new \RuntimeException('No message handler set');
    }
    
    call_user_func($this->messageHandler, $message);
}
```

### 2. 使用类型提示和文档

```php
/**
 * 处理消息
 * 
 * @param string $message 消息内容
 * @throws \RuntimeException 当没有设置消息处理器时
 */
public function handleMessage(string $message): void
{
    if (!$this->hasMessageHandler()) {
        throw new \RuntimeException('No message handler set');
    }
    
    call_user_func($this->messageHandler, $message);
}
```

### 3. 使用接口约束

```php
interface MessageHandlerInterface
{
    public function handle(string $message): void;
}

class Example
{
    /**
     * @var MessageHandlerInterface|null
     */
    private $messageHandler = null;
    
    public function setMessageHandler(MessageHandlerInterface $handler): void
    {
        $this->messageHandler = $handler;
    }
    
    public function handleMessage(string $message): void
    {
        if ($this->messageHandler !== null) {
            $this->messageHandler->handle($message);
        }
    }
}
```

## 迁移指南

如果您正在从使用 `?callable` 的代码迁移，请按以下步骤进行：

1. **替换属性声明**：
   ```php
   // 从
   private ?callable $handler = null;
   
   // 到
   /** @var callable|null */
   private $handler = null;
   ```

2. **替换方法参数**：
   ```php
   // 从
   public function setHandler(?callable $handler): void
   
   // 到
   public function setHandler(callable $handler = null): void
   ```

3. **添加类型检查方法**：
   ```php
   public function hasHandler(): bool
   {
       return $this->handler !== null && is_callable($this->handler);
   }
   ```

4. **更新调用代码**：
   ```php
   // 从
   if ($this->handler !== null) {
       call_user_func($this->handler, $data);
   }
   
   // 到
   if ($this->hasHandler()) {
       call_user_func($this->handler, $data);
   }
   ```

## 总结

通过使用传统的 PHP 语法和适当的类型检查，我们可以避免 `?callable` 语法带来的兼容性问题，同时保持代码的类型安全性和可读性。这种方法在大多数 PHP 环境中都能正常工作，并且与静态分析工具和 IDE 有更好的兼容性。
