# API 文档

本文档描述了 PFPMcp MCP 服务器的 API 接口。

## 概述

PFPMcp 实现了 Model Context Protocol (MCP) 规范，支持以下功能：

- 工具调用 (Tools)
- 资源访问 (Resources)
- 提示管理 (Prompts)
- 会话管理 (Sessions)

## 基础信息

### 协议版本

- MCP 版本：2024-11-05
- JSON-RPC 版本：2.0
- 支持传输协议：stdio、HTTP、WebSocket、HTTP+SSE

### 服务器信息

```json
{
  "name": "PFPMcp",
  "version": "1.0.0",
  "protocolVersion": "2024-11-05"
}
```

## 核心 API

### 初始化 (initialize)

初始化 MCP 连接。

**请求：**

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "initialize",
  "params": {
    "protocolVersion": "2024-11-05",
    "capabilities": {
      "tools": {},
      "resources": {},
      "prompts": {}
    },
    "clientInfo": {
      "name": "client-name",
      "version": "1.0.0"
    }
  }
}
```

**响应：**

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "result": {
    "protocolVersion": "2024-11-05",
    "capabilities": {
      "tools": {},
      "resources": {},
      "prompts": {}
    },
    "serverInfo": {
      "name": "PFPMcp",
      "version": "1.0.0"
    }
  }
}
```

## 工具 API

### 列出工具 (tools/list)

获取所有可用工具的列表。

**请求：**

```json
{
  "jsonrpc": "2.0",
  "id": 2,
  "method": "tools/list",
  "params": {}
}
```

**响应：**

```json
{
  "jsonrpc": "2.0",
  "id": 2,
  "result": {
    "tools": [
      {
        "name": "calculate",
        "description": "执行数学计算，支持基本的四则运算",
        "inputSchema": {
          "type": "object",
          "properties": {
            "expression": {
              "type": "string",
              "description": "要计算的数学表达式，如 2 + 3 * 4"
            }
          },
          "required": ["expression"]
        }
      }
    ]
  }
}
```

### 调用工具 (tools/call)

调用指定的工具。

**请求：**

```json
{
  "jsonrpc": "2.0",
  "id": 3,
  "method": "tools/call",
  "params": {
    "name": "calculate",
    "arguments": {
      "expression": "2 + 3 * 4"
    }
  }
}
```

**响应：**

```json
{
  "jsonrpc": "2.0",
  "id": 3,
  "result": {
    "content": [
      {
        "type": "text",
        "text": "{\"success\":true,\"result\":14,\"expression\":\"2 + 3 * 4\",\"timestamp\":1704067200}"
      }
    ]
  }
}
```

## 资源 API

### 列出资源 (resources/list)

获取所有可用资源的列表。

**请求：**

```json
{
  "jsonrpc": "2.0",
  "id": 4,
  "method": "resources/list",
  "params": {}
}
```

**响应：**

```json
{
  "jsonrpc": "2.0",
  "id": 4,
  "result": {
    "resources": [
      {
        "uri": "file:///path/to/file.txt",
        "name": "file",
        "description": "文件系统资源",
        "mimeType": "text/plain"
      }
    ]
  }
}
```

### 读取资源 (resources/read)

读取指定资源的内容。

**请求：**

```json
{
  "jsonrpc": "2.0",
  "id": 5,
  "method": "resources/read",
  "params": {
    "uri": "file:///path/to/file.txt"
  }
}
```

**响应：**

```json
{
  "jsonrpc": "2.0",
  "id": 5,
  "result": {
    "uri": "file:///path/to/file.txt",
    "mimeType": "text/plain",
    "text": "文件内容..."
  }
}
```

## 提示 API

### 列出提示 (prompts/list)

获取所有可用提示的列表。

**请求：**

```json
{
  "jsonrpc": "2.0",
  "id": 6,
  "method": "prompts/list",
  "params": {}
}
```

**响应：**

```json
{
  "jsonrpc": "2.0",
  "id": 6,
  "result": {
    "prompts": [
      {
        "name": "greeting",
        "description": "问候提示",
        "arguments": {
          "type": "object",
          "properties": {
            "name": {
              "type": "string",
              "description": "用户名"
            }
          }
        }
      }
    ]
  }
}
```

### 获取提示 (prompts/get)

获取指定提示的内容。

**请求：**

```json
{
  "jsonrpc": "2.0",
  "id": 7,
  "method": "prompts/get",
  "params": {
    "name": "greeting",
    "arguments": {
      "name": "张三"
    }
  }
}
```

**响应：**

```json
{
  "jsonrpc": "2.0",
  "id": 7,
  "result": {
    "prompt": [
      {
        "role": "user",
        "content": "你好，张三！"
      }
    ]
  }
}
```

## 错误处理

### 错误响应格式

```json
{
  "jsonrpc": "2.0",
  "id": null,
  "error": {
    "code": -32601,
    "message": "Method not found",
    "data": {
      "method": "unknown_method"
    }
  }
}
```

### 错误代码

| 代码 | 含义 | 描述 |
|------|------|------|
| -32700 | Parse error | JSON 解析错误 |
| -32600 | Invalid Request | 无效的请求 |
| -32601 | Method not found | 方法未找到 |
| -32602 | Invalid params | 无效的参数 |
| -32603 | Internal error | 内部错误 |
| -32000 to -32099 | Server error | 服务器错误 |

## 内置工具

### 计算器工具

#### calculate

执行数学计算。

**参数：**

- `expression` (string, 必需): 数学表达式

**示例：**

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "tools/call",
  "params": {
    "name": "calculate",
    "arguments": {
      "expression": "2 + 3 * 4"
    }
  }
}
```

**响应：**

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "result": {
    "content": [
      {
        "type": "text",
        "text": "{\"success\":true,\"result\":14,\"expression\":\"2 + 3 * 4\",\"timestamp\":1704067200}"
      }
    ]
  }
}
```

#### add

计算两个数的和。

**参数：**

- `a` (float, 必需): 第一个数
- `b` (float, 必需): 第二个数

#### subtract

计算两个数的差。

**参数：**

- `a` (float, 必需): 第一个数
- `b` (float, 必需): 第二个数

#### multiply

计算两个数的积。

**参数：**

- `a` (float, 必需): 第一个数
- `b` (float, 必需): 第二个数

#### divide

计算两个数的商。

**参数：**

- `a` (float, 必需): 第一个数
- `b` (float, 必需): 第二个数

#### power

计算幂运算。

**参数：**

- `base` (float, 必需): 底数
- `exponent` (float, 必需): 指数

#### sqrt

计算平方根。

**参数：**

- `number` (float, 必需): 要计算平方根的数

## 自定义工具开发

### 工具定义

使用 PHP 8 Attributes 定义工具：

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

### 工具注册

```php
$server = new Server();
$server->registerTool(new MyTool());
```

## 传输协议

### stdio

通过标准输入输出通信，适用于命令行环境。

**启动：**

```bash
php server.php
```

**通信：**

```bash
echo '{"jsonrpc":"2.0","id":1,"method":"initialize","params":{}}' | php server.php
```

### HTTP

通过 HTTP 协议通信，适用于 Web 环境。

**启动：**

```bash
MCP_TRANSPORT=http MCP_PORT=8080 php server.php
```

**请求：**

```bash
curl -X POST http://localhost:8080 \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","id":1,"method":"initialize","params":{}}'
```

### WebSocket

通过 WebSocket 协议通信，适用于实时通信场景。

**启动：**

```bash
MCP_TRANSPORT=ws MCP_PORT=8080 php server.php
```

## 会话管理

### 会话状态

服务器会为每个连接维护会话状态，包括：

- 连接信息
- 用户偏好
- 临时数据

### 会话配置

```php
$config = new ServerConfig([
    'session' => [
        'backend' => 'memory',  // memory, redis, database
        'ttl' => 3600,          // 会话超时时间（秒）
    ]
]);
```

## 安全考虑

### 输入验证

所有输入都会进行验证：

- 参数类型检查
- 参数范围验证
- 恶意代码检测

### 速率限制

支持请求速率限制：

```php
$config = new ServerConfig([
    'security' => [
        'rate_limit' => 100,    // 每分钟最大请求数
        'rate_window' => 60,    // 时间窗口（秒）
    ]
]);
```

### 错误处理

- 详细的错误信息
- 安全的错误响应
- 错误日志记录

## 性能优化

### 连接池

- 连接复用
- 连接限制
- 连接监控

### 缓存

- 工具结果缓存
- 资源内容缓存
- 会话数据缓存

### 异步处理

- 异步工具调用
- 异步资源读取
- 异步日志记录

## 监控和日志

### 日志级别

- `debug`: 调试信息
- `info`: 一般信息
- `warning`: 警告信息
- `error`: 错误信息

### 日志格式

```json
{
  "timestamp": "2024-01-01T00:00:00Z",
  "level": "info",
  "message": "Tool called",
  "context": {
    "tool": "calculate",
    "arguments": {"expression": "2+2"},
    "duration": 0.001
  }
}
```

### 监控指标

- 请求数量
- 响应时间
- 错误率
- 连接数
- 内存使用

## 示例

### 完整的工具调用流程

1. **初始化连接**

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "initialize",
  "params": {
    "protocolVersion": "2024-11-05",
    "capabilities": {},
    "clientInfo": {
      "name": "test-client",
      "version": "1.0.0"
    }
  }
}
```

2. **获取工具列表**

```json
{
  "jsonrpc": "2.0",
  "id": 2,
  "method": "tools/list",
  "params": {}
}
```

3. **调用工具**

```json
{
  "jsonrpc": "2.0",
  "id": 3,
  "method": "tools/call",
  "params": {
    "name": "calculate",
    "arguments": {
      "expression": "10 + 20"
    }
  }
}
```

4. **处理响应**

```json
{
  "jsonrpc": "2.0",
  "id": 3,
  "result": {
    "content": [
      {
        "type": "text",
        "text": "{\"success\":true,\"result\":30,\"expression\":\"10 + 20\",\"timestamp\":1704067200}"
      }
    ]
  }
}
```

## 更多信息

- 查看 [快速开始](quickstart.md) 了解基本使用方法
- 查看 [安装指南](installation.md) 了解安装和配置
- 查看 [示例代码](../examples/) 了解更多使用场景
- 查看 [最佳实践](best-practices.md) 了解开发建议
