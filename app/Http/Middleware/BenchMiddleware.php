<?php
/**
 * @author lurrpis
 * @date 16/7/5 下午6:24
 * @blog http://lurrpis.com
 */

namespace App\Http\Middleware;

use Closure;
use Ubench;

class BenchMiddleware
{
    public function handle($request, Closure $next)
    {
        $bench = new Ubench();
        $bench->start();
        $response = $next($request);
        $bench->end();

        if ($response->headers->get('content-type') == 'application/json') {
            $collection = $response->original;
            $collection['time'] = $bench->getTime();
            $collection['usage'] = $bench->getMemoryUsage();
            $response->setContent($collection);
        }

        return $response;
    }
}