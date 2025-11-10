@extends('admin_layout')
@section('admin_content')

<div class="row">
    <div class="col-lg-12">
        <section class="panel">
            <header class="panel-heading">CẬP NHẬT SẢN PHẨM</header>

            <div class="panel-body">
                @section('breadcrumb')
                    <x-breadcrumbs :items="[
                        ['label' => 'Bảng điều khiển', 'url' => URL::to('/dashboard')],
                        ['label' => 'Sản phẩm', 'url' => URL::to('/all-products')],
                        ['label' => 'Cập nhật sản phẩm']
                    ]" />
                @endsection

                {{-- Thông báo --}}
                <div style="min-height:50px;margin-bottom:15px;">
                    @if (session('message'))
                        <div class="alert alert-success">{{ session('message') }}</div>
                    @endif
                </div>

                <div class="position-center">
                    <form id="update-product-form" role="form"
                          action="{{ URL::to('/update-product/'.$product->product_id) }}"
                          method="post" enctype="multipart/form-data">
                        {{ csrf_field() }}

                        {{-- Tên --}}
                        <div class="form-group">
                            <label>Tên sản phẩm:</label>
                            <input type="text" name="product_name" value="{{ $product->product_name }}"
                                   class="form-control" placeholder="Tên sản phẩm"
                                   data-validation="length" data-validation-length="5-1000"
                                   data-validation-error-msg="Tên sản phẩm phải từ 5 đến 1000 ký tự">
                            @error('product_name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Ảnh chính --}}
                        <div class="form-group">
                            <label>Hình ảnh (ảnh chính):</label>
                            <input type="file" name="product_image" accept="image/*"
                                   class="form-control" id="main-image-input"
                                   data-validation="mime size"
                                   data-validation-allowing="jpg, png, jpeg, gif, webp"
                                   data-validation-max-size="2M"
                                   data-validation-error-msg="Vui lòng chọn ảnh (jpg, jpeg, png, gif, webp) < 2MB">
                            @if($product->product_image)
                                <div class="mt-2" style="display:flex;gap:10px;align-items:center">
                                    <img src="{{ URL::to('/uploads/products/'.$product->product_image) }}"
                                         width="80" style="border:1px solid #eee;border-radius:6px;padding:2px">
                                    <img id="main-image-preview" src="#" alt=""
                                         style="max-width:120px;display:none;border:1px solid #eee;border-radius:6px;padding:2px">
                                </div>
                            @endif
                            @error('product_image') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Mô tả --}}
                        <div class="form-group">
                            <label>Mô tả:</label>
                            <textarea name="product_description" class="form-control" rows="8" placeholder="Mô tả sản phẩm" style="resize:none">{{ $product->product_description }}</textarea>
                            @error('product_description') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Nồng độ cồn --}}
                        <div class="form-group">
                            <label>Nồng độ cồn (%):</label>
                            <input type="number"
                                name="alcohol_percent" value="{{ $product->alcohol_percent }}"
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
                                name="grape_variety" value="{{ $product-> grape_variety}}"
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

                        {{-- Giá --}}
                        <div class="form-group">
                            <label>Giá nhập:</label>
                            <input type="text" name="cost_price" value="{{ $product->cost_price }}"
                                   class="form-control" placeholder="Giá sản phẩm"
                                   data-validation="number"
                                   data-validation-allowing="range[1000;1000000000]"
                                   data-validation-error-msg="Giá sản phẩm phải là số dương và lớn hơn 0">
                            @error('cost_price') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Giá --}}
                        <div class="form-group">
                            <label>Giá bán:</label>
                            <input type="text" name="product_price" value="{{ $product->product_price }}"
                                   class="form-control" placeholder="Giá bán"
                                   data-validation="number"
                                   data-validation-allowing="range[1000;1000000000]"
                                   data-validation-error-msg="Giá bán phải là số dương và lớn hơn 0">
                            @error('product_price') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Dung tích --}}
                        <div class="form-group">
                            <label>Dung tích (ml):</label>
                            <input type="text" name="product_capacity" value="{{ $product->product_capacity }}"
                                   class="form-control" placeholder="Dung tích sản phẩm"
                                   data-validation-allowing="float"
                                   data-validation-error-msg="Dung tích phải là số và từ 10 đến 1000"
                                   data-validation-min="100" data-validation-max="1000">
                            @error('product_capacity') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Số lượng --}}
                        <div class="form-group">
                            <label>Số lượng:</label>
                            <input type="number" name="product_stock" value="{{ $product->product_stock }}"
                                   class="form-control" placeholder="Số lượng"
                                   data-validation="number"
                                   data-validation-allowing="range[1;1000000]"
                                   data-validation-error-msg="Số lượng là số và nằm trong khoảng 1 đến 1.000.000">
                            @error('product_stock') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Danh mục --}}
                        <div class="form-group">
                            <label>Danh mục:</label>
                            <select name="category_id" class="form-control" data-validation="required" data-validation-error-msg="Vui lòng chọn danh mục">
                                <option value="">-- Chọn danh mục --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->category_id }}" {{ $product->category_id == $category->category_id ? 'selected' : '' }}>
                                        {{ $category->category_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Thương hiệu --}}
                        <div class="form-group">
                            <label>Thương hiệu:</label>
                            <select name="brand_id" class="form-control" data-validation="required" data-validation-error-msg="Vui lòng chọn thương hiệu">
                                <option value="">-- Chọn thương hiệu --</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->brand_id }}" {{ $product->brand_id == $brand->brand_id ? 'selected' : '' }}>
                                        {{ $brand->brand_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('brand_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Giá khuyến mãi --}}
                        <div class="form-group">
                            <label>Giá khuyến mãi:</label>
                            <input type="number"
                                name="promo_price" value="{{ $product->promo_price }}"
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
                                name="promo_start" value="{{ $product->promo_start }}"
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
                                name="promo_end" value="{{ $product->promo_end }}"
                                class="form-control" 
                                value="{{ old('promo_end') }}">
                            @error('promo_end')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        {{-- Trạng thái --}}
                        <div class="form-group">
                            <label>Hiển thị</label>
                            <select name="product_status" class="form-control">
                                <option value="0" {{ $product->product_status == 0 ? 'selected' : '' }}>Ẩn</option>
                                <option value="1" {{ $product->product_status == 1 ? 'selected' : '' }}>Hiện</option>
                            </select>
                            @error('product_status') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Ảnh phụ: thêm mới --}}
                        <div class="form-group">
                            <label>Ảnh phụ (chọn nhiều):</label>
                            <input type="file" name="sub_images[]" multiple accept="image/*" class="form-control"
                                   id="sub-images-input"
                                   data-validation="mime size"
                                   data-validation-allowing="jpg, png, jpeg, gif, webp"
                                   data-validation-max-size="2M"
                                   data-validation-error-msg="Vui lòng chọn ảnh (jpg, jpeg, png, gif, webp) < 2MB mỗi ảnh">
                            @error('sub_images') <small class="text-danger">{{ $message }}</small> @enderror
                            @error('sub_images.*') <small class="text-danger">{{ $message }}</small> @enderror

                            {{-- Preview ảnh phụ sắp thêm --}}
                            <div id="sub-images-preview" class="mt-2" style="display:flex;flex-wrap:wrap;gap:10px"></div>
                            <small class="text-muted d-block mt-1">Giữ Ctrl/Cmd để chọn nhiều ảnh. Ảnh trùng nội dung sẽ được bỏ qua.</small>
                        </div>

                        {{-- Ảnh phụ hiện có: giữ/xoá/sắp xếp --}}
                        @if($product->images->count())
                            <div class="form-group">
                                <label>Ảnh phụ hiện có:</label>
                                <div class="mt-2" style="display:flex;flex-wrap:wrap;gap:12px">
                                    @foreach($product->images as $img)
                                        <div style="border:1px solid #ddd;border-radius:8px;padding:6px;width:130px;text-align:center">
                                            <img src="{{ URL::to('/uploads/products/'.$img->image_path) }}" width="110"
                                                 style="border:1px solid #eee;border-radius:6px;padding:2px">
                                            <div class="mt-1">
                                                <label style="font-weight:normal">
                                                    <input type="checkbox" name="keep_ids[]" value="{{ $img->id }}" checked>
                                                    Giữ ảnh
                                                </label>
                                            </div>
                                            <div class="mt-1">
                                                <label style="font-weight:normal">Thứ tự</label>
                                                <input type="number" name="orders[{{ $img->id }}]"
                                                       value="{{ $img->sort_order }}" style="width:70px" class="form-control">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Nút --}}
                        <button type="button" class="btn btn-info"
                                onclick="HTMLFormElement.prototype.submit.call(document.getElementById('update-product-form'));">
                            Cập nhật sản phẩm
                        </button>
                        <button type="reset" class="btn btn-warning"
                                onclick="return confirm('Bạn có chắc muốn khôi phục dữ liệu ban đầu?')">
                            Khôi phục
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>

{{-- Script preview ảnh --}}
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
      reader.onload = e => { mainPreview.src = e.target.result; mainPreview.style.display = 'inline-block'; };
      reader.readAsDataURL(f);
    });
  }

  // Preview ảnh phụ mới
  const subInput = document.getElementById('sub-images-input');
  const subPreview = document.getElementById('sub-images-preview');
  if (subInput && subPreview) {
    subInput.addEventListener('change', function () {
      subPreview.innerHTML = '';
      const files = Array.from(this.files || []);
      // loại trùng theo tên trong cùng lần chọn (client-side nhẹ nhàng)
      const unique = [];
      files.forEach(f => {
        if (!unique.some(u => u.name === f.name && u.size === f.size)) unique.push(f);
      });
      unique.forEach((file, idx) => {
        const reader = new FileReader();
        reader.onload = e => {
          const box = document.createElement('div');
          box.style.cssText = 'width:110px;display:flex;flex-direction:column;align-items:center;gap:4px';
          const img = document.createElement('img');
          img.src = e.target.result;
          img.style.cssText = 'width:110px;height:110px;object-fit:cover;border:1px solid #eee;border-radius:6px;padding:2px';
          const cap = document.createElement('small');
          cap.textContent = file.name.length > 14 ? file.name.slice(0,12) + "…" : file.name;
          box.appendChild(img); box.appendChild(cap);
          subPreview.appendChild(box);
        };
        reader.readAsDataURL(file);
      });
    });
  }
})();
</script>

@endsection
