<!DOCTYPE html>
<html>
<head>
    <title>TRANG QUẢN LÝ ADMIN</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="utf-8">
    <!-- bootstrap-css -->
    <link rel="stylesheet" href="{{ asset('backend/css/bootstrap.min.css') }}">
    <!-- Custom CSS -->
    <link href="{{ asset('backend/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/css/style-responsive.css') }}" rel="stylesheet">
    <!-- font CSS -->
    <link href="//fonts.googleapis.com/css?family=Roboto:400,100,300,700,900" rel="stylesheet" type="text/css">
    <!-- font-awesome icons -->
    <link rel="stylesheet" href="{{ asset('backend/css/font.css') }}" type="text/css">
    <link href="{{ asset('backend/css/font-awesome.css') }}" rel="stylesheet">
    <script src="{{ asset('backend/js/jquery2.0.3.min.js') }}"></script>
</head>
<body>
<div class="log-w3">
    <div class="w3layouts-main">
        <h2>ĐĂNG NHẬP</h2>

        <!-- Hiển thị thông báo thành công -->
        @if (session('success'))
            <div class="alert alert-success" style="margin-bottom: 15px;">
                {{ session('success') }}
            </div>
        @endif

        <!-- Hiển thị lỗi login -->
        @if ($errors->any())
            <div class="alert alert-danger" style="margin-bottom: 15px;">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        
        <form action="{{ route('login') }}" method="POST">
            @csrf
            <input type="email" class="ggg" name="email" placeholder="E-MAIL" required="">
            <input type="password" class="ggg" name="password" placeholder="PASSWORD" required="">
            <span>
                <input type="checkbox"> Nhớ đăng nhập
            </span>
            <h6><a href="{{ route('password.request') }}">Quên mật khẩu?</a></h6>
            <div class="clearfix"></div>
            <input type="submit" value="Đăng nhập" name="login">
        </form>
        <!-- Link đăng ký -->
        <p class="mt-3" style="text-align: center; margin-top: 15px;">
            Chưa có tài khoản?
            <a href="http://localhost/shopbanhang/public/register" style="color: #080808ff; font-weight: 500;">Đăng ký ngay</a>
        </p>
        
        <p class="mt-2" style="text-align: center;">
            <a href="{{ url('/home') }}" style="color: #337ab7;">
                ← Quay về Trang chủ
            </a>
        </p>
    </div>
</div>

<script src="{{ asset('backend/js/bootstrap.js') }}"></script>
<script src="{{ asset('backend/js/jquery.dcjqaccordion.2.7.js') }}"></script>
<script src="{{ asset('backend/js/scripts.js') }}"></script>
<script src="{{ asset('backend/js/jquery.slimscroll.js') }}"></script>
<script src="{{ asset('backend/js/jquery.nicescroll.js') }}"></script>
<script src="{{ asset('backend/js/jquery.scrollTo.js') }}"></script>
</body>
</html>
