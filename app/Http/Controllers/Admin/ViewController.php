<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Barryvdh\DomPDF\Facade\Pdf;

// *** Thêm các use cần cho lớp Export ở cuối file ***
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ViewController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);
        $rows = $this->queryDaily($from, $to)->get();

        $totals = [
            'orders'   => (int) $rows->sum('orders'),
            'quantity' => (int) $rows->sum('quantity'),
            'sales'    => (int) $rows->sum('sales'),
            'profit'   => (int) $rows->sum('profit'),
        ];

        return view('admin.invoices.index', compact('rows', 'from', 'to', 'totals'));
    }

    /** Xuất báo cáo: xlsx | csv | pdf */
    public function export(Request $request, string $format)
    {
        [$from, $to] = $this->resolveRange($request);
        $rows = $this->queryDaily($from, $to)->get();

        // Dùng class Export được khai báo NGAY TRONG FILE NÀY (bên dưới)
        $export = new DailyRevenueStyledExport($rows, $from, $to);

        return match ($format) {
            'excel', 'xlsx' => Excel::download(
                $export, "bao_cao_{$from}_{$to}.xlsx", ExcelWriter::XLSX
            ),
            'pdf' => Pdf::loadView('admin.invoices.pdf', [
                'rows'   => $rows,
                'from'   => $from,
                'to'     => $to,
                'totals' => [
                    'orders'   => (int) $rows->sum('orders'),
                    'quantity' => (int) $rows->sum('quantity'),
                    'sales'    => (int) $rows->sum('sales'),
                    'profit'   => (int) $rows->sum('profit'),
                ],
            ])->setPaper('a4','portrait')->download("bao_cao_{$from}_{$to}.pdf"),
            default => abort(404),
        };
    }

    /** Gom theo ngày từ orders + order_items */
    protected function queryDaily(string $from, string $to)
    {
        $completed = $this->completedStatuses();

        return DB::table('orders as o')
            ->join('order_items as oi', 'oi.order_id', '=', 'o.order_id')
            ->leftJoin('products as p', 'p.product_id', '=', 'oi.product_id')
            ->whereIn('o.status', $completed)
            ->whereBetween('o.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->selectRaw("
                DATE(o.created_at) as period,
                COUNT(DISTINCT o.order_id) as orders,
                SUM(oi.quantity) as quantity,
                SUM(oi.quantity * oi.price) * 1.05 as sales,  -- doanh thu có thuế 5%
                SUM(oi.quantity * oi.price) * 1.05 - SUM(oi.quantity * COALESCE(p.cost_price,0)) as profit
            ")
            ->groupByRaw('DATE(o.created_at)')
            ->orderBy('period');
    }

    protected function completedStatuses(): array
    {
        if (defined(\App\Models\Order::class.'::STATUS_COMPLETED')) {
            return [\App\Models\Order::STATUS_COMPLETED];
        }
        return ['completed', 'hoan_thanh', 'Hoàn thành', 'Đã hoàn tất'];
    }

    protected function resolveRange(Request $request): array
    {
        $to   = $request->query('to')   ?: Carbon::now()->toDateString();
        $from = $request->query('from') ?: Carbon::now()->subDays(30)->toDateString();
        return [$from, $to];
    }
}

/**
 * ====== EXPORT CLASS: đặt chung file cho tiện ======
 * Không trùng tên với class khác trong project.
 */
class DailyRevenueStyledExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithEvents,
    WithColumnWidths,
    WithCustomStartCell,
    ShouldAutoSize,
    WithTitle
{
    protected Collection $rows;
    protected string $from;
    protected string $to;

    public function __construct(Collection $rows, string $from, string $to)
    {
        $this->rows = $rows->values();
        $this->from = $from;
        $this->to   = $to;
    }

    public function title(): string { return 'Báo cáo ngày'; }
    public function startCell(): string { return 'A4'; }

    public function collection(): Collection { return $this->rows; }

    public function headings(): array
    {
        return ['Date','Orders','Quantity','Sales (₫)','Profit (₫)'];
    }

    public function map($row): array
    {
        return [
            (string) $row->period,
            (int)    $row->orders,
            (int)    $row->quantity,
            (float)  $row->sales,
            (float)  $row->profit,
        ];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        // Tiêu đề lớn
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'BÁO CÁO DOANH THU & LỢI NHUẬN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Khoảng ngày
        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A2', "Khoảng: {$this->from} → {$this->to}");
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header bảng
        $sheet->getStyle('A4:E4')->getFont()->setBold(true);
        $sheet->getStyle('A4:E4')->getFill()->setFillType(Fill::FILL_SOLID)
              ->getStartColor()->setARGB('FFEFEFEF');
        $sheet->getStyle('A4:E4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    public function columnWidths(): array
    {
        return ['A'=>14,'B'=>10,'C'=>12,'D'=>18,'E'=>18];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet        = $event->sheet->getDelegate();
                $rowStart     = 4;
                $rowDataStart = 5;
                $rowEnd       = $rowDataStart + max(0, $this->rows->count() - 1);
                $lastRow      = $rowEnd;
                $hasRows      = $this->rows->count() > 0;

                // Tổng cuối
                if ($hasRows) {
                    $totalRow = $rowEnd + 1;
                    $sheet->setCellValue("A{$totalRow}", 'TOTAL');
                    $sheet->setCellValue("B{$totalRow}", "=SUM(B{$rowDataStart}:B{$rowEnd})");
                    $sheet->setCellValue("C{$totalRow}", "=SUM(C{$rowDataStart}:C{$rowEnd})");
                    $sheet->setCellValue("D{$totalRow}", "=SUM(D{$rowDataStart}:D{$rowEnd})");
                    $sheet->setCellValue("E{$totalRow}", "=SUM(E{$rowDataStart}:E{$rowEnd})");
                    $sheet->getStyle("A{$totalRow}:E{$totalRow}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$totalRow}:E{$totalRow}")->getFill()->setFillType(Fill::FILL_SOLID)
                          ->getStartColor()->setARGB('FFF7F7F7');
                    $lastRow = $totalRow;
                }

                // Freeze + filter
                $sheet->freezePane('A5');
                $sheet->setAutoFilter("A{$rowStart}:E{$lastRow}");

                // Căn lề + format số
                $sheet->getStyle("A{$rowDataStart}:A{$lastRow}")
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("B{$rowDataStart}:C{$lastRow}")
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("D{$rowDataStart}:E{$lastRow}")
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->getStyle("B{$rowDataStart}:C{$lastRow}")
                      ->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("D{$rowDataStart}:E{$lastRow}")
                      ->getNumberFormat()->setFormatCode('#,##0" đ"');

                // Viền
                $sheet->getStyle("A{$rowStart}:E{$lastRow}")
                      ->getBorders()->getAllBorders()
                      ->setBorderStyle(Border::BORDER_THIN)
                      ->getColor()->setARGB('FFBFBFBF');

                // Zebra
                for ($r = $rowDataStart; $r <= $rowEnd; $r++) {
                    if ($r % 2 === 0) {
                        $sheet->getStyle("A{$r}:E{$r}")
                              ->getFill()->setFillType(Fill::FILL_SOLID)
                              ->getStartColor()->setARGB('FFFDFDFD');
                    }
                }

                // Profit < 0 highlight
                if ($hasRows) {
                    $conditionalStyles = $sheet->getStyle("E{$rowDataStart}:E{$rowEnd}")
                                               ->getConditionalStyles();

                    $cond = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
                    $cond->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS)
                         ->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_LESSTHAN)
                         ->addCondition('0');
                    $cond->getStyle()->getFont()->getColor()->setARGB('FF9C0006');
                    $cond->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)
                         ->getStartColor()->setARGB('FFFFC7CE');

                    $conditionalStyles[] = $cond;
                    $sheet->getStyle("E{$rowDataStart}:E{$rowEnd}")
                          ->setConditionalStyles($conditionalStyles);
                }
            },
        ];
    }
}
