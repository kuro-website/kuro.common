<?php
declare (strict_types = 1);

namespace kuro\middleware;

use Closure;
use think\Request;
use think\Response;

/**
 * 全局响应中间件
 */
class GlobalResponse
{
    /**
     * 处理请求
     *
     * @param Request $request
     * @param Closure $next
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        // 网页抱错信息直接输出
        if(defined('DEBUG_ERROR') && DEBUG_ERROR === true) {
            return $response;
        }

        // 返回的数据
        $data = $response->getData();
        $returnType = gettype($data);
        // 重新封装返回的数据
        $resData = [
            '_data' => $data,
            '_code' => 0,
            '_errorCode' => '',
            '_message' => 'success',
            '_returnType' => $returnType,
        ];

        Response::create($resData, 'json', $response->getCode())->send();
        exit;
    }
}

