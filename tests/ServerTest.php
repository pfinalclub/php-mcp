<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp\Tests;

use PFPMcp\Server;
use PFPMcp\Config\ServerConfig;
use PFPMcp\Tools\Calculator;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * 服务器测试类
 * 
 * @package PFPMcp\Tests
 */
class ServerTest extends TestCase
{
    private Server $server;
    private ServerConfig $config;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->config = new ServerConfig([
            'transport' => 'stdio',
            'log_level' => 'debug'
        ]);
        
        $this->server = new Server($this->config, new NullLogger());
    }
    
    protected function tearDown(): void
    {
        $this->server = null;
        parent::tearDown();
    }
    
    /**
     * 测试服务器创建
     */
    public function testServerCreation(): void
    {
        $this->assertInstanceOf(Server::class, $this->server);
        $this->assertFalse($this->server->isRunning());
    }
    
    /**
     * 测试配置获取
     */
    public function testConfigRetrieval(): void
    {
        $config = $this->server->getConfig();
        $this->assertInstanceOf(ServerConfig::class, $config);
        $this->assertEquals('stdio', $config->getTransport());
    }
    
    /**
     * 测试工具注册
     */
    public function testToolRegistration(): void
    {
        $calculator = new Calculator();
        $this->server->registerTool($calculator);
        
        // 这里可以添加更多测试逻辑
        $this->assertTrue(true);
    }
    
    /**
     * 测试日志记录器获取
     */
    public function testLoggerRetrieval(): void
    {
        $logger = $this->server->getLogger();
        $this->assertInstanceOf(\Psr\Log\LoggerInterface::class, $logger);
    }
}
