@extends('admin_layout')
@section('admin_content')

<div class="row">
    <div class="col-lg-12">
        <section class="panel">
            <header class="panel-heading">
                THÊM DANH MỤC SẢN PHẨM
            </header>

            @include('partials.breadcrumb', [
                'items' => [
                    ['label' => 'Bảng điều khiển', 'url' => URL::to('/dashboard'), 'icon' => 'fa fa-home'],
                    ['label' => 'Danh mục', 'url' => URL::to('/all-categories'), 'icon' => 'fa fa-tasks'],
                    ['label' => 'Thêm Danh mục', 'active' => true, 'icon' => 'fa fa-plus']
                ]
            ])

            <div class="panel-body">

                {{-- Hiển thị thông báo thành công --}}
                @if (session('message'))
                    <div class="alert alert-success">{{ session('message') }}</div>
                @endif

                <div class="position-center">
                    <form id="create-category-form" role="form"
                          action="{{ URL::to('save-category') }}" method="post">
                        @csrf

                        {{-- Tên danh mục --}}
                        <div class="form-group">
                            <label>Tên danh mục:</label>
                            <input type="text" name="category_name" class="form-control"
                                   placeholder="Tên danh mục" value="{{ old('category_name') }}"
                                   data-validation="length"
                                   data-validation-length="5-50"
                                   data-validation-error-msg="Tên danh mục phải từ 5 đến 50 ký tự">
                            @error('category_name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Mô tả --}}
                        <div class="form-group">
                            <label>Mô tả:</label>
                            <textarea name="category_description" class="form-control" rows="8"
                                      placeholder="Mô tả danh mục"
                                      data-validation="length"
                                      data-validation-length="5-1000"
                                      data-validation-error-msg="Mô tả phải từ 5 đến 1000 ký tự">{{ old('category_description') }}</textarea>
                            @error('category_description')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Trạng thái --}}
                        <div class="form-group">
                            <label>Hiển thị:</label>
                            <select name="category_status" class="form-control input-sm m-bot15"
                                    data-validation="required"
                                    data-validation-error-msg="Vui lòng chọn trạng thái">
                                <option value="0" {{ old('category_status', '1') == '0' ? 'selected' : '' }}>Ẩn</option>
                                <option value="1" {{ old('category_status', '1') == '1' ? 'selected' : '' }}>Hiển thị</option>
                            </select>
                            @error('category_status')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Danh mục cha --}}
                        <div class="form-group">
                            <label>Danh mục cha:</label>
                            <select name="parent_id" class="form-control input-sm m-bot15">
                                <option value="">-- Không có (Danh mục gốc) --</option>
                                @foreach($parents as $p)
                                    <option value="{{ $p->category_id }}"
                                        {{ old('parent_id') == $p->category_id ? 'selected' : '' }}>
                                        {{ $p->category_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Nút hành động --}}
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Thêm danh mục</button>
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
