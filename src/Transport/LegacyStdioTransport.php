<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/01/27
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp\Transport;

/**
 * 传统阻塞式标准输入输出传输协议
 * 
 * 使用传统的阻塞式 I/O 处理 stdio，适合简单的单线程应用场景。
 * 主要特性：
 * - 阻塞式 I/O 处理
 * - 简单直接的实现
 * - 良好的兼容性
 * - 适合资源受限的环境
 * 
 * @package PFPMcp\Transport
 */
class LegacyStdioTransport implements TransportInterface
{
    /**
     * 消息处理器回调函数
     */
    private $messageHandler = null;
    
    /**
     * 传输协议运行状态
     */
    private bool $isRunning = false;
    
    /**
     * 启动传输协议
     * 
     * 标记运行状态，阻塞式传输不需要特殊的启动逻辑
     */
    public function start(): void
    {
        $this->isRunning = true;
    }
    
    /**
     * 停止传输协议
     * 
     * 标记停止状态，阻塞式传输不需要特殊的停止逻辑
     */
    public function stop(): void
    {
        $this->isRunning = false;
    }
    
    /**
     * 发送消息到标准输出
     * 
     * @param string $message 要发送的消息内容
     */
    public function send(string $message): void
    {
        fwrite(STDOUT, $message . "\n");
        fflush(STDOUT); // 确保立即输出
    }
    
    /**
     * 设置消息处理器并开始监听标准输入
     * 
     * 使用阻塞式的 fgets() 循环监听 STDIN，处理接收到的消息
     * 
     * @param callable $handler 消息处理回调函数
     */
    public function onMessage(callable $handler): void
    {
        $this->messageHandler = $handler;
        
        // 阻塞式监听标准输入
        while ($this->isRunning && ($line = fgets(STDIN)) !== false) {
            $message = trim($line);
            if (!empty($message) && $this->messageHandler !== null) {
                try {
                    // 调用消息处理器处理接收到的消息
                    call_user_func($this->messageHandler, $message);
                } catch (\Throwable $e) {
                    // 记录错误但不中断处理流程
                    error_log("Error processing stdio message: " . $e->getMessage());
                }
            }
        }
    }
    
    /**
     * 获取传输协议信息
     * 
     * @return array 包含传输协议详细信息的数组
     */
    public function getInfo(): array
    {
        return [
            'type' => 'stdio',
            'mode' => 'blocking',
            'blocking' => true,
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
