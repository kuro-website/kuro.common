<?php
/**
 * Example.php
 *
 * User: sunanzhi
 * Date: 2021.6.18
 * Email: <sunanzhi@kurogame.com>
 */

namespace kuro\sdk\services;

use kuro\sdk\CClient;

class Example
{
    public function returnBool(): bool
    {
        return (new CClient())->request('services', 'Example/returnBool', false, 'bool');
    }
}