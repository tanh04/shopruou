@extends('admin_layout')
@section('admin_content')

<div class="table-agile-info">
  <div class="panel panel-default">
    <div class="panel-heading">
      LIỆT KÊ DANH MỤC SẢN PHẨM
    </div>

    @include('partials.breadcrumb', [
      'items' => [
          ['label' => 'Bảng điều khiển', 'url' => URL::to('/dashboard'), 'icon' => 'fa fa-home'],
          ['label' => 'Sản phẩm', 'url' => URL::to('/all-products'), 'icon' => 'fa fa-list'],
          ['label' => 'Danh sách', 'active' => true]
      ]
  ])

    <!-- Hiển thị thông báo -->
    <div style="min-height: 40px; margin-bottom: 20px;">
        @if (session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
    </div>

    <!-- Thông báo tồn kho theo kiểu Dashboard -->
    @php
        $outOfStock = $all_products->where('product_stock', 0)->count();
        $lowStock = $all_products->where('product_stock', '>', 0)->where('product_stock', '<', 5)->count();
        $inStock = $all_products->where('product_stock', '>=', 5)->count();
        
        // Lấy danh sách sản phẩm hết hàng và sắp hết hàng
        $outOfStockProducts = $all_products->where('product_stock', 0);
        $lowStockProducts = $all_products->where('product_stock', '>', 0)->where('product_stock', '<', 5);
    @endphp
    
    @if($outOfStock > 0)
    <!-- Cảnh báo: Sản phẩm hết hàng -->
    <div class="alert alert-danger alert-dismissible" style="margin-bottom: 15px; border-left: 4px solid #d32f2f;">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <h4><i class="fa fa-exclamation-triangle"></i> Cảnh báo: Sản phẩm hết hàng</h4>
        <p>Có <strong>{{ $outOfStock }}</strong> sản phẩm đã hết hàng:</p>
        <ul style="margin-bottom: 0;">
            @foreach($outOfStockProducts->take(5) as $product)
            <li><strong>{{ $product->product_name }}</strong> - {{ number_format($product->product_price, 0, ',', '.') }} đ</li>
            @endforeach
            @if($outOfStock > 5)
            <li><em>... và {{ $outOfStock - 5 }} sản phẩm khác</em></li>
            @endif
        </ul>
    </div>
    @endif

    @if($lowStock > 0)
    <!-- Cảnh báo: Sản phẩm sắp hết hàng -->
    <div class="alert alert-warning alert-dismissible" style="margin-bottom: 15px; border-left: 4px solid #ff9800;">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <h4><i class="fa fa-exclamation-circle"></i> Cảnh báo: Sản phẩm sắp hết hàng</h4>
        <p>Có <strong>{{ $lowStock }}</strong> sản phẩm sắp hết hàng (≤ 5 sản phẩm):</p>
        <ul style="margin-bottom: 0;">
            @foreach($lowStockProducts->take(5) as $product)
            <li><strong>{{ $product->product_name }}</strong> - Còn <span style="color: #ff5722; font-weight: bold;">{{ $product->product_stock }}</span> sản phẩm - {{ number_format($product->product_price, 0, ',', '.') }} đ</li>
            @endforeach
            @if($lowStock > 5)
            <li><em>... và {{ $lowStock - 5 }} sản phẩm khác</em></li>
            @endif
        </ul>
    </div>
    @endif

  <form class="row w3-res-tb g-2" method="GET" action="{{ url()->current() }}">
    <div class="col-sm-5">
      <div class="input-group">
        <input type="text" name="s" class="input-sm form-control"
              placeholder="Tìm theo tên, dung tích, danh mục, thương hiệu..."
              value="{{ request('s') }}">
        <span class="input-group-btn">
          <button class="btn btn-sm btn-primary" type="submit"><i class="fa fa-search"></i> Tìm</button>
        </span>
      </div>
    </div>

    <div class="col-sm-3">
      <select name="category_id" class="input-sm form-control">
        <option value="">-- Danh mục --</option>
        @foreach($allCategories as $c)
          <option value="{{ $c->category_id }}" {{ (string)request('category_id')===(string)$c->category_id ? 'selected' : '' }}>
            {{ $c->category_name }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="col-sm-3">
      <select name="brand_id" class="input-sm form-control">
        <option value="">-- Thương hiệu --</option>
        @foreach($allBrands as $b)
          <option value="{{ $b->brand_id }}" {{ (string)request('brand_id')===(string)$b->brand_id ? 'selected' : '' }}>
            {{ $b->brand_name }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="col-sm-1 text-right">
      <a href="{{ url()->current() }}" class="btn btn-sm btn-default">Xóa lọc</a>
    </div>
  </form>


    <div class="mb-3 text-right">
      <a href="{{ URL::to('/create-product') }}" class="btn btn-success">
          <i class="fa fa-plus"></i> Thêm sản phẩm
      </a>
  </div>

    <div class="table-responsive">
        <table class="table table-striped b-t b-light">
            <thead>
                <tr>
                  <th style="width: 15%;">Tên sản phẩm</th>
                  <th style="width: 10%; text-align: center;">Hình ảnh</th>
                  <th style="width: 10%; text-align: center;">Giá nhập</th>
                  <th style="width: 10%; text-align: center;">Giá bán</th>

                  {{-- Thêm 3 trường mới --}}
                  <th style="width: 10%; text-align: center;">Giá khuyến mãi</th>
                  <th style="width: 10%; text-align: center;">Bắt đầu KM</th>
                  <th style="width: 10%; text-align: center;">Kết thúc KM</th>

                  <th style="width: 8%; text-align: center;">Số lượng</th>
                  <th style="width: 7%; text-align: center;">Hiển thị</th>
                  <th style="width: 14%; text-align: center;">Hành động</th>

                </tr>
            </thead>
            <tbody>
                @foreach ($all_products as $product)
                <tr>
                    <td>{{ $product->product_name }}</td>
                    <td class="text-center">
                        @if ($product->product_image)
                            <img src="{{ asset('uploads/products/'.$product->product_image) }}" style="max-width:100px; height:auto;" alt="{{ $product->product_name }}">
                        @else
                            Không có
                        @endif
                    </td>
                    <td class="text-center">{{ $product->cost_price }}</td>
                    <td class="text-center">{{ $product->product_price }}</td>
                    <td style="text-align:center;">
                      {{ $product->promo_price ? number_format($product->promo_price, 0, ',', '.') . ' đ' : '—' }}
                    </td>
                    <td style="text-align:center;">
                      {{ $product->promo_start ? $product->promo_start->format('d/m/Y H:i') : '—' }}
                    </td>
                    <td style="text-align:center;">
                      {{ $product->promo_end ? $product->promo_end->format('d/m/Y H:i') : '—' }}
                    </td>
                    <td class="text-center">{{ $product->product_stock }}</td>
                    <td class="text-center">
                        @if ($product->product_status == 1)
                            <a href="{{ URL::to('/unactive-product/'.$product->product_id) }}">
                                <span class="fa fa-thumbs-up" style="font-size: 24px; color: blue;"></span>
                            </a>
                        @else
                            <a href="{{ URL::to('/active-product/'.$product->product_id) }}">
                                <span class="fa fa-thumbs-down" style="font-size: 24px; color: red;"></span>
                            </a>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ URL::to('/edit-product/'.$product->product_id) }}" class="btn btn-sm btn-primary" title="Sửa">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                        <a onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này không?')" 
                        href="{{ URL::to('/delete-product/'.$product->product_id) }}" 
                        class="btn btn-sm btn-danger" title="Xóa">
                            <i class="fa fa-trash"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Phân trang --}}
    <footer class="panel-footer">
        @include('partials.pagination', ['paginator' => $all_products, 'infoLabel' => 'sản phẩm'])
    </footer>

    </div>

      <div class="d-flex justify-content-between align-items-center mb-2" style="gap:8px; flex-wrap:wrap;">
      <a href="{{ route('products.export','xlsx') }}" class="btn btn-success btn-sm">Export .xlsx</a>

      <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data" class="d-inline-flex" style="gap:8px;">
        @csrf
        <input type="file" name="file" accept=".xlsx,.csv" required class="form-control input-sm" style="max-width:260px;">
        <button type="submit" class="btn btn-warning btn-sm"><i class="fa fa-upload"></i> Import Excel/CSV</button>
        <!-- <a href="{{ route('products.template') }}" class="btn btn-link btn-sm">Tải file mẫu</a> -->
      </form>

    </div>
</div>

@endsection