@extends('welcome')

@section('content')
<div class="container" style="max-width: 700px; margin-top: 20px;">
    <h2 class="mb-4">Thông tin cá nhân</h2>

    {{-- Thông báo thành công --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Tổng hợp lỗi (nếu muốn giữ) --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form cập nhật profile --}}
    <form method="POST" action="{{ route('user.profile.update') }}" novalidate>
        @csrf

        {{-- Họ tên --}}
        <div class="form-group mb-3">
            <label>Họ tên:</label>
            <input type="text" name="name" class="form-control"
                   value="{{ old('name', $user->name) }}"
                   placeholder="Nguyễn Văn A"
                   data-validation="length"
                   data-validation-length="3-50"
                   data-validation-error-msg="Họ và tên phải từ 3 đến 50 ký tự">
            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        {{-- Email --}}
        <div class="form-group mb-3">
            <label>Email:</label>
            <input type="email" name="email" class="form-control"
                   value="{{ old('email', $user->email) }}"
                   placeholder="name@example.com" autocomplete="email"
                   data-validation="email"
                   data-validation-error-msg="Vui lòng nhập email hợp lệ">
            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        {{-- Số điện thoại (bỏ pattern/title để không bật popup HTML5) --}}
        <div class="form-group mb-3">
            <label>Số điện thoại:</label>
            <input type="tel" name="phone" class="form-control"
                   value="{{ old('phone', $user->phone) }}"
                   placeholder="VD: 0912345678 hoặc +84912345678"
                   data-validation="custom"
                   data-validation-regexp="^(0|\+84)\d{9,10}$"
                   data-validation-optional="true"
                   data-validation-error-msg="SĐT bắt đầu bằng 0 hoặc +84 và dài 10-11 số">
            @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        {{-- Địa chỉ --}}
        <div class="form-group mb-3">
            <label>Địa chỉ:</label>
            <input type="text" name="address" class="form-control"
                   value="{{ old('address', $user->address) }}"
                   placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành"
                   data-validation="length"
                   data-validation-length="max255"
                   data-validation-error-msg="Địa chỉ không vượt quá 255 ký tự">
            @error('address') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Cập nhật</button>
            <a href="{{ url()->current() }}" class="btn btn-default" style="margin-top: 13px;">Khôi phục</a>
            <a href="{{ url('/home') }}" class="btn btn-secondary" style="margin-top: 13px;">Quay lại Trang chủ</a>
        </div>
    </form>
</div>
@endsection
