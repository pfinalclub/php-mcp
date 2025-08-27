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
 * 定义传输协议的基本接口，支持完整的连接生命周期管理
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
     * @param callable $handler 消息处理器，接收 connection 和 data 参数
     */
    public function onMessage(callable $handler): void;
    
    /**
     * 设置连接处理器
     * 
     * @param callable $handler 连接处理器，接收 connection 参数
     */
    public function onConnect(callable $handler): void;
    
    /**
     * 设置关闭处理器
     * 
     * @param callable $handler 关闭处理器，接收 connection 参数
     */
    public function onClose(callable $handler): void;
    
    /**
     * 设置错误处理器
     * 
     * @param callable $handler 错误处理器，接收 connection 和 error 参数
     */
    public function onError(callable $handler): void;
    
    /**
     * 获取传输协议信息
     * 
     * @return array 传输协议信息
     */
    public function getInfo(): array;
    
    /**
     * 检查传输协议是否正在运行
     * 
     * @return bool 是否正在运行
     */
    public function isRunning(): bool;
}
