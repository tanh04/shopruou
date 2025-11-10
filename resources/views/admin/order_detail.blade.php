@extends('admin_layout')
@section('admin_content')

<div class="table-agile-info">
  <div class="panel panel-default">

    {{-- Breadcrumb --}}
    @include('partials.breadcrumb', [
        'items' => [
            ['label' => 'Bảng điều khiển', 'url' => URL::to('/dashboard'), 'icon' => 'fa fa-home'],
            ['label' => 'Đơn hàng',        'url' => URL::to('/manage-order'), 'icon' => 'fa fa-shopping-cart'],
            ['label' => 'Chi tiết',        'active' => true, 'icon' => 'fa fa-eye']
        ]
    ])
    
    <div style="min-height: 40px; margin-bottom: 20px;">
        @if (session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
    </div>

    {{-- Thông tin khách hàng --}}
    <div class="panel-heading">Thông tin khách hàng</div>
    <table class="table table-bordered">
        <tr>
            <th style="width:50%">Tên khách hàng</th>
            <td>{{ $order->order_name ?? ($order->user->name ?? 'N/A') }}</td>
        </tr>
        <tr>
            <th style="width:50%">Email</th>
            <td>{{ $order->order_email ?? ($order->user->email ?? 'N/A') }}</td>
        </tr>
    </table>

    {{-- Thông tin vận chuyển --}}
    <div class="panel-heading">Thông tin vận chuyển</div>
    <table class="table table-bordered">
        <tr>
            <th style="width:50%">Số điện thoại</th>
            <td>{{ $order->order_phone }}</td>
        </tr>
        <tr>
            <th style="width:50%">Địa chỉ giao hàng</th>
            <td>{{ $order->order_address }}</td>
        </tr>
    </table>

    {{-- Thông tin thanh toán --}}
    @php
        $payment      = $order->payment; // có thể null
        $methodLabel  = strtoupper($payment->payment_method ?? '—');
        $payStatus    = $payment->payment_status ?? 'Chưa thanh toán';
        $badgeClass   = match($payStatus) {
            'Đã thanh toán'       => 'bg-success',
            'Đang chờ xử lý'      => 'bg-warning',
            'Thanh toán thất bại' => 'bg-danger',
            default               => 'bg-secondary',
        };
    @endphp

    <div class="panel-heading">Thông tin thanh toán</div>
    <table class="table table-bordered">
        <tr>
            <th style="width:50%">Phương thức</th>
            <td>{{ $methodLabel }}</td>
        </tr>
        <tr>
            <th style="width:50%">Trạng thái</th>
            <td>
                <span class="badge {{ $badgeClass }}">{{ $payStatus }}</span>
            </td>
        </tr>
        @if($payment?->created_at)
        <tr>
            <th style="width:50%">Thời điểm tạo giao dịch</th>
            <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        @endif
        @if($payment?->updated_at)
        <tr>
            <th style="width:50%">Cập nhật gần nhất</th>
            <td>{{ $payment->updated_at->format('d/m/Y H:i') }}</td>
        </tr>
        @endif
    </table>

    {{-- Chi tiết đơn hàng --}}
    <div class="panel-heading">CHI TIẾT ĐƠN HÀNG #{{ $order->order_id }}</div>
    <table class="table table-striped">
        <thead>
           <tr>
                <th style="width:15%; text-align:center;">Ảnh</th>
                <th style="width:35%; text-align:center;">Tên sản phẩm</th>
                <th style="width:15%; text-align:center;">Giá</th>
                <th style="width:10%; text-align:center;">Số lượng</th>
                <th style="width:25%; text-align:center;">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr class="text-center align-middle">
                <td>
                    <img src="{{ asset('uploads/products/' . $item->product->product_image) }}"
                         alt="{{ $item->product->product_name }}" style="width: 100px;">
                </td>
                <td>{{ $item->product->product_name }}</td>
                <td>{{ number_format($item->price, 0, ',', '.') }} đ</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->quantity * $item->price, 0, ',', '.') }} đ</td>
           </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $itemsTotal = $order->items->sum(fn($i) => $i->quantity * $i->price);
        $tax        = round($itemsTotal * 0.05); // 5%
        $ship       = (int) ($order->shipping_fee ?? 0);
        $discount   = (int) ($order->discount_total ?? 0);
        $grandTotal = max(0, $itemsTotal + $tax + $ship - $discount);
    @endphp

    <table class="table table-bordered" style="max-width:420px">
        <tr><th>Tạm tính</th><td class="text-end">{{ number_format($itemsTotal,0,',','.') }} đ</td></tr>
        <tr><th>Thuế (5%)</th><td class="text-end">{{ number_format($tax,0,',','.') }} đ</td></tr>
        @if($ship)    <tr><th>Phí vận chuyển</th><td class="text-end">{{ number_format($ship,0,',','.') }} đ</td></tr>@endif
        @if($discount)<tr><th>Giảm giá</th><td class="text-end">-{{ number_format($discount,0,',','.') }} đ</td></tr>@endif
        <tr class="table-active">
            <th>Tổng cộng</th>
            <td class="text-end"><strong>{{ number_format($grandTotal,0,',','.') }} đ</strong></td>
        </tr>
    </table>

    <a href="{{ route('admin.orders.print', $order->order_id) }}" class="btn btn-sm btn-primary" target="_blank">
        <i class="fa fa-print"></i> In đơn hàng
    </a>

  </div>
</div>

@endsection
