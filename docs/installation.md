# 安装指南

本指南将帮助您安装和配置 PFPMcp MCP 服务器。

## 系统要求

### 最低要求

- PHP 8.2 或更高版本
- Composer 2.0 或更高版本
- 支持 POSIX 扩展（Linux/Unix 环境）
- 至少 128MB 可用内存

### 推荐配置

- PHP 8.3 或更高版本
- Composer 2.6 或更高版本
- 512MB 或更多可用内存
- SSD 存储（用于日志和缓存）

### 必需的 PHP 扩展

- `ext-json` - JSON 处理
- `ext-openssl` - 加密和安全
- `ext-mbstring` - 多字节字符串处理
- `ext-curl` - HTTP 请求（可选）
- `ext-redis` - Redis 支持（可选）

## 安装方法

### 方法一：使用 Composer（推荐）

#### 1. 安装 Composer

如果还没有安装 Composer，请先安装：

```bash
# Linux/macOS
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Windows
# 下载并运行 Composer-Setup.exe
```

#### 2. 创建项目

```bash
# 创建新项目
composer create-project pfinal/php-mcp my-mcp-server

# 进入项目目录
cd my-mcp-server
```

#### 3. 安装依赖

```bash
composer install
```

### 方法二：从源码安装

#### 1. 克隆仓库

```bash
git clone https://github.com/pfinal/php-mcp.git
cd php-mcp
```

#### 2. 安装依赖

```bash
composer install
```

#### 3. 设置权限

```bash
# 创建日志目录
mkdir -p logs
chmod 755 logs

# 创建缓存目录
mkdir -p cache
chmod 755 cache
```

### 方法三：Docker 安装

#### 1. 拉取镜像

```bash
docker pull pfinal/php-mcp:latest
```

#### 2. 运行容器

```bash
docker run -d \
  --name mcp-server \
  -p 8080:8080 \
  -e MCP_TRANSPORT=http \
  -e MCP_PORT=8080 \
  pfinal/php-mcp:latest
```

## 验证安装

### 检查 PHP 版本

```bash
php --version
```

确保版本为 8.2 或更高。

### 检查 PHP 扩展

```bash
php -m | grep -E "(json|openssl|mbstring)"
```

确保所有必需的扩展都已安装。

### 运行测试

```bash
# 运行单元测试
composer test

# 检查代码质量
composer all
```

### 启动服务器

```bash
# 使用默认配置启动
php server.php

# 或使用自定义配置
MCP_TRANSPORT=http MCP_PORT=8080 php server.php
```

## 配置

### 基本配置

创建配置文件 `config/server.php`：

```php
<?php declare(strict_types=1);

return [
    'transport' => $_ENV['MCP_TRANSPORT'] ?? 'stdio',
    'host' => $_ENV['MCP_HOST'] ?? '0.0.0.0',
    'port' => (int)($_ENV['MCP_PORT'] ?? 8080),
    'log_level' => $_ENV['MCP_LOG_LEVEL'] ?? 'info',
    'log_file' => $_ENV['MCP_LOG_FILE'] ?? 'php://stderr',
    'session' => [
        'backend' => $_ENV['MCP_SESSION_BACKEND'] ?? 'memory',
        'ttl' => (int)($_ENV['MCP_SESSION_TTL'] ?? 3600),
    ],
    'security' => [
        'rate_limit' => (int)($_ENV['MCP_RATE_LIMIT'] ?? 100),
        'rate_window' => (int)($_ENV['MCP_RATE_WINDOW'] ?? 60),
    ],
    'performance' => [
        'max_connections' => (int)($_ENV['MCP_MAX_CONNECTIONS'] ?? 1000),
        'timeout' => (int)($_ENV['MCP_TIMEOUT'] ?? 30),
    ],
];
```

### 环境变量

设置环境变量：

```bash
# 传输协议配置
export MCP_TRANSPORT=stdio
export MCP_HOST=0.0.0.0
export MCP_PORT=8080
export MCP_LOG_LEVEL=info

# 会话配置
export MCP_SESSION_BACKEND=memory
export MCP_SESSION_TTL=3600

# 安全配置
export MCP_RATE_LIMIT=100
export MCP_RATE_WINDOW=60

# 性能配置
export MCP_MAX_CONNECTIONS=1000
export MCP_TIMEOUT=30
```

### 日志配置

#### 基本日志

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('mcp-server');
$logger->pushHandler(new StreamHandler('php://stderr', Logger::INFO));
```

#### 文件日志

```php
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

$logger = new Logger('mcp-server');
$logger->pushHandler(new RotatingFileHandler('logs/mcp.log', 30, Logger::INFO));
```

## 故障排除

### 常见问题

#### 1. PHP 版本过低

**错误信息：** `Fatal error: Uncaught Error: Class "PFPMcp\Server" not found`

**解决方案：** 确保 PHP 版本为 8.2 或更高。

#### 2. 缺少必需扩展

**错误信息：** `Fatal error: Uncaught Error: Call to undefined function json_encode()`

**解决方案：** 安装 JSON 扩展：

```bash
# Ubuntu/Debian
sudo apt-get install php-json

# CentOS/RHEL
sudo yum install php-json

# macOS
brew install php@8.3
```

#### 3. 权限问题

**错误信息：** `Permission denied`

**解决方案：** 设置正确的文件权限：

```bash
chmod 755 server.php
chmod -R 755 src/
chmod -R 755 config/
```

#### 4. 端口被占用

**错误信息：** `Address already in use`

**解决方案：** 更改端口或停止占用端口的服务：

```bash
# 更改端口
export MCP_PORT=8081

# 或查找并停止占用端口的进程
lsof -i :8080
kill -9 <PID>
```

### 调试模式

启用调试模式以获取更多信息：

```bash
export MCP_LOG_LEVEL=debug
php server.php
```

### 日志文件

查看日志文件以诊断问题：

```bash
# 查看错误日志
tail -f logs/error.log

# 查看访问日志
tail -f logs/access.log
```

## 性能优化

### 生产环境配置

```php
<?php declare(strict_types=1);

return [
    'transport' => 'http',
    'host' => '0.0.0.0',
    'port' => 8080,
    'log_level' => 'warning',
    'log_file' => 'logs/mcp.log',
    'session' => [
        'backend' => 'redis',
        'ttl' => 3600,
    ],
    'security' => [
        'rate_limit' => 1000,
        'rate_window' => 60,
    ],
    'performance' => [
        'max_connections' => 10000,
        'timeout' => 30,
    ],
];
```

### 系统优化

#### 1. 增加文件描述符限制

```bash
# 编辑 /etc/security/limits.conf
echo "* soft nofile 65536" >> /etc/security/limits.conf
echo "* hard nofile 65536" >> /etc/security/limits.conf
```

#### 2. 优化 PHP 配置

编辑 `php.ini`：

```ini
memory_limit = 512M
max_execution_time = 300
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
```

#### 3. 使用 Redis 会话存储

```bash
# 安装 Redis
sudo apt-get install redis-server

# 安装 PHP Redis 扩展
sudo apt-get install php-redis
```

## 安全配置

### 基本安全设置

```php
<?php declare(strict_types=1);

return [
    'security' => [
        'rate_limit' => 100,
        'rate_window' => 60,
        'allowed_origins' => ['https://yourdomain.com'],
        'api_key_required' => true,
    ],
];
```

### HTTPS 配置

```bash
# 生成 SSL 证书
openssl req -x509 -newkey rsa:4096 -keyout key.pem -out cert.pem -days 365 -nodes

# 配置 Nginx
server {
    listen 443 ssl;
    server_name yourdomain.com;
    
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    
    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

## 下一步

安装完成后，您可以：

1. 查看 [快速开始](quickstart.md) 了解基本使用方法
2. 查看 [API 文档](api.md) 了解详细的 API 接口
3. 查看 [示例代码](../examples/) 了解更多使用场景
4. 查看 [最佳实践](best-practices.md) 了解开发建议
