<?php
/**
 * AuthRequest.php
 *
 * User: 0
 * Date: 2021.2.24
 * Email: <sunanzhi@kurogame.com>
 */
namespace kuro\middleware;

use kuro\console\queue\Producer;
use kuro\dto\AdminDTO;
use kuro\exception\AbandonException;
use kuro\exception\AuthException;
use kuro\exception\ForbiddenException;
use Closure;
use Exception;
use kuro\exception\NotFoundException;
use kuro\sdk\center\Auth;
use think\Request;
use Throwable;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionException;

/**
 * class AuthRequest
 */
class AuthRequest
{
    /**
     * 权限校验中间件
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws AbandonException
     * @throws ForbiddenException
     * @throws ReflectionException|Throwable
     *
     * @author sunanzhi <sunanzhi@kurogame.com>
     */
    public function handle(Request $request, Closure $next)
    {
        $baseUrlArr = $this->getNamespaceAndMethod($request);
        try {
            $method = new \ReflectionMethod($baseUrlArr['namespace'], $baseUrlArr['method']);
        } catch (\Throwable $e) {
            throw new NotFoundException($e->getMessage());
        }
        $docComment = $method->getDocComment();
        $factory = DocBlockFactory::createInstance();
        $docblock = $factory->create($docComment);
        if($docblock->hasTag('abandon')){
            throw new AbandonException('接口已遗弃');
        }
        if($docblock->hasTag('internal')){
            throw new ForbiddenException('不对外开放');
        }
        // 接口角色 true: 可游客 false: 必须登陆
        $isGuest = $docblock->hasTag('guest');
        if(!$isGuest) {
            // 判断接口权限
            $this->checkAuth($request);
        }
        // 是否入队列
        if($docblock->hasTag('queue')) {
            $params = array_merge($request->get(), $request->post());
            Producer::defaultRequest($baseUrlArr['namespace'], $baseUrlArr['method'], $params);
        }

        return $next($request);
    }

    /**
     * 获取类命名空间
     *
     * @param Request $request
     * @return array
     *
     * @throws Exception
     * @author sunanzhi <sunanzhi@hotmail.com>
     */
    private function getNamespaceAndMethod(Request $request): array
    {
        $baseUrlArr = explode('/', $request->baseUrl());
        throwIf(count($baseUrlArr) < 3, ForbiddenException::class, '访问出错');
        $namespace = 'app\\controller\\' . ucfirst($baseUrlArr[1]);

        return ['namespace' => $namespace, 'controller' => $baseUrlArr[1], 'method' => $baseUrlArr[2]];
    }

    /**
     * 判断是否登陆
     *
     * @param Request $request
     * @return void
     * @throws Exception|Throwable
     */
    private function checkAuth(Request $request)
    {
        $authorization = $request->header('Authorization');
        throwIf(empty($authorization), AuthException::class, 'Missing Authorization header');
        $userInfo = (new Auth())->check('/'.request()->pathinfo(), array_merge($request->get(), $request->post()));
        $adminDTO = new AdminDTO();
        $adminDTO->setAdminId($userInfo['adminId']);
        $adminDTO->setUsername($userInfo['username']);

        app()->admin = $adminDTO;
    }
}
