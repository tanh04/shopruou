@extends('admin_layout')
@section('admin_content')

<div class="table-agile-info">
  <div class="panel panel-default">
    <div class="panel-heading">
      LIỆT KÊ MÃ GIẢM GIÁ
    </div>

    @include('partials.breadcrumb', [
      'items' => [
          ['label' => 'Bảng điều khiển', 'url' => URL::to('/dashboard'), 'icon' => 'fa fa-home'],
          ['label' => 'Mã giảm giá', 'url' => URL::to('/all-coupons'), 'icon' => 'fa fa-ticket'],
          ['label' => 'Danh sách', 'active' => true]
      ]
  ])

    <!-- Hiển thị thông báo -->
    <div style="min-height: 40px; margin-bottom: 20px;">
        @if (session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
    </div>

    <div class="row w3-res-tb">
      <div class="col-sm-6">
        <form method="GET" action="{{ url('/all-coupons') }}" class="form-inline">
          <div class="form-group mr-2">
            <input type="text" name="keyword" class="form-control"
                  placeholder="Tìm theo mã code..."
                  value="{{ request('keyword') }}">
          </div>

          <div class="form-group mr-2">
            <select name="status" class="form-control">
              <option value="">-- Trạng thái --</option>
              <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Hiển thị</option>
              <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Ẩn</option>
            </select>
          </div>

          <button type="submit" class="btn btn-primary">Tìm kiếm</button>
          <a href="{{ url('/all-coupons') }}" class="btn btn-default ml-2">Xóa lọc</a>
        </form>
      </div>
    </div>


    <div class="mb-3 text-right">
      <a href="{{ URL::to('/create-coupon') }}" class="btn btn-success">
          <i class="fa fa-plus"></i> Thêm mã giảm giá
      </a>
  </div>

    <div class="table-responsive">
    <table class="table table-striped b-t b-light">
        <thead>
        <tr>
            <th style="width: 3%; text-align: center;">ID</th>
            <th style="width: 10%; text-align: center;">Mã code</th>
            <th style="width: 8%;">Hiển thị</th>
            <th style="width: 7%; text-align: center;">Số lượng</th>
            <th style="width: 10%; text-align: center;">Hình thức</th>
            <th style="width: 12%; text-align: center;">Giá trị giảm</th>
            <th style="width: 15%; text-align: center;">Giá trị đơn hàng tối thiểu</th>
            <th style="width: 10%; text-align: center;">Ngày thêm</th>
        
            <th style="width: 15%; text-align: center;">Ngày cập nhật</th>
            <th style="width: 20%; text-align: center;">Hành động</th>
        </tr>
        </thead>
        <tbody>
          @foreach ($all_coupons as $coupon)
          <tr>
            <td class="text-center">{{ $coupon->coupon_id }}</td>
            <td class="text-center">{{ $coupon->coupon_code }}</td>
            <td class="text-center">
              @if ($coupon->status == 1)
                  <a href="{{ URL::to('/unactive-coupon/'.$coupon->coupon_id) }}" 
                    onclick="return confirm('Bạn có chắc muốn hủy kích hoạt mã giảm giá này?');">
                      <span class="fa fa-thumbs-up" style="font-size: 24px; color: blue;"></span>
                  </a>
              @else
                  <a href="{{ URL::to('/active-coupon/'.$coupon->coupon_id) }}">
                      <span class="fa fa-thumbs-down" style="font-size: 24px; color: red;"></span>
                  </a>
              @endif
          </td>
            <td class="text-center">{{ $coupon->coupon_quantity }}</td>

            <!-- Hình thức -->
            <td class="text-center">
                @if ($coupon->discount_percent)
                    Giảm %
                @elseif ($coupon->discount_amount)
                    Giảm theo tiền
                @else
                    --
                @endif
            </td>

            <!-- Giá trị giảm -->
            <td class="text-center">
                @if ($coupon->discount_percent)
                    {{ $coupon->discount_percent }} %
                @elseif ($coupon->discount_amount)
                    {{ number_format($coupon->discount_amount, 0, ',', '.') }} đ
                @else
                    0 đ
                @endif
            </td>

            <!-- Giá trị đơn hàng tối thiểu -->
            <td class="text-center">
                {{ number_format($coupon->min_order_value, 0, ',', '.') }} đ
            </td>

            <td class="text-center">{{ $coupon->created_at->format('d-m-Y') }}</td>
            <td class="text-center">{{ $coupon->updated_at->format('d-m-Y') }}</td>

            <td class="text-center">
                <a href="{{ URL::to('/edit-coupon/'.$coupon->coupon_id) }}" class="btn btn-sm btn-primary" title="Sửa">
                    <i class="fa fa-pencil-square-o text-success text-active"></i>
                </a>

                <a onclick="return confirm('Bạn có chắc muốn xóa mã này không?')" 
                  href="{{ URL::to('/delete-coupon/'.$coupon->coupon_id) }}" 
                  class="btn btn-sm btn-danger" title="Xóa">
                    <i class="fa fa-trash"></i>
                </a>
            </td>
          </tr>
          @endforeach
          </tbody>

    </table>
  
</div>
    {{-- Phân trang --}}
    <footer class="panel-footer">
        @include('partials.pagination', ['paginator' => $all_coupons, 'infoLabel' => 'coupon'])
    </footer>
@endsection
