@extends('welcome')

@section('content')

@php
    // map key => label để đưa lên tiêu đề
    $labelByKey = collect($priceRanges ?? [])->mapWithKeys(fn($r) => [$r['key'] => $r['label']]);
    $selectedLabels = collect($selectedKeys ?? [])->map(fn($k) => $labelByKey[$k] ?? $k)->values();

    // text tiêu đề khoảng giá
    $rangeTitle = $selectedLabels->isNotEmpty()
        ? $selectedLabels->join(', ')
        : 'TẤT CẢ KHOẢNG GIÁ';

    // lấy tên danh mục/brand nếu có (tuỳ bạn có truyền hay không)
    $currentCategoryName = null;
    if (request('category_id') && isset($categories)) {
        $currentCategoryName = optional($categories->firstWhere('category_id', request('category_id')))->category_name;
    }
    $currentBrandName = null;
    if (request('brand_id') && isset($brands)) {
        $currentBrandName = optional($brands->firstWhere('brand_id', request('brand_id')))->brand_name;
    }

    // url gốc để khôi phục (loại bỏ ranges/category_id/brand_id/page)
    $resetUrl = url()->current().'?'.http_build_query(
        collect(request()->except(['ranges','category_id','brand_id','page']))->toArray()
    );
@endphp

<div class="features_items">
    <h2 class="title text-center">
        CÁC SẢN PHẨM 
        @if($currentCategoryName) TRONG DANH MỤC: “{{ $currentCategoryName }}” @endif
        @if($currentBrandName) — THƯƠNG HIỆU: “{{ $currentBrandName }}” @endif
         KHOẢNG: {{ $rangeTitle }}
    </h2>

    {{-- Chips bộ lọc đang áp dụng --}}
    <!-- <div class="text-center" style="margin-bottom:12px;">
        @if($currentCategoryName)
            <span class="label label-info" style="display:inline-block;margin:3px;padding:6px 8px;">
                Danh mục: {{ $currentCategoryName }}
            </span>
        @endif
        @if($currentBrandName)
            <span class="label label-info" style="display:inline-block;margin:3px;padding:6px 8px;">
                Thương hiệu: {{ $currentBrandName }}
            </span>
        @endif
        @forelse($selectedLabels as $lb)
            <span class="label label-success" style="display:inline-block;margin:3px;padding:6px 8px;">
                {{ $lb }}
            </span>
        @empty
            <span class="label label-default" style="display:inline-block;margin:3px;padding:6px 8px;">
                Không giới hạn khoảng giá
            </span>
        @endforelse

    </div> -->

    @if(method_exists($products, 'count') ? $products->count() : count($products))
        <div class="row">
            @foreach($products as $product)
                <div class="col-sm-4 mb-4">
                    <div class="product-image-wrapper">
                        <div class="single-products">
                            <div class="productinfo text-center">
                                <img src="{{ asset('uploads/products/' . $product->product_image) }}"
                                     alt="{{ $product->product_name }}"
                                     style="height: 250px; width:100%; object-fit: cover;">
                                <h2>{{ number_format($product->product_price, 0, ',', '.') }} VNĐ</h2>
                                <p style="height: 50px; overflow: hidden;">{{ $product->product_name }}</p>
                                <a href="{{ url('/product-details/' . $product->product_id) }}"
                                   class="btn btn-default add-to-cart">
                                    <i class="fa fa-eye"></i> Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Phân trang --}}
        <div class="text-center">
            @if(method_exists($products, 'links'))
                {{ $products->links() }}
            @endif
        </div>
    @else
        <p class="text-center">Không có sản phẩm nào phù hợp với bộ lọc hiện tại.</p>
    @endif
</div>

@endsection
