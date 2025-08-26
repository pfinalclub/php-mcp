# PHP 8.2+ 兼容性修复文档

## 问题描述

在 PHP 8.2+ 版本中，隐式可空类型弃用警告成为了一个重要的兼容性问题。当参数类型为可空类型但没有显式声明时，PHP 会发出弃用警告。

### 具体问题

1. **隐式可空类型弃用警告**：
   ```
   PFPMcp\EventHandler\EventHandler::off(): Implicitly marking parameter $listener as nullable is deprecated
   ```

2. **影响的方法**：
   - `EventHandler::off()` 方法中的 `callable $listener = null` 参数
   - `ServerException::__construct()` 方法中的 `\Throwable $previous = null` 参数

## 修复方案

### 1. 显式声明可空类型

将隐式可空类型改为显式可空类型声明：

#### 修复前
```php
public function off(string $event, callable $listener = null): void
public function __construct(string $message = '', int $code = 0, \Throwable $previous = null)
```

#### 修复后
```php
public function off(string $event, ?callable $listener = null): void
public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
```

### 2. 修复的文件

#### src/EventHandler/EventHandler.php
- **修复位置**：第 91 行
- **修复内容**：`callable $listener = null` → `?callable $listener = null`

#### src/Exceptions/ServerException.php
- **修复位置**：第 32 行
- **修复内容**：`\Throwable $previous = null` → `?\Throwable $previous = null`

## 兼容性说明

### PHP 版本支持

- **PHP 7.4+**：支持 `?callable` 和 `?\Throwable` 语法
- **PHP 8.0+**：完全支持所有可空类型语法
- **PHP 8.2+**：要求显式声明可空类型，避免弃用警告

### 向后兼容性

修复后的代码保持了完全的向后兼容性：
- 所有现有的调用方式仍然有效
- 不会破坏现有的 API 接口
- 保持了相同的功能行为

## 验证结果

### 语法检查
```bash
find src/ -name "*.php" -exec php -l {} \;
```

所有 PHP 文件通过语法检查，无语法错误。

### 测试建议

1. **单元测试**：确保修复后的方法行为与之前一致
2. **集成测试**：验证整个系统在 PHP 8.2+ 环境下的运行
3. **静态分析**：使用 PHPStan 等工具进行类型检查

## 最佳实践

### 1. 类型声明规范

- 对于可空参数，始终使用显式的可空类型声明
- 使用 `?` 前缀明确表示参数可以为 null
- 在 PHPDoc 注释中也使用 `@param type|null` 格式

### 2. 代码示例

```php
// ✅ 正确：显式可空类型
public function processData(string $data, ?callable $callback = null): void
{
    if ($callback !== null) {
        $callback($data);
    }
}

// ❌ 错误：隐式可空类型（PHP 8.2+ 弃用警告）
public function processData(string $data, callable $callback = null): void
{
    if ($callback !== null) {
        $callback($data);
    }
}
```

### 3. 异常处理

```php
// ✅ 正确：显式可空异常类型
public function __construct(
    string $message = '',
    int $code = 0,
    ?\Throwable $previous = null
) {
    parent::__construct($message, $code, $previous);
}
```

## 总结

通过显式声明可空类型，我们解决了 PHP 8.2+ 的兼容性问题，同时保持了代码的类型安全性和可读性。这种修复方式：

1. **消除了弃用警告**：符合 PHP 8.2+ 的新规范
2. **提高了类型安全性**：明确表达了参数的可空性
3. **保持了向后兼容性**：不影响现有代码的使用
4. **提升了代码质量**：更清晰的类型声明

建议在未来的开发中，始终使用显式的可空类型声明，以避免类似的兼容性问题。
