<?php

declare(strict_types=1);

namespace Aoding9\Dcat\Xlswriter\Export\Tests\Stubs;

/**
 * Test stub with format configuration for number format tests
 */
class TestExportWithFormat extends TestExport
{
    /**
     * Disable global style to avoid Format dependency in tests
     */
    public $useGlobalStyle = false;

    public $header = [
        ['column' => 'a', 'width' => 10, 'name' => 'ID'],
        ['column' => 'b', 'width' => 20, 'name' => '名称'],
        ['column' => 'c', 'width' => 15, 'name' => '金额', 'format' => '0.00'],
        ['column' => 'd', 'width' => 15, 'name' => '数量', 'format' => '#,##0'],
        ['column' => 'e', 'width' => 15, 'name' => '折扣率', 'format' => '0.00%'],
    ];

    public $fileName = '格式测试导出';

    public $tableTitle = '格式测试表格';

    /**
     * Track calls to insertCellHandle for testing
     */
    public array $insertCellCalls = [];

    /**
     * Override insertCellHandle to apply format logic and track calls without actual Excel operations
     */
    public function insertCellHandle($currentLine, $column, $data, $format, $formatHandle)
    {
        // Apply the same format logic as parent
        if ($format === null) {
            $format = $this->getColumnFormat($column);
        }

        if ($format === null && is_float($data)) {
            $format = '0.00';
        }

        // Track the call with processed format
        $this->insertCellCalls[] = [
            'line' => $currentLine,
            'column' => $column,
            'data' => $data,
            'format' => $format,
            'formatHandle' => $formatHandle,
        ];

        return $this;
    }

    public function eachRow($row): array
    {
        return [
            $row['id'] ?? '',
            $row['name'] ?? '',
            $row['amount'] ?? 0.0,
            $row['quantity'] ?? 0,
            $row['discount'] ?? 0.0,
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
