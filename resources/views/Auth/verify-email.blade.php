@extends('welcome')

@section('content')
<div class="container mt-5">
    <h2>Xác thực Email</h2>
    <p>Vui lòng kiểm tra email để xác thực tài khoản của bạn.</p>

    {{-- Hiển thị thông báo nếu có --}}
    @if (session('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    {{-- Form gửi lại email xác thực --}}
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-primary">Gửi lại email xác thực</button>
    </form>
</div>
@endsection
