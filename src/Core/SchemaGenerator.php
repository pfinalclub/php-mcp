<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp\Core;

use PFPMcp\Attributes\Schema;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Schema 生成器
 * 
 * 基于反射生成 JSON Schema
 * 
 * @package PFPMcp\Core
 */
class SchemaGenerator
{
    /**
     * 为方法生成输入 Schema
     * 
     * @param ReflectionMethod $method 方法反射
     * @return array JSON Schema
     */
    public function generateInputSchema(ReflectionMethod $method): array
    {
        $schema = [
            'type' => 'object',
            'properties' => [],
            'required' => []
        ];
        
        foreach ($method->getParameters() as $param) {
            $paramName = $param->getName();
            $paramSchema = $this->generateParameterSchema($param);
            
            $schema['properties'][$paramName] = $paramSchema;
            
            if (!$param->isOptional()) {
                $schema['required'][] = $paramName;
            }
        }
        
        return $schema;
    }
    
    /**
     * 为参数生成 Schema
     * 
     * @param ReflectionParameter $param 参数反射
     * @return array 参数 Schema
     */
    private function generateParameterSchema(ReflectionParameter $param): array
    {
        $schema = [
            'type' => $this->getParameterType($param),
            'description' => $this->getParameterDescription($param)
        ];
        
        // 获取 Schema 属性
        $attributes = $param->getAttributes(Schema::class);
        if (!empty($attributes)) {
            $schemaAttr = $attributes[0]->newInstance();
            
            if (!empty($schemaAttr->description)) {
                $schema['description'] = $schemaAttr->description;
            }
            
            if (!empty($schemaAttr->type)) {
                $schema['type'] = $schemaAttr->type;
            }
            
            if (!$schemaAttr->required) {
                $schema['required'] = false;
            }
            
            if ($schemaAttr->default !== null) {
                $schema['default'] = $schemaAttr->default;
            }
        }
        
        // 处理数组类型
        if ($schema['type'] === 'array') {
            $schema['items'] = ['type' => 'string']; // 默认字符串数组
        }
        
        return $schema;
    }
    
    /**
     * 获取参数类型
     * 
     * @param ReflectionParameter $param 参数反射
     * @return string JSON Schema 类型
     */
    private function getParameterType(ReflectionParameter $param): string
    {
        $type = $param->getType();
        if (!$type) {
            return 'string';
        }
        
        $typeName = $type->getName();
        
        return match($typeName) {
            'string' => 'string',
            'int', 'integer' => 'integer',
            'float', 'double' => 'number',
            'bool', 'boolean' => 'boolean',
            'array' => 'array',
            'object' => 'object',
            default => 'string'
        };
    }
    
    /**
     * 获取参数描述
     * 
     * @param ReflectionParameter $param 参数反射
     * @return string 参数描述
     */
    private function getParameterDescription(ReflectionParameter $param): string
    {
        $method = $param->getDeclaringFunction();
        $docComment = $method->getDocComment();
        
        if (!$docComment) {
            return '';
        }
        
        // 简单的注释解析
        $paramName = $param->getName();
        $pattern = "/@param\s+[^\s]+\s+\\\${$paramName}\s+(.+)/";
        
        if (preg_match($pattern, $docComment, $matches)) {
            return trim($matches[1]);
        }
        
        return '';
    }
    
    /**
     * 为方法生成输出 Schema
     * 
     * @param ReflectionMethod $method 方法反射
     * @return array JSON Schema
     */
    public function generateOutputSchema(ReflectionMethod $method): array
    {
        $returnType = $method->getReturnType();
        
        if (!$returnType) {
            return ['type' => 'object'];
        }
        
        $typeName = $returnType->getName();
        
        return match($typeName) {
            'string' => ['type' => 'string'],
            'int', 'integer' => ['type' => 'integer'],
            'float', 'double' => ['type' => 'number'],
            'bool', 'boolean' => ['type' => 'boolean'],
            'array' => [
                'type' => 'object',
                'properties' => [
                    'success' => ['type' => 'boolean'],
                    'result' => ['type' => 'string'],
                    'timestamp' => ['type' => 'integer']
                ]
            ],
            default => ['type' => 'object']
        };
    }
}
