<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>@yield('title', 'DASHBOARD')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- CSS -->
  <link rel="stylesheet" href="{{ asset('backend/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('backend/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('backend/css/style-responsive.css') }}">
  <link rel="stylesheet" href="{{ asset('backend/css/font.css') }}">
  <link rel="stylesheet" href="{{ asset('backend/css/font-awesome.css') }}">
  <link rel="stylesheet" href="{{ asset('backend/css/morris.css') }}">
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">

  @stack('styles')
</head>
<body>
<section id="container">
  <!-- header -->
  <header class="header fixed-top clearfix">
    <div class="brand">
      <a href="{{ url('/dashboard') }}" class="logo">VISITORS</a>
      <div class="sidebar-toggle-box"><div class="fa fa-bars"></div></div>
    </div>

    <div class="top-nav clearfix">
      <ul class="nav pull-right top-menu">
        <li><input type="text" class="form-control search" placeholder=" Search"></li>
        <li class="dropdown">
          <a data-toggle="dropdown" class="dropdown-toggle" href="#">
            <img alt="" src="{{ asset('backend/images/2.png') }}">
            <span class="username">{{ auth()->user()->name ?? 'Admin' }}</span>
            <b class="caret"></b>
          </a>
          <ul class="dropdown-menu extended logout">
            <li><a href="{{ url('/profile') }}"><i class="fa fa-user"></i> Hồ sơ Admin</a></li>
            <li><a href="#"><i class="fa fa-cog"></i> Settings</a></li>
            <li>
              <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fa fa-key"></i> Log Out
              </a>
              <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display:none;">
                @csrf
              </form>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </header>

  <!-- sidebar -->
  <aside>
    <div id="sidebar" class="nav-collapse">
      <div class="leftside-navigation">
        <ul class="sidebar-menu" id="nav-accordion">
          <li>
            <a class="{{ request()->is('dashboard') ? 'active' : '' }}" href="{{ url('/dashboard') }}">
              <i class="fa fa-bar-chart-o"></i><span>Tổng quan</span>
            </a>
          </li>

          <li><a href="{{ url('/all-banners') }}"><i class="fa fa-tasks"></i><span>Quản lý Banner</span></a></li>
          <li><a href="{{ url('/all-categories') }}"><i class="fa fa-tasks"></i><span>Quản lý Danh mục</span></a></li>
          <li><a href="{{ url('/all-brands') }}"><i class="fa fa-tags"></i><span>Quản lý Thương hiệu</span></a></li>
          <li><a href="{{ url('/all-products') }}"><i class="fa fa-list"></i><span>Quản lý Sản phẩm</span></a></li>
          <li><a href="{{ url('/all-coupons') }}"><i class="fa fa-ticket"></i><span>Quản lý Mã giảm giá</span></a></li>
          <li><a href="{{ url('/manage-order') }}"><i class="fa fa-shopping-cart"></i><span>Quản lý Đơn hàng</span></a></li>
          <li><a href="{{ url('/all-users') }}"><i class="fa fa-universal-access"></i><span>Quản lý Người dùng</span></a></li>

          <!-- <li class="sub-menu">
            <a href="javascript:;"><i class="fa fa-book"></i><span>Quản lý Bài viết</span></a>
            <ul class="sub">
              <li><a href="{{ url('/all-category-posts') }}"><i class="fa fa-folder"></i> Danh mục Bài viết</a></li>
              <li><a href="{{ url('/all-posts') }}"><i class="fa fa-file-text"></i> Bài viết</a></li>
            </ul>
          </li> -->

          <li><a href="{{ url('/all-reviews') }}"><i class="fa fa-comments"></i><span>Quản lý đánh giá</span></a></li>

          <li>
            <a href="{{ url('/contacts') }}"><i class="fa fa-envelope"></i><span>Liên hệ</span></a>
          </li>

          <li class="sub-menu">
            <a href="javascript:;"><i class="fa fa-wpforms"></i><span>Báo cáo thống kê</span></a>
            <ul class="sub">
              <li><a href="{{ url('/admin/revenue/stocks_report') }}">Thống kê tồn kho</a></li>
              <li><a href="{{ url('/reports') }}">Tổng doanh thu các hạng mục</a></li>
            </ul>
          </li>

          <li>
            <a href="{{ route('admin.livechat.index') }}" class="{{ request()->is('admin/livechat*') ? 'active' : '' }}">
              <i class="fa fa-comments"></i>
              <span>Live Chat</span>
              @if( ($lc_unread ?? 0) > 0 )
                <span class="badge badge-danger pull-right" style="margin-left:8px;">{{ $lc_unread }}</span>
              @endif
            </a>
          </li>

        </ul>
      </div>
    </div>
  </aside>

  <!-- main -->
  <section id="main-content">
    <section class="wrapper">
      @yield('admin_content')
    </section>
  </section>
</section>

<!-- JS (chung) -->
<script src="{{ asset('backend/js/jquery2.0.3.min.js') }}"></script>
<script src="{{ asset('backend/js/bootstrap.js') }}"></script>
<script src="{{ asset('backend/js/jquery.dcjqaccordion.2.7.js') }}"></script>
<script src="{{ asset('backend/js/scripts.js') }}"></script>
<script src="{{ asset('backend/js/jquery.slimscroll.js') }}"></script>
<script src="{{ asset('backend/js/jquery.nicescroll.js') }}"></script>
<script src="{{ asset('backend/js/jquery.scrollTo.js') }}"></script>

<!-- Morris & Raphael (chỉ nạp 1 lần) -->
<script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>
{{-- <script src="{{ asset('backend/js/morris.js') }}"></script>  --}}

<!-- jQuery UI -->
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

@stack('scripts')
</body>
</html>
