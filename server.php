#!/usr/bin/env php
<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

require_once __DIR__ . '/vendor/autoload.php';

use PFPMcp\Server;
use PFPMcp\Config\ServerConfig;
use PFPMcp\Tools\Calculator;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

// 设置错误处理
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// 创建日志记录器
$logger = new Logger('mcp-server');
$handler = new StreamHandler('php://stderr', Logger::DEBUG);
$handler->setFormatter(new LineFormatter("[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"));
$logger->pushHandler($handler);

// 加载配置
$config = loadConfig();

// 创建服务器
$server = new Server($config, $logger);

// 注册默认工具
$server->registerTool(new Calculator());

// 启动服务器
try {
    $logger->info('Starting MCP server', $config->getAll());
    $server->start();
} catch (\Throwable $e) {
    $logger->error('Failed to start server', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    exit(1);
}

/**
 * 加载配置
 */
function loadConfig(): ServerConfig
{
    $configFile = $_ENV['MCP_CONFIG'] ?? __DIR__ . '/config/server.php';
    
    if (file_exists($configFile)) {
        return ServerConfig::createFromFile($configFile);
    }
    
    return new ServerConfig();
}
