# Cursor MCP 服务故障排除指南

## 🚨 常见问题诊断

### 1. 服务器无法启动

#### 问题症状
- Cursor 显示 "MCP server failed to start"
- 服务器进程立即退出
- 无法连接到 MCP 服务器

#### 诊断步骤

**步骤 1: 检查 PHP 环境**
```bash
# 检查 PHP 版本
php --version

# 检查 PHP 扩展
php -m | grep -E "(json|openssl)"

# 检查 PHP 路径
which php
```

**步骤 2: 手动测试服务器**
```bash
# 进入项目目录
cd /path/to/pfinal-php-mcp

# 手动启动服务器
php examples/01-basic-usage/server.php

# 如果成功，应该看到类似输出：
# [INFO] Starting basic MCP server...
```

**步骤 3: 检查文件权限**
```bash
# 确保服务器文件有执行权限
chmod +x examples/01-basic-usage/server.php

# 检查目录权限
ls -la examples/01-basic-usage/
```

#### 解决方案

**问题 A: PHP 版本过低**
```bash
# 安装 PHP 8.2+
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-json php8.2-openssl
```

**问题 B: 缺少依赖**
```bash
# 安装 Composer 依赖
composer install

# 检查依赖是否完整
composer show
```

**问题 C: 路径问题**
```json
// 修正 Cursor 配置中的路径
{
  "mcpServers": {
    "pfinal-memo": {
      "command": "php",
      "args": ["/absolute/path/to/pfinal-php-mcp/examples/01-basic-usage/server.php"],
      "env": {
        "MCP_TRANSPORT": "stdio",
        "MCP_LOG_LEVEL": "debug"
      }
    }
  }
}
```

### 2. 连接超时

#### 问题症状
- Cursor 显示 "Connection timeout"
- 服务器启动但无法响应请求
- 长时间等待无响应

#### 诊断步骤

**步骤 1: 检查服务器状态**
```bash
# 检查服务器是否正在运行
ps aux | grep php

# 检查端口占用
netstat -tlnp | grep 8080
```

**步骤 2: 测试 stdio 传输**
```bash
# 使用测试脚本验证
echo '{"jsonrpc":"2.0","id":1,"method":"tools/list","params":[]}' | php examples/01-basic-usage/server.php
```

**步骤 3: 检查日志输出**
```bash
# 启动服务器并查看详细日志
MCP_LOG_LEVEL=debug php examples/01-basic-usage/server.php
```

#### 解决方案

**问题 A: 传输协议配置错误**
```json
// 确保使用 stdio 传输
{
  "mcpServers": {
    "pfinal-memo": {
      "command": "php",
      "args": ["/path/to/server.php"],
      "env": {
        "MCP_TRANSPORT": "stdio",  // 重要：必须使用 stdio
        "MCP_LOG_LEVEL": "debug"
      }
    }
  }
}
```

**问题 B: 服务器阻塞**
```php
// 检查服务器代码是否有阻塞操作
// 确保服务器正确处理 stdio 输入输出
```

### 3. 工具调用失败

#### 问题症状
- 工具列表为空
- 工具调用返回错误
- 参数验证失败

#### 诊断步骤

**步骤 1: 验证工具注册**
```bash
# 测试工具列表
echo '{"jsonrpc":"2.0","id":1,"method":"tools/list","params":[]}' | php examples/01-basic-usage/server.php
```

**步骤 2: 测试工具调用**
```bash
# 测试计算器工具
echo '{"jsonrpc":"2.0","id":2,"method":"tools/call","params":{"name":"add","arguments":{"a":10,"b":20}}}' | php examples/01-basic-usage/server.php
```

**步骤 3: 检查工具定义**
```php
// 确保工具类正确使用 Attributes
#[McpTool(name: 'add', description: '计算两个数的和')]
public function add(
    #[Schema(description: '第一个数')] float $a,
    #[Schema(description: '第二个数')] float $b
): array
```

#### 解决方案

**问题 A: 工具未注册**
```php
// 确保在服务器中注册工具
$server = new Server($config, $logger);
$server->registerTool(new Calculator());  // 重要：必须注册工具
```

**问题 B: Attributes 未生效**
```php
// 确保使用正确的命名空间
use PFPMcp\Attributes\McpTool;
use PFPMcp\Attributes\Schema;
```

### 4. JSON-RPC 协议错误

#### 问题症状
- 返回 "Invalid JSON-RPC" 错误
- 消息格式不正确
- 协议版本不匹配

#### 诊断步骤

**步骤 1: 验证 JSON 格式**
```bash
# 测试 JSON 格式
echo '{"jsonrpc":"2.0","id":1,"method":"tools/list","params":[]}' | jq .
```

**步骤 2: 检查协议版本**
```php
// 确保使用正确的 JSON-RPC 2.0 格式
{
    "jsonrpc": "2.0",
    "id": 1,
    "method": "tools/list",
    "params": {}
}
```

**步骤 3: 验证响应格式**
```bash
# 检查服务器响应格式
echo '{"jsonrpc":"2.0","id":1,"method":"tools/list","params":[]}' | php examples/01-basic-usage/server.php | jq .
```

#### 解决方案

**问题 A: 协议版本错误**
```php
// 确保服务器返回正确的 JSON-RPC 2.0 响应
{
    "jsonrpc": "2.0",
    "id": 1,
    "result": {
        "tools": [...]
    }
}
```

**问题 B: 消息格式错误**
```php
// 确保所有必需字段都存在
// jsonrpc, id, method, params (对于请求)
// jsonrpc, id, result/error (对于响应)
```

## 🔧 调试技巧

### 1. 启用详细日志

```json
{
  "mcpServers": {
    "pfinal-memo": {
      "command": "php",
      "args": ["/path/to/server.php"],
      "env": {
        "MCP_TRANSPORT": "stdio",
        "MCP_LOG_LEVEL": "debug",  // 启用调试日志
        "MCP_DEBUG": "true"
      }
    }
  }
}
```

### 2. 使用测试脚本

```bash
# 运行完整测试套件
php examples/test-mcp.php | php examples/01-basic-usage/server.php

# 运行交互式测试
php examples/test-mcp.php --interactive | php examples/01-basic-usage/server.php
```

### 3. 检查 Cursor 日志

在 Cursor 中：
1. 打开开发者工具 (Ctrl+Shift+I)
2. 查看 Console 标签页
3. 查找 MCP 相关的错误信息

### 4. 验证配置

```bash
# 创建测试配置文件
cat > test-config.json << EOF
{
  "mcpServers": {
    "test-server": {
      "command": "php",
      "args": ["$(pwd)/examples/01-basic-usage/server.php"],
      "env": {
        "MCP_TRANSPORT": "stdio",
        "MCP_LOG_LEVEL": "debug"
      }
    }
  }
}
EOF

# 测试配置
cp test-config.json ~/.cursor/settings.json
```

## 📋 检查清单

### 基础检查
- [ ] PHP 版本 >= 8.2
- [ ] 安装了必要的 PHP 扩展 (json, openssl)
- [ ] Composer 依赖已安装
- [ ] 服务器文件存在且有执行权限

### 配置检查
- [ ] Cursor 配置文件路径正确
- [ ] 服务器文件路径使用绝对路径
- [ ] 环境变量设置正确
- [ ] 传输协议设置为 "stdio"

### 功能检查
- [ ] 服务器可以手动启动
- [ ] 工具列表可以正常返回
- [ ] 工具调用可以正常执行
- [ ] JSON-RPC 协议格式正确

### 集成检查
- [ ] Cursor 可以连接到服务器
- [ ] 工具在 Cursor 中可见
- [ ] 工具调用在 Cursor 中工作
- [ ] 错误信息正确显示

## 🚀 快速修复方案

### 方案 1: 最小化配置

```json
{
  "mcpServers": {
    "pfinal-memo": {
      "command": "php",
      "args": ["/absolute/path/to/pfinal-php-mcp/examples/01-basic-usage/server.php"],
      "env": {
        "MCP_TRANSPORT": "stdio"
      }
    }
  }
}
```

### 方案 2: 调试配置

```json
{
  "mcpServers": {
    "pfinal-memo": {
      "command": "php",
      "args": ["/absolute/path/to/pfinal-php-mcp/examples/01-basic-usage/server.php"],
      "env": {
        "MCP_TRANSPORT": "stdio",
        "MCP_LOG_LEVEL": "debug",
        "MCP_DEBUG": "true"
      }
    }
  }
}
```

### 方案 3: 自定义服务器

```php
<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

use PFPMcp\Server;
use PFPMcp\Config\ServerConfig;
use PFPMcp\Tools\Calculator;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// 创建日志记录器
$logger = new Logger('mcp-server');
$logger->pushHandler(new StreamHandler('php://stderr', Logger::DEBUG));

// 创建配置
$config = new ServerConfig([
    'transport' => 'stdio',
    'log_level' => 'debug'
]);

// 创建服务器
$server = new Server($config, $logger);

// 注册工具
$server->registerTool(new Calculator());

// 启动服务器
$logger->info('Starting MCP server...');
$server->start();
```

## 📞 获取帮助

如果以上方案都无法解决问题，请：

1. **收集错误信息**：
   - Cursor 错误日志
   - 服务器启动日志
   - 测试脚本输出

2. **提供环境信息**：
   - 操作系统版本
   - PHP 版本
   - Cursor 版本
   - 项目路径

3. **创建最小复现示例**：
   - 简化的服务器代码
   - 简化的配置文件
   - 具体的错误步骤

4. **联系支持**：
   - GitHub Issues: https://github.com/pfinalclub/php-mcp/issues
   - 项目文档: https://github.com/pfinalclub/php-mcp

---

**注意**: 大多数问题都可以通过正确的配置和调试日志来解决。如果问题仍然存在，请提供详细的错误信息和环境配置。
