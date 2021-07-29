<?php
/**
 * Consumer.php
 *
 * User: sunanzhi
 * Date: 2021.3.16
 * Email: <sunanzhi@kurogame.com>
 */
namespace kuro\console\queue;

use think\facade\Log;
use think\queue\Job;

/**
 * Class Consumer
 * @package app\console\queue
 */
class Consumer
{
    /**
     * 类名
     *
     * @var string
     */
    public $class;

    /**
     * 执行方法
     *
     * @var string
     */
    public $action;

    public function fire(Job $job, $data)
    {
        $this->class = $data['class'];
        $this->action = $data['action'];
        if ($job->attempts() >= 3) {
            Log::write([
                'class' => $this->class,
                'action' => $this->action,
                'data' => $data
            ], 'queue');
            $job->delete();
        }
        try{
            $args = $data['args'];
            $res = call_user_func_array([$this->class, $this->action], unserialize($args));
            $job->delete();
        }catch(\Throwable $e){
            Log::write([
                'jobId' => $job->getJobId(),
                'action' => $this->action,
                'time' => date('Y-m-d H:i:s'),
                'errorMessage' => $e->getMessage(),
                'data' => $data,
            ], 'queue');
        }
    }

    public function failed($data)
    {
        // ...任务达到最大重试次数后，失败了
        Log::write($data, 'queue');
    }
}