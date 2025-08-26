# Stdio 传输协议优化

## 概述

PFPMcp 的 stdio 传输协议已经进行了重大优化，充分利用了 Workerman 的常驻进程和事件驱动优势，提供了更好的性能和稳定性。

## 优化特性

### 1. 非阻塞 I/O
- 使用 `stream_set_blocking()` 设置非阻塞模式
- 通过 `stream_select()` 进行非阻塞输入检查
- 避免阻塞主线程，提高响应性

### 2. 事件驱动架构
- 利用 Workerman 的 Timer 进行定时处理
- 可配置的缓冲区处理间隔
- 支持信号处理和优雅关闭

### 3. 智能模式选择
- **auto**: 自动选择最优模式
- **optimized**: 强制使用优化模式
- **blocking**: 使用传统阻塞模式

## 配置选项

### 环境变量
```bash
# stdio 模式选择
MCP_STDIO_MODE=optimized

# 缓冲区处理间隔（毫秒）
MCP_STDIO_BUFFER_INTERVAL=10

# 是否启用非阻塞模式
MCP_STDIO_NON_BLOCKING=true
```

### 代码配置
```php
$config = [
    'transport' => 'stdio',
    'stdio' => [
        'mode' => 'optimized',        // auto | optimized | blocking
        'buffer_interval' => 10,      // 缓冲区处理间隔（毫秒）
        'non_blocking' => true,       // 是否使用非阻塞模式
    ]
];
```

## 使用示例

### 基本使用
```php
use PFPMcp\Server;
use PFPMcp\Config\ServerConfig;

$config = new ServerConfig([
    'transport' => 'stdio',
    'stdio' => [
        'mode' => 'optimized'
    ]
]);

$server = new Server($config);
$server->start();
```

### 高级配置
```php
$config = new ServerConfig([
    'transport' => 'stdio',
    'stdio' => [
        'mode' => 'optimized',
        'buffer_interval' => 5,  // 5毫秒处理间隔
        'non_blocking' => true
    ]
]);
```

## 性能对比

| 特性 | 传统阻塞模式 | 优化非阻塞模式 |
|------|-------------|---------------|
| I/O 处理 | 阻塞式 | 非阻塞式 |
| 事件驱动 | ❌ | ✅ |
| 定时器支持 | ❌ | ✅ |
| 信号处理 | ❌ | ✅ |
| 优雅关闭 | ❌ | ✅ |
| 缓冲区管理 | ❌ | ✅ |
| 错误恢复 | 有限 | 完善 |

## 技术实现

### OptimizedStdioTransport
- 使用 Workerman Timer 进行定时处理
- 实现输入缓冲区管理
- 支持进程信号处理
- 提供优雅关闭机制

### LegacyStdioTransport
- 保持传统阻塞式处理
- 兼容性更好
- 适合简单应用场景

### StdioTransport 工厂
- 根据配置自动选择实现
- 支持运行时模式切换
- 提供统一接口

## 最佳实践

### 1. 模式选择
- **生产环境**: 使用 `optimized` 模式
- **开发环境**: 使用 `auto` 模式
- **兼容性要求**: 使用 `blocking` 模式

### 2. 性能调优
- 调整 `buffer_interval` 以平衡响应性和 CPU 使用
- 监控缓冲区大小和处理延迟
- 根据实际负载调整配置

### 3. 错误处理
- 启用详细日志记录
- 监控进程状态
- 实现健康检查

## 故障排除

### 常见问题

1. **非阻塞模式不支持**
   - 检查 PHP 扩展：`stream_set_blocking`, `stream_select`, `pcntl_signal`
   - 回退到 `blocking` 模式

2. **缓冲区处理延迟**
   - 调整 `buffer_interval` 参数
   - 检查系统负载

3. **进程退出异常**
   - 检查信号处理器注册
   - 验证优雅关闭逻辑

### 调试技巧

```php
// 启用调试日志
$config = [
    'transport' => 'stdio',
    'stdio' => [
        'mode' => 'optimized',
        'buffer_interval' => 10
    ],
    'log_level' => 'debug'
];

// 获取传输协议信息
$transport = $server->getTransport();
$info = $transport->getInfo();
var_dump($info);
```

## 兼容性

### 系统要求
- PHP 8.2+
- Workerman 4.0+
- 支持 POSIX 信号处理（Linux/Unix）

### 向后兼容
- 保持原有 API 接口不变
- 默认配置向后兼容
- 支持渐进式升级

## 总结

stdio 传输协议的优化充分利用了 Workerman 的优势，提供了更好的性能、稳定性和可维护性。通过智能模式选择和丰富的配置选项，可以适应不同的使用场景和性能要求。
