<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp\Config;

use PFPMcp\Exceptions\ConfigException;

/**
 * 服务器配置管理类
 * 
 * 负责管理 MCP 服务器的配置信息，包括传输协议、网络设置、日志配置等
 * 
 * @package PFPMcp\Config
 */
class ServerConfig
{
    /**
     * 默认配置
     */
    private const DEFAULT_CONFIG = [
        'transport' => 'stdio',
        'host' => '0.0.0.0',
        'port' => 8080,
        'log_level' => 'info',
        'log_file' => 'php://stderr',
        'session' => [
            'backend' => 'memory',
            'ttl' => 3600,
        ],
        'security' => [
            'rate_limit' => 100,
            'rate_window' => 60,
        ],
        'performance' => [
            'max_connections' => 1000,
            'timeout' => 30,
        ],
    ];
    
    /**
     * 配置数据
     */
    private array $config;
    
    /**
     * 构造函数
     * 
     * @param array $config 配置数组
     * @throws ConfigException 当配置无效时
     */
    public function __construct(array $config = [])
    {
        $this->config = $this->mergeConfig($config);
        $this->validateConfig();
    }
    
    /**
     * 合并配置
     * 
     * @param array $config 用户配置
     * @return array 合并后的配置
     */
    private function mergeConfig(array $config): array
    {
        // 从环境变量加载配置
        $envConfig = $this->loadFromEnvironment();
        
        // 合并配置：默认配置 < 环境变量 < 用户配置
        return array_replace_recursive(self::DEFAULT_CONFIG, $envConfig, $config);
    }
    
    /**
     * 从环境变量加载配置
     * 
     * @return array 环境变量配置
     */
    private function loadFromEnvironment(): array
    {
        return [
            'transport' => $_ENV['MCP_TRANSPORT'] ?? null,
            'host' => $_ENV['MCP_HOST'] ?? null,
            'port' => isset($_ENV['MCP_PORT']) ? (int)$_ENV['MCP_PORT'] : null,
            'log_level' => $_ENV['MCP_LOG_LEVEL'] ?? null,
            'log_file' => $_ENV['MCP_LOG_FILE'] ?? null,
            'session' => [
                'backend' => $_ENV['MCP_SESSION_BACKEND'] ?? null,
                'ttl' => isset($_ENV['MCP_SESSION_TTL']) ? (int)$_ENV['MCP_SESSION_TTL'] : null,
            ],
            'security' => [
                'rate_limit' => isset($_ENV['MCP_RATE_LIMIT']) ? (int)$_ENV['MCP_RATE_LIMIT'] : null,
                'rate_window' => isset($_ENV['MCP_RATE_WINDOW']) ? (int)$_ENV['MCP_RATE_WINDOW'] : null,
            ],
            'performance' => [
                'max_connections' => isset($_ENV['MCP_MAX_CONNECTIONS']) ? (int)$_ENV['MCP_MAX_CONNECTIONS'] : null,
                'timeout' => isset($_ENV['MCP_TIMEOUT']) ? (int)$_ENV['MCP_TIMEOUT'] : null,
            ],
        ];
    }
    
    /**
     * 验证配置
     * 
     * @throws ConfigException 当配置无效时
     */
    private function validateConfig(): void
    {
        $errors = [];
        
        // 验证传输协议
        $validTransports = ['stdio', 'http', 'ws', 'http+sse', 'streamable-http'];
        if (!in_array($this->config['transport'], $validTransports, true)) {
            $errors[] = "Invalid transport: {$this->config['transport']}. Valid options: " . implode(', ', $validTransports);
        }
        
        // 验证端口
        if ($this->config['port'] < 1 || $this->config['port'] > 65535) {
            $errors[] = "Invalid port: {$this->config['port']}. Must be between 1 and 65535";
        }
        
        // 验证日志级别
        $validLogLevels = ['debug', 'info', 'warning', 'error'];
        if (!in_array($this->config['log_level'], $validLogLevels, true)) {
            $errors[] = "Invalid log level: {$this->config['log_level']}. Valid options: " . implode(', ', $validLogLevels);
        }
        
        // 验证会话后端
        $validSessionBackends = ['memory', 'redis', 'database'];
        if (!in_array($this->config['session']['backend'], $validSessionBackends, true)) {
            $errors[] = "Invalid session backend: {$this->config['session']['backend']}. Valid options: " . implode(', ', $validSessionBackends);
        }
        
        // 验证数值配置
        if ($this->config['session']['ttl'] < 1) {
            $errors[] = "Session TTL must be greater than 0";
        }
        
        if ($this->config['security']['rate_limit'] < 1) {
            $errors[] = "Rate limit must be greater than 0";
        }
        
        if ($this->config['security']['rate_window'] < 1) {
            $errors[] = "Rate window must be greater than 0";
        }
        
        if ($this->config['performance']['max_connections'] < 1) {
            $errors[] = "Max connections must be greater than 0";
        }
        
        if ($this->config['performance']['timeout'] < 1) {
            $errors[] = "Timeout must be greater than 0";
        }
        
        if (!empty($errors)) {
            throw new ConfigException('Configuration validation failed: ' . implode('; ', $errors));
        }
    }
    
    /**
     * 获取传输协议
     * 
     * @return string 传输协议
     */
    public function getTransport(): string
    {
        return $this->config['transport'];
    }
    
    /**
     * 获取主机地址
     * 
     * @return string 主机地址
     */
    public function getHost(): string
    {
        return $this->config['host'];
    }
    
    /**
     * 获取端口号
     * 
     * @return int 端口号
     */
    public function getPort(): int
    {
        return $this->config['port'];
    }
    
    /**
     * 获取日志级别
     * 
     * @return string 日志级别
     */
    public function getLogLevel(): string
    {
        return $this->config['log_level'];
    }
    
    /**
     * 获取日志文件
     * 
     * @return string 日志文件路径
     */
    public function getLogFile(): string
    {
        return $this->config['log_file'];
    }
    
    /**
     * 获取会话配置
     * 
     * @return array 会话配置
     */
    public function getSessionConfig(): array
    {
        return $this->config['session'];
    }
    
    /**
     * 获取安全配置
     * 
     * @return array 安全配置
     */
    public function getSecurityConfig(): array
    {
        return $this->config['security'];
    }
    
    /**
     * 获取性能配置
     * 
     * @return array 性能配置
     */
    public function getPerformanceConfig(): array
    {
        return $this->config['performance'];
    }
    
    /**
     * 获取完整配置
     * 
     * @return array 完整配置数组
     */
    public function getAll(): array
    {
        return $this->config;
    }
    
    /**
     * 获取配置项
     * 
     * @param string $key 配置键
     * @param mixed $default 默认值
     * @return mixed 配置值
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }
    
    /**
     * 设置配置项
     * 
     * @param string $key 配置键
     * @param mixed $value 配置值
     * @throws ConfigException 当配置无效时
     */
    public function set(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
        $this->validateConfig();
    }
    
    /**
     * 检查配置项是否存在
     * 
     * @param string $key 配置键
     * @return bool 是否存在
     */
    public function has(string $key): bool
    {
        return isset($this->config[$key]);
    }
    
    /**
     * 从文件加载配置
     * 
     * @param string $file 配置文件路径
     * @throws ConfigException 当文件不存在或配置无效时
     */
    public function loadFromFile(string $file): void
    {
        if (!file_exists($file)) {
            throw new ConfigException("Configuration file not found: {$file}");
        }
        
        $config = require $file;
        
        if (!is_array($config)) {
            throw new ConfigException("Invalid configuration format in file: {$file}");
        }
        
        $this->config = $this->mergeConfig($config);
        $this->validateConfig();
    }
    
    /**
     * 导出配置到数组
     * 
     * @return array 配置数组
     */
    public function toArray(): array
    {
        return $this->config;
    }
    
    /**
     * 创建配置实例
     * 
     * @param array $config 配置数组
     * @return self 配置实例
     */
    public static function create(array $config = []): self
    {
        return new self($config);
    }
    
    /**
     * 从文件创建配置实例
     * 
     * @param string $file 配置文件路径
     * @return self 配置实例
     * @throws ConfigException 当文件不存在或配置无效时
     */
    public static function createFromFile(string $file): self
    {
        $instance = new self();
        $instance->loadFromFile($file);
        return $instance;
    }
}
