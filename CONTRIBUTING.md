# 贡献指南

感谢您对 PFPMcp 项目的关注！我们欢迎所有形式的贡献。

## 如何贡献

### 报告问题

如果您发现了问题，请：

1. 搜索现有的 [Issues](https://github.com/pfinal/php-mcp/issues) 确认问题是否已经被报告
2. 创建新的 Issue，包含：
   - 问题的详细描述
   - 重现步骤
   - 期望的行为
   - 实际的行为
   - 环境信息（PHP版本、操作系统等）

### 提交功能请求

如果您有功能建议，请：

1. 搜索现有的 Issues 确认功能是否已经被请求
2. 创建新的 Issue，包含：
   - 功能的详细描述
   - 使用场景
   - 预期收益

### 提交代码

如果您想贡献代码，请：

1. Fork 项目仓库
2. 创建功能分支：`git checkout -b feature/your-feature-name`
3. 提交更改：`git commit -m 'Add some feature'`
4. 推送分支：`git push origin feature/your-feature-name`
5. 创建 Pull Request

## 开发环境设置

### 环境要求

- PHP 8.2 或更高版本
- Composer
- Git

### 安装步骤

```bash
# 克隆仓库
git clone https://github.com/pfinal/php-mcp.git
cd php-mcp

# 安装依赖
composer install

# 运行测试
composer test

# 代码格式化
composer fix
```

## 代码规范

### 基本规范

- 遵循 PSR-12 编码规范
- 使用 PHP 8.2+ 特性
- 所有文件开头使用 `<?php declare(strict_types=1);`
- 使用 UTF-8 编码，无 BOM
- 行结束符使用 LF (\n)
- 代码缩进使用 4 个空格
- 行长度限制在 120 字符以内

### 命名规范

- 类名使用 PascalCase
- 方法名使用 camelCase
- 常量使用 UPPER_SNAKE_CASE
- 变量使用 camelCase
- 命名具有描述性和可读性

### 类型声明

- 所有方法参数包含类型声明
- 所有方法包含返回类型声明
- 使用联合类型和可空类型（PHP 8.0+）
- 优先使用严格类型比较（===, !==）

### 文档规范

- 所有公共方法包含 PHPDoc 注释
- 包含参数类型、返回类型和异常说明
- 复杂逻辑包含行内注释
- 类和方法有清晰的描述

## 测试规范

### 测试要求

- 测试覆盖率要求达到 80% 以上
- 包含正向测试用例
- 包含异常测试用例
- 包含边界条件测试
- 测试用例独立且可重复执行

### 运行测试

```bash
# 运行所有测试
composer test

# 生成测试覆盖率报告
composer test-coverage

# 运行代码质量检查
composer all
```

## 提交信息规范

提交信息应遵循以下格式：

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Type 类型

- `feat`: 新功能
- `fix`: 修复问题
- `docs`: 文档更新
- `style`: 代码格式调整
- `refactor`: 代码重构
- `test`: 测试相关
- `chore`: 构建过程或辅助工具的变动

### 示例

```
feat(tools): add calculator tool

Add a new calculator tool that supports basic mathematical operations
including addition, subtraction, multiplication, and division.

Closes #123
```

## 代码审查

所有 Pull Request 都会经过代码审查，审查要点包括：

- 代码逻辑正确性
- 性能影响评估
- 安全性考虑
- 可维护性评估
- 测试覆盖情况
- 文档完整性

## 发布流程

1. 确保所有测试通过
2. 更新版本号
3. 更新 CHANGELOG.md
4. 创建发布标签
5. 发布到 Packagist

## 联系方式

如果您有任何问题或建议，请：

- 创建 [Issue](https://github.com/pfinal/php-mcp/issues)
- 发送邮件到 admin@pfinal.cn

感谢您的贡献！
