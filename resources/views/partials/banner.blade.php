{{-- resources/views/pages/banner.blade.php --}}

@if(!isset($hideSlider) || !$hideSlider)
@if(!empty($banners) && $banners->count())
<section id="slider"><!--slider-->
  <div class="container">
    <div class="row">
      <div class="col-sm-12">
        <div id="slider-carousel" class="carousel slide" data-ride="carousel">
          
          {{-- Indicators --}}
          <ol class="carousel-indicators">
            @foreach($banners as $i => $ban)
              <li data-target="#slider-carousel" data-slide-to="{{ $i }}" class="{{ $i===0 ? 'active' : '' }}"></li>
            @endforeach
          </ol>

          {{-- Slides --}}
          <div class="carousel-inner">
            @foreach($banners as $ban)
              <div class="item {{ $loop->first ? 'active' : '' }}">
                
                {{-- Col trái: text & nút --}}
                <div class="col-sm-6">
                  @if(!empty($ban->title))
                    <h1>{{ $ban->title }}</h1>
                  @endif
                  @if(!empty($ban->description))
                    <h2>{{ $ban->description }}</h2>
                  @endif
                  @if(!empty($ban->content))
                    <p>{{ $ban->content }}</p>
                  @endif

                  @if(!empty($ban->link_url))
                    <a href="{{ $ban->link_url }}" class="btn btn-default get">Xem chi tiết</a>
                  @endif
                </div>

                {{-- Col phải: ảnh chính + (tuỳ chọn) ảnh overlay như "pricing.png" của mẫu --}}
                <div class="col-sm-6">
                  @if(!empty($ban->image_path))
                    <img
                      src="{{ URL::to('/uploads/banners/'.$ban->image_path) }}"
                      alt="banner"
                      class="girl img-responsive"
                    />
                  @else
                    <img src="{{ asset('frontend/images/no-image.png') }}" alt="no image" class="girl img-responsive" />
                  @endif

                  @if(!empty($ban->overlay_image)) {{-- nếu có trường ảnh overlay --}}
                    <img
                      src="{{ URL::to('/uploads/banners/'.$ban->overlay_image) }}"
                      alt="overlay"
                      class="pricing"
                    />
                  @endif
                </div>
              </div>

            @endforeach
          </div>

          {{-- Controls --}}
          <a href="#slider-carousel" class="left control-carousel hidden-xs" data-slide="prev">
            <i class="fa fa-angle-left"></i>
          </a>
          <a href="#slider-carousel" class="right control-carousel hidden-xs" data-slide="next">
            <i class="fa fa-angle-right"></i>
          </a>
        </div>
      </div>
    </div>
  </div>

	<style>
		/* clear float trong từng slide */
		#slider .carousel-inner .item::after {
		content: "";
		display: block;
		clear: both;
		}

		/* (tuỳ chọn) cố định chiều cao để các slide không "nhảy" */
		#slider .carousel-inner .item { min-height: 360px; }

		/* Ảnh co giãn đúng tỉ lệ */
		#slider .girl.img-responsive { max-width: 100%; height: auto; display: block; margin-left: auto; margin-right: auto; }

	</style>

</section><!--/slider-->
@endif
@endif
