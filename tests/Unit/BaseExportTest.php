<?php

declare(strict_types=1);

use Aoding9\Dcat\Xlswriter\Export\Tests\Stubs\TestExport;
use Illuminate\Support\Collection;

beforeEach(function () {
    $this->tempDir = sys_get_temp_dir();
});

describe('getColumn - 列索引转列名', function () {
    it('converts single digit column index to letter', function () {
        $export = createTestExport();

        expect($export->getColumn(0))->toBe('A')
            ->and($export->getColumn(1))->toBe('B')
            ->and($export->getColumn(25))->toBe('Z');
    });

    it('converts double digit column index to letters', function () {
        $export = createTestExport();

        expect($export->getColumn(26))->toBe('AA')
            ->and($export->getColumn(27))->toBe('AB')
            ->and($export->getColumn(51))->toBe('AZ')
            ->and($export->getColumn(52))->toBe('BA');
    });

    it('converts triple digit column index to letters', function () {
        $export = createTestExport();

        expect($export->getColumn(702))->toBe('AAA')
            ->and($export->getColumn(703))->toBe('AAB');
    });

    it('caches column names for performance', function () {
        $export = createTestExport();

        $export->getColumn(0);
        $export->getColumn(0);

        expect($export->columnMap)->toHaveKey(0)
            ->and($export->columnMap[0])->toBe('A');
    });
});

describe('getColumnIndexByName - 列名转列索引', function () {
    it('converts single letter to column index', function () {
        $export = createTestExport();

        expect($export->getColumnIndexByName('A'))->toBe(0)
            ->and($export->getColumnIndexByName('B'))->toBe(1)
            ->and($export->getColumnIndexByName('Z'))->toBe(25);
    });

    it('converts double letters to column index', function () {
        $export = createTestExport();

        expect($export->getColumnIndexByName('AA'))->toBe(26)
            ->and($export->getColumnIndexByName('AB'))->toBe(27)
            ->and($export->getColumnIndexByName('AZ'))->toBe(51)
            ->and($export->getColumnIndexByName('BA'))->toBe(52);
    });

    it('converts triple letters to column index', function () {
        $export = createTestExport();

        expect($export->getColumnIndexByName('AAA'))->toBe(702)
            ->and($export->getColumnIndexByName('AAB'))->toBe(703);
    });

    it('caches column index for performance', function () {
        $export = createTestExport();

        $export->getColumnIndexByName('A');
        $export->getColumnIndexByName('A');

        expect($export->columnIndexMap)->toHaveKey('A')
            ->and($export->columnIndexMap['A'])->toBe(0);
    });
});

describe('column conversion bidirectional', function () {
    it('converts back and forth correctly', function () {
        $export = createTestExport();

        foreach ([0, 1, 25, 26, 51, 52, 100, 702, 703] as $index) {
            $columnName = $export->getColumn($index);
            $backToIndex = $export->getColumnIndexByName($columnName);

            expect($backToIndex)->toBe($index);
        }
    });
});

describe('initDataSource - 数据源初始化', function () {
    it('initializes with array data source', function () {
        $data = [['id' => 1, 'name' => 'Test']];
        $export = createTestExport($data);

        expect($export->dataSourceType)->toBe('collection')
            ->and($export->getData())->toBeInstanceOf(Collection::class)
            ->and($export->getData()->count())->toBe(1)
            ->and($export->index)->toBe(1);
    });

    it('initializes with collection data source', function () {
        $data = collect([['id' => 1, 'name' => 'Test']]);
        $export = createTestExport($data);

        expect($export->dataSourceType)->toBe('collection')
            ->and($export->getData())->toBeInstanceOf(Collection::class);
    });

    it('initializes with null data source as other type', function () {
        $export = createTestExport(null);

        expect($export->dataSourceType)->toBe('other');
    });
});

describe('setData - 设置数据', function () {
    it('converts array to collection', function () {
        $export = createTestExport();
        $export->setData(['item1', 'item2']);

        expect($export->getData())->toBeInstanceOf(Collection::class)
            ->and($export->getData()->count())->toBe(2);
    });

    it('keeps collection as is', function () {
        $export = createTestExport();
        $collection = collect(['item1', 'item2']);
        $export->setData($collection);

        expect($export->getData())->toBe($collection);
    });

    it('returns self for chaining', function () {
        $export = createTestExport();
        $result = $export->setData([]);

        expect($result)->toBe($export);
    });
});

describe('setFilename - 设置文件名', function () {
    it('appends timestamp and extension to filename', function () {
        $export = createTestExport();
        $export->setFilename('test');

        expect($export->getFilename())->toMatch('/^test\d{14}\.xlsx$/');
    });

    it('returns self for chaining', function () {
        $export = createTestExport();
        $result = $export->setFilename('test');

        expect($result)->toBe($export);
    });
});

describe('getTmpDir - 临时目录', function () {
    it('returns a valid directory path', function () {
        $export = createTestExport();
        $tmpDir = $export->getTmpDir();

        expect(is_dir($tmpDir))->toBeTrue();
    });
});

describe('getStoreFilePath - 存储路径', function () {
    it('returns temp directory with trailing slash', function () {
        $export = createTestExport();
        $path = $export->getStoreFilePath();

        expect($path)->toEndWith('/');
    });
});

describe('setHeaderData - 设置表头数据', function () {
    it('includes title and header when useTitle is true', function () {
        $export = createTestExport();
        $export->useTitle = true;
        $export->setHeaderData();

        expect($export->headerData->count())->toBe(2)
            ->and($export->headerData[0])->toBe([$export->getTableTitle()]);
    });

    it('excludes title when useTitle is false', function () {
        $export = createTestExport();
        $export->useTitle = false;
        $export->setHeaderData();

        expect($export->headerData->count())->toBe(1);
    });

    it('returns self for chaining', function () {
        $export = createTestExport();
        $result = $export->setHeaderData();

        expect($result)->toBe($export);
    });
});

describe('useFreezePanes - 冻结设置', function () {
    it('enables freeze panes', function () {
        $export = createTestExport();
        $export->useFreezePanes(true);

        expect($export->useFreezePanes)->toBeTrue();
    });

    it('disables freeze panes', function () {
        $export = createTestExport();
        $export->useFreezePanes = true;
        $export->useFreezePanes(false);

        expect($export->useFreezePanes)->toBeFalse();
    });

    it('returns self for chaining', function () {
        $export = createTestExport();
        $result = $export->useFreezePanes(true);

        expect($result)->toBe($export);
    });
});

describe('shouldDelete - 删除设置', function () {
    it('sets shouldDelete flag', function () {
        $export = createTestExport();
        $export->shouldDelete(false);

        expect($export->shouldDelete)->toBeFalse();
    });

    it('keeps current value when null passed', function () {
        $export = createTestExport();
        $export->shouldDelete = false;
        $export->shouldDelete(null);

        expect($export->shouldDelete)->toBeFalse();
    });

    it('returns self for chaining', function () {
        $export = createTestExport();
        $result = $export->shouldDelete(true);

        expect($result)->toBe($export);
    });
});

describe('setter methods - 设置器方法', function () {
    it('sets font family', function () {
        $export = createTestExport();
        $export->setFontFamily('Arial');

        expect($export->fontFamily)->toBe('Arial');
    });

    it('sets header row height', function () {
        $export = createTestExport();
        $export->setHeaderRowHeight(50);

        expect($export->headerRowHeight)->toBe(50);
    });

    it('sets title row height', function () {
        $export = createTestExport();
        $export->setTitleRowHeight(60);

        expect($export->titleRowHeight)->toBe(60);
    });

    it('sets use title flag', function () {
        $export = createTestExport();
        $export->setUseTitle(false);

        expect($export->useTitle)->toBeFalse();
    });

    it('sets max export limit', function () {
        $export = createTestExport();
        $export->setMax(100000);

        expect($export->max)->toBe(100000);
    });

    it('sets chunk size', function () {
        $export = createTestExport();
        $export->setChunkSize(1000);

        expect($export->chunkSize)->toBe(1000);
    });

    it('sets debug mode', function () {
        $export = createTestExport();
        $export->setDebug(true);

        expect($export->debug)->toBeTrue();
    });

    it('all setters return self for chaining', function () {
        $export = createTestExport();

        expect($export->setFontFamily('Arial'))->toBe($export)
            ->and($export->setHeaderRowHeight(50))->toBe($export)
            ->and($export->setTitleRowHeight(60))->toBe($export)
            ->and($export->setUseTitle(false))->toBe($export)
            ->and($export->setMax(100000))->toBe($export)
            ->and($export->setChunkSize(1000))->toBe($export)
            ->and($export->setDebug(true))->toBe($export);
    });
});

describe('setSheet - 工作表设置', function () {
    it('sets sheet name', function () {
        $export = createTestExport();
        $export->setSheet('MySheet');

        expect($export->sheetName)->toBe('MySheet');
    });

    it('returns self for chaining', function () {
        $export = createTestExport();
        $result = $export->setSheet('MySheet');

        expect($result)->toBe($export);
    });
});

describe('setHeaderLen and setEnd - 表头长度和末尾列', function () {
    it('sets header length from header array', function () {
        $export = createTestExport();
        $export->setHeaderLen();

        expect($export->headerLen)->toBe(count($export->getHeader()));
    });

    it('sets custom header length', function () {
        $export = createTestExport();
        $export->setHeaderLen(10);

        expect($export->headerLen)->toBe(10);
    });

    it('sets end column based on header length', function () {
        $export = createTestExport();
        $export->setHeaderLen(3);
        $export->setEnd();

        expect($export->end)->toBe('C');
    });

    it('sets custom end column', function () {
        $export = createTestExport();
        $export->setEnd('Z');

        expect($export->end)->toBe('Z');
    });
});

describe('getCellName - 单元格名称', function () {
    it('generates correct cell name', function () {
        $export = createTestExport();

        expect($export->getCellName(1, 0))->toBe('A1')
            ->and($export->getCellName(1, 1))->toBe('B1')
            ->and($export->getCellName(10, 2))->toBe('C10');
    });
});

describe('getCurrentLine - 当前行', function () {
    it('returns line number starting from 1', function () {
        $export = createTestExport();
        $export->currentLine = 0;

        expect($export->getCurrentLine())->toBe(1);
    });

    it('returns correct line after increment', function () {
        $export = createTestExport();
        $export->currentLine = 5;

        expect($export->getCurrentLine())->toBe(6);
    });
});

describe('getIndex - 当前数据索引', function () {
    it('returns current index', function () {
        $export = createTestExport();
        $export->index = 10;

        expect($export->getIndex())->toBe(10);
    });
});

describe('mergeCellsAfterInsertData - 合并单元格', function () {
    it('returns merge config when useTitle is true', function () {
        $export = createTestExport();
        $export->useTitle = true;
        $export->end = 'C';
        $export->titleStyle = null;

        $result = $export->mergeCellsAfterInsertData();

        expect($result)->toBeArray()
            ->and($result[0]['range'])->toBe('A1:C1')
            ->and($result[0]['value'])->toBe($export->getTableTitle());
    });

    it('returns empty array when useTitle is false', function () {
        $export = createTestExport();
        $export->useTitle = false;

        $result = $export->mergeCellsAfterInsertData();

        expect($result)->toBeEmpty();
    });
});

describe('useSwoole - Swoole 设置', function () {
    it('returns current value when null passed', function () {
        $export = createTestExport();
        $export->useSwoole = true;

        expect($export->useSwoole(null))->toBeTrue();
    });

    it('returns passed value when boolean passed', function () {
        $export = createTestExport();
        $export->useSwoole = false;

        expect($export->useSwoole(true))->toBeTrue();
    });
});

describe('buildDataFromCollection - 集合数据构建', function () {
    it('returns paginated data from collection', function () {
        $data = collect(range(1, 100));
        $export = createTestExport($data);

        $result = $export->buildDataFromCollection(1, 10);

        expect($result->count())->toBe(10)
            ->and($result->first())->toBe(1);
    });

    it('returns correct page of data', function () {
        $data = collect(range(1, 100));
        $export = createTestExport($data);

        $result = $export->buildDataFromCollection(2, 10);

        expect($result->first())->toBe(11);
    });
});

describe('default values - 默认值', function () {
    it('has correct default values', function () {
        $export = createTestExport();

        expect($export->fontFamily)->toBe('微软雅黑')
            ->and($export->rowHeight)->toBe(40)
            ->and($export->headerRowHeight)->toBe(40)
            ->and($export->titleRowHeight)->toBe(50)
            ->and($export->useFreezePanes)->toBeFalse()
            ->and($export->useTitle)->toBeTrue()
            ->and($export->useGlobalStyle)->toBeTrue()
            ->and($export->max)->toBe(500000)
            ->and($export->chunkSize)->toBe(5000)
            ->and($export->debug)->toBeFalse()
            ->and($export->shouldDelete)->toBeTrue()
            ->and($export->useSwoole)->toBeFalse()
            ->and($export->sheetName)->toBe('Sheet1')
            ->and($export->currentLine)->toBe(0);
    });
});

// Helper function
function createTestExport($dataSource = []): TestExport
{
    return new TestExport($dataSource);
}
