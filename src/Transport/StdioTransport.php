<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp\Transport;

use Workerman\Worker;

/**
 * 标准输入输出传输协议
 * 
 * @package PFPMcp\Transport
 */
class StdioTransport implements TransportInterface
{
    /**
     * 消息处理器
     */
    private $messageHandler = null;
    
    /**
     * 启动传输协议
     */
    public function start(): void
    {
        // stdio 传输不需要特殊的启动逻辑
    }
    
    /**
     * 停止传输协议
     */
    public function stop(): void
    {
        // stdio 传输不需要特殊的停止逻辑
    }
    
    /**
     * 发送消息
     * 
     * @param string $message 消息内容
     */
    public function send(string $message): void
    {
        fwrite(STDOUT, $message . "\n");
    }
    
    /**
     * 设置消息处理器
     * 
     * @param callable $handler 消息处理器
     */
    public function onMessage(callable $handler): void
    {
        $this->messageHandler = $handler;
        
        // 监听标准输入
        while (($line = fgets(STDIN)) !== false) {
            $message = trim($line);
            if (!empty($message) && $this->messageHandler !== null) {
                call_user_func($this->messageHandler, $message);
            }
        }
    }
}
