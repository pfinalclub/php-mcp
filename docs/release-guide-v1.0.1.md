# PFPMcp v1.0.1 发布指南

## 发布概述

PFPMcp v1.0.1 是一个重要的功能更新版本，主要包含 stdio 传输协议的全面优化和代码质量提升。

## 版本信息

- **版本号**: 1.0.1
- **发布日期**: 2025-01-27
- **类型**: 功能更新 (Feature Release)
- **兼容性**: 向后兼容 v1.0.0

## 主要更新内容

### 🚀 重大优化
- **Stdio 传输协议全面优化**：利用 Workerman 的常驻进程和事件驱动优势
- **非阻塞 I/O 处理**：使用 `stream_set_blocking()` 和 `stream_select()` 实现非阻塞输入
- **事件驱动架构**：利用 Workerman Timer 进行定时缓冲区处理
- **智能模式选择**：支持 `auto`、`optimized`、`blocking` 三种模式

### 🔧 核心改进
- **新增 OptimizedStdioTransport**：优化的非阻塞实现，支持缓冲区管理和优雅关闭
- **新增 LegacyStdioTransport**：传统阻塞式实现，保持向后兼容
- **重构 StdioTransport**：智能工厂类，自动选择最优实现
- **配置系统增强**：添加 stdio 专用配置选项和环境变量支持

### 📊 性能提升
- **响应性提升**：非阻塞 I/O 避免主线程阻塞
- **资源利用优化**：更好的 CPU 和内存使用效率
- **稳定性增强**：完善的错误恢复和优雅关闭机制
- **可观测性**：提供传输协议状态信息和性能监控

## 发布步骤

### 1. 代码提交
```bash
# 添加所有更改
git add -A

# 提交更改
git commit -m "feat: release v1.0.1 - stdio transport optimization and code cleanup"
```

### 2. 创建标签
```bash
# 创建带注释的标签
git tag -a v1.0.1 -m "Release v1.0.1

🚀 重大优化
- Stdio 传输协议全面优化
- 非阻塞 I/O 处理
- 事件驱动架构
- 智能模式选择

🔧 核心改进
- 新增 OptimizedStdioTransport
- 新增 LegacyStdioTransport
- 重构 StdioTransport 工厂类
- 配置系统增强

📊 性能提升
- 响应性提升
- 资源利用优化
- 稳定性增强
- 可观测性

🛠️ 代码质量
- PHP 8.2+ 兼容性修复
- 配置验证优化
- 传输协议修复
- 注释完善

📚 文档更新
- 新增优化文档
- 更新项目状态
- 代码整理总结

🔄 向后兼容
- 保持 API 接口不变
- 默认配置兼容
- 渐进式升级支持"
```

### 3. 推送更改
```bash
# 推送提交
git push origin master

# 推送标签
git push origin v1.0.1
```

### 4. 生成发布包
```bash
# 创建 dist 目录
mkdir -p dist

# 生成发布包
git archive --format=zip --output=dist/pfinalclub-php-mcp-1.0.1.zip v1.0.1
```

## GitHub Release 创建

### 1. 访问 GitHub
- 前往 https://github.com/pfinalclub/php-mcp/releases
- 点击 "Create a new release"

### 2. 填写发布信息
- **Tag version**: v1.0.1
- **Release title**: PFPMcp v1.0.1 - Stdio Transport Optimization
- **Description**: 使用上面标签消息中的内容

### 3. 上传文件
- 上传 `dist/pfinalclub-php-mcp-1.0.1.zip` 发布包
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
composer create-project pfinalclub/php-mcp test-install 1.0.1

# 验证版本
cd test-install
composer show pfinalclub/php-mcp
```

### 2. 功能测试
```bash
# 运行示例
php examples/05-stdio-optimization/server.php
```

## 发布后检查清单

- [ ] Git 标签已创建并推送
- [ ] GitHub Release 已发布
- [ ] Packagist 包已更新
- [ ] 文档链接已更新
- [ ] 示例代码已测试
- [ ] 兼容性已验证

## 回滚计划

如果发布后发现问题，可以：

1. **删除标签**
```bash
git tag -d v1.0.1
git push origin :refs/tags/v1.0.1
```

2. **创建修复版本**
```bash
# 修复问题后创建 v1.0.2
git tag -a v1.0.2 -m "Release v1.0.2 - Bug fixes"
git push origin v1.0.2
```

## 联系信息

如有问题，请联系：
- **邮箱**: lampxiezi@gmail.com
- **GitHub**: https://github.com/pfinalclub/php-mcp/issues
- **文档**: https://github.com/pfinalclub/php-mcp/blob/main/README.md

---

**注意**: 请确保在发布前已完成所有测试和验证，确保代码质量和功能完整性。
