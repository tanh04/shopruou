<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;

class BrandController extends Controller
{
    public function all_brands(Request $request)
    {
        $keyword = $request->input('keyword');
        $status  = $request->input('status');

        $query = Brand::orderByDesc('brand_id');

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('brand_name', 'like', "%$keyword%")
                ->orWhere('brand_description', 'like', "%$keyword%");
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('brand_status', $status);
        }

        $all_brands = $query->paginate(7)->appends($request->query());

        return view('admin.all_brands', compact('all_brands'));
    }


    public function create_brand()
    {
        return view('admin.create_brand');
    }

    public function save_brand(Request $request)
    {
        Brand::create([
            'brand_name'        => $request->input('brand_name'),
            'brand_description' => $request->input('brand_description'),
            'brand_status'      => $request->input('brand_status', 1),
        ]);

    return redirect('create-brand')->with('message', 'Thêm thương hiệu sản phẩm thành công!');
    }
    public function active_brand($brand_id)
    {
        $brand = Brand::findOrFail($brand_id);
        $brand->brand_status = 1;
        $brand->save();

        // Kích hoạt tất cả sản phẩm thuộc thương hiệu này
        Product::where('brand_id', $brand_id)->update(['product_status' => 1]);

        return redirect('all-brands')->with('message', 'Đã kích hoạt thương hiệu và sản phẩm liên quan!');
    }

    public function unactive_brand($brand_id)
    {
        $brand = Brand::findOrFail($brand_id);
        $brand->brand_status = 0;
        $brand->save();

        // Hủy kích hoạt tất cả sản phẩm thuộc thương hiệu này
        Product::where('brand_id', $brand_id)->update(['product_status' => 0]);

        return redirect('all-brands')->with('message', 'Đã hủy kích hoạt thương hiệu và sản phẩm liên quan!');
    }

    public function show_brand(string $brand_id)
    {
        $categories = Category::where('category_status', 1)->get();
        $brands = Brand::where('brand_status', 1)->get();

        // Lấy danh mục hiện tại để lấy tên
        $brand = Brand::find($brand_id);

        $products = Product::where('brand_id', $brand_id)
                    ->where('product_status', 1)
                    ->orderBy('product_id', 'desc')
                    ->get();

        return view('pages.brand.show_brand', [
            'categories' => $categories,
            'brands' => $brands,
            'products' => $products,
            'brand_name' => $brand->brand_name ?? 'Thương hiệu không tồn tại',
        ]);
    }

    public function edit_brand($brand_id)
    {
        $brand = Brand::findOrFail($brand_id);
        return view('admin.edit_brand', compact('brand'));
    }

    public function update_brand(Request $request, $brand_id)
    {
        $brand = Brand::findOrFail($brand_id);
        $brand->brand_name = $request->brand_name;
        $brand->brand_description = $request->brand_description;
        $brand->save();

        return redirect('all-brands')->with('message', 'Cập nhật danh mục thành công!');
    }

    public function delete_brand($brand_id)
    {
        $brand = brand::findOrFail($brand_id);

        // Xóa sản phẩm thuộc thương hiệu
        Product::where('brand_id', $brand_id)->delete();

        // Xóa thương hiệu
        $brand->delete();

        return redirect('all-brands')->with('message', 'Đã xóa thương hiệu và tất cả sản phẩm liên quan!');
    }
}
