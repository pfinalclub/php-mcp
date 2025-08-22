<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp\Attributes;

use Attribute;

/**
 * MCP Schema 属性
 * 
 * 用于定义参数 Schema
 * 
 * @package PFPMcp\Attributes
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class Schema
{
    /**
     * 参数描述
     */
    public string $description;
    
    /**
     * 参数类型
     */
    public string $type;
    
    /**
     * 是否必需
     */
    public bool $required;
    
    /**
     * 默认值
     */
    public mixed $default;
    
    /**
     * 构造函数
     * 
     * @param string $description 参数描述
     * @param string $type 参数类型
     * @param bool $required 是否必需
     * @param mixed $default 默认值
     */
    public function __construct(
        string $description = '',
        string $type = '',
        bool $required = true,
        mixed $default = null
    ) {
        $this->description = $description;
        $this->type = $type;
        $this->required = $required;
        $this->default = $default;
    }
}
