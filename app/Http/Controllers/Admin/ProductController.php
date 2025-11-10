<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ProductImage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;
use App\Exports\ProductsTemplateExport;
use App\Imports\ProductsImport;
class ProductController extends Controller
{
       /**
     * Display a listing of the resource.
     */

    public function all_products(Request $request)
    {
        // Eager load đúng khóa (không dùng 'id' vì bảng của bạn dùng PK là *_id)
        $query = Product::with([
            'category:category_id,category_name',
            'brand:brand_id,brand_name',
        ]);

        // --- TÌM KIẾM TỪ KHÓA ---
        if ($s = trim($request->input('s', ''))) {
            $query->where(function ($q) use ($s) {
                $q->where('product_name', 'like', "%{$s}%")
                ->orWhere('product_capacity', 'like', "%{$s}%")
                ->orWhereHas('category', fn($qc) => $qc->where('category_name', 'like', "%{$s}%"))
                ->orWhereHas('brand',    fn($qb) => $qb->where('brand_name',    'like', "%{$s}%"));
            });
        }

        // --- LỌC CATEGORY / BRAND (nếu có) ---
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // --- SẮP XẾP + PHÂN TRANG 10/SP ---
        $all_products = $query->orderByDesc('product_id')
            ->paginate(10)
            ->withQueryString(); // giữ ?s=&category_id=&brand_id= khi bấm trang

        // Dữ liệu dropdown (nếu bạn dùng)
        $allCategories = Category::select('category_id','category_name')->orderBy('category_name')->get();
        $allBrands     = Brand::select('brand_id','brand_name')->orderBy('brand_name')->get();

        return view('admin.all_products', compact('all_products', 'allCategories', 'allBrands'));
    }


    public function create_product()
    {
        // Lấy tất cả brands và categories từ database
        $brands = Brand::all(); 
        $categories = Category::all(); 

        // Truyền dữ liệu sang view
        return view('admin.create_product', compact('brands', 'categories'));
    }

        public function save_product(Request $request)
    {
        // Validate dữ liệu
        $request->validate([
            'product_name'        => 'required|string|max:255',
            'product_description' => 'required|string',
            'alcohol_percent'       => 'required|numeric',
            'grape_variety'        => 'required|string|max:255',
            'cost_price'       => 'required|numeric',
            'product_price'       => 'required|numeric',
            'product_capacity'    => 'required|numeric',
            'product_stock'       => 'required|numeric',
            'category_id'         => 'required|exists:categories,category_id',
            'brand_id'            => 'required|exists:brands,brand_id',
            'product_image'       => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'product_status'      => 'required|in:0,1',
            'promo_price' => 'nullable|numeric|min:0|lt:product_price',
            'promo_start' => 'nullable|date',
            'promo_end'   => 'nullable|date|after:promo_start',

            // Ảnh phụ
            'sub_images'          => 'array',
            'sub_images.*'        => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Upload ảnh chính
        $image_name = null;
        if ($request->hasFile('product_image')) {
            $image = $request->file('product_image');
            $image_name = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME)
                        . '_' . time()
                        . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/products'), $image_name);
        }

        // Tạo sản phẩm
        $product = Product::create([
            'product_name'        => $request->input('product_name'),
            'product_description' => $request->input('product_description'),
            'alcohol_percent'     => $request->input('alcohol_percent'),
            'grape_variety'        => $request->input('grape_variety'),
            'cost_price'          => $request->input('cost_price'),
            'product_price'       => $request->input('product_price'),
            'product_capacity'    => $request->input('product_capacity'),
            'product_stock'       => $request->input('product_stock'),
            'category_id'         => $request->input('category_id'),
            'brand_id'            => $request->input('brand_id'),
            'product_image'       => $image_name,
            'product_status'      => $request->input('product_status', 1),
            'promo_price'         => $request->promo_price,
            'promo_start'         => $request->promo_price,
            'promo_end'           => $request->promo_end,
        ]);

        // Ảnh phụ: loại trùng theo nội dung (hash) trong cùng batch
        if ($request->hasFile('sub_images')) {
            $batchHashes = []; // tránh trùng nhau trong cùng lần upload
            $i = 0;

            foreach ($request->file('sub_images') as $img) {
                $tmpPath = $img->getPathname();
                $hash = @sha1_file($tmpPath);
                if ($hash === false) { continue; }

                // đã thấy trong batch?
                if (in_array($hash, $batchHashes, true)) {
                    continue;
                }

                // Tạo tên file và lưu
                $name = Str::slug(pathinfo($img->getClientOriginalName(), PATHINFO_FILENAME))
                    . '_' . time() . '_' . $i
                    . '.' . $img->getClientOriginalExtension();

                $img->move(public_path('uploads/products'), $name);

                ProductImage::create([
                    'product_id' => $product->product_id,
                    'image_path' => $name,
                    'sort_order' => $i++,
                ]);

                $batchHashes[] = $hash;
            }
        }

        return redirect('create-product')->with('message', 'Thêm sản phẩm thành công!');
    }

    public function active_product($product_id)
    {
        $product = Product::findOrFail($product_id);

        $category = Category::find($product->category_id);
        $brand = Brand::find($product->brand_id);

        if ($category && $category->category_status == 0 && $brand && $brand->brand_status == 0) {
            return redirect('all-products')->with('error', 'Không thể kích hoạt sản phẩm vì danh mục và thương hiệu đang bị hủy kích hoạt!');
        }

        if ($category && $category->category_status == 0) {
            return redirect('all-products')->with('error', 'Không thể kích hoạt sản phẩm vì danh mục đang bị hủy kích hoạt!');
        }

        if ($brand && $brand->brand_status == 0) {
            return redirect('all-products')->with('error', 'Không thể kích hoạt sản phẩm vì thương hiệu đang bị hủy kích hoạt!');
        }


        $product->product_status = 1;
        $product->save();

        return redirect('all-products')->with('message', 'Đã kích hoạt sản phẩm!');
    }


    public function unactive_product($product_id)
    {
        $product = product::findOrFail($product_id);
        $product->product_status = 0;
        $product->save();

        return redirect('all-products')->with('message', 'Đã hủy kích hoạt sản phẩm!');
    }

    public function show_product($product_id)
    {
        $categories = Category::where('category_status', 1)->get();
        $brands = Brand::where('brand_status', 1)->get();

        // Eager load images
        $product = Product::with('images')
            ->where('product_id', $product_id)
            ->where('product_status', 1)
            ->first();

        if (!$product) abort(404);

        $category = Category::find($product->category_id);
        $brand = Brand::find($product->brand_id);

        $related_products = Product::where('category_id', $product->category_id)
            ->where('product_id', '!=', $product_id)
            ->where('product_status', 1)
            ->take(6)->get();

        return view('pages.product.show_product', compact(
            'categories','brands','product','category','brand','related_products'
        ));
    }

    public function edit_product($product_id)
    {
        $brands = Brand::all(); 
        $categories = Category::all(); 
        $product = Product::findOrFail($product_id);

        // Truyền tất cả dữ liệu sang view
        return view('admin.edit_product', compact('product', 'brands', 'categories'));
    }


    public function update_product(Request $request, $product_id)
    {
        $product = Product::findOrFail($product_id);

        // Validate dữ liệu
        $request->validate([
            'product_name'        => 'required|string|max:255',
            'product_description' => 'required|string',
            'alcohol_percent'       => 'required|numeric',
            'grape_variety'        => 'required|string|max:255',
            'cost_price'       => 'required|numeric',
            'product_price'       => 'required|numeric',
            'product_capacity'    => 'required|numeric',
            'product_stock'       => 'required|numeric',
            'category_id'         => 'required|exists:categories,category_id',
            'brand_id'            => 'required|exists:brands,brand_id',
            'product_image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'product_status'      => 'required|in:0,1',
            'promo_price' => 'nullable|numeric|min:0|lt:product_price',
            'promo_start' => 'nullable|date',
            'promo_end'   => 'nullable|date|after:promo_start',

            // Ảnh phụ + điều khiển giữ/xoá/sắp xếp
            'sub_images'          => 'array',
            'sub_images.*'        => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'keep_ids'            => 'array',
            'orders'              => 'array',
        ]);

        // 1) Ảnh chính (nếu tải mới)
        if ($request->hasFile('product_image')) {
            $image = $request->file('product_image');
            $image_name = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/products'), $image_name);

            // Xoá ảnh cũ nếu có
            if ($product->product_image && file_exists(public_path('uploads/products/'.$product->product_image))) {
                @unlink(public_path('uploads/products/'.$product->product_image));
            }
            $product->product_image = $image_name;
        }

        // 2) Update các trường còn lại
        $product->product_name        = $request->input('product_name');
        $product->product_description = $request->input('product_description');
        $product->alcohol_percent     = $request->input('alcohol_percent');
        $product->grape_variety       = $request->input('grape_variety');
        $product->cost_price          = $request->input('cost_price');
        $product->product_price       = $request->input('product_price');
        $product->product_capacity    = $request->input('product_capacity');
        $product->product_stock       = $request->input('product_stock');
        $product->category_id         = $request->input('category_id');
        $product->brand_id            = $request->input('brand_id');
        $product->product_status      = $request->input('product_status');
        $product->promo_price         = $request->input('promo_price');
        $product->promo_start         = $request->input('promo_start');
        $product->promo_end           = $request->input('promo_end');
        $product->save();

        // 3) Ảnh phụ: giữ / xoá / sắp xếp
        $keepIds = collect((array)$request->input('keep_ids', []))->map(fn($v)=>(int)$v)->all();
        $orders  = (array)$request->input('orders', []);

        // Xoá ảnh phụ không giữ
        $currentImages = ProductImage::where('product_id', $product->product_id)->get();
        foreach ($currentImages as $img) {
            if (!in_array($img->id, $keepIds, true)) {
                $path = public_path('uploads/products/'.$img->image_path);
                if ($img->image_path && file_exists($path)) {
                    @unlink($path);
                }
                $img->delete();
            }
        }

        // Cập nhật sort_order cho ảnh còn giữ
        if (!empty($keepIds)) {
            $kept = ProductImage::where('product_id', $product->product_id)->whereIn('id', $keepIds)->get();
            foreach ($kept as $img) {
                if (isset($orders[$img->id])) {
                    $img->sort_order = (int)$orders[$img->id];
                    $img->save();
                }
            }
        }

        // 4) Thêm ảnh phụ mới, loại trùng theo nội dung (hash) so với ảnh đã có + trong batch
        if ($request->hasFile('sub_images')) {
            // Tập hash của ảnh hiện có (DB)
            $existingHashes = [];
            $existing = ProductImage::where('product_id', $product->product_id)->get();

            // hash ảnh phụ đã lưu
            foreach ($existing as $img) {
                $path = public_path('uploads/products/'.$img->image_path);
                if (file_exists($path)) {
                    $h = @sha1_file($path);
                    if ($h !== false) $existingHashes[] = $h;
                }
            }
            // (tuỳ chọn) cũng tránh trùng với ảnh chính
            if ($product->product_image) {
                $mainPath = public_path('uploads/products/'.$product->product_image);
                if (file_exists($mainPath)) {
                    $mh = @sha1_file($mainPath);
                    if ($mh !== false) $existingHashes[] = $mh;
                }
            }

            // Hash trong batch để tránh trùng nhau trong cùng lần upload
            $batchHashes = [];
            $startOrder = (int)(ProductImage::where('product_id', $product->product_id)->max('sort_order') ?? 0) + 1;

            foreach ($request->file('sub_images') as $offset => $img) {
                $tmpPath = $img->getPathname();
                $hash = @sha1_file($tmpPath);
                if ($hash === false) { continue; }

                // Nếu nội dung đã tồn tại ở DB hoặc đã thấy trong batch -> bỏ qua
                if (in_array($hash, $existingHashes, true) || in_array($hash, $batchHashes, true)) {
                    continue;
                }

                // Lưu file
                $name = Str::slug(pathinfo($img->getClientOriginalName(), PATHINFO_FILENAME))
                    . '_' . time() . '_' . $offset
                    . '.' . $img->getClientOriginalExtension();
                $img->move(public_path('uploads/products'), $name);

                ProductImage::create([
                    'product_id' => $product->product_id,
                    'image_path' => $name,
                    'sort_order' => $startOrder + $offset,
                ]);

                $batchHashes[] = $hash;
            }
        }

        return redirect('all-products')->with('message', 'Cập nhật sản phẩm thành công!');
    }

    public function delete_product($product_id)
    {
        $product = Product::findOrFail($product_id);

        // Xoá file ảnh phụ trước (DB sẽ cascade nhưng file cần tự xoá)
        foreach ($product->images as $img) {
            $path = public_path('uploads/products/'.$img->image_path);
            if ($img->image_path && file_exists($path)) {
                @unlink($path);
            }
        }

        // Xoá file ảnh chính
        if ($product->product_image && file_exists(public_path('uploads/products/'.$product->product_image))) {
            @unlink(public_path('uploads/products/'.$product->product_image));
        }

        $product->delete();
        return redirect('all-products')->with('message', 'Xóa sản phẩm thành công!');
    }

    // Lọc theo khoảng giá
    public function filter_price(Request $request, $categoryId = null, $brandId = null)
    {
        // Lấy dữ liệu cho sidebar (chỉ brand/category đang active)
        $categories = Category::where('category_status', 1)->get();
        $brands     = Brand::where('brand_status', 1)->get();

        // Base query: chỉ sản phẩm đang active
        $base = Product::query()
            ->where('product_status', 1)
            ->when($categoryId ?? $request->input('category_id'), function ($q, $catId) {
                $q->where('category_id', $catId);
            })
            ->when($brandId ?? $request->input('brand_id'), function ($q, $brId) {
                $q->where('brand_id', $brId);
            })
            ->when($search = $request->input('q'), function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%");
            });

        // Các khoảng giá cố định
        $priceRanges = [
            ['key' => 'lt_500k',  'label' => 'Dưới 500K',             'min' => null,      'max' => 500_000],
            ['key' => '500k_1m',  'label' => 'Từ 500K - 1 triệu',     'min' => 500_000,   'max' => 1_000_000],
            ['key' => '1m_2m',    'label' => 'Từ 1 triệu - 2 triệu',  'min' => 1_000_000, 'max' => 2_000_000],
            ['key' => '2m_3m',    'label' => 'Từ 2 triệu - 3 triệu',  'min' => 2_000_000, 'max' => 3_000_000],
            ['key' => '3m_5m',    'label' => 'Từ 3 triệu - 5 triệu',  'min' => 3_000_000, 'max' => 5_000_000],
            ['key' => '5m_7m',    'label' => 'Từ 5 triệu - 7 triệu',  'min' => 5_000_000, 'max' => 7_000_000],
            ['key' => '7m_10m',   'label' => 'Từ 7 triệu - 10 triệu', 'min' => 7_000_000, 'max' => 10_000_000],
            ['key' => '10m_15m',  'label' => 'Từ 10 triệu - 15 triệu','min'=>10_000_000,  'max' => 15_000_000],
            ['key' => '15m_20m',  'label' => 'Từ 15 triệu - 20 triệu','min'=>15_000_000,  'max' => 20_000_000],
            ['key' => 'gt_20m',   'label' => 'Trên 20 triệu',         'min' => 20_000_000,'max' => null],
        ];

        // Đếm số sản phẩm theo từng khoảng (dựa trên base, KHÔNG áp dụng lọc giá hiện tại)
        $rangeCounts = [];
        foreach ($priceRanges as $r) {
            $q = (clone $base);
            if (!is_null($r['min'])) $q->where('product_price', '>=', $r['min']);
            if (!is_null($r['max'])) $q->where('product_price', '<',  $r['max']); // [min, max)
            $rangeCounts[$r['key']] = $q->count();
        }

        // Áp dụng lọc theo các khoảng người dùng chọn (có thể nhiều)
        $selectedKeys = (array) $request->input('ranges', []);
        $productsQ = (clone $base);

        if (!empty($selectedKeys)) {
            $byKey = collect($priceRanges)->keyBy('key');
            $productsQ->where(function ($q) use ($selectedKeys, $byKey) {
                foreach ($selectedKeys as $key) {
                    if (!$byKey->has($key)) continue;
                    $r = $byKey[$key];
                    $q->orWhere(function ($qq) use ($r) {
                        if (!is_null($r['min'])) $qq->where('product_price', '>=', $r['min']);
                        if (!is_null($r['max'])) $qq->where('product_price', '<',  $r['max']);
                    });
                }
            });
        }

        // Lấy danh sách sản phẩm
        $products = $productsQ
            ->orderByDesc('product_id')
            ->paginate(12)
            ->appends($request->query());

        // TODO: đổi view dưới đây thành view danh sách mà bạn đang dùng
        return view('pages.product.filter_price', [
            'categories'  => $categories,
            'brands'      => $brands,
            'products'    => $products,
            'priceRanges' => $priceRanges,
            'rangeCounts' => $rangeCounts,
            'selectedKeys'=> $selectedKeys,
        ]);
    }
 public function export(Request $request, string $format = 'xlsx')
    {
        $file = 'san_pham_' . now()->format('Ymd_His') . '.' . $format;

        $export = new ProductsExport(
            categoryId: $request->query('category_id'),
            brandId:    $request->query('brand_id'),
            status:     $request->query('status')
        );

        return match ($format) {
            'csv'  => Excel::download($export, $file, \Maatwebsite\Excel\Excel::CSV),
            default => Excel::download($export, $file),
        };
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required','file','mimes:xlsx,csv'],
        ]);

        $import = new ProductsImport;
        try {
            $import->import($request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Import thất bại: '.$e->getMessage());
        }

        // Thông tin lỗi từng dòng (nếu có)
        if ($import->failures()->isNotEmpty()) {
            // Lấy 5 lỗi đầu để hiển thị gọn
            $lines = $import->failures()->take(5)->map(function ($f) {
                return "Dòng {$f->row()}: ".implode('; ', $f->errors());
            })->implode("\n");

            return back()->with('error', "Một số dòng không hợp lệ:\n".$lines)
                         ->with('message', "Đã xử lý: tạo mới {$import->inserted} / cập nhật {$import->updated}.");
        }

        return back()->with('message', "Import thành công! Tạo mới: {$import->inserted}, Cập nhật: {$import->updated}.");
    }

    public function downloadTemplate()
    {
        $file = 'mau_import_san_pham.xlsx';
        return Excel::download(new ProductsTemplateExport, $file);
    }

}
