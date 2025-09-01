<?php

namespace Azuriom\Plugin\Shop\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShopAuthentification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (shop_user() === null) {
            $intended = $request->isMethod('GET') && $request->route() && ! $request->expectsJson()
                ? $request->fullUrl()
                : $request->session()->previousUrl();

            $request->session()->put('shop.url.intended', $intended);

            return redirect()->route('shop.login');
        }

        return $next($request);
    }
}
