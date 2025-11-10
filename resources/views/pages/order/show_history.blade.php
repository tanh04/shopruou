@extends('welcome')

@section('content')
@php
    // Tính tạm tính từ items (giá đã chốt lúc đặt)
    $subtotal = (float) $order->items->sum(function($i){
        return (float)($i->price ?? 0) * (int)($i->quantity ?? 0);
    });

    // Ưu tiên số đã lưu trong DB
    $discount = (float)($order->discount_amount ?? 0);

    // Fallback từ coupon (nếu cần)
    $couponCode = null;
    if ($discount <= 0 && !empty($order->coupon_id)) {
        $coupon = \App\Models\Coupon::find($order->coupon_id);
        if ($coupon) {
            $couponCode = $coupon->coupon_code ?? null;
            $amount  = (float)($coupon->discount_amount  ?? 0);
            $percent = (float)($coupon->discount_percent ?? 0);

            if ($amount > 0) {
                $discount = min($amount, $subtotal);
            } elseif ($percent > 0) {
                $discount = round($subtotal * $percent / 100, 0);
            }
        }
    }

    // Thuế (5%) sau giảm giá
    $tax   = (float)($order->tax_amount ?? round(max($subtotal - $discount, 0) * 0.05, 0));
    $grand = (float)($order->total_price ?? (max($subtotal - $discount, 0) + $tax));

    // Map trạng thái đơn
    $statusMap = [
        0 => 'Chờ xử lý',
        1 => 'Đang xử lý',
        2 => 'Hoàn tất',
        3 => 'Đã hủy',
        'pending'    => 'Chờ xử lý',
        'processing' => 'Đang xử lý',
        'completed'  => 'Hoàn tất',
        'cancelled'  => 'Đã hủy',
    ];
    $statusText = $statusMap[$order->status] ?? (string)$order->status;

    // ==== Thông tin thanh toán (THÊM MỚI) ====
    $payment     = optional($order->payment);
    $payMethod   = strtoupper($payment->payment_method ?? '—');
    // Một số nơi bạn lưu 'status', nơi khác 'payment_status' -> lấy cả hai
    $payStatus   = $payment->payment_status ?? $payment->status ?? null;

    // Badge màu sắc
    $badgeClass = match (trim(strtolower((string)$payStatus))) {
        'đã thanh toán', 'paid', 'success'     => 'bg-success',
        'đang chờ xử lý', 'pending','processing' => 'bg-warning',
        'thanh toán thất bại', 'failed','canceled','cancelled' => 'bg-danger',
        default => 'bg-secondary',
    };
@endphp

<div class="container mt-4">
    <h3>Chi tiết đơn hàng #{{ $order->order_id }}</h3>

    <p><strong>Người đặt:</strong> {{ $order->order_name }}</p>
    <p><strong>Email:</strong> {{ $order->order_email }}</p>
    <p><strong>Số điện thoại:</strong> {{ $order->order_phone }}</p>
    <p><strong>Địa chỉ:</strong> {{ $order->order_address }}</p>
    <p><strong>Ghi chú:</strong> {{ $order->order_note ?: 'Không có' }}</p>
    <p><strong>Trạng thái đơn:</strong> {{ $statusText }}</p>

    {{-- ==== HIỂN THỊ PAYMENT_METHOD + PAYMENT_STATUS (THÊM MỚI) ==== --}}
    <p>
        <strong>Thanh toán:</strong>
        {{ $payMethod }}
        @if($payStatus)
            <span class="badge {{ $badgeClass }}" style="margin-left:6px;">
                {{ $payStatus }}
            </span>
        @else
            <span class="badge bg-secondary" style="margin-left:6px;">Chưa thanh toán</span>
        @endif
    </p>

    <table class="table mt-3">
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th class="text-end">Giá</th>
                <th class="text-center">Số lượng</th>
                <th class="text-end">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ optional($item->product)->product_name ?? 'Sản phẩm đã xóa' }}</td>
                    <td class="text-end">{{ number_format($item->price ?? 0, 0, ',', '.') }}₫</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-end">{{ number_format(($item->price ?? 0) * ($item->quantity ?? 0), 0, ',', '.') }}₫</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-end">Tạm tính</th>
                <th class="text-end">{{ number_format($subtotal, 0, ',', '.') }}₫</th>
            </tr>
            <tr>
                <th colspan="3" class="text-end">Giảm giá</th>
                <th class="text-end">-{{ number_format($discount, 0, ',', '.') }}₫</th>
            </tr>
            <tr>
                <th colspan="3" class="text-end">Thuế (5%)</th>
                <th class="text-end">{{ number_format($tax, 0, ',', '.') }}₫</th>
            </tr>
            <tr>
                <th colspan="3" class="text-end">Tổng thanh toán</th>
                <th class="text-end">{{ number_format($grand, 0, ',', '.') }}₫</th>
            </tr>
        </tfoot>
    </table>

    <a href="{{ route('order.history') }}" class="btn btn-secondary">Quay lại</a>
</div>
@endsection
