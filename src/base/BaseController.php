<?php
declare (strict_types = 1);

namespace kuro\base;

use ReflectionClass;
use ReflectionException;
use think\App;
use think\exception\ValidateException;
use think\Validate;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 构造方法
     * @access public
     * @param App $app 应用对象
     * @throws ReflectionException
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

    /**
     * 初始化
     *
     * @throws ReflectionException
     *
     * @author sunanzhi <sunanzhi@kurogame.com>
     * @since 2021.3.1 15:47
     */
    protected function initialize()
    {
        $this->injectRepository();
    }

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

    /**
     * 注入
     *
     * @throws ReflectionException
     *
     * @author sunanzhi <sunanzhi@hotmail.com>
     */
    private function injectRepository()
    {
        $obj = new ReflectionClass(get_class($this));
        $ps = $obj->getProperties(\ReflectionProperty::IS_PROTECTED);
        $factory = DocBlockFactory::createInstance();
        foreach($ps as $v) {
            $docComment = $v->getDocComment();
            $docblock = $factory->create($docComment);
            $vName = $v->getName();
            if($docblock->hasTag('inject') && $docblock->hasTag('var')){
                $injectTag = current($docblock->getTagsByName('var'));
                assert($injectTag instanceof Var_);
                $repository = $injectTag->__toString();
                $this->$vName = new $repository;
            }
        }
    }
}
