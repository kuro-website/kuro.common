<?php
namespace kuro\base;

use kuro\exception\ValidateException;
use think\Validate;

/**
 * 验证
 */
class BaseValidate extends Validate
{
    protected $childFailException;

    public function __construct($childFailException = true)
    {
        parent::__construct();
        $this->childFailException = $childFailException;
    }

    /**
     * @override
     *
     * @param array $data
     * @param array $rules
     * @return bool
     * @throws ValidateException
     *
     * @author sunanzhi <sunanzhi@kurogame.com>
     * @since 2021.4.1 11:47
     */
    public function check(array $data, array $rules = []): bool
    {
        $res = parent::check($data, $rules = []);
        if($res === false && $this->childFailException === true) {
            throw new ValidateException($this->getError());
        }

        return $res;
    }
}