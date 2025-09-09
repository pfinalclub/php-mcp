<?php declare(strict_types=1);

/**
 * 文件操作示例
 * 
 * 演示如何使用 PFPMcp 创建文件系统操作工具
 * 
 * @author PFinal南丞
 * @date 2025-01-27
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use PFPMcp\Server;
use PFPMcp\Config\ServerConfig;
use PFPMcp\Tools\FileOperations;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// 创建日志记录器
$logger = new Logger('file-operations-server');
$logger->pushHandler(new StreamHandler('php://stderr', Logger::INFO));

// 创建配置
$config = new ServerConfig([
    'transport' => 'stdio',
    'log_level' => 'info'
]);

// 创建服务器
$server = new Server($config, $logger);

// 注册文件操作工具
$server->registerTool(new FileOperations());

// 启动服务器
$logger->info('Starting file operations MCP server...');
$server->start();
