<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp\Transport;

use Workerman\Worker;
use Workerman\Connection\TcpConnection;

/**
 * HTTP传输协议
 * 
 * @package PFPMcp\Transport
 */
class HttpTransport implements TransportInterface
{
    /**
     * 消息处理器
     */
    private ?callable $messageHandler = null;
    
    /**
     * Worker实例
     */
    private ?Worker $worker = null;
    
    /**
     * 启动传输协议
     */
    public function start(): void
    {
        $this->worker = new Worker('http://0.0.0.0:8080');
        
        $this->worker->onMessage = function (TcpConnection $connection, $data) {
            if ($this->messageHandler !== null) {
                call_user_func($this->messageHandler, $data);
            }
        };
    }
    
    /**
     * 停止传输协议
     */
    public function stop(): void
    {
        if ($this->worker !== null) {
            $this->worker->stop();
        }
    }
    
    /**
     * 发送消息
     * 
     * @param string $message 消息内容
     */
    public function send(string $message): void
    {
        // HTTP传输的发送逻辑
    }
    
    /**
     * 设置消息处理器
     * 
     * @param callable $handler 消息处理器
     */
    public function onMessage(callable $handler): void
    {
        $this->messageHandler = $handler;
    }
}
