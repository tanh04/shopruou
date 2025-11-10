@extends('admin_layout')
@section('admin_content')

<div class="table-agile-info">
  <div class="panel panel-default">
    <div class="panel-heading">
      DANH SÁCH BANNER
    </div>

        {{-- Breadcrumb --}}
    @include('partials.breadcrumb', [
      'items' => [
        ['label' => 'Trang chủ', 'url' => URL::to('/dashboard'), 'icon' => 'fa fa-home'],
        ['label' => 'Banner',    'url' => URL::to('/all-banners'), 'icon' => 'fa fa-picture-o'],
        ['label' => 'Danh sách', 'active' => true]
      ]
    ])

  {{-- Thông báo --}}
  <div id="flash-message" style="min-height: 40px; margin-bottom: 20px;">
    @if (session('message'))
      <div class="alert alert-success">{{ session('message') }}</div>
    @endif
    @if (session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
  </div>

  {{-- Form tìm kiếm --}}

  <div class="row w3-res-tb mb-3">
    <div class="col-sm-6 col-md-5">
      <form method="GET" action="{{ url('/all-banners') }}" class="form-inline">
        <div class="form-group mr-2">
          <input type="text" name="keyword" class="form-control"
                placeholder="Tìm theo tiêu đề..." value="{{ request('keyword') }}">
        </div>

        <div class="form-group mr-2">
          <select name="position" class="form-control">
            <option value="">-- Vị trí --</option>
            @foreach($positions as $key => $label)
              <option value="{{ $key }}" {{ request('position')==$key ? 'selected':'' }}>
                {{ $label }}
              </option>
            @endforeach
          </select>
        </div>

        <button type="submit" class="btn btn-primary">
          <i class="fa fa-search"></i> Tìm kiếm
        </button>
        <a href="{{ url('/all-banners') }}" class="btn btn-default ml-2">Xóa lọc</a>
      </form>
    </div>

    <div class="col-sm-6 col-md-7 text-right">
      <a href="{{ URL::to('/create-banner') }}" class="btn btn-success">
        <i class="fa fa-plus"></i> Thêm banner
      </a>
    </div>
  </div>


    
    {{-- Bảng dữ liệu --}}
    <div class="table-responsive">
      <table class="table table-striped b-t b-light">
        <thead>
          <tr>
            <th class="text-center" style="width: 5%;">Mã</th>
            <th class="text-center" style="width: 15%;">Hình ảnh</th>
            <th class="text-center" style="width: 15%;">Tiêu đề</th>
            <th class="text-center" style="width: 10%;">Vị trí</th>
            <th class="text-center" style="width: 10%;">Thứ tự</th>
            <th class="text-center" style="width: 10%;">Hiển thị</th>
            <th class="text-center" style="width: 20%;">Hiệu lực</th>
            <th class="text-center" style="width: 15%;">Hành động</th>
          </tr>
        </thead>
        <tbody>
          @forelse($banners as $banner)
            <tr>
              <td class="text-center">{{ $banner->id }}</td>

              {{-- Hình ảnh --}}
              <td class="text-center">
                @if ($banner->image_path)
                  <img src="{{ asset('uploads/banners/' . $banner->image_path) }}" alt="Banner" style="height: 50px;">
                @else
                  <span class="text-muted">Không có</span>
                @endif
              </td>

              {{-- Tiêu đề --}}
              <td class="text-center">{{ $banner->title ?? '(Không có tiêu đề)' }}</td>

              {{-- Vị trí --}}
              <td class="text-center">{{ $banner->position ?? '--' }}</td>

              {{-- Thứ tự --}}
              <td class="text-center">{{ $banner->sort_order ?? 0 }}</td>

              {{-- Trạng thái --}}
              <td class="text-center">
                @if ($banner->is_active)
                  <span class="badge badge-success">Đang hiện</span>
                @else
                  <span class="badge badge-secondary">
                    {{ $banner->status ? 'Hết hạn' : 'Đang ẩn' }}
                  </span>
                @endif
              </td>

              {{-- Hiệu lực --}}
              <td class="text-center">
                @if ($banner->starts_at || $banner->ends_at)
                  {{ optional($banner->starts_at)->format('d/m/Y H:i') ?? '—' }}
                  <br>→
                  {{ optional($banner->ends_at)->format('d/m/Y H:i') ?? '—' }}
                @else
                  <span class="text-muted">Không giới hạn</span>
                @endif
              </td>

              {{-- Hành động --}}
              <td class="text-center">
                <a href="{{ url('edit-banners/' . $banner->id) }}" class="btn btn-sm btn-info" title="Sửa">
                  <i class="fa fa-pencil-square-o"></i>
                </a>
                <form action="{{ url('/delete-banner/' . $banner->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Xóa banner này?')">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-danger" title="Xóa"><i class="fa fa-trash"></i></button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center text-muted">Chưa có banner</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Phân trang --}}
    <footer class="panel-footer">
        @include('partials.pagination', ['paginator' => $banners, 'infoLabel' => 'banner'])
    </footer>

  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const flash = document.getElementById('flash-message');
    if (flash && flash.textContent.trim().length) {
      setTimeout(() => {
        flash.style.transition = "opacity .5s";
        flash.style.opacity = 0;
        setTimeout(() => flash.remove(), 500);
      }, 3000);
    }
  });
</script>

@endsection

