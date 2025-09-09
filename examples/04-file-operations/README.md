# 文件操作示例

## 📋 概述

本示例演示如何使用 PFPMcp 创建文件系统操作工具，包括文件的读取、写入、删除、复制、移动等操作。

## 🛠️ 功能特性

### 支持的操作
- **读取文件** - 读取文件内容并获取文件信息
- **写入文件** - 写入内容到文件（支持覆盖和追加模式）
- **删除文件** - 安全删除文件
- **列出目录** - 列出目录内容（支持隐藏文件选项）
- **创建目录** - 创建新目录
- **获取文件信息** - 获取详细的文件信息
- **复制文件** - 复制文件到新位置
- **移动文件** - 移动文件到新位置
- **搜索文件** - 根据模式搜索文件

### 安全特性
- 路径遍历攻击防护
- 系统目录访问限制
- 文件权限检查
- 输入验证和清理

## 🚀 快速开始

### 1. 启动服务器

```bash
cd examples/04-file-operations
chmod +x server.php
./server.php
```

### 2. 测试工具

#### 读取文件
```json
{
  "jsonrpc": "2.0",
  "method": "tools/call",
  "params": {
    "name": "read_file",
    "arguments": {
      "filePath": "/tmp/test.txt"
    }
  },
  "id": 1
}
```

#### 写入文件
```json
{
  "jsonrpc": "2.0",
  "method": "tools/call",
  "params": {
    "name": "write_file",
    "arguments": {
      "filePath": "/tmp/test.txt",
      "content": "Hello, World!",
      "append": false
    }
  },
  "id": 2
}
```

#### 列出目录
```json
{
  "jsonrpc": "2.0",
  "method": "tools/call",
  "params": {
    "name": "list_directory",
    "arguments": {
      "directoryPath": "/tmp",
      "includeHidden": false
    }
  },
  "id": 3
}
```

## 📚 工具详细说明

### read_file
读取文件内容并返回文件信息。

**参数:**
- `filePath` (string) - 要读取的文件路径

**返回:**
```json
{
  "success": true,
  "content": "文件内容",
  "file_info": {
    "path": "/tmp/test.txt",
    "size": 1024,
    "modified": 1640995200,
    "permissions": "0644"
  },
  "timestamp": 1640995200
}
```

### write_file
写入内容到文件。

**参数:**
- `filePath` (string) - 要写入的文件路径
- `content` (string) - 要写入的内容
- `append` (boolean, 可选) - 是否追加模式，默认为 false

**返回:**
```json
{
  "success": true,
  "bytes_written": 1024,
  "file_path": "/tmp/test.txt",
  "mode": "overwrite",
  "timestamp": 1640995200
}
```

### delete_file
删除文件。

**参数:**
- `filePath` (string) - 要删除的文件路径

**返回:**
```json
{
  "success": true,
  "message": "文件已删除: /tmp/test.txt",
  "timestamp": 1640995200
}
```

### list_directory
列出目录内容。

**参数:**
- `directoryPath` (string) - 要列出的目录路径
- `includeHidden` (boolean, 可选) - 是否包含隐藏文件，默认为 false

**返回:**
```json
{
  "success": true,
  "directory": "/tmp",
  "items": [
    {
      "name": "test.txt",
      "type": "file",
      "size": 1024,
      "modified": 1640995200,
      "permissions": "0644"
    }
  ],
  "total_count": 1,
  "include_hidden": false,
  "timestamp": 1640995200
}
```

### create_directory
创建目录。

**参数:**
- `directoryPath` (string) - 要创建的目录路径
- `permissions` (integer, 可选) - 目录权限，默认为 0755

**返回:**
```json
{
  "success": true,
  "message": "目录已创建: /tmp/newdir",
  "permissions": "0755",
  "timestamp": 1640995200
}
```

### get_file_info
获取文件详细信息。

**参数:**
- `filePath` (string) - 文件路径

**返回:**
```json
{
  "success": true,
  "file_info": {
    "path": "/tmp/test.txt",
    "name": "test.txt",
    "filename": "test",
    "extension": "txt",
    "directory": "/tmp",
    "size": 1024,
    "type": "file",
    "permissions": "0644",
    "owner": 1000,
    "group": 1000,
    "created": 1640995200,
    "modified": 1640995200,
    "accessed": 1640995200,
    "is_readable": true,
    "is_writable": true,
    "is_executable": false
  },
  "timestamp": 1640995200
}
```

### copy_file
复制文件。

**参数:**
- `sourcePath` (string) - 源文件路径
- `destinationPath` (string) - 目标文件路径

**返回:**
```json
{
  "success": true,
  "message": "文件已复制: /tmp/test.txt -> /tmp/test_copy.txt",
  "source": "/tmp/test.txt",
  "destination": "/tmp/test_copy.txt",
  "size": 1024,
  "timestamp": 1640995200
}
```

### move_file
移动文件。

**参数:**
- `sourcePath` (string) - 源文件路径
- `destinationPath` (string) - 目标文件路径

**返回:**
```json
{
  "success": true,
  "message": "文件已移动: /tmp/test.txt -> /tmp/moved/test.txt",
  "source": "/tmp/test.txt",
  "destination": "/tmp/moved/test.txt",
  "timestamp": 1640995200
}
```

### search_files
搜索文件。

**参数:**
- `directoryPath` (string) - 搜索目录路径
- `pattern` (string) - 搜索模式，支持通配符 * 和 ?
- `recursive` (boolean, 可选) - 是否递归搜索子目录，默认为 true

**返回:**
```json
{
  "success": true,
  "directory": "/tmp",
  "pattern": "*.txt",
  "recursive": true,
  "files": [
    {
      "path": "/tmp/test.txt",
      "name": "test.txt",
      "size": 1024,
      "modified": 1640995200,
      "permissions": "0644"
    }
  ],
  "total_count": 1,
  "timestamp": 1640995200
}
```

## 🔒 安全注意事项

### 路径安全
- 防止路径遍历攻击（`../`）
- 限制访问系统目录
- 验证文件路径长度和字符

### 权限检查
- 检查文件读写权限
- 验证目录访问权限
- 确保操作安全性

### 输入验证
- 验证所有输入参数
- 清理恶意输入
- 防止注入攻击

## 🧪 测试示例

### 创建测试文件
```bash
echo "Hello, World!" > /tmp/test.txt
```

### 测试读取
```bash
echo '{"jsonrpc":"2.0","method":"tools/call","params":{"name":"read_file","arguments":{"filePath":"/tmp/test.txt"}},"id":1}' | ./server.php
```

### 测试写入
```bash
echo '{"jsonrpc":"2.0","method":"tools/call","params":{"name":"write_file","arguments":{"filePath":"/tmp/test2.txt","content":"New content"}},"id":2}' | ./server.php
```

### 测试列表
```bash
echo '{"jsonrpc":"2.0","method":"tools/call","params":{"name":"list_directory","arguments":{"directoryPath":"/tmp"}},"id":3}' | ./server.php
```

## 🚨 错误处理

### 常见错误
- **文件不存在** - 当尝试访问不存在的文件时
- **权限不足** - 当没有足够权限执行操作时
- **路径不安全** - 当文件路径包含不安全字符时
- **目录不存在** - 当目标目录不存在时

### 错误响应格式
```json
{
  "success": false,
  "error": "错误描述",
  "timestamp": 1640995200
}
```

## 📈 性能考虑

### 大文件处理
- 对于大文件，考虑使用流式处理
- 限制单次操作的文件大小
- 提供进度反馈

### 批量操作
- 支持批量文件操作
- 优化目录遍历性能
- 使用迭代器减少内存使用

## 🔗 相关链接

- [PFPMcp 主文档](../../docs/)
- [API 参考](../../docs/api-reference.md)
- [安全最佳实践](../../docs/deployment-best-practices.md)
- [故障排除指南](../../docs/troubleshooting.md)

---

**示例版本**: 1.0  
**创建日期**: 2025-01-27  
**维护者**: PFPMcp 开发团队
