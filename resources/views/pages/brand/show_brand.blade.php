@extends('welcome')

@section('content')

<div class="features_items">
    <h2 class="title text-center">SẢN PHẨM TRONG THƯƠNG HIỆU {{ $brand_name }}</h2>

    @foreach($products as $product)
       

            <div class="col-sm-4">
                <div class="product-image-wrapper">
                    <div class="single-products">
                        <div class="productinfo text-center">
                            <img src="{{ asset('uploads/products/' . $product->product_image) }}" alt="{{ $product->product_name }}" height="250">
                            <h2>{{ number_format($product->product_price, 0, ',', '.') }} VNĐ</h2>
                            <p style="height: 50px;">{{ $product->product_name }}</p>
                            
                            <a href="{{ URL::to('/product-details/' . $product->product_id) }}" class="btn btn-default add-to-cart">
                                <i class="fa-solid fa-eye"></i> Xem chi tiết
                            </a>
                        
                        </div>
                    </div>
                </div>
            </div>
        
    @endforeach
</div>

@endsection
