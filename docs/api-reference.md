# API 参考文档

## 📋 概述

本文档提供了 PFPMcp MCP 服务器的完整 API 参考，包括所有类、方法、属性和配置选项的详细说明。

## 🏗️ 核心类

### Server 类

主要的 MCP 服务器类，负责管理服务器的生命周期和配置。

#### 构造函数

```php
public function __construct(?ServerConfig $config = null, ?LoggerInterface $logger = null)
```

**参数:**
- `ServerConfig|null $config` - 服务器配置对象，可选
- `LoggerInterface|null $logger` - 日志记录器，可选

**示例:**
```php
use PFPMcp\Server;
use PFPMcp\Config\ServerConfig;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$config = new ServerConfig(['transport' => 'http', 'port' => 8080]);
$logger = new Logger('mcp-server');
$logger->pushHandler(new StreamHandler('php://stderr', Logger::INFO));

$server = new Server($config, $logger);
```

#### 主要方法

##### start()
启动 MCP 服务器

```php
public function start(): void
```

**示例:**
```php
$server->start();
```

**异常:**
- `ServerException` - 当服务器启动失败时

##### stop()
停止 MCP 服务器

```php
public function stop(): void
```

**示例:**
```php
$server->stop();
```

##### restart()
重启 MCP 服务器

```php
public function restart(): void
```

**示例:**
```php
$server->restart();
```

##### registerTool()
注册 MCP 工具

```php
public function registerTool(object $tool): void
```

**参数:**
- `object $tool` - 工具对象，必须包含带有 `#[McpTool]` 属性的方法

**示例:**
```php
use PFPMcp\Tools\Calculator;

$calculator = new Calculator();
$server->registerTool($calculator);
```

##### registerResource()
注册 MCP 资源

```php
public function registerResource(object $resource): void
```

**参数:**
- `object $resource` - 资源对象，必须包含带有 `#[McpResource]` 属性的方法

##### registerPrompt()
注册 MCP 提示

```php
public function registerPrompt(object $prompt): void
```

**参数:**
- `object $prompt` - 提示对象，必须包含带有 `#[McpPrompt]` 属性的方法

##### isRunning()
检查服务器是否正在运行

```php
public function isRunning(): bool
```

**返回值:**
- `bool` - 服务器运行状态

##### getConfig()
获取服务器配置

```php
public function getConfig(): ServerConfig
```

**返回值:**
- `ServerConfig` - 服务器配置对象

##### getLogger()
获取日志记录器

```php
public function getLogger(): LoggerInterface
```

**返回值:**
- `LoggerInterface` - 日志记录器实例

## ⚙️ 配置类

### ServerConfig 类

服务器配置管理类，负责处理所有配置选项。

#### 构造函数

```php
public function __construct(array $config = [])
```

**参数:**
- `array $config` - 配置数组，可选

**示例:**
```php
use PFPMcp\Config\ServerConfig;

$config = new ServerConfig([
    'transport' => 'http',
    'host' => '0.0.0.0',
    'port' => 8080,
    'log_level' => 'info'
]);
```

#### 配置选项

##### 传输协议配置

| 选项 | 类型 | 默认值 | 描述 |
|------|------|--------|------|
| `transport` | string | `'stdio'` | 传输协议类型 |
| `host` | string | `'0.0.0.0'` | 服务器主机地址 |
| `port` | int | `8080` | 服务器端口号 |

**支持的传输协议:**
- `stdio` - 标准输入输出
- `http` - HTTP 协议
- `ws` - WebSocket 协议
- `http+sse` - HTTP + Server-Sent Events
- `streamable-http` - 可恢复的 HTTP 传输

##### 日志配置

| 选项 | 类型 | 默认值 | 描述 |
|------|------|--------|------|
| `log_level` | string | `'info'` | 日志级别 |
| `log_file` | string | `'php://stderr'` | 日志文件路径 |

**支持的日志级别:**
- `debug` - 调试信息
- `info` - 一般信息
- `warning` - 警告信息
- `error` - 错误信息

##### 会话配置

| 选项 | 类型 | 默认值 | 描述 |
|------|------|--------|------|
| `session.backend` | string | `'memory'` | 会话后端类型 |
| `session.ttl` | int | `3600` | 会话超时时间（秒） |

**支持的会话后端:**
- `memory` - 内存存储
- `redis` - Redis 存储
- `database` - 数据库存储

##### 安全配置

| 选项 | 类型 | 默认值 | 描述 |
|------|------|--------|------|
| `security.rate_limit` | int | `100` | 请求速率限制 |
| `security.rate_window` | int | `60` | 速率限制时间窗口（秒） |

##### 性能配置

| 选项 | 类型 | 默认值 | 描述 |
|------|------|--------|------|
| `performance.max_connections` | int | `1000` | 最大连接数 |
| `performance.timeout` | int | `30` | 连接超时时间（秒） |

##### Stdio 配置

| 选项 | 类型 | 默认值 | 描述 |
|------|------|--------|------|
| `stdio.mode` | string | `'optimized'` | Stdio 模式 |
| `stdio.buffer_interval` | int | `10` | 缓冲区处理间隔（毫秒） |
| `stdio.non_blocking` | bool | `true` | 是否使用非阻塞模式 |

**支持的 Stdio 模式:**
- `auto` - 自动选择最优模式
- `optimized` - 优化的非阻塞模式
- `blocking` - 传统的阻塞模式

#### 主要方法

##### getTransport()
获取传输协议

```php
public function getTransport(): string
```

##### getHost()
获取主机地址

```php
public function getHost(): string
```

##### getPort()
获取端口号

```php
public function getPort(): int
```

##### getLogLevel()
获取日志级别

```php
public function getLogLevel(): string
```

##### getLogFile()
获取日志文件路径

```php
public function getLogFile(): string
```

##### getStdioConfig()
获取 Stdio 配置

```php
public function getStdioConfig(): array
```

##### getSessionConfig()
获取会话配置

```php
public function getSessionConfig(): array
```

##### getSecurityConfig()
获取安全配置

```php
public function getSecurityConfig(): array
```

##### getPerformanceConfig()
获取性能配置

```php
public function getPerformanceConfig(): array
```

##### getAll()
获取完整配置

```php
public function getAll(): array
```

##### get()
获取配置项

```php
public function get(string $key, mixed $default = null): mixed
```

**参数:**
- `string $key` - 配置键
- `mixed $default` - 默认值

##### set()
设置配置项

```php
public function set(string $key, mixed $value): void
```

**参数:**
- `string $key` - 配置键
- `mixed $value` - 配置值

**异常:**
- `ConfigException` - 当配置无效时

##### has()
检查配置项是否存在

```php
public function has(string $key): bool
```

##### loadFromFile()
从文件加载配置

```php
public function loadFromFile(string $file): void
```

**参数:**
- `string $file` - 配置文件路径

**异常:**
- `ConfigException` - 当文件不存在或配置无效时

##### createFromFile()
从文件创建配置实例

```php
public static function createFromFile(string $file): self
```

**参数:**
- `string $file` - 配置文件路径

**返回值:**
- `self` - 配置实例

## 🛠️ 工具系统

### McpTool 属性

用于标记 MCP 工具方法的属性。

```php
#[Attribute(Attribute::TARGET_METHOD)]
class McpTool
{
    public string $name;
    public string $description;
    
    public function __construct(string $name, string $description = '');
}
```

**参数:**
- `string $name` - 工具名称
- `string $description` - 工具描述

**示例:**
```php
use PFPMcp\Attributes\McpTool;

class MyTool
{
    #[McpTool(name: 'my_tool', description: '我的工具')]
    public function execute(string $input): array
    {
        return ['result' => strtoupper($input)];
    }
}
```

### Schema 属性

用于定义参数描述和类型的属性。

```php
#[Attribute(Attribute::TARGET_PARAMETER)]
class Schema
{
    public string $description;
    
    public function __construct(string $description = '');
}
```

**参数:**
- `string $description` - 参数描述

**示例:**
```php
use PFPMcp\Attributes\Schema;

class MyTool
{
    #[McpTool(name: 'my_tool')]
    public function execute(
        #[Schema(description: '输入参数')]
        string $input
    ): array {
        return ['result' => $input];
    }
}
```

### ToolManager 类

工具管理器，负责工具的注册、发现和调用。

#### 构造函数

```php
public function __construct(LoggerInterface $logger)
```

#### 主要方法

##### registerTool()
注册工具

```php
public function registerTool(object $tool): void
```

##### callTool()
调用工具

```php
public function callTool(string $toolName, array $arguments = []): mixed
```

**参数:**
- `string $toolName` - 工具名称
- `array $arguments` - 参数数组

**返回值:**
- `mixed` - 工具执行结果

##### listTools()
列出所有工具

```php
public function listTools(): array
```

**返回值:**
- `array` - 工具列表

##### hasTool()
检查工具是否存在

```php
public function hasTool(string $toolName): bool
```

##### getTool()
获取工具信息

```php
public function getTool(string $toolName): ?array
```

##### removeTool()
移除工具

```php
public function removeTool(string $toolName): void
```

##### clearTools()
清空所有工具

```php
public function clearTools(): void
```

##### getToolCount()
获取工具数量

```php
public function getToolCount(): int
```

## 🚀 传输协议

### TransportInterface 接口

传输协议的统一接口。

```php
interface TransportInterface
{
    public function start(): void;
    public function stop(): void;
    public function send(string $message): void;
    public function onMessage(callable $handler): void;
    public function onConnect(callable $handler): void;
    public function onClose(callable $handler): void;
    public function onError(callable $handler): void;
    public function getInfo(): array;
    public function isRunning(): bool;
}
```

### StdioTransport 类

标准输入输出传输协议。

#### 构造函数

```php
public function __construct(array $config = [])
```

**配置选项:**
- `mode` - 模式选择 (auto|optimized|blocking)
- `non_blocking` - 是否启用非阻塞模式
- `buffer_interval` - 缓冲区处理间隔

### HttpTransport 类

HTTP 传输协议。

#### 构造函数

```php
public function __construct(string $host = '0.0.0.0', int $port = 8080)
```

### WebSocketTransport 类

WebSocket 传输协议。

#### 构造函数

```php
public function __construct(string $host = '0.0.0.0', int $port = 8080)
```

## 🔧 工具示例

### Calculator 类

内置的计算器工具，提供基本的数学计算功能。

#### 方法

##### calculate()
执行数学计算

```php
#[McpTool(name: 'calculate', description: '执行数学计算，支持基本的四则运算')]
public function calculate(
    #[Schema(description: '要计算的数学表达式，如 2 + 3 * 4')]
    string $expression
): array
```

**参数:**
- `string $expression` - 数学表达式

**返回值:**
- `array` - 计算结果

**示例:**
```php
$calculator = new Calculator();
$result = $calculator->calculate('2 + 3 * 4');
// 返回: ['success' => true, 'result' => 14, 'expression' => '2 + 3 * 4', 'timestamp' => 1640995200]
```

##### add()
计算两个数的和

```php
#[McpTool(name: 'add', description: '计算两个数的和')]
public function add(
    #[Schema(description: '第一个数')]
    float $a,
    #[Schema(description: '第二个数')]
    float $b
): array
```

##### subtract()
计算两个数的差

```php
#[McpTool(name: 'subtract', description: '计算两个数的差')]
public function subtract(
    #[Schema(description: '第一个数')]
    float $a,
    #[Schema(description: '第二个数')]
    float $b
): array
```

##### multiply()
计算两个数的积

```php
#[McpTool(name: 'multiply', description: '计算两个数的积')]
public function multiply(
    #[Schema(description: '第一个数')]
    float $a,
    #[Schema(description: '第二个数')]
    float $b
): array
```

##### divide()
计算两个数的商

```php
#[McpTool(name: 'divide', description: '计算两个数的商')]
public function divide(
    #[Schema(description: '第一个数')]
    float $a,
    #[Schema(description: '第二个数')]
    float $b
): array
```

##### power()
计算幂运算

```php
#[McpTool(name: 'power', description: '计算幂运算')]
public function power(
    #[Schema(description: '底数')]
    float $base,
    #[Schema(description: '指数')]
    float $exponent
): array
```

##### sqrt()
计算平方根

```php
#[McpTool(name: 'sqrt', description: '计算平方根')]
public function sqrt(
    #[Schema(description: '要计算平方根的数')]
    float $number
): array
```

## 🚨 异常处理

### ServerException

服务器相关异常。

```php
class ServerException extends \Exception
{
    protected string $errorCode = 'SERVER_ERROR';
}
```

### ConfigException

配置相关异常。

```php
class ConfigException extends \Exception
{
    protected string $errorCode = 'CONFIG_ERROR';
}
```

### ToolException

工具相关异常。

```php
class ToolException extends \Exception
{
    protected string $errorCode = 'TOOL_ERROR';
}
```

### TransportException

传输协议相关异常。

```php
class TransportException extends \Exception
{
    protected string $errorCode = 'TRANSPORT_ERROR';
}
```

## 📝 环境变量

PFPMcp 支持通过环境变量进行配置：

### 传输协议配置
- `MCP_TRANSPORT` - 传输协议类型
- `MCP_HOST` - 服务器主机地址
- `MCP_PORT` - 服务器端口号

### 日志配置
- `MCP_LOG_LEVEL` - 日志级别
- `MCP_LOG_FILE` - 日志文件路径

### 会话配置
- `MCP_SESSION_BACKEND` - 会话后端类型
- `MCP_SESSION_TTL` - 会话超时时间

### 安全配置
- `MCP_RATE_LIMIT` - 请求速率限制
- `MCP_RATE_WINDOW` - 速率限制时间窗口

### 性能配置
- `MCP_MAX_CONNECTIONS` - 最大连接数
- `MCP_TIMEOUT` - 连接超时时间

### Stdio 配置
- `MCP_STDIO_MODE` - Stdio 模式
- `MCP_STDIO_BUFFER_INTERVAL` - 缓冲区处理间隔
- `MCP_STDIO_NON_BLOCKING` - 是否使用非阻塞模式

## 🔗 相关链接

- [快速开始指南](quickstart.md)
- [配置说明](configuration.md)
- [示例代码](../examples/)
- [故障排除指南](troubleshooting.md)

---

**文档版本**: 1.0  
**创建日期**: 2025-01-27  
**最后更新**: 2025-01-27  
**维护者**: PFPMcp 开发团队
