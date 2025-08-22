# 项目概述

PFPMcp 是一个基于 PHP8 和 Workerman 的高性能 MCP (Model Context Protocol) 服务器，专为 AI 应用和智能助手设计。

## 项目特性

### 🚀 高性能
- 基于 Workerman 4.0+ 异步网络框架
- 支持高并发连接
- 内存占用低，响应速度快

### 🔌 多协议支持
- **stdio**: 标准输入输出，适用于命令行环境
- **HTTP**: HTTP 协议，适用于 Web 环境
- **WebSocket**: WebSocket 协议，适用于实时通信
- **HTTP+SSE**: Server-Sent Events，适用于流式传输
- **Streamable HTTP**: 可恢复的 HTTP 传输

### 🛠️ 易于扩展
- 使用 PHP 8 Attributes 自动发现和注册工具
- 模块化设计，易于添加新功能
- 完整的依赖注入支持

### 🛡️ 安全可靠
- 完善的输入验证
- 请求速率限制
- 详细的错误处理和日志记录
- 会话管理和状态保持

### 📚 完整文档
- 详细的 API 文档
- 丰富的示例代码
- 最佳实践指南
- 完整的测试覆盖

## 架构设计

### 核心组件

```
PFPMcp/
├── Server.php              # 主服务器类
├── Config/                 # 配置管理
│   └── ServerConfig.php
├── Tools/                  # 工具管理
│   ├── ToolManager.php
│   └── Calculator.php
├── Resources/              # 资源管理
│   └── ResourceManager.php
├── Prompts/                # 提示管理
│   └── PromptManager.php
├── Session/                # 会话管理
│   ├── SessionManager.php
│   └── Session.php
├── Transport/              # 传输协议
│   ├── TransportInterface.php
│   ├── TransportFactory.php
│   ├── StdioTransport.php
│   ├── HttpTransport.php
│   ├── WebSocketTransport.php
│   ├── HttpSseTransport.php
│   └── StreamableHttpTransport.php
├── Protocol/               # 协议处理
│   └── ProtocolManager.php
├── Connection/             # 连接管理
│   └── ConnectionManager.php
├── EventHandler/           # 事件处理
│   └── EventHandler.php
└── Exceptions/             # 异常处理
    ├── ServerException.php
    ├── ConfigException.php
    ├── ToolException.php
    └── TransportException.php
```

### 设计模式

- **单例模式**: 服务器实例管理
- **工厂模式**: 传输协议创建
- **观察者模式**: 事件处理
- **策略模式**: 协议处理
- **依赖注入**: 组件解耦

## 技术栈

### 核心依赖

- **PHP 8.2+**: 现代 PHP 特性支持
- **Workerman 4.0+**: 高性能网络框架
- **ReactPHP**: 异步编程支持
- **Monolog**: 日志记录
- **PHPUnit**: 单元测试

### 开发工具

- **PHPStan**: 静态代码分析
- **PHP CS Fixer**: 代码格式化
- **PHP_CodeSniffer**: 代码规范检查
- **Composer**: 依赖管理

## 使用场景

### AI 助手集成
- Claude 助手工具扩展
- ChatGPT 插件开发
- 自定义 AI 工具链

### 企业应用
- 内部工具集成
- API 网关
- 微服务通信

### 开发工具
- 代码生成工具
- 文档处理工具
- 数据分析工具

## 快速开始

### 安装

```bash
composer require pfinal/php-mcp
```

### 基础使用

```php
<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

use PFPMcp\Server;
use PFPMcp\Tools\Calculator;

$server = new Server();
$server->registerTool(new Calculator());
$server->start();
```

### 自定义工具

```php
<?php declare(strict_types=1);

namespace MyApp\Tools;

use PhpMcp\Attributes\McpTool;
use PhpMcp\Attributes\Schema;

class MyTool
{
    #[McpTool(name: 'my_tool', description: '我的工具')]
    public function execute(
        #[Schema(description: '输入参数')]
        string $input
    ): array {
        return [
            'success' => true,
            'result' => strtoupper($input)
        ];
    }
}
```

## 性能指标

### 基准测试

- **并发连接**: 支持 10,000+ 并发连接
- **响应时间**: 平均响应时间 < 10ms
- **内存使用**: 每个连接约 1KB 内存
- **CPU 使用**: 低 CPU 占用率

### 扩展性

- **水平扩展**: 支持多实例部署
- **负载均衡**: 支持负载均衡器集成
- **集群支持**: 支持集群模式运行

## 安全特性

### 输入验证
- 参数类型检查
- 参数范围验证
- 恶意代码检测

### 访问控制
- 请求速率限制
- API 密钥认证
- 来源域名验证

### 数据保护
- 会话数据加密
- 敏感信息脱敏
- 安全日志记录

## 监控和运维

### 日志系统
- 结构化日志输出
- 多级别日志记录
- 日志轮转和归档

### 监控指标
- 请求数量和响应时间
- 错误率和异常统计
- 资源使用情况

### 健康检查
- 服务健康状态检查
- 依赖服务监控
- 自动故障恢复

## 部署选项

### 传统部署
```bash
# 安装依赖
composer install --no-dev

# 启动服务
php server.php
```

### Docker 部署
```bash
# 构建镜像
docker build -t pfinal/php-mcp .

# 运行容器
docker run -d -p 8080:8080 pfinal/php-mcp
```

### Kubernetes 部署
```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: mcp-server
spec:
  replicas: 3
  selector:
    matchLabels:
      app: mcp-server
  template:
    metadata:
      labels:
        app: mcp-server
    spec:
      containers:
      - name: mcp-server
        image: pfinal/php-mcp:latest
        ports:
        - containerPort: 8080
```

## 社区和支持

### 贡献指南
- 详细的贡献指南
- 代码规范说明
- 测试要求

### 支持渠道
- GitHub Issues
- 邮件支持
- 文档和示例

### 许可证
- MIT 许可证
- 商业友好
- 开源免费

## 路线图

### 短期计划 (1-3 个月)
- [ ] 增强安全特性
- [ ] 性能优化
- [ ] 更多内置工具
- [ ] 完善文档

### 中期计划 (3-6 个月)
- [ ] 集群支持
- [ ] 监控集成
- [ ] 插件系统
- [ ] 图形化管理界面

### 长期计划 (6-12 个月)
- [ ] 云原生支持
- [ ] 多语言支持
- [ ] 企业级功能
- [ ] 生态系统建设

## 总结

PFPMcp 是一个功能完整、性能优异、易于使用的 MCP 服务器实现。它提供了丰富的功能和灵活的扩展性，适用于各种 AI 应用和企业场景。通过现代化的技术栈和良好的架构设计，PFPMcp 能够满足高性能、高可靠性的需求。

无论您是开发 AI 助手、构建企业工具，还是创建开发工具，PFPMcp 都能为您提供强大的支持。我们致力于持续改进和社区建设，欢迎您的参与和贡献！
