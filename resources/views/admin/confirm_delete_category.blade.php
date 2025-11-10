@extends('admin_layout')

@section('admin_content')
    <h2>Xác nhận xóa danh mục</h2>

    <p>Danh mục <strong>{{ $category->category_name }}</strong> đang có <strong>{{ $productCount }}</strong> sản phẩm.</p>
    <p>Bạn có chắc muốn xóa danh mục này và tất cả sản phẩm liên quan?</p>

    <form id="delete-category-form" 
        action="{{ route('force_delete_category', $category->category_id) }}" 
        method="POST">
        @csrf
        @method('DELETE')

        <button type="button" class="btn btn-danger"
            onclick="HTMLFormElement.prototype.submit.call(document.getElementById('delete-category-form'));">
            Xóa tất cả
        </button>

        <a href="{{ url('all-categories') }}" class="btn btn-secondary">Hủy</a>
    </form>

@endsection
