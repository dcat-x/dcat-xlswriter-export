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

</div>

## 特性

- 高性能、低内存占用（支持 50 万+ 行数据导出）
- 大数据集分块处理
- 自定义样式（字体、颜色、边框、对齐方式）
- 单元格合并
- 冻结表头
- 多种数据源（Query Builder、Collection、Array、Dcat Grid）
- Swoole 兼容

## 环境要求

- PHP ^8.2
- Laravel ^12.0
- [xlswriter PHP 扩展](https://xlswriter-docs.viest.me/)
- dcat-x/laravel-admin ^1.0

## 安装

### 1. 安装 xlswriter 扩展

参考官方文档：https://xlswriter-docs.viest.me/

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
    public $fileName = '用户列表';
    public $tableTitle = '用户数据';

    // 样式设置
    public $fontFamily = '微软雅黑';
    public $rowHeight = 40;
    public $headerRowHeight = 40;
    public $titleRowHeight = 50;

    // 功能开关
    public $useTitle = true;           // 显示标题行
    public $useFreezePanes = false;    // 冻结表头
    public $useGlobalStyle = true;     // 应用全局样式

    // 性能配置
    public $chunkSize = 5000;          // 每块数据量
    public $max = 500000;              // 最大导出行数

    // 调试模式
    public $debug = false;             // 开启调试输出
}
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
        // 每行数据插入后调用
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

- [aoding9](https://github.com/aoding9)
- [cooper](https://github.com/myxiaoao)
- [所有贡献者](../../contributors)

## 开源协议

MIT 协议。详见 [LICENSE](LICENSE)。
