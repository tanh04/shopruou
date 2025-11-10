@extends('admin_layout')
@section('admin_content')

<div class="row">
  <div class="col-lg-12">
    <section class="panel">
      <header class="panel-heading">
        CẬP NHẬT NGƯỜI DÙNG
      </header>
      <div class="panel-body">

        @section('breadcrumb')
            <x-breadcrumbs :items="[
                ['label' => 'Bảng điều khiển', 'url' => URL::to('/dashboard')],
                ['label' => 'Tài khoản', 'url' => URL::to('/all-users')],
                ['label' => 'Cập nhật tài khoản']
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

        {{-- Form cập nhật user --}}
        <div class="position-center">
          <form id="update-user-form" role="form" 
                action="{{ URL::to('/update-user/'.$user->id) }}" 
                method="post">
            @csrf

           {{-- Họ và tên --}}
          <div class="form-group">
            <label>Họ và tên:</label>
            <input type="text" name="name" class="form-control"
                  value="{{ old('name', $user->name) }}"
                  placeholder="Nguyễn Văn A"
                  data-validation="length"
                  data-validation-length="3-50"
                  data-validation-error-msg="Họ và tên phải từ 3 đến 50 ký tự">
            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
          </div>

          {{-- Email --}}
          <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" class="form-control"
                  value="{{ old('email', $user->email) }}"
                  placeholder="name@example.com" autocomplete="email"
                  data-validation="email"
                  data-validation-error-msg="Vui lòng nhập địa chỉ email hợp lệ">
            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
          </div>

          {{-- Mật khẩu mới (để trống nếu không đổi) --}}
          <div class="form-group">
            <label>Mật khẩu mới (để trống nếu không đổi):</label>
            <input type="password" name="password" class="form-control"
                  autocomplete="new-password"
                  data-validation-optional="true"
                  data-validation="length"
                  data-validation-length="min8"
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
                  value="{{ old('address', $user->address) }}"
                  placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành"
                  data-validation="length"
                  data-validation-length="5-255"
                  data-validation-error-msg="Địa chỉ từ 5 đến 255 ký tự">
            @error('address') <small class="text-danger">{{ $message }}</small> @enderror
          </div>

          {{-- Số điện thoại --}}
          <div class="form-group">
            <label>Số điện thoại:</label>
            <input type="tel" name="phone" class="form-control"
                  value="{{ old('phone', $user->phone) }}"
                  placeholder="VD: 0912345678 hoặc +84912345678"
                  inputmode="tel" pattern="^(0|\+84)\d{9,10}$"
                  title="SĐT bắt đầu bằng 0 hoặc +84 và dài 10-11 số">
            @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
          </div>

          {{-- Vai trò --}}
          <div class="form-group">
            <label>Vai trò:</label>
            <select name="role" class="form-control" data-validation="required"
                    data-validation-error-msg="Vui lòng chọn vai trò">
              <option value="1" {{ (string)old('role', $user->role) === '1' ? 'selected' : '' }}>Khách hàng</option>
              <option value="0" {{ (string)old('role', $user->role) === '0' ? 'selected' : '' }}>Quản trị</option>
            </select>
            @error('role') <small class="text-danger">{{ $message }}</small> @enderror
          </div>

          {{-- Nút hành động --}}
          <div class="mt-3">
            <button type="submit" class="btn btn-info">Cập nhật người dùng</button>
            <button type="reset"  class="btn btn-warning"
                    onclick="return confirm('Khôi phục các giá trị đã nhập về mặc định?')">Khôi phục</button>
          </div>
        </form>
      </div>
      </div>
    </section>
  </div>
</div>

@endsection
