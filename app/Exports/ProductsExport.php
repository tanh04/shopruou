<?php
// app/Exports/ProductsExport.php
namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\{FromQuery, WithHeadings, WithMapping, ShouldAutoSize};
use Maatwebsite\Excel\Concerns\Exportable;

class ProductsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    public function __construct(
        public ?int $categoryId = null,
        public ?int $brandId = null,
        public ?string $status = null,
    ) {}

    public function query()
    {
        return Product::query()
            ->with(['category:category_id,category_name','brand:brand_id,brand_name'])
            ->when($this->categoryId, fn($q)=>$q->where('category_id',$this->categoryId))
            ->when($this->brandId, fn($q)=>$q->where('brand_id',$this->brandId))
            ->when($this->status !== null && $this->status !== '',
                fn($q)=>$q->where('product_status',(int)$this->status)
            )
            ->orderBy('product_id');
    }

    public function headings(): array
    {
        return [
            'product_id',
            'product_name',
            'category_id',
            'category_name',
            'brand_id',
            'brand_name',
            'product_capacity',
            'product_stock',
            'cost_price',      // giá nhập (nếu có)
            'product_price',   // giá bán
            'product_status',  // 1/0
            'product_image',   // tên file/đường dẫn (nếu có)
            'product_description',
            'created_at',
            'updated_at',
        ];
    }

    public function map($p): array
    {
        return [
            $p->product_id,
            $p->product_name,
            $p->category_id,
            optional($p->category)->category_name,
            $p->brand_id,
            optional($p->brand)->brand_name,
            $p->product_capacity,
            $p->product_stock,
            $p->cost_price,
            $p->product_price,
            $p->product_status,
            $p->product_image,
            $p->product_description,
            optional($p->created_at)?->format('Y-m-d H:i:s'),
            optional($p->updated_at)?->format('Y-m-d H:i:s'),
        ];
    }
}
