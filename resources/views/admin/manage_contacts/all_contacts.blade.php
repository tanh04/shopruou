@extends('admin_layout')
@section('admin_content')

<div class="table-agile-info">
  <div class="panel panel-default">
    <div class="panel-heading">LIỆT KÊ LIÊN HỆ</div>

    @include('partials.breadcrumb', [
      'items' => [
        ['label' => 'Bảng điều khiển', 'url' => URL::to('/dashboard'), 'icon' => 'fa fa-home'],
        ['label' => 'Liên hệ',         'url' => URL::to('/contacts'), 'icon' => 'fa fa-envelope'],
        ['label' => 'Danh sách',       'active' => true]
      ]
    ])

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

    {{-- Bộ lọc --}}
    <div class="row w3-res-tb">
      <div class="col-sm-9">
        <form class="row g-2" method="GET" action="{{ url()->current() }}">
          <div class="col-sm-5">
            <input type="text" name="s" class="input-sm form-control"
                   placeholder="Tìm tên / email / nội dung" value="{{ request('s') }}">
          </div>
          <div class="col-sm-3">
            <select name="status" class="input-sm form-control">
              <option value="">-- Trạng thái --</option>
              <option value="pending" @selected(request('status')==='pending')>Chờ duyệt</option>
              <option value="done"    @selected(request('status')==='done')>Đã duyệt</option>
              <option value="spam"    @selected(request('status')==='spam')>Spam</option>
            </select>
          </div>
          <div class="col-sm-3">
            <button class="btn btn-sm btn-default" type="submit">Lọc</button>
            <a href="{{ url()->current() }}" class="btn btn-sm btn-default">Khôi phục</a>
          </div>
        </form>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-striped b-t b-light">
        <thead>
          <tr>
            <th class="text-center" style="width:6%;">Mã</th>
            <th class="text-center" style="width:20%;">Khách</th>
            <th class="text-center" style="width:16%;">Chủ đề</th>
            <th class="text-center" style="width:24%;">Trích nội dung</th>
            <th class="text-center" style="width:14%;">Trạng thái</th>
            <th class="text-center" style="width:12%;">Thời gian</th>
            <th class="text-center" style="width:18%;">Hành động</th>
          </tr>
        </thead>
        <tbody>
          @php
            $statusMap = [
              'pending' => ['Chờ duyệt','bg-warning'],
              'done'    => ['Đã duyệt', 'bg-success'],
              'spam'    => ['Spam',     'bg-danger'],
            ];
          @endphp

          @forelse($q as $c)
            @php $meta = $statusMap[$c->status] ?? [$c->status,'bg-secondary']; @endphp
            <tr>
              <td class="text-center">{{ $c->id }}</td>

              <td class="text-center">
                <div>{{ $c->name }}</div>
                <div class="text-muted small">{{ $c->email }} @if($c->phone) • {{ $c->phone }} @endif</div>
              </td>

              <td class="text-center">{{ $c->subject ?: '—' }}</td>

              <td class="text-left">{{ \Illuminate\Support\Str::limit($c->message, 80) }}</td>

              <td class="text-center">
                <span class="badge {{ $meta[1] }}">{{ $meta[0] }}</span>

                {{-- CHỈ hiện option đổi trạng thái khi đang pending --}}
                @if($c->status === 'pending')
                  <form action="{{ route('manage_contacts.update', $c) }}" method="POST" class="d-inline-block" style="margin-top:6px;">
                    @csrf @method('PATCH')
                    <select name="status" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                      <option value="pending" selected>Chờ duyệt</option>
                      <option value="done">Đã duyệt</option>
                      <option value="spam">Spam</option>
                    </select>
                  </form>
                @endif
              </td>

              <td class="text-center">{{ optional($c->created_at)->format('d/m/Y H:i') }}</td>

              <td class="text-center">
                <a class="btn btn-primary btn-sm" href="{{ route('manage_contacts.show_contact', $c) }}">
                  Xem & phản hồi
                </a>
                <form action="{{ route('manage_contacts.destroy', $c) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Xoá liên hệ #{{ $c->id }}?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm">Xoá</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted">Không có dữ liệu</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <footer class="panel-footer">
      @include('partials.pagination', ['paginator' => $q, 'infoLabel' => 'liên hệ'])
    </footer>
  </div>
</div>
@endsection
