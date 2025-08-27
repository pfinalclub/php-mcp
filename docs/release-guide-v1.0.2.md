# PFPMcp v1.0.2 发布指南

## 发布概述

PFPMcp v1.0.2 是一个重要的兼容性修复版本，主要解决了 WebSocket 连接管理、消息路由和 JSON Schema 格式兼容性问题，提升了与 Cursor IDE 等 MCP 客户端的集成体验。

## 版本信息

- **版本号**: 1.0.2
- **发布日期**: 2025-01-27
- **类型**: 兼容性修复 (Compatibility Fix)
- **兼容性**: 向后兼容 v1.0.1 和 v1.0.0

## 主要修复内容

### 🔌 WebSocket 连接管理优化

#### 问题修复
- **连接状态跟踪**: 添加 `currentConnection` 属性跟踪活跃连接
- **事件处理器完善**: 新增 `onConnect`、`onClose`、`onError` 事件处理器
- **消息路由改进**: 确保消息正确传递到服务器事件系统

#### 技术改进
```php
// WebSocketTransport 新增属性
private $connectHandler = null;
private $closeHandler = null;
private $errorHandler = null;
private ?TcpConnection $currentConnection = null;

// 完善的事件处理
$this->worker->onConnect = function (TcpConnection $connection) {
    $this->currentConnection = $connection;
    if ($this->connectHandler !== null) {
        call_user_func($this->connectHandler, $connection);
    }
};
```

### 🛠️ 传输协议接口统一

#### 接口扩展
- **TransportInterface 更新**: 新增连接生命周期事件方法
- **统一实现**: 所有传输协议实现统一的事件处理接口
- **向后兼容**: 保持原有 API 不变，新增功能可选

#### 新增方法
```php
interface TransportInterface
{
    // 原有方法保持不变
    public function start(): void;
    public function stop(): void;
    public function send(string $message): void;
    public function onMessage(callable $handler): void;
    
    // 新增方法
    public function onConnect(callable $handler): void;
    public function onClose(callable $handler): void;
    public function onError(callable $handler): void;
}
```

### 📡 服务器事件路由增强

#### 新增功能
- **setupTransportEventHandlers() 方法**: 绑定传输协议事件到服务器事件系统
- **事件路由机制**: 确保传输协议事件正确传递到服务器事件处理器
- **统一事件处理**: 提供一致的事件处理接口

#### 实现细节
```php
private function setupTransportEventHandlers(): void
{
    // 设置连接处理器
    $this->transport->onConnect(function (TcpConnection $connection) {
        $this->eventHandler->emit('connect', $connection);
    });
    
    // 设置消息处理器
    $this->transport->onMessage(function (TcpConnection $connection, $data) {
        $this->eventHandler->emit('message', $connection, $data);
    });
    
    // 设置关闭处理器
    $this->transport->onClose(function (TcpConnection $connection) {
        $this->eventHandler->emit('close', $connection);
    });
    
    // 设置错误处理器
    $this->transport->onError(function (TcpConnection $connection, $error) {
        $this->eventHandler->emit('error', $connection, $error);
    });
}
```

### 📊 JSON Schema 格式兼容性

#### 问题修复
- **空参数工具**: 修复空参数工具返回空数组 `[]` 的问题
- **Cursor 兼容**: 确保格式符合 Cursor IDE 的要求
- **MCP 协议合规**: 符合 MCP 协议规范

#### 修复实现
```php
public function listTools(): array
{
    $tools = [];
    
    foreach ($this->tools as $name => $tool) {
        $properties = $this->buildProperties($tool['parameters']);
        
        // 确保空数组在 JSON 序列化时变成空对象 {}
        $inputSchema = [
            'type' => 'object',
            'properties' => empty($properties) ? (object) [] : $properties,
            'required' => $this->getRequiredProperties($tool['parameters'])
        ];
        
        $tools[] = [
            'name' => $name,
            'description' => $tool['description'],
            'inputSchema' => $inputSchema
        ];
    }
    
    return $tools;
}
```

## 发布步骤

### 1. 代码提交
```bash
# 添加所有更改
git add -A

# 提交更改
git commit -m "fix: release v1.0.2 - WebSocket connection management and compatibility fixes"
```

### 2. 创建标签
```bash
# 创建带注释的标签
git tag -a v1.0.2 -m "Release v1.0.2

🔌 使用改进和兼容性修复
- WebSocket 连接管理优化
- 传输协议接口统一
- 服务器事件路由增强
- JSON Schema 格式兼容性

🛠️ 核心功能改进
- WebSocketTransport 增强
- TransportInterface 扩展
- Server 类优化
- ToolManager 修复

📊 兼容性提升
- Cursor IDE 集成修复
- MCP 协议合规
- WebSocket 稳定性提升
- 事件系统完善

🔄 向后兼容
- 保持原有 API 接口不变
- 保持原有配置格式兼容
- 保持原有工具定义方式
- 新增功能不影响现有代码"
```

### 3. 推送更改
```bash
# 推送提交
git push origin master

# 推送标签
git push origin v1.0.2
```

### 4. 生成发布包
```bash
# 创建 dist 目录
mkdir -p dist

# 生成发布包
git archive --format=zip --output=dist/pfinalclub-php-mcp-1.0.2.zip v1.0.2
```

## GitHub Release 创建

### 1. 访问 GitHub
- 前往 https://github.com/pfinalclub/php-mcp/releases
- 点击 "Create a new release"

### 2. 填写发布信息
- **Tag version**: v1.0.2
- **Release title**: PFPMcp v1.0.2 - WebSocket Connection Management & Compatibility Fixes
- **Description**: 使用上面标签消息中的内容

### 3. 上传文件
- 上传 `dist/pfinalclub-php-mcp-1.0.2.zip` 发布包
- 标记为 "Latest release"

### 4. 发布
- 点击 "Publish release"

## Packagist 发布

### 1. 自动发布
如果已配置 GitHub Webhook，Packagist 会自动检测新标签并发布。

### 2. 手动发布
- 访问 https://packagist.org/packages/pfinalclub/php-mcp
- 点击 "Update Package" 按钮

## 验证发布

### 1. 安装测试
```bash
# 测试新版本安装
composer create-project pfinalclub/php-mcp test-install 1.0.2

# 验证版本
cd test-install
composer show pfinalclub/php-mcp
```

### 2. 功能测试
```bash
# 测试 WebSocket 连接
php examples/websocket-server.php

# 测试工具调用
php examples/tool-test.php
```

### 3. 兼容性测试
```bash
# 测试 Cursor IDE 集成
# 验证 JSON Schema 格式
# 测试 WebSocket 连接稳定性
```

## 发布后检查清单

- [ ] Git 标签已创建并推送
- [ ] GitHub Release 已发布
- [ ] Packagist 包已更新
- [ ] 文档链接已更新
- [ ] WebSocket 连接测试通过
- [ ] Cursor IDE 集成测试通过
- [ ] JSON Schema 格式验证通过
- [ ] 向后兼容性验证通过

## 回滚计划

如果发布后发现问题，可以：

1. **删除标签**
```bash
git tag -d v1.0.2
git push origin :refs/tags/v1.0.2
```

2. **创建修复版本**
```bash
# 修复问题后创建 v1.0.3
git tag -a v1.0.3 -m "Release v1.0.3 - Additional fixes"
git push origin v1.0.3
```

## 联系信息

如有问题，请联系：
- **邮箱**: lampxiezi@gmail.com
- **GitHub**: https://github.com/pfinalclub/php-mcp/issues
- **文档**: https://github.com/pfinalclub/php-mcp/blob/main/README.md

---

**注意**: 请确保在发布前已完成所有测试和验证，确保代码质量和功能完整性。
