<div align="center">

# Dcat Xlswriter Export

<p>
    <a href="https://github.com/dcat-x/dcat-xlswriter-export/actions"><img src="https://github.com/dcat-x/dcat-xlswriter-export/actions/workflows/tests.yml/badge.svg" alt="Tests"></a>
    <a href="https://packagist.org/packages/dcat-x/dcat-xlswriter-export"><img src="https://poser.pugx.org/dcat-x/dcat-xlswriter-export/v/stable" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/dcat-x/dcat-xlswriter-export"><img src="https://img.shields.io/packagist/dt/dcat-x/dcat-xlswriter-export.svg" alt="Total Downloads"></a>
    <a href="https://www.php.net/"><img src="https://img.shields.io/badge/php-8.2+-59a9f8.svg" alt="PHP Version"></a>
    <a href="https://laravel.com/"><img src="https://img.shields.io/badge/laravel-12+-59a9f8.svg" alt="Laravel Version"></a>
    <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue.svg" alt="License"></a>
</p>

**基于 [xlswriter](https://xlswriter-docs.viest.me/) 的 [Dcat Admin](https://github.com/dcat-x/laravel-admin) 高性能 Excel 导出扩展**

[English](README.en.md) | 简体中文

</div>

## 特性

- 高性能、低内存占用（支持 50 万+ 行数据导出）
- 大数据集分块处理（默认 5000 条/块）
- 自定义样式（字体、颜色、边框、对齐方式）
- 单元格合并支持
- 冻结表头支持
- 多种数据源（Query Builder、Collection、Array、Dcat Grid）
- Swoole 兼容
- 生命周期钩子

## 环境要求

| 依赖 | 版本 |
|------|------|
| PHP | ^8.2 |
| Laravel | ^12.0 |
| Dcat Admin | ^1.0 |
| xlswriter 扩展 | * |

## 安装

### 1. 安装 xlswriter 扩展

参考官方文档：https://xlswriter-docs.viest.me/

```bash
# Linux
pecl install xlswriter

# 添加到 php.ini
extension=xlswriter.so
```

安装后通过 `php -m | grep xlswriter` 或 `phpinfo()` 验证。

### 2. 安装扩展包

```bash
composer require dcat-x/dcat-xlswriter-export
```

## 快速开始

### 1. 创建导出类

```php
<?php

namespace App\Admin\Exports;

use Aoding9\Dcat\Xlswriter\Export\BaseExport;

class UserExport extends BaseExport
{
    public $fileName = '用户列表';

    public $tableTitle = '用户数据';

    public $header = [
        ['column' => 'id', 'width' => 10, 'name' => 'ID'],
        ['column' => 'name', 'width' => 20, 'name' => '姓名'],
        ['column' => 'email', 'width' => 30, 'name' => '邮箱'],
        ['column' => 'created_at', 'width' => 20, 'name' => '创建时间'],
    ];

    public function eachRow($row): array
    {
        return [
            $row->id,
            $row->name,
            $row->email,
            $row->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
```

### 2. 在 Dcat Admin 控制器中使用

```php
use App\Admin\Exports\UserExport;

protected function grid()
{
    return Grid::make(new User(), function (Grid $grid) {
        $grid->export(new UserExport());

        // ... 其他配置
    });
}
```

## 高级用法

### 自定义数据源

```php
// 使用 Query Builder
$export = new UserExport(User::query()->where('active', true));

// 使用 Collection
$export = new UserExport(collect([
    ['id' => 1, 'name' => '张三'],
    ['id' => 2, 'name' => '李四'],
]));

// 使用数组
$export = new UserExport([
    ['id' => 1, 'name' => '张三'],
    ['id' => 2, 'name' => '李四'],
]);
```

### 配置选项

```php
class UserExport extends BaseExport
{
    // 基础配置
    public $fileName = '用户列表';      // 文件名（自动添加时间戳）
    public $tableTitle = '用户数据';    // 首行标题
    public $sheetName = 'Sheet1';       // 工作表名称

    // 样式设置
    public $fontFamily = '微软雅黑';    // 默认字体
    public $rowHeight = 40;             // 数据行高
    public $headerRowHeight = 40;       // 表头行高
    public $titleRowHeight = 50;        // 标题行高

    // 功能开关
    public $useTitle = true;            // 显示标题行
    public $useFreezePanes = false;     // 冻结表头
    public $useGlobalStyle = true;      // 应用全局样式

    // 性能配置
    public $chunkSize = 5000;           // 每块数据量
    public $max = 500000;               // 最大导出行数

    // 其他
    public $debug = false;              // 调试模式
    public $shouldDelete = true;        // 下载后删除临时文件
}
```

### 链式调用配置

```php
$export = (new UserExport())
    ->setFontFamily('Arial')
    ->setHeaderRowHeight(50)
    ->setTitleRowHeight(60)
    ->setMax(100000)
    ->setChunkSize(2000)
    ->useFreezePanes(true)
    ->setDebug(true);
```

### 单元格合并

重写 `mergeCellsAfterInsertData()` 方法定义合并规则：

```php
public function mergeCellsAfterInsertData(): array
{
    return [
        [
            'range' => 'A1:D1',
            'value' => $this->getTableTitle(),
            'formatHandle' => $this->titleStyle,
        ],
    ];
}
```

### 自定义样式

```php
class UserExport extends BaseExport
{
    protected function getSpecialStyle()
    {
        return (new \Vtiful\Kernel\Format($this->fileHandle))
            ->fontSize(12)
            ->font('微软雅黑')
            ->bold()
            ->italic()
            ->background(\Vtiful\Kernel\Format::COLOR_YELLOW)
            ->align(
                \Vtiful\Kernel\Format::FORMAT_ALIGN_CENTER,
                \Vtiful\Kernel\Format::FORMAT_ALIGN_VERTICAL_CENTER
            )
            ->border(\Vtiful\Kernel\Format::BORDER_THIN)
            ->toResource();
    }

    public function insertCellHandle($line, $column, $data, $format, $formatHandle)
    {
        // 根据条件应用不同样式
        if ($column === 2 && $data === '特殊值') {
            $formatHandle = $this->getSpecialStyle();
        }

        return $this->excel->insertText($line, $column, $data, $format, $formatHandle);
    }
}
```

### 生命周期钩子

```php
class UserExport extends BaseExport
{
    public function beforeInsertData()
    {
        // 数据插入前调用
        return $this;
    }

    public function afterInsertEachRowInEachChunk($rowData)
    {
        // 每行数据插入后调用（可用于动态合并单元格）
    }

    public function afterInsertData()
    {
        // 所有数据插入后调用
        return $this;
    }

    public function beforeOutput()
    {
        // 文件输出前调用
    }

    public function afterStore()
    {
        // 文件保存后调用
    }
}
```

### Swoole 支持

Swoole 环境下无法调用 `exit()`，需要开启 Swoole 模式：

```php
// 在导出类中设置
public $useSwoole = true;
```

然后在控制器中使用 trait：

```php
use Aoding9\Dcat\Xlswriter\Export\HandleExportIfUseSwoole;

class UserController extends AdminController
{
    use HandleExportIfUseSwoole;

    // ...
}
```

## API 参考

### BaseExport 属性

| 属性 | 类型 | 默认值 | 说明 |
|------|------|--------|------|
| `$fileName` | string | '文件名' | 导出文件名 |
| `$tableTitle` | string | '表名' | 首行标题 |
| `$header` | array | [] | 表头定义 |
| `$fontFamily` | string | '微软雅黑' | 默认字体 |
| `$rowHeight` | int | 40 | 数据行高 |
| `$headerRowHeight` | int | 40 | 表头行高 |
| `$titleRowHeight` | int | 50 | 标题行高 |
| `$useTitle` | bool | true | 是否显示标题行 |
| `$useFreezePanes` | bool | false | 是否冻结表头 |
| `$useGlobalStyle` | bool | true | 是否使用全局样式 |
| `$chunkSize` | int | 5000 | 分块大小 |
| `$max` | int | 500000 | 最大导出行数 |
| `$debug` | bool | false | 调试模式 |
| `$shouldDelete` | bool | true | 下载后删除文件 |
| `$useSwoole` | bool | false | Swoole 模式 |

### BaseExport 方法

| 方法 | 说明 |
|------|------|
| `eachRow($row): array` | **抽象方法**，定义数据到列的映射 |
| `export()` | 执行导出（保存 + 下载） |
| `store()` | 保存到临时文件 |
| `download($filePath)` | 下载文件 |
| `getColumn(int $index): string` | 列索引转字母（0 → A） |
| `getColumnIndexByName(string $name): int` | 列字母转索引（A → 0） |
| `mergeCellsAfterInsertData(): array` | 定义合并单元格规则 |

## 测试

```bash
composer test
```

## 代码风格

本扩展使用 [Laravel Pint](https://laravel.com/docs/pint) 进行代码格式化：

```bash
composer lint
```

## 更新日志

详见 [CHANGELOG](CHANGELOG.md)。

## 贡献指南

详见 [CONTRIBUTING](CONTRIBUTING.md)。

## 安全漏洞

请通过 [安全策略](../../security/policy) 报告安全漏洞。

## 致谢

- [aoding9](https://github.com/aoding9) - 原作者
- [cooper](https://github.com/myxiaoao)
- [所有贡献者](../../contributors)

## 开源协议

MIT 协议。详见 [LICENSE](LICENSE)。
