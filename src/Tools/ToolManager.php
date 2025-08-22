<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp\Tools;

use PFPMcp\Exceptions\ToolException;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionAttribute;

/**
 * 工具管理器
 * 
 * 负责管理 MCP 工具的注册、发现和调用
 * 
 * @package PFPMcp\Tools
 */
class ToolManager
{
    /**
     * 已注册的工具
     */
    private array $tools = [];
    
    /**
     * 日志记录器
     */
    private LoggerInterface $logger;
    
    /**
     * 构造函数
     * 
     * @param LoggerInterface $logger 日志记录器
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * 注册工具
     * 
     * @param object $tool 工具对象
     * @throws ToolException 当工具注册失败时
     */
    public function registerTool(object $tool): void
    {
        try {
            $reflection = new ReflectionClass($tool);
            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
            
            foreach ($methods as $method) {
                $attributes = $method->getAttributes(\PhpMcp\Attributes\McpTool::class);
                
                if (empty($attributes)) {
                    continue;
                }
                
                $attribute = $attributes[0];
                $args = $attribute->getArguments();
                
                $toolName = $args['name'] ?? $method->getName();
                $description = $args['description'] ?? '';
                
                $this->tools[$toolName] = [
                    'object' => $tool,
                    'method' => $method->getName(),
                    'description' => $description,
                    'parameters' => $this->extractParameters($method)
                ];
                
                $this->logger->info('Tool registered', [
                    'name' => $toolName,
                    'method' => $method->getName(),
                    'description' => $description
                ]);
            }
            
        } catch (\Throwable $e) {
            $this->logger->error('Failed to register tool', [
                'tool' => get_class($tool),
                'error' => $e->getMessage()
            ]);
            throw new ToolException('Failed to register tool: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * 提取方法参数信息
     * 
     * @param ReflectionMethod $method 方法反射对象
     * @return array 参数信息
     */
    private function extractParameters(ReflectionMethod $method): array
    {
        $parameters = [];
        
        foreach ($method->getParameters() as $param) {
            $paramInfo = [
                'name' => $param->getName(),
                'type' => $param->getType()?->getName() ?? 'mixed',
                'required' => !$param->isOptional(),
                'default' => $param->isOptional() ? $param->getDefaultValue() : null,
                'description' => ''
            ];
            
            // 获取参数描述
            $attributes = $param->getAttributes(\PhpMcp\Attributes\Schema::class);
            if (!empty($attributes)) {
                $args = $attributes[0]->getArguments();
                $paramInfo['description'] = $args['description'] ?? '';
            }
            
            $parameters[] = $paramInfo;
        }
        
        return $parameters;
    }
    
    /**
     * 调用工具
     * 
     * @param string $toolName 工具名称
     * @param array $arguments 参数
     * @return mixed 工具执行结果
     * @throws ToolException 当工具调用失败时
     */
    public function callTool(string $toolName, array $arguments = []): mixed
    {
        if (!isset($this->tools[$toolName])) {
            throw new ToolException("Tool not found: {$toolName}");
        }
        
        $tool = $this->tools[$toolName];
        
        try {
            $this->logger->info('Calling tool', [
                'name' => $toolName,
                'arguments' => $arguments
            ]);
            
            $result = call_user_func_array(
                [$tool['object'], $tool['method']],
                $this->prepareArguments($tool['parameters'], $arguments)
            );
            
            $this->logger->info('Tool executed successfully', [
                'name' => $toolName,
                'result_type' => gettype($result)
            ]);
            
            return $result;
            
        } catch (\Throwable $e) {
            $this->logger->error('Tool execution failed', [
                'name' => $toolName,
                'arguments' => $arguments,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new ToolException("Tool execution failed: {$e->getMessage()}", 0, $e);
        }
    }
    
    /**
     * 准备方法参数
     * 
     * @param array $parameters 参数定义
     * @param array $arguments 传入参数
     * @return array 准备好的参数
     */
    private function prepareArguments(array $parameters, array $arguments): array
    {
        $prepared = [];
        
        foreach ($parameters as $param) {
            $name = $param['name'];
            
            if (isset($arguments[$name])) {
                $prepared[] = $arguments[$name];
            } elseif ($param['required']) {
                throw new ToolException("Missing required parameter: {$name}");
            } else {
                $prepared[] = $param['default'];
            }
        }
        
        return $prepared;
    }
    
    /**
     * 列出所有工具
     * 
     * @return array 工具列表
     */
    public function listTools(): array
    {
        $tools = [];
        
        foreach ($this->tools as $name => $tool) {
            $tools[] = [
                'name' => $name,
                'description' => $tool['description'],
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => $this->buildProperties($tool['parameters']),
                    'required' => $this->getRequiredProperties($tool['parameters'])
                ]
            ];
        }
        
        return $tools;
    }
    
    /**
     * 构建属性定义
     * 
     * @param array $parameters 参数定义
     * @return array 属性定义
     */
    private function buildProperties(array $parameters): array
    {
        $properties = [];
        
        foreach ($parameters as $param) {
            $properties[$param['name']] = [
                'type' => $this->mapPhpTypeToJsonSchema($param['type']),
                'description' => $param['description']
            ];
            
            if (!$param['required']) {
                $properties[$param['name']]['default'] = $param['default'];
            }
        }
        
        return $properties;
    }
    
    /**
     * 获取必需属性
     * 
     * @param array $parameters 参数定义
     * @return array 必需属性列表
     */
    private function getRequiredProperties(array $parameters): array
    {
        $required = [];
        
        foreach ($parameters as $param) {
            if ($param['required']) {
                $required[] = $param['name'];
            }
        }
        
        return $required;
    }
    
    /**
     * 映射 PHP 类型到 JSON Schema 类型
     * 
     * @param string $phpType PHP 类型
     * @return string JSON Schema 类型
     */
    private function mapPhpTypeToJsonSchema(string $phpType): string
    {
        return match ($phpType) {
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
     * 检查工具是否存在
     * 
     * @param string $toolName 工具名称
     * @return bool 是否存在
     */
    public function hasTool(string $toolName): bool
    {
        return isset($this->tools[$toolName]);
    }
    
    /**
     * 获取工具信息
     * 
     * @param string $toolName 工具名称
     * @return array|null 工具信息
     */
    public function getTool(string $toolName): ?array
    {
        return $this->tools[$toolName] ?? null;
    }
    
    /**
     * 移除工具
     * 
     * @param string $toolName 工具名称
     */
    public function removeTool(string $toolName): void
    {
        if (isset($this->tools[$toolName])) {
            unset($this->tools[$toolName]);
            $this->logger->info('Tool removed', ['name' => $toolName]);
        }
    }
    
    /**
     * 清空所有工具
     */
    public function clearTools(): void
    {
        $this->tools = [];
        $this->logger->info('All tools cleared');
    }
    
    /**
     * 获取工具数量
     * 
     * @return int 工具数量
     */
    public function getToolCount(): int
    {
        return count($this->tools);
    }
}
