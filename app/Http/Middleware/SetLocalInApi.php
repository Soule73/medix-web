<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocalInApi
{
    /**
     * Handle an incoming request.
     *
     * set application lang if header : Accept-Language exist in api request
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $lang = $request->header('Accept-Language');
        if ($lang) {
            App::setLocale($lang);
        }
        return $next($request);
    }
}
