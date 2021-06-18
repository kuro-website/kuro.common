<?php
namespace kuro\dto;

use DomainException;
use think\Paginator as ThinkPaginator;

/**
 * 分页
 */
class DefaultPaginatorDTO extends ThinkPaginator
{
    public function render(){}

    /**
     * 转化返回键值
     *
     * @return array
     */
    public function toArray(): array
    {
        try {
            $total = $this->total();
        } catch (DomainException $e) {
            $total = null;
        }

        return [
            'perPage' => $this->listRows(),
            'current' => $this->currentPage(),
            'totalCount' => $total,
            'totalPage' => $this->lastPage,
            'item' => $this->items->toArray()
        ];
    }
}