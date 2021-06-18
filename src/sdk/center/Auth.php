<?php
/**
 * Auth.php
 *
 * User: sunanzhi
 * Date: 2021.6.18
 * Email: <sunanzhi@kurogame.com>
 */

namespace kuro\sdk\center;

use kuro\sdk\CClient;

class Auth
{
    public function check(string $router, array $params = [], array $deviceInfo =[]): array
    {
        return (new CClient())->request('center', 'Auth/check', false, 'array', $router, $params, $deviceInfo);
    }
}