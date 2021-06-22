<?php
/**
 * BaseModel.php
 *
 * User: 0
 * Date: 2021.2.24
 * Email: <sunanzhi@kurogame.com>
 */
namespace kuro\base;

use kuro\exception\LogicException;
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

    /**
     * 批量修改状态
     *
     * @param array $ids 主键
     * @param int $status 修改后的状态
     * @param array $statusPool 状态池
     * @return bool
     *
     * @throws LogicException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author sunanzhi <sunanzhi@kurogame.com>
     * @since 2021.6.10 10:46
     */
    public function batchStatus(array $ids, int $status, array $statusPool): bool
    {
        if (!in_array($status, $statusPool)) {
            throw new LogicException('state value error');
        }
        $list = self::where($this->getPk(), 'in', $ids)->select();
        foreach ($list as $model) {
            if ($model->status == $status) {
                continue;
            }
            $model->status = $status;
            $model->save();
        }

        return true;
    }
}