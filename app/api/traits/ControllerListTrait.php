<?php /** @noinspection DuplicatedCode */

namespace app\api\traits;

use hg\apidoc\annotation as Apidoc;
use support\Response;

trait ControllerListTrait
{
    /**
     * 列表(不分页)
     * @Apidoc\Method ("get")
     * @return Response
     */
    public function list(): Response
    {
        //前置操作（作用于where条件、页数、条数、order排序处理）
        $data = $this->beforeList();
        //返回json
        return api_success($this->afterList(
            static::$service::getLists($data['where'],$data['field'],$data['order']),
            static::$service::getTotal($data['where'])
        ));
    }

    //前置where条件
    protected function beforeWhereList($where = []): ?array
    {
        return $where;
    }

    //前置order条件
    protected function beforeOrderList($order = []): ?array
    {
        return $order;
    }

    //前置group条件
    protected function beforeGroupList($group = []): ?array
    {
        return $group;
    }

    //前置field条件
    protected function beforeFieldList($field = []): ?array
    {
        return $field;
    }

    //前置操作（where条件、页数、条数、order排序处理）
    protected function beforeList($where = []): ?array
    {
        $where = $this->beforeWhere($where);
        $where = $this->beforeWhereList($where);
        $where = $this->beforeWhereUser($where);//默认关联用户id查询
        return [
            'where' => $where,
            'field' => $this->beforeFieldList(),
            'page' => $this->getPage(),
            'limit' => $this->getLimit(),
            'order' => $this->beforeOrderList(),
            'group' => $this->beforeGroupList()
        ];
    }

    //后置操作（处理查询返回的data、total数据）
    protected function afterList($data,$total): ?array
    {
        return compact('data','total');
    }

    /** 关联额外的条件或操作 */
    use ControllerRelationTrait;
}