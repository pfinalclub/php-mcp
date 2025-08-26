<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp\Exceptions;

/**
 * 服务器异常基类
 * 
 * @package PFPMcp\Exceptions
 */
class ServerException extends \RuntimeException
{
    /**
     * 错误代码
     */
    protected string $errorCode = 'SERVER_ERROR';
    
    /**
     * 构造函数
     * 
     * @param string $message 错误消息
     * @param int $code 错误代码
     * @param \Throwable|null $previous 前一个异常
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * 获取错误代码
     * 
     * @return string 错误代码
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
