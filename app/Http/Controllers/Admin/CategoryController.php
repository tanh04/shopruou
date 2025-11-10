<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;

class CategoryController extends Controller
{
    public function all_categories(Request $request)
    {
        $keyword = $request->input('keyword');
        $status  = $request->input('status');

        $query = Category::with('parent')->orderByDesc('category_id');

        if (!empty($keyword)) {
            $query->where(function($q) use ($keyword) {
                $q->where('category_name', 'like', "%$keyword%")
                ->orWhere('category_description', 'like', "%$keyword%");
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('category_status', $status);
        }

        $all_categories = $query->paginate(7)->appends($request->query());

        return view('admin.all_categories', compact('all_categories'));
    }

    public function create_category()
    {
        // Truyền danh sách để chọn làm cha
        $parents = Category::orderBy('category_name')->get();
        return view('admin.create_category', compact('parents'));
    }

    public function save_category(Request $request)
    {
        $data = $request->validate([
            'category_name'        => 'required|string|min:5|max:50',
            'category_description' => 'nullable|string|min:5|max:1000',
            'category_status'      => 'required|in:0,1',
            'parent_id'            => 'nullable|exists:categories,category_id',
        ]);

        Category::create($data);

        return redirect('create-category')->with('message', 'Thêm danh mục sản phẩm thành công!');
    }

    public function active_category($category_id)
    {
        $category = Category::findOrFail($category_id);
        $category->category_status = 1;
        $category->save();

        Product::where('category_id', $category_id)->update(['product_status' => 1]);

        return redirect('all-categories')->with('message', 'Đã kích hoạt danh mục và sản phẩm liên quan!');
    }

    public function unactive_category($category_id)
    {
        $category = Category::findOrFail($category_id);
        $category->category_status = 0;
        $category->save();

        Product::where('category_id', $category_id)->update(['product_status' => 0]);

        return redirect('all-categories')->with('message', 'Đã hủy kích hoạt danh mục và sản phẩm liên quan!');
    }

    public function show_category(string $category_id)
    {
        $categories = Category::where('category_status', 1)->get();
        $brands = Brand::where('brand_status', 1)->get();

        $category = Category::find($category_id);

        $products = Product::where('category_id', $category_id)
                    ->where('product_status', 1)
                    ->orderBy('product_id', 'desc')
                    ->get();

        return view('pages.category.show_category', [
            'categories'    => $categories,
            'brands'        => $brands,
            'products'      => $products,
            'category_name' => $category->category_name ?? 'Danh mục không tồn tại',
        ]);
    }

    public function edit_category($category_id)
    {
        $category = Category::findOrFail($category_id);
        $parents = Category::where('category_id','!=',$category_id)
                    ->orderBy('category_name')->get();

        return view('admin.edit_category', compact('category','parents'));
    }

    public function update_category(Request $request, $category_id)
    {
        $category = Category::findOrFail($category_id);

        $data = $request->validate([
            'category_name'        => 'required|string|min:5|max:50',
            'category_description' => 'nullable|string|min:5|max:1000',
            'category_status'      => 'required|in:0,1',
            'parent_id'            => 'nullable|exists:categories,category_id|not_in:'.$category_id,
        ]);

        // Chống tạo chu kỳ: không cho gán con/cháu của nó làm cha
        if (!empty($data['parent_id']) && $this->isDescendant($data['parent_id'], $category_id)) {
            return back()
                ->withErrors(['parent_id' => 'Không thể chọn danh mục con/cháu làm danh mục cha.'])
                ->withInput();
        }

        $category->update($data);

        return redirect('all-categories')->with('message', 'Cập nhật danh mục thành công!');
    }

    public function delete_category($category_id)
    {
        $category = Category::withCount(['children'])->findOrFail($category_id);

        // Nếu có danh mục con thì cảnh báo (tránh xoá nhầm cây)
        if ($category->children_count > 0) {
            return back()->with('message', 'Danh mục này có danh mục con. Hãy xoá/di chuyển các danh mục con trước.');
        }

        $productCount = Product::where('category_id', $category_id)->count();

        if ($productCount > 0) {
            return view('admin.confirm_delete_category', [
                'category'     => $category,
                'productCount' => $productCount,
            ]);
        }

        $category->delete();

        return redirect('all-categories')->with('message', 'Xóa danh mục thành công!');
    }

    public function force_delete_category($category_id)
    {
        $category = Category::findOrFail($category_id);

        // Xóa sản phẩm thuộc danh mục
        Product::where('category_id', $category_id)->delete();

        // (Tuỳ chọn) nếu bạn dùng foreign key parent_id ON DELETE CASCADE,
        // con cháu sẽ tự xoá. Nếu KHÔNG, cần tự xoá cây con tại đây.

        $category->delete();

        return redirect('all-categories')->with('message', 'Đã xóa danh mục và tất cả sản phẩm liên quan!');
    }

    // ===== Helpers =====
    // Kiểm tra $candidateId có nằm trong cây con của $currentId không
    private function isDescendant($candidateId, $currentId): bool
    {
        $node = Category::with('children')->find($candidateId);
        if (!$node) return false;
        if ($node->category_id == $currentId) return true;

        foreach ($node->children as $child) {
            if ($this->isDescendant($child->category_id, $currentId)) return true;
        }
        return false;
    }
}
