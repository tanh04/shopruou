@extends('admin_layout')
@section('admin_content')

<div class="row">
  <div class="col-lg-12">
    <section class="panel">
      <header class="panel-heading">THÊM NGƯỜI DÙNG</header>

      @include('partials.breadcrumb', [
          'items' => [
              ['label' => 'Bảng điều khiển', 'url' => URL::to('/dashboard'), 'icon' => 'fa fa-home'],
              ['label' => 'Người dùng', 'url' => URL::to('/all-users'), 'icon' => 'fa fa-universal-access'],
              ['label' => 'Thêm Người dùng', 'active' => true, 'icon' => 'fa fa-plus']
          ]
      ])

      <div class="panel-body">

        {{-- Flash success --}}
        @if (session('message'))
          <div class="alert alert-success">{{ session('message') }}</div>
        @endif

        <div class="position-center">
          <form id="create-user-form" role="form" action="{{ route('admin.users.store') }}" method="post">
            @csrf

            {{-- Họ và tên --}}
            <div class="form-group">
              <label>Họ và tên:</label>
              <input type="text" name="name" class="form-control"
                    value="{{ old('name') }}" placeholder="Nguyễn Văn A"
                    data-validation="length" data-validation-length="3-50"
                    data-validation-error-msg="Họ và tên phải từ 3 đến 50 ký tự">
              @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Email --}}
            <div class="form-group">
              <label>Email:</label>
              <input type="email" name="email" class="form-control"
                    value="{{ old('email') }}" placeholder="name@example.com"
                    autocomplete="email"
                    data-validation="email"
                    data-validation-error-msg="Vui lòng nhập địa chỉ email hợp lệ">
              @error('email') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Mật khẩu --}}
            <div class="form-group">
              <label>Mật khẩu:</label>
              <input type="password" name="password" class="form-control"
                    autocomplete="new-password"
                    data-validation="length" data-validation-length="min8"
                    data-validation-error-msg="Mật khẩu phải có ít nhất 8 ký tự">
              @error('password') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Xác nhận mật khẩu --}}
            <div class="form-group">
              <label>Xác nhận mật khẩu:</label>
              <input type="password" name="password_confirmation" class="form-control"
                    autocomplete="new-password">
            </div>

            {{-- Địa chỉ --}}
            <div class="form-group">
              <label>Địa chỉ:</label>
              <input type="text" name="address" class="form-control"
                    value="{{ old('address') }}" placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành"
                    data-validation="length" data-validation-length="5-255"
                    data-validation-error-msg="Địa chỉ từ 5 đến 255 ký tự">
              @error('address') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Số điện thoại --}}
            <div class="form-group">
              <label>Số điện thoại:</label>
              <input type="tel" name="phone" class="form-control"
                    value="{{ old('phone') }}" placeholder="VD: 0912345678 hoặc +84912345678"
                    inputmode="tel" pattern="^(0|\+84)\d{9,10}$"
                    title="SĐT bắt đầu bằng 0 hoặc +84 và dài 10-11 số">
              @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Vai trò --}}
            <div class="form-group">
              <label>Vai trò:</label>
              <select name="role" class="form-control" data-validation="required"
                      data-validation-error-msg="Vui lòng chọn vai trò">
                <option value="1" {{ old('role', '1') === '1' ? 'selected' : '' }}>Khách hàng</option>
                <option value="0" {{ old('role') === '0' ? 'selected' : '' }}>Quản trị</option>
              </select>
              @error('role') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Nút hành động --}}
            <div class="form-group">
              <button type="submit" class="btn btn-primary">Thêm tài khoản</button>
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
