@extends('admin_layout')
@section('admin_content')

<div class="container mt-4">
    <h3>💬 Hội thoại #{{ $conversation->id }}</h3>
    <p>
        <b>Khách hàng:</b> {{ $conversation->customer_name ?? 'Khách' }} <br>
        <b>Liên hệ:</b> {{ $conversation->customer_contact ?? '-' }} <br>
        <b>Trạng thái:</b> 
        @if($conversation->status === 'open')
            <span class="badge bg-success">Đang mở</span>
        @else
            <span class="badge bg-secondary">Đã đóng</span>
        @endif
    </p>

    <div class="card mb-3">
        <div class="card-header">Lịch sử tin nhắn</div>
        <div class="card-body" style="height:400px; overflow-y:auto; background:#f9f9f9;">
            @foreach($messages as $m)
                <div class="mb-2">
                    @if($m->direction === 'in')
                        <div>
                            <span class="badge bg-secondary">👤 {{ $m->sender_name ?? 'Khách' }}</span>
                            <span class="text-muted" style="font-size:12px;">{{ $m->created_at->format('H:i d/m/Y') }}</span>
                        </div>
                        <div class="p-2 bg-light border rounded" style="display:inline-block;">
                            {{ $m->body }}
                        </div>
                    @else
                        <div>
                            <span class="badge bg-primary">🤖 {{ $m->sender_name ?? 'Shop' }}</span>
                            <span class="text-muted" style="font-size:12px;">{{ $m->created_at->format('H:i d/m/Y') }}</span>
                        </div>
                        <div class="p-2 bg-white border rounded" style="display:inline-block;">
                            {{ $m->body }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <form method="POST" action="{{ route('admin.livechat.reply', $conversation->id) }}">
        @csrf
        <div class="mb-3">
            <label for="body" class="form-label">Trả lời</label>
            <textarea name="body" id="body" class="form-control" rows="3" placeholder="Nhập tin nhắn..."></textarea>
        </div>
        <button type="submit" class="btn btn-success">Gửi</button>
        <a href="{{ route('admin.livechat.index') }}" class="btn btn-secondary">⬅ Quay lại</a>
    </form>
</div>
@endsection
