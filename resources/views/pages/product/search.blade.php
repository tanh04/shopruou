@extends('welcome')

@section('content')

<div class="features_items">
    <h2 class="title text-center">KẾT QUẢ TÌM KIẾM</h2>

    @if(count($products) > 0)
        <div class="row">
            @foreach($products as $product)
                <div class="col-sm-4 mb-4">
                    <div class="product-image-wrapper">
                        <div class="single-products">
                            <div class="productinfo text-center">
                                <img src="{{ asset('uploads/products/' . $product->product_image) }}" alt="{{ $product->product_name }}" style="height: 250px; object-fit: cover;">
                                <h2>{{ number_format($product->product_price, 0, ',', '.') }} VNĐ</h2>
                                <p style="height: 50px; overflow: hidden;">{{ $product->product_name }}</p>
                                <a href="{{ URL::to('/product-details/' . $product->product_id) }}" class="btn btn-default add-to-cart">
                                    <i class="fa fa-eye"></i> Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-center">Không tìm thấy sản phẩm nào phù hợp với từ khóa.</p>
    @endif
</div>

@endsection
