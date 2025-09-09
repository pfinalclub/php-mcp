<?php declare(strict_types=1);

/**
 * 文件操作工具
 * 
 * 提供文件系统操作功能，包括读取、写入、删除、列表等操作
 * 
 * @package PFPMcp\Examples\FileOperations
 */

namespace PFPMcp\Examples\FileOperations;

use PFPMcp\Attributes\McpTool;
use PFPMcp\Attributes\Schema;

/**
 * 文件操作工具类
 */
class FileOperations
{
    /**
     * 读取文件内容
     * 
     * @param string $filePath 文件路径
     * @return array 文件内容
     */
    #[McpTool(
        name: 'read_file',
        description: '读取文件内容'
    )]
    public function readFile(
        #[Schema(description: '要读取的文件路径')]
        string $filePath
    ): array {
        try {
            // 安全检查：防止路径遍历攻击
            $this->validateFilePath($filePath);
            
            if (!file_exists($filePath)) {
                return [
                    'success' => false,
                    'error' => "文件不存在: {$filePath}",
                    'timestamp' => time()
                ];
            }
            
            if (!is_readable($filePath)) {
                return [
                    'success' => false,
                    'error' => "文件不可读: {$filePath}",
                    'timestamp' => time()
                ];
            }
            
            $content = file_get_contents($filePath);
            $fileInfo = [
                'path' => $filePath,
                'size' => filesize($filePath),
                'modified' => filemtime($filePath),
                'permissions' => substr(sprintf('%o', fileperms($filePath)), -4)
            ];
            
            return [
                'success' => true,
                'content' => $content,
                'file_info' => $fileInfo,
                'timestamp' => time()
            ];
            
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => time()
            ];
        }
    }
    
    /**
     * 写入文件内容
     * 
     * @param string $filePath 文件路径
     * @param string $content 文件内容
     * @param bool $append 是否追加模式
     * @return array 操作结果
     */
    #[McpTool(
        name: 'write_file',
        description: '写入文件内容'
    )]
    public function writeFile(
        #[Schema(description: '要写入的文件路径')]
        string $filePath,
        #[Schema(description: '要写入的内容')]
        string $content,
        #[Schema(description: '是否追加模式，默认为覆盖模式')]
        bool $append = false
    ): array {
        try {
            // 安全检查
            $this->validateFilePath($filePath);
            
            // 确保目录存在
            $directory = dirname($filePath);
            if (!is_dir($directory)) {
                if (!mkdir($directory, 0755, true)) {
                    return [
                        'success' => false,
                        'error' => "无法创建目录: {$directory}",
                        'timestamp' => time()
                    ];
                }
            }
            
            $flags = $append ? FILE_APPEND | LOCK_EX : LOCK_EX;
            $result = file_put_contents($filePath, $content, $flags);
            
            if ($result === false) {
                return [
                    'success' => false,
                    'error' => "写入文件失败: {$filePath}",
                    'timestamp' => time()
                ];
            }
            
            return [
                'success' => true,
                'bytes_written' => $result,
                'file_path' => $filePath,
                'mode' => $append ? 'append' : 'overwrite',
                'timestamp' => time()
            ];
            
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => time()
            ];
        }
    }
    
    /**
     * 删除文件
     * 
     * @param string $filePath 文件路径
     * @return array 操作结果
     */
    #[McpTool(
        name: 'delete_file',
        description: '删除文件'
    )]
    public function deleteFile(
        #[Schema(description: '要删除的文件路径')]
        string $filePath
    ): array {
        try {
            // 安全检查
            $this->validateFilePath($filePath);
            
            if (!file_exists($filePath)) {
                return [
                    'success' => false,
                    'error' => "文件不存在: {$filePath}",
                    'timestamp' => time()
                ];
            }
            
            if (!is_writable($filePath)) {
                return [
                    'success' => false,
                    'error' => "文件不可写: {$filePath}",
                    'timestamp' => time()
                ];
            }
            
            $deleted = unlink($filePath);
            
            if (!$deleted) {
                return [
                    'success' => false,
                    'error' => "删除文件失败: {$filePath}",
                    'timestamp' => time()
                ];
            }
            
            return [
                'success' => true,
                'message' => "文件已删除: {$filePath}",
                'timestamp' => time()
            ];
            
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => time()
            ];
        }
    }
    
    /**
     * 列出目录内容
     * 
     * @param string $directoryPath 目录路径
     * @param bool $includeHidden 是否包含隐藏文件
     * @return array 目录内容
     */
    #[McpTool(
        name: 'list_directory',
        description: '列出目录内容'
    )]
    public function listDirectory(
        #[Schema(description: '要列出的目录路径')]
        string $directoryPath,
        #[Schema(description: '是否包含隐藏文件，默认为 false')]
        bool $includeHidden = false
    ): array {
        try {
            // 安全检查
            $this->validateFilePath($directoryPath);
            
            if (!is_dir($directoryPath)) {
                return [
                    'success' => false,
                    'error' => "目录不存在: {$directoryPath}",
                    'timestamp' => time()
                ];
            }
            
            if (!is_readable($directoryPath)) {
                return [
                    'success' => false,
                    'error' => "目录不可读: {$directoryPath}",
                    'timestamp' => time()
                ];
            }
            
            $items = [];
            $iterator = new \DirectoryIterator($directoryPath);
            
            foreach ($iterator as $item) {
                if ($item->isDot()) {
                    continue;
                }
                
                if (!$includeHidden && $item->getFilename()[0] === '.') {
                    continue;
                }
                
                $items[] = [
                    'name' => $item->getFilename(),
                    'type' => $item->isDir() ? 'directory' : 'file',
                    'size' => $item->isFile() ? $item->getSize() : null,
                    'modified' => $item->getMTime(),
                    'permissions' => substr(sprintf('%o', $item->getPerms()), -4)
                ];
            }
            
            // 按名称排序
            usort($items, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
            
            return [
                'success' => true,
                'directory' => $directoryPath,
                'items' => $items,
                'total_count' => count($items),
                'include_hidden' => $includeHidden,
                'timestamp' => time()
            ];
            
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => time()
            ];
        }
    }
    
    /**
     * 创建目录
     * 
     * @param string $directoryPath 目录路径
     * @param int $permissions 目录权限
     * @return array 操作结果
     */
    #[McpTool(
        name: 'create_directory',
        description: '创建目录'
    )]
    public function createDirectory(
        #[Schema(description: '要创建的目录路径')]
        string $directoryPath,
        #[Schema(description: '目录权限，默认为 0755')]
        int $permissions = 0755
    ): array {
        try {
            // 安全检查
            $this->validateFilePath($directoryPath);
            
            if (is_dir($directoryPath)) {
                return [
                    'success' => false,
                    'error' => "目录已存在: {$directoryPath}",
                    'timestamp' => time()
                ];
            }
            
            $created = mkdir($directoryPath, $permissions, true);
            
            if (!$created) {
                return [
                    'success' => false,
                    'error' => "创建目录失败: {$directoryPath}",
                    'timestamp' => time()
                ];
            }
            
            return [
                'success' => true,
                'message' => "目录已创建: {$directoryPath}",
                'permissions' => substr(sprintf('%o', $permissions), -4),
                'timestamp' => time()
            ];
            
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => time()
            ];
        }
    }
    
    /**
     * 获取文件信息
     * 
     * @param string $filePath 文件路径
     * @return array 文件信息
     */
    #[McpTool(
        name: 'get_file_info',
        description: '获取文件信息'
    )]
    public function getFileInfo(
        #[Schema(description: '文件路径')]
        string $filePath
    ): array {
        try {
            // 安全检查
            $this->validateFilePath($filePath);
            
            if (!file_exists($filePath)) {
                return [
                    'success' => false,
                    'error' => "文件不存在: {$filePath}",
                    'timestamp' => time()
                ];
            }
            
            $stat = stat($filePath);
            $pathInfo = pathinfo($filePath);
            
            return [
                'success' => true,
                'file_info' => [
                    'path' => $filePath,
                    'name' => $pathInfo['basename'],
                    'filename' => $pathInfo['filename'],
                    'extension' => $pathInfo['extension'] ?? null,
                    'directory' => $pathInfo['dirname'],
                    'size' => $stat['size'],
                    'type' => filetype($filePath),
                    'permissions' => substr(sprintf('%o', $stat['mode']), -4),
                    'owner' => $stat['uid'],
                    'group' => $stat['gid'],
                    'created' => $stat['ctime'],
                    'modified' => $stat['mtime'],
                    'accessed' => $stat['atime'],
                    'is_readable' => is_readable($filePath),
                    'is_writable' => is_writable($filePath),
                    'is_executable' => is_executable($filePath)
                ],
                'timestamp' => time()
            ];
            
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => time()
            ];
        }
    }
    
    /**
     * 复制文件
     * 
     * @param string $sourcePath 源文件路径
     * @param string $destinationPath 目标文件路径
     * @return array 操作结果
     */
    #[McpTool(
        name: 'copy_file',
        description: '复制文件'
    )]
    public function copyFile(
        #[Schema(description: '源文件路径')]
        string $sourcePath,
        #[Schema(description: '目标文件路径')]
        string $destinationPath
    ): array {
        try {
            // 安全检查
            $this->validateFilePath($sourcePath);
            $this->validateFilePath($destinationPath);
            
            if (!file_exists($sourcePath)) {
                return [
                    'success' => false,
                    'error' => "源文件不存在: {$sourcePath}",
                    'timestamp' => time()
                ];
            }
            
            if (!is_readable($sourcePath)) {
                return [
                    'success' => false,
                    'error' => "源文件不可读: {$sourcePath}",
                    'timestamp' => time()
                ];
            }
            
            // 确保目标目录存在
            $destinationDir = dirname($destinationPath);
            if (!is_dir($destinationDir)) {
                if (!mkdir($destinationDir, 0755, true)) {
                    return [
                        'success' => false,
                        'error' => "无法创建目标目录: {$destinationDir}",
                        'timestamp' => time()
                    ];
                }
            }
            
            $copied = copy($sourcePath, $destinationPath);
            
            if (!$copied) {
                return [
                    'success' => false,
                    'error' => "复制文件失败: {$sourcePath} -> {$destinationPath}",
                    'timestamp' => time()
                ];
            }
            
            return [
                'success' => true,
                'message' => "文件已复制: {$sourcePath} -> {$destinationPath}",
                'source' => $sourcePath,
                'destination' => $destinationPath,
                'size' => filesize($destinationPath),
                'timestamp' => time()
            ];
            
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => time()
            ];
        }
    }
    
    /**
     * 移动文件
     * 
     * @param string $sourcePath 源文件路径
     * @param string $destinationPath 目标文件路径
     * @return array 操作结果
     */
    #[McpTool(
        name: 'move_file',
        description: '移动文件'
    )]
    public function moveFile(
        #[Schema(description: '源文件路径')]
        string $sourcePath,
        #[Schema(description: '目标文件路径')]
        string $destinationPath
    ): array {
        try {
            // 安全检查
            $this->validateFilePath($sourcePath);
            $this->validateFilePath($destinationPath);
            
            if (!file_exists($sourcePath)) {
                return [
                    'success' => false,
                    'error' => "源文件不存在: {$sourcePath}",
                    'timestamp' => time()
                ];
            }
            
            // 确保目标目录存在
            $destinationDir = dirname($destinationPath);
            if (!is_dir($destinationDir)) {
                if (!mkdir($destinationDir, 0755, true)) {
                    return [
                        'success' => false,
                        'error' => "无法创建目标目录: {$destinationDir}",
                        'timestamp' => time()
                    ];
                }
            }
            
            $moved = rename($sourcePath, $destinationPath);
            
            if (!$moved) {
                return [
                    'success' => false,
                    'error' => "移动文件失败: {$sourcePath} -> {$destinationPath}",
                    'timestamp' => time()
                ];
            }
            
            return [
                'success' => true,
                'message' => "文件已移动: {$sourcePath} -> {$destinationPath}",
                'source' => $sourcePath,
                'destination' => $destinationPath,
                'timestamp' => time()
            ];
            
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => time()
            ];
        }
    }
    
    /**
     * 搜索文件
     * 
     * @param string $directoryPath 搜索目录
     * @param string $pattern 搜索模式
     * @param bool $recursive 是否递归搜索
     * @return array 搜索结果
     */
    #[McpTool(
        name: 'search_files',
        description: '搜索文件'
    )]
    public function searchFiles(
        #[Schema(description: '搜索目录路径')]
        string $directoryPath,
        #[Schema(description: '搜索模式，支持通配符 * 和 ?')]
        string $pattern,
        #[Schema(description: '是否递归搜索子目录，默认为 true')]
        bool $recursive = true
    ): array {
        try {
            // 安全检查
            $this->validateFilePath($directoryPath);
            
            if (!is_dir($directoryPath)) {
                return [
                    'success' => false,
                    'error' => "目录不存在: {$directoryPath}",
                    'timestamp' => time()
                ];
            }
            
            $files = [];
            $iterator = $recursive 
                ? new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directoryPath))
                : new \DirectoryIterator($directoryPath);
            
            foreach ($iterator as $item) {
                if ($item->isDot()) {
                    continue;
                }
                
                if ($item->isFile() && fnmatch($pattern, $item->getFilename())) {
                    $files[] = [
                        'path' => $item->getPathname(),
                        'name' => $item->getFilename(),
                        'size' => $item->getSize(),
                        'modified' => $item->getMTime(),
                        'permissions' => substr(sprintf('%o', $item->getPerms()), -4)
                    ];
                }
            }
            
            return [
                'success' => true,
                'directory' => $directoryPath,
                'pattern' => $pattern,
                'recursive' => $recursive,
                'files' => $files,
                'total_count' => count($files),
                'timestamp' => time()
            ];
            
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => time()
            ];
        }
    }
    
    /**
     * 验证文件路径安全性
     * 
     * @param string $filePath 文件路径
     * @throws \InvalidArgumentException 当路径不安全时
     */
    private function validateFilePath(string $filePath): void
    {
        // 检查路径遍历攻击
        if (strpos($filePath, '..') !== false) {
            throw new \InvalidArgumentException('路径包含不安全的字符: ..');
        }
        
        // 检查绝对路径
        if (strpos($filePath, '/') === 0 && !str_starts_with($filePath, '/tmp/') && !str_starts_with($filePath, '/var/tmp/')) {
            throw new \InvalidArgumentException('不允许访问系统目录');
        }
        
        // 检查路径长度
        if (strlen($filePath) > 255) {
            throw new \InvalidArgumentException('文件路径过长');
        }
        
        // 检查非法字符
        if (preg_match('/[<>:"|?*]/', $filePath)) {
            throw new \InvalidArgumentException('文件路径包含非法字符');
        }
    }
}
