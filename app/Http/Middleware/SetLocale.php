<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next)
{
    $locale = session('locale', 'en');
    App::setLocale($locale);
    
    $response = $next($request);
    $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
    return $response;
}
}