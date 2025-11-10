@extends('admin_layout')
@section('admin_content')

<div class="row">
  <div class="col-lg-12">
    <section class="panel">
      <header class="panel-heading">
        THÊM BANNER MỚI
      </header>
      <div class="panel-body">

        {{-- Breadcrumb --}}
        @include('partials.breadcrumb', [
          'items' => [
            ['label' => 'Bảng điều khiển', 'url' => URL::to('/dashboard'), 'icon' => 'fa fa-home'],
            ['label' => 'Banner', 'url' => URL::to('/all-banners'), 'icon' => 'fa fa-picture-o'],
            ['label' => 'Thêm banner', 'active' => true, 'icon' => 'fa fa-plus']
          ]
        ])

        {{-- Flash + Errors --}}
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
 

        {{-- Form thêm banner --}}
        <div class="position-center">
          <form id="bannerForm" role="form" action="{{ URL::to('save-banner') }}" method="post" enctype="multipart/form-data">
            @csrf

            {{-- Tiêu đề --}}
            <div class="form-group">
              <label for="bannerTitle">Tiêu đề (tuỳ chọn):</label>
              <input type="text" id="bannerTitle" name="title" class="form-control"
                     placeholder="Ví dụ: Siêu sale cuối tuần"
                     value="{{ old('title') }}"
                     maxlength="255">
              @error('title') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Link khi click --}}
            <div class="form-group">
              <label for="bannerLink">Liên kết (URL, tuỳ chọn):</label>
              <input type="url" id="bannerLink" name="link_url" class="form-control"
                     placeholder="https://example.com/khuyen-mai"
                     value="{{ old('link_url') }}">
              @error('link_url') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Ảnh banner --}}
            <div class="form-group">
              <label for="bannerImage">Ảnh banner:</label>
              <input type="file" id="bannerImage" name="image" class="form-control" accept="image/*">
              @error('image') <small class="text-danger">{{ $message }}</small> @enderror

              <div class="mt-2" id="previewWrap" style="display:none">
                <img id="previewImg" src="#" alt="Preview"
                     style="max-width:420px;border:1px solid #eee;border-radius:6px;padding:4px">
              </div>
            </div>

            {{-- Vị trí --}}
            <div class="form-group">
              <label for="bannerPosition">Vị trí hiển thị <span class="text-danger">*</span>:</label>
              @php
                $positions = [
                  'home_top'     => 'Trang chủ - Slider trên',
                  'home_mid'     => 'Trang chủ - Giữa',
                  'sidebar_right'=> 'Sidebar phải'
                ];
              @endphp
              <select id="bannerPosition" name="position" class="form-control" required>
                <option value="">-- Chọn vị trí --</option>
                @foreach($positions as $val => $label)
                  <option value="{{ $val }}" {{ old('position')===$val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
              @error('position') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Thứ tự --}}
            <div class="form-group">
              <label for="sortOrder">Thứ tự (tăng dần):</label>
              <input type="number" id="sortOrder" name="sort_order" class="form-control"
                     value="{{ old('sort_order', 0) }}" min="0" step="1">
              @error('sort_order') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Thời gian hiệu lực --}}
            <div class="form-group">
              <label>Thời gian hiệu lực (tuỳ chọn):</label>
              <div class="row">
                <div class="col-sm-6">
                  <input type="datetime-local" id="startsAt" name="starts_at" class="form-control"
                         value="{{ old('starts_at') ? \Carbon\Carbon::parse(old('starts_at'))->format('Y-m-d\TH:i') : '' }}"
                         placeholder="Bắt đầu">
                </div>
                <div class="col-sm-6">
                  <input type="datetime-local" id="endsAt" name="ends_at" class="form-control"
                         value="{{ old('ends_at') ? \Carbon\Carbon::parse(old('ends_at'))->format('Y-m-d\TH:i') : '' }}"
                         placeholder="Kết thúc">
                </div>
              </div>
              @error('starts_at') <small class="text-danger">{{ $message }}</small> @enderror
              @error('ends_at')   <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Trạng thái --}}
            <div class="form-group">
              <label for="bannerStatus">Hiển thị:</label>
              <select id="bannerStatus" name="status" class="form-control" required>
                <option value="0" {{ old('status','1')=='0' ? 'selected' : '' }}>Ẩn</option>
                <option value="1" {{ old('status','1')=='1' ? 'selected' : '' }}>Hiển thị</option>
              </select>
              @error('status') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Nút --}}
            <div class="form-group">
              <button type="submit" class="btn btn-primary">Thêm banner</button>
              <a href="{{ URL::to('/all-banners')}}" class="btn btn-default">Danh sách</a>
              <button type="reset" class="btn btn-warning"
                      onclick="return confirm('Khôi phục dữ liệu đã nhập?')">Khôi phục</button>
            </div>

          </form>
        </div>
      </div>
    </section>
  </div>
</div>

{{-- Scripts nhỏ: preview ảnh, auto-hide flash, ràng buộc thời gian, chống double-submit --}}
<script>
(function(){
  // 1) Preview ảnh
  const inp  = document.getElementById('bannerImage');
  const wrap = document.getElementById('previewWrap');
  const img  = document.getElementById('previewImg');
  if (inp && wrap && img) {
    inp.addEventListener('change', function(){
      const file = this.files && this.files[0];
      if (!file) { wrap.style.display='none'; return; }
      const reader = new FileReader();
      reader.onload = e => { img.src = e.target.result; wrap.style.display = 'block'; };
      reader.readAsDataURL(file);
    });
  }

  // 2) Tự ẩn flash sau 3s
  const flash = document.getElementById('flash-message');
  if (flash && flash.textContent.trim().length) {
    setTimeout(() => {
      flash.style.transition = "opacity .5s";
      flash.style.opacity = 0;
      setTimeout(() => flash.remove(), 500);
    }, 3000);
  }

  // 3) Ràng buộc ends_at >= starts_at (ở client)
  const startEl = document.getElementById('startsAt');
  const endEl   = document.getElementById('endsAt');
  function syncMin() {
    if (startEl && endEl && startEl.value) endEl.min = startEl.value;
  }
  if (startEl && endEl) {
    startEl.addEventListener('change', syncMin);
    window.addEventListener('load', syncMin);
  }

  // 4) Chống double-submit
  const form = document.getElementById('bannerForm');
  if (form) {
    form.addEventListener('submit', function(e){
      const btn = form.querySelector('button[type="submit"]');
      if (btn) { btn.disabled = true; btn.innerText = 'Đang lưu...'; }
    });
  }
})();
</script>
@endsection
