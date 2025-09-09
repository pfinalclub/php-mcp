# PFPMcp - PHP8 + Workerman MCP Server

[![PHP Version](https://img.shields.io/badge/php-8.2+-blue.svg)](https://php.net)
[![Workerman Version](https://img.shields.io/badge/workerman-4.0+-green.svg)](https://www.workerman.net/)
[![License](https://img.shields.io/badge/license-MIT-yellow.svg)](LICENSE)
[![Version](https://img.shields.io/badge/version-1.0.3-blue.svg)](https://github.com/pfinalclub/php-mcp/releases)
[![Tests](https://img.shields.io/badge/tests-passing-brightgreen.svg)](https://github.com/pfinalclub/php-mcp/actions)

一个基于 PHP8 和 Workerman 的高性能 MCP (Model Context Protocol) 服务器，提供稳定可靠的网络通信服务。

## ✨ 特性

- 🚀 基于 PHP8.2+ 和 Workerman 4.0+ 的纯 Workerman 实现
- 🔌 支持多种传输协议：stdio、HTTP、HTTP+SSE、WebSocket、Streamable HTTP
- 📡 事件驱动的架构设计，支持非阻塞 I/O
- 🛠️ 自动 MCP 元素发现和注册
- 🛡️ 完善的错误处理和日志记录
- 🧪 完整的测试覆盖
- 📚 详细的文档和示例
- 🔧 支持自定义工具、资源和提示
- 🎯 高性能并发处理
- 🔥 零外部 MCP 依赖，完全自主实现
- ⚡ 优化的 stdio 传输协议，支持智能模式选择
- 🔌 完整的 WebSocket 连接管理和事件路由

## 📦 安装

```bash
composer require pfinalclub/php-mcp
```

## 🚀 快速开始

### 基础使用

```php
<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

use PFPMcp\Server\Server;
use PFPMcp\Tools\Calculator;

// 创建服务器实例
$server = new Server();

// 注册工具
$server->registerTool(new Calculator());

// 启动服务器
$server->start();
```

### 自定义工具

```php
<?php declare(strict_types=1);

namespace PFPMcp\Tools;

use PhpMcp\Attributes\McpTool;
use PhpMcp\Attributes\Schema;

class CustomTool
{
    #[McpTool(
        name: 'custom_action',
        description: '执行自定义操作'
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

### 配置服务器

```php
<?php declare(strict_types=1);

use PFPMcp\Server\Server;
use PFPMcp\Config\ServerConfig;

$config = new ServerConfig([
    'transport' => 'http',
    'host' => '0.0.0.0',
    'port' => 8080,
    'log_level' => 'info',
    'max_connections' => 1000
]);

$server = new Server($config);
$server->start();
```

## 📖 文档

详细文档请查看 [docs/](docs/) 目录：

- [快速开始](docs/quickstart.md) - 快速上手指南
- [安装指南](docs/installation.md) - 详细的安装和配置说明
- [API 文档](docs/api.md) - 完整的 API 接口文档
- [编辑器集成](docs/editor-integration.md) - 在 Cursor、VS Code 等编辑器中配置 MCP 服务器
- [项目概述](docs/project-overview.md) - 项目特性和架构设计
- [示例代码](examples/) - 使用示例和最佳实践

## 🧪 测试

运行测试套件：

```bash
# 运行所有测试
composer test

# 生成测试覆盖率报告
composer test-coverage

# 运行代码质量检查
composer all
```

## 📁 项目结构

```
pfinal-php-mcp/
├── src/                    # 源代码目录
│   ├── Server.php         # 主服务器类
│   ├── Connection.php     # 连接处理类
│   ├── Protocol.php       # 协议解析类
│   ├── EventHandler.php   # 事件处理类
│   ├── Tools/             # MCP Tools 实现
│   ├── Resources/         # MCP Resources 实现
│   ├── Prompts/           # MCP Prompts 实现
│   ├── Transport/         # 传输协议实现
│   ├── Session/           # 会话管理
│   └── Config/            # 配置管理
├── tests/                 # 测试代码目录
├── examples/              # 示例代码目录
├── docs/                  # 文档目录
├── docker/                # Docker 配置文件
├── composer.json          # Composer 配置
├── phpunit.xml           # PHPUnit 配置
├── .php-cs-fixer.php     # PHP CS Fixer 配置
├── server.php            # 服务器入口文件
└── README.md             # 项目说明文档
```

## 🔧 配置

### 环境变量

```bash
# 传输协议配置
MCP_TRANSPORT=stdio          # stdio, http, ws
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

```php
<?php declare(strict_types=1);

return [
    'transport' => $_ENV['MCP_TRANSPORT'] ?? 'stdio',
    'host' => $_ENV['MCP_HOST'] ?? '0.0.0.0',
    'port' => (int)($_ENV['MCP_PORT'] ?? 8080),
    'log_level' => $_ENV['MCP_LOG_LEVEL'] ?? 'info',
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

## 🐳 Docker 部署

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

## 👥 社区

### 参与方式

- **GitHub Issues**: [报告 Bug 和功能请求](https://github.com/pfinalclub/php-mcp/issues)
- **GitHub Discussions**: [技术讨论和问题咨询](https://github.com/pfinalclub/php-mcp/discussions)
- **贡献指南**: [查看如何贡献代码](CONTRIBUTING.md)
- **社区指南**: [了解社区文化和行为准则](docs/community-guidelines.md)

### 社区活动

- **月度技术分享**: 每月最后一个周五
- **代码审查会议**: 每周三
- **新功能讨论**: 功能发布前
- **年度贡献者大会**: 每年 10 月

### 贡献者等级

- 🌟 **新手贡献者**: 文档改进、简单 Bug 修复
- 🚀 **活跃贡献者**: 功能开发、代码审查
- 💎 **核心贡献者**: 架构设计、重要功能
- 👑 **维护者**: 版本发布、社区管理

## 🤝 贡献

欢迎提交 Issue 和 Pull Request！

### 开发环境设置

```bash
# 克隆仓库
git clone https://github.com/pfinalclub/php-mcp.git
cd php-mcp

# 安装依赖
composer install

# 运行测试
composer test

# 代码格式化
composer fix
```

### 代码规范

- 遵循 PSR-12 编码规范
- 使用 PHP 8.2+ 特性
- 编写完整的测试用例
- 添加详细的文档注释

### 贡献流程

1. Fork 项目
2. 创建功能分支
3. 提交更改
4. 创建 Pull Request
5. 等待代码审查

## 📄 许可证

本项目采用 MIT 许可证 - 查看 [LICENSE](LICENSE) 文件了解详情。

## 🔗 相关链接

- [Model Context Protocol](https://modelcontextprotocol.io/)
- [Workerman](https://www.workerman.net/)
- [PHP MCP Server](https://github.com/php-mcp/server)
- [Claude](https://claude.ai/)
- [ChatGPT](https://chat.openai.com/)

## 📞 支持

如果您遇到问题或有建议，请：

1. 查看 [文档](docs/)
2. 搜索 [Issues](https://github.com/pfinalclub/php-mcp/issues)
3. 创建新的 [Issue](https://github.com/pfinalclub/php-mcp/issues/new)
4. 参与 [Discussions](https://github.com/pfinalclub/php-mcp/discussions)
5. 联系维护者: lampxiezi@gmail.com

---

**PFPMcp** - 让 MCP 服务器开发更简单！ 🚀
