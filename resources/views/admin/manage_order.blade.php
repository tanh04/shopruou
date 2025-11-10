@extends('admin_layout')
@section('admin_content')

<div class="table-agile-info">
  <div class="panel panel-default">
    <div class="panel-heading">
      LIỆT KÊ ĐƠN HÀNG
    </div>

    {{-- Breadcrumb --}}
    @include('partials.breadcrumb', [
      'items' => [
        ['label' => 'Bảng điều khiển', 'url' => URL::to('/dashboard'), 'icon' => 'fa fa-home'],
        ['label' => 'Đơn hàng',        'url' => URL::to('/manage-order'), 'icon' => 'fa fa-shopping-cart'],
        ['label' => 'Liệt kê',         'active' => true]
      ]
    ])


    <!-- Hiển thị thông báo -->
    <div style="min-height: 40px; margin-bottom: 20px;">
      @if (session('message'))
          <div class="alert alert-success">{{ session('message') }}</div>
      @endif
      @if (session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      @if (session('error'))
          <div class="alert alert-danger">{{ session('error') }}</div>
      @endif
    </div>

    <div class="row w3-res-tb">
      <div class="col-sm-5">
        <form method="GET" action="{{ url('/manage-order') }}" class="form-inline">
          <div class="form-group mr-2">
            <input type="text" name="keyword" class="form-control"
                  placeholder="Tìm theo mã hoặc tên khách hàng..."
                  value="{{ request('keyword') }}">
          </div>

          <div class="form-group mr-2">
            <select name="status" class="form-control">
              <option value="">-- Trạng thái đơn --</option>
              @foreach(App\Models\Order::getStatusOptions() as $status)
                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                  {{ $status }}
                </option>
              @endforeach
            </select>
          </div>

          <button type="submit" class="btn btn-primary">Tìm kiếm</button>
          <a href="{{ url('/manage-order') }}" class="btn btn-secondary">Xóa lọc</a>
        </form>
      </div>
    </div>


    <div class="table-responsive">
      <table class="table table-striped b-t b-light">
        <thead>
          <tr>
            <th style="width: 8%; text-align: center;">Mã đơn</th>
            <th style="width: 10%; text-align: center;">Tên khách hàng</th>
            <th style="width: 10%; text-align: center;">Tổng tiền</th>
            <th style="width: 15%; text-align: center;">Phương thức thanh toán</th>
            <th style="width: 15%; text-align: center;">Thanh toán</th>
            <th style="width: 20%; text-align: center;">Trạng thái</th>
            <th style="width: 10%; text-align: center;">Ngày tạo</th>
            <th style="width: 15%; text-align: center;">Hành động</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($orders as $order)
          <tr>
            <td class="text-center">{{ $order->order_id }}</td>
            <td class="text-center">{{ $order->order_name }}</td>
            <td class="text-center">{{ number_format($order->total_price, 0, ',', '.') }} đ</td>

            {{-- Phương thức thanh toán --}}
            <td class="text-center">
              {{ optional($order->payment)->payment_method ?? 'Chưa rõ' }}
            </td>

            {{-- Cột THÀNH TOÁN (trạng thái payment) --}}
            <td class="text-center">
              @php
                $payStatus = optional($order->payment)->payment_status ?? 'Chưa thanh toán';
                $badgeClass = match($payStatus) {
                    'Đã thanh toán'        => 'bg-success',
                    'Đang chờ xử lý'       => 'bg-warning',
                    'Thanh toán thất bại'  => 'bg-danger',
                    default                 => 'bg-secondary',
                };
              @endphp

              <span class="badge {{ $badgeClass }}">{{ $payStatus }}</span>
              @if(optional($order->payment)->payment_method)
                <div><small class="text-muted">({{ strtoupper(optional($order->payment)->payment_method) }})</small></div>
              @endif
            </td>

            {{-- Cột TRẠNG THÁI ĐƠN (logic cũ) --}}
            <td class="text-center">
              @if($order->status === App\Models\Order::STATUS_COMPLETED ||
                  $order->status === App\Models\Order::STATUS_CANCELLED)

                <span class="badge {{ $order->status === App\Models\Order::STATUS_COMPLETED ? 'bg-success' : 'bg-danger' }}">
                  {{ $order->status }}
                </span>

              @else
                <form id="update-status-{{ $order->order_id }}"
                      action="{{ route('order_update_status', $order->order_id) }}"
                      method="POST"
                      class="d-flex align-items-center gap-2"
                      style="display: inline-flex;">
                  @csrf
                  @method('PUT')

                  <select name="status" class="form-select form-select-sm" style="width:auto; margin-left: 5px;">
                    @foreach(App\Models\Order::getStatusOptions() as $status)
                      <option value="{{ $status }}" {{ $order->status === $status ? 'selected' : '' }}>
                        {{ $status }}
                      </option>
                    @endforeach
                  </select>

                  <button type="button" class="btn btn-sm btn-primary"
                          onclick="document.getElementById('update-status-{{ $order->order_id }}').submit();"
                          style="margin-left: 5px;">
                    Cập nhật
                  </button>
                </form>

                {{-- Nút "Đánh dấu đã giao" (đang ẩn theo yêu cầu) --}}
                @if($order->status !== App\Models\Order::STATUS_COMPLETED)
                  <form action="{{ route('order_mark_delivered', $order->order_id) }}"
                        method="POST" style="margin-top: 5px; display:none;">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-sm btn-success">Đánh dấu đã giao</button>
                  </form>
                @endif
              @endif
            </td>

            <td class="text-center">{{ $order->created_at->format('d/m/Y H:i') }}</td>

            <td class="text-center">
              <a href="{{ route('order_detail', $order->order_id) }}" class="btn btn-info btn-sm">Xem</a>
              <a href="{{ route('order_delete', $order->order_id) }}"
                 onclick="return confirm('Bạn có chắc muốn xóa đơn hàng này không?')"
                 class="btn btn-danger btn-sm">Xóa</a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Legend trạng thái thanh toán --}}
    <div class="mt-3" style="padding: 10px 15px;">
      <strong>Trạng thái thanh toán:</strong>
      <span class="badge bg-success" style="margin-left:8px;">Đã thanh toán</span>
      <span class="badge bg-warning" style="margin-left:8px;">Đang chờ xử lý</span>
      <span class="badge bg-danger"  style="margin-left:8px;">Thanh toán thất bại</span>
      <span class="badge bg-secondary" style="margin-left:8px;">Chưa thanh toán</span>
    </div>

    <footer class="panel-footer">
      <div class="row">
        <div class="col-sm-5 text-center">
          <small class="text-muted inline m-t-sm m-b-sm">
            Hiển thị {{ $orders->firstItem() }} đến {{ $orders->lastItem() }} của {{ $orders->total() }} đơn hàng
          </small>
        </div>
        <div class="col-sm-7 text-right text-center-xs">
          {{ $orders->links() }}
        </div>
      </div>
    </footer>
  </div>
</div>

@endsection
