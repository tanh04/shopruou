{{-- resources/views/invoices/template.blade.php --}}
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa đơn #{{ $order->order_id ?? $order->id }}</title>
    <style>
        * { box-sizing: border-box; font-family: DejaVu Sans, DejaVu Sans Mono, sans-serif; }
        body { font-size: 12px; color: #111; }
        h1,h2,h3,h4 { margin: 0; }
        .wrap { width: 100%; }
        .row { display: flex; gap: 16px; }
        .col { flex: 1; }
        .mb8 { margin-bottom: 8px; }
        .mb12 { margin-bottom: 12px; }
        .mb16 { margin-bottom: 16px; }
        .mb24 { margin-bottom: 24px; }
        .right { text-align: right; }
        .center { text-align: center; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #ddd; padding: 6px 8px; vertical-align: middle; }
        .table th { background: #f2f2f2; }
        .meta { width: 100%; border: 1px solid #ddd; padding: 8px 10px; }
        .muted { color: #666; }
        .totals td { padding: 6px 8px; }
        .totals .label { text-align: right; }
        .badge { display:inline-block; padding:2px 6px; border:1px solid #999; border-radius:4px; font-size:11px; }
    </style>
</head>
<body>
<div class="wrap">

    <div class="row mb16">
        <div class="col">
            <h2>HÓA ĐƠN BÁN HÀNG</h2>
            <div class="muted">In lúc: {{ $printedAt }}</div>
            <div class="muted">Mã đơn: #{{ $order->order_id ?? $order->id }}</div>
        </div>
        <div class="col right">
            @php
                $status = method_exists($order, 'getStatusText') ? $order->getStatusText() : ($order->status ?? '');
            @endphp
            @if(!empty($status))
                <span class="badge">{{ $status }}</span>
            @endif
        </div>
    </div>

    <div class="row mb16">
        <div class="col">
            <div class="meta">
                <strong>Khách hàng</strong><br>
                Họ tên: {{ $custName }}<br>
                Điện thoại: {{ $custPhone }}<br>
                Email: {{ $custMail }}
            </div>
        </div>
        <div class="col">
            <div class="meta">
                <strong>Người nhận</strong><br>
                Họ tên: {{ $recvName }}<br>
                Địa chỉ: {{ $recvAddr }}<br>
                Điện thoại: {{ $recvPhone }}<br>
                Email: {{ $recvMail }}
            </div>
        </div>
    </div>

    @if(!empty($note))
    <div class="mb16">
        <strong>Ghi chú đơn hàng:</strong> {{ $note }}
    </div>
    @endif

    <table class="table mb16">
        <thead>
        <tr>
            <th style="width:38%;">Sản phẩm</th>
            <th style="width:12%;">Mã KM</th>
            <th style="width:12%;" class="right">*Phí/giảm</th>
            <th style="width:10%;" class="right">SL</th>
            <th style="width:14%;" class="right">Đơn giá</th>
            <th style="width:14%;" class="right">Thành tiền</th>
        </tr>
        </thead>
        <tbody>
        {!! $rows !!}
        </tbody>
    </table>

    <table class="totals" style="width:100%;">
        <tr>
            <td class="label">Tổng tiền hàng:</td>
            <td class="right" style="width:160px;">{{ $fmt($order->items->sum(fn($i) => $i->quantity * $i->price)) }}</td>
        </tr>
        <tr>
            <td class="label">Giảm giá (tổng):</td>
            <td class="right">{{ $fmt($discount) }}</td>
        </tr>
        <tr>
            <td class="label">Thuế (VAT):</td>
            <td class="right">{{ $fmt($tax) }}</td>
        </tr>
        <tr>
            <td class="label">Phí vận chuyển:</td>
            <td class="right">{{ $fmt($ship) }}</td>
        </tr>
        <tr>
            <td class="label"><strong>TỔNG THANH TOÁN:</strong></td>
            <td class="right"><strong>{{ $fmt($grandTotal) }}</strong></td>
        </tr>
    </table>

</div>
</body>
</html>
