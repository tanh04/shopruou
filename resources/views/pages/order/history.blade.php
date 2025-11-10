@extends('welcome')

@section('content')
<div class="container">
    <h2>Lịch sử đơn hàng</h2>

    @if($orders->isEmpty())
        <p>Bạn chưa có đơn hàng nào.</p>
    @else
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Ngày đặt</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            @foreach($orders as $order)
                @php
                    $method    = optional($order->payment)->payment_method;     // MOMO | VNPAY | COD | null
                    $payStatus = optional($order->payment)->payment_status;     // Đã thanh toán | ...
                    $isPaid    = ($payStatus === \App\Models\Payment::STATUS_PAID);

                    $showRepay = !$isPaid
                        && !in_array($order->status, [\App\Models\Order::STATUS_COMPLETED, \App\Models\Order::STATUS_CANCELLED], true)
                        && in_array($method, [\App\Models\Payment::METHOD_MOMO, \App\Models\Payment::METHOD_VNPAY], true);
                @endphp
                <tr>
                    <td>#{{ $order->order_id }}</td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ number_format($order->total_price) }}₫</td>
                    <td>
                        <span class="badge bg-{{ $order->status == \App\Models\Order::STATUS_COMPLETED ? 'success' : ($order->status == \App\Models\Order::STATUS_CANCELLED ? 'danger' : 'warning') }}">
                            {{ $order->status }}
                        </span>
                        @if($order->payment)
                            <div class="small text-muted">
                                {{ $order->payment->payment_method }} — {{ $order->payment->payment_status }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('order.show_history', $order->order_id) }}" class="btn btn-sm btn-primary">
                            Xem chi tiết
                        </a>

                        @if($order->status === \App\Models\Order::STATUS_PENDING)
                            <form action="{{ route('order.cancel', $order->order_id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')">
                                    Hủy đơn
                                </button>
                            </form>
                        @endif

                        {{-- Nút thanh toán lại: CHỈ hiện đúng phương thức ban đầu --}}
                        @if($showRepay)
                            @if($method === \App\Models\Payment::METHOD_MOMO)
                                <form action="{{ route('user.orders.payAgain.momo', $order->order_id) }}"
                                      method="POST" style="display:inline-block; margin-left:6px;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        Thanh toán lại MoMo
                                    </button>
                                </form>
                            @elseif($method === \App\Models\Payment::METHOD_VNPAY)
                                <form action="{{ route('user.orders.payAgain.atm', $order->order_id) }}"
                                      method="POST" style="display:inline-block; margin-left:6px;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        Thanh toán lại ATM
                                    </button>
                                </form>
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        @if(method_exists($orders, 'links'))
            <div class="mt-3">
                {{ $orders->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
