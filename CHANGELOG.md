# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0]

### Added
- 列数字格式配置：在 header 中通过 `format` 字段定义列的数字格式（如 `'0.00'`、`'#,##0'`、`'0.00%'`）
- 浮点数自动格式化：未配置格式的浮点数自动应用 `'0.00'` 格式，确保 Excel 正确识别数字类型
- 新增 `getColumnFormat()` 方法获取列格式配置
- 新增 `OrderExport` 示例类展示数字格式功能用法
- 新增 `ColumnFormatTest` 测试覆盖数字格式功能

### Changed
- `insertCellHandle()` 方法增强，支持格式优先级：传入格式 > header 配置格式 > 浮点数自动格式

## [1.0.0]

### Added
- 基于 xlswriter 扩展的高性能 Excel 导出
- 支持 50 万+ 行数据导出，低内存占用
- 大数据集分块处理
- 自定义样式（字体、颜色、边框、对齐方式）
- 单元格合并支持
- 冻结表头支持
- 多种数据源（Query Builder、Collection、Array、Dcat Grid）
- Swoole 兼容
- 生命周期钩子（beforeInsertData、afterInsertData 等）
- Pest 测试框架
- Laravel Pint 代码风格

### Requirements
- PHP ^8.2
- Laravel ^12.0
- dcat-x/laravel-admin ^1.0
- ext-xlswriter
