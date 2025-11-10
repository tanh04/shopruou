<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;

class WishlistController extends Controller
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth', only: ['show_wishlist','save_wishlist','destroy','handleWishlistAction']),
        ];
    }

    /**
     * Thêm sản phẩm vào wishlist
     */
    public function save_wishlist($product_id)
    {
        // Nếu product không tồn tại thì báo lỗi
        if (!Product::where('product_id', $product_id)->exists()) {
            return back()->with('error', 'Sản phẩm không tồn tại.');
        }

        Wishlist::firstOrCreate([
            'user_id'    => Auth::id(),
            'product_id' => $product_id,
        ]);

        return back()->with('success', 'Đã thêm vào danh sách yêu thích');
    }

    /**
     * Hiển thị danh sách wishlist của user
     */
    public function show_wishlist()
    {
        $wishlist = Wishlist::with('product')
            ->where('user_id', Auth::id())
            ->get();

        return view('pages.wishlist.show_wishlist', [
            'wishlist'   => $wishlist,
            'hideSidebar'=> true,
            'hideSlider' => true,
        ]);

    }

    /**
     * Xóa 1 mục khỏi wishlist
     */
    public function destroy($id)
    {
        Wishlist::where('user_id', Auth::id())
            ->where('id', $id)
            ->delete();

        return back()->with('success', 'Đã xóa khỏi danh sách yêu thích');
    }

    /**
     * Xử lý thao tác từ form wishlist (thêm nhiều vào giỏ, xóa nhiều, xử lý từng item)
     */
    public function handleWishlistAction(Request $request)
    {
        $action        = $request->input('action');          // move_selected_to_cart | remove_selected | move_single:{id} | remove_single:{id}
        $selectedItems = (array) $request->input('selected_items', []);
        $userId        = Auth::id();

        // helper: thêm 1 wishlist item vào giỏ (nếu khả dụng)
        $addToCart = function ($wishlistId) use ($userId) {
            $item = Wishlist::with('product')->where('user_id', $userId)->find($wishlistId);
            if (!$item || !$item->product)            return [false, 'Sản phẩm không tồn tại.'];
            $p = $item->product;

            // chỉ cho mua khi đang bán & còn hàng
            if ((int)$p->product_status !== 1)        return [false, 'Sản phẩm đã ngừng kinh doanh.'];
            if ((int)($p->product_stock ?? 0) <= 0)   return [false, 'Sản phẩm tạm hết hàng.'];

            // Tùy vào bảng products: nếu PK là id thì dùng id, nếu không thì product_id
            $pid = $p->id ?? $p->product_id;

            Cart::updateOrCreate(
                ['user_id' => $userId, 'product_id' => $pid],
                ['quantity' => DB::raw('quantity + 1')]
            );

            return [true, 'Đã thêm vào giỏ.'];
        };

        // ----- xử lý action đơn
        if (str_starts_with($action, 'move_single:')) {
            $wid = (int) str_replace('move_single:', '', $action);
            [$ok, $msg] = $addToCart($wid);
            return back()->with($ok ? 'success' : 'error', $msg);
        }

        if (str_starts_with($action, 'remove_single:')) {
            $wid = (int) str_replace('remove_single:', '', $action);
            Wishlist::where('user_id', $userId)->where('id', $wid)->delete();
            return back()->with('success', 'Đã xóa khỏi yêu thích.');
        }

        // ----- xử lý theo lô
        $bulkActions = ['move_selected_to_cart', 'remove_selected'];
        if (in_array($action, $bulkActions, true) && empty($selectedItems)) {
            return back()->with('error', 'Vui lòng chọn ít nhất 1 sản phẩm.');
        }

        if ($action === 'move_selected_to_cart') {
            $okCount = 0; $skip = 0;
            foreach ($selectedItems as $wid) {
                [$ok, ] = $addToCart((int)$wid);
                $ok ? $okCount++ : $skip++;
            }
            $msg = "Đã thêm {$okCount} sản phẩm vào giỏ.";
            if ($skip) $msg .= " {$skip} mục không khả dụng.";
            return back()->with('success', $msg);
        }

        if ($action === 'remove_selected') {
            Wishlist::where('user_id', $userId)->whereIn('id', $selectedItems)->delete();
            return back()->with('success', 'Đã xóa các mục đã chọn.');
        }

        return back()->with('error', 'Hành động không hợp lệ.');
    }
}
