<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Chạy LogVisitor cho tất cả route thuộc nhóm "web"
        $middleware->appendToGroup('web', \App\Http\Middleware\LogVisitor::class);
        // hoặc có thể dùng:
        // $middleware->web(append: [\App\Http\Middleware\LogVisitor::class]);

        // Alias sẵn có của bạn
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            // (tuỳ chọn) nếu muốn dùng theo route: 'log.visitor' => \App\Http\Middleware\LogVisitor::class,
        ]);
    })
    ->withExceptions(function ($exceptions) {
        //
    })
    ->create();
