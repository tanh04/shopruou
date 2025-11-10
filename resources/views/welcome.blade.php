<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>@yield('title', 'Home | E-Shopper')</title>
    
    <!-- Modern CSS -->
    <link href="{{ asset('css/modern-user.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Legacy CSS (for compatibility) -->
    <link href="{{ asset('frontend/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('frontend/css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('frontend/css/prettyPhoto.css') }}" rel="stylesheet">
    <link href="{{ asset('frontend/css/price-range.css') }}" rel="stylesheet">
    <link href="{{ asset('frontend/css/animate.css') }}" rel="stylesheet">
    <link href="{{ asset('frontend/css/main.css') }}" rel="stylesheet">
    <link href="{{ asset('frontend/css/responsive.css') }}" rel="stylesheet">
    
    <link rel="shortcut icon" href="images/ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="images/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="images/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="images/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="images/ico/apple-touch-icon-57-precomposed.png">

    @yield('styles')
</head><!--/head-->

<body>
	<header id="header"><!--header-->
		@if(session('success'))
			<div id="success-alert" 
				class="alert alert-success" 
				style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
				{{ session('success') }}
			</div>

			<script>
				setTimeout(function() {
					var alert = document.getElementById('success-alert');
					if (alert) {
						alert.style.display = 'none';
					}
				}, 3000); // 3000ms = 3s
			</script>
		@endif

		<div class="header-middle"><!--header-middle-->
			<div class="container">
				<div class="row">
					<div class="col-sm-2"></div>
				
					<div class="col-sm-10">
						<div class="shop-menu pull-right">
							<ul class="nav navbar-nav">
								<li><a href="#"><i class="fa fa-user"></i> Tài khoản</a></li>
								<li><a href="{{ route('wishlist.index') }}"><i class="fa fa-star"></i> Yêu thích</a></li>
								<!-- <li><a href="checkout.html"><i class="fa fa-crosshairs"></i> Checkout</a></li> -->

								@if(Auth::check())
								<li><a href="{{ url('/cart') }}"><i class="fa fa-shopping-cart"></i> Giỏ hàng</a></li>
								<li><a href="{{ route('order.history') }}"><i class="fa fa-list"></i> Lịch sử đơn hàng</a></li>
								<li><a href="#">Xin chào, {{ Auth::user()->name }}</a></li>
								<li>
									<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
										@csrf
									</form>
									<a href="#" onclick="event.preventDefault(); 
										if(confirm('Bạn có chắc muốn đăng xuất?')) {
											document.getElementById('logout-form').submit();
										}">
										Đăng xuất
									</a>
								</li>
								@else
									<li><a href="{{ route('login') }}">Đăng nhập</a></li>
									<li><a href="{{ route('register') }}">Đăng ký</a></li> {{-- Nút đăng ký --}}
								@endif
							</ul>
						</div>
					</div>

				</div>
			</div>
		</div><!--/header-middle-->
	
		<div class="header-bottom"><!--header-bottom-->
			<div class="container">
				<div class="row">
					<div class="col-sm-9">
						<div class="mainmenu pull-left">
							<ul class="nav navbar-nav collapse navbar-collapse">
								<li><a href="{{route('home')}}" class="active">TRANG CHỦ</a></li>
								<li class="dropdown">
                                    <a href="#">TIN TỨC<i class="fa fa-angle-down"></i></a>
                                    <ul role="menu" class="sub-menu">
                                        <li><a href="shop.html">Products</a></li>
                                    </ul>
                                </li>
								<li><a href="{{ route('contact') }}">LIÊN HỆ</a></li>
								<li><a href="{{ URL::to('/user/profile') }}">THÔNG TIN CÁ NHÂN</a></li>
							</ul>
						</div>
					</div>
					<div class="col-sm-3">
						<form action="{{ url('/search') }}" method="post" class="search-form" autocomplete="off">
							@csrf
							<div class="input-group" style="position: relative;">
								<input type="text"
									id="search-box"
									name="keywords_submit"
									class="form-control"
									style="width: 200px;"
									placeholder="Tìm kiếm sản phẩm">
								<span class="input-group-btn">
									<button type="submit" name="search_items"
											style="margin-top: -2px; height: 33px; margin-left: 5px; color:#666"
											class="btn btn-primary">Tìm kiếm</button>
								</span>

								{{-- Dropdown gợi ý --}}
								<ul id="search-suggest"
									class="dropdown-menu"
									style="display:none; position:absolute; left:0; right:65px; top:100%; margin-top:2px; max-height:260px; overflow:auto;">
								</ul>
							</div>
						</form>
					</div>
					<style>
						#search-suggest > li > a { display:flex; align-items:center; gap:8px; }
						#search-suggest img { width:28px; height:28px; object-fit:cover; border-radius:4px; }
						#search-suggest li.active > a,
						#search-suggest li:hover > a { background:#f5f5f5; }
					</style>

				</div>
			</div>
		</div><!--/header-bottom-->
	</header><!--/header-->

<!----------------------------------------- Trang slider------------------------------------>

	@include('partials.banner')

<!----------------------------------------------------------------------------------------->
	<section>
		<div class="container">
			<div class="row">
<!----------------------------------------- Trang sidebar------------------------------------>
				<div class="col-sm-3">
					{{-- Nếu trang con định nghĩa @section('sidebar') thì dùng nó, không thì dùng partial mặc định --}}
					@hasSection('sidebar')
						@yield('sidebar')
					@else
						@include('partials.sidebar')
					@endif
				</div>
				
<!----------------------------------------- Trang home------------------------------------>
				<div class="{{ (isset($hideSidebar) && $hideSidebar) ? 'col-sm-12' : 'col-sm-9 padding-right' }}">
					@yield('content')
				</div>
<!----------------------------------------------------------------------------------------->
			</div>
		</div>
	</section>
	
	@include('partials.footer')
	
    {{-- Chèn scripts --}}
    @yield('scripts')
  
    <script src="{{asset('frontend/js/jquery.js')}}"></script>
	<script src="{{asset('frontend/js/bootstrap.min.js')}}"></script>
	<script src="{{asset('frontend/js/jquery.scrollUp.min.js')}}"></script>
	<script src="{{asset('frontend/js/price-range.js')}}"></script>
    <script src="{{asset('frontend/js/jquery.prettyPhoto.js')}}"></script>
    <script src="{{asset('frontend/js/main.js')}}"></script>
	<script>
    $.validate({
        lang: 'vi', // đổi ngôn ngữ sang tiếng Việt
        errorMessagePosition: 'top', // hoặc 'inline' nếu muốn hiện dưới input
    });
	</script>

{{-- Zalo Chat (dưới cùng) --}}
<a id="zalo-chat-btn" href="https://zalo.me/0337065081" target="_blank"
   style="position:fixed; right:20px; bottom:20px; z-index:9998;
          background:#0288d1; color:#fff; border-radius:30px;
          padding:10px 18px; font-weight:600; text-decoration:none;
          box-shadow:0 4px 10px rgba(0,0,0,0.2); display:inline-flex; 
          align-items:center; gap:8px; font-size:14px;
          transition:all 0.3s;">
    <i class="fas fa-comments"></i> Chat Zalo
</a>

{{-- Nút bong bóng mở Livechat (trên Zalo) --}}
<div id="livechat-toggle" 
     style="position:fixed; right:20px; bottom:80px; z-index:10001;
            width:55px; height:55px; border-radius:50%; background:#4caf50;
            color:#fff; display:flex; align-items:center; justify-content:center;
            cursor:pointer; box-shadow:0 4px 8px rgba(0,0,0,0.3); font-size:22px;">
    <i class="fas fa-comments"></i>
</div>

{{-- Livechat Widget (ẩn/hiện, load từ partials) --}}
<div id="livechat-widget" 
     style="position:fixed; right:20px; bottom:140px; z-index:10000; 
            display:none; width:300px; height:400px;">
    @include('partials.livechat_widget')
</div>

<script>
    // Toggle livechat widget
    const toggle = document.getElementById('livechat-toggle');
    const widget = document.getElementById('livechat-widget');
    const closeBtn = document.querySelector('#livechat-widget #livechat-close'); // nút X trong header

    toggle.addEventListener('click', () => {
        widget.style.display = "block";
        toggle.style.display = "none"; // ẩn nút bong bóng
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            widget.style.display = "none";
            toggle.style.display = "flex"; // hiện lại nút bong bóng
        });
    }

    // Search suggest (không ảnh hưởng code khác)
    (function() {
      var $box = $('#search-box');
      var $list = $('#search-suggest');
      var typingTimer, lastQuery = '', highlighted = -1;

      function debounce(fn, ms) {
        return function() {
          clearTimeout(typingTimer);
          var args = arguments;
          typingTimer = setTimeout(function(){ fn.apply(null, args); }, ms);
        }
      }
      function hideList(){ $list.hide().empty(); highlighted = -1; }
      function render(items){
        if(!items || !items.length){ hideList(); return; }
        var html = items.map(function(p, idx){
          var img = p.product_image ? p.product_image : '';
          return '<li data-index="'+idx+'" data-id="'+(p.product_id||'')+'" data-name="'+(p.product_name||'')+'">' +
                   '<a href="javascript:void(0)">' +
                     (img ? '<img src="'+img+'" alt="">' : '') +
                     '<span>'+ (p.product_name||'') +'</span>' +
                   '</a>' +
                 '</li>';
        }).join('');
        $list.html(html).show();
      }
      function fetchSuggest(q){
        if(!q || q.trim()===''){ hideList(); return; }
        if(q === lastQuery) return;
        lastQuery = q;
        $.getJSON('{{ url('/search-suggest') }}', { term: q })
          .done(function(data){ render(data); })
          .fail(function(){ hideList(); });
      }
      $box.on('input', debounce(function(){
        fetchSuggest($box.val());
      }, 200));
      $list.on('click', 'li', function(){
        var name = $(this).data('name');
        $box.val(name);
        hideList();
        $(this).closest('form').trigger('submit');
      });
      $box.on('keydown', function(e){
        var $items = $list.find('li');
        if(!$items.length) return;
        if(e.key === 'ArrowDown'){
          e.preventDefault();
          highlighted = (highlighted + 1) % $items.length;
        } else if(e.key === 'ArrowUp'){
          e.preventDefault();
          highlighted = (highlighted - 1 + $items.length) % $items.length;
        } else if(e.key === 'Enter'){
          if(highlighted >= 0){
            e.preventDefault();
            $items.eq(highlighted).click();
          }
          return;
        } else if(e.key === 'Escape'){
          hideList();
          return;
        } else {
          return;
        }
        $items.removeClass('active');
        var $cur = $items.eq(highlighted).addClass('active');
        $box.val($cur.data('name'));
      });
      $box.on('blur', function(){ setTimeout(hideList, 150); });
    })();
</script>

</body>
</html>
