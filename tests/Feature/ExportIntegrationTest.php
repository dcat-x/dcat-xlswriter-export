<?php

declare(strict_types=1);

use Aoding9\Dcat\Xlswriter\Export\Tests\Stubs\TestExport;
use Illuminate\Support\Collection;

describe('Export Integration', function () {
    it('can create export instance with array data', function () {
        $data = [
            ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'],
            ['id' => 2, 'name' => 'Jane', 'email' => 'jane@example.com'],
        ];

        $export = new TestExport($data);

        expect($export)->toBeInstanceOf(TestExport::class)
            ->and($export->dataSourceType)->toBe('collection')
            ->and($export->getData()->count())->toBe(2);
    });

    it('can create export instance with collection data', function () {
        $data = collect([
            ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'],
        ]);

        $export = new TestExport($data);

        expect($export)->toBeInstanceOf(TestExport::class)
            ->and($export->dataSourceType)->toBe('collection');
    });

    it('can chain setter methods', function () {
        $export = new TestExport([]);

        $result = $export
            ->setFontFamily('Arial')
            ->setHeaderRowHeight(50)
            ->setTitleRowHeight(60)
            ->setUseTitle(false)
            ->useFreezePanes(true)
            ->setMax(100000)
            ->setChunkSize(1000)
            ->setDebug(true)
            ->setSheet('CustomSheet');

        expect($result)->toBe($export)
            ->and($export->fontFamily)->toBe('Arial')
            ->and($export->headerRowHeight)->toBe(50)
            ->and($export->titleRowHeight)->toBe(60)
            ->and($export->useTitle)->toBeFalse()
            ->and($export->useFreezePanes)->toBeTrue()
            ->and($export->max)->toBe(100000)
            ->and($export->chunkSize)->toBe(1000)
            ->and($export->debug)->toBeTrue()
            ->and($export->sheetName)->toBe('CustomSheet');
    });

    it('maps row data correctly through eachRow', function () {
        $export = new TestExport([]);
        $row = ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'];

        $result = $export->eachRow($row);

        expect($result)->toBe([1, 'John', 'john@example.com']);
    });

    it('handles missing row data gracefully', function () {
        $export = new TestExport([]);
        $row = ['id' => 1];

        $result = $export->eachRow($row);

        expect($result)->toBe([1, '', '']);
    });

    it('processes collection data in chunks', function () {
        $data = collect(range_map(1, 100, fn ($i) => [
            'id' => $i,
            'name' => "User $i",
            'email' => "user$i@example.com",
        ]));

        $export = new TestExport($data);
        $export->setChunkSize(10);

        $chunk1 = $export->buildDataFromCollection(1, 10);
        $chunk2 = $export->buildDataFromCollection(2, 10);

        expect($chunk1->count())->toBe(10)
            ->and($chunk2->count())->toBe(10)
            ->and($chunk1->first()['id'])->toBe(1)
            ->and($chunk2->first()['id'])->toBe(11);
    });

    it('correctly sets up header data with title', function () {
        $export = new TestExport([]);
        $export->useTitle = true;
        $export->setHeaderData();

        expect($export->headerData)->toBeInstanceOf(Collection::class)
            ->and($export->headerData->count())->toBe(2)
            ->and($export->headerData[0])->toBe(['测试表格标题'])
            ->and($export->headerData[1])->toBe(['ID', '名称', '邮箱']);
    });

    it('correctly sets up header data without title', function () {
        $export = new TestExport([]);
        $export->useTitle = false;
        $export->setHeaderData();

        expect($export->headerData->count())->toBe(1)
            ->and($export->headerData[0])->toBe(['ID', '名称', '邮箱']);
    });

    it('generates correct filename with timestamp', function () {
        $export = new TestExport([]);
        $beforeTime = date('YmdHis');

        $export->setFilename('report');

        $afterTime = date('YmdHis');
        $filename = $export->getFilename();

        expect($filename)->toStartWith('report')
            ->and($filename)->toEndWith('.xlsx')
            ->and(strlen($filename))->toBe(strlen('report') + 14 + 5);
    });

    it('calculates end column correctly', function () {
        $export = new TestExport([]);
        $export->setHeaderLen(); // Should be 3 based on header definition
        $export->setEnd();

        expect($export->headerLen)->toBe(3)
            ->and($export->end)->toBe('C');
    });

    it('generates merge cell config for title', function () {
        $export = new TestExport([]);
        $export->useTitle = true;
        $export->setHeaderLen();
        $export->setEnd();

        $mergeConfig = $export->mergeCellsAfterInsertData();

        expect($mergeConfig)->toHaveCount(1)
            ->and($mergeConfig[0]['range'])->toBe('A1:C1')
            ->and($mergeConfig[0]['value'])->toBe('测试表格标题');
    });

    it('returns empty merge config when no title', function () {
        $export = new TestExport([]);
        $export->useTitle = false;

        $mergeConfig = $export->mergeCellsAfterInsertData();

        expect($mergeConfig)->toBeEmpty();
    });
});

// Helper to create range as array
function range_map(int $start, int $end, callable $callback): array
{
    return array_map($callback, range($start, $end));
}
