<?php

namespace kuro\sdk;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use kuro\console\queue\Producer;
use kuro\exception\HttpException;
use think\facade\Cache;

/**
 * CClient.php
 *
 * User: sunanzhi
 * Date: 2021.6.18
 * Email: <sunanzhi@kurogame.com>
 */

class CClient
{
    /**
     * 项目
     *
     * @var string
     */
    protected $project;

    /**
     * 命名空间
     *
     * @var string
     */
    protected $namespace;

    /**
     * 模块
     *
     * @var string
     */
    protected $module;

    /**
     * 方法
     *
     * @var string
     */
    protected $method;

    /**
     * 多文件
     *
     * @var array
     */
    private $files;

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
    public function request(string $project, string $url, bool $async, string $returnType, ...$args)
    {
        $this->project = $project;
        // 获取api参数
        $apiParams = $this->getParams($url);
        // 封装请求参数
        $requestParams = $this->packageArgs($apiParams, $args);
        // 获取请求url
        $apiUrl = config('api.' . $this->project) . '/' . $url;

        return $this->guzzleRequest($apiUrl, $requestParams, $async, $returnType);
    }

    /**
     * 设置文件
     *
     * @param array $files
     *
     * @author sunanzhi <sunanzhi@kurogame.com>
     * @since 2021.6.18 17:36
     */
    public function setFiles(array $files)
    {
        $this->files = $files;

        return $this;
    }

    /**
     * 获取接口参数
     *
     * @param string $url 请求接口路径
     * @return array
     *
     * @throws ReflectionException
     * @author sunanzhi <sunanzhi@hotmail.com>
     */
    private function getParams(string $url): array
    {
        // 当前命名空间
        $this->namespace = (new \ReflectionClass(__CLASS__))->getNamespaceName();
        // 获取模块
        $urlExplode = explode('/', $url);
        $this->module = $urlExplode[0];
        $this->method = $urlExplode[1];
        // 获取接口参数
        return array_column((new \ReflectionMethod($this->namespace . '\\' .$this->project .'\\'. $this->module, $this->method))->getParameters(), 'name');
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
    private function packageArgs(array $apiParams, array $args): array
    {
        $resParams = [];
        $i = 0;
        foreach ($apiParams as $v) {
            if(isset($args[$i])) {
                $resParams[$v] = $args[$i];
                $i++;
            }
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
    private function guzzleRequest(string $url, array $args, bool $async, string $returnType)
    {
        // 异步设置丢队列
        if($async === true){
            // @todo
        }
        // 非异步设置正常走http请求
        $guzzleClient = new \GuzzleHttp\Client(['verify' => false]);
        $headers = $this->getHeaders();
        if(!empty($this->files)) {
            // 代表有文件
            $options = [
                'headers' => [
                    'Authorization' => request()->header('Authorization')
                ],
                'multipart' => $this->getMultipart($args),
                'http_errors' => false,
            ];
        } else {
            $body = json_encode($args);
            $options = ['body' => $body, 'headers' => $headers, 'http_errors' => false];
        }
        try {
            $response = $guzzleClient->post($url, $options);
        } catch (\Throwable $e) {
            throw new HttpException($e->getMessage());
        }
        $statusCode = $response->getStatusCode();
        $content = (string) $response->getBody();
        $object = '';

        if ('' != $content) {
            $object = json_decode($content, true);
        }

        // 内部接收到逻辑异常处理
        if (isset($object['_code']) && $object['_code'] > 0) {
            response(['_message' => $object['_message'] ?? 'Internal Server Error', '_code' => $statusCode, '_returnType' => 'error'], $statusCode, [], 'json')->send();
            exit;
        }
        $object = $this->bodyToObject($object);

        return $object;
    }

    private function getMultipart(array $args)
    {
        $multipart = [];
        foreach ($args as $k => $v) {
            $multipart[] = [
                'name'     => $k,
                'contents' => $v,
            ];
        }
        foreach ($this->files as $k => $v) {
            $multipart[] = [
                'name'     => $k,
                'contents' => fopen($v, 'r'),
            ];
        }

        return $multipart;
    }

    /**
     * 设置头部信息
     *
     * @return array
     *
     * @author sunanzhi <sunanzhi@hotmail.com>
     */
    private function getHeaders()
    {
        $authorization = request()->header('Authorization');
        $res = [
            'Authorization' => $authorization,
            'content-type'  => 'application/json'
        ];

        return $res;
    }

    /**
     * @param array $body
     *
     * @return mixed|string|int
     */
    private function bodyToObject(array $body)
    {
        $status = 1;
        isset($body['_returnType']) && ($status <<= 1)
        && (!\in_array($body['_returnType'], ['int', 'integer', 'float', 'double', 'string', 'boolean', 'bool', 'array']) && class_exists($body['_returnType'])) && ($status <<= 1)
        && is_subclass_of($body['_returnType'], Exception::class) && ($status <<= 1)
        && isset($body['context']) && ($status <<= 1);
        $object = null;
        switch ($status) {
            case 2:
                $object = $body['_data'];
                settype($object, $body['_returnType']);
                break;
            case 4:
                $object = $body['_returnType']::hydractor($body['_data']);
                break;
            case 16:
            case 8:
                throw new $body['_returnType']($body['_message']);
            case 1:
                $object = (string) $body;
                break;
        }

        return $object;
    }
}