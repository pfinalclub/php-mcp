# 贡献指南

## 📋 欢迎贡献

感谢您对 PFPMcp 项目的关注！我们欢迎各种形式的贡献，包括但不限于：

- 🐛 报告 Bug
- 💡 提出新功能建议
- 📝 改进文档
- 🔧 提交代码
- 🧪 编写测试
- 📢 分享使用经验

## 🚀 快速开始

### 1. Fork 项目

1. 访问 [PFPMcp GitHub 仓库](https://github.com/pfinalclub/php-mcp)
2. 点击右上角的 "Fork" 按钮
3. 克隆您的 Fork 到本地：

```bash
git clone https://github.com/your-username/php-mcp.git
cd php-mcp
```

### 2. 设置开发环境

```bash
# 安装依赖
composer install

# 运行测试
composer test

# 代码格式化
composer fix

# 代码质量检查
composer all
```

### 3. 创建分支

```bash
# 创建功能分支
git checkout -b feature/your-feature-name

# 或创建修复分支
git checkout -b fix/your-bug-fix
```

## 📝 贡献类型

### 🐛 报告 Bug

#### 报告前检查
- [ ] 确认是 Bug 而不是功能请求
- [ ] 搜索现有 Issues 确认未被报告
- [ ] 使用最新版本测试

#### Bug 报告模板
```markdown
## Bug 描述
简要描述 Bug 的情况

## 复现步骤
1. 执行 '...'
2. 点击 '....'
3. 滚动到 '....'
4. 看到错误

## 预期行为
描述您期望的行为

## 实际行为
描述实际发生的行为

## 环境信息
- PHP 版本: 
- 操作系统: 
- PFPMcp 版本: 
- 传输协议: 

## 错误信息
```
粘贴错误信息或日志
```

## 附加信息
任何其他相关信息
```

### 💡 功能请求

#### 功能请求模板
```markdown
## 功能描述
简要描述您希望添加的功能

## 使用场景
描述这个功能的使用场景和解决的问题

## 详细说明
详细描述功能的实现方式

## 替代方案
描述您考虑过的其他解决方案

## 附加信息
任何其他相关信息
```

### 🔧 代码贡献

#### 代码规范

##### PHP 代码规范
- 遵循 [PSR-12](https://www.php-fig.org/psr/psr-12/) 编码规范
- 使用 PHP 8.2+ 特性
- 所有方法必须包含类型声明
- 所有公共方法必须包含 PHPDoc 注释

##### 代码示例
```php
<?php declare(strict_types=1);

namespace PFPMcp\YourNamespace;

/**
 * 示例类
 * 
 * @package PFPMcp\YourNamespace
 */
class ExampleClass
{
    /**
     * 示例方法
     * 
     * @param string $input 输入参数
     * @return array 返回结果
     * @throws \InvalidArgumentException 当输入无效时
     */
    public function exampleMethod(string $input): array
    {
        if (empty($input)) {
            throw new \InvalidArgumentException('Input cannot be empty');
        }
        
        return [
            'success' => true,
            'result' => strtoupper($input),
            'timestamp' => time()
        ];
    }
}
```

#### 提交规范

##### 提交信息格式
```
<type>(<scope>): <subject>

<body>

<footer>
```

##### 类型说明
- `feat`: 新功能
- `fix`: Bug 修复
- `docs`: 文档更新
- `style`: 代码格式调整
- `refactor`: 代码重构
- `test`: 测试相关
- `chore`: 构建过程或辅助工具的变动

##### 提交示例
```
feat(tools): add file operations tool

- Add read_file tool for reading file contents
- Add write_file tool for writing file contents
- Add delete_file tool for deleting files
- Include security validation for file paths

Closes #123
```

#### 测试要求

##### 单元测试
```php
<?php declare(strict_types=1);

namespace PFPMcp\Tests\YourNamespace;

use PFPMcp\YourNamespace\ExampleClass;
use PHPUnit\Framework\TestCase;

/**
 * ExampleClass 测试
 */
class ExampleClassTest extends TestCase
{
    private ExampleClass $example;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->example = new ExampleClass();
    }
    
    /**
     * 测试正常情况
     */
    public function testExampleMethodWithValidInput(): void
    {
        $result = $this->example->exampleMethod('hello');
        
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('HELLO', $result['result']);
    }
    
    /**
     * 测试异常情况
     */
    public function testExampleMethodWithEmptyInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Input cannot be empty');
        
        $this->example->exampleMethod('');
    }
}
```

##### 测试覆盖率要求
- 新代码测试覆盖率必须达到 90% 以上
- 所有公共方法必须有测试用例
- 包含正向测试和异常测试

### 📚 文档贡献

#### 文档类型
- API 文档
- 使用指南
- 示例代码
- 故障排除
- 最佳实践

#### 文档规范
- 使用 Markdown 格式
- 包含代码示例
- 提供清晰的说明
- 保持文档更新

#### 文档示例
```markdown
# 功能标题

## 概述
简要描述功能

## 使用方法
```php
// 代码示例
$example = new ExampleClass();
$result = $example->exampleMethod('input');
```

## 参数说明
| 参数 | 类型 | 描述 |
|------|------|------|
| input | string | 输入参数 |

## 返回值
```json
{
  "success": true,
  "result": "OUTPUT",
  "timestamp": 1640995200
}
```

## 注意事项
- 注意事项1
- 注意事项2
```

## 🔄 提交流程

### 1. 准备提交

```bash
# 添加更改
git add .

# 提交更改
git commit -m "feat: add new feature"

# 推送到您的 Fork
git push origin feature/your-feature-name
```

### 2. 创建 Pull Request

1. 访问您的 Fork 页面
2. 点击 "New Pull Request" 按钮
3. 选择正确的分支
4. 填写 PR 描述

#### PR 模板
```markdown
## 变更描述
简要描述本次变更

## 变更类型
- [ ] Bug 修复
- [ ] 新功能
- [ ] 文档更新
- [ ] 代码重构
- [ ] 性能优化
- [ ] 其他

## 测试
- [ ] 添加了测试用例
- [ ] 所有测试通过
- [ ] 测试覆盖率符合要求

## 检查清单
- [ ] 代码遵循项目规范
- [ ] 添加了必要的文档
- [ ] 更新了 CHANGELOG.md
- [ ] 没有破坏性变更

## 相关 Issue
Closes #123
```

### 3. 代码审查

#### 审查标准
- 代码质量和规范
- 功能正确性
- 测试覆盖率
- 文档完整性
- 性能影响

#### 审查流程
1. 自动化检查（CI/CD）
2. 维护者审查
3. 社区反馈
4. 最终合并

## 🏷️ 标签说明

### Issue 标签
- `bug`: Bug 报告
- `enhancement`: 功能增强
- `documentation`: 文档相关
- `question`: 问题咨询
- `help wanted`: 需要帮助
- `good first issue`: 适合新手的 Issue

### PR 标签
- `ready for review`: 准备审查
- `needs review`: 需要审查
- `approved`: 已批准
- `changes requested`: 需要修改
- `merged`: 已合并

## 🎯 贡献者等级

### 🌟 新手贡献者
- 修复文档错误
- 添加测试用例
- 解决 `good first issue` 标签的问题

### 🚀 活跃贡献者
- 修复 Bug
- 实现小功能
- 改进文档

### 💎 核心贡献者
- 实现重要功能
- 代码审查
- 项目维护

### 👑 维护者
- 项目决策
- 版本发布
- 社区管理

## 🏆 贡献者认可

### 贡献者列表
所有贡献者都会在以下位置被认可：
- README.md 贡献者列表
- CHANGELOG.md 版本记录
- 项目网站

### 特殊贡献
- 重大功能贡献者
- 长期维护贡献者
- 社区活跃贡献者

## 📞 获取帮助

### 联系方式
- **GitHub Issues**: [创建 Issue](https://github.com/pfinalclub/php-mcp/issues)
- **GitHub Discussions**: [参与讨论](https://github.com/pfinalclub/php-mcp/discussions)
- **邮件**: lampxiezi@gmail.com

### 社区资源
- [项目文档](../docs/)
- [示例代码](../examples/)
- [API 参考](../docs/api-reference.md)
- [故障排除](../docs/troubleshooting.md)

## 📋 行为准则

### 我们的承诺
为了营造开放和友好的环境，我们承诺：

- 尊重所有贡献者
- 接受建设性批评
- 关注社区最佳利益
- 对其他社区成员表示同理心

### 期望行为
- 使用友好和包容的语言
- 尊重不同的观点和经验
- 优雅地接受建设性批评
- 关注对社区最有利的事情
- 对其他社区成员表示同理心

### 不可接受的行为
- 使用性暗示的语言或图像
- 挑衅、侮辱或贬损性评论
- 公开或私人骚扰
- 未经许可发布他人私人信息
- 其他在专业环境中不当的行为

## 📄 许可证

通过贡献，您同意您的贡献将在 MIT 许可证下获得许可。

## 🙏 感谢

感谢所有为 PFPMcp 项目做出贡献的开发者！

---

**文档版本**: 1.0  
**创建日期**: 2025-01-27  
**最后更新**: 2025-01-27  
**维护者**: PFPMcp 开发团队