<?php /** @noinspection DuplicatedCode */

namespace app\api\traits;

use hg\apidoc\annotation as Apidoc;
use support\Response;

trait ControllerIndexTrait
{
    /**
     * 列表(分页)
     * @Apidoc\Method ("get")
     * @return Response
     */
    public function index(): Response
    {
        //前置操作（作用于where条件、页数、条数、order排序处理）
        $data = $this->beforeIndex();
        //返回json数据
        return api_success(
        //后置操作处理data、total数据
            $this->afterIndex(
                static::$service::getIndex($data['where'],$data['field'],$data['page'],$data['limit'],$data['order']),
                static::$service::getTotal($data['where'])
            ));
    }

    //前置where条件
    protected function beforeWhereIndex($where = []): ?array
    {
        return $where;
    }

    //前置order条件
    protected function beforeOrderIndex($order = []): ?array
    {
        return $order;
    }

    //前置group条件
    protected function beforeGroupIndex($group = []): ?array
    {
        return $group;
    }

    //前置field条件
    protected function beforeFieldIndex($field = []): ?array
    {
        return $field;
    }

    //前置操作（where条件、页数、条数、order排序处理）
    protected function beforeIndex($where = []): ?array
    {
        $where = $this->beforeWhere($where);
        $where = $this->beforeWhereIndex($where);
        $where = $this->beforeWhereUser($where);//默认关联用户id查询
        return [
            'where' => $where,
            'field' => $this->beforeFieldIndex(),
            'page' => $this->getPage(),
            'limit' => $this->getLimit(),
            'order' => $this->beforeOrderIndex(),
            'group' => $this->beforeGroupIndex()
        ];
    }

    //后置操作（处理查询返回的data、total数据）
    protected function afterIndex($data,$total): ?array
    {
        return compact('data','total');
    }

    /** 关联额外的条件或操作 */
    use ControllerRelationTrait;
}