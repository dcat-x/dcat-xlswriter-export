<?php

declare(strict_types=1);

use Aoding9\Dcat\Xlswriter\Export\Tests\Stubs\TestExport;
use Aoding9\Dcat\Xlswriter\Export\Tests\Stubs\TestExportWithFormat;

describe('getColumnFormat - 获取列格式配置', function () {
    it('returns format from header config', function () {
        $export = new TestExportWithFormat();

        // Column 2 (金额) has format '0.00'
        expect($export->getColumnFormat(2))->toBe('0.00');
        // Column 3 (数量) has format '#,##0'
        expect($export->getColumnFormat(3))->toBe('#,##0');
        // Column 4 (折扣率) has format '0.00%'
        expect($export->getColumnFormat(4))->toBe('0.00%');
    });

    it('returns null for columns without format config', function () {
        $export = new TestExportWithFormat();

        // Column 0 (ID) has no format
        expect($export->getColumnFormat(0))->toBeNull();
        // Column 1 (名称) has no format
        expect($export->getColumnFormat(1))->toBeNull();
    });

    it('returns null for non-existent column index', function () {
        $export = new TestExportWithFormat();

        expect($export->getColumnFormat(99))->toBeNull();
    });
});

describe('insertCellHandle - 数字格式自动处理', function () {
    it('uses format from header config when no format passed', function () {
        $export = new TestExportWithFormat();

        // Insert into column with format config
        $export->insertCell(0, 2, 10.50, null, null);

        expect($export->insertCellCalls)->toHaveCount(1);
        expect($export->insertCellCalls[0]['format'])->toBe('0.00');
        expect($export->insertCellCalls[0]['data'])->toBe(10.50);
    });

    it('prioritizes passed format over header config', function () {
        $export = new TestExportWithFormat();

        // Pass explicit format, should override header config
        $export->insertCell(0, 2, 10.50, '#,##0.00', null);

        expect($export->insertCellCalls[0]['format'])->toBe('#,##0.00');
    });

    it('auto-applies 0.00 format for float without header config', function () {
        $export = new TestExportWithFormat();

        // Column 0 has no format config, but data is float
        $export->insertCell(0, 0, 123.456, null, null);

        expect($export->insertCellCalls[0]['format'])->toBe('0.00');
    });

    it('does not auto-apply format for integer', function () {
        $export = new TestExportWithFormat();

        // Column 0 has no format config, data is integer
        $export->insertCell(0, 0, 123, null, null);

        expect($export->insertCellCalls[0]['format'])->toBeNull();
    });

    it('does not auto-apply format for string', function () {
        $export = new TestExportWithFormat();

        // Column 0 has no format config, data is string
        $export->insertCell(0, 0, 'hello', null, null);

        expect($export->insertCellCalls[0]['format'])->toBeNull();
    });

    it('does not auto-apply format for numeric string', function () {
        $export = new TestExportWithFormat();

        // Column 0 has no format config, data is numeric string
        $export->insertCell(0, 0, '123.45', null, null);

        expect($export->insertCellCalls[0]['format'])->toBeNull();
    });

    it('applies header format for integer data', function () {
        $export = new TestExportWithFormat();

        // Column 3 has '#,##0' format, integer data
        $export->insertCell(0, 3, 1000, null, null);

        expect($export->insertCellCalls[0]['format'])->toBe('#,##0');
    });

    it('applies header format for string data', function () {
        $export = new TestExportWithFormat();

        // Column 2 has '0.00' format, but data is string
        $export->insertCell(0, 2, '100.50', null, null);

        expect($export->insertCellCalls[0]['format'])->toBe('0.00');
    });
});

describe('insertCellHandle - format priority chain', function () {
    it('follows priority: passed format > header format > auto float format', function () {
        $export = new TestExportWithFormat();

        // Test 1: Passed format takes precedence
        $export->insertCell(0, 2, 10.5, 'custom', null);
        expect($export->insertCellCalls[0]['format'])->toBe('custom');

        // Test 2: Header format when no passed format
        $export->insertCellCalls = [];
        $export->insertCell(0, 2, 10.5, null, null);
        expect($export->insertCellCalls[0]['format'])->toBe('0.00'); // from header

        // Test 3: Auto format for float when no header/passed format
        $export->insertCellCalls = [];
        $export->insertCell(0, 0, 10.5, null, null); // column 0 has no header format
        expect($export->insertCellCalls[0]['format'])->toBe('0.00'); // auto

        // Test 4: No format for non-float when no header/passed format
        $export->insertCellCalls = [];
        $export->insertCell(0, 0, 10, null, null);
        expect($export->insertCellCalls[0]['format'])->toBeNull();
    });
});

describe('TestExport without format config', function () {
    it('returns null for all columns', function () {
        $export = new TestExport();

        expect($export->getColumnFormat(0))->toBeNull();
        expect($export->getColumnFormat(1))->toBeNull();
        expect($export->getColumnFormat(2))->toBeNull();
    });
});

describe('real-world scenarios - 实际场景测试', function () {
    it('handles money conversion (fen to yuan)', function () {
        $export = new TestExportWithFormat();

        // Simulate: amount stored as fen (integer), converted to yuan (float)
        $amountInFen = 1001; // 10.01 yuan
        $amountInYuan = $amountInFen / 100; // 10.01

        $export->insertCell(0, 2, $amountInYuan, null, null);

        expect($export->insertCellCalls[0]['data'])->toBe(10.01);
        expect($export->insertCellCalls[0]['format'])->toBe('0.00');
    });

    it('handles percentage rate', function () {
        $export = new TestExportWithFormat();

        // Discount rate: 0.15 means 15%
        $export->insertCell(0, 4, 0.15, null, null);

        expect($export->insertCellCalls[0]['data'])->toBe(0.15);
        expect($export->insertCellCalls[0]['format'])->toBe('0.00%');
    });

    it('handles quantity with thousand separator', function () {
        $export = new TestExportWithFormat();

        $export->insertCell(0, 3, 12345, null, null);

        expect($export->insertCellCalls[0]['data'])->toBe(12345);
        expect($export->insertCellCalls[0]['format'])->toBe('#,##0');
    });
});
