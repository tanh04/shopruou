<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Kiểm tra nếu user đã đăng nhập và role = 0 (admin)
        if (Auth::check() && Auth::user()->role == 0) {
            return $next($request);
        }

        // Nếu không phải admin thì chuyển hướng hoặc báo lỗi
        return redirect('/')->with('error', 'Bạn không có quyền truy cập trang này!');
    }
}
