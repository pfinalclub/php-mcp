# 编辑器集成指南

本文档详细说明如何在 Cursor、VS Code 等编辑器中配置使用 PFPMcp 开发的 MCP 服务器。

## 目录

- [Cursor 集成](#cursor-集成)
- [VS Code 集成](#vs-code-集成)
- [其他编辑器](#其他编辑器)
- [常见问题](#常见问题)

## Cursor 集成

### 1. 基本配置

在 Cursor 中配置 MCP 服务器，需要编辑 `~/.cursor/settings.json` 文件：

```json
{
  "mcpServers": {
    "pfinal-mcp": {
      "command": "php",
      "args": ["/path/to/your/mcp-server/server.php"],
      "env": {
        "MCP_TRANSPORT": "stdio",
        "MCP_LOG_LEVEL": "info"
      }
    }
  }
}
```

### 2. 使用示例项目

#### 基础计算器服务器

```json
{
  "mcpServers": {
    "calculator-mcp": {
      "command": "php",
      "args": ["/path/to/pfinal-php-mcp/examples/01-basic-usage/server.php"],
      "env": {
        "MCP_TRANSPORT": "stdio",
        "MCP_LOG_LEVEL": "debug"
      }
    }
  }
}
```

#### 自定义工具服务器

```json
{
  "mcpServers": {
    "custom-tools-mcp": {
      "command": "php",
      "args": ["/path/to/pfinal-php-mcp/examples/02-custom-tools/server.php"],
      "env": {
        "MCP_TRANSPORT": "stdio",
        "MCP_LOG_LEVEL": "info"
      }
    }
  }
}
```

### 3. 高级配置

#### 多服务器配置

```json
{
  "mcpServers": {
    "calculator": {
      "command": "php",
      "args": ["/path/to/calculator-server/server.php"],
      "env": {
        "MCP_TRANSPORT": "stdio"
      }
    },
    "file-manager": {
      "command": "php",
      "args": ["/path/to/file-manager-server/server.php"],
      "env": {
        "MCP_TRANSPORT": "stdio"
      }
    },
    "database-tools": {
      "command": "php",
      "args": ["/path/to/database-server/server.php"],
      "env": {
        "MCP_TRANSPORT": "stdio"
      }
    }
  }
}
```

#### 带配置文件的服务器

```json
{
  "mcpServers": {
    "configurable-mcp": {
      "command": "php",
      "args": ["/path/to/server.php", "--config=/path/to/config.php"],
      "env": {
        "MCP_TRANSPORT": "stdio",
        "MCP_LOG_LEVEL": "info"
      }
    }
  }
}
```

## VS Code 集成

### 1. 安装 MCP 扩展

首先需要安装 MCP 相关的 VS Code 扩展：

1. 打开 VS Code
2. 按 `Ctrl+Shift+X` 打开扩展面板
3. 搜索 "MCP" 或 "Model Context Protocol"
4. 安装相关扩展

### 2. 配置 MCP 服务器

在 VS Code 中，编辑 `settings.json` 文件：

```json
{
  "mcp.servers": {
    "pfinal-mcp": {
      "command": "php",
      "args": ["/path/to/your/mcp-server/server.php"],
      "env": {
        "MCP_TRANSPORT": "stdio"
      }
    }
  }
}
```

### 3. 工作区配置

在项目根目录创建 `.vscode/settings.json`：

```json
{
  "mcp.servers": {
    "project-mcp": {
      "command": "php",
      "args": ["./server.php"],
      "env": {
        "MCP_TRANSPORT": "stdio",
        "MCP_LOG_LEVEL": "debug"
      }
    }
  }
}
```

## 其他编辑器

### 1. Neovim 配置

在 `init.lua` 或相关配置文件中：

```lua
-- 配置 MCP 服务器
vim.g.mcp_servers = {
  pfinal_mcp = {
    command = "php",
    args = { "/path/to/your/mcp-server/server.php" },
    env = {
      MCP_TRANSPORT = "stdio",
      MCP_LOG_LEVEL = "info"
    }
  }
}
```

### 2. Sublime Text 配置

在用户设置中添加：

```json
{
  "mcp_servers": {
    "pfinal-mcp": {
      "command": "php",
      "args": ["/path/to/your/mcp-server/server.php"],
      "env": {
        "MCP_TRANSPORT": "stdio"
      }
    }
  }
}
```

## 配置文件示例

### Cursor 配置示例

项目提供了 Cursor 配置示例文件：`examples/cursor-config.json`

```json
{
  "mcpServers": {
    "pfinal-calculator": {
      "command": "php",
      "args": ["/path/to/pfinal-php-mcp/examples/01-basic-usage/server.php"],
      "env": {
        "MCP_TRANSPORT": "stdio",
        "MCP_LOG_LEVEL": "info"
      }
    },
    "pfinal-custom-tools": {
      "command": "php",
      "args": ["/path/to/pfinal-php-mcp/examples/02-custom-tools/server.php"],
      "env": {
        "MCP_TRANSPORT": "stdio",
        "MCP_LOG_LEVEL": "debug"
      }
    }
  }
}
```

### VS Code 配置示例

项目提供了 VS Code 配置示例文件：`examples/vscode-config.json`

```json
{
  "mcp.servers": {
    "pfinal-calculator": {
      "command": "php",
      "args": ["/path/to/pfinal-php-mcp/examples/01-basic-usage/server.php"],
      "env": {
        "MCP_TRANSPORT": "stdio",
        "MCP_LOG_LEVEL": "info"
      }
    }
  }
}
```

## 实际示例

假设您正在开发一个基于 PFPMcp 的 MCP 服务器：

```bash
# 项目结构
my-mcp-server/
├── server.php
├── src/
│   ├── Tools/
│   │   └── MyTools.php
│   └── Config/
│       └── config.php
└── composer.json
```

**Cursor 配置** (`~/.cursor/settings.json`):

```json
{
  "mcpServers": {
    "my-dev-server": {
      "command": "php",
      "args": ["/path/to/my-mcp-server/server.php"],
      "env": {
        "MCP_TRANSPORT": "stdio",
        "MCP_LOG_LEVEL": "debug",
        "MCP_DEBUG": "true"
      }
    }
  }
}
```

### 示例 2: 生产环境配置

对于生产环境的 MCP 服务器：

```json
{
  "mcpServers": {
    "production-mcp": {
      "command": "php",
      "args": ["/opt/mcp-server/server.php"],
      "env": {
        "MCP_TRANSPORT": "stdio",
        "MCP_LOG_LEVEL": "error",
        "MCP_SESSION_BACKEND": "redis",
        "MCP_RATE_LIMIT": "1000"
      }
    }
  }
}
```

### 示例 3: Docker 环境配置

如果您的 MCP 服务器运行在 Docker 中：

```json
{
  "mcpServers": {
    "docker-mcp": {
      "command": "docker",
      "args": ["run", "--rm", "-i", "my-mcp-server:latest"],
      "env": {
        "MCP_TRANSPORT": "stdio"
      }
    }
  }
}
```

## 验证配置

### 1. 检查服务器状态

在编辑器中，您可以通过以下方式验证 MCP 服务器是否正常工作：

1. **Cursor**: 在聊天窗口中输入 "请使用计算器工具计算 2+2"
2. **VS Code**: 查看 MCP 扩展的状态栏
3. **检查日志**: 查看服务器输出的日志信息

### 2. 测试工具调用

项目提供了完整的测试脚本 `examples/test-mcp.php` 来验证 MCP 服务器：

#### 运行所有测试

```bash
# 运行所有测试
php examples/test-mcp.php | php server.php

# 交互式测试
php examples/test-mcp.php --interactive | php server.php

# 运行特定测试
php examples/test-mcp.php --test 3 | php server.php
```

#### 交互式测试示例

```bash
$ php examples/test-mcp.php --interactive | php server.php
=== MCP 服务器交互式测试 ===
输入 'quit' 退出，'help' 显示帮助

MCP> help
可用命令:
  tools/list     - 列出所有工具
  tools/call     - 调用工具 (格式: tools/call tool_name param1=value1 param2=value2)
  resources/list - 列出所有资源
  prompts/list   - 列出所有提示
  quit/exit      - 退出
  help           - 显示帮助

MCP> tools/list
{"jsonrpc":"2.0","id":1703123456,"method":"tools/list","params":[]}

MCP> tools/call add a=10 b=20
{"jsonrpc":"2.0","id":1703123457,"method":"tools/call","params":{"name":"add","arguments":{"a":"10","b":"20"}}}

MCP> quit
```

#### 简单测试

```bash
# 测试工具列表
echo '{"jsonrpc":"2.0","id":1,"method":"tools/list","params":[]}' | php server.php

# 测试计算器工具
echo '{"jsonrpc":"2.0","id":2,"method":"tools/call","params":{"name":"add","arguments":{"a":10,"b":20}}}' | php server.php
```

## 常见问题

### 1. 服务器无法启动

**问题**: MCP 服务器无法启动或连接失败

**解决方案**:
- 检查 PHP 路径是否正确
- 确认服务器文件存在且有执行权限
- 检查环境变量配置
- 查看错误日志

```bash
# 手动测试服务器
php /path/to/server.php --test
```

### 2. 工具调用失败

**问题**: 工具注册成功但调用失败

**解决方案**:
- 检查工具方法的参数验证
- 确认工具返回格式正确
- 查看服务器日志

### 3. 性能问题

**问题**: MCP 服务器响应缓慢

**解决方案**:
- 优化工具实现
- 使用缓存机制
- 调整服务器配置

```json
{
  "mcpServers": {
    "optimized-mcp": {
      "command": "php",
      "args": ["/path/to/server.php"],
      "env": {
        "MCP_TRANSPORT": "stdio",
        "MCP_MAX_CONNECTIONS": "100",
        "MCP_TIMEOUT": "30"
      }
    }
  }
}
```

### 4. 权限问题

**问题**: 编辑器无法访问 MCP 服务器

**解决方案**:
- 检查文件权限
- 确认路径正确
- 使用绝对路径

```bash
# 设置正确的权限
chmod +x /path/to/server.php
```

## 最佳实践

### 1. 路径配置

- 使用绝对路径避免路径问题
- 在配置文件中使用环境变量
- 为不同环境创建不同的配置文件

### 2. 日志管理

- 在开发环境启用详细日志
- 在生产环境使用适当的日志级别
- 定期清理日志文件

### 3. 错误处理

- 实现完善的错误处理机制
- 提供有意义的错误信息
- 记录详细的错误日志

### 4. 性能优化

- 使用连接池管理连接
- 实现缓存机制
- 优化工具实现

## 调试技巧

### 1. 启用调试模式

```json
{
  "mcpServers": {
    "debug-mcp": {
      "command": "php",
      "args": ["/path/to/server.php"],
      "env": {
        "MCP_TRANSPORT": "stdio",
        "MCP_LOG_LEVEL": "debug",
        "MCP_DEBUG": "true"
      }
    }
  }
}
```

### 2. 查看详细日志

```bash
# 直接运行服务器查看输出
php /path/to/server.php 2>&1 | tee server.log
```

### 3. 使用测试工具

```bash
# 使用提供的测试脚本
php examples/test-mcp.php | php server.php
```

## 总结

通过以上配置，您可以在 Cursor、VS Code 等编辑器中成功集成使用 PFPMcp 开发的 MCP 服务器。记住要根据您的具体需求和环境调整配置参数，并定期检查和优化服务器性能。

### 快速开始步骤

1. **安装 PFPMcp**: `composer require pfinal/php-mcp`
2. **复制配置文件**: 参考 `examples/cursor-config.json` 或 `examples/vscode-config.json`
3. **修改路径**: 将配置文件中的路径替换为您的实际路径
4. **测试连接**: 使用 `examples/test-mcp.php` 验证服务器功能
5. **重启编辑器**: 重启 Cursor 或 VS Code 使配置生效

### 验证集成

在编辑器中，您可以尝试以下操作来验证 MCP 服务器是否正常工作：

- **Cursor**: 在聊天窗口中输入 "请使用计算器工具计算 2+2"
- **VS Code**: 查看 MCP 扩展的状态栏和输出面板
- **命令行**: 使用测试脚本验证功能

如果您在使用过程中遇到问题，请参考：
- [安装指南](installation.md) - 详细的安装和配置说明
- [API 文档](api.md) - 完整的 API 接口文档
- [快速开始](quickstart.md) - 快速上手指南
- [示例代码](../examples/) - 使用示例和最佳实践
- [项目概述](project-overview.md) - 项目特性和架构设计
