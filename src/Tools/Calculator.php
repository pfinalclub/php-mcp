<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

namespace PFPMcp\Tools;

use PFPMcp\Attributes\McpTool;
use PFPMcp\Attributes\Schema;

/**
 * 计算器工具
 * 
 * 提供基本的数学计算功能
 * 
 * @package PFPMcp\Tools
 */
class Calculator
{
    /**
     * 执行数学计算
     * 
     * @param string $expression 数学表达式
     * @return array 计算结果
     */
    #[McpTool(
        name: 'calculate',
        description: '执行数学计算，支持基本的四则运算'
    )]
    public function calculate(
        #[Schema(description: '要计算的数学表达式，如 2 + 3 * 4')]
        string $expression
    ): array {
        try {
            // 验证表达式安全性
            $this->validateExpression($expression);
            
            // 计算结果
            $result = eval("return {$expression};");
            
            return [
                'success' => true,
                'result' => $result,
                'expression' => $expression,
                'timestamp' => time()
            ];
            
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'expression' => $expression,
                'timestamp' => time()
            ];
        }
    }
    
    /**
     * 计算两个数的和
     * 
     * @param float $a 第一个数
     * @param float $b 第二个数
     * @return array 计算结果
     */
    #[McpTool(
        name: 'add',
        description: '计算两个数的和'
    )]
    public function add(
        #[Schema(description: '第一个数')]
        float $a,
        #[Schema(description: '第二个数')]
        float $b
    ): array {
        $result = $a + $b;
        
        return [
            'success' => true,
            'result' => $result,
            'operation' => 'add',
            'operands' => [$a, $b],
            'timestamp' => time()
        ];
    }
    
    /**
     * 计算两个数的差
     * 
     * @param float $a 第一个数
     * @param float $b 第二个数
     * @return array 计算结果
     */
    #[McpTool(
        name: 'subtract',
        description: '计算两个数的差'
    )]
    public function subtract(
        #[Schema(description: '第一个数')]
        float $a,
        #[Schema(description: '第二个数')]
        float $b
    ): array {
        $result = $a - $b;
        
        return [
            'success' => true,
            'result' => $result,
            'operation' => 'subtract',
            'operands' => [$a, $b],
            'timestamp' => time()
        ];
    }
    
    /**
     * 计算两个数的积
     * 
     * @param float $a 第一个数
     * @param float $b 第二个数
     * @return array 计算结果
     */
    #[McpTool(
        name: 'multiply',
        description: '计算两个数的积'
    )]
    public function multiply(
        #[Schema(description: '第一个数')]
        float $a,
        #[Schema(description: '第二个数')]
        float $b
    ): array {
        $result = $a * $b;
        
        return [
            'success' => true,
            'result' => $result,
            'operation' => 'multiply',
            'operands' => [$a, $b],
            'timestamp' => time()
        ];
    }
    
    /**
     * 计算两个数的商
     * 
     * @param float $a 第一个数
     * @param float $b 第二个数
     * @return array 计算结果
     */
    #[McpTool(
        name: 'divide',
        description: '计算两个数的商'
    )]
    public function divide(
        #[Schema(description: '第一个数')]
        float $a,
        #[Schema(description: '第二个数')]
        float $b
    ): array {
        if ($b == 0) {
            return [
                'success' => false,
                'error' => 'Division by zero is not allowed',
                'operation' => 'divide',
                'operands' => [$a, $b],
                'timestamp' => time()
            ];
        }
        
        $result = $a / $b;
        
        return [
            'success' => true,
            'result' => $result,
            'operation' => 'divide',
            'operands' => [$a, $b],
            'timestamp' => time()
        ];
    }
    
    /**
     * 计算幂运算
     * 
     * @param float $base 底数
     * @param float $exponent 指数
     * @return array 计算结果
     */
    #[McpTool(
        name: 'power',
        description: '计算幂运算'
    )]
    public function power(
        #[Schema(description: '底数')]
        float $base,
        #[Schema(description: '指数')]
        float $exponent
    ): array {
        $result = pow($base, $exponent);
        
        return [
            'success' => true,
            'result' => $result,
            'operation' => 'power',
            'operands' => [$base, $exponent],
            'timestamp' => time()
        ];
    }
    
    /**
     * 计算平方根
     * 
     * @param float $number 要计算平方根的数
     * @return array 计算结果
     */
    #[McpTool(
        name: 'sqrt',
        description: '计算平方根'
    )]
    public function sqrt(
        #[Schema(description: '要计算平方根的数')]
        float $number
    ): array {
        if ($number < 0) {
            return [
                'success' => false,
                'error' => 'Cannot calculate square root of negative number',
                'operation' => 'sqrt',
                'operand' => $number,
                'timestamp' => time()
            ];
        }
        
        $result = sqrt($number);
        
        return [
            'success' => true,
            'result' => $result,
            'operation' => 'sqrt',
            'operand' => $number,
            'timestamp' => time()
        ];
    }
    
    /**
     * 验证表达式安全性
     * 
     * @param string $expression 表达式
     * @throws \InvalidArgumentException 当表达式不安全时
     */
    private function validateExpression(string $expression): void
    {
        // 只允许数字、运算符、括号和空格
        if (!preg_match('/^[\d\s\+\-\*\/\(\)\.]+$/', $expression)) {
            throw new \InvalidArgumentException('Expression contains invalid characters');
        }
        
        // 检查括号匹配
        $openBrackets = substr_count($expression, '(');
        $closeBrackets = substr_count($expression, ')');
        
        if ($openBrackets !== $closeBrackets) {
            throw new \InvalidArgumentException('Unmatched brackets in expression');
        }
        
        // 检查连续运算符
        if (preg_match('/[\+\-\*\/]{2,}/', $expression)) {
            throw new \InvalidArgumentException('Invalid consecutive operators');
        }
    }
}
