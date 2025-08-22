# 快速开始

本指南将帮助您快速上手 PFPMcp MCP 服务器。

## 安装

### 使用 Composer 安装

```bash
composer require pfinal/php-mcp
```

### 从源码安装

```bash
git clone https://github.com/pfinal/php-mcp.git
cd php-mcp
composer install
```

## 基础使用

### 最简单的服务器

```php
<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

use PFPMcp\Server;
use PFPMcp\Tools\Calculator;

// 创建服务器
$server = new Server();

// 注册工具
$server->registerTool(new Calculator());

// 启动服务器
$server->start();
```

### 使用配置文件

```php
<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

use PFPMcp\Server;
use PFPMcp\Config\ServerConfig;
use PFPMcp\Tools\Calculator;

// 创建配置
$config = new ServerConfig([
    'transport' => 'http',
    'host' => '0.0.0.0',
    'port' => 8080,
    'log_level' => 'info'
]);

// 创建服务器
$server = new Server($config);

// 注册工具
$server->registerTool(new Calculator());

// 启动服务器
$server->start();
```

## 创建自定义工具

### 基本工具

```php
<?php declare(strict_types=1);

namespace MyApp\Tools;

use PhpMcp\Attributes\McpTool;
use PhpMcp\Attributes\Schema;

class MyTool
{
    #[McpTool(
        name: 'my_tool',
        description: '我的自定义工具'
    )]
    public function execute(
        #[Schema(description: '输入参数')]
        string $input
    ): array {
        return [
            'success' => true,
            'result' => strtoupper($input),
            'timestamp' => time()
        ];
    }
}
```

### 注册自定义工具

```php
<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

use PFPMcp\Server;
use MyApp\Tools\MyTool;

$server = new Server();
$server->registerTool(new MyTool());
$server->start();
```

## 配置选项

### 环境变量

```bash
# 传输协议
MCP_TRANSPORT=stdio          # stdio, http, ws, http+sse
MCP_HOST=0.0.0.0            # 服务器主机
MCP_PORT=8080               # 服务器端口
MCP_LOG_LEVEL=info          # 日志级别
MCP_LOG_FILE=php://stderr   # 日志文件

# 会话配置
MCP_SESSION_BACKEND=memory  # 会话后端
MCP_SESSION_TTL=3600        # 会话超时时间

# 安全配置
MCP_RATE_LIMIT=100          # 速率限制
MCP_RATE_WINDOW=60          # 速率窗口

# 性能配置
MCP_MAX_CONNECTIONS=1000    # 最大连接数
MCP_TIMEOUT=30              # 超时时间
```

### 配置文件

创建 `config/server.php`：

```php
<?php declare(strict_types=1);

return [
    'transport' => 'stdio',
    'host' => '0.0.0.0',
    'port' => 8080,
    'log_level' => 'info',
    'session' => [
        'backend' => 'memory',
        'ttl' => 3600,
    ],
    'security' => [
        'rate_limit' => 100,
        'rate_window' => 60,
    ],
    'performance' => [
        'max_connections' => 1000,
        'timeout' => 30,
    ],
];
```

## 传输协议

### stdio（默认）

适用于命令行环境，通过标准输入输出通信。

```php
$config = new ServerConfig([
    'transport' => 'stdio'
]);
```

### HTTP

适用于 Web 环境，通过 HTTP 协议通信。

```php
$config = new ServerConfig([
    'transport' => 'http',
    'host' => '0.0.0.0',
    'port' => 8080
]);
```

### WebSocket

适用于实时通信场景。

```php
$config = new ServerConfig([
    'transport' => 'ws',
    'host' => '0.0.0.0',
    'port' => 8080
]);
```

## 日志配置

### 基本日志

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('mcp-server');
$logger->pushHandler(new StreamHandler('php://stderr', Logger::INFO));

$server = new Server($config, $logger);
```

### 文件日志

```php
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

$logger = new Logger('mcp-server');
$logger->pushHandler(new RotatingFileHandler('logs/mcp.log', 30, Logger::INFO));

$server = new Server($config, $logger);
```

## 错误处理

### 异常处理

```php
try {
    $server = new Server($config);
    $server->start();
} catch (\PFPMcp\Exceptions\ServerException $e) {
    echo "服务器错误: " . $e->getMessage() . "\n";
    exit(1);
} catch (\Throwable $e) {
    echo "未知错误: " . $e->getMessage() . "\n";
    exit(1);
}
```

## 测试

### 运行测试

```bash
# 运行所有测试
composer test

# 生成测试覆盖率报告
composer test-coverage

# 运行代码质量检查
composer all
```

### 编写测试

```php
<?php declare(strict_types=1);

namespace PFPMcp\Tests;

use PFPMcp\Server;
use PFPMcp\Config\ServerConfig;
use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
    public function testServerCreation(): void
    {
        $config = new ServerConfig(['transport' => 'stdio']);
        $server = new Server($config);
        
        $this->assertInstanceOf(Server::class, $server);
        $this->assertFalse($server->isRunning());
    }
}
```

## 部署

### Docker 部署

```bash
# 构建镜像
docker build -t pfinal/php-mcp .

# 运行容器
docker run -d \
  --name mcp-server \
  -p 8080:8080 \
  -e MCP_TRANSPORT=http \
  -e MCP_PORT=8080 \
  pfinal/php-mcp
```

### Docker Compose

```bash
# 启动服务
docker-compose up -d

# 查看日志
docker-compose logs -f mcp-server

# 停止服务
docker-compose down
```

## 下一步

- 查看 [API 文档](api.md) 了解详细的 API 接口
- 查看 [配置说明](configuration.md) 了解所有配置选项
- 查看 [示例代码](../examples/) 了解更多使用场景
- 查看 [最佳实践](best-practices.md) 了解开发建议
