@extends('welcome')

@section('content')

<section id="cart_items">
  <div class="container">
    <!--breadcrums-->
    <div class="breadcrumbs">
      <ol class="breadcrumb">
        <li><a href="{{ url('/') }}">Home</a></li>
        <li><a href="{{ route('show_cart', ['restore' => 1]) }}">Giỏ hàng</a></li>
        <li class="active">Thanh toán</li>
      </ol>
    </div>
    <!--/breadcrums-->

    {{-- Thông báo lỗi từ server (tổng) --}}
    @if ($errors->any())
      <div class="alert alert-danger">
        <strong>Vui lòng kiểm tra lại:</strong>
        <ul style="margin:8px 0 0 18px;">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="shopper-informations">
      <div class="row">
        <div class="col-sm-12 clearfix">
          <div class="bill-to">
            <p>Điền thông tin gửi hàng</p>

            <div class="form-two">
              <form action="{{ route('save-order') }}" method="post" novalidate>
                @csrf

                {{-- Coupon (ẩn) --}}
                <input type="hidden" name="coupon_id" value="{{ session('coupon_id') }}">
                @error('coupon_id')
                  <small class="help-block text-danger">{{ $message }}</small>
                @enderror

                {{-- Các item đã chọn (ẩn) --}}
                @foreach($cartItems as $item)
                  <input type="hidden" name="selected_items[]" value="{{ $item->id }}">
                @endforeach
                @error('selected_items')
                  <small class="help-block text-danger">{{ $message }}</small>
                @enderror

                <div class="form-group @error('order_email') has-error @enderror">
                  <input type="email" name="order_email" placeholder="Email"
                         class="form-control @error('order_email') is-invalid @enderror"
                         value="{{ old('order_email') }}">
                  @error('order_email')
                    <small class="help-block text-danger">{{ $message }}</small>
                  @enderror
                </div>

                <div class="form-group @error('order_name') has-error @enderror">
                  <input type="text" name="order_name" placeholder="Họ và tên"
                         class="form-control @error('order_name') is-invalid @enderror"
                         value="{{ old('order_name') }}"
                         minlength="2" maxlength="100" required>
                  @error('order_name')
                    <small class="help-block text-danger">{{ $message }}</small>
                  @enderror
                </div>

                <div class="form-group @error('order_phone') has-error @enderror">
                  <input type="tel" name="order_phone" placeholder="Số điện thoại"
                         class="form-control @error('order_phone') is-invalid @enderror"
                         value="{{ old('order_phone') }}"
                         pattern="^0[0-9]{9,10}$"
                         title="Số điện thoại bắt đầu bằng 0 và dài 10-11 chữ số"
                         required>
                  @error('order_phone')
                    <small class="help-block text-danger">{{ $message }}</small>
                  @enderror
                </div>

                <div class="form-group @error('order_address') has-error @enderror">
                  <input type="text" name="order_address" placeholder="Địa chỉ nhận hàng"
                         class="form-control @error('order_address') is-invalid @enderror"
                         value="{{ old('order_address') }}"
                         minlength="5" required>
                  @error('order_address')
                    <small class="help-block text-danger">{{ $message }}</small>
                  @enderror
                </div>

                <div class="form-group @error('order_note') has-error @enderror">
                  <textarea name="order_note" placeholder="Ghi chú đơn hàng của bạn" rows="5"
                            class="form-control @error('order_note') is-invalid @enderror">{{ old('order_note') }}</textarea>
                  @error('order_note')
                    <small class="help-block text-danger">{{ $message }}</small>
                  @enderror
                </div>

          </div> {{-- /.form-two --}}
        </div>
      </div>

      <div class="payment-title" style="margin: 40px;"><h4>Chọn hình thức thanh toán</h4></div>

      <div class="payment-section">

        <div class="payment-options @error('payment_option') has-error @enderror">
          <!-- <label class="payment-option">
            <input type="radio" name="payment_option" value="ATM"
              {{ old('payment_option')==='ATM' ? 'checked' : '' }} required>
            <img src="{{ asset('frontend/images/atm.jpg') }}" alt="ATM" class="payment-icon">
            Thanh toán bằng thẻ ATM
          </label> -->

          <label class="payment-option">
            <input type="radio" name="payment_option" value="MOMO"
              {{ old('payment_option')==='MOMO' ? 'checked' : '' }} required>
            <img src="{{ asset('frontend/images/momo.jpg') }}" alt="MOMO" class="payment-icon">
            Thanh toán bằng MOMO
          </label>

          <label class="payment-option">
            <input type="radio" name="payment_option" value="COD"
              {{ old('payment_option','COD')==='COD' ? 'checked' : '' }} required>
            <img src="{{ asset('frontend/images/cod.jpg') }}" alt="COD" class="payment-icon">
            Thanh toán khi nhận hàng (COD)
          </label>

          @error('payment_option')
            <small class="help-block text-danger">{{ $message }}</small>
          @enderror
        </div>

        <input type="submit" value="Đặt hàng" name="send_order_place" class="btn btn-primary btn-sm order-btn">
      </div>

              </form> {{-- Đóng form --}}

      <div class="review-payment">
        <h2>Xem lại giỏ hàng</h2>
      </div>

      <div class="table-responsive cart_info">
        <table class="table table-condensed" style="width:1050px;">
          <thead class="cart-table-head">
            <tr class="cart_menu text-center">
              <td>Ảnh sản phẩm</td>
              <td style="width:30%;">Tên sản phẩm</td>
              <td>Giá sản phẩm</td>
              <td>Số lượng</td>
              <td>Tổng tiền</td>
            </tr>
          </thead>
          <tbody>
            @foreach ($cartItems as $item)
              @php
                $p   = $item->product;

                // chỉ dùng promo_price nếu còn hiệu lực, ngược lại product_price
                $now   = now();
                $start = $p->promo_start ? \Carbon\Carbon::parse($p->promo_start) : null;
                $end   = $p->promo_end   ? \Carbon\Carbon::parse($p->promo_end)->endOfDay() : null;

                $isPromo = !is_null($p->promo_price)
                  && $p->promo_price > 0
                  && $p->promo_price < $p->product_price
                  && (!$start || $start->lte($now))
                  && (!$end   || $end->gte($now));

                // đơn giá đang áp dụng
                $unit = (float) ($isPromo ? $p->promo_price : $p->product_price);

                // tổng tiền dòng
                $line = $unit * (int) $item->quantity;
              @endphp

              <tr class="text-center align-middle" data-id="{{ $item->id }}">
                <td>
                  <img src="{{ asset('uploads/products/' . $p->product_image) }}"
                       alt="{{ $p->product_name }}" style="width:100px;">
                </td>
                <td><h4>{{ $p->product_name }}</h4></td>
                <td>{{ number_format($unit, 0, ',', '.') }}₫</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($line, 0, ',', '.') }}₫</td>
              </tr>
            @endforeach
          </tbody>

          <tfoot>
            <tr>
              <td>Tạm tính</td>
              <td>{{ number_format($subtotal, 0, ',', '.') }}₫</td>
            </tr>
            <tr>
              <td>Giảm giá</td>
              <td>-{{ number_format($discountAmount ?? $discount ?? 0, 0, ',', '.') }}₫</td>
            </tr>
            <tr>
              <td>Thuế (5%)</td>
              <td>{{ number_format($tax, 0, ',', '.') }}₫</td>
            </tr>
            <tr>
              <td><strong>Tổng thanh toán</strong></td>
              <td><strong>{{ number_format($total, 0, ',', '.') }}₫</strong></td>
            </tr>
          </tfoot>
        </table>
      </div>

    </div>
  </div>

</div>
</section> <!--/#cart_items-->

<style>
.payment-section { margin: 30px 70px; }
.payment-title { font-size: 20px; font-weight: bold; margin-bottom: 20px; }
.payment-options { display: flex; flex-direction: column; gap: 15px; }
.payment-option {
  display: flex; align-items: center; gap: 10px; padding: 12px 15px;
  border: 1px solid #ccc; border-radius: 8px; transition: background .2s ease; cursor: pointer;
}
.payment-option:hover { background: #f5f5f5; }
.payment-option input[type="radio"] { transform: scale(1.2); }
.payment-icon { width: 32px; height: 32px; object-fit: contain; }
.order-btn { margin-top: 25px; padding: 10px 20px; font-weight: bold; }
.has-error .form-control, .is-invalid { border-color: #e74c3c; }
.help-block.text-danger { margin-top: 4px; display: block; }

/* (tuỳ chọn) làm nổi bật input lỗi khi focus */
.is-invalid:focus {
  outline: 0;
  box-shadow: 0 0 0 2px rgba(231,76,60,.15);
}
</style>

{{-- (tuỳ chọn) tự cuộn tới ô lỗi đầu tiên --}}
@if ($errors->any())
<script>
  (function() {
    var el = document.querySelector('.is-invalid, .has-error .form-control');
    if (el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'center' });
      setTimeout(function(){ try { el.focus(); } catch(e){} }, 250);
    }
  })();
</script>
@endif

@endsection
