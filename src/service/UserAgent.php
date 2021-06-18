<?php
/**
 * UserAgent.php
 *
 * User: sunanzhi
 * Date: 2021.5.6
 * Email: <sunanzhi@kurogame.com>
 */
namespace kuro\service;

use think\Service;

/**
 * Class UserAgent
 * @package app\service
 */
class UserAgent extends Service
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
        $this->app->bind('userAgent', \Jenssegers\Agent\Agent::class);
    }
}