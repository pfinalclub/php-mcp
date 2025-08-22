<?php declare(strict_types=1);

/**
 * 纯 Workerman MCP 服务器示例
 * 
 * 展示如何使用完全基于 Workerman 的 MCP 实现
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use PFPMcp\Core\WorkermanMcpServer;
use PFPMcp\Tools\ToolManager;
use PFPMcp\Resources\ResourceManager;
use PFPMcp\Prompts\PromptManager;
use PFPMcp\Tools\Calculator;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// 创建日志记录器
$logger = new Logger('mcp-server');
$logger->pushHandler(new StreamHandler('php://stderr', Logger::DEBUG));

// 创建管理器
$toolManager = new ToolManager($logger);
$resourceManager = new ResourceManager($logger);
$promptManager = new PromptManager($logger);

// 注册工具
$calculator = new Calculator();
$toolManager->registerTool($calculator);

// 创建服务器配置
$config = [
    'host' => '0.0.0.0',
    'port' => 8080,
    'transport' => 'http'
];

// 创建并启动服务器
$server = new WorkermanMcpServer(
    $toolManager,
    $resourceManager,
    $promptManager,
    $logger,
    $config
);

echo "Starting Pure Workerman MCP Server on http://{$config['host']}:{$config['port']}\n";
echo "Press Ctrl+C to stop\n";

try {
    $server->start();
} catch (\Throwable $e) {
    $logger->error('Server failed to start', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    exit(1);
}
