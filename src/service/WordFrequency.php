<?php
/**
 * WordFrequency.php
 *
 * User: sunanzhi
 * Date: 2021.4.15
 * Email: <sunanzhi@kurogame.com>
 */

namespace kuro\service;


use Grpc\ChannelCredentials;
use Grpc\Client\WordFrequencyClient;
use think\facade\Config;
use think\Service;

/**
 * Class WordFrequency
 * @package app\service
 */
class WordFrequency extends Service
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
        $client = new WordFrequencyClient(Config::get('gprc.wordFrequency.host'), [
            'credentials' => ChannelCredentials::createInsecure(),
            'timeout' => 1000000,
        ]);

        $this->app->bind('wordFrequency', $client);
    }
}