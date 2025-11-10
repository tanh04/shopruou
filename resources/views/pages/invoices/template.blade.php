<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Phiếu đặt hàng #{{ $order->order_id }}</title>
<style>
    @page { margin: 22px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
    .header { text-align:center; font-weight:700; margin-bottom: 10px; }
    .sub { font-weight: 400; font-size: 11px; margin-top: 2px; }
    h3 { margin: 14px 0 6px; }
    th, td { border: 1px solid #333; padding: 6px; vertical-align: top; }
    table { width:100%; border-collapse: collapse; }
    .no-border td, .no-border th { border: none; padding: 2px 0; }
    .right { text-align:right; }
    .muted { color:#555; }
</style>
</head>
<body>
@php
    // Formatter dự phòng (nếu service không truyền)
    if (!isset($fmt) || !is_callable($fmt)) {
        $fmt = fn($v) => number_format((float)$v, 0, ',', '.') . ' đ';
    }

    // Dùng biến từ service; nếu thiếu thì fallback từ order->payment
    $payMethod = $payMethod ?? (strtoupper(optional($order->payment)->payment_method ?? '—'));
    $payStatus = $payStatus ?? (optional($order->payment)->payment_status ?? '—');

    // $discount, $ship, $tax, $grandTotal, $printedAt, $rows được service tính sẵn
@endphp

<div class="header">
    <div>WINMART</div>
    <div class="sub">Mã đơn: #{{ $order->order_id }} • Ngày in: {{ $printedAt }}</div>
</div>

<h3>Thông tin đơn hàng</h3>
<table class="no-border">
    <tr>
        <td><b>Ngày tạo:</b> {{ $order->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
        <td><b>Phương thức thanh toán:</b> {{ $payMethod }}</td>
        <td><b>Trạng thái thanh toán:</b> {{ $payStatus }}</td>
    </tr>
</table>

<h3>Thông tin khách hàng</h3>
<p>
    <b>{{ $custName }}</b>
    <span class="muted">— {{ $custPhone }} — {{ $custMail }}</span>
</p>

<h3>Thông tin giao hàng</h3>
<p>
    {{ $recvName }} | {{ $recvAddr }} | {{ $recvPhone }} | {{ $recvMail }}<br>
    <span class="muted">Ghi chú: {{ $note ?: '—' }}</span>
</p>
<p>
    <b>Phương thức thanh toán:</b> {{ $payMethod }}
    <b>Trạng thái thanh toán:</b> {{ $payStatus }}
    
</p>
<h3>Chi tiết đơn hàng</h3>
<table>
    <thead>
        <tr>
            <th style="width: 40%;">Sản phẩm</th>
            <th style="width: 15%;">Mã giảm</th>
            <th style="width: 10%;" class="right">SL</th>
            <th style="width: 15%;" class="right">Giá</th>
            <th style="width: 20%;" class="right">Thành tiền</th>
        </tr>
    </thead>
    <tbody>
        {!! $rows !!} {{-- do InvoiceService render: tên | mã | sl | giá | thành tiền --}}
    </tbody>
</table>

<h3>Tổng kết</h3>
<table style="max-width:420px; margin-left:auto; border-collapse:collapse; width:100%;">
    <tr>
        <th style="text-align:left; border:1px solid #333; padding:6px;">Tổng tiền hàng</th>
        <td class="right" style="border:1px solid #333; padding:6px;">{{ $fmt($itemsTotal) }}</td>
    </tr>
    <tr>
        <th style="text-align:left; border:1px solid #333; padding:6px;">Giảm giá (tổng)</th>
        <td class="right" style="border:1px solid #333; padding:6px;">-{{ $fmt($discount) }}</td>
    </tr>
    <tr>
        <th style="text-align:left; border:1px solid #333; padding:6px;">Thuế (VAT)</th>
        <td class="right" style="border:1px solid #333; padding:6px;">{{ $fmt($tax) }}</td>
    </tr>
    <tr>
        <th style="text-align:left; border:1px solid #333; padding:6px;">Phí vận chuyển</th>
        <td class="right" style="border:1px solid #333; padding:6px;">{{ $fmt($ship) }}</td>
    </tr>
    <tr>
        <th style="text-align:left; border:1px solid #333; padding:6px;">TỔNG THANH TOÁN</th>
        <td class="right" style="border:1px solid #333; padding:6px;"><b>{{ $fmt($grandTotal) }}</b></td>
    </tr>
</table>

</body>
</html>
