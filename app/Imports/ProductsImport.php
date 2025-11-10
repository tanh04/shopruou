<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsFailures; // -> failures()
use Maatwebsite\Excel\Concerns\SkipsErrors;   // -> errors()

class ProductsImport implements
    OnEachRow,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    SkipsOnError,
    SkipsEmptyRows,
    WithUpserts,
    WithChunkReading
{
    use Importable, SkipsFailures, SkipsErrors;

    /** Đếm số bản ghi */
    public int $inserted = 0;
    public int $updated  = 0;

    /** Cột dùng để upsert (nếu có) */
    public function uniqueBy()
    {
        // Import sẽ cập nhật nếu file có cột product_id trùng với DB
        return 'product_id';
    }

    /** Kích thước chunk (đọc file lớn) */
    public function chunkSize(): int
    {
        return 500;
    }

    /** Xử lý từng dòng */
    public function onRow(Row $row): void
    {
        // Lấy dữ liệu và normalize
        $raw = collect($row->toArray())->map(function ($v) {
            return is_string($v) ? trim($v) : $v;
        });

        // Map theo header (yêu cầu file có hàng tiêu đề):
        // product_id (optional), product_name, category_id, brand_id,
        // cost_price, product_price, product_capacity, product_stock, product_status, product_image (optional)
        $data = [
            'product_name'     => (string) $raw->get('product_name'),
            'category_id'      => self::toInt($raw->get('category_id')),
            'brand_id'         => self::toInt($raw->get('brand_id')),
            'cost_price'       => self::toNumber($raw->get('cost_price')),
            'product_price'    => self::toNumber($raw->get('product_price')),
            'product_capacity' => $raw->get('product_capacity') !== null ? (string)$raw->get('product_capacity') : null,
            'product_stock'    => self::toInt($raw->get('product_stock'), 0),
            'product_status'   => self::toStatus($raw->get('product_status', 1)),
            'product_image'    => $raw->get('product_image') ?: null,
        ];

        $productId = self::toInt($raw->get('product_id'));

        if ($productId) {
            // Update theo product_id nếu tồn tại
            $p = Product::where('product_id', $productId)->first();
            if ($p) {
                $p->fill($data)->save();
                $this->updated++;
                return;
            }
        }

        // Tạo mới
        $p = new Product($data);
        $p->save();
        $this->inserted++;
    }

    /** Validate mỗi dòng */
    public function rules(): array
    {
        return [
            'product_name'   => ['required','string','max:255'],
            'category_id'    => ['nullable','integer'],
            'brand_id'       => ['nullable','integer'],
            'cost_price'     => ['nullable','numeric','min:0'],
            'product_price'  => ['required','numeric','min:0'],
            'product_stock'  => ['nullable','integer','min:0'],
            'product_status' => ['nullable','in:0,1,ẩn,an,hiển thị,hien thi,show,hide,true,false,yes,no,on,off'],
        ];
    }

    public function customValidationAttributes(): array
    {
        return [
            'product_name'  => 'Tên sản phẩm',
            'category_id'   => 'Danh mục',
            'brand_id'      => 'Thương hiệu',
            'cost_price'    => 'Giá nhập',
            'product_price' => 'Giá bán',
            'product_stock' => 'Tồn kho',
            'product_status'=> 'Trạng thái',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'required' => ':attribute là bắt buộc.',
            'numeric'  => ':attribute phải là số.',
            'integer'  => ':attribute phải là số nguyên.',
            'min'      => ':attribute phải ≥ :min.',
            'in'       => ':attribute không hợp lệ.',
            'max'      => ':attribute tối đa :max ký tự.',
        ];
    }

    /* ================= Helpers ================ */

    /** Ép int (nhận cả chuỗi "1,000") */
    public static function toInt($v, ?int $default = null): ?int
    {
        if ($v === null || $v === '') return $default;
        if (is_numeric($v)) return (int)$v;
        $v = preg_replace('/[^\d\-]/', '', (string)$v);
        return $v === '' ? $default : (int)$v;
    }

    /** Ép số (nhận "1.234.567", "1,234,567.89") */
    public static function toNumber($v, float $default = 0.0): float
    {
        if ($v === null || $v === '') return $default;
        if (is_numeric($v)) return (float)$v;

        // chuẩn hóa: bỏ dấu chấm/ngăn cách nghìn, đổi , thành .
        $s = str_replace([' ', "\xC2\xA0"], '', (string)$v); // bỏ space và nbsp
        $s = str_replace(['.', ','], ['','.' ], $s);         // "1.234,56" -> "1234.56"
        return is_numeric($s) ? (float)$s : $default;
    }

    /** Chuẩn hóa trạng thái: chấp nhận 1/0, true/false, hiển thị/ẩn, on/off */
    public static function toStatus($v): int
    {
        if ($v === null || $v === '') return 1;
        $val = Str::lower(trim((string)$v));

        $truthy = ['1','true','yes','on','hiển thị','hien thi','show'];
        $falsy  = ['0','false','no','off','ẩn','an','hide'];

        if (in_array($val, $truthy, true)) return 1;
        if (in_array($val, $falsy,  true)) return 0;

        // fallback
        return (int) (bool) $v;
    }
}
