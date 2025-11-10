<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\ProductExcelController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ViewController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Http\Controllers\Customer\ForgotPasswordController;
use App\Http\Controllers\Customer\ResetPasswordController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\Customer\ContactController;
use App\Http\Controllers\Admin\AdminContactController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Customer\CustomerProfileController;
use App\Http\Controllers\Customer\WishlistController;
use App\Http\Controllers\Customer\LiveChatController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\AdminLiveChatController;
use App\Http\Controllers\Admin\ChatBotController;

// Hiển thị thông báo yêu cầu xác thực email
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// Xử lý link xác nhận (từ email)
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect()->route('home');
})->middleware(['auth', 'signed'])->name('verification.verify');

// Gửi lại email xác thực
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');


// Các route đăng ký và đăng nhập cho guest (chưa đăng nhập)
Route::middleware('guest')->group(function () {
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register']);

    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

// Đăng xuất dành cho user đã đăng nhập
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Quên mật khẩu
Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

// Reset mật khẩu
Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');


Route::middleware(['auth'])->group(function () {
    // Xem trang hồ sơ
    Route::get('/user/profile', [CustomerProfileController::class, 'profile'])->name('user.profile');

    // Cập nhật thông tin cơ bản (tên, email, phone, address)
    Route::post('/user/profile', [CustomerProfileController::class, 'updateProfile'])->name('user.profile.update');

});

/*
Frontend
*/

    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    //Gợi ý tìm kiếm sp
    Route::get('/search-suggest', [HomeController::class, 'searchSuggest'])->name('search.suggest');
    /*
    DANH MỤC SẢN PHẨM
    */
    Route::get('/category/{category_id}', [CategoryController::class, 'show_category']);
    /*
    THƯƠNG HIỆU
    */
    Route::get('/brand/{brand_id}', [BrandController::class, 'show_brand']);
    /*
    CHI TIẾT SẢN PHẨM
    */
    Route::get('/product-details/{product_id}', [ProductController::class, 'show_product'])->name('product.details');

    //Lọc theo khoảng giá
    Route::get('/products', [ProductController::class, 'filter_price'])->name('product.filter_price');

    //Liên hệ
    Route::get('/contact', [ContactController::class, 'index'])->name('contact');

    // ====== Public (khách) ======
    Route::prefix('livechat')->group(function () {
        Route::post('/boot', [LiveChatController::class, 'boot'])->name('livechat.boot'); // tạo/nhận conversation_id theo session
        Route::get('/poll', [LiveChatController::class, 'poll'])->name('livechat.poll');  // lấy tin nhắn mới
        Route::post('/send', [LiveChatController::class, 'send'])->name('livechat.send'); // khách gửi tin
    });


Route::middleware(['auth','verified'])->group(function () {
    // Route::get('/home', [HomeController::class, 'index'])->name('home');
    // ... thêm các route cần bắt buộc xác thực


    Route::get('/welcome', function () {return view('welcome');});

    // Route::get('/', [HomeController::class, 'index'])->name('home');
    // Route::resource('home', HomeController::class);
    Route::post('/search', [HomeController::class, 'search'])->name('search');
    // /*
    // DANH MỤC SẢN PHẨM
    // */
    // Route::get('/category/{category_id}', [CategoryController::class, 'show_category']);
    // /*
    // THƯƠNG HIỆU
    // */
    // Route::get('/brand/{brand_id}', [BrandController::class, 'show_brand']);
    // /*
    // CHI TIẾT SẢN PHẨM
    // */
    // Route::get('/product-details/{product_id}', [ProductController::class, 'show_product'])->name('product.details');

    /*
    WHISHLIST
    */

 
    // Hiển thị wishlist
    Route::get('/wishlist', [WishlistController::class, 'show_wishlist'])
        ->name('wishlist.index');

    // Thêm sản phẩm vào wishlist
    Route::post('/wishlist/add/{product_id}', [WishlistController::class, 'save_wishlist'])
        ->whereNumber('product_id')
        ->name('wishlist.add');

    // Xóa 1 mục khỏi wishlist
    Route::delete('/wishlist/remove/{id}', [WishlistController::class, 'destroy'])
        ->whereNumber('id')
        ->name('wishlist.remove');

    // Xử lý thao tác hàng loạt (thêm nhiều vào giỏ, xóa nhiều)
    Route::post('/wishlist/handle', [WishlistController::class, 'handleWishlistAction'])
        ->name('handle_wishlist_action');
    


    /*
    CART
    */
    Route::get('/cart', [CartController::class, 'show_cart'])->name('show_cart');
    Route::post('/save-cart', [CartController::class, 'save_cart'])->middleware('auth');
    Route::post('/update-quantity', [CartController::class, 'update_quantity'])->name('update_quantity');

    Route::delete('/cart/{id}', [CartController::class, 'removeFromCart'])->name('cart.remove');

    //Route::get('/updatePrice', [CartController::class, 'updatePrice'])->name('updatePrice');
    Route::post('/cart/action', [CartController::class, 'handleCartAction'])->name('handle_cart_action');
    Route::post('/cart/update-price', [CartController::class, 'updatePrice']) ->name('cart.updatePrice');

    /*
    ORDER
    */

    Route::post('/checkout', [CustomerOrderController::class, 'checkout'])->name('checkout');
    Route::get('/show-order', [CustomerOrderController::class, 'show_order'])->name('show_order')->middleware('auth');

    Route::post('/save-order', [CustomerOrderController::class, 'save_order'])->name('save-order')->middleware('auth');
    //hủy đơn
    Route::put('/orders/{id}/cancel', [CustomerOrderController::class, 'cancel'])->name('order.cancel');

    Route::post('/orders/{order}/pay-again/momo', [CustomerOrderController::class, 'payAgainMomo'])->name('user.orders.payAgain.momo');
    Route::post('/orders/{order}/pay-again/atm',  [CustomerOrderController::class, 'payAgainAtm'])->name('user.orders.payAgain.atm');

    //Payment
    // Thanh toán MoMo
    Route::get('/payment/momo/{order_id}', [PaymentController::class, 'momo_payment'])->name('momo_payment'); 


    //VNPay
    Route::get('/payment/vnpay/{order}', [PaymentController::class, 'vnpay_payment'])->name('vnpay_payment');
    Route::get('/vnpay-return', [PaymentController::class, 'vnpayReturn'])->name('vnpay.return');
    // Đi VNPAY
    Route::get('/payment/vnpay/{order}', [PaymentController::class, 'processVnpPayment'])
        ->name('vnpay_payment');

    // IPN từ VNPAY bắn về (GET hoặc POST tuỳ cấu hình portal)
    Route::match(['get','post'], '/payment/vnpay/ipn', [PaymentController::class, 'vnpIpn'])
    ->name('ipn.vnp');


    // Return URL (hiển thị kết quả)
    Route::get('/thanks/{order_id}', [PaymentController::class, 'thanks'])->name('thanks');

    // IPN (server-to-server)
    Route::post('/ipn/momo', [PaymentController::class, 'momoIpn'])->name('ipn.momo');
    Route::post('/ipn/vnp',  [PaymentController::class, 'vnpIpn'])->name('ipn.vnp');

    Route::middleware(['auth'])->group(function () {
        Route::get('/orders/history', [CustomerOrderController::class, 'history'])
            ->name('order.history');

        Route::get('/orders/show_history/{order_id}', [CustomerOrderController::class, 'show_history'])
            ->name('order.show_history');
    });

    Route::get('/products/{product}', [ReviewController::class, 'show'])->name('products.show');


    // ĐÁNH GIÁ: đổi name sai -> name đúng
    Route::middleware('auth')->group(function () {
        Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])
            ->name('reviews.store');
    });

    // //Lọc theo khoảng giá
    // Route::get('/products', [ProductController::class, 'filter_price'])->name('product.filter_price');

    // //Liên hệ
    // Route::get('/contact', [ContactController::class, 'index'])->name('contact');
    Route::post('/contact', [ContactController::class, 'sendContact'])
        ->middleware('throttle:5,1') // tuỳ chọn: chống spam
        ->name('contact.send');
});


/*
Backend - ADMIN
*/
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
    Route::get('/dashboard', [AdminController::class, 'show'])->name('dashboard');

    /*
    Banner
    */
    Route::get('/all-banners', [BannerController::class,'all_banners']);
    Route::get('/create-banner', [BannerController::class,'create_banner']);
    Route::post('/save-banner', [BannerController::class,'save_banner']);
    Route::get('/edit-banners/{id}', [BannerController::class,'edit_banner']);
    Route::put('/banners/{id}', [BannerController::class,'update_banner'])->name('banners.update');

    Route::delete('/delete-banner/{id}', [BannerController::class,'destroy']);
    Route::post('banners/{id}/toggle',   [BannerController::class,'toggle']);


    /*
    Category
    */
    Route::get('/create-category', [CategoryController::class, 'create_category']);
    Route::get('/edit-category/{category_id}', [CategoryController::class, 'edit_category']);
    Route::post('/update-category/{category_id}', [CategoryController::class, 'update_category']);

    Route::get('/delete-category/{category_id}', [CategoryController::class, 'delete_category']);

    Route::get('/all-categories', [CategoryController::class, 'all_categories']);
    Route::post('/save-category', [CategoryController::class, 'save_category']);

    Route::get('/active-category/{category_id}', [CategoryController::class, 'active_category']);
    Route::get('/unactive-category/{category_id}', [CategoryController::class, 'unactive_category']);

    Route::delete('force-delete-category/{category_id}', [CategoryController::class, 'force_delete_category'])->name('force_delete_category');

    /*
    Brands
    */
    Route::get('/create-brand', [BrandController::class, 'create_brand']);
    Route::get('/edit-brand/{brand_id}', [BrandController::class, 'edit_brand']);
    Route::post('/update-brand/{brand_id}', [BrandController::class, 'update_brand']);

    Route::get('/delete-brand/{brand_id}', [BrandController::class, 'delete_brand']);

    Route::get('/all-brands', [BrandController::class, 'all_brands']);
    Route::post('/save-brand', [BrandController::class, 'save_brand']);

    Route::get('/active-brand/{brand_id}', [BrandController::class, 'active_brand']);
    Route::get('/unactive-brand/{brand_id}', [BrandController::class, 'unactive_brand']);

    /*
    Product
    */
    Route::get('/create-product', [ProductController::class, 'create_product']);
    Route::get('/edit-product/{product_id}', [ProductController::class, 'edit_product']);
    Route::post('/update-product/{product_id}', [ProductController::class, 'update_product']);

    Route::get('/delete-product/{product_id}', [ProductController::class, 'delete_product']);

    Route::get('/all-products', [ProductController::class, 'all_products']);
    Route::post('/save-product', [ProductController::class, 'save_product']);

    Route::get('/active-product/{product_id}', [ProductController::class, 'active_product']);
    Route::get('/unactive-product/{product_id}', [ProductController::class, 'unactive_product']);
    Route::get('/add-to-cart/{id}', [CartController::class, 'addToCart'])->name('add-to-cart');

     Route::get('products/export/{format?}', [ProductController::class, 'export'])
        ->whereIn('format', ['xlsx', 'csv'])
        ->name('products.export');

    // Import
    Route::post('products/import', [ProductController::class, 'import'])
        ->name('products.import');

    // Tải file mẫu
    Route::get('products/template', [ProductController::class, 'downloadTemplate'])
        ->name('products.template');

    //user
    Route::get('/create-user', [UserController::class, 'create_user'])->name('admin.users.create');
    Route::post('/save-user',   [UserController::class, 'save_user'])->name('admin.users.store');

    // (tuỳ chọn) liệt kê/sửa/xoá nếu cần sau này:
    Route::get('/all-users',          [UserController::class, 'all_users'])->name('admin.users.index');
    Route::get('/edit-user/{id}',     [UserController::class, 'edit_user'])->name('admin.users.edit');
    Route::post('/update-user/{id}',  [UserController::class, 'update_user'])->name('admin.users.update');
    Route::get('/delete-user/{id}',   [UserController::class, 'delete_user'])->name('admin.users.delete');

    /*
    Order
    */
    Route::get('/manage-order', [AdminOrderController::class, 'manage_order'])->name('manage_order');
    Route::get('/order-detail/{order_id}', [AdminOrderController::class, 'order_detail']) ->name('order_detail');
    Route::get('/order-delete/{id}', [AdminOrderController::class, 'order_delete'])->name('order_delete');
    Route::put('/orders/{order_id}/status', [AdminOrderController::class, 'updateStatus'])->name('order_update_status');

    Route::put('/orders/{id}/deliver', [AdminOrderController::class, 'markDelivered'])->name('order_mark_delivered');


    /*
    Coupon
    */
    Route::middleware(['auth','admin'])->group(function () {
        Route::get('/create-coupon', [CouponController::class, 'create_coupon']);
        Route::post('/save-coupon', [CouponController::class, 'save_coupon'])->name('save_coupon');

        Route::get('/edit-coupon/{coupon_id}', [CouponController::class, 'edit_coupon']);
        Route::post('/update-coupon/{coupon_id}', [CouponController::class, 'update_coupon']);
        Route::get('/delete-coupon/{coupon_id}', [CouponController::class, 'delete_coupon']);
        Route::get('/all-coupons', [CouponController::class, 'all_coupons']);
        Route::get('/active-coupon/{coupon_id}', [CouponController::class, 'active_coupon']);
        Route::get('/unactive-coupon/{coupon_id}', [CouponController::class, 'unactive_coupon']);
    // });

        //Xuất pdf
        Route::get('/admin/orders/{order_id}', [AdminOrderController::class, 'order_detail'])->name('admin.orders.detail');
        Route::get('/admin/orders/{order_id}/print', [AdminOrderController::class, 'print_order'])->name('admin.orders.print');

        //Liên hệ
        Route::get('/contacts', [AdminContactController::class, 'all_contacts'])
                ->name('manage_contacts.all_contacts');

            // Xem chi tiết 1 liên hệ
            Route::get('/contacts/{contact}', [AdminContactController::class, 'show_contacts'])
                ->name('manage_contacts.show_contact');

            // Cập nhật trạng thái
            Route::patch('/contacts/{contact}', [AdminContactController::class, 'update'])
                ->name('manage_contacts.update');

            // Xoá liên hệ
            Route::delete('/contacts/{contact}', [AdminContactController::class, 'destroy'])
                ->name('manage_contacts.destroy');

            //Reply 
            Route::post('/admin/contacts/{id}/reply', [AdminContactController::class, 'reply'])
                ->name('admin.contacts.reply');


        //Đánh giá
        Route::get('/all-reviews', [AdminReviewController::class, 'all_reviews'])->name('all_reviews');
                // Đổi trạng thái (Hiện/Ẩn)
            Route::patch('/reviews/{review}/toggle', [AdminReviewController::class, 'toggle'])
                ->name('admin.manage_reviews.toggle');

            // Xoá review
            Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])
                ->name('admin.manage_reviews.destroy');

        //view
        Route::get('/reports', [ReportController::class, 'index'])->name('reports');
        // Trang dashboard
        // Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        //dashboard
        Route::get('/admin/dashboard', [DashboardController::class, 'show_user']) ->name('admin.dashboard');
        
        // API cho chart (GET để khỏi cần CSRF)
        Route::get('/chart/revenue-profit',   [DashboardController::class, 'chartRevenueProfit']);
        Route::get('/chart/orders-qty',       [DashboardController::class, 'chartOrdersQty']);
        Route::get('/chart/categories-share', [DashboardController::class, 'chartCategoriesShare']);
        Route::get('/chart/brands-share',     [DashboardController::class, 'chartBrandsShare']);
        Route::get('/chart/top-products',     [DashboardController::class, 'chartTopProducts']);
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export/{format}', [ReportController::class, 'export'])->name('reports.export');

        //Xuất báo cáo thống kê
        Route::get('/views', [ViewController::class, 'index'])->name('views.index');
        Route::get('/views/export/{format}', [ViewController::class, 'export'])->name('views.export');

        //////////

        Route::get('admin/revenue/stocks_report', [ReportController::class, 'stocksReport'])
            ->name('revenue.stocks_report');

        //Trang cá nhân
        Route::get('/profile',  [AdminProfileController::class, 'edit'])->name('Auth.admin_infor.edit');
        Route::patch('/profile',[AdminProfileController::class, 'update'])->name('Auth.admin_infor.update');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/days-order',       [DashboardController::class, 'daysOrder']);
        Route::post('/filter-by-date',   [DashboardController::class, 'filterByDate']);
        Route::post('/dashboard-filter', [DashboardController::class, 'dashboardFilter']);
        // (tuỳ chọn) donut
        Route::post('/totals-donut',     [DashboardController::class, 'totalsDonut']);


    });

    Route::prefix('admin/livechat')->middleware('admin')->group(function () {
        Route::get('/', [AdminLiveChatController::class, 'index'])->name('admin.livechat.index');

        Route::get('/conversations', [AdminLiveChatController::class, 'conversations'])
            ->name('admin.livechat.conversations');

        Route::get('/messages/{conversation}', [AdminLiveChatController::class, 'messages'])
            ->name('admin.livechat.messages');

        Route::post('/send/{conversation}', [AdminLiveChatController::class, 'send'])
            ->name('admin.livechat.send');

        Route::post('/close/{conversation}', [AdminLiveChatController::class, 'close'])
            ->name('admin.livechat.close');
    });
});