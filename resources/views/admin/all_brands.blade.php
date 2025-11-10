@extends('admin_layout')
@section('admin_content')

<div class="table-agile-info">
  <div class="panel panel-default">
    <div class="panel-heading">
      LIỆT KÊ THƯƠNG HIỆU SẢN PHẨM
    </div>

    @include('partials.breadcrumb', [
      'items' => [
          ['label' => 'Bảng điều khiển', 'url' => URL::to('/dashboard'), 'icon' => 'fa fa-home'],
          ['label' => 'Thương hiệu', 'url' => URL::to('/all-brands'), 'icon' => 'fa fa-tags'],
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

  <div class="row w3-res-tb">
    <div class="col-sm-5">
      <form method="GET" action="{{ url('/all-brands') }}" class="form-inline">
        <div class="form-group mr-2">
          <input type="text" name="keyword" class="form-control"
                placeholder="Tìm theo tên / mô tả..."
                value="{{ request('keyword') }}">
        </div>

        <div class="form-group mr-2">
          <select name="status" class="form-control">
            <option value="">-- Trạng thái --</option>
            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Hiển thị</option>
            <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Ẩn</option>
          </select>
        </div>

        <button type="submit" class="btn btn-primary">Tìm kiếm</button>
        <a href="{{ url('/all-brands') }}" class="btn btn-default ml-2">Xóa lọc</a>
      </form>
    </div>
  </div>


    <div class="mb-3 text-right">
      <a href="{{ URL::to('/create-brand') }}" class="btn btn-success">
          <i class="fa fa-plus"></i> Thêm thương hiệu
      </a>
  </div>

    <div class="table-responsive">
    <table class="table table-striped b-t b-light">
        <thead>
        <tr>
            <th style="width: 8%; text-align: center;">Mã</th>
            <th style="width: 10%; text-align: center;">Tên thương hiệu</th>
            <th style="width: 10%; text-align: center;">Hiển thị</th>
            <th style="width: 30%; text-align: center;">Mô tả</th>
            <th style="width: 15%; text-align: center;">Ngày thêm</th>
            <th style="width: 15%; text-align: center;">Ngày cập nhật</th>
            <th style="width: 10%; text-align: center;">Hành động</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($all_brands as $brand)
          <tr>
            <td class="text-center">{{ $brand->brand_id }}</td>
            <td class="text-center">{{ $brand->brand_name }}</td>
            <td class="text-center">
                @if ($brand->brand_status == 1)
                    <a href="{{ URL::to('/unactive-brand/'.$brand->brand_id) }}">
                        <span class="fa fa-thumbs-up" style="font-size: 24px; color: blue;"></span>
                    </a>
                @else
                    <a href="{{ URL::to('/active-brand/'.$brand->brand_id) }}">
                        <span class="fa fa-thumbs-down" style="font-size: 24px; color: red;"></span>
                    </a>
                @endif
            </td>

            <td class="text-center">{{ $brand->brand_description }}</td>
            <td class="text-center">{{ $brand->created_at->format('d/m/Y H:i') }}</td>
            <td class="text-center">{{ $brand->updated_at->format('d/m/Y H:i') }}</td>
            
            <td class="text-center">
                <a href="{{ URL::to('/edit-brand/'.$brand->brand_id) }}" class="btn btn-sm btn-primary" title="Sửa">
                    <i class="fa fa-pencil-square-o text-success text-active"></i>
                </a>

                <a onclick="return confirm('Bạn có chắc muốn xóa thương hiệu này không?')" 
                  href="{{ URL::to('/delete-brand/'.$brand->brand_id) }}" 
                  class="btn btn-sm btn-danger" title="Xóa">
                    <i class="fa fa-trash"></i>
                </a>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>


    <footer class="panel-footer">
      <div class="row">
        
        <div class="col-sm-5 text-center">
          <small class="text-muted inline m-t-sm m-b-sm">showing 20-30 of 50 items</small>
        </div>
        <div class="col-sm-7 text-right text-center-xs">                
          <ul class="pagination pagination-sm m-t-none m-b-none">
            <li><a href=""><i class="fa fa-chevron-left"></i></a></li>
            <li><a href="">1</a></li>
            <li><a href="">2</a></li>
            <li><a href="">3</a></li>
            <li><a href="">4</a></li>
            <li><a href=""><i class="fa fa-chevron-right"></i></a></li>
          </ul>
        </div>
      </div>
    </footer>
  </div>
</div>
@endsection