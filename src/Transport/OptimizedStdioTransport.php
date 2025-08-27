<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/01/27
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp\Transport;

use Workerman\Timer;

/**
 * 优化的标准输入输出传输协议
 * 
 * 利用 Workerman 的常驻进程和事件驱动优势，实现非阻塞的 stdio 处理。
 * 主要特性：
 * - 非阻塞 I/O 处理
 * - 事件驱动的缓冲区管理
 * - 优雅的进程退出处理
 * - 可配置的性能参数
 * 
 * @package PFPMcp\Transport
 */
class OptimizedStdioTransport implements TransportInterface
{
    /**
     * 消息处理器回调函数
     */
    private $messageHandler = null;
    
    /**
     * 输入数据缓冲区
     * 用于存储不完整的输入行
     */
    private string $inputBuffer = '';
    
    /**
     * Workerman 定时器 ID
     * 用于定期处理输入缓冲区
     */
    private ?int $timerId = null;
    
    /**
     * 传输协议运行状态
     */
    private bool $isRunning = false;
    
    /**
     * 缓冲区处理间隔（毫秒）
     * 较小的值提供更好的响应性，但会增加 CPU 使用
     */
    private int $bufferInterval = 10;
    
    /**
     * 构造函数
     * 
     * @param array $config 配置选项
     *        - buffer_interval: 缓冲区处理间隔（毫秒），默认 10
     */
    public function __construct(array $config = [])
    {
        $this->bufferInterval = max(1, $config['buffer_interval'] ?? 10);
    }
    
    /**
     * 启动传输协议
     * 
     * 初始化非阻塞模式，启动定时器，注册进程退出处理
     */
    public function start(): void
    {
        $this->isRunning = true;
        
        // 设置 STDIN/STDOUT 为非阻塞模式
        $this->setNonBlockingMode();
        
        // 启动定时器处理输入缓冲区
        $this->startBufferProcessor();
        
        // 注册进程退出处理（信号处理和关闭函数）
        $this->registerShutdownHandler();
    }
    
    /**
     * 停止传输协议
     * 
     * 清理定时器，标记停止状态
     */
    public function stop(): void
    {
        $this->isRunning = false;
        
        // 停止并清理定时器
        if ($this->timerId !== null) {
            Timer::del($this->timerId);
            $this->timerId = null;
        }
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
        // stdio 传输协议不支持连接事件
    }
    
    /**
     * 设置关闭处理器
     * 
     * @param callable $handler 关闭处理回调函数，接收 connection 参数
     */
    public function onClose(callable $handler): void
    {
        // stdio 传输协议不支持关闭事件
    }
    
    /**
     * 设置错误处理器
     * 
     * @param callable $handler 错误处理回调函数，接收 connection 和 error 参数
     */
    public function onError(callable $handler): void
    {
        // stdio 传输协议不支持错误事件
    }
    
    /**
     * 设置非阻塞模式
     * 
     * 将 STDIN 和 STDOUT 设置为非阻塞模式，避免 I/O 操作阻塞主线程
     */
    private function setNonBlockingMode(): void
    {
        // 设置 STDIN 为非阻塞模式
        if (function_exists('stream_set_blocking')) {
            stream_set_blocking(STDIN, false);
        }
        
        // 设置 STDOUT 为非阻塞模式
        if (function_exists('stream_set_blocking')) {
            stream_set_blocking(STDOUT, false);
        }
    }
    
    /**
     * 启动缓冲区处理器
     * 
     * 创建 Workerman 定时器，定期检查和处理输入缓冲区
     */
    private function startBufferProcessor(): void
    {
        $this->timerId = Timer::add($this->bufferInterval / 1000, function () {
            $this->processInputBuffer();
        });
    }
    
    /**
     * 处理输入缓冲区
     * 
     * 读取可用的输入数据，添加到缓冲区，处理完整的行
     */
    private function processInputBuffer(): void
    {
        // 检查运行状态和消息处理器
        if (!$this->isRunning || $this->messageHandler === null) {
            return;
        }
        
        // 读取可用的输入数据
        $data = $this->readAvailableInput();
        
        if ($data === '') {
            return; // 没有新数据
        }
        
        // 将新数据添加到缓冲区
        $this->inputBuffer .= $data;
        
        // 处理缓冲区中的完整行
        $this->processCompleteLines();
    }
    
    /**
     * 读取可用的输入数据
     * 
     * 使用 stream_select 非阻塞地检查 STDIN 是否有数据可读
     * 
     * @return string 读取的数据，如果没有数据则返回空字符串
     */
    private function readAvailableInput(): string
    {
        $data = '';
        $read = [STDIN];
        $write = [];
        $except = [];
        
        // 非阻塞检查是否有数据可读（超时时间为 0）
        if (stream_select($read, $write, $except, 0, 0) > 0) {
            $chunk = fgets(STDIN);
            if ($chunk !== false) {
                $data = $chunk;
            }
        }
        
        return $data;
    }
    
    /**
     * 处理完整的行
     * 
     * 将缓冲区按换行符分割，处理完整的行，保留不完整的行在缓冲区中
     */
    private function processCompleteLines(): void
    {
        $lines = explode("\n", $this->inputBuffer);
        
        // 保留最后一行（可能不完整）在缓冲区中
        $this->inputBuffer = array_pop($lines);
        
        // 处理所有完整的行
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                try {
                    // 调用消息处理器处理完整的行
                    call_user_func($this->messageHandler, $line);
                } catch (\Throwable $e) {
                    // 记录错误但不中断处理流程
                    error_log("Error processing stdio message: " . $e->getMessage());
                }
            }
        }
    }
    
    /**
     * 注册进程退出处理
     * 
     * 注册信号处理器和关闭函数，确保进程退出时能够优雅地清理资源
     */
    private function registerShutdownHandler(): void
    {
        // 注册 POSIX 信号处理器（Linux/Unix 系统）
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [$this, 'handleShutdown']);
            pcntl_signal(SIGINT, [$this, 'handleShutdown']);
        }
        
        // 注册 PHP 关闭函数（所有系统都支持）
        register_shutdown_function([$this, 'handleShutdown']);
    }
    
    /**
     * 处理进程退出
     * 
     * 停止传输协议，处理缓冲区中的剩余数据
     */
    public function handleShutdown(): void
    {
        $this->stop();
        
        // 处理缓冲区中的剩余数据（如果有的话）
        if (!empty($this->inputBuffer)) {
            $this->processCompleteLines();
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
            'mode' => 'optimized',
            'blocking' => false,
            'buffer_interval' => $this->bufferInterval,
            'is_running' => $this->isRunning,
            'buffer_size' => strlen($this->inputBuffer)
        ];
    }
    
    /**
     * 设置缓冲区处理间隔
     * 
     * 动态调整缓冲区处理间隔，影响响应性和 CPU 使用
     * 
     * @param int $interval 新的处理间隔（毫秒）
     */
    public function setBufferInterval(int $interval): void
    {
        $this->bufferInterval = max(1, $interval);
        
        // 如果正在运行，重启定时器以应用新的间隔
        if ($this->isRunning && $this->timerId !== null) {
            Timer::del($this->timerId);
            $this->startBufferProcessor();
        }
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
