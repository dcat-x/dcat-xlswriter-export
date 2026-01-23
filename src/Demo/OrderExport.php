<?php

namespace Aoding9\Dcat\Xlswriter\Export\Demo;

use Aoding9\Dcat\Xlswriter\Export\BaseExport;

/**
 * 订单导出示例 - 展示数字格式功能
 *
 * 在 header 中通过 'format' 字段定义列的数字格式：
 * - '0.00'      两位小数
 * - '#,##0'     整数带千分位
 * - '#,##0.00'  两位小数带千分位
 * - '0.00%'     百分比格式
 */
class OrderExport extends BaseExport
{
    public $header = [
        ['column' => 'a', 'width' => 8, 'name' => '序号'],
        ['column' => 'b', 'width' => 15, 'name' => '订单号'],
        ['column' => 'c', 'width' => 12, 'name' => '金额', 'format' => '0.00'],
        ['column' => 'd', 'width' => 12, 'name' => '手续费', 'format' => '0.00'],
        ['column' => 'e', 'width' => 10, 'name' => '数量', 'format' => '#,##0'],
        ['column' => 'f', 'width' => 10, 'name' => '折扣率', 'format' => '0.00%'],
        ['column' => 'g', 'width' => 20, 'name' => '创建时间'],
    ];

    public $fileName = '订单导出表';

    public $tableTitle = '订单导出表';

    public function eachRow($row)
    {
        return [
            $this->index,
            $row->order_no,
            $row->amount / 100,       // 分转元，自动应用 '0.00' 格式
            $row->fee / 100,          // 分转元，自动应用 '0.00' 格式
            $row->quantity,           // 自动应用 '#,##0' 格式
            $row->discount_rate,      // 自动应用 '0.00%' 格式
            $row->created_at->toDateTimeString(),
        ];
    }
}
