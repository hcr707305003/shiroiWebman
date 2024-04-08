<?php /** @noinspection DuplicatedCode */

namespace app\api\traits;

use hg\apidoc\annotation as Apidoc;
use support\Response;

trait ControllerShowTrait
{
    /**
     * 详情
     * @Apidoc\RouteParam("id",type="string",desc="ID",require=true)
     */
    public function read($id): Response
    {
        //前置操作（获取要保存的参数）
        $data = $this->beforeRead($id);
        //后置操作(对接收的参数进行处理)
        return api_success(
            $this->afterRead(
                static::$service::getInfo($data['where'], $data['field'], $data['order'])
            ));
    }
    public function show($id): Response
    {
        //前置操作（获取要保存的参数）
        $data = $this->beforeShow($id);
        //后置操作(对接收的参数进行处理)
        return api_success(
            $this->afterShow(
                static::$service::getInfo($data['where'], $data['field'], $data['order'])
            ));
    }

    //查(个人详情)前置操作
    protected function beforeShow($id, $where = []): ?array
    {
        $where = $this->beforeWhere($where);
        $where = $this->beforeWhereShow($where);
        $where = $this->beforeWhereUser($where);//默认关联用户id查询
        $where = $this->beforeWhereId($id,$where);
        return [
            'where' => $where,
            'field' => $this->beforeFieldShow(),
            'order' => $this->beforeOrderShow()
        ];
    }

    //前置where条件
    protected function beforeWhereShow($where = []): ?array
    {
        return $where;
    }

    //前置order条件
    protected function beforeOrderShow($order = []): ?array
    {
        return $order;
    }

    //前置field条件
    protected function beforeFieldShow($field = []): ?array
    {
        return $field;
    }

    //查(个人详情)后置操作
    protected function afterShow($data) {
        //处理查询返回的数据
        return $data ? $data->toArray(): [];
    }

    //查(个人详情)前置操作
    protected function beforeRead($id, $where = []): ?array
    {
        $where = $this->beforeWhere($where);
        $where = $this->beforeWhereRead($where);
        $where = $this->beforeWhereUser($where);//默认关联用户id查询
        $where = $this->beforeWhereId($id,$where);
        return [
            'where' => $where,
            'field' => $this->beforeFieldRead(),
            'order' => $this->beforeOrderRead()
        ];
    }

    //前置where条件
    protected function beforeWhereRead($where = []): ?array
    {
        return $where;
    }

    //前置order条件
    protected function beforeOrderRead($order = []): ?array
    {
        return $order;
    }

    //前置field条件
    protected function beforeFieldRead($field = []): ?array
    {
        return $field;
    }

    //查(个人详情)后置操作
    protected function afterRead($data) {
        //处理查询返回的数据
        return $data ? $data->toArray(): [];
    }

    /** 关联额外的条件或操作 */
    use ControllerRelationTrait;
}