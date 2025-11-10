@extends('welcome')

@section('content')

@if($product)
<div class="product-details"><!--product-details-->
    @php
    // Gom 1 mảng thumbnail: ảnh chính + các ảnh phụ
    $thumbs = collect([
        (object)['image_path' => $product->product_image, 'is_main' => true],
    ])->merge(
        $product->images->map(fn($i) => (object)['image_path' => $i->image_path, 'is_main' => false])
    );
    @endphp

    <div class="col-sm-5">
        <!-- Ảnh chính -->
        <div class="view-product">
            <img id="mainImage"
                src="{{ asset('uploads/products/'.$product->product_image) }}"
                alt="{{ $product->product_name }}"
                style="max-width:100%; max-height:100%; object-fit:contain;">
            <h3>ZOOM</h3>
        </div>

        <!-- Carousel thumbnails -->
        @if($thumbs->count())
        <div id="similar-product" class="carousel slide mt-3 position-relative" data-ride="carousel" data-interval="3000">
            <div class="carousel-inner">
                @foreach($thumbs->chunk(3) as $index => $chunk)
                    <div class="item {{ $index === 0 ? 'active' : '' }}">
                        <div class="row">
                            @foreach($chunk as $t)
                                <div class="col-xs-4 text-center">
                                    <a href="javascript:void(0)" 
                                    class="thumb {{ $t->is_main ? 'is-main' : '' }}"
                                    data-full="{{ asset('uploads/products/'.$t->image_path) }}">
                                        <img src="{{ asset('uploads/products/'.$t->image_path) }}"
                                            alt="thumb"
                                            style="width:100px; height:100px; object-fit:cover; border:2px solid #eee; border-radius:6px; padding:2px; margin-bottom:5px;">
                                        @if($t->is_main)
                                            <div style="font-size:12px; color:#888;">Ảnh chính</div>
                                        @endif
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Điều hướng mũi tên -->
            <a class="left item-control" href="#similar-product" data-slide="prev">
                <i class="fa fa-angle-left"></i>
            </a>
            <a class="right item-control" href="#similar-product" data-slide="next">
                <i class="fa fa-angle-right"></i>
            </a>
        </div>
        @endif

    </div>


        <script>
        (function(){
        var main = document.getElementById('mainImage');
        if (!main) return;
        var original = main.getAttribute('src'); // lưu src ảnh chính ban đầu

        // set active viền cho thumbnail tương ứng
        function setActiveBySrc(src) {
            document.querySelectorAll('#thumbnail-carousel .thumb').forEach(function(btn){
            var full = btn.getAttribute('data-full');
            btn.classList.toggle('active', full === src);
            });
        }
        setActiveBySrc(original);

        // click thumbnail -> đổi ảnh chính + active viền
        document.querySelectorAll('#thumbnail-carousel .thumb').forEach(function(btn){
            btn.addEventListener('click', function(e){
            e.preventDefault(); e.stopPropagation();
            var full = this.getAttribute('data-full');
            if (!full) return;
            main.setAttribute('src', full);
            setActiveBySrc(full);
            });
        });
        })();
        </script>


        <div class="col-sm-7">
          <div class="product-information"><!--/product-information-->
              @php
                  // ==== Pricing ====
                  $now        = now();
                  $promoPrice = $product->promo_price ?? null;
                  $start      = $product->promo_start ? \Carbon\Carbon::parse($product->promo_start) : null;
                  $end        = $product->promo_end   ? \Carbon\Carbon::parse($product->promo_end)   : null;

                  $isPromo = $promoPrice
                      && $promoPrice > 0
                      && $product->product_price > 0
                      && $promoPrice < $product->product_price
                      && (!$start || $start->lte($now))
                      && (!$end   || $end->gte($now));

                  $finalPrice = $isPromo ? $promoPrice : $product->product_price;
                  $discount   = $isPromo
                      ? max(0, min(100, (int) round(100 * (1 - ($promoPrice / $product->product_price)))))
                      : null;

                  // ==== Rating ====
                  // Nếu controller đã eager-load reviews đã duyệt thì $product->reviews đã là approved; nếu chưa, lọc status=1 ở đây
                  $all      = $product->reviews ?? collect();
                  $approved = $all->filter(fn($r) => (int)($r->status ?? 1) === 1);
                  $count    = $approved->count();
                  $avg      = $count ? round($approved->avg('rating'), 1) : 0.0;
                  $full     = (int) floor($avg);
                  $half     = ($avg - $full) >= 0.5 && $full < 5;
              @endphp

              <h2>{{ $product->product_name }}</h2>
              <p>Mã: {{ $product->product_id }}</p>

              {{-- Rating trung bình --}}
              <div style="display:flex;align-items:center;gap:8px;margin:6px 0 10px;">
                  <div style="color:#F5A623;font-size:18px;">
                      @for($i=1; $i<=5; $i++)
                          @if($i <= $full)
                              <i class="fa fa-star"></i>
                          @elseif($half && $i === $full + 1)
                              <i class="fa fa-star-half-o"></i>
                          @else
                              <i class="fa fa-star-o"></i>
                          @endif
                      @endfor
                  </div>
                  <div class="text-muted small">
                      @if($count > 0)
                          {{ number_format($avg,1) }}/5 từ {{ $count }} đánh giá
                      @else
                          Chưa có đánh giá
                      @endif
                  </div>
              </div>

              <form action="{{ URL::to('/save-cart') }}" method="POST">
                  {{ csrf_field() }}
                  <span>
                      {{-- Giá bán / khuyến mãi --}}
                      <span style="display:block;margin-bottom:6px;">
                          @if($isPromo)
                              <span style="text-decoration:line-through;color:#777;margin-right:8px;">
                                  {{ number_format($product->product_price, 0, ',', '.') }} VNĐ
                              </span>
                              @if(!is_null($discount))
                                  <span style="background:#fdecec;color:#c1272d;border-radius:16px;padding:2px 8px;font-weight:600;font-size:12px;display:inline-block;">
                                      -{{ $discount }}%
                                  </span>
                              @endif
                              <div style="color:#8c1620;font-weight:700;font-size:20px;margin-top:4px;">
                                  {{ number_format($finalPrice, 0, ',', '.') }} VNĐ
                              </div>
                          @else
                              <span style="color:#8c1620;font-weight:700;font-size:20px;">
                                  {{ number_format($finalPrice, 0, ',', '.') }} VNĐ
                              </span>
                          @endif
                      </span>

                      <label>Số lượng:</label>
                      <input id="quantity" name="quantity" type="number" min="1" value="1" data-stock="{{ $product->product_stock }}" />

                      <input name="productid_hidden" type="hidden" value="{{ $product->product_id }}" />

                      @if(Auth::check())
                          <button type="submit" class="btn btn-default add-to-cart">
                              <i class="fa fa-shopping-cart"></i> Thêm vào giỏ
                          </button>
                      @else
                          <a href="#" class="btn btn-default add-to-cart"
                            onclick="alert('Vui lòng đăng nhập để thêm sản phẩm vào giỏ'); return false;">
                              <i class="fa fa-shopping-cart"></i> Thêm vào giỏ
                          </a>
                      @endif
                  </span>
              </form>

              {{-- NÚT THÊM VÀO YÊU THÍCH - SẢN PHẨM CHÍNH --}}
              <div class="mt-2">
                  @if(Auth::check())
                      <form action="{{ route('wishlist.add', $product->product_id) }}" method="POST" style="display:inline-block;">
                          @csrf
                          <button type="submit" class="btn btn-warning" style="margin-top: -22px;">
                              <i class="fa fa-heart"></i> Thêm vào yêu thích
                          </button>
                      </form>
                  @else
                      <a href="#" class="btn btn-warning"
                        onclick="alert('Vui lòng đăng nhập để thêm vào yêu thích'); return false;">
                        <i class="fa fa-heart"></i> Thêm vào yêu thích
                      </a>
                  @endif
              </div>

              <p><b>Số lượng:</b> {{ $product->product_stock }}</p>
              <!-- <p><b>Dung tích:</b> {{ $product->product_capacity }} ml</p>  -->
              <p><b>Thương hiệu:</b> {{ $brand->brand_name }}</p>
              <p><b>Danh  mục:</b> {{ $category->category_name }}</p>
              <a href=""><img src="images/product-details/share.png" class="share img-responsive" alt="" /></a>

              <div id="qty-error" style="color: red; display: none; margin-top: 5px;"></div>
          </div><!--/product-information-->

        </div>
    </div><!--/product-details-->


<div class="category-tab shop-details-tab"><!--category-tab-->
    <div class="col-sm-12">
        <ul class="nav nav-tabs">
            {{-- Tab "Chi tiết" hiển thị đầu tiên --}}
            <li class="active">
                <a href="#details" data-toggle="tab">Chi tiết</a>
            </li>
            <li>
                <a href="#reviews" data-toggle="tab">Đánh giá</a>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        {{-- Tab Chi tiết --}}
        <div class="tab-pane active in" id="details"> {{-- nếu vẫn ẩn, tạm bỏ class fade --}}
          <div class="row" style="margin:10px 0 15px;">

            {{-- Dung tích --}}
            <div class="col-xs-12 col-sm-6 col-md-4" style="margin-bottom:10px;">
              <div style="display:flex;align-items:center;">
                <span style="width:32px;height:32px;border:1px solid #ddd;border-radius:8px;display:flex;align-items:center;justify-content:center;margin-right:10px;">
                  <i class="fa fa-arrows-v"></i>
                </span>
                <div>
                  <span style="color:#8f8f8f;">Dung Tích:</span>
                  <strong style="color:#111;">
                    {{ $product->product_capacity ? strtoupper(number_format($product->product_capacity,0,',','.')).' ML' : '—' }}
                  </strong>
                </div>
              </div>
            </div>

            {{-- Giống nho --}}
            @if(!empty($product->grape_variety))
            <div class="col-xs-12 col-sm-6 col-md-4" style="margin-bottom:10px;">
              <div style="display:flex;align-items:center;">
                <span style="width:32px;height:32px;border:1px solid #ddd;border-radius:8px;display:flex;align-items:center;justify-content:center;margin-right:10px;">
                  <i class="fa fa-leaf"></i>
                </span>
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                  <span style="color:#8f8f8f;">Giống Nho:</span>
                  <strong style="color:#111;">{{ $product->grape_variety }}</strong>
                </div>
              </div>
            </div>
            @endif

            {{-- Loại rượu --}}
            <div class="col-xs-12 col-sm-6 col-md-4" style="margin-bottom:10px;">
              <div style="display:flex;align-items:center;">
                <span style="width:32px;height:32px;border:1px solid #ddd;border-radius:8px;display:flex;align-items:center;justify-content:center;margin-right:10px;">
                  <i class="fa fa-glass"></i>
                </span>
                <div>
                  <span style="color:#8f8f8f;">Loại Rượu:</span>
                  <strong style="color:#111;">{{ $product->category->category_name ?? '—' }}</strong>
                </div>
              </div>
            </div>

            {{-- Nồng độ --}}
            <div class="col-xs-12 col-sm-6 col-md-4" style="margin-bottom:10px;">
              <div style="display:flex;align-items:center;">
                <span style="width:32px;height:32px;border:1px solid #ddd;border-radius:8px;display:flex;align-items:center;justify-content:center;margin-right:10px;">
                  <i class="fa fa-percent"></i>
                </span>
                <div>
                  <span style="color:#8f8f8f;">Nồng Độ:</span>
                  <strong style="color:#111;">
                    {{ $product->alcohol_percent !== null ? rtrim(rtrim(number_format($product->alcohol_percent,1,'.',''), '0'), '.') . ' %' : '—' }}
                  </strong>
                </div>
              </div>
            </div>

            {{-- Xuất xứ --}}
            <div class="col-xs-12 col-sm-6 col-md-4" style="margin-bottom:10px;">
              <div style="display:flex;align-items:center;">
                <span style="width:32px;height:32px;border:1px solid #ddd;border-radius:8px;display:flex;align-items:center;justify-content:center;margin-right:10px;">
                  <i class="fa fa-map-marker"></i>
                </span>
                <div>
                  <span style="color:#8f8f8f;">Xuất Xứ:</span>
                  <strong style="color:#111;">{{ $product->brand->brand_name ?? '—' }}</strong>
                </div>
              </div>
            </div>

          </div>

          {{-- Mô tả --}}
          <div style="margin-top:10px;">
            {!! nl2br(e($product->product_description)) !!}
          </div>
        </div>

        {{-- Tab Đánh giá --}}
        <div class="tab-pane fade" id="reviews">
          <div class="col-sm-12">

            {{-- Lấy ds đánh giá + check đã mua --}}
            @php
              // lấy tất cả rồi lọc đã duyệt ngay trên Collection (an toàn nếu chưa eager-load)
              $allReviews  = $product->reviews ?? collect();
              $reviews     = $allReviews->where('status', 1)->sortByDesc('created_at');

              $avgRating   = $reviews->avg('rating');
              $countRating = $reviews->count();

              // Dùng hằng số từ Model Order (enum tiếng Việt trong DB)
              $allowedStatuses = [\App\Models\Order::STATUS_COMPLETED];
              // Nếu muốn cho cả đơn đang giao cũng được review, thêm:
              // $allowedStatuses[] = \App\Models\Order::STATUS_SHIPPING;

              $hasPurchased = auth()->check()
                ? \App\Models\Order::query()
                    ->where('user_id', auth()->id())
                    ->whereIn('status', $allowedStatuses)
                    ->whereHas('items', fn($q) => $q->where('product_id', $product->product_id))
                    ->exists()
                : false;
            @endphp


            {{-- Tổng quan sao --}}
            <div class="mb-2" style="display:flex;align-items:center;gap:10px;">
              <div style="color:#F5A623;font-size:18px;">
                @for($i=1;$i<=5;$i++)
                  <i class="fa {{ $i <= round($avgRating) ? 'fa-star' : 'fa-star-o' }}"></i>
                @endfor
              </div>
              <div class="text-muted small">
                @if($countRating>0)
                  {{ number_format($avgRating,1) }}/5 từ {{ $countRating }} đánh giá
                @else
                  Chưa có đánh giá
                @endif
              </div>
            </div>

            {{-- Danh sách đánh giá --}}
            @forelse($reviews as $review)
              <div class="review-item mb-3 border-bottom pb-2">
                <ul class="list-inline small text-muted" style="margin-bottom:6px;">
                  <li class="list-inline-item">
                    <i class="fa fa-user"></i> {{ $review->user->name ?? 'Ẩn danh' }}
                    {{-- Badge ĐÃ MUA --}}
                    @if($review->verified_purchase ?? false)
                      <span class="badge-verified">
                        <i class="fa fa-check-circle"></i> Đã mua
                      </span>
                    @endif
                  </li>
                  <li class="list-inline-item">
                    <i class="fa fa-clock-o"></i> {{ $review->created_at?->format('H:i') }}
                  </li>
                  <li class="list-inline-item">
                    <i class="fa fa-calendar-o"></i> {{ $review->created_at?->format('d/m/Y') }}
                  </li>
                </ul>

                <div class="mb-1" style="color:#F5A623;">
                  @for($i=1; $i<=5; $i++)
                    <i class="fa {{ $i <= (int)$review->rating ? 'fa-star' : 'fa-star-o' }}"></i>
                  @endfor
                </div>

                <p style="margin-bottom:10px;">{{ $review->comment }}</p>
              </div>
            @empty
              <p class="text-muted">Chưa có đánh giá nào cho sản phẩm này.</p>
            @endforelse

            {{-- Form viết đánh giá (chỉ người đã mua) --}}
            <hr>
            <p><b>Viết đánh giá của bạn</b></p>

            <div id="reviewFlash">
              @if(session('success'))
                <div class="alert alert-success" style="margin-bottom:10px;">
                  {{ session('success') }}
                </div>
              @endif
            </div>

            @auth
              @if($hasPurchased)
                <form id="reviewForm" method="POST" action="{{ route('reviews.store', ['product' => $product->product_id]) }}">
                  @csrf
                  <span>
                    <input type="text" value="{{ auth()->user()->name }}" disabled />
                    <input type="email" value="{{ auth()->user()->email }}" disabled />
                  </span>
                  <textarea name="comment" placeholder="Nội dung đánh giá..." required></textarea>
                  <label><b>Đánh giá: </b></label>
                  <select name="rating" required>
                    <option value="">-- Chọn sao --</option>
                    @for($i=5; $i>=1; $i--)
                      <option value="{{ $i }}">{{ $i }} sao</option>
                    @endfor
                  </select>
                  <button type="submit" class="btn btn-default pull-right">Gửi</button>
                </form>
              @else
                <div class="alert alert-info" style="margin-top:10px;">
                  Chỉ khách <b>đã mua</b> sản phẩm này mới có thể viết đánh giá.
                </div>
              @endif
            @else
              <p>Vui lòng <a href="{{ route('login') }}">đăng nhập</a> để viết đánh giá.</p>
            @endauth

          </div>
        </div>

        {{-- style nhỏ cho badge --}}
        <style>
          .badge-verified{
            background:#e8f8ef; color:#1f8a50; border:1px solid #b8e6cf;
            border-radius:12px; padding:2px 8px; font-size:12px; font-weight:600; margin-left:6px;
          }
        </style>


        {{-- style nhỏ cho badge --}}
        <style>
          .badge-verified{
            background:#e8f8ef; color:#1f8a50; border:1px solid #b8e6cf;
            border-radius:12px; padding:2px 8px; font-size:12px; font-weight:600; margin-left:6px;
          }
        </style>

    </div>
</div><!--/category-tab-->

@endif

<div class="recommended_items">  <!-- recommended_items -->
  <h2 class="title text-center">Sản phẩm liên quan</h2>

  <div id="recommended-item-carousel" class="carousel slide" data-ride="carousel" data-interval="5000">
    <div class="carousel-inner">

      @foreach($related_products->chunk(3) as $chunkIndex => $chunk)
        <div class="item {{ $chunkIndex === 0 ? 'active' : '' }}">
          @foreach($chunk as $related)
            <div class="col-sm-4">
              <div class="product-image-wrapper">
                <div class="single-products">
                  <div class="productinfo text-center">
                    <a href="{{ URL::to('/product-details/' . $related->product_id) }}">
                      <img src="{{ asset('uploads/products/' . $related->product_image) }}"
                           alt="{{ $related->product_name }}" style="height:250px; object-fit:contain;">
                      <h2>{{ number_format($related->product_price, 0, ',', '.') }} VNĐ</h2>
                      <p>{{ $related->product_name }}</p>
                    </a>

                    @if(Auth::check())
                      {{-- LƯU Ý: phải dùng $related->product_id, không phải $product->product_id --}}
                      <a href="{{ route('add-to-cart', $related->product_id) }}" class="btn btn-default add-to-cart">
                        <i class="fa fa-shopping-cart"></i> Thêm vào giỏ
                      </a>
                    @else
                      <a href="#" class="btn btn-default add-to-cart"
                         onclick="alert('Vui lòng đăng nhập để thêm sản phẩm vào giỏ'); return false;">
                        <i class="fa fa-shopping-cart"></i> Thêm vào giỏ
                      </a>
                    @endif
                    {{-- NÚT THÊM VÀO YÊU THÍCH - MỖI SẢN PHẨM LIÊN QUAN --}}
                    @if(Auth::check())
                      <form action="{{ route('wishlist.add', $related->product_id) }}" method="POST" style="display:inline-block; margin-top:6px;">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm"style="margin-top: -25px;">
                          <i class="fa fa-heart"></i> Yêu thích
                        </button>
                      </form>
                    @else
                      <a href="#" class="btn btn-warning btn-sm" style="margin-top: -25px;"
                        onclick="alert('Vui lòng đăng nhập để thêm vào yêu thích'); return false;">
                        <i class="fa fa-heart"></i> Yêu thích
                      </a>
                    @endif

                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @endforeach

    </div>

    <a class="left recommended-item-control" href="#recommended-item-carousel" data-slide="prev">
      <i class="fa fa-angle-left"></i>
    </a>
    <a class="right recommended-item-control" href="#recommended-item-carousel" data-slide="next">
      <i class="fa fa-angle-right"></i>
    </a>
  </div>
</div> <!-- /recommended_items -->


<script>
const qtyInput = document.getElementById('quantity');
const errorDiv = document.getElementById('qty-error');
const maxStock = parseInt(qtyInput.dataset.stock);

qtyInput.addEventListener('input', function() {
    let currentVal = parseInt(this.value);

    if (isNaN(currentVal) || currentVal < 1) {
        this.value = 1;
        errorDiv.style.display = 'none';
        return;
    }

    if (currentVal > maxStock) {
        errorDiv.textContent = `Số lượng vượt quá tồn kho (${maxStock}). Vui lòng chọn lại.`;
        errorDiv.style.display = 'block';
        this.value = maxStock;
    } else {
        errorDiv.style.display = 'none';
    }
});
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#reviewForm').on('submit', function(e){
  e.preventDefault();
  const $form = $(this);

  $.ajax({
    url: $form.attr('action'),
    type: 'POST',
    data: $form.serialize(),
    dataType: 'json',
    success: function(res){
    const d = res?.data || res;

    // Hiện thông báo thành công ở trên form
    $('#reviewFlash').html(
        `<div class="alert alert-success" style="margin-bottom:10px;">
        ${d.message || 'Gửi đánh giá thành công, đang chờ duyệt.'}
        </div>`
    );

    // Tuỳ chọn: tự ẩn sau 3 giây
    setTimeout(() => { 
        $('#reviewFlash .alert').fadeOut(300, function(){ $(this).remove(); }); 
    }, 3000);

    // Reset form
    $('#reviewForm')[0].reset();
    },
    error: function(xhr){
      let msg = 'Đã xảy ra lỗi, vui lòng thử lại!';
      if (xhr.status === 422 && xhr.responseJSON?.errors) {
        msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
      } else if (xhr.responseJSON?.message) {
        msg = xhr.responseJSON.message;
      }
      $('#reviewFlash').html(`<div class="alert alert-danger" style="margin-bottom:10px;">${msg}</div>`);
    }
  });
});

</script>

@endsection

