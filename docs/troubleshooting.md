# 故障排除指南

## 📋 概述

本指南帮助您解决在使用 PFPMcp MCP 服务器时可能遇到的常见问题。如果您遇到的问题不在本指南中，请查看 [GitHub Issues](https://github.com/pfinalclub/php-mcp/issues) 或创建新的问题。

## 🚨 常见问题

### 1. 服务器启动失败

#### 问题描述
服务器无法启动，出现错误信息。

#### 可能原因
- 端口被占用
- 配置错误
- 权限不足
- 依赖缺失

#### 解决方案

##### 检查端口占用
```bash
# 检查端口是否被占用
netstat -tulpn | grep :8080
# 或使用 lsof
lsof -i :8080

# 如果端口被占用，可以：
# 1. 杀死占用端口的进程
sudo kill -9 <PID>
# 2. 或更改配置使用其他端口
```

##### 检查配置
```php
// 验证配置是否正确
try {
    $config = new ServerConfig([
        'transport' => 'http',
        'host' => '0.0.0.0',
        'port' => 8080
    ]);
    echo "配置验证通过\n";
} catch (ConfigException $e) {
    echo "配置错误: " . $e->getMessage() . "\n";
}
```

##### 检查权限
```bash
# 确保有足够的权限
sudo chown -R $USER:$USER /path/to/your/project
chmod +x server.php
```

##### 检查依赖
```bash
# 检查 PHP 版本
php -v

# 检查扩展
php -m | grep -E "(json|openssl|pcntl)"

# 安装缺失的扩展
sudo apt-get install php-json php-openssl php-pcntl
```

### 2. 工具注册失败

#### 问题描述
工具无法注册，出现 "Tool registration failed" 错误。

#### 可能原因
- 工具类没有正确的属性
- 方法签名不正确
- 依赖注入失败

#### 解决方案

##### 检查工具定义
```php
// 正确的工具定义
use PFPMcp\Attributes\McpTool;
use PFPMcp\Attributes\Schema;

class MyTool
{
    #[McpTool(name: 'my_tool', description: '我的工具')]
    public function execute(
        #[Schema(description: '输入参数')]
        string $input
    ): array {
        return ['result' => strtoupper($input)];
    }
}

// 错误的工具定义（缺少属性）
class BadTool
{
    public function execute(string $input): array
    {
        return ['result' => $input];
    }
}
```

##### 检查方法签名
```php
// 正确的方法签名
public function execute(string $input): array

// 错误的方法签名（返回类型不匹配）
public function execute(string $input): string
```

### 3. 连接超时

#### 问题描述
客户端连接服务器时出现超时错误。

#### 可能原因
- 网络问题
- 防火墙阻止
- 服务器负载过高
- 配置错误

#### 解决方案

##### 检查网络连接
```bash
# 测试网络连接
ping your-server-ip

# 测试端口连通性
telnet your-server-ip 8080
# 或使用 nc
nc -zv your-server-ip 8080
```

##### 检查防火墙
```bash
# 检查防火墙状态
sudo ufw status

# 允许端口通过防火墙
sudo ufw allow 8080

# 或临时关闭防火墙测试
sudo ufw disable
```

##### 检查服务器负载
```bash
# 检查系统负载
top
htop

# 检查内存使用
free -h

# 检查磁盘空间
df -h
```

##### 调整超时配置
```php
$config = new ServerConfig([
    'performance' => [
        'timeout' => 60, // 增加超时时间
        'max_connections' => 500 // 减少最大连接数
    ]
]);
```

### 4. 内存泄漏

#### 问题描述
服务器运行一段时间后内存使用持续增长。

#### 可能原因
- 对象没有正确释放
- 循环引用
- 缓存无限增长
- 事件监听器未清理

#### 解决方案

##### 检查对象释放
```php
// 确保对象正确释放
class MyTool
{
    private $resource;
    
    public function __construct()
    {
        $this->resource = fopen('file.txt', 'r');
    }
    
    public function __destruct()
    {
        if ($this->resource) {
            fclose($this->resource);
        }
    }
}
```

##### 检查循环引用
```php
// 避免循环引用
class ParentClass
{
    private $children = [];
    
    public function addChild(ChildClass $child): void
    {
        $this->children[] = $child;
        $child->setParent($this);
    }
    
    public function removeChild(ChildClass $child): void
    {
        $key = array_search($child, $this->children, true);
        if ($key !== false) {
            unset($this->children[$key]);
            $child->setParent(null);
        }
    }
}
```

##### 监控内存使用
```php
// 添加内存监控
class MemoryMonitor
{
    public function checkMemoryUsage(): void
    {
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        
        if ($memoryUsage > 100 * 1024 * 1024) { // 100MB
            $this->logger->warning('High memory usage detected', [
                'current' => $memoryUsage,
                'peak' => $memoryPeak
            ]);
        }
    }
}
```

### 5. 性能问题

#### 问题描述
服务器响应缓慢，处理请求时间长。

#### 可能原因
- 工具执行效率低
- 网络延迟
- 资源竞争
- 配置不当

#### 解决方案

##### 优化工具性能
```php
// 使用缓存提高性能
class OptimizedTool
{
    private array $cache = [];
    
    #[McpTool(name: 'expensive_operation')]
    public function expensiveOperation(string $input): array
    {
        $cacheKey = md5($input);
        
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        $result = $this->performExpensiveOperation($input);
        $this->cache[$cacheKey] = $result;
        
        return $result;
    }
}
```

##### 使用异步处理
```php
// 对于耗时操作，使用异步处理
class AsyncTool
{
    #[McpTool(name: 'async_operation')]
    public function asyncOperation(string $input): array
    {
        // 启动异步任务
        $this->startAsyncTask($input);
        
        return [
            'status' => 'processing',
            'message' => '任务已启动，请稍后查询结果'
        ];
    }
}
```

##### 调整配置
```php
$config = new ServerConfig([
    'performance' => [
        'max_connections' => 1000,
        'timeout' => 30
    ],
    'stdio' => [
        'mode' => 'optimized',
        'buffer_interval' => 5
    ]
]);
```

### 6. 日志问题

#### 问题描述
日志文件过大或日志级别不正确。

#### 解决方案

##### 配置日志轮转
```php
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

$logger = new Logger('mcp-server');
$handler = new RotatingFileHandler('logs/mcp.log', 30, Logger::INFO);
$logger->pushHandler($handler);
```

##### 调整日志级别
```php
$config = new ServerConfig([
    'log_level' => 'warning' // 生产环境使用 warning 或 error
]);
```

##### 清理旧日志
```bash
# 清理超过 30 天的日志
find logs/ -name "*.log" -mtime +30 -delete

# 或使用 logrotate
sudo logrotate -f /etc/logrotate.d/mcp-server
```

## 🔧 调试技巧

### 1. 启用调试模式

```php
$config = new ServerConfig([
    'log_level' => 'debug'
]);

$logger = new Logger('mcp-server');
$logger->pushHandler(new StreamHandler('php://stderr', Logger::DEBUG));
```

### 2. 使用 Xdebug

```bash
# 安装 Xdebug
sudo apt-get install php-xdebug

# 配置 Xdebug
echo "xdebug.mode=debug" >> /etc/php/8.2/cli/conf.d/20-xdebug.ini
echo "xdebug.start_with_request=yes" >> /etc/php/8.2/cli/conf.d/20-xdebug.ini
```

### 3. 添加调试日志

```php
class DebugTool
{
    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    #[McpTool(name: 'debug_tool')]
    public function debugTool(string $input): array
    {
        $this->logger->debug('Tool called', ['input' => $input]);
        
        try {
            $result = $this->processInput($input);
            $this->logger->debug('Tool completed', ['result' => $result]);
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Tool failed', [
                'input' => $input,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
```

### 4. 性能分析

```php
class PerformanceProfiler
{
    private array $timers = [];
    
    public function startTimer(string $name): void
    {
        $this->timers[$name] = microtime(true);
    }
    
    public function endTimer(string $name): float
    {
        if (!isset($this->timers[$name])) {
            return 0;
        }
        
        $duration = microtime(true) - $this->timers[$name];
        unset($this->timers[$name]);
        
        return $duration;
    }
    
    public function logPerformance(string $operation, float $duration): void
    {
        if ($duration > 1.0) { // 超过 1 秒
            $this->logger->warning('Slow operation detected', [
                'operation' => $operation,
                'duration' => $duration
            ]);
        }
    }
}
```

## 📊 监控和诊断

### 1. 健康检查

```php
class HealthChecker
{
    public function checkHealth(): array
    {
        return [
            'status' => 'healthy',
            'timestamp' => time(),
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'uptime' => time() - $this->startTime,
            'connections' => $this->getConnectionCount()
        ];
    }
}
```

### 2. 系统监控

```bash
# 监控系统资源
htop

# 监控网络连接
netstat -tulpn

# 监控日志
tail -f logs/mcp.log

# 监控错误日志
tail -f logs/error.log
```

### 3. 性能监控

```php
class PerformanceMonitor
{
    public function getMetrics(): array
    {
        return [
            'requests_per_second' => $this->calculateRPS(),
            'average_response_time' => $this->calculateAverageResponseTime(),
            'error_rate' => $this->calculateErrorRate(),
            'memory_usage' => memory_get_usage(true),
            'cpu_usage' => $this->getCpuUsage()
        ];
    }
}
```

## 🆘 获取帮助

### 1. 查看日志

```bash
# 查看最新日志
tail -n 100 logs/mcp.log

# 查看错误日志
grep "ERROR" logs/mcp.log

# 实时监控日志
tail -f logs/mcp.log
```

### 2. 检查配置

```php
// 验证配置
$config = new ServerConfig();
echo "当前配置:\n";
print_r($config->getAll());
```

### 3. 测试连接

```bash
# 测试 stdio 传输
echo '{"jsonrpc":"2.0","method":"initialize","id":1}' | php server.php

# 测试 HTTP 传输
curl -X POST http://localhost:8080/mcp \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"initialize","id":1}'
```

### 4. 联系支持

- **GitHub Issues**: [创建问题](https://github.com/pfinalclub/php-mcp/issues)
- **文档**: 查看 [完整文档](../docs/)
- **示例**: 参考 [示例代码](../examples/)

## 📝 问题报告模板

如果您需要报告问题，请包含以下信息：

```markdown
## 问题描述
简要描述遇到的问题

## 环境信息
- PHP 版本: 
- 操作系统: 
- PFPMcp 版本: 
- 传输协议: 

## 复现步骤
1. 
2. 
3. 

## 预期行为
描述您期望的行为

## 实际行为
描述实际发生的行为

## 错误信息
```
粘贴错误信息或日志
```

## 配置信息
```php
// 粘贴相关配置
```

## 附加信息
任何其他相关信息
```

---

**文档版本**: 1.0  
**创建日期**: 2025-01-27  
**最后更新**: 2025-01-27  
**维护者**: PFPMcp 开发团队
