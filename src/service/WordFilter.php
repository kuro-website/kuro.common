<?php
/**
 * WordFilter.php
 *
 * User: sunanzhi
 * Date: 2021.4.15
 * Email: <sunanzhi@kurogame.com>
 */

namespace kuro\service;


use Grpc\ChannelCredentials;
use Grpc\Client\WordFilterClient;
use think\facade\Config;
use think\Service;

/**
 * Class WordFilter
 * @package app\service
 */
class WordFilter extends Service
{
    /**
     * 服务注册
     *
     * @throws \Exception
     *
     * @author sunanzhi <sunanzhi@kurogame.com>
     * @since 2021.4.15 17:30
     */
    public function register()
    {
        $client = new WordFilterClient(Config::get('grpc.wordFilter.host'), [
            'credentials' => ChannelCredentials::createInsecure(),
            'timeout' => 1000000,
        ]);

        $this->app->bind('wordFilter', $client);
    }
}