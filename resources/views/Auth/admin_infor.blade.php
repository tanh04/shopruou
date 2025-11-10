@extends('admin_layout')
@section('admin_content')

<div class="table-agile-info">
  <div class="panel panel-default">
    <div class="panel-heading">
      HỒ SƠ QUẢN TRỊ
    </div>

    {{-- Thông báo --}}
    <div style="min-height: 40px; margin: 15px;">
      @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      @if ($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach ($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      @endif
    </div>

    <div class="panel-body">
      <div class="row">
        <div class="col-lg-6 col-md-8">
          <form action="{{ route('Auth.admin_infor.update') }}" method="POST">
            @csrf
            @method('PATCH')

            {{-- Họ và tên --}}
            <div class="form-group">
              <label>Họ và tên:</label>
              <input type="text" name="name" class="form-control"
                     value="{{ old('name', $user->name) }}" required
                     placeholder="Nguyễn Văn A">
              @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Email --}}
            <div class="form-group">
              <label>Email:</label>
              <input type="email" name="email" class="form-control"
                     value="{{ old('email', $user->email) }}" required
                     placeholder="name@example.com" autocomplete="email">
              @error('email') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Số điện thoại --}}
            <div class="form-group">
              <label>Số điện thoại:</label>
              <input type="tel" name="phone" class="form-control"
                     value="{{ old('phone', $user->phone) }}"
                     placeholder="VD: 0912345678 hoặc +84912345678"
                     inputmode="tel">
              @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Địa chỉ --}}
            <div class="form-group">
              <label>Địa chỉ:</label>
              <input type="text" name="address" class="form-control"
                     value="{{ old('address', $user->address) }}"
                     placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành (>= 5 ký tự)">
              @error('address') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <hr>

            {{-- Mật khẩu mới --}}
            <div class="form-group">
              <label>Mật khẩu mới (để trống nếu không đổi):</label>
              <input type="password" name="password" class="form-control" autocomplete="new-password">
              @error('password') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Xác nhận mật khẩu --}}
            <div class="form-group">
              <label>Xác nhận mật khẩu:</label>
              <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
            </div>

            <div class="mt-3">
              <button type="submit" class="btn btn-info">Lưu thay đổi</button>
              <a href="{{ url()->current() }}" class="btn btn-default">Khôi phục</a>
              <a href="{{ url('/dashboard') }}" class="btn btn-default">Quay lại</a>
            </div>
          </form>
        </div>

        {{-- Có thể bổ sung cột bên phải để hiển thị thông tin tóm tắt --}}
        <div class="col-lg-6 col-md-4">
          <div class="well">
            <p><strong>Tên đăng nhập:</strong> {{ $user->name }}</p>
            <p><strong>Email hiện tại:</strong> {{ $user->email }}</p>
            <p><strong>Vai trò:</strong> {{ $user->role === \App\Models\User::ROLE_ADMIN ? 'Quản trị' : 'Khách hàng' }}</p>
            <p class="text-muted"><small>Cập nhật email có thể ảnh hưởng tới xác minh email nếu bạn đang bật tính năng xác minh.</small></p>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

@endsection
