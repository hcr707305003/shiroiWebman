<?php /** @noinspection DuplicatedCode */

namespace app\api\traits;

use app\common\exception\HttpResponseException;
use hg\apidoc\annotation as Apidoc;
use ReflectionException;
use support\Response;

trait ControllerEditTrait
{
    /**
     * 修改
     * @Apidoc\Method("put")
     * @return Response
     */
    public function edit(): Response
    {
        //前置操作(要保存的参数)
        $data = $this->beforeEdit();
        //判断验证器类是否存在
        try {
            $this->loadValidate($data['update']);
        } catch (ReflectionException|HttpResponseException $e) {
        }
        //后置操作(对接收的参数进行处理)
        $this->afterEdit(static::$service::update($data['where'],$data['update']));
        //返回结果
        return api_success();
    }

    protected function beforeEdit($where = []): ?array
    {
        $where = $this->beforeWhereEdit($where);
        $where = $this->beforeWhereUser($where);//默认关联用户id查询
        $update = $this->beforeEditData();
        $update = $this->beforeEditOtherData($update);
        $update = $this->beforeEditTime($update);
        return [
            'where' => $where,
            'update' => $update
        ];
    }

    //改（后置）
    protected function afterUpdate($data, $id) {
        //处理查询返回的数据
        return $data;
    }

    protected function beforeWhereEdit($where = []): ?array
    {
        return $where;
    }

    protected function beforeEditOtherData($update = []): ?array
    {
        return $update;
    }

    protected function beforeEditData(): ?array
    {
        return request()->post();
    }

    protected function beforeEditTime($update): ?array
    {
        return $update;
    }

    //改（后置）
    protected function afterEdit($data) {
        //处理查询返回的数据
        return $data;
    }

    /** 关联额外的条件或操作 */
    use ControllerRelationTrait;
}