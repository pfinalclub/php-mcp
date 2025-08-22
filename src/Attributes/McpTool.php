<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp\Attributes;

use Attribute;

/**
 * MCP 工具属性
 * 
 * 用于标记 MCP 工具方法
 * 
 * @package PFPMcp\Attributes
 */
#[Attribute(Attribute::TARGET_METHOD)]
class McpTool
{
    /**
     * 工具名称
     */
    public string $name;
    
    /**
     * 工具描述
     */
    public string $description;
    
    /**
     * 构造函数
     * 
     * @param string $name 工具名称
     * @param string $description 工具描述
     */
    public function __construct(string $name, string $description = '')
    {
        $this->name = $name;
        $this->description = $description;
    }
}
