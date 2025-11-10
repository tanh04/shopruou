<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Banner;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        // Thương hiệu
        $brands = Brand::where('brand_status', 1)->get(); 

        // Lấy danh mục cha + con
        $parents = Category::whereNull('parent_id')
            ->where('category_status', 1)
            ->with(['children' => function ($q) {
                $q->where('category_status', 1)->orderBy('category_name');
            }])
            ->orderBy('category_name')
            ->get();

        // Sản phẩm mới nhất, status = 1, phân trang 9
        $products = Product::where('product_status', 1)
        ->where('product_stock', '>', 0) // chỉ lấy sản phẩm có tồn kho > 0
        ->orderBy('product_id', 'desc')
        ->paginate(6);


        // 👉 Thêm banner (hiển thị nếu đang active và trong thời gian hiệu lực nếu có)
        $now = Carbon::now();
        $banners = Banner::where('status', 1)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->orderBy('sort_order')
            ->get();

        // Truyền sang view
        return view('pages.home', compact('parents', 'brands', 'products', 'banners'));
    }


    public function search(Request $request)
    {
        $keywords = $request->keywords_submit;

        $brands = Brand::where('brand_status', 1)->get(); 
        $categories = Category::where('category_status', 1)->get(); 

        // Truy vấn sản phẩm theo từ khóa
        $products = Product::where('product_status', 1)
            ->where('product_name', 'like', '%' . $keywords . '%')
            ->orderBy('product_id', 'desc')
            ->get();

        return view('pages.product.search', compact('categories', 'brands', 'products', 'keywords'));
    }
    public function searchSuggest(Request $request)
    {
        // Nhận cả ?q= và ?term=
        $termRaw = $request->get('q', $request->get('term', ''));
        $term = trim($termRaw);
        if ($term === '') {
            return response()->json([]);
        }

        $termEscaped = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $term);

        $products = Product::where('product_status', 1)
            ->where('product_name', 'like', '%' . $termEscaped . '%')
            ->orderBy('product_name')
            ->limit(10)
            ->get(['product_id', 'product_name'])
            ->values();

        return response()->json($products);
    }


    public function sidebar()
    {
        $parents = Category::whereNull('parent_id')
            ->where('category_status', 1)
            ->with(['children' => function ($q) {
                $q->where('category_status', 1)->orderBy('category_name');
            }])
            ->orderBy('category_name')
            ->get();

        return view('partials.sidebar_categories', compact('parents'));
    }

}
