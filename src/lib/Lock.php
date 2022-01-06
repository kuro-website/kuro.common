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
    const IF_NOT_EXISTS = 'NX';
    const MILLISECOND_EXPIRE_TIME = 'PX';
    const LOCK_VALUE = 1;

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
        $redis = Cache::store('redis')->handler();

        return !$redis->set($key, self::LOCK_VALUE, [self::IF_NOT_EXISTS, self::MILLISECOND_EXPIRE_TIME => $expire * 1000]);
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
        $redis = Cache::store('redis')->handler();
        $lua =<<<EOT
if redis.call("get",KEYS[1]) == ARGV[1] then
    return redis.call("del",KEYS[1])
else
    return 0
end
EOT;
        $redis->eval($lua, array($key, self::LOCK_VALUE), 1);

        return true;
    }
}