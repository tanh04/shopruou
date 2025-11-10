@extends('welcome')

@section('content')
<div class="features_items">
  <h2 class="title text-center">SẢN PHẨM MỚI NHẤT</h2>

  <div class="row g-4">
    @foreach($products as $product)
        @php
        $isPromo = $product->promo_price
            && (!$product->promo_start || \Carbon\Carbon::parse($product->promo_start)->lte(now()))
            && (!$product->promo_end   || \Carbon\Carbon::parse($product->promo_end)->gte(now()));

        $discount = $isPromo && $product->product_price > 0
            ? max(1, round(100 * (1 - ($product->promo_price / $product->product_price))))
            : null;

        $brand    = $product->brand->brand_name ?? '—';
        $cat      = $product->category->category_name ?? '—';
        $alcohol  = $product->alcohol_percent ?? null;
        $grape    = trim((string)($product->grape_variety ?? ''));
        @endphp

        <div class="col-12 col-sm-6 col-md-4">
        <div class="wine-card">
            {{-- ribbon giảm giá --}}
            @if($isPromo && $discount)
            <div class="discount-ribbon">-{{ $discount }}%</div>
            @endif

            {{-- ảnh --}}
            <a class="thumb" href="{{ URL::to('/product-details/' . $product->product_id) }}" aria-label="{{ $product->product_name }}">
            <img src="{{ asset('uploads/products/' . $product->product_image) }}" alt="{{ $product->product_name }}">
            </a>

            {{-- giá --}}
            <div class="price-wrap">
            @if($isPromo)
                <div class="old">{{ number_format($product->product_price, 0, ',', '.') }} đ</div>
                <div class="new">{{ number_format($product->promo_price, 0, ',', '.') }} đ</div>
            @else
                <div class="new">{{ number_format($product->product_price, 0, ',', '.') }} đ</div>
            @endif
            </div>

            {{-- tên --}}
            <h3 class="name" title="{{ $product->product_name }}">{{ $product->product_name }}</h3>

            {{-- thuộc tính --}}
            <ul class="attrs">
            <li><span class="ico">📍</span> {{ $brand }}</li>
            <li><span class="ico">🍷</span> {{ $cat }}</li>
            @if(!is_null($alcohol))
                <li><span class="ico">⚖️</span> {{ rtrim(rtrim(number_format($alcohol, 1, '.', ''), '0'), '.') }} %</li>
            @endif
            @if($grape !== '')
                <li><span class="ico">🍇</span> {{ $grape }}</li>
            @endif
            </ul>

            <div class="actions">
            <a class="btn btn-outline-wine" href="{{ URL::to('/product-details/' . $product->product_id) }}">
                <i class="fa fa-eye"></i> Xem chi tiết
            </a>
            </div>
        </div>
        </div>
    @endforeach
    </div>


  <div class="d-flex justify-content-center mt-4">
    {{ $products->onEachSide(1)->links('pagination::bootstrap-5') }}
  </div>
</div>

{{-- Styles --}}
<style>
/* reset xung đột từ theme cũ (giá h2 bị position:absolute, v.v.) */
.wine-card h1, .wine-card h2, .wine-card .price, .wine-card .price h2 { display: none !important; }
.price-wrap .new { position: static !important; }

/* card */
.wine-card{
  margin: 6px;
  position: relative;
  height: 100%;
  border: 2px solid #f0a51a;
  border-radius: 14px;
  padding: 7px;
  display: flex; 
  flex-direction: column;
  transition: box-shadow .2s ease, transform .12s ease;
  background: #fff;
}
.wine-card:hover{ box-shadow: 0 6px 18px rgba(0,0,0,.08); transform: translateY(-2px); }

/* ribbon */
.discount-ribbon{
  position: absolute; top: 10px; right: 10px;
  background: #fdecec; color: #c1272d;
  border: 1px solid #f5c3c6; border-radius: 999px;
  padding: 4px 10px; font-weight: 700; font-size: 12px; z-index: 2;
}

/* ảnh */
.wine-card .thumb{ display:block; width:100%; height:220px; border-radius:8px; overflow:hidden; background:#fff; }
.wine-card img{ width:100%; height:100%; object-fit:contain; }

/* giá */
.price-wrap{
  margin: 10px 0 8px;
  display: flex; align-items: baseline; gap: 8px; justify-content: center;
  min-height: 28px; text-align:center;
}
.price-wrap .old{ color:#888; text-decoration: line-through; font-size:14px; }
.price-wrap .new{ color:#8c1620; font-weight:800; font-size:22px; line-height:1.1; }

/* tên */
.wine-card .name{
  margin: 6px 0 8px; font-size: 16px; font-weight: 600; color:#333; text-align:center;
  min-height: 44px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
}

/* thuộc tính */
.attrs{ list-style:none; padding:0; margin:0 0 10px 0; font-size:14px; color:#555; }
.attrs li{ display:flex; gap:8px; align-items:center; margin:4px 0; }
.attrs .ico{ width:18px; text-align:center; }

/* nút */
.actions{ margin-top:auto; display:flex; justify-content:center; }
.btn-outline-wine{
  display:inline-flex; align-items:center; gap:6px; padding:8px 14px;
  border:1px solid #c9c9c9; border-radius:10px; color:#333; background:#f3f3f1;
  text-decoration:none; font-weight:600;
}
.btn-outline-wine:hover{ border-color:#8c1620; color:#8c1620; background:#fff; }

</style>
@endsection
