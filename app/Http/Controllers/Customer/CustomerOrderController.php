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
use App\Models\OrderItems;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\Payment;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmationMail;
use App\Mail\OrderDeliveredMail;
use Carbon\Carbon;

class CustomerOrderController extends Controller
{
    public function show_order()
    {
        $categories = Category::all();
        $brands     = Brand::all();

        $selectedItems = session('checkout_items', []);
        if (empty($selectedItems)) {
            return redirect()->route('show_cart')->with('error', 'Vui lòng chọn sản phẩm để thanh toán.');
        }

        $cartItems = CartItems::whereIn('id', $selectedItems)->with('product')->get();
        if ($cartItems->isEmpty()) {
            return redirect()->route('show_cart')->with('error', 'Không tìm thấy sản phẩm hợp lệ.');
        }

        // --- Đơn giá hiệu lực (promo còn hạn -> promo_price; hết -> product_price)
        $unit = function ($p) {
            $now   = now();
            $start = $p->promo_start ? \Carbon\Carbon::parse($p->promo_start) : null;
            $end   = $p->promo_end   ? \Carbon\Carbon::parse($p->promo_end)->endOfDay() : null;

            $isPromo = !is_null($p->promo_price)
                && (float)$p->promo_price > 0
                && (float)$p->promo_price < (float)$p->product_price
                && (!$start || $start->lte($now))
                && (!$end   || $end->gte($now));

            return (float) ($isPromo ? $p->promo_price : $p->product_price);
        };

        $subtotal = $cartItems->sum(fn($i) => $unit($i->product) * (int)$i->quantity);

        // --- Voucher
        $couponId         = session('coupon_id');
        $discountAmount   = 0.0;   // 👈 TIỀN
        $discountPercent  = null;  // 👈 %
        $couponCode       = null;

        if ($couponId) {
            $coupon = Coupon::find($couponId);
            if ($coupon && (int)$coupon->status === 1) {
                $minOk = is_null($coupon->min_order_value) || $subtotal >= (float)$coupon->min_order_value;

                if ($minOk) {
                    // Nếu có cột điều kiện %/số tiền (coupon_condition): 1 = số tiền; 2 = %
                    $amount  = (float)($coupon->discount_amount  ?? 0);
                    $percent = (float)($coupon->discount_percent ?? 0);

                    if (!empty($coupon->coupon_condition) && (int)$coupon->coupon_condition === 1) {
                        $discountAmount = min($amount, $subtotal);
                    } elseif ($percent > 0) {
                        $discountPercent = (int)$percent;
                        $discountAmount  = round($subtotal * $percent / 100, 0);
                    } elseif ($amount > 0) {
                        $discountAmount = min($amount, $subtotal);
                    }

                    $couponCode = $coupon->coupon_code;
                }
            }
        }

        $taxable = max($subtotal - $discountAmount, 0);
        $tax     = round($taxable * 0.05, 0);
        $total   = $taxable + $tax;

        return view('pages.order.show_order', array_merge(
            compact(
                'categories', 'brands', 'cartItems',
                'subtotal', 'tax', 'total', 'couponCode',
                'discountAmount', 'discountPercent'
            ),
            [
                'hideSidebar' => true,
                'hideSlider'  => true,
            ]
        ));

    }


    public function save_order(Request $request)
    {
        // 1) Validate đầu vào (ngắn gọn, tự back với $errors + old())
        $data = $request->validate([
            'order_name'       => ['required','string','min:2','max:255'],
            'order_address'    => ['required','string','min:5','max:255'],
            'order_phone'      => ['required','regex:/^0[0-9]{9,10}$/'],
            'order_email'      => ['nullable','email'],
            'order_note'       => ['nullable','string','max:500'],
            'payment_option'   => ['required','in:ATM,MOMO,COD'],   // chỉ 3 giá trị hợp lệ
            'selected_items'   => ['required','array','min:1'],     // phải có item được chọn
            'selected_items.*' => ['integer'],
            'coupon_id'        => ['nullable','integer'],
        ], [
            'order_phone.regex'       => 'Số điện thoại phải bắt đầu bằng 0 và dài 10-11 chữ số.',
            'payment_option.in'       => 'Vui lòng chọn phương thức thanh toán hợp lệ.',
            'selected_items.required' => 'Vui lòng chọn sản phẩm để thanh toán.',
            'selected_items.min'      => 'Vui lòng chọn ít nhất 1 sản phẩm.',
        ]);

        $userId        = Auth::id();
        $selectedItems = $data['selected_items'];

        // 2) Lấy giỏ hàng
        $cart = Cart::where('user_id', $userId)->with('items.product')->first();
        if (!$cart) {
            return back()->withErrors(['selected_items' => 'Giỏ hàng không tồn tại.'])->withInput();
        }

        $selectedCartItems = $cart->items->whereIn('id', $selectedItems);
        if ($selectedCartItems->isEmpty()) {
            return back()->withErrors(['selected_items' => 'Không tìm thấy sản phẩm được chọn.'])->withInput();
        }

        // 3) Kiểm tra tồn kho
        foreach ($selectedCartItems as $ci) {
            if ($ci->product->product_stock < $ci->quantity) {
                return back()->withErrors([
                    'selected_items' => 'Sản phẩm "'.$ci->product->product_name.'" không đủ tồn kho.'
                ])->withInput();
            }
        }

        // 4) Đơn giá hiệu lực tại thời điểm đặt
        $effectiveUnit = function ($product, $ref = null) {
            $ref   = $ref ? \Carbon\Carbon::parse($ref) : now();
            $start = $product->promo_start ? \Carbon\Carbon::parse($product->promo_start) : null;
            $end   = $product->promo_end   ? \Carbon\Carbon::parse($product->promo_end)->endOfDay() : null;

            $isPromo = !is_null($product->promo_price)
                && (float)$product->promo_price > 0
                && (float)$product->promo_price < (float)$product->product_price
                && (!$start || $start->lte($ref))
                && (!$end   || $end->gte($ref));

            return (float) ($isPromo ? $product->promo_price : $product->product_price);
        };

        $nowRef  = now();
        $subtotal = $selectedCartItems->sum(fn($i) => $effectiveUnit($i->product, $nowRef) * (int)$i->quantity);

        // 5) Coupon (nếu có)
        $couponId = $data['coupon_id'] ?? session('coupon_id');
        $discount = 0;
        $coupon   = null;

        if ($couponId) {
            $coupon = Coupon::find($couponId);
            if (!$coupon || (int)$coupon->status !== 1) {
                return back()->withErrors(['coupon_id' => 'Mã giảm giá không hợp lệ hoặc đã hết hiệu lực.'])->withInput();
            }

            $minOk = is_null($coupon->min_order_value) || $subtotal >= (float)$coupon->min_order_value;
            if (!$minOk) {
                return back()->withErrors(['coupon_id' => 'Đơn hàng chưa đạt giá trị tối thiểu để áp dụng mã giảm giá.'])->withInput();
            }

            if ((int)$coupon->coupon_condition === 1) {
                $discount = min((float)$coupon->discount_amount, $subtotal);
            } else {
                $percent  = (float)($coupon->discount_percent ?? 0);
                $discount = round($subtotal * $percent / 100, 0);
            }
        }

        $taxable    = max($subtotal - $discount, 0);
        $tax        = round($taxable * 0.05, 0);
        $grandTotal = $taxable + $tax;

        // 6) Payment record
        $method = strtoupper($data['payment_option']);                 // ATM|MOMO|COD
        $method = $method === 'ATM' ? Payment::METHOD_VNPAY : $method; // chuẩn hoá về VNPAY

        $paymentStatus = in_array($method, [Payment::METHOD_MOMO, Payment::METHOD_VNPAY], true)
            ? Payment::STATUS_PENDING
            : Payment::STATUS_UNPAID;

        $payment = Payment::create([
            'payment_method' => $method,
            'payment_status' => $paymentStatus,
        ]);

        // 7) Order: online -> WAITING_PAYMENT; COD -> PENDING
        $defaultOrderStatus = $method === Payment::METHOD_COD
            ? Order::STATUS_PENDING
            : Order::STATUS_WAITING_PAYMENT;

        $order = Order::create([
            'user_id'         => $userId,
            'order_name'      => $data['order_name'],
            'order_address'   => $data['order_address'],
            'order_phone'     => $data['order_phone'],
            'order_email'     => $data['order_email'] ?? null,
            'order_note'      => $data['order_note'] ?? null,
            'status'          => $defaultOrderStatus,
            'payment_id'      => $payment->payment_id,
            'coupon_id'       => $coupon?->coupon_id,
            'total_price'     => (int)$grandTotal,
            'discount_amount' => (int)$discount,
        ]);

        // 8) Lưu items (giá chốt)
        foreach ($selectedCartItems as $ci) {
            $unit = $effectiveUnit($ci->product, $nowRef);

            OrderItems::create([
                'order_id'   => $order->order_id,
                'product_id' => $ci->product_id,
                'quantity'   => $ci->quantity,
                'price'      => $unit,
            ]);
        }

        // 9) Trừ coupon & tồn kho
        if ($coupon && !is_null($coupon->coupon_quantity) && $coupon->coupon_quantity > 0) {
            $coupon->decrement('coupon_quantity');
        }
        foreach ($selectedCartItems as $ci) {
            $ci->product()->decrement('product_stock', $ci->quantity);
        }

        // 10) Gửi mail (không để lỗi mail chặn luồng)
        if (!empty($data['order_email'])) {
            try {
                Mail::to($data['order_email'])->send(new OrderConfirmationMail($order));
            } catch (\Throwable $e) {
                // \Log::warning('Send mail failed: '.$e->getMessage());
            }
        }

        // 11) Xoá item đã checkout + session
        CartItems::whereIn('id', $selectedItems)->delete();
        session()->forget('checkout_items');

        // 12) Điều hướng theo phương thức thanh toán
        switch ($method) {
            case Payment::METHOD_MOMO:
                return app(PaymentController::class)->processMomoPayment($order->order_id, (int)$grandTotal);
            case Payment::METHOD_VNPAY:
                return app(PaymentController::class)->processVnpPayment($order->order_id, (int)$grandTotal);
            default:
                return redirect()->route('thanks', ['order_id' => $order->order_id]);
        }
    }

    // Danh sách lịch sử
    public function history()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with('items.product', 'payment')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('pages.order.history', array_merge(
            compact('orders'),
            [
                'hideSidebar' => true,
                'hideSlider'  => true,
            ]
        ));

    }

    public function show_history($order_id)
    {
        $order = Order::with(['items.product', 'payment', 'coupon'])
            ->where('order_id', $order_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('pages.order.show_history', array_merge(
            compact('order'),
            [
                'hideSidebar' => true,
                'hideSlider'  => true,
            ]
        ));

    }

    public function cancel($order_id)
    {
        $order = Order::where('order_id', $order_id)->where('user_id', Auth::id())->firstOrFail();

        if (!in_array($order->status, [Order::STATUS_WAITING_PAYMENT, Order::STATUS_PENDING], true)) {
            return back()->with('error', 'Chỉ hủy đơn khi đang chờ thanh toán/đang xử lý.');
        }

        $order->status = Order::STATUS_CANCELLED;
        $order->save();

        if ($order->coupon_id) {
            $coupon = Coupon::find($order->coupon_id);
            if ($coupon && (is_null($coupon->end_date) || $coupon->end_date >= now())) {
                $coupon->increment('coupon_quantity', 1);
            }
        }

        return back()->with('success', 'Đơn hàng đã được hủy.');
    }

    /** ===== Thanh toán lại (MoMo) ===== */
    public function payAgainMomo(Order $order)
    {
        if ($order->user_id !== Auth::id()) abort(403, 'Bạn không có quyền.');

        if (($order->payment && $order->payment->payment_status === Payment::STATUS_PAID)
            || $order->status === Order::STATUS_COMPLETED) {
            return redirect()->route('order.history')->with('info', 'Đơn này đã thanh toán.');
        }

        $order->update(['status' => Order::STATUS_WAITING_PAYMENT]);

        if ($order->payment) {
            $order->payment->update([
                'payment_method' => Payment::METHOD_MOMO,
                'payment_status' => Payment::STATUS_PENDING,
            ]);
        }

        return app(PaymentController::class)->processMomoPayment($order->order_id, (int)$order->total_price);
    }

    /** ===== Thanh toán lại (ATM/VNPAY) ===== */
    public function payAgainAtm(Order $order)
    {
        if ($order->user_id !== Auth::id()) abort(403, 'Bạn không có quyền.');

        if (($order->payment && $order->payment->payment_status === Payment::STATUS_PAID)
            || $order->status === Order::STATUS_COMPLETED) {
            return redirect()->route('order.history')->with('info', 'Đơn này đã thanh toán.');
        }

        $order->update(['status' => Order::STATUS_WAITING_PAYMENT]);

        if ($order->payment) {
            $order->payment->update([
                'payment_method' => Payment::METHOD_VNPAY,
                'payment_status' => Payment::STATUS_PENDING,
            ]);
        }

        return app(PaymentController::class)->processVnpPayment($order->order_id, (int)$order->total_price);
    }
}
