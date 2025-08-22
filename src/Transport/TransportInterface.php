<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp\Transport;

/**
 * 传输协议接口
 * 
 * 定义传输协议的基本接口
 * 
 * @package PFPMcp\Transport
 */
interface TransportInterface
{
    /**
     * 启动传输协议
     */
    public function start(): void;
    
    /**
     * 停止传输协议
     */
    public function stop(): void;
    
    /**
     * 发送消息
     * 
     * @param string $message 消息内容
     */
    public function send(string $message): void;
    
    /**
     * 设置消息处理器
     * 
     * @param callable $handler 消息处理器
     */
    public function onMessage(callable $handler): void;
}
