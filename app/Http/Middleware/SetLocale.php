<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Carbon;
use App\Models\Setting;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Tangkap jika ada parameter url ?lang=en atau ?lang=id
        if ($request->has('lang') && in_array($request->query('lang'), ['en', 'id'])) {
            $locale = $request->query('lang');
            session(['locale' => $locale]);
        } else {
            // 2. Ambil dari session atau setting default sistem
            $defaultLang = Setting::get('default_language', 'en');
            $locale = session('locale', $defaultLang);
        }

        if (!in_array($locale, ['en', 'id'])) {
            $locale = 'en';
        }

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
