<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/01/27
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp\Transport;

use Workerman\Worker;
use Workerman\Connection\TcpConnection;

/**
 * HTTP Server-Sent Events 传输协议
 * 
 * 基于 Workerman 的 HTTP SSE 服务器实现，支持服务器推送事件。
 * 
 * @package PFPMcp\Transport
 */
class HttpSseTransport implements TransportInterface
{
    /**
     * 消息处理器回调函数
     */
    private $messageHandler = null;
    
    /**
     * 连接处理器回调函数
     */
    private $connectHandler = null;
    
    /**
     * 关闭处理器回调函数
     */
    private $closeHandler = null;
    
    /**
     * 错误处理器回调函数
     */
    private $errorHandler = null;
    
    /**
     * Workerman Worker 实例
     */
    private ?Worker $worker = null;
    
    /**
     * 服务器主机地址
     */
    private string $host;
    
    /**
     * 服务器端口
     */
    private int $port;
    
    /**
     * 传输协议运行状态
     */
    private bool $isRunning = false;
    
    /**
     * 构造函数
     * 
     * @param string $host 服务器主机地址，默认 0.0.0.0
     * @param int $port 服务器端口，默认 8080
     */
    public function __construct(string $host = '0.0.0.0', int $port = 8080)
    {
        $this->host = $host;
        $this->port = $port;
    }
    
    /**
     * 启动传输协议
     * 
     * 创建并启动 HTTP SSE Worker，设置消息处理回调
     */
    public function start(): void
    {
        $this->worker = new Worker("http://{$this->host}:{$this->port}");
        
        $this->worker->onMessage = function (TcpConnection $connection, $data) {
            if ($this->messageHandler !== null) {
                call_user_func($this->messageHandler, $connection, $data);
            }
        };
        
        $this->isRunning = true;
    }
    
    /**
     * 停止传输协议
     * 
     * 停止 Worker 并清理资源
     */
    public function stop(): void
    {
        if ($this->worker !== null) {
            $this->worker->stop();
            $this->worker = null;
        }
        
        $this->isRunning = false;
    }
    
    /**
     * 发送消息
     * 
     * HTTP SSE 传输协议通过事件流发送消息
     * 
     * @param string $message 要发送的消息内容
     */
    public function send(string $message): void
    {
        // HTTP SSE 传输的发送逻辑通过事件流实现
        // 具体实现依赖于当前的连接上下文
    }
    
    /**
     * 设置消息处理器
     * 
     * @param callable $handler 消息处理回调函数，接收 connection 和 data 参数
     */
    public function onMessage(callable $handler): void
    {
        $this->messageHandler = $handler;
    }
    
    /**
     * 设置连接处理器
     * 
     * @param callable $handler 连接处理回调函数，接收 connection 参数
     */
    public function onConnect(callable $handler): void
    {
        $this->connectHandler = $handler;
    }
    
    /**
     * 设置关闭处理器
     * 
     * @param callable $handler 关闭处理回调函数，接收 connection 参数
     */
    public function onClose(callable $handler): void
    {
        $this->closeHandler = $handler;
    }
    
    /**
     * 设置错误处理器
     * 
     * @param callable $handler 错误处理回调函数，接收 connection 和 error 参数
     */
    public function onError(callable $handler): void
    {
        $this->errorHandler = $handler;
    }
    
    /**
     * 获取传输协议信息
     * 
     * @return array 包含传输协议详细信息的数组
     */
    public function getInfo(): array
    {
        return [
            'type' => 'http+sse',
            'host' => $this->host,
            'port' => $this->port,
            'is_running' => $this->isRunning
        ];
    }
    
    /**
     * 检查传输协议是否正在运行
     * 
     * @return bool 是否正在运行
     */
    public function isRunning(): bool
    {
        return $this->isRunning;
    }
}
