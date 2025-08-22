<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

return [
    // 传输协议配置
    'transport' => $_ENV['MCP_TRANSPORT'] ?? 'stdio',
    'host' => $_ENV['MCP_HOST'] ?? '0.0.0.0',
    'port' => (int)($_ENV['MCP_PORT'] ?? 8080),
    
    // 日志配置
    'log_level' => $_ENV['MCP_LOG_LEVEL'] ?? 'info',
    'log_file' => $_ENV['MCP_LOG_FILE'] ?? 'php://stderr',
    
    // 会话配置
    'session' => [
        'backend' => $_ENV['MCP_SESSION_BACKEND'] ?? 'memory',
        'ttl' => (int)($_ENV['MCP_SESSION_TTL'] ?? 3600),
    ],
    
    // 安全配置
    'security' => [
        'rate_limit' => (int)($_ENV['MCP_RATE_LIMIT'] ?? 100),
        'rate_window' => (int)($_ENV['MCP_RATE_WINDOW'] ?? 60),
    ],
    
    // 性能配置
    'performance' => [
        'max_connections' => (int)($_ENV['MCP_MAX_CONNECTIONS'] ?? 1000),
        'timeout' => (int)($_ENV['MCP_TIMEOUT'] ?? 30),
    ],
];
