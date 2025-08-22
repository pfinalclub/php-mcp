<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use PFPMcp\Server;
use PFPMcp\Config\ServerConfig;
use PFPMcp\Tools\Calculator;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// 创建日志记录器
$logger = new Logger('mcp-server');
$logger->pushHandler(new StreamHandler('php://stderr', Logger::INFO));

// 创建配置
$config = new ServerConfig([
    'transport' => 'stdio',
    'log_level' => 'info'
]);

// 创建服务器
$server = new Server($config, $logger);

// 注册工具
$server->registerTool(new Calculator());

// 启动服务器
$logger->info('Starting basic MCP server...');
$server->start();
