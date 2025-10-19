<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $proxies = env('TRUSTED_PROXIES', '*');
        if ($proxies <> '*')
        {
            $proxies = array_map('trim', explode(',', $proxies));
        }

        $middleware->trustProxies(
            at: is_array($proxies) ? $proxies : [$proxies],
            headers: Request::HEADER_X_FORWARDED_FOR
               | Request::HEADER_X_FORWARDED_HOST
               | Request::HEADER_X_FORWARDED_PORT
               | Request::HEADER_X_FORWARDED_PROTO
               | Request::HEADER_X_FORWARDED_AWS_ELB
        );

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
