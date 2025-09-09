# 部署最佳实践

## 📋 概述

本文档提供了 PFPMcp MCP 服务器在生产环境中的部署最佳实践，包括安全配置、性能优化、监控设置和运维建议。

## 🏗️ 部署架构

### 推荐架构

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   负载均衡器     │    │   MCP 服务器     │    │   数据库/缓存    │
│   (Nginx)      │────│   (PFPMcp)     │────│   (Redis/MySQL) │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   SSL 终端      │    │   监控系统       │    │   日志系统       │
│   (Let's Encrypt)│    │   (Prometheus)  │    │   (ELK Stack)   │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## 🔒 安全配置

### 1. 网络安全

#### 防火墙配置
```bash
# 基本防火墙规则
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw deny 8080/tcp  # MCP 端口不对外暴露
sudo ufw enable
```

#### SSL/TLS 配置
```nginx
# Nginx SSL 配置
server {
    listen 443 ssl http2;
    server_name mcp.yourdomain.com;
    
    # SSL 配置
    ssl_certificate /etc/letsencrypt/live/mcp.yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/mcp.yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    
    # 安全头
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # MCP 服务器代理
    location /mcp {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_buffering off;
        proxy_cache off;
    }
}
```

### 2. 应用安全

#### 输入验证
```php
<?php declare(strict_types=1);

namespace PFPMcp\Security;

class InputValidator
{
    /**
     * 验证工具输入
     */
    public function validateToolInput(string $toolName, array $params): array
    {
        $rules = $this->getValidationRules($toolName);
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            if (!isset($params[$field])) {
                if ($rule['required'] ?? false) {
                    $errors[] = "Missing required parameter: {$field}";
                }
                continue;
            }
            
            if (!$this->validateField($field, $params[$field], $rule)) {
                $errors[] = "Invalid value for parameter: {$field}";
            }
        }
        
        if (!empty($errors)) {
            throw new ValidationException(implode('; ', $errors));
        }
        
        return $params;
    }
    
    /**
     * 验证字段
     */
    private function validateField(string $field, mixed $value, array $rule): bool
    {
        // 类型验证
        if (isset($rule['type']) && gettype($value) !== $rule['type']) {
            return false;
        }
        
        // 长度验证
        if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
            return false;
        }
        
        // 模式验证
        if (isset($rule['pattern']) && !preg_match($rule['pattern'], $value)) {
            return false;
        }
        
        // 范围验证
        if (isset($rule['min']) && $value < $rule['min']) {
            return false;
        }
        
        if (isset($rule['max']) && $value > $rule['max']) {
            return false;
        }
        
        return true;
    }
}
```

#### 速率限制
```php
<?php declare(strict_types=1);

namespace PFPMcp\Security;

class RateLimiter
{
    private array $requests = [];
    private int $maxRequests;
    private int $windowSeconds;
    
    public function __construct(int $maxRequests = 100, int $windowSeconds = 60)
    {
        $this->maxRequests = $maxRequests;
        $this->windowSeconds = $windowSeconds;
    }
    
    /**
     * 检查是否允许请求
     */
    public function isAllowed(string $identifier): bool
    {
        $now = time();
        $this->cleanup($now);
        
        if (!isset($this->requests[$identifier])) {
            $this->requests[$identifier] = [];
        }
        
        if (count($this->requests[$identifier]) >= $this->maxRequests) {
            return false;
        }
        
        $this->requests[$identifier][] = $now;
        return true;
    }
    
    /**
     * 清理过期请求
     */
    private function cleanup(int $now): void
    {
        foreach ($this->requests as $identifier => &$timestamps) {
            $timestamps = array_filter($timestamps, function($timestamp) use ($now) {
                return $timestamp > $now - $this->windowSeconds;
            });
        }
    }
}
```

### 3. 数据安全

#### 敏感信息加密
```php
<?php declare(strict_types=1);

namespace PFPMcp\Security;

class Encryption
{
    private string $key;
    private string $cipher = 'AES-256-GCM';
    
    public function __construct(string $key)
    {
        $this->key = $key;
    }
    
    /**
     * 加密数据
     */
    public function encrypt(string $data): string
    {
        $iv = random_bytes(12);
        $tag = '';
        
        $encrypted = openssl_encrypt($data, $this->cipher, $this->key, 0, $iv, $tag);
        
        return base64_encode($iv . $tag . $encrypted);
    }
    
    /**
     * 解密数据
     */
    public function decrypt(string $encryptedData): string
    {
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 12);
        $tag = substr($data, 12, 16);
        $encrypted = substr($data, 28);
        
        return openssl_decrypt($encrypted, $this->cipher, $this->key, 0, $iv, $tag);
    }
}
```

## ⚡ 性能优化

### 1. 服务器配置

#### PHP 配置优化
```ini
; php.ini 优化配置
memory_limit = 512M
max_execution_time = 300
max_input_time = 300
post_max_size = 100M
upload_max_filesize = 100M

; OPcache 配置
opcache.enable = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 20000
opcache.validate_timestamps = 0
opcache.save_comments = 1
opcache.fast_shutdown = 1

; 其他优化
realpath_cache_size = 4M
realpath_cache_ttl = 600
```

#### 系统配置优化
```bash
# 系统限制配置
echo "* soft nofile 65535" >> /etc/security/limits.conf
echo "* hard nofile 65535" >> /etc/security/limits.conf

# 内核参数优化
echo "net.core.somaxconn = 65535" >> /etc/sysctl.conf
echo "net.ipv4.tcp_max_syn_backlog = 65535" >> /etc/sysctl.conf
echo "net.core.netdev_max_backlog = 5000" >> /etc/sysctl.conf
sysctl -p
```

### 2. 应用优化

#### 连接池
```php
<?php declare(strict_types=1);

namespace PFPMcp\Pool;

class ConnectionPool
{
    private array $pool = [];
    private int $maxSize;
    private int $currentSize = 0;
    
    public function __construct(int $maxSize = 100)
    {
        $this->maxSize = $maxSize;
    }
    
    /**
     * 获取连接
     */
    public function get(): ConnectionInterface
    {
        if (empty($this->pool)) {
            if ($this->currentSize < $this->maxSize) {
                $connection = $this->createConnection();
                $this->currentSize++;
                return $connection;
            }
            
            // 等待连接可用
            return $this->waitForConnection();
        }
        
        return array_pop($this->pool);
    }
    
    /**
     * 释放连接
     */
    public function release(ConnectionInterface $connection): void
    {
        if (count($this->pool) < $this->maxSize) {
            $connection->reset();
            $this->pool[] = $connection;
        } else {
            $connection->close();
            $this->currentSize--;
        }
    }
    
    /**
     * 创建新连接
     */
    private function createConnection(): ConnectionInterface
    {
        return new Connection();
    }
    
    /**
     * 等待连接可用
     */
    private function waitForConnection(): ConnectionInterface
    {
        // 实现等待逻辑
        usleep(1000); // 1ms
        return $this->get();
    }
}
```

#### 缓存策略
```php
<?php declare(strict_types=1);

namespace PFPMcp\Cache;

class CacheManager
{
    private array $caches = [];
    
    /**
     * 获取缓存
     */
    public function get(string $key): mixed
    {
        foreach ($this->caches as $cache) {
            $value = $cache->get($key);
            if ($value !== null) {
                return $value;
            }
        }
        return null;
    }
    
    /**
     * 设置缓存
     */
    public function set(string $key, mixed $value, int $ttl = 300): void
    {
        foreach ($this->caches as $cache) {
            $cache->set($key, $value, $ttl);
        }
    }
    
    /**
     * 添加缓存层
     */
    public function addCache(CacheInterface $cache): void
    {
        $this->caches[] = $cache;
    }
}
```

### 3. 数据库优化

#### 连接管理
```php
<?php declare(strict_types=1);

namespace PFPMcp\Database;

class DatabaseManager
{
    private array $connections = [];
    private array $config;
    
    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    /**
     * 获取数据库连接
     */
    public function getConnection(string $name = 'default'): PDO
    {
        if (!isset($this->connections[$name])) {
            $this->connections[$name] = $this->createConnection($name);
        }
        
        return $this->connections[$name];
    }
    
    /**
     * 创建数据库连接
     */
    private function createConnection(string $name): PDO
    {
        $config = $this->config[$name] ?? $this->config['default'];
        
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        return new PDO($dsn, $config['username'], $config['password'], $options);
    }
}
```

## 📊 监控和日志

### 1. 监控配置

#### Prometheus 监控
```yaml
# prometheus.yml
global:
  scrape_interval: 15s

scrape_configs:
  - job_name: 'mcp-server'
    static_configs:
      - targets: ['localhost:9090']
    metrics_path: '/metrics'
    scrape_interval: 5s
```

#### 监控指标
```php
<?php declare(strict_types=1);

namespace PFPMcp\Monitoring;

class MetricsCollector
{
    private array $metrics = [];
    
    /**
     * 记录请求指标
     */
    public function recordRequest(string $method, float $duration, bool $success): void
    {
        $this->incrementCounter('requests_total', [
            'method' => $method,
            'status' => $success ? 'success' : 'error'
        ]);
        
        $this->recordHistogram('request_duration_seconds', $duration, [
            'method' => $method
        ]);
    }
    
    /**
     * 记录内存使用
     */
    public function recordMemoryUsage(): void
    {
        $this->recordGauge('memory_usage_bytes', memory_get_usage(true));
        $this->recordGauge('memory_peak_bytes', memory_get_peak_usage(true));
    }
    
    /**
     * 记录连接数
     */
    public function recordConnectionCount(int $count): void
    {
        $this->recordGauge('connections_active', $count);
    }
    
    /**
     * 增加计数器
     */
    private function incrementCounter(string $name, array $labels = []): void
    {
        $key = $name . '_' . md5(serialize($labels));
        $this->metrics[$key] = ($this->metrics[$key] ?? 0) + 1;
    }
    
    /**
     * 记录直方图
     */
    private function recordHistogram(string $name, float $value, array $labels = []): void
    {
        $key = $name . '_' . md5(serialize($labels));
        $this->metrics[$key][] = $value;
    }
    
    /**
     * 记录仪表
     */
    private function recordGauge(string $name, float $value): void
    {
        $this->metrics[$name] = $value;
    }
    
    /**
     * 获取指标
     */
    public function getMetrics(): array
    {
        return $this->metrics;
    }
}
```

### 2. 日志配置

#### 结构化日志
```php
<?php declare(strict_types=1);

namespace PFPMcp\Logging;

class StructuredLogger
{
    private LoggerInterface $logger;
    private array $context = [];
    
    public function __construct(LoggerInterface $logger, array $context = [])
    {
        $this->logger = $logger;
        $this->context = $context;
    }
    
    /**
     * 记录请求日志
     */
    public function logRequest(string $method, array $params, mixed $result, float $duration): void
    {
        $this->logger->info('Request processed', array_merge($this->context, [
            'method' => $method,
            'params' => $params,
            'result' => $result,
            'duration' => $duration,
            'timestamp' => time()
        ]));
    }
    
    /**
     * 记录错误日志
     */
    public function logError(string $method, \Throwable $error, array $context = []): void
    {
        $this->logger->error('Request failed', array_merge($this->context, $context, [
            'method' => $method,
            'error' => $error->getMessage(),
            'code' => $error->getCode(),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'trace' => $error->getTraceAsString(),
            'timestamp' => time()
        ]));
    }
}
```

#### 日志轮转
```bash
# logrotate 配置
# /etc/logrotate.d/mcp-server
/var/log/mcp-server/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        /bin/kill -USR1 `cat /var/run/mcp-server.pid 2> /dev/null` 2> /dev/null || true
    endscript
}
```

## 🚀 部署流程

### 1. 自动化部署

#### Docker 部署
```dockerfile
# Dockerfile
FROM php:8.3-fpm-alpine

# 安装系统依赖
RUN apk --no-cache add \
    nginx \
    supervisor \
    && docker-php-ext-enable opcache

# 安装 PHP 扩展
RUN docker-php-ext-install pdo_mysql pdo_sqlite opcache

# 设置工作目录
WORKDIR /var/www/mcp

# 复制应用代码
COPY . /var/www/mcp

# 安装 Composer 依赖
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 设置权限
RUN chown -R www-data:www-data /var/www/mcp

# 暴露端口
EXPOSE 80

# 启动服务
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
```

#### Docker Compose
```yaml
# docker-compose.yml
version: '3.8'

services:
  mcp-server:
    build: .
    ports:
      - "8080:80"
    environment:
      - MCP_ENV=production
      - MCP_LOG_LEVEL=info
      - MCP_TRANSPORT=http
      - MCP_HOST=0.0.0.0
      - MCP_PORT=80
    volumes:
      - ./storage:/var/www/mcp/storage
      - ./logs:/var/log/mcp
    restart: unless-stopped
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost/health"]
      interval: 30s
      timeout: 10s
      retries: 3
    depends_on:
      - redis
      - mysql

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
    restart: unless-stopped

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql
    restart: unless-stopped

volumes:
  redis_data:
  mysql_data:
```

### 2. 部署脚本

#### 部署脚本
```bash
#!/bin/bash
# deploy.sh

set -e

# 配置变量
APP_NAME="mcp-server"
APP_DIR="/var/www/mcp-server"
BACKUP_DIR="/var/backups/mcp-server"
REPO_URL="https://github.com/yourorg/php-mcp.git"

# 创建备份
backup() {
    echo "Creating backup..."
    if [ -d "$APP_DIR" ]; then
        sudo cp -r "$APP_DIR" "$BACKUP_DIR/$(date +%Y%m%d_%H%M%S)"
    fi
}

# 更新代码
update_code() {
    echo "Updating code..."
    if [ -d "$APP_DIR" ]; then
        cd "$APP_DIR"
        git pull origin main
    else
        git clone "$REPO_URL" "$APP_DIR"
        cd "$APP_DIR"
    fi
}

# 安装依赖
install_dependencies() {
    echo "Installing dependencies..."
    cd "$APP_DIR"
    composer install --no-dev --optimize-autoloader --no-interaction
}

# 运行测试
run_tests() {
    echo "Running tests..."
    cd "$APP_DIR"
    composer test
}

# 重启服务
restart_services() {
    echo "Restarting services..."
    sudo supervisorctl restart mcp-server:*
    sudo systemctl reload nginx
}

# 健康检查
health_check() {
    echo "Performing health check..."
    sleep 10
    curl -f http://localhost:8080/health || exit 1
}

# 主流程
main() {
    backup
    update_code
    install_dependencies
    run_tests
    restart_services
    health_check
    echo "Deployment completed successfully!"
}

main "$@"
```

### 3. 回滚策略

#### 回滚脚本
```bash
#!/bin/bash
# rollback.sh

set -e

APP_DIR="/var/www/mcp-server"
BACKUP_DIR="/var/backups/mcp-server"

# 获取最新的备份
LATEST_BACKUP=$(ls -t "$BACKUP_DIR" | head -n1)

if [ -z "$LATEST_BACKUP" ]; then
    echo "No backup found!"
    exit 1
fi

echo "Rolling back to: $LATEST_BACKUP"

# 停止服务
sudo supervisorctl stop mcp-server:*

# 恢复备份
sudo rm -rf "$APP_DIR"
sudo cp -r "$BACKUP_DIR/$LATEST_BACKUP" "$APP_DIR"

# 重启服务
sudo supervisorctl start mcp-server:*

echo "Rollback completed successfully!"
```

## 🔧 运维建议

### 1. 日常维护

#### 监控检查
```bash
#!/bin/bash
# daily_check.sh

# 检查服务状态
sudo supervisorctl status

# 检查磁盘空间
df -h

# 检查内存使用
free -h

# 检查日志大小
du -sh /var/log/mcp-server/

# 检查错误日志
grep -c "ERROR" /var/log/mcp-server/error.log
```

#### 性能调优
```bash
#!/bin/bash
# performance_tune.sh

# 清理缓存
sudo rm -rf /tmp/opcache-*

# 重启 PHP-FPM
sudo systemctl restart php8.3-fpm

# 清理日志
sudo find /var/log/mcp-server/ -name "*.log" -mtime +30 -delete
```

### 2. 故障处理

#### 自动重启
```bash
#!/bin/bash
# auto_restart.sh

# 检查服务状态
if ! curl -f http://localhost:8080/health > /dev/null 2>&1; then
    echo "Service is down, restarting..."
    sudo supervisorctl restart mcp-server:*
    sleep 10
    
    # 再次检查
    if ! curl -f http://localhost:8080/health > /dev/null 2>&1; then
        echo "Service restart failed, sending alert..."
        # 发送告警
        curl -X POST "https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK" \
             -H 'Content-type: application/json' \
             --data '{"text":"MCP Server is down and restart failed!"}'
    fi
fi
```

### 3. 备份策略

#### 数据备份
```bash
#!/bin/bash
# backup.sh

BACKUP_DIR="/var/backups/mcp-server"
DATE=$(date +%Y%m%d_%H%M%S)

# 创建备份目录
mkdir -p "$BACKUP_DIR/$DATE"

# 备份应用代码
cp -r /var/www/mcp-server "$BACKUP_DIR/$DATE/"

# 备份数据库
mysqldump -u root -p mcp_database > "$BACKUP_DIR/$DATE/database.sql"

# 备份配置文件
cp -r /etc/nginx/sites-available/mcp-server "$BACKUP_DIR/$DATE/"
cp -r /etc/supervisor/conf.d/mcp-server.conf "$BACKUP_DIR/$DATE/"

# 压缩备份
tar -czf "$BACKUP_DIR/mcp-server-$DATE.tar.gz" -C "$BACKUP_DIR" "$DATE"
rm -rf "$BACKUP_DIR/$DATE"

# 清理旧备份（保留30天）
find "$BACKUP_DIR" -name "*.tar.gz" -mtime +30 -delete

echo "Backup completed: mcp-server-$DATE.tar.gz"
```

## 📈 性能基准

### 1. 基准测试

#### 压力测试
```bash
# 使用 ab 进行压力测试
ab -n 10000 -c 100 http://localhost:8080/mcp

# 使用 wrk 进行压力测试
wrk -t12 -c400 -d30s http://localhost:8080/mcp
```

#### 性能指标
- **响应时间**: < 100ms (95th percentile)
- **吞吐量**: > 1000 requests/second
- **并发连接**: > 1000 connections
- **内存使用**: < 512MB
- **CPU 使用**: < 50%

### 2. 监控告警

#### 告警规则
```yaml
# alerting.yml
groups:
  - name: mcp-server
    rules:
      - alert: HighResponseTime
        expr: histogram_quantile(0.95, rate(request_duration_seconds_bucket[5m])) > 0.1
        for: 5m
        labels:
          severity: warning
        annotations:
          summary: "High response time detected"
          
      - alert: HighErrorRate
        expr: rate(requests_total{status="error"}[5m]) > 0.1
        for: 5m
        labels:
          severity: critical
        annotations:
          summary: "High error rate detected"
          
      - alert: HighMemoryUsage
        expr: memory_usage_bytes > 400 * 1024 * 1024
        for: 5m
        labels:
          severity: warning
        annotations:
          summary: "High memory usage detected"
```

---

**文档版本**: 1.0  
**创建日期**: 2025-01-27  
**最后更新**: 2025-01-27  
**维护者**: PFPMcp 开发团队
