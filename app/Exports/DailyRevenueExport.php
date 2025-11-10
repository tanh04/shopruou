<?php
// app/Exports/DailyRevenueExport.php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DailyRevenueExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected Collection $rows,
        protected string $from,
        protected string $to
    ) {}

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            ["BÁO CÁO DOANH THU TỪ {$this->from} ĐẾN {$this->to}"],
            ['Ngày', 'Số đơn', 'Số lượng', 'Doanh thu (đ)', 'Lợi nhuận (đ)'],
        ];
    }

    public function map($row): array
    {
        return [
            $row->period,
            (int) $row->orders,
            (int) $row->quantity,
            (int) $row->sales,
            (int) $row->profit,
        ];
    }
}
