<?php
/**
 * DingDing.php
 *
 * User: sunanzhi
 * Date: 2021.3.31
 * Email: <sunanzhi@kurogame.com>
 */

namespace kuro\lib;

use GuzzleHttp\Client;
use think\facade\Config;
use think\facade\Log;
use Throwable;

/**
 * Class DingDing
 * @package app\lib
 */
class DingDing
{
    /**
     * 推送错误
     *
     * @param Throwable $exception 异常
     *
     * @author sunanzhi <sunanzhi@kurogame.com>
     * @since 2021.3.31 16:55
     */
    public static function pushError(Throwable $exception)
    {
        $robots = Config::get('app.other.dingRobot.error');
        $url = $robots[array_rand($robots)];
        $content = 'exception'.json_encode([
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ], JSON_UNESCAPED_UNICODE);

        self::send($content, $url);
    }

    /**
     * 推送信息
     *
     * @param array $data 数据
     *
     * @author sunanzhi <sunanzhi@kurogame.com>
     * @since 2021.3.31 17:04
     */
    public static function pushInfo(array $data)
    {
        $robots = Config::get('app.other.dingRobot.info');
        $url = $robots[array_rand($robots)];

        self::send('info:'.json_encode($data, JSON_UNESCAPED_UNICODE), $url);
    }

    /**
     * Description
     *
     * @param string $content
     * @param string $url
     *
     * @author sunanzhi <sunanzhi@kurogame.com>
     * @since 2021.3.31 17:07
     */
    private static function send(string $content, string $url)
    {
        $data = [
            'msgtype' => 'text',
            'text' => [
                'content' => $content
            ]
        ];

        try {
            $promise = (new Client())->postAsync($url, [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => $data
            ])->then();
        } catch (Throwable $e) {
            Log::write($e->getMessage(), 'error');
            return;
        }
        $promise->wait();
    }
}