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
          ['label' => 'Danh mục', 'url' => URL::to('/all-categories'), 'icon' => 'fa fa-tasks'],
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
      <form method="GET" action="{{ url('/all-categories') }}" class="form-inline">
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
        <a href="{{ url('/all-categories') }}" class="btn btn-default ml-2">Xóa lọc</a>
      </form>
    </div>
  </div>

    <div class="mb-3 text-right">
      <a href="{{ URL::to('/create-category') }}" class="btn btn-success">
          <i class="fa fa-plus"></i> Thêm danh mục
      </a>
  </div>
    <div class="table-responsive">
    <table class="table table-striped b-t b-light">
        <thead>
        <tr>
            <th style="width: 5%; text-align: center;">Mã</th>
            <th style="width: 15%; text-align: center;">Tên danh mục</th>
            <th style="width: 10%; text-align: center;">Hiển thị</th>
            <th style="width: 35%; text-align: center;">Mô tả</th>
            <th style="width: 12%; text-align: center;">Ngày thêm</th>
            <th style="width: 12%; text-align: center;">Ngày cập nhật</th>
            <th style="width: 10%; text-align: center;">Hành động</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($all_categories as $category)
        <tr>
          <td class="text-center">{{ $category->category_id }}</td>
          <td class="text-center">{{ $category->category_name }}</td>
          <td class="text-center">
              @if ($category->category_status == 1)
                  <a href="{{ URL::to('/unactive-category/'.$category->category_id) }}" 
                    onclick="return confirm('Bạn có chắc muốn hủy kích hoạt danh mục này và tất cả sản phẩm liên quan?');">
                      <span class="fa fa-thumbs-up" style="font-size: 24px; color: blue;"></span>
                  </a>
              @else
                  <a href="{{ URL::to('/active-category/'.$category->category_id) }}">
                      <span class="fa fa-thumbs-down" style="font-size: 24px; color: red;"></span>
                  </a>
              @endif
          </td>
          <td class="text-center">{{ $category->category_description }}</td>
          <td class="text-center">{{ $category->created_at }}</td>
          <td class="text-center">{{ $category->updated_at }}</td>
          <td class="text-center">
              <a href="{{ URL::to('/edit-category/'.$category->category_id) }}" class="btn btn-sm btn-primary"  title="Sửa">
                  <i class="fa fa-pencil-square-o text-success text-active"></i>
              </a>

              <a onclick="return confirm('Bạn có chắc muốn xóa danh mục này không?')" 
                href="{{ URL::to('/delete-category/'.$category->category_id) }}" 
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
        @include('partials.pagination', ['paginator' => $all_categories, 'infoLabel' => 'category'])
    </footer>
  </div>
</div>
@endsection