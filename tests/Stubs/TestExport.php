<?php

declare(strict_types=1);

namespace Aoding9\Dcat\Xlswriter\Export\Tests\Stubs;

use Aoding9\Dcat\Xlswriter\Export\BaseExport;
use Vtiful\Kernel\Excel;

class TestExport extends BaseExport
{
    public $header = [
        ['column' => 'id', 'width' => 10, 'name' => 'ID'],
        ['column' => 'name', 'width' => 20, 'name' => '名称'],
        ['column' => 'email', 'width' => 30, 'name' => '邮箱'],
    ];

    public $fileName = '测试导出';

    public $tableTitle = '测试表格标题';

    /**
     * Override constructor to avoid xlswriter dependency in tests
     */
    public function __construct($dataSource = null, $time = null)
    {
        // Skip parent constructor to avoid xlswriter dependency
        // Just initialize necessary properties
        if ($this->debug) {
            $this->time = $time ?? microtime(true);
        }
        $this->initDataSource($dataSource);
    }

    /**
     * Override init to skip Excel initialization
     */
    public function init($dataSource = null)
    {
        $this->setConfig()
            ->initDataSource($dataSource);

        return $this;
    }

    /**
     * Override newExcel to skip Excel instantiation
     */
    public function newExcel($config)
    {
        // Don't create actual Excel instance in tests
        return $this;
    }

    public function eachRow($row): array
    {
        return [
            $row['id'] ?? $row->id ?? '',
            $row['name'] ?? $row->name ?? '',
            $row['email'] ?? $row->email ?? '',
        ];
    }

    /**
     * Expose getColumnFormat for testing
     */
    public function getColumnFormat(int $column): ?string
    {
        return parent::getColumnFormat($column);
    }
}
