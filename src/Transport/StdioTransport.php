<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/01/27
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp\Transport;

/**
 * 标准输入输出传输协议工厂类
 * 
 * 根据配置自动选择最优的 stdio 实现方式，提供智能的模式选择。
 * 支持的模式：
 * - auto: 自动选择最优模式
 * - optimized: 强制使用优化的非阻塞模式
 * - blocking: 强制使用传统的阻塞模式
 * 
 * @package PFPMcp\Transport
 */
class StdioTransport implements TransportInterface
{
    /**
     * 内部传输协议实例
     * 根据配置自动选择 OptimizedStdioTransport 或 LegacyStdioTransport
     */
    private TransportInterface $transport;
    
    /**
     * 构造函数
     * 
     * 根据配置创建合适的传输协议实例
     * 
     * @param array $config 配置选项
     *        - mode: 模式选择 (auto|optimized|blocking)，默认 auto
     *        - non_blocking: 是否启用非阻塞模式，默认 true
     *        - buffer_interval: 缓冲区处理间隔（仅优化模式），默认 10
     */
    public function __construct(array $config = [])
    {
        $this->transport = $this->createTransport($config);
    }
    
    /**
     * 创建传输协议实例
     * 
     * 根据配置模式自动选择最优的实现方式
     * 
     * @param array $config 配置选项
     * @return TransportInterface 传输协议实例
     */
    private function createTransport(array $config): TransportInterface
    {
        $mode = $config['mode'] ?? 'auto';
        $nonBlocking = $config['non_blocking'] ?? true;
        
        // 根据模式选择实现
        switch ($mode) {
            case 'optimized':
                // 强制使用优化模式
                return new OptimizedStdioTransport($config);
                
            case 'blocking':
                // 强制使用阻塞模式
                return new LegacyStdioTransport();
                
            case 'auto':
            default:
                // 自动选择：如果支持非阻塞且配置启用，则使用优化版本
                if ($nonBlocking && $this->supportsNonBlocking()) {
                    return new OptimizedStdioTransport($config);
                } else {
                    return new LegacyStdioTransport();
                }
        }
    }
    
    /**
     * 检查是否支持非阻塞模式
     * 
     * 验证系统是否支持非阻塞 stdio 处理所需的函数和扩展
     * 
     * @return bool 是否支持非阻塞模式
     */
    private function supportsNonBlocking(): bool
    {
        // 检查必要的函数和扩展
        return function_exists('stream_set_blocking') 
            && function_exists('stream_select')
            && function_exists('pcntl_signal');
    }
    
    /**
     * 启动传输协议
     * 
     * 委托给内部传输协议实例
     */
    public function start(): void
    {
        $this->transport->start();
    }
    
    /**
     * 停止传输协议
     * 
     * 委托给内部传输协议实例
     */
    public function stop(): void
    {
        $this->transport->stop();
    }
    
    /**
     * 发送消息
     * 
     * 委托给内部传输协议实例
     * 
     * @param string $message 要发送的消息内容
     */
    public function send(string $message): void
    {
        $this->transport->send($message);
    }
    
    /**
     * 设置消息处理器
     * 
     * 委托给内部传输协议实例
     * 
     * @param callable $handler 消息处理回调函数
     */
    public function onMessage(callable $handler): void
    {
        $this->transport->onMessage($handler);
    }
    
    /**
     * 获取传输协议信息
     * 
     * 返回内部传输协议的信息，并添加工厂相关的信息
     * 
     * @return array 包含传输协议详细信息的数组
     */
    public function getInfo(): array
    {
        $info = $this->transport->getInfo();
        $info['factory'] = true;
        $info['selected_mode'] = $info['mode'];
        
        return $info;
    }
    
    /**
     * 检查传输协议是否正在运行
     * 
     * 委托给内部传输协议实例
     * 
     * @return bool 是否正在运行
     */
    public function isRunning(): bool
    {
        return $this->transport->isRunning();
    }
    
    /**
     * 获取内部传输协议实例
     * 
     * 用于高级用户访问具体的传输协议实现
     * 
     * @return TransportInterface 内部传输协议实例
     */
    public function getInternalTransport(): TransportInterface
    {
        return $this->transport;
    }
}
