<?php declare(strict_types=1);

/**
 * PFPMcp v1.0.3 发布脚本
 * 
 * 自动执行版本发布流程
 * 
 * @author PFinal南丞
 * @date 2025-01-27
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class ReleaseManager
{
    private Logger $logger;
    private string $version = '1.0.3';
    private string $releaseDate = '2025-01-27';
    
    public function __construct()
    {
        $this->logger = new Logger('release');
        $this->logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
    }
    
    /**
     * 执行发布流程
     */
    public function release(): void
    {
        $this->logger->info("开始发布 PFPMcp v{$this->version}");
        
        try {
            $this->validateEnvironment();
            $this->runTests();
            $this->updateVersion();
            $this->generateReleaseNotes();
            $this->createGitTag();
            $this->pushToRemote();
            
            $this->logger->info("✅ PFPMcp v{$this->version} 发布成功！");
            $this->displayReleaseSummary();
            
        } catch (\Exception $e) {
            $this->logger->error("❌ 发布失败: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 验证环境
     */
    private function validateEnvironment(): void
    {
        $this->logger->info("🔍 验证发布环境...");
        
        // 检查 Git 状态
        $gitStatus = shell_exec('git status --porcelain');
        if (!empty(trim($gitStatus))) {
            throw new \RuntimeException('工作目录不干净，请先提交所有更改');
        }
        
        // 检查当前分支
        $currentBranch = trim(shell_exec('git branch --show-current'));
        if ($currentBranch !== 'main' && $currentBranch !== 'master') {
            throw new \RuntimeException("当前分支 {$currentBranch} 不是主分支");
        }
        
        // 检查 PHP 版本
        if (version_compare(PHP_VERSION, '8.2.0', '<')) {
            throw new \RuntimeException('PHP 版本必须 >= 8.2.0');
        }
        
        // 检查 Composer
        if (!file_exists('composer.json')) {
            throw new \RuntimeException('composer.json 文件不存在');
        }
        
        $this->logger->info("✅ 环境验证通过");
    }
    
    /**
     * 运行测试
     */
    private function runTests(): void
    {
        $this->logger->info("🧪 运行测试套件...");
        
        // 运行代码质量检查
        $this->runCommand('composer cs', '代码规范检查');
        $this->runCommand('composer stan', '静态分析检查');
        $this->runCommand('composer test', '单元测试');
        
        $this->logger->info("✅ 所有测试通过");
    }
    
    /**
     * 更新版本信息
     */
    private function updateVersion(): void
    {
        $this->logger->info("📝 更新版本信息...");
        
        // 更新 composer.json
        $composerJson = json_decode(file_get_contents('composer.json'), true);
        $composerJson['version'] = $this->version;
        file_put_contents('composer.json', json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        // 更新 README.md
        $readme = file_get_contents('README.md');
        $readme = preg_replace('/version-[\d\.]+-blue\.svg/', "version-{$this->version}-blue.svg", $readme);
        file_put_contents('README.md', $readme);
        
        $this->logger->info("✅ 版本信息更新完成");
    }
    
    /**
     * 生成发布说明
     */
    private function generateReleaseNotes(): void
    {
        $this->logger->info("📋 生成发布说明...");
        
        $releaseNotes = $this->generateReleaseNotesContent();
        file_put_contents("docs/release-notes-v{$this->version}.md", $releaseNotes);
        
        $this->logger->info("✅ 发布说明生成完成");
    }
    
    /**
     * 创建 Git 标签
     */
    private function createGitTag(): void
    {
        $this->logger->info("🏷️ 创建 Git 标签...");
        
        // 提交所有更改
        $this->runCommand('git add .', '添加文件到暂存区');
        $this->runCommand("git commit -m \"chore: release v{$this->version}\"", '提交版本更新');
        
        // 创建标签
        $this->runCommand("git tag -a v{$this->version} -m \"Release v{$this->version}\"", '创建版本标签');
        
        $this->logger->info("✅ Git 标签创建完成");
    }
    
    /**
     * 推送到远程仓库
     */
    private function pushToRemote(): void
    {
        $this->logger->info("🚀 推送到远程仓库...");
        
        $this->runCommand('git push origin main', '推送主分支');
        $this->runCommand("git push origin v{$this->version}", '推送版本标签');
        
        $this->logger->info("✅ 推送完成");
    }
    
    /**
     * 运行命令
     */
    private function runCommand(string $command, string $description): void
    {
        $this->logger->info("执行: {$description}");
        
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \RuntimeException("命令执行失败: {$command}\n输出: " . implode("\n", $output));
        }
        
        $this->logger->info("✅ {$description} 完成");
    }
    
    /**
     * 生成发布说明内容
     */
    private function generateReleaseNotesContent(): string
    {
        return "# PFPMcp v{$this->version} 发布说明

## 🎉 版本概述

PFPMcp v{$this->version} 是一个专注于**文档完善**和**社区建设**的版本。本次发布大幅提升了项目的文档质量、社区基础设施和用户体验，为项目的长期发展奠定了坚实基础。

## 📅 发布日期

**发布日期**: {$this->releaseDate}  
**版本类型**: 功能增强版本  
**兼容性**: 向后兼容 v1.0.2

## 🚀 主要更新

### 📚 文档系统完善
- **完整的 API 参考文档**: 新增详细的 API 接口文档
- **故障排除指南**: 新增全面的故障排除指南
- **部署最佳实践**: 新增生产环境部署指南
- **文件操作示例**: 新增完整的文件系统操作工具示例

### 👥 社区基础设施
- **贡献者指南**: 新增详细的贡献指南
- **GitHub 模板**: 新增 Bug 报告、功能请求、问题咨询、Pull Request 模板
- **社区指南**: 新增社区价值观、成员角色、交流渠道说明
- **行为准则**: 新增社区行为准则
- **社区活动计划**: 新增 2025 年完整活动日历
- **贡献者列表**: 新增贡献者等级体系、奖励机制、成长路径

### 🛠️ 示例项目
- **文件操作工具**: 完整的文件系统操作工具，包含 9 个核心功能
- **安全特性**: 路径验证、权限检查、输入清理
- **详细文档**: 每个工具都有详细的参数说明、返回值格式、使用示例

## 📊 质量指标

### 文档质量
- **API 文档覆盖率**: 100%
- **示例项目数量**: 4个
- **文档页面数量**: 15+
- **代码示例数量**: 50+

### 社区建设
- **贡献者指南**: 完整
- **GitHub 模板**: 4个
- **社区活动计划**: 2025年全年
- **支持渠道**: 5个

## 🔄 向后兼容性

- 保持所有现有 API 接口不变
- 保持所有现有配置格式兼容
- 保持所有现有工具定义方式
- 新增功能不影响现有代码

## 🚀 升级指南

### 从 v1.0.2 升级

```bash
composer update pfinalclub/php-mcp
```

## 📞 支持信息

- **文档**: [完整文档](docs/)
- **Issues**: [GitHub Issues](https://github.com/pfinalclub/php-mcp/issues)
- **Discussions**: [GitHub Discussions](https://github.com/pfinalclub/php-mcp/discussions)
- **邮件**: lampxiezi@gmail.com

---

**PFPMcp v{$this->version}** - 让 MCP 服务器开发更简单，让社区更活跃！ 🚀
";
    }
    
    /**
     * 显示发布摘要
     */
    private function displayReleaseSummary(): void
    {
        $this->logger->info("
🎉 PFPMcp v{$this->version} 发布成功！

📋 发布摘要:
- 版本: v{$this->version}
- 日期: {$this->releaseDate}
- 类型: 功能增强版本
- 兼容性: 向后兼容

📚 主要更新:
- 完整的 API 参考文档
- 故障排除指南
- 部署最佳实践
- 文件操作示例
- 社区基础设施
- 贡献者指南
- GitHub 模板系统
- 社区活动计划

🔗 相关链接:
- GitHub: https://github.com/pfinalclub/php-mcp
- 文档: https://github.com/pfinalclub/php-mcp/blob/main/docs/
- 发布说明: https://github.com/pfinalclub/php-mcp/releases/tag/v{$this->version}

感谢所有贡献者！🚀
        ");
    }
}

// 执行发布
if (php_sapi_name() === 'cli') {
    $releaseManager = new ReleaseManager();
    $releaseManager->release();
} else {
    echo "此脚本只能在命令行中运行\n";
    exit(1);
}
