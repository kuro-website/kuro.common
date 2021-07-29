<?php
namespace kuro\exception;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\ErrorException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\InvalidArgumentException;
use think\exception\ValidateException;
use think\facade\Log;
use think\Response;
use Throwable;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
        LogicException::class,
        InvalidArgumentException::class,
        HttpException::class
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // 添加自定义异常处理机制
        switch($e){
            // 逻辑异常
            case $e instanceof LogicException : 
                response(['_message' => $e->getMessage(), '_code' => 1000, '_returnType' => 'LogicException'], $e->getCode(), [], 'json')->send();
                exit;
            // 验证数据异常
            case $e instanceof ValidateException : 
                response(['_message' => $e->getMessage(), '_code' => 400, '_returnType' => 'ValidateException'], $e->getCode(), [], 'json')->send();
                exit;
            // 参数错误异常
            case $e instanceof InvalidArgumentException :
                response(['_message' => $e->getMessage(), '_code' => 400, '_returnType' => 'InvalidArgumentException'], 400, [], 'json')->send();
                exit;
            // 登陆auth异常
            case $e instanceof AuthException : 
                response(['_message' => $e->getMessage(), '_code' => 401, '_returnType' => 'AuthException'], 401, [], 'json')->send();
                exit;
            // 权限禁止异常
            case $e instanceof ForbiddenException : 
                response(['_message' => $e->getMessage(), '_code' => 403, '_returnType' => 'ForbiddenException'], 403, [], 'json')->send();
                exit;
            // 接口废除
            case $e instanceof AbandonException : 
            case $e instanceof NotFoundException :
                response(['_message' => $e->getMessage(), '_code' => 404, '_returnType' => 'AbandonException'], 404, [], 'json')->send();
                exit;
        }
        // 其他错误交给系统处理
        if(env('APP_DEBUG')) {
            (parent::render($request, $e))->send();
            exit;
        } else {
            Log::write($e->getMessage(), 'error');
            // 输出错误到钉钉
            response(['_message' => 'Internal Server Error', '_code' => 500, '_returnType' => 'error'], 500, [], 'json')->send();
            exit;
        }
    }
}
