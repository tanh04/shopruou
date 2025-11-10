@extends('welcome')

@section('content')
<section id="wishlist_items">
    @if(!Auth::check())
        <p>Bạn cần <a href="{{ route('login') }}">đăng nhập</a> để xem danh sách yêu thích.</p>
    @else
        <div class="col-sm-12">

            <!-- {{-- Thông báo flash --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="breadcrumbs">
                <ol class="breadcrumb">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li class="active">Yêu thích</li>
                </ol>
            </div> -->

            @if($wishlist->isEmpty())
                <p class="text-center">Danh sách yêu thích của bạn đang trống.</p>
            @else
                {{-- Form tổng hợp thao tác --}}
                <form id="wishlist-form" method="POST" action="{{ route('handle_wishlist_action') }}">
                    @csrf

                    <div id="wishlist-message" style="min-height:25px; margin-bottom:10px;"></div>

                    <div class="table-responsive cart_info">
                        <table class="table table-condensed">
                            <thead class="cart-table-head">
                                <tr class="cart_menu text-center">
                                    <td style="width: 5%;">
                                        <input type="checkbox" id="select-all-wl">
                                    </td>
                                    <td style="width: 15%;">Ảnh sản phẩm</td>
                                    <td style="width: 35%;">Tên sản phẩm</td>
                                    <td style="width: 15%;">Giá</td>
                                    <td style="width: 15%;">Trạng thái</td>
                                    <td style="width: 15%;">Thao tác</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($wishlist as $item)
                                    @php
                                        $product     = $item->product; // belongsTo Product
                                        $isActive    = $product && (int)$product->product_status === 1;
                                        $inStock     = $product && (int)($product->product_stock ?? 0) > 0;
                                        $canAddToCart= $product && $isActive && $inStock;
                                        $price       = $product ? (int)$product->product_price : 0;
                                        $image       = $product?->product_image;
                                    @endphp

                                    <tr class="text-center align-middle" data-id="{{ $item->id }}">
                                        <td>
                                            <input type="checkbox"
                                                   name="selected_items[]"
                                                   class="wl-item-checkbox"
                                                   value="{{ $item->id }}"
                                                   @if(!$canAddToCart) disabled @endif>
                                        </td>

                                        <td>
                                            @if($product && $image)
                                                <img src="{{ asset('uploads/products/' . $image) }}"
                                                     alt="{{ $product->product_name }}"
                                                     style="width: 100px;">
                                            @elseif($product)
                                                <span class="text-muted">Không có ảnh</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>

                                        <td class="text-left">
                                            <h4 class="m-0">
                                                @if($product)
                                                    {{ $product->product_name }}
                                                @else
                                                    <span class="text-muted">Sản phẩm đã bị xóa</span>
                                                @endif
                                            </h4>
                                            @if($product && !$isActive)
                                                <small class="text-danger d-block">Ngừng kinh doanh</small>
                                            @endif
                                            @if($product && $isActive && !$inStock)
                                                <small class="text-warning d-block">Tạm hết hàng</small>
                                            @endif
                                        </td>

                                        <td>
                                            <p class="wl_price" data-price="{{ $price }}">
                                                {{ number_format($price,0,',','.') }}₫
                                            </p>
                                        </td>

                                        <td>
                                            @if(!$product)
                                                <span class="label label-default">Không khả dụng</span>
                                            @elseif(!$isActive)
                                                <span class="label label-danger">Ngừng kinh doanh</span>
                                            @elseif(!$inStock)
                                                <span class="label label-warning">Hết hàng</span>
                                            @else
                                                <span class="label label-success">Có thể mua</span>
                                            @endif
                                        </td>

                                        <td>
                                            {{-- Chuyển 1 item sang giỏ (nếu khả dụng) --}}
                                            @if($canAddToCart)
                                                <button type="submit"
                                                        name="action"
                                                        style="margin-top: 0px;"
                                                        value="move_single:{{ $item->id }}"
                                                        class="btn btn-primary btn-sm">
                                                    Thêm vào giỏ
                                                </button>
                                            @else
                                                <button type="button"
                                                        class="btn btn-default btn-sm"
                                                        style="margin-top: 0px;"
                                                        disabled
                                                        title="Sản phẩm không khả dụng để mua">
                                                    Thêm vào giỏ
                                                </button>
                                            @endif

                                            {{-- Xóa item khỏi wishlist --}}
                                            <button type="submit"
                                                    class="btn btn-danger btn-sm"
                                                    name="action"
                                                    value="remove_single:{{ $item->id }}"
                                                    onclick="return confirm('Xóa sản phẩm này khỏi yêu thích?');">
                                                Xóa
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Khu tổng tiền tham khảo (không áp dụng coupon/tax cho wishlist) --}}
                    <section id="do_action">
                        <div class="total_area">
                            <ul>
                                <li>Tổng tạm tính (mục chọn được) <span id="wl_subtotal">0₫</span></li>
                                <li class="text-muted"><small>* Đây chỉ là ước tính để tham khảo.</small></li>
                            </ul>

                            {{-- Thao tác cho danh sách đã chọn --}}
                            <div class="mt-2">
                                <button type="submit"
                                        name="action"
                                        value="move_selected_to_cart"
                                        style="margin-top: 0px;"
                                        class="btn btn-default">
                                    Thêm các mục đã chọn vào giỏ
                                </button>

                                <button type="submit"
                                        name="action"
                                        value="remove_selected"
                                        class="btn btn-default"
                                        onclick="return confirm('Xóa các mục đã chọn khỏi yêu thích?');">
                                    Xóa các mục đã chọn
                                </button>
                            </div>
                        </div>
                    </section>
                </form>
            @endif
        </div>
    @endif
</section>
@endsection

<style>
    .cart_menu td { 
        vertical-align: middle; 
            border: none;
        font-weight: 600;
        text-align: center;
    }
    .cart_menu {
        background: #ff9800;
        color: #fff;
  }
</style>

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const msgBox     = document.getElementById("wishlist-message");
    const selectAll  = document.getElementById("select-all-wl");
    const checkboxes = document.querySelectorAll(".wl-item-checkbox");
    const subtotalEl = document.getElementById("wl_subtotal");
    const form       = document.getElementById("wishlist-form");

    function showMessage(type, text){
        if(!msgBox) return;
        msgBox.innerHTML = `<div class="alert alert-${type}">${text}</div>`;
        setTimeout(() => msgBox.innerHTML = "", 3000);
    }

    function formatCurrency(n){ n = Number(n)||0; return n.toLocaleString("vi-VN")+"₫"; }

    function refreshSubtotal() {
        let sum = 0;
        checkboxes.forEach(cb => {
            if (cb.checked) {
                const row = cb.closest("tr");
                const priceEl = row.querySelector(".wl_price");
                const price = parseInt(priceEl?.dataset.price || "0");
                sum += price;
            }
        });
        subtotalEl.innerText = formatCurrency(sum);
    }

    // Check all
    selectAll?.addEventListener("change", function(){
        checkboxes.forEach(cb => {
            if (!cb.disabled) cb.checked = this.checked;
        });
        refreshSubtotal();
    });

    // Each item
    checkboxes.forEach(cb => {
        cb.addEventListener("change", function(){
            if (!this.checked && selectAll) selectAll.checked = false;
            refreshSubtotal();
        });
    });

    // Intercept submit để nhắc nếu chưa chọn khi dùng action theo lô
    form?.addEventListener("submit", function(e){
        const submitter = e.submitter;
        const actionVal = submitter?.value || "";

        const selected = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);

        // Các action hàng loạt cần có lựa chọn
        const bulkActions = ["move_selected_to_cart", "remove_selected"];
        if (bulkActions.includes(actionVal) && selected.length === 0) {
            e.preventDefault();
            showMessage("warning", "Vui lòng chọn ít nhất một sản phẩm.");
        }

        // Với move_single:/remove_single: thì cứ để submit mặc định
    });

    // Khởi tạo
    refreshSubtotal();
});
</script>
@endsection
