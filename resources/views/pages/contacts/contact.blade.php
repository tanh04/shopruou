@extends('welcome')

@section('content')
<div class="container mt-5 mb-5">
    <h2 class="text-center mb-4">Liên hệ với chúng tôi</h2>

    {{-- Flash message --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-md-6 mb-4">
            <h4>Thông tin</h4>
            <p><i class="fa fa-map-marker"></i> 123 Nguyễn Trãi, Hà Nội</p>
            <p><i class="fa fa-phone"></i> 0123 456 789</p>
            <p><i class="fa fa-envelope"></i> support@yourshop.com</p>

            <h4>Bản đồ</h4>
            <iframe src="https://www.google.com/maps/embed?pb=!1m18..."
                    width="100%" height="250" style="border:0;" allowfullscreen></iframe>
        </div>

        <div class="col-md-6">
            <h4>Gửi liên hệ</h4>

            <form action="{{ route('contact.send') }}" method="POST" id="contact-form">
                @csrf

                {{-- Honeypot chống bot (ẩn) --}}
                {{-- <input type="text" name="hp_field" style="display:none"> --}}

                {{-- Họ tên --}}
                <div class="form-group mb-3">
                    <label>Họ và tên:</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" placeholder="Nguyễn Văn A"
                           data-validation="length" data-validation-length="3-50"
                           data-validation-error-msg="Họ và tên phải từ 3 đến 50 ký tự">
                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                {{-- Email --}}
                <div class="form-group mb-3">
                    <label>Email:</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" placeholder="name@example.com" autocomplete="email"
                           data-validation="email"
                           data-validation-error-msg="Vui lòng nhập địa chỉ email hợp lệ">
                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                {{-- Điện thoại (tuỳ chọn) --}}
                <div class="form-group mb-3">
                    <label>Số điện thoại (tuỳ chọn):</label>
                    <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone') }}" placeholder="VD: 0912345678 hoặc +84912345678"
                           inputmode="tel" pattern="^(0|\+84)\d{9,10}$"
                           title="SĐT bắt đầu bằng 0 hoặc +84 và dài 10-11 số">
                    @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                {{-- Chủ đề (tuỳ chọn) --}}
                <div class="form-group mb-3">
                    <label>Chủ đề (tuỳ chọn):</label>
                    <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror"
                           value="{{ old('subject') }}" placeholder="Ví dụ: Hỏi về đơn hàng #1234"
                           data-validation-optional="true" data-validation="length" data-validation-length="max120"
                           data-validation-error-msg="Chủ đề tối đa 120 ký tự">
                    @error('subject') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                {{-- Nội dung --}}
                <div class="form-group mb-3">
                    <label>Nội dung:</label>
                    <textarea name="message" rows="5"
                              class="form-control @error('message') is-invalid @enderror"
                              placeholder="Nội dung liên hệ..."
                              data-validation="length" data-validation-length="5-2000"
                              data-validation-error-msg="Nội dung phải từ 5 đến 2000 ký tự">{{ old('message') }}</textarea>
                    @error('message') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Gửi liên hệ</button>
                    <button href="{{ url()->current() }}" type="reset" class="btn btn-warning"
                        onclick="return confirm('Khôi phục toàn bộ dữ liệu đã nhập?')" style="margin-top: 13px;">Khôi phục</button>
                </div>
            </form>
        </div>
        
    </div>
</div>
@endsection
