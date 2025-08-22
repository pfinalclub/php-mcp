#!/usr/bin/env php
<?php declare(strict_types=1);

/**
 * Author: PFinal南丞
 * Date: 2025/08/22
 * Email: <lampxiezi@gmail.com>
 */

/**
 * MCP 服务器测试脚本
 * 
 * 用于验证 MCP 服务器的配置和功能
 * 
 * 使用方法:
 * php examples/test-mcp.php | php server.php
 * 
 * 或者:
 * echo '{"jsonrpc":"2.0","id":1,"method":"tools/list","params":[]}' | php server.php
 */

// 测试消息列表
$tests = [
    // 1. 初始化测试
    [
        'name' => 'Initialize',
        'message' => [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
            'params' => [
                'protocolVersion' => '2024-11-05',
                'capabilities' => [
                    'tools' => [],
                    'resources' => [],
                    'prompts' => []
                ],
                'clientInfo' => [
                    'name' => 'test-client',
                    'version' => '1.0.0'
                ]
            ]
        ]
    ],
    
    // 2. 工具列表测试
    [
        'name' => 'Tools List',
        'message' => [
            'jsonrpc' => '2.0',
            'id' => 2,
            'method' => 'tools/list',
            'params' => []
        ]
    ],
    
    // 3. 计算器工具测试
    [
        'name' => 'Calculator Add',
        'message' => [
            'jsonrpc' => '2.0',
            'id' => 3,
            'method' => 'tools/call',
            'params' => [
                'name' => 'add',
                'arguments' => [
                    'a' => 10,
                    'b' => 20
                ]
            ]
        ]
    ],
    
    // 4. 计算器乘法测试
    [
        'name' => 'Calculator Multiply',
        'message' => [
            'jsonrpc' => '2.0',
            'id' => 4,
            'method' => 'tools/call',
            'params' => [
                'name' => 'multiply',
                'arguments' => [
                    'a' => 5,
                    'b' => 6
                ]
            ]
        ]
    ],
    
    // 5. 计算器除法测试
    [
        'name' => 'Calculator Divide',
        'message' => [
            'jsonrpc' => '2.0',
            'id' => 5,
            'method' => 'tools/call',
            'params' => [
                'name' => 'divide',
                'arguments' => [
                    'a' => 100,
                    'b' => 4
                ]
            ]
        ]
    ],
    
    // 6. 计算器幂运算测试
    [
        'name' => 'Calculator Power',
        'message' => [
            'jsonrpc' => '2.0',
            'id' => 6,
            'method' => 'tools/call',
            'params' => [
                'name' => 'power',
                'arguments' => [
                    'base' => 2,
                    'exponent' => 8
                ]
            ]
        ]
    ],
    
    // 7. 计算器平方根测试
    [
        'name' => 'Calculator Sqrt',
        'message' => [
            'jsonrpc' => '2.0',
            'id' => 7,
            'method' => 'tools/call',
            'params' => [
                'name' => 'sqrt',
                'arguments' => [
                    'number' => 16
                ]
            ]
        ]
    ],
    
    // 8. 资源列表测试
    [
        'name' => 'Resources List',
        'message' => [
            'jsonrpc' => '2.0',
            'id' => 8,
            'method' => 'resources/list',
            'params' => []
        ]
    ],
    
    // 9. 提示列表测试
    [
        'name' => 'Prompts List',
        'message' => [
            'jsonrpc' => '2.0',
            'id' => 9,
            'method' => 'prompts/list',
            'params' => []
        ]
    ],
    
    // 10. 错误测试 - 不存在的工具
    [
        'name' => 'Non-existent Tool',
        'message' => [
            'jsonrpc' => '2.0',
            'id' => 10,
            'method' => 'tools/call',
            'params' => [
                'name' => 'non_existent_tool',
                'arguments' => []
            ]
        ]
    ],
    
    // 11. 错误测试 - 无效参数
    [
        'name' => 'Invalid Parameters',
        'message' => [
            'jsonrpc' => '2.0',
            'id' => 11,
            'method' => 'tools/call',
            'params' => [
                'name' => 'add',
                'arguments' => [
                    'a' => 'invalid',
                    'b' => 20
                ]
            ]
        ]
    ]
];

// 检查命令行参数
$interactive = false;
$singleTest = null;

if ($argc > 1) {
    if ($argv[1] === '--interactive' || $argv[1] === '-i') {
        $interactive = true;
    } elseif ($argv[1] === '--test' || $argv[1] === '-t') {
        if (isset($argv[2])) {
            $singleTest = (int)$argv[2];
        }
    } elseif ($argv[1] === '--help' || $argv[1] === '-h') {
        echo "MCP 服务器测试脚本\n\n";
        echo "使用方法:\n";
        echo "  php examples/test-mcp.php                    # 运行所有测试\n";
        echo "  php examples/test-mcp.php --interactive      # 交互式测试\n";
        echo "  php examples/test-mcp.php --test 3           # 运行特定测试\n";
        echo "  php examples/test-mcp.php --help             # 显示帮助\n\n";
        echo "示例:\n";
        echo "  php examples/test-mcp.php | php server.php\n";
        echo "  php examples/test-mcp.php -i | php server.php\n";
        exit(0);
    }
}

// 交互式模式
if ($interactive) {
    echo "=== MCP 服务器交互式测试 ===\n";
    echo "输入 'quit' 退出，'help' 显示帮助\n\n";
    
    while (true) {
        echo "MCP> ";
        $input = trim(fgets(STDIN));
        
        if ($input === 'quit' || $input === 'exit') {
            break;
        }
        
        if ($input === 'help') {
            echo "可用命令:\n";
            echo "  tools/list     - 列出所有工具\n";
            echo "  tools/call     - 调用工具 (格式: tools/call tool_name param1=value1 param2=value2)\n";
            echo "  resources/list - 列出所有资源\n";
            echo "  prompts/list   - 列出所有提示\n";
            echo "  quit/exit      - 退出\n";
            echo "  help           - 显示帮助\n\n";
            continue;
        }
        
        // 解析命令
        $parts = explode(' ', $input);
        $command = $parts[0] ?? '';
        
        switch ($command) {
            case 'tools/list':
                $message = [
                    'jsonrpc' => '2.0',
                    'id' => time(),
                    'method' => 'tools/list',
                    'params' => []
                ];
                break;
                
            case 'tools/call':
                if (count($parts) < 2) {
                    echo "错误: 请指定工具名称\n";
                    continue 2;
                }
                
                $toolName = $parts[1];
                $arguments = [];
                
                // 解析参数
                for ($i = 2; $i < count($parts); $i++) {
                    if (strpos($parts[$i], '=') !== false) {
                        [$key, $value] = explode('=', $parts[$i], 2);
                        $arguments[$key] = $value;
                    }
                }
                
                $message = [
                    'jsonrpc' => '2.0',
                    'id' => time(),
                    'method' => 'tools/call',
                    'params' => [
                        'name' => $toolName,
                        'arguments' => $arguments
                    ]
                ];
                break;
                
            case 'resources/list':
                $message = [
                    'jsonrpc' => '2.0',
                    'id' => time(),
                    'method' => 'resources/list',
                    'params' => []
                ];
                break;
                
            case 'prompts/list':
                $message = [
                    'jsonrpc' => '2.0',
                    'id' => time(),
                    'method' => 'prompts/list',
                    'params' => []
                ];
                break;
                
            default:
                echo "未知命令: {$command}\n";
                echo "输入 'help' 查看可用命令\n";
                continue 2;
        }
        
        // 发送消息
        echo json_encode($message) . "\n";
    }
    
    exit(0);
}

// 运行测试
if ($singleTest !== null) {
    if (isset($tests[$singleTest])) {
        $tests = [$tests[$singleTest]];
    } else {
        echo "错误: 测试 {$singleTest} 不存在\n";
        exit(1);
    }
}

// 输出测试消息
foreach ($tests as $index => $test) {
    if ($singleTest === null) {
        echo "# 测试 " . ($index + 1) . ": {$test['name']}\n";
    }
    echo json_encode($test['message']) . "\n";
    
    // 在测试之间添加小延迟
    if ($singleTest === null && $index < count($tests) - 1) {
        usleep(100000); // 100ms
    }
}

echo "# 测试完成\n";
