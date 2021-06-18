<?php
/**
 * BaseModel.php
 *
 * User: 0
 * Date: 2021.2.24
 * Email: <sunanzhi@kurogame.com>
 */
namespace kuro\base;

use think\Model;

/**
 * 基础模型层
 */
class BaseModel extends Model
{
    /**
     * 数据集返回类型 简化使用
     *
     * @var string
     */
    protected $resultSetType = 'collection';

    /**
     * 接口默认分页配置
     *
     * @param int $page
     * @param int|null $listRows
     * @return array
     */
    public static function defaultPage(int $page = 1, ?int $listRows = 20): array
    {
        return [
            'list_rows' => $listRows,
            'page' => $page,
        ];
    }

    /**
     * 带链接参数分页配置
     *
     * @return array
     */
    public static function queryPage(): array
    {
        return [
            'type' => 'Bootstrap',
            'var_page' => 'page',
            'query' => request()->param(),
        ];
    }
}