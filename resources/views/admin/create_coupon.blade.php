@extends('admin_layout')
@section('admin_content')

   <div class="row">
            <div class="col-lg-12">
                <section class="panel">
                    <header class="panel-heading">
                        THÊM MÃ GIẢM GIÁ
                    </header>
                    <div class="panel-body">

                        @include('partials.breadcrumb', [
                            'items' => [
                                ['label' => 'Bảng điều khiển', 'url' => URL::to('/dashboard'), 'icon' => 'fa fa-home'],
                                ['label' => 'Mã giảm giá', 'url' => URL::to('/all-coupons'), 'icon' => 'fa fa-ticket'],
                                ['label' => 'Thêm Mã giảm giá', 'active' => true, 'icon' => 'fa fa-plus']
                            ]
                        ])


                        {{-- Hiển thị thông báo --}}
                        <div style="min-height: 50px; margin-bottom: 15px;">
                            @if (session('message'))
                                <div class="alert alert-success">
                                    {{ session('message') }}
                                </div>
                            @endif
                        </div>
                        {{-- Form thêm danh mục --}}
                        <div class="position-center">
                            <form id="couponFormCreate" action="{{ route('save_coupon') }}" method="post">
                                @csrf

                                {{-- Mã giảm giá --}}
                                <div class="form-group">
                                    <label>Mã giảm giá:</label>
                                    <input type="text" name="coupon_code" class="form-control"
                                        placeholder="Nhập mã giảm giá"
                                        value="{{ old('coupon_code') }}"
                                        data-validation="length"
                                        data-validation-length="3-50"
                                        data-validation-error-msg="Mã giảm giá phải từ 3 đến 50 ký tự">
                                    @error('coupon_code')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Số lượng --}}
                                <div class="form-group">
                                    <label>Số lượng:</label>
                                    <input type="number" name="coupon_quantity" class="form-control"
                                        placeholder="Số lượng mã"
                                        min="1"
                                        value="{{ old('coupon_quantity') }}"
                                        data-validation="number"
                                        data-validation-allowing="range[1;1000000]"
                                        data-validation-error-msg="Số lượng phải là số nguyên >= 1">
                                    @error('coupon_quantity')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                               {{-- Ngày bắt đầu --}}
                                <div class="form-group">
                                    <label>Ngày bắt đầu:</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control"
                                        value="{{ old('start_date') }}">
                                    @error('start_date') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                {{-- Ngày kết thúc --}}
                                <div class="form-group">
                                    <label>Ngày kết thúc:</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control"
                                        value="{{ old('end_date') }}">
                                    @error('end_date') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                {{-- Giảm theo % --}}
                                <div class="form-group">
                                    <label>Giảm theo %</label>
                                    <input type="number" name="discount_percent"
                                        value="{{ old('discount_percent', 0) }}"
                                        class="form-control" min="0" max="100" step="0.01"
                                        placeholder="0 - 100">
                                    <small>Chỉ nhập nếu giảm % (0–100). Nếu dùng số tiền, để 0.</small>
                                    @error('discount_percent')
                                        <small class="text-danger d-block">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Giảm theo số tiền --}}
                                <div class="form-group">
                                    <label>Giảm theo số tiền (VNĐ)</label>
                                    <input type="number" name="discount_amount"
                                        value="{{ old('discount_amount', 0) }}"
                                        class="form-control" min="0" step="1"
                                        placeholder="0 = không dùng giảm theo tiền">
                                    <small>Chỉ nhập nếu giảm theo số tiền. Nếu dùng %, để 0.</small>
                                    @error('discount_amount')
                                        <small class="text-danger d-block">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Giá trị tối thiểu --}}
                                <div class="form-group">
                                    <label>Giá trị đơn hàng tối thiểu (VNĐ):</label>
                                    <input type="number" name="min_order_value" class="form-control"
                                        min="0" step="1"
                                        value="{{ old('min_order_value', 0) }}">
                                    @error('min_order_value')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Trạng thái --}}
                                <div class="form-group">
                                    <label>Trạng thái:</label>
                                    <select name="status" class="form-control">
                                        <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Hiện</option>
                                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Ẩn</option>
                                    </select>
                                    @error('status')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Nút hành động --}}
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">Thêm mã giảm giá</button>
                                    <button type="reset" class="btn btn-warning"
                                            onclick="return confirm('Bạn có chắc muốn khôi phục dữ liệu ban đầu?')">
                                        Khôi phục
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>

<script>
(function () {
  const start = document.getElementById('start_date');
  const end   = document.getElementById('end_date');
  const today = new Date().toISOString().slice(0,10);

  // end_date không được trước hôm nay
  if (end) end.min = today;

  // Khi chọn start_date, yêu cầu end_date >= max(start_date, today)
  function updateEndMin() {
    if (!end) return;
    const s = start?.value || today;
    end.min = (s > today) ? s : today;
    if (end.value && end.value < end.min) end.value = '';
  }

  start?.addEventListener('change', updateEndMin);
  updateEndMin();
})();
</script>

@endsection

