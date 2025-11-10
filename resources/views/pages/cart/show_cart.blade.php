@extends('welcome')

@section('content')
<section id="cart_items">
    @if(!Auth::check())
        <p>Bạn cần <a href="{{ route('login') }}">đăng nhập</a> để xem giỏ hàng.</p>
    @else
        <div class="col-sm-12">
            <div class="breadcrumbs">
                <ol class="breadcrumb">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li class="active">Giỏ hàng</li>
                </ol>
            </div>

            @if($cartItems->isEmpty())
                <p class="text-center">Giỏ hàng của bạn đang trống.</p>
            @else
                <form id="cart-form" method="POST" action="{{ route('handle_cart_action') }}">
                    @csrf
                    <div class="table-responsive cart_info">
                        <table class="table table-condensed">
                            <thead class="cart-table-head">
                                <tr class="cart_menu text-center">
                                    <td style="width: 5%;">
                                        <input type="checkbox" id="select-all">
                                    </td>
                                    <td style="width: 15%;">Ảnh sản phẩm</td>
                                    <td style="width: 30%;">Tên sản phẩm</td>
                                    <td style="width: 10%;">Giá sản phẩm</td>
                                    <td style="width: 20%;">Số lượng</td>
                                    <td style="width: 10%;">Tổng tiền</td>
                                    <td style="width: 10%;">Thao tác</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cartItems as $item)
                                    @php
                                      $p     = $item->product;
                                      $now   = now();
                                      $start = $p->promo_start ? \Carbon\Carbon::parse($p->promo_start) : null;
                                      $end   = $p->promo_end   ? \Carbon\Carbon::parse($p->promo_end)->endOfDay() : null;

                                      $isPromo = !is_null($p->promo_price)
                                          && $p->promo_price > 0
                                          && $p->promo_price < $p->product_price
                                          && (!$start || $start->lte($now))
                                          && (!$end   || $end->gte($now));

                                      $unit = $isPromo ? $p->promo_price : $p->product_price; // chỉ lấy promo_price nếu còn hạn
                                    @endphp


                                    <tr class="text-center align-middle" data-id="{{ $item->id }}">
                                        <td>
                                            <input type="checkbox" name="selected_items[]" class="item-checkbox" value="{{ $item->id }}">
                                        </td>
                                        <td>
                                            <img src="{{ asset('uploads/products/' . $p->product_image) }}"
                                                 alt="{{ $p->product_name }}"
                                                 style="width: 100px;">
                                        </td>
                                        <td><h4>{{ $p->product_name }}</h4></td>

                                        {{-- GIÁ SẢN PHẨM (dùng đơn giá hiệu lực) --}}
                                        <td>
                                          <span class="unit-price" data-unit="{{ $unit }}">
                                            {{ number_format($unit, 0, ',', '.') }}₫
                                          </span>
                                        </td>

                                        <td>
                                            <div class="cart_quantity_button">
                                                <button type="button" class="btn btn-sm quantity-decrease" data-id="{{ $item->id }}">−</button>
                                                <input class="cart_quantity_input"
                                                       type="text"
                                                       value="{{ $item->quantity }}"
                                                       readonly
                                                       style="width:45px;"
                                                       data-id="{{ $item->id }}"
                                                       data-stock="{{ $p->product_stock }}">
                                                <button type="button" class="btn btn-sm quantity-increase" data-id="{{ $item->id }}">+</button>
                                            </div>
                                        </td>

                                        {{-- TỔNG TIỀN DÒNG (đơn giá hiệu lực * số lượng) --}}
                                        <td>
                                            <p class="cart_total_price"
                                               data-unit="{{ $unit }}"               {{-- 👈 dùng data-unit --}}
                                               data-item-total="{{ $item->id }}">
                                                {{ number_format($unit * $item->quantity,0,',','.') }}₫
                                            </p>
                                        </td>

                                        <td>
                                          <button type="submit"
                                                  class="btn btn-danger btn-sm"
                                                  form="remove-{{ $item->id }}"
                                                  onclick="return confirm('Bạn có chắc chắn muốn xóa?');">
                                            Xóa
                                          </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- KHU VỰC TÍNH TỔNG / COUPON --}}
                    <section id="do_action">
                        {{-- Chọn coupon --}}
                        <div class="form-group">
                            <label for="coupon_id">Chọn mã giảm giá:</label>
                            <select id="coupon_id" name="coupon_id" class="form-control">
                                <option value="">-- Chọn mã giảm giá --</option>
                                @foreach($all_coupons as $coupon)
                                    <option value="{{ $coupon->coupon_id }}">
                                        {{ $coupon->coupon_code }}
                                        @if(!is_null($coupon->discount_amount) && $coupon->discount_amount > 0)
                                            Giảm {{ number_format($coupon->discount_amount, 0, ',', '.') }}₫
                                        @elseif(!is_null($coupon->discount_percent) && $coupon->discount_percent > 0)
                                            Giảm {{ $coupon->discount_percent }}%
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="cart-message" style="min-height:25px; margin-bottom:10px;"></div>

                        <div class="total_area">
                            <ul>
                                <li>Tổng <span id="subtotal">0₫</span></li>
                                <li>Thuế (5%) <span id="tax5">0₫</span></li>
                                <li>Giảm giá <span id="discount_amount">0₫</span></li>
                                <li>Thành tiền <span id="total_price">0₫</span></li>
                            </ul>
                            <button type="submit" name="action" value="checkout" class="btn btn-default">Thanh toán</button>
                        </div>
                    </section>
                </form>
            @endif
        </div>

        @foreach ($cartItems as $item)
            <form id="remove-{{ $item->id }}"
                  action="{{ route('cart.remove', $item->id) }}"
                  method="POST" style="display:none;">
                @csrf
                @method('DELETE')
            </form>
        @endforeach

    @endif
</section>
@endsection

<style>
    .cart_quantity_button {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
    }
</style>

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
  const messageBox = document.getElementById("cart-message");
  const selectAll  = document.getElementById("select-all");
  const checkboxes = document.querySelectorAll(".item-checkbox");
  const cartForm   = document.getElementById("cart-form");

  function showMessage(type, text) {
    if (!messageBox) return;
    messageBox.innerHTML = `<div class="alert alert-${type}">${text}</div>`;
    setTimeout(() => { messageBox.innerHTML = ""; }, 3000);
  }

  function formatCurrency(n){ n = Number(n)||0; return n.toLocaleString("vi-VN")+"₫"; }

  function setTotalsZero(){
    document.getElementById("subtotal").innerText        = "0₫";
    document.getElementById("discount_amount").innerText = "0₫";
    document.getElementById("tax5").innerText            = "0₫";
    document.getElementById("total_price").innerText     = "0₫";
  }

  function collectPayload() {
    const selectedItems = Array.from(document.querySelectorAll(".item-checkbox:checked"))
                          .map(cb => cb.value);
    const couponId = document.getElementById("coupon_id")?.value || null;
    return { selected_items: selectedItems, coupon_id: couponId };
  }

  function refreshTotals() {
    const payload = collectPayload();
    if (!payload.selected_items || payload.selected_items.length === 0) {
      setTotalsZero();
      return;
    }

    fetch("{{ route('cart.updatePrice') }}", {
      method: "POST",
      headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
      body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
      if (!data.success) { setTotalsZero(); return; }

      document.getElementById("subtotal").innerText        = formatCurrency(data.subtotal_original);
      document.getElementById("discount_amount").innerText = formatCurrency(data.discount);
      document.getElementById("tax5").innerText            = formatCurrency(data.tax);
      document.getElementById("total_price").innerText     = formatCurrency(data.total);

      if (data.coupon_message) {
        showMessage("warning", data.coupon_message);
      }

      // (tuỳ chọn) cập nhật tổng từng dòng nếu server trả items
      if (Array.isArray(data.items)) {
        data.items.forEach(it => {
          const cell = document.querySelector(`[data-item-total="${it.id}"]`);
          if (cell) cell.innerText = formatCurrency(it.total);
        });
      }
    })
    .catch(() => setTotalsZero());
  }

  function updateQuantity(id, quantity, inputEl, rowEl) {
    fetch("{{ route('update_quantity') }}", {
      method: "POST",
      headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
      body: JSON.stringify({ id, quantity })
    })
    .then(res => res.json())
    .then(data => {
      if (!data.success) { showMessage("danger", data.message || "Cập nhật số lượng thất bại"); return; }

      inputEl.value = data.quantity;

      // ✅ dùng đơn giá hiệu lực đã nhúng ở HTML (data-unit)
      const priceCell = rowEl.querySelector(".cart_total_price");
      const unit      = Number(priceCell?.dataset.unit) || 0;
      const totalItem = unit * data.quantity;
      if (priceCell) {
        priceCell.innerText = formatCurrency(totalItem);
        priceCell.setAttribute("data-item-total", id);
      }

      refreshTotals();
    })
    .catch(err => console.error(err));
  }

  // ===== Bind nút tăng/giảm số lượng =====
  document.querySelectorAll(".quantity-increase").forEach(btn => {
    btn.addEventListener("click", function() {
      const id = this.dataset.id;
      const input = document.querySelector(`input[data-id='${id}']`);
      const row = this.closest("tr");
      const current = parseInt(input.value);
      const stock   = parseInt(input.dataset.stock);
      if (current < stock) updateQuantity(id, current + 1, input, row);
      else showMessage("warning", "Số lượng sản phẩm đã đạt tối đa trong kho!");
    });
  });

  document.querySelectorAll(".quantity-decrease").forEach(btn => {
    btn.addEventListener("click", function() {
      const id = this.dataset.id;
      const input = document.querySelector(`input[data-id='${id}']`);
      const row = this.closest("tr");
      const current = parseInt(input.value);
      if (current > 1) updateQuantity(id, current - 1, input, row);
      else showMessage("warning", "Số lượng tối thiểu là 1 sản phẩm!");
    });
  });

  // ===== Checkbox chọn tất cả / từng dòng =====
  selectAll?.addEventListener("change", function() {
    checkboxes.forEach(cb => cb.checked = this.checked);
    refreshTotals();
  });

  checkboxes.forEach(cb => {
    cb.addEventListener("change", function() {
      if (!this.checked) selectAll.checked = false;
      refreshTotals();
    });
  });

  // ===== Đổi mã coupon -> refresh =====
  document.getElementById("coupon_id")?.addEventListener("change", refreshTotals);

  // ===== Submit form =====
  cartForm?.addEventListener("submit", function(e) {
    const submitButton = e.submitter;
    const action = submitButton?.value;
    if (action === "update") {
      e.preventDefault();
      refreshTotals();
    }
    // action === "checkout" -> submit bình thường
  });

  // Lần đầu mở trang: nếu chưa chọn item nào -> hiện 0₫
  refreshTotals();
});
</script>
@endsection
