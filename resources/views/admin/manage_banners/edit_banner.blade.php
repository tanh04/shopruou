@extends('admin_layout')
@section('admin_content')

<div class="row">
    <div class="col-lg-12">
        <section class="panel">
            <header class="panel-heading">
                CẬP NHẬT BANNER
            </header>
            <div class="panel-body">

                {{-- Breadcrumbs --}}
                @include('partials.breadcrumb', [
                'items' => [
                    ['label' => 'Bảng điều khiển', 'url' => URL::to('/dashboard'), 'icon' => 'fa fa-home'],
                    ['label' => 'Banner', 'url' => URL::to('/all-banners'), 'icon' => 'fa fa-picture-o'],
                    ['label' => 'Cập nhật banner', 'active' => true, 'icon' => 'fa fa-plus']
                ]
                ])

                {{-- Thông báo --}}
                <div id="flash-message" style="min-height: 40px; margin-bottom: 20px;">
                    @if (session('message'))
                        <div class="alert alert-success">{{ session('message') }}</div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                </div>

                {{-- Form cập nhật --}}
                <div class="position-center">
                    <form action="{{ route('banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                        {{-- Tiêu đề --}}
                        <div class="form-group">
                            <label for="title">Tiêu đề</label>
                            <input type="text" name="title" id="title" class="form-control"
                                   value="{{ old('title', $banner->title) }}">
                            @error('title')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Link --}}
                        <div class="form-group">
                            <label for="link_url">Liên kết</label>
                            <input type="url" name="link_url" id="link_url" class="form-control"
                                   placeholder="https://..." value="{{ old('link_url', $banner->link_url) }}">
                            @error('link_url')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Vị trí --}}
                        <div class="form-group">
                            <label for="position">Vị trí</label>
                            <select name="position" id="position" class="form-control">
                                @foreach ($positions as $pos)
                                    <option value="{{ $pos }}" {{ old('position', $banner->position) === $pos ? 'selected' : '' }}>
                                        {{ $pos }}
                                    </option>
                                @endforeach
                            </select>
                            @error('position')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Thứ tự --}}
                        <div class="form-group">
                            <label for="sort_order">Thứ tự</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control"
                                   value="{{ old('sort_order', $banner->sort_order) }}">
                            @error('sort_order')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Thời gian hiệu lực --}}
                        <div class="form-group">
                            <label>Thời gian hiệu lực (tuỳ chọn)</label>
                            <div class="row">
                                <div class="col-sm-6">
                                    <input type="datetime-local" name="starts_at" class="form-control"
                                           value="{{ old('starts_at', optional($banner->starts_at)->format('Y-m-d\TH:i')) }}">
                                </div>
                                <div class="col-sm-6">
                                    <input type="datetime-local" name="ends_at" class="form-control"
                                           value="{{ old('ends_at', optional($banner->ends_at)->format('Y-m-d\TH:i')) }}">
                                </div>
                            </div>
                            @error('starts_at')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                            @error('ends_at')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Ảnh --}}
                        <div class="form-group">
                            <label>Ảnh banner</label><br>
                            @if ($banner->image_path)
                                <div style="margin-bottom: 10px;">
                                    <img src="{{ URL::to('/uploads/banners/' . $banner->image_path) }}" alt="banner"
                                         style="max-width: 200px; max-height: 100px;">
                                </div>
                            @endif
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">Để nguyên nếu không muốn đổi ảnh</small>
                            @error('image')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Trạng thái --}}
                        <div class="form-group">
                            <label for="status">Trạng thái hiển thị</label>
                            <select name="status" id="status" class="form-control">
                                <option value="1" {{ old('status', $banner->status) == 1 ? 'selected' : '' }}>Hiện</option>
                                <option value="0" {{ old('status', $banner->status) == 0 ? 'selected' : '' }}>Ẩn</option>
                            </select>
                            @error('status')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Nút hành động --}}
                        <div class="form-group">
                            <button type="submit" class="btn btn-info">Cập nhật banner</button>
                            <a href="{{ URL::to('/all-banners') }}" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                </div>

            </div>
        </section>
    </div>
</div>

<script>
    (function(){
      // auto-hide flash after 3s
      const flash = document.getElementById('flash-message');
      if (flash && flash.textContent.trim().length) {
        setTimeout(() => {
          flash.style.transition = "opacity .5s";
          flash.style.opacity = 0;
          setTimeout(() => flash.remove(), 500);
        }, 3000);
      }
    })();
</script>
@endsection
