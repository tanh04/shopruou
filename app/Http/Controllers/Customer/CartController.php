<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\CartItems;
use App\Models\Coupon;
use Carbon\Carbon;

class CartController extends Controller
{
    public function addToCart(Request $request, $product_id)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập trước khi thêm vào giỏ hàng.');
        }

        $userId = Auth::id();
        $product = Product::findOrFail($product_id);
        // Lấy hoặc tạo cart cho user
        $cart = Cart::firstOrCreate(
            ['user_id' => $userId],
            ['created_at' => now(), 'updated_at' => now()]
        );

        // Lấy số lượng (nếu không có thì mặc định là 1)
        $quantity = $request->input('quantity', 1);

        // Kiểm tra nếu sản phẩm đã tồn tại trong giỏ
        $cartItem = $cart->items()->where('product_id', $product_id)->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            $cart->items()->create([
                'product_id' => $product_id,
                'quantity'   => $quantity,
                'price'      => $product->product_price,
            ]);
        }

        return redirect()->route('show_cart')->with('success', 'Đã thêm sản phẩm vào giỏ.');
    }

    public function save_cart(Request $request)
    {
        $productId = $request->input('productid_hidden');
        $quantity = max(1, (int) $request->input('quantity'));

        $product = Product::find($productId);
        if (!$product) {
            return redirect()->back()->with('error', 'Sản phẩm không tồn tại.');
        }

        // Kiểm tra tồn kho
        if ($quantity > $product->product_stock) {
            return redirect()->back()->with('error', 'Số lượng đặt vượt quá tồn kho. Vui lòng chọn lại.');
        }

        $userId = Auth::id();
        $sessionId = session()->getId();

        // Lấy hoặc tạo cart theo user_id hoặc session_id
        $cart = Cart::firstOrCreate(
            $userId ? ['user_id' => $userId] : ['session_id' => $sessionId],
            [
                'status' => 'active',
                'user_id' => $userId,
                'session_id' => $userId ? null : $sessionId
            ]
        );

        $cartItem = CartItems::where('cart_id', $cart->cart_id ?? $cart->id)
                            ->where('product_id', $productId)
                            ->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            CartItems::create([
                'cart_id' => $cart->cart_id ?? $cart->id,
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $product->product_price ?? 0,
            ]);
        }

        return redirect()->route('show_cart')->with('success', 'Sản phẩm đã được thêm vào giỏ hàng.');
    }

    public function show_cart()
    {
        $categories = Category::all();
        $brands = Brand::all();
        //$coupons = Coupon::where('status', 1)->get();

        $userId = Auth::id();
        $sessionId = session()->getId();

        //Xác định giỏ theo user hoặc session:
        $cart = Cart::when($userId, function ($query) use ($userId) {
            return $query->where('user_id', $userId);
        }, function ($query) use ($sessionId) {
            return $query->where('session_id', $sessionId);
        })->first();

        //Lấy danh sách CartItems kèm product
        $cartItems = [];

        if ($cart) {
            $cartItems = CartItems::where('cart_id', $cart->cart_id ?? $cart->id)
                                ->with('product')
                                ->get();
        }

        // Lấy danh sách coupon còn hiệu lực
        $today = Carbon::today('Asia/Ho_Chi_Minh');
        $all_coupons = Coupon::where('status', 1)
            ->where(function($q) use ($today){ $q->whereNull('start_date')->orWhereDate('start_date', '<=', $today); })
            ->where(function($q) use ($today){ $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today); })
            ->where(function($q){ $q->whereNull('coupon_quantity')->orWhere('coupon_quantity', '>', 0); })
            ->get();

            // Lấy coupon hiện tại từ session nếu có
            $coupon = null;
            if (session()->has('coupon_id')) {
                $coupon = Coupon::find(session('coupon_id'));
            }

        return view('pages.cart.show_cart', [
            'categories' => $categories,
            'brands' => $brands,
            'cartItems' => $cartItems,
            'hideSidebar' => true,
            'hideSlider' => true,
            'all_coupons' => $all_coupons, // truyền vào view
            'coupon' => $coupon, // truyền coupon vào view
        ]);
    }


    public function update_quantity(Request $request)
    {
        $item = CartItems::find($request->id);

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
        }

        // Không cho số lượng < 1
        if ($request->quantity < 1) {
            return response()->json(['success' => false, 'message' => 'Số lượng không hợp lệ']);
        }

        $item->quantity = $request->quantity;
        $item->save();

        return response()->json([
            'success' => true,
            'quantity' => $item->quantity,
            'total' => number_format($item->product->product_price * $item->quantity, 0, ',', '.').'₫'
        ]);
    }

    public function removeFromCart($id)
    {
        $cartItem = CartItems::find($id);
        if (!$cartItem) {
            return redirect()->route('show_cart')->with('error', 'Sản phẩm không tồn tại trong giỏ hàng.');
        }
        $cartItem->delete();
        return redirect()->route('show_cart')->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }


    // Price, mã giảm giá và thuế
    public function handleCartAction(Request $request)
    {
        $action = $request->input('action');
        $selectedItems = $request->input('selected_items', []);

        if (empty($selectedItems)) {
            return redirect()->route('show_cart')->with('error', 'Vui lòng chọn ít nhất 1 sản phẩm.');
        }

        // LƯU CẢ COUPON ID từ form giỏ hàng
        session([
            'checkout_items' => $selectedItems,
            'coupon_id'      => $request->input('coupon_id') ?: null,
        ]);

        if ($action === 'checkout') {
            return redirect()->route('show_order');
        }
        if ($action === 'update') {
            return $this->updatePrice($request);
        }
        return redirect()->route('show_cart');
    }


public function updatePrice(Request $request) {
    $selected = $request->selected_items ?? [];
    $couponId = $request->coupon_id ?? null;

    // Lấy giỏ theo user/session
    $userId    = Auth::id();
    $sessionId = session()->getId();

    $cart = Cart::when($userId, function ($q) use ($userId) {
        return $q->where('user_id', $userId);
    }, function ($q) use ($sessionId) {
        return $q->where('session_id', $sessionId);
    })->first();

    if (!$cart) {
        return response()->json(['success' => false, 'message' => 'Giỏ hàng trống']);
    }

    $cartItemsQuery = CartItems::where('cart_id', $cart->cart_id ?? $cart->id)->with('product');
    $cartItems = empty($selected)
        ? $cartItemsQuery->get()
        : $cartItemsQuery->whereIn('id', $selected)->get();

    if ($cartItems->isEmpty()) {
        return response()->json(['success' => false, 'message' => 'Giỏ hàng trống']);
    }

    // =========================
    // TÍNH THEO GIÁ KHUYẾN MÃI (NẾU CÒN HẠN)
    // =========================
    $subtotalOriginal = 0;   // = tổng theo "đơn giá hiệu lực"
    $itemsData = [];

    foreach ($cartItems as $item) {
        $p = $item->product;

        // robust promo window: start <= now <= endOfDay(end)
        $now   = now(); // đảm bảo app timezone = Asia/Ho_Chi_Minh
        $start = $p->promo_start ? Carbon::parse($p->promo_start) : null;
        $end   = $p->promo_end   ? Carbon::parse($p->promo_end)->endOfDay() : null;

        $isPromo = !is_null($p->promo_price)
            && (float)$p->promo_price > 0
            && (float)$p->product_price > 0
            && (float)$p->promo_price < (float)$p->product_price
            && (!$start || $start->lte($now))
            && (!$end   || $end->gte($now));

        // ĐƠN GIÁ HIỆU LỰC
        $unit = $isPromo ? (float)$p->promo_price : (float)$p->product_price;

        $totalItem        = $unit * (int)$item->quantity;
        $subtotalOriginal += $totalItem;

        $itemsData[] = [
            'id'    => $item->id,
            'total' => $totalItem,  // trả về tổng dòng theo đơn giá hiệu lực
            // (tuỳ chọn) trả thêm 'unit' nếu FE muốn hiện
            // 'unit'  => $unit,
        ];
    }

    // =========================
    // ÁP MÃ GIẢM GIÁ (nếu có)
    // =========================
    $discountAmount = 0;
    $couponMessage  = null;

    if ($couponId) {
        $coupon = Coupon::find($couponId);

        if (!$coupon) {
            $couponMessage = 'Mã không tồn tại.';
        } elseif ((int)$coupon->status !== 1) {
            $couponMessage = 'Mã đã bị vô hiệu hóa.';
        } else {
            // Kiểm tra thời gian hiệu lực (DATE)
            $today = Carbon::today('Asia/Ho_Chi_Minh'); // hoặc today() nếu app timezone đã set

            if ($coupon->start_date && $today->lt(Carbon::parse($coupon->start_date))) {
                $couponMessage = 'Mã chưa đến ngày áp dụng.';
            } elseif ($coupon->end_date && $today->gt(Carbon::parse($coupon->end_date))) {
                $couponMessage = 'Mã đã hết hạn.';
            } elseif (!is_null($coupon->coupon_quantity) && (int)$coupon->coupon_quantity <= 0) {
                $couponMessage = 'Mã đã hết lượt sử dụng.';
            } elseif (!is_null($coupon->min_order_value) && $subtotalOriginal < (float)$coupon->min_order_value) {
                $need = number_format($coupon->min_order_value, 0, ',', '.').'₫';
                $couponMessage = 'Chưa đạt giá trị đơn tối thiểu (' . $need . ').';
            } else {
                // Giảm trên tổng theo đơn giá hiệu lực
                if (!is_null($coupon->discount_amount) && $coupon->discount_amount > 0) {
                    $discountAmount = min((float)$coupon->discount_amount, $subtotalOriginal);
                } elseif (!is_null($coupon->discount_percent) && $coupon->discount_percent > 0) {
                    $discountAmount = round($subtotalOriginal * ((float)$coupon->discount_percent) / 100, 0);
                }
            }
        }
    }

    $subtotalAfterDiscount = max($subtotalOriginal - $discountAmount, 0);
    $tax   = $subtotalAfterDiscount * 0.05; // 5%
    $total = $subtotalAfterDiscount + $tax;

    return response()->json([
        'success'           => true,
        'subtotal_original' => $subtotalOriginal,   // tổng trước giảm, theo đơn giá hiệu lực
        'discount'          => $discountAmount,
        'subtotal'          => $subtotalAfterDiscount,
        'tax'               => $tax,
        'total'             => $total,
        'items'             => $itemsData,          // mỗi dòng đã theo đơn giá hiệu lực
        'coupon_message'    => $couponMessage,
    ]);
}

}
