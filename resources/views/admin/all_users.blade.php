@extends('admin_layout')
@section('admin_content')

<div class="table-agile-info">
  <div class="panel panel-default">
    <div class="panel-heading d-flex justify-content-between align-items-center">
      <span>LIỆT KÊ NGƯỜI DÙNG</span>
    </div>

    @include('partials.breadcrumb', [
      'items' => [
          ['label' => 'Bảng điều khiển', 'url' => URL::to('/dashboard'), 'icon' => 'fa fa-home'],
          ['label' => 'Người dùng', 'url' => URL::to('/all-users'), 'icon' => 'fa fa-universal-access'],
          ['label' => 'Danh sách', 'active' => true]
      ]
  ])

    {{-- Flash messages --}}
    <div style="min-height: 40px; margin-bottom: 20px;">
      @if (session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
      @endif
      @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif
    </div>

    {{-- Thanh công cụ + tìm kiếm --}}
    <div class="row w3-res-tb">
      <div class="col-sm-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="form-inline">
          <div class="form-group mr-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                  placeholder="Tìm tên hoặc email...">
          </div>

          <div class="form-group mr-2">
            <select name="role" class="form-control">
              <option value="">-- Vai trò --</option>
              @foreach(App\Models\User::getRoleOptions() as $value => $label)
                <option value="{{ $value }}" {{ request('role') == (string)$value ? 'selected' : '' }}>
                  {{ $label }}
                </option>
              @endforeach
            </select>
          </div>

          <button type="submit" class="btn btn-primary">Tìm kiếm</button>
          <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Xóa lọc</a>
        </form>

      </div>
    </div>


    <div class="mb-3 text-right">
      <a href="{{ URL::to('/create-user') }}" class="btn btn-success">
          <i class="fa fa-plus"></i> Thêm tài khoản
      </a>
  </div>

    {{-- Bảng --}}
    <div class="table-responsive">
      <table class="table table-striped b-t b-light">
        <thead>
          <tr>
            <th style="width: 6%;  text-align:center;">Mã</th>
            <th style="width: 16%; text-align:center;">Tên</th>
            <th style="width: 18%; text-align:center;">Email</th>
            <th style="width: 10%; text-align:center;">Vai trò</th>
            <th style="width: 12%; text-align:center;">SĐT</th>
            <th style="width: 18%; text-align:center;">Địa chỉ</th>
            <th style="width: 12%; text-align:center;">Ngày tạo</th>
            <th style="width: 8%;  text-align:center;">Hành động</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($users as $user)
            @php
              $isSelf = auth()->check() && auth()->id() === $user->id;
            @endphp
            <tr>
              <td class="text-center">{{ $user->id }}</td>
              <td class="text-center">{{ $user->name }}</td>
              <td class="text-center">{{ $user->email }}</td>
              <td class="text-center">
                @if($user->role == 0)
                  <span class="label label-primary">Admin</span>
                @elseif($user->role == 1)
                  <span class="label label-default">Customer</span>
                @else
                  <span class="label label-warning">Không xác định</span>
                @endif
              </td>
              <td class="text-center">{{ $user->phone ?? 'N/A' }}</td>
              <td class="text-center">{{ $user->address ?? 'N/A' }}</td>
              <td class="text-center">{{ optional($user->created_at)->format('d/m/Y H:i') }}</td>
              <td class="text-center">
                <a href="{{ route('admin.users.edit', $user->id) }}"
                   class="btn btn-sm btn-primary" title="Sửa">
                  <i class="fa fa-pencil-square-o"></i>
                </a>

                @if($isSelf)
                  <button class="btn btn-sm btn-danger" title="Không thể xoá chính bạn" disabled>
                    <i class="fa fa-ban"></i>
                  </button>
                @else
                  <a onclick="return confirm('Xoá người dùng này?')"
                     href="{{ route('admin.users.delete', $user->id) }}"
                     class="btn btn-sm btn-danger" title="Xoá">
                    <i class="fa fa-trash"></i>
                  </a>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-muted">Không có người dùng nào.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Phân trang --}}
    <footer class="panel-footer">
        @include('partials.pagination', ['paginator' => $users, 'infoLabel' => 'user'])
    </footer>
  </div>
</div>
@endsection
