<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Message;
use App\Models\Conversation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * 1) Chia sẻ dữ liệu menu (categories/brands/parents) cho mọi view FRONTEND
         *    - Có kiểm tra bảng/tồn tại cột để không lỗi khi chưa migrate
         *    - Dùng cache 10 phút để giảm query lặp lại
         */
        View::composer('*', function ($view) {
            // Chỉ chạy khi đã có bảng
            if (Schema::hasTable('categories') && Schema::hasTable('brands')) {
                try {
                    $categories = Cache::remember('v.categories.active', 600, function () {
                        return Category::where('category_status', 1)->get();
                    });

                    $brands = Cache::remember('v.brands.active', 600, function () {
                        return Brand::where('brand_status', 1)->get();
                    });

                    // Nếu bảng categories có cột parent_id thì mới lấy parents
                    $parents = null;
                    if (Schema::hasColumn('categories', 'parent_id')) {
                        $parents = Cache::remember('v.categories.parents', 600, function () {
                            return Category::with('children')
                                ->where('category_status', 1)
                                ->whereNull('parent_id')
                                ->get();
                        });
                    }

                    $view->with(compact('categories', 'brands', 'parents'));
                } catch (\Throwable $e) {
                    // Trong trường hợp migration chưa xong/vừa deploy
                    $view->with([
                        'categories' => collect(),
                        'brands'     => collect(),
                        'parents'    => collect(),
                    ]);
                }
            } else {
                // Chưa có bảng -> trả về rỗng để view không lỗi
                $view->with([
                    'categories' => collect(),
                    'brands'     => collect(),
                    'parents'    => collect(),
                ]);
            }
        });

        /**
         * 2) Badge Live Chat chưa đọc cho khu vực ADMIN
         *    - Chỉ bind vào layout/admin views để tránh query ở mọi trang
         *    - Có kiểm tra bảng messages + try/catch
         */
        View::composer(['admin_layout', 'admin.*', 'layouts.admin'], function ($view) {
            $unread = 0;

            if (Schema::hasTable('messages')) {
                try {
                    $unread = Cache::remember('v.livechat.unread_count', 30, function () {
                        return Message::whereNull('read_at')
                                      ->where('direction', 'in')
                                      ->count();
                    });
                } catch (\Throwable $e) {
                    $unread = 0;
                }
            }

            $view->with('lc_unread', $unread);
        });

        /**
         * 3) Composer cho widget livechat frontend
         *    - Đảm bảo luôn có biến $conversation khi include partials/livechat_widget
         */
        View::composer('partials.livechat_widget', function ($view) {
            // đảm bảo session tồn tại
            if (!session()->getId()) {
                session()->start();
            }
            $sessionId = session()->getId();

            if (Schema::hasTable('conversations')) {
                try {
                    $conversation = Conversation::firstOrCreate(
                        ['session_id' => $sessionId],
                        ['status' => 'open', 'last_message_at' => now()]
                    );
                } catch (\Throwable $e) {
                    $conversation = (object) ['session_id' => $sessionId];
                }
            } else {
                $conversation = (object) ['session_id' => $sessionId];
            }

            $view->with('conversation', $conversation);
        });
    }
}
