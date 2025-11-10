@extends('admin_layout')
@section('admin_content')

<div class="row">
  <div class="col-lg-12">
    <section class="panel">
      <header class="panel-heading">
        CẬP NHẬT MÃ GIẢM GIÁ
      </header>

      <div class="panel-body">

        @section('breadcrumb')
          <x-breadcrumbs :items="[
              ['label' => 'Bảng điều khiển', 'url' => URL::to('/dashboard')],
              ['label' => 'Mã giảm giá', 'url' => URL::to('/all-coupons')],
              ['label' => 'Cập nhật mã giảm giá']
          ]" />
        @endsection
        {{-- Thông báo --}}
        <div style="min-height: 50px; margin-bottom: 15px;">
          @if (session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
          @endif
        </div>

        <div class="position-center">
          <form id="couponFormEdit" action="{{ url('/update-coupon/'.$coupon->coupon_id) }}" method="post" novalidate>
            @csrf

            {{-- Mã code --}}
            <div class="form-group">
              <label for="coupon_code">Mã code</label>
              <input type="text" name="coupon_code" id="coupon_code"
                    value="{{ old('coupon_code', $coupon->coupon_code) }}"
                    class="form-control" placeholder="Nhập mã code"
                    data-validation="length" data-validation-length="3-50"
                    data-validation-error-msg="Mã giảm giá phải từ 3 đến 50 ký tự">
              @error('coupon_code') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Số lượng --}}
            <div class="form-group">
              <label for="coupon_quantity">Số lượng mã</label>
              <input type="number" name="coupon_quantity" id="coupon_quantity"
                    value="{{ old('coupon_quantity', $coupon->coupon_quantity) }}"
                    class="form-control" min="1"
                    data-validation="number" data-validation-allowing="range[1;1000000]"
                    data-validation-error-msg="Số lượng phải là số nguyên ≥ 1">
              @error('coupon_quantity') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Ngày bắt đầu --}}
            <div class="form-group">
              <label for="start_date">Ngày bắt đầu</label>
              <input type="date" name="start_date" id="start_date" class="form-control"
                    value="{{ old('start_date', $coupon->start_date ? \Carbon\Carbon::parse($coupon->start_date)->format('Y-m-d') : '') }}">
              @error('start_date') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Ngày kết thúc --}}
            <div class="form-group">
              <label for="end_date">Ngày kết thúc</label>
              <input type="date" name="end_date" id="end_date" class="form-control"
                    value="{{ old('end_date', $coupon->end_date ? \Carbon\Carbon::parse($coupon->end_date)->format('Y-m-d') : '') }}">
              @error('end_date') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Giảm theo % --}}
            <div class="form-group">
              <label>Giảm theo %</label>
              <input type="number" name="discount_percent"
                    value="{{ old('discount_percent', $coupon->discount_percent ?? 0) }}"
                    class="form-control" min="0" max="100" step="0.01" placeholder="0 - 100">
              <small>Chỉ nhập nếu giảm % (0–100). Nếu dùng số tiền, để 0.</small>
              @error('discount_percent') <small class="text-danger d-block">{{ $message }}</small> @enderror
            </div>

            {{-- Giảm theo số tiền --}}
            <div class="form-group">
              <label>Giảm theo số tiền (đ)</label>
              <input type="number" name="discount_amount"
                    value="{{ old('discount_amount', $coupon->discount_amount ?? 0) }}"
                    class="form-control" min="0" step="1" placeholder="0 = không dùng giảm theo tiền">
              <small>Chỉ nhập nếu giảm số tiền. Nếu dùng %, để 0.</small>
              @error('discount_amount') <small class="text-danger d-block">{{ $message }}</small> @enderror
            </div>

            {{-- Giá trị đơn tối thiểu --}}
            <div class="form-group">
              <label for="min_order_value">Giá trị đơn hàng tối thiểu (đ)</label>
              <input type="number" name="min_order_value" id="min_order_value"
                    value="{{ old('min_order_value', $coupon->min_order_value ?? 0) }}"
                    class="form-control" min="0" step="1">
              @error('min_order_value') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Trạng thái --}}
            <div class="form-group">
              <label for="status">Trạng thái</label>
              <select name="status" id="status" class="form-control">
                <option value="1" {{ old('status', $coupon->status)==1 ? 'selected' : '' }}>Kích hoạt</option>
                <option value="0" {{ old('status', $coupon->status)==0 ? 'selected' : '' }}>Hủy kích hoạt</option>
              </select>
              @error('status') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Nút hành động --}}
            <div class="form-group">
              <button type="submit" class="btn btn-info">Cập nhật</button>
              <button type="reset" class="btn btn-warning"
                      onclick="return confirm('Khôi phục về giá trị ban đầu?')">Khôi phục</button>
            </div>
          </form>
        </div>
      </div>
    </section>
  </div>
</div>

{{-- Script nhỏ: nếu nhập % > 0 thì reset tiền = 0 và ngược lại --}}
<script>
document.addEventListener('DOMContentLoaded', function(){
  // Ràng buộc ngày
  const start = document.getElementById('start_date');
  const end   = document.getElementById('end_date');
  const today = new Date().toISOString().slice(0,10);

  if (end) end.min = today; // end_date không được trước hôm nay

  function syncEndMin() {
    if (!end) return;
    const s = start?.value || today;
    end.min = (s > today) ? s : today; // end >= max(start, today)
    if (end.value && end.value < end.min) end.value = '';
  }
  start?.addEventListener('change', syncEndMin);
  syncEndMin();

  // Chỉ 1 trong 2: % hoặc tiền
  const p = document.querySelector('input[name="discount_percent"]');
  const a = document.querySelector('input[name="discount_amount"]');
  if(p && a){
    p.addEventListener('input', ()=> { if(+p.value > 0){ a.value = 0; }});
    a.addEventListener('input', ()=> { if(+a.value > 0){ p.value = 0; }});
  }
});
</script>


@endsection
