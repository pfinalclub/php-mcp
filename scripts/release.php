#!/usr/bin/env php
<?php declare(strict_types=1);

/**
 * PFPMcp 发布脚本
 * 
 * 自动化版本发布流程
 * 
 * @package PFPMcp\Scripts
 */

echo "🚀 PFPMcp v1.0.1 发布脚本\n";
echo "========================\n\n";

// 检查当前目录
if (!file_exists('composer.json')) {
    echo "❌ 错误：请在项目根目录运行此脚本\n";
    exit(1);
}

// 检查 Git 状态
echo "📋 检查 Git 状态...\n";
$gitStatus = shell_exec('git status --porcelain');
if (!empty($gitStatus)) {
    echo "⚠️  警告：有未提交的更改\n";
    echo $gitStatus;
    echo "\n请先提交或暂存更改，然后继续。\n";
    exit(1);
}

// 检查当前分支
$currentBranch = trim(shell_exec('git branch --show-current'));
if ($currentBranch !== 'main' && $currentBranch !== 'master') {
    echo "⚠️  警告：当前分支是 {$currentBranch}，建议在 main 分支发布\n";
    echo "是否继续？(y/N): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    if (trim($line) !== 'y' && trim($line) !== 'Y') {
        echo "❌ 发布已取消\n";
        exit(1);
    }
}

// 检查版本号
echo "🔍 检查版本号...\n";
$composerJson = json_decode(file_get_contents('composer.json'), true);
$version = $composerJson['version'] ?? 'unknown';
echo "当前版本: {$version}\n";

if ($version !== '1.0.1') {
    echo "❌ 错误：版本号不匹配，期望 1.0.1，实际 {$version}\n";
    exit(1);
}

// 检查标签是否已存在
$existingTag = shell_exec("git tag -l v{$version}");
if (!empty($existingTag)) {
    echo "⚠️  警告：标签 v{$version} 已存在\n";
    echo "是否删除并重新创建？(y/N): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    if (trim($line) === 'y' || trim($line) === 'Y') {
        shell_exec("git tag -d v{$version}");
        shell_exec("git push origin :refs/tags/v{$version}");
    } else {
        echo "❌ 发布已取消\n";
        exit(1);
    }
}

// 运行测试
echo "🧪 运行测试...\n";
$testResult = shell_exec('composer test 2>&1');
if (strpos($testResult, 'FAILURES') !== false || strpos($testResult, 'ERRORS') !== false) {
    echo "❌ 测试失败，请修复后重试\n";
    echo $testResult;
    exit(1);
}
echo "✅ 测试通过\n";

// 代码质量检查
echo "🔍 代码质量检查...\n";
$csResult = shell_exec('composer cs 2>&1');
if (strpos($csResult, 'ERROR') !== false) {
    echo "⚠️  代码风格问题，但继续发布\n";
    echo $csResult;
} else {
    echo "✅ 代码风格检查通过\n";
}

// 创建标签
echo "🏷️  创建 Git 标签...\n";
$tagMessage = "Release v{$version}

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
- 渐进式升级支持";

file_put_contents("release_message.txt", $tagMessage);
shell_exec("git tag -a v{$version} -F release_message.txt");
unlink("release_message.txt");

echo "✅ 标签创建成功\n";

// 推送标签
echo "📤 推送标签到远程仓库...\n";
shell_exec("git push origin v{$version}");
echo "✅ 标签推送成功\n";

// 生成发布包
echo "📦 生成发布包...\n";
if (!is_dir('dist')) {
    mkdir('dist');
}

// 创建发布包
$distFile = "dist/pfinalclub-php-mcp-{$version}.zip";
shell_exec("git archive --format=zip --output={$distFile} v{$version}");
echo "✅ 发布包生成成功: {$distFile}\n";

// 显示发布信息
echo "\n🎉 发布完成！\n";
echo "========================\n";
echo "版本: v{$version}\n";
echo "标签: v{$version}\n";
echo "发布包: {$distFile}\n";
echo "\n📋 下一步操作：\n";
echo "1. 在 GitHub 上创建 Release\n";
echo "2. 上传发布包到 Release\n";
echo "3. 发布到 Packagist（如果需要）\n";
echo "4. 更新文档和示例\n";
echo "\n🔗 相关链接：\n";
echo "- GitHub: https://github.com/pfinalclub/php-mcp\n";
echo "- Packagist: https://packagist.org/packages/pfinalclub/php-mcp\n";
echo "- 文档: https://github.com/pfinalclub/php-mcp/blob/main/README.md\n";

echo "\n✨ 感谢使用 PFPMcp！\n";
