<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Log; // Log modelini unutma

class LogRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Gelen isteği logla
        $log = new Log();
        $log->ip = $request->ip();
        $log->url = $request->fullUrl();
        $log->save();

        return $next($request);
    }
}
