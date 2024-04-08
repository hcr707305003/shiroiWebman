<?php

namespace app\api\traits;

use hg\apidoc\annotation as Apidoc;
use support\Response;

trait ControllerTotalTrait
{
    /**
     * 条数
     * @Apidoc\Method ("get")
     * @return Response
     */
    public function total(): Response
    {
        //前置操作（作用于where条件、页数、条数、order排序处理）
        $data = $this->beforeTotal();
        //返回json数据
        return api_success(
        //后置操作处理data、total数据
            $this->afterTotal([
                'total' => static::$service::getTotal($data['where'])
            ]));
    }

    protected function beforeTotal($where = []): ?array
    {
        $where = $this->beforeWhere($where);
        $where = $this->beforeWhereTotal($where);
        $where = $this->beforeWhereUser($where);//默认关联用户id查询
        return [
            'where' => $where
        ];
    }

    public function beforeWhereTotal($where = []): ?array
    {
        return $where;
    }

    //后置操作（处理查询返回的data、total数据）
    protected function afterTotal($total): ?array
    {
        return $total;
    }

    /** 关联额外的条件或操作 */
    use ControllerRelationTrait;
}