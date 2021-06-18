<?php
namespace kuro\sdk;

use GuzzleHttp\Exception\GuzzleException;
use kuro\console\queue\Producer;
use GuzzleHttp\Exception\ServerException;
use ReflectionException;
use think\Exception;
use think\facade\Cache;

/**
 * class Client
 *
 * @author sunanzhi <sunanzhi@hotmail.com>
 */
class Client
{
    /**
     * 命名空间
     *
     * @var string
     */
    protected static $namespace;

    /**
     * 模块
     *
     * @var string
     */
    protected static $module;

    /**
     * 控制器
     *
     * @var string
     */
    protected static $moduleClass;

    /**
     * 请求接口
     *
     * @param string $url 请求接口路径
     * @param string $method 请求接口方法
     * @param bool $async
     * @param string $returnType
     * @param mixed ...$args 参数
     * @return mixed
     *
     * @throws Exception
     * @author sunanzhi <sunanzhi@hotmail.com>
     */
    public static function request(string $url, string $method, bool $async, string $returnType, ...$args)
    {
        // 获取api参数
        $apiParams = self::getParams($url, $method);
        // 封装请求参数
        $requestParams = self::packageArgs($apiParams, $args);
        // 获取请求url
        $apiUrl = config('api.providerurl.' . self::$module) . '/' . self::$module . '/' . self::$moduleClass . '/' . $method;
        return self::guzzleRequest($apiUrl, $requestParams, $async, $returnType);
    }

    /**
     * 获取接口参数
     *
     * @param string $url 请求接口路径
     * @param string $method 请求接口方法
     * @return array
     *
     * @throws ReflectionException
     * @author sunanzhi <sunanzhi@hotmail.com>
     */
    private static function getParams(string $url, string $method): array
    {
        // 当前命名空间
        self::$namespace = (new \ReflectionClass(__CLASS__))->getNamespaceName();
        // 获取模块
        $urlExplode = explode('/', $url);
        self::$module = $urlExplode[0];
        self::$moduleClass = $urlExplode[1];
        // 获取接口参数
        return array_column((new \ReflectionMethod(self::$namespace . '\\' . self::$module . '\\' . self::$moduleClass, $method))->getParameters(), 'name');
    }

    /**
     * 封装参数
     *
     * @param array $apiParams 接口参数
     * @param array $args 请求的参数
     * @return mixed
     *
     * @author sunanzhi <sunanzhi@hotmail.com>
     */
    private static function packageArgs(array $apiParams, array $args): array
    {
        $resParams = [];
        $i = 0;
        foreach ($apiParams as $v) {
            $resParams[$v] = $args[$i];
            $i++;
        }
        return $resParams;
    }

    /**
     * 发送请求
     *
     * @param string $url 请求url
     * @param array $args 请求参数
     * @param bool $async
     * @param string $returnType
     * @return mixed
     *
     * @throws GuzzleException|Exception
     * @author sunanzhi <sunanzhi@hotmail.com>
     */
    private static function guzzleRequest(string $url, array $args, bool $async, string $returnType)
    {
        // 异步设置丢队列
        if($async === true){
            $urlArr = parse_url($url);
            $urlPathArr = explode('/', $urlArr['path']);
            $namespace = 'app\\' . $urlPathArr[1] . '\\logic\\' . $urlPathArr[2] . 'Logic';
            (new Producer())->publish('asyncRequest', $args, ['baseUrlArr' => ['namespace' => $namespace, 'method' => end($urlPathArr)]]);
            return forceChangeType($returnType, 'async');
        }
        // 非异步设置正常走http请求
        $guzzleClient = new \GuzzleHttp\Client(['verify' => false]);
        $body = json_encode($args);
        $headers = self::getHeaders();
        try {
            $response = $guzzleClient->request('POST', $url, ['body' => $body, 'headers' => $headers]);
        } catch (ServerException $e) {
            throw new Exception($e->getMessage());
        }
        $content = (string) $response->getBody();
        $object = '';

        if ('' != $content) {
            $object = json_decode($content, true);
        }

        // 内部接收到逻辑异常处理
        if (isset($object['errorCode']) && $object['errorCode'] > 0) {
            throw new LogicException(isset($object['errorMsg']) ? $object['errorMsg'] : '系统错误');
        }
        $object = self::bodyToObject($object);

        return $object;
    }

    /**
     * 设置头部信息
     *
     * @return array
     *
     * @author sunanzhi <sunanzhi@hotmail.com>
     */
    private static function getHeaders()
    {
        $lowModule = strtolower(self::$module);
        $morsmordre = require env('app_path') . $lowModule . '/config/api.php';
        $options = $morsmordre['morsmordre']['options'];
        $guzzleClient = new \GuzzleHttp\Client(['verify' => false]);
        $body = json_encode([
            'grant_type' => 'client_credentials',
            'client_id' => $options['clientId'],
            'client_secret' => $options['clientSecret'],
        ]);
        if(Cache::get($body)){
            return Cache::get($body);
        }
        $url = $options['urlAccessToken'];
        try {
            $response = $guzzleClient->request('POST', $url, ['body' => $body, 'headers' => ['content-type' => 'application/json']]);
        } catch (ServerException $e) {
            throw new Exception($e->getMessage());
        }
        $content = (string) $response->getBody();
        $object = '';

        if ('' != $content) {
            $object = json_decode($content, true);
        }
        // 内部接收到逻辑异常处理
        if (isset($object['errorCode']) && $object['errorCode'] > 0) {
            throw new LogicException(isset($object['errorMsg']) ? $object['errorMsg'] : '系统错误');
        }
        $res = [
            'Authorization' => $object['data']['accessToken'],
            'content-type'  => 'application/json'
        ];
        Cache::set($body, $res, 1800);

        return $res;
    }

    /**
     * @param array $body
     *
     * @return mixed|string|int
     */
    private static function bodyToObject(array $body)
    {
        $status = 1;
        isset($body['returnType']) && ($status <<= 1)
        && (!\in_array($body['returnType'], ['int', 'integer', 'float', 'double', 'string', 'boolean', 'bool', 'array']) && class_exists($body['returnType'])) && ($status <<= 1)
        && is_subclass_of($body['returnType'], Exception::class) && ($status <<= 1)
        && isset($body['context']) && ($status <<= 1);
        $object = null;
        switch ($status) {
            case 2:
                $object = $body['data'];
                settype($object, $body['returnType']);
                break;
            case 4:
                $object = $body['returnType']::hydractor($body['data']);
                break;
            case 16:
            case 8:
                throw new $body['returnType']($body['errorMsg']);
            case 1:
                $object = (string) $body;
                break;
        }

        return $object;
    }
}