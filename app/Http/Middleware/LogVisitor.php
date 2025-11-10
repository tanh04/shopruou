<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogVisitor
{
    public function handle(Request $request, Closure $next)
    {
        // đảm bảo có session id
        if (!$request->session()->isStarted()) {
            $request->session()->start();
        }

        $sessionId = $request->session()->getId();
        $today     = now()->toDateString();

        DB::table('visitors')->updateOrInsert(
            [
                'session_id' => $sessionId,
                'visit_date' => $today,
            ],
            [
                'ip'         => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'visited_at' => now(),
                'updated_at' => now(),
                'created_at' => now(), // sẽ bỏ qua khi update
            ]
        );

        return $next($request);
    }
}
