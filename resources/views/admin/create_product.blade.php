@extends('admin_layout')
@section('admin_content')

<div class="row">
    <div class="col-lg-12">
        <section class="panel">
            <header class="panel-heading">
                THÊM SẢN PHẨM
            </header>

            @include('partials.breadcrumb', [
                'items' => [
                    ['label' => 'Bảng điều khiển', 'url' => URL::to('/dashboard'), 'icon' => 'fa fa-home'],
                    ['label' => 'Sản phẩm', 'url' => URL::to('/all-products'), 'icon' => 'fa fa-list'],
                    ['label' => 'Thêm Sản phẩm', 'active' => true, 'icon' => 'fa fa-plus']
                ]
            ])

            <div class="panel-body">

                @section('breadcrumb')
                    <x-breadcrumbs :items="[
                        ['label' => 'Bảng điều khiển', 'url' => URL::to('/dashboard')],
                        ['label' => 'Sản phẩm', 'url' => URL::to('/all-products')],
                        ['label' => 'Thêm sản phẩm']
                    ]" />
                @endsection

                {{-- Hiển thị thông báo thành công --}}
                @if (session('message'))
                    <div class="alert alert-success">
                        {{ session('message') }}
                    </div>
                @endif

                <div class="position-center">
                    <form id="create-product-form" role="form" action="{{ URL::to('save-product') }}" method="post" enctype="multipart/form-data">
                        {{ csrf_field() }}

                        {{-- Tên sản phẩm --}}
                        <div class="form-group">
                            <label>Tên sản phẩm:</label>
                            <input type="text" data-validation="length"
                                data-validation-length="5-1000" data-validation-error-msg="Tên sản phẩm phải từ 5 đến 1000 ký tự"
                                name="product_name" class="form-control" placeholder="Tên sản phẩm" value="{{ old('product_name') }}">
                            @error('product_name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Hình ảnh (ảnh chính) --}}
                        <div class="form-group">
                            <label>Hình ảnh (ảnh chính):</label>
                            <input type="file" name="product_image"
                                accept="image/*"
                                data-validation="mime size"
                                data-validation-allowing="jpg, png, jpeg, gif, webp"
                                data-validation-max-size="2M"
                                data-validation-error-msg="Vui lòng chọn ảnh (jpg, jpeg, png, gif, webp) dung lượng < 2MB"
                                class="form-control"
                                id="main-image-input">

                            @error('product_image')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror

                            <div class="mt-2">
                                <img id="main-image-preview" src="#" alt="" style="max-width:180px;display:none;border:1px solid #eee;border-radius:6px;padding:2px">
                            </div>
                        </div>


                        {{-- Ảnh phụ (nhiều ảnh) --}}
                        <div class="form-group">
                            <label>Ảnh phụ (có thể chọn nhiều):</label>
                            <input type="file" name="sub_images[]" multiple
                                accept="image/*"
                                data-validation="mime size"
                                data-validation-allowing="jpg, png, jpeg, gif, webp"
                                data-validation-max-size="2M"
                                data-validation-error-msg="Vui lòng chọn ảnh (jpg, jpeg, png, gif, webp) dung lượng < 2MB mỗi ảnh"
                                class="form-control"
                                id="sub-images-input">

                            @error('sub_images')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                            @error('sub_images.*')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror

                            {{-- Khu vực xem trước thumbnail --}}
                            <div id="sub-images-preview" class="mt-2" style="display:flex;flex-wrap:wrap;gap:10px"></div>
                            <small class="text-muted d-block mt-1">
                                Mẹo: Giữ Ctrl (hoặc Cmd) để chọn nhiều ảnh; có thể chọn lại để thay đổi.
                            </small>
                        </div>

                        {{-- Mô tả --}}
                        <div class="form-group">
                            <label>Mô tả:</label>
                            <textarea name="product_description" class="form-control" rows="8" placeholder="Mô tả sản phẩm">{{ old('product_description') }}</textarea>
                            @error('product_description')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        
                        {{-- Nồng độ cồn --}}
                        <div class="form-group">
                            <label>Nồng độ cồn (%):</label>
                            <input type="number"
                                name="alcohol_percent"
                                class="form-control"
                                placeholder="vd: 14.5"
                                step="0.1" min="0" max="100"
                                data-validation="number"
                                data-validation-allowing="float,range[0;100]"
                                data-validation-error-msg="Nồng độ cồn phải là số từ 0 đến 100"
                                value="{{ old('alcohol_percent') }}">
                            @error('alcohol_percent')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Giống nho --}}
                        <div class="form-group">
                            <label>Giống nho:</label>
                            <input type="text"
                                name="grape_variety"
                                class="form-control"
                                placeholder="vd: Sangiovese, Cabernet Sauvignon"
                                data-validation="length"
                                data-validation-length="max255"
                                data-validation-error-msg="Giống nho tối đa 255 ký tự"
                                value="{{ old('grape_variety') }}">
                            <small class="text-muted">Có thể nhập nhiều giống, ngăn cách bằng dấu phẩy.</small>
                            @error('grape_variety')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>


                        {{-- Giá nhập --}}
                        <div class="form-group">
                            <label>Giá nhập:</label>
                            <input type="number" name="cost_price" 
                                data-validation="number" 
                                data-validation-allowing="range[1000;1000000000]"
                                data-validation-error-msg="Giá nhập phải là số dương và lớn hơn 0"
                                class="form-control" placeholder="Giá nhập" value="{{ old('cost_price') }}">
                            @error('cost_price')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Giá bán --}}
                        <div class="form-group">
                            <label>Giá bán:</label>
                            <input type="number" name="product_price" 
                                data-validation="number" 
                                data-validation-allowing="range[1000;1000000000]"
                                data-validation-error-msg="Giá bán phải là số dương và lớn hơn 0"
                                class="form-control" placeholder="Giá bán" value="{{ old('product_price') }}">
                            @error('product_price')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                         {{-- Dung tích --}}
                        <div class="form-group">
                            <label>Dung tích (ml):</label>
                            <input type="number" name="product_capacity"
                                data-validation-allowing="float" 
                                data-validation-error-msg="Dung tích phải là số và từ 10 đến 1000"
                                data-validation-min="100"
                                data-validation-max="1000"
                                class="form-control" placeholder="Dung tích sản phẩm" value="{{ old('product_capacity') }}">
                            @error('product_capacity')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Số lượng --}}
                        <div class="form-group">
                            <label>Số lượng:</label>
                            <input type="number" min="1" name="product_stock"
                                data-validation="number"
                                data-validation-allowing="range[1;1000000]"
                                data-validation-error-msg="Số lượng là số và nằm trong khoảng 1 đến 1.000.000"
                                class="form-control" placeholder="Số lượng" value="{{ old('product_stock') }}">
                            @error('product_stock')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Danh mục --}}
                        <div class="form-group">
                            <label>Danh mục:</label>
                            <select name="category_id" class="form-control" data-validation="required" data-validation-error-msg="Vui lòng chọn danh mục">
                                <option value="">-- Chọn danh mục --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->category_id }}" {{ old('category_id') == $category->category_id ? 'selected' : '' }}>
                                        {{ $category->category_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Thương hiệu --}}
                        <div class="form-group">
                            <label>Thương hiệu:</label>
                            <select name="brand_id" class="form-control" data-validation="required" data-validation-error-msg="Vui lòng chọn thương hiệu">
                                <option value="">-- Chọn thương hiệu --</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->brand_id }}" {{ old('brand_id') == $brand->brand_id ? 'selected' : '' }}>
                                        {{ $brand->brand_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('brand_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Giá khuyến mãi --}}
                        <div class="form-group">
                            <label>Giá khuyến mãi:</label>
                            <input type="number"
                                name="promo_price"
                                class="form-control"
                                placeholder="vd: 900000"
                                min="0"
                                value="{{ old('promo_price') }}">
                            @error('promo_price')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        {{-- Ngày bắt đầu khuyến mãi --}}
                        <div class="form-group">
                            <label>Ngày bắt đầu khuyến mãi:</label>
                            <input type="datetime-local"
                                name="promo_start"
                                class="form-control"
                                value="{{ old('promo_start') }}">
                            @error('promo_start')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Ngày kết thúc khuyến mãi --}}
                        <div class="form-group">
                            <label>Ngày kết thúc khuyến mãi:</label>
                            <input type="datetime-local"
                                name="promo_end"
                                class="form-control"
                                value="{{ old('promo_end') }}">
                            @error('promo_end')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        {{-- Trạng thái --}}
                        <div class="form-group">
                            <label>Hiển thị:</label>
                            <select name="product_status" class="form-control">
                                <option value="0" {{ old('product_status') == '0' ? 'selected' : '' }}>Ẩn</option>
                                <option value="1" {{ old('product_status') == '1' ? 'selected' : '' }}>Hiện</option>
                            </select>
                            @error('product_status')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Nút submit --}}
                        {{-- Nút submit (bypass preventDefault) --}}
                       <div class="form-group">
                            <button type="submit" class="btn btn-primary">Thêm sản phẩm</button>

                            <button type="reset" class="btn btn-warning"
                                    onclick="return confirm('Bạn có chắc muốn khôi phục dữ liệu ban đầu?')">
                                Khôi phục
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>

@push('scripts')
<script>
(function () {
  // Preview ảnh chính
  const mainInput = document.getElementById('main-image-input');
  const mainPreview = document.getElementById('main-image-preview');
  if (mainInput && mainPreview) {
    mainInput.addEventListener('change', function () {
      const f = this.files && this.files[0];
      if (!f) { mainPreview.style.display='none'; return; }
      const reader = new FileReader();
      reader.onload = e => {
        mainPreview.src = e.target.result;
        mainPreview.style.display = 'inline-block';
      };
      reader.readAsDataURL(f);
    });
  }

  // Preview ảnh phụ (nhiều ảnh)
  const subInput = document.getElementById('sub-images-input');
  const subPreview = document.getElementById('sub-images-preview');
  if (subInput && subPreview) {
    subInput.addEventListener('change', function () {
      subPreview.innerHTML = '';
      const files = Array.from(this.files || []);
      if (!files.length) return;
      files.forEach((file, idx) => {
        const reader = new FileReader();
        reader.onload = e => {
          const wrap = document.createElement('div');
          wrap.style.cssText = 'width:100px;display:flex;flex-direction:column;align-items:center;gap:4px';
          const img = document.createElement('img');
          img.src = e.target.result;
          img.alt = 'thumb_'+idx;
          img.style.cssText = 'width:100px;height:100px;object-fit:cover;border:1px solid #eee;border-radius:6px;padding:2px';
          const cap = document.createElement('small');
          cap.textContent = file.name.length > 14 ? file.name.slice(0,12) + '…' : file.name;
          cap.style.textAlign = 'center';
          wrap.appendChild(img);
          wrap.appendChild(cap);
          subPreview.appendChild(wrap);
        };
        reader.readAsDataURL(file);
      });
    });
  }
})();
</script>
@endpush

@endsection
