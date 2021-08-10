<?php
/**
 * AllowCrossDomain.php
 *
 * User: sunanzhi
 * Date: 2021.3.17
 * Email: <sunanzhi@kurogame.com>
 */

namespace kuro\middleware;

use think\Response;

class AllowCrossDomain
{
    public function handle($request, \Closure $next)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Max-Age: 3600');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE');
        header('Access-Control-Allow-Credentials: false');
        header('Access-Control-Allow-Headers: Content-Type, Content-Length, Authorization, Accept, X-Requested-With, Site-Id, Site-Lang');
        if (strtoupper($request->method()) == "OPTIONS") {
            Response::create()->send();
            exit;
        }

        return $next($request);
    }
}