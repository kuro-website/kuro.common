<?php
/**
 * BaseMongo.php
 *
 * User: sunanzhi
 * Date: 2021.4.13
 * Email: <sunanzhi@kurogame.com>
 */

namespace kuro\base;

use think\Model;

/**
 * Class BaseMongo
 * @package app\base
 */
class BaseMongo extends Model
{
    protected $connection = 'mongo';
}