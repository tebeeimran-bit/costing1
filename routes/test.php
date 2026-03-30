<?php

use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json([
        'status' => 'ok',
        'time' => now(),
        'url' => request()->fullUrl(),
        'host' => request()->host(),
        'ip' => request()->ip(),
        'headers' => [
            'user-agent' => request()->header('user-agent'),
            'referer' => request()->header('referer'),
            'x-forwarded-for' => request()->header('x-forwarded-for'),
            'x-forwarded-host' => request()->header('x-forwarded-host'),
            'x-forwarded-proto' => request()->header('x-forwarded-proto'),
        ]
    ]);
});
