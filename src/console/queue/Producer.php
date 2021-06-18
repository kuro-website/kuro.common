<?php
/**
 * Producer.php
 *
 * User: sunanzhi
 * Date: 2021.3.16
 * Email: <sunanzhi@kurogame.com>
 */
namespace kuro\console\queue;

use ReflectionException;
use think\facade\Queue;
use think\Response;

/**
 * Class Producer
 * @package app\console\queue
 */
class Producer
{
    /**
     * 发送
     *
     * @param string $class 类
     * @param string $action 执行函数
     * @param array $params 参数
     * @param array $extra 额外拓展
     * @return void
     *
     * @throws ReflectionException
     * @author sunanzhi <sunanzhi@kurogame.com>
     */
    public static function send(string $class, string $action, array $params, array $extra = [])
    {
        $method = new \ReflectionMethod($class, $action);
        $parameters = $method->getParameters();
        $args = [];
        foreach($parameters as $param) {
            $type = $param->getType()->getName();
            $name = $param->getName();
            switch($type) {
                default :
                    $args[$name] = isset($params[$name]) ? $params[$name] : ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null);
            }
        }
        if(substr($class, 0, 1 ) != '\\') {
            $class = '\\'.$class;
        }

        $data = [
            'class' => $class,
            'action' => $action,
            'args' => serialize($args),
            'extra' => $extra
        ];

        Queue::push(Consumer::class, $data, $queue = null);
    }

    /**
     * 默认请求入队列
     *
     * @param string $class 类
     * @param string $action 执行行为
     * @param array $params 参数
     * @param array $extra 拓展
     *
     * @throws ReflectionException
     * @author sunanzhi <sunanzhi@kurogame.com>
     */
    public static function defaultRequest(string $class, string $action, array $params = [], array $extra = [])
    {
        self::send($class, $action, $params, $extra);
        $resData = [
            '_data' => true,
            '_code' => 0,
            '_message' => 'SUCCESS',
            '_returnType' => 'boolean',
        ];

        Response::create($resData, 'json', 200)->send();
        die;
    }
}