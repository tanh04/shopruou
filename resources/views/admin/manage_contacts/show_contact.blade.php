@extends('admin_layout')
@section('admin_content')

<div class="panel panel-default">
  <div class="panel-heading">CHI TIẾT LIÊN HỆ #{{ $contact->id }}</div>
  <div class="panel-body">
    @include('partials.breadcrumb', [
      'items' => [
        ['label' => 'Bảng điều khiển', 'url' => URL::to('/dashboard'), 'icon' => 'fa fa-home'],
        ['label' => 'Liên hệ', 'url' => URL::to('/contacts'), 'icon' => 'fa fa-envelope'],
        ['label' => 'Chi tiết', 'active' => true]
      ]
    ])

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

    <div class="row">
      <div class="col-sm-7">
        <div class="panel panel-default">
          <div class="panel-heading">Thông tin khách hàng</div>
          <div class="panel-body">
            <p><strong>Khách:</strong> {{ $contact->name }}</p>
            <p><strong>Email:</strong> {{ $contact->email }} @if($contact->phone) — <strong>ĐT:</strong> {{ $contact->phone }} @endif</p>
            <p><strong>Chủ đề:</strong> {{ $contact->subject ?: '—' }}</p>
            <p><strong>Nội dung:</strong></p>
            <div class="well" style="white-space:pre-wrap">{{ $contact->message }}</div>
            <p class="text-muted">
              <strong>Gửi lúc:</strong> {{ optional($contact->created_at)->format('d/m/Y H:i') }} —
              <strong>IP:</strong> {{ $contact->ip }} —
              <strong>UA:</strong> {{ $contact->user_agent }}
            </p>
          </div>
        </div>
      </div>

      <div class="col-sm-5">
        @include('admin.manage_contacts.Reply_contact', ['contact' => $contact])

        {{-- Chỉ cho đổi khi đang pending --}}
        @if($contact->status === 'pending')
          <form action="{{ route('manage_contacts.update', $contact) }}" method="POST" class="mt-2">
            @csrf @method('PATCH')
            <label>Trạng thái:</label>
            <select name="status" class="form-control" style="max-width:220px; display:inline-block">
              <option value="pending" selected>Chờ duyệt</option>
              <option value="done">Đã duyệt</option>
              <option value="spam">Spam</option>
            </select>
            <button class="btn btn-default">Lưu</button>
          </form>
        @else
          <p class="mt-2">
            <label>Trạng thái:</label>
            @php
              $statusClass = ['pending'=>'bg-warning','done'=>'bg-success','spam'=>'bg-danger'][$contact->status] ?? 'bg-secondary';
              $statusText  = ['pending'=>'Chờ duyệt','done'=>'Đã duyệt','spam'=>'Spam'][$contact->status] ?? $contact->status;
            @endphp
            <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
          </p>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
