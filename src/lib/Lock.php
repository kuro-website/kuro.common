<?php
/**
 * Lock.php
 *
 * User: sunanzhi
 * Date: 2021.4.20
 * Email: <sunanzhi@kurogame.com>
 */

namespace kuro\lib;

use think\facade\Cache;

/**
 * Class Lock
 * @package app\lib
 */
class Lock
{
    /**
     * 检查是否有锁
     *
     * @param string $key
     * @param integer $expire
     * @return bool
     *
     * @author sunanzhi <sunanzhi@kurogame.com>
     * @since 2021.4.20 17:30
     */
    public static function check(string $key, int $expire = 3):bool
    {
        if(!empty(Cache::get($key))) {
            // 有锁
            return true;
        } else {
            self::set($key, $expire);
        }

        return false;
    }

    /**
     * 设置锁
     *
     * @param string $key
     * @param integer $expire
     * @return bool
     *
     * @author sunanzhi <sunanzhi@kurogame.com>
     * @since 2021.4.20 17:27
     */
    public static function set(string $key, int $expire = 3): bool
    {
        Cache::set($key, true, $expire);

        return true;
    }

    /**
     * 清除锁
     *
     * @param string $key
     *
     * @return bool
     *
     * @author sunanzhi <sunanzhi@kurogame.com>
     * @since 2021.4.20 17:29
     */
    public static function delete(string $key): bool
    {
        Cache::delete($key);

        return true;
    }
}