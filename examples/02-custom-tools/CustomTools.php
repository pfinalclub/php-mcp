<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

namespace Examples\CustomTools;

use PhpMcp\Attributes\McpTool;
use PhpMcp\Attributes\Schema;

/**
 * 自定义工具示例
 * 
 * @package Examples\CustomTools
 */
class CustomTools
{
    /**
     * 字符串反转工具
     * 
     * @param string $text 要反转的文本
     * @return array 结果
     */
    #[McpTool(
        name: 'reverse_text',
        description: '反转字符串'
    )]
    public function reverseText(
        #[Schema(description: '要反转的文本')]
        string $text
    ): array {
        return [
            'success' => true,
            'result' => strrev($text),
            'original' => $text,
            'timestamp' => time()
        ];
    }
    
    /**
     * 获取当前时间工具
     * 
     * @param string $format 时间格式
     * @return array 结果
     */
    #[McpTool(
        name: 'get_current_time',
        description: '获取当前时间'
    )]
    public function getCurrentTime(
        #[Schema(description: '时间格式，默认为 Y-m-d H:i:s')]
        string $format = 'Y-m-d H:i:s'
    ): array {
        return [
            'success' => true,
            'result' => date($format),
            'format' => $format,
            'timestamp' => time()
        ];
    }
    
    /**
     * 生成随机数工具
     * 
     * @param int $min 最小值
     * @param int $max 最大值
     * @return array 结果
     */
    #[McpTool(
        name: 'generate_random_number',
        description: '生成指定范围内的随机数'
    )]
    public function generateRandomNumber(
        #[Schema(description: '最小值')]
        int $min = 1,
        #[Schema(description: '最大值')]
        int $max = 100
    ): array {
        if ($min > $max) {
            return [
                'success' => false,
                'error' => '最小值不能大于最大值',
                'min' => $min,
                'max' => $max
            ];
        }
        
        $result = rand($min, $max);
        
        return [
            'success' => true,
            'result' => $result,
            'range' => ['min' => $min, 'max' => $max],
            'timestamp' => time()
        ];
    }
    
    /**
     * 文本统计工具
     * 
     * @param string $text 要统计的文本
     * @return array 结果
     */
    #[McpTool(
        name: 'text_statistics',
        description: '统计文本信息'
    )]
    public function textStatistics(
        #[Schema(description: '要统计的文本')]
        string $text
    ): array {
        $charCount = strlen($text);
        $wordCount = str_word_count($text);
        $lineCount = substr_count($text, "\n") + 1;
        
        return [
            'success' => true,
            'result' => [
                'characters' => $charCount,
                'words' => $wordCount,
                'lines' => $lineCount,
                'text' => $text
            ],
            'timestamp' => time()
        ];
    }
}
