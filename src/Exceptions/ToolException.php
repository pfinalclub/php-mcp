<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp\Exceptions;

/**
 * 工具异常类
 * 
 * @package PFPMcp\Exceptions
 */
class ToolException extends ServerException
{
    /**
     * 错误代码
     */
    protected string $errorCode = 'TOOL_ERROR';
}
