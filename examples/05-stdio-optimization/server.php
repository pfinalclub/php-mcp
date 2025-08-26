#!/usr/bin/env php
<?php declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use PFPMcp\Server;
use PFPMcp\Config\ServerConfig;
use PFPMcp\Tools\Calculator;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// 创建日志记录器
$logger = new Logger('stdio-optimization');
$logger->pushHandler(new StreamHandler('php://stderr', Logger::DEBUG));

// 配置 stdio 优化选项
$config = [
    'transport' => 'stdio',
    'stdio' => [
        'mode' => 'optimized',
        'buffer_interval' => 5,
        'non_blocking' => true,
    ],
    'log_level' => 'debug'
];

try {
    $serverConfig = new ServerConfig($config);
    $server = new Server($serverConfig, $logger);
    
    // 注册计算器工具
    $server->registerTool(new Calculator());
    
    $logger->info('Starting optimized stdio MCP server...', [
        'config' => $serverConfig->getStdioConfig()
    ]);
    
    // 启动服务器
    $server->start();
    
} catch (\Throwable $e) {
    $logger->error('Failed to start server', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    exit(1);
}
