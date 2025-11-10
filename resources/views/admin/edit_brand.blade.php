@extends('admin_layout')
@section('admin_content')

<div class="row">
    <div class="col-lg-12">
        <section class="panel">
            <header class="panel-heading">
                CẬP NHẬT THƯƠNG HIỆU SẢN PHẨM
            </header>
            <div class="panel-body">

                @section('breadcrumb')
                    <x-breadcrumbs :items="[
                        ['label' => 'Bảng điều khiển', 'url' => URL::to('/dashboard')],
                        ['label' => 'Thương hiệu', 'url' => URL::to('/all-brands')],
                        ['label' => 'Cập nhật thương hiệu']
                    ]" />
                @endsection

                {{-- Hiển thị thông báo --}}
                <div style="min-height: 50px; margin-bottom: 15px;">
                    @if (session('message'))
                        <div class="alert alert-success">
                            {{ session('message') }}
                        </div>
                    @endif
                </div>

                {{-- Form edit thương hiệu --}}
                <div class="position-center">
                    <form id="update-brand-form" role="form"
                        action="{{ URL::to('/update-brand/'.$brand->brand_id) }}" method="post">
                        @csrf
                        {{-- Nếu route dùng PUT/PATCH thì mở dòng dưới: --}}
                        {{-- @method('PUT') --}}

                        {{-- Tên thương hiệu --}}
                        <div class="form-group">
                            <label for="brandName">Tên thương hiệu:</label>
                            <input
                                type="text"
                                id="brandName"
                                name="brand_name"
                                class="form-control"
                                placeholder="Tên thương hiệu"
                                value="{{ old('brand_name', $brand->brand_name) }}"
                                data-validation="length"
                                data-validation-length="5-50"
                                data-validation-error-msg="Tên thương hiệu phải từ 5 đến 50 ký tự"
                            >
                            @error('brand_name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Mô tả --}}
                        <div class="form-group">
                            <label for="brandDesc">Mô tả:</label>
                            <textarea
                                id="brandDesc"
                                name="brand_description"
                                class="form-control"
                                rows="8"
                                placeholder="Mô tả thương hiệu"
                                data-validation="length"
                                data-validation-length="5-1000"
                                data-validation-error-msg="Mô tả phải từ 5 đến 1000 ký tự"
                            >{{ old('brand_description', $brand->brand_description) }}</textarea>
                            @error('brand_description')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Trạng thái (đồng bộ với trang tạo) --}}
                        <div class="form-group">
                            <label for="brandStatus">Hiển thị</label>
                            <select
                                id="brandStatus"
                                name="brand_status"
                                class="form-control input-sm m-bot15"
                                data-validation="required"
                                data-validation-error-msg="Vui lòng chọn trạng thái"
                            >
                                <option value="0" {{ old('brand_status', $brand->brand_status) == 0 ? 'selected' : '' }}>Ẩn</option>
                                <option value="1" {{ old('brand_status', $brand->brand_status) == 1 ? 'selected' : '' }}>Hiển thị</option>
                            </select>
                            @error('brand_status')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Nút hành động --}}
                        <div class="form-group">
                            <button type="submit" class="btn btn-info">Cập nhật thương hiệu</button>
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
@endsection
