<?php /** @noinspection DuplicatedCode */

namespace app\api\traits;

use app\common\exception\HttpResponseException;
use hg\apidoc\annotation as Apidoc;
use ReflectionException;
use support\Response;

trait ControllerUpdateTrait
{
    /**
     * 根据id修改
     * @Apidoc\Method ("put")
     * @Apidoc\RouteParam ("id",type="string",desc="ID",require=true)
     */
    public function update($id): Response
    {
        $id = $id ?: $this->getId();
        //前置操作（获取要保存的参数）
        $data = $this->beforeUpdate($id);
        //判断验证器类是否存在
        try {
            $this->loadValidate($data['update']);
        } catch (ReflectionException|HttpResponseException $e) {}
        //后置操作(对接收的参数进行处理)
        $this->afterUpdate(static::$service::update($data['where'],$data['update']),$id);
        //返回结果
        return api_success();
    }

    protected function beforeWhereUpdate($where = []): ?array
    {
        return $where;
    }

    protected function beforeUpdateOtherData($update = []): ?array
    {
        return $update;
    }

    protected function beforeUpdateData(): ?array
    {
        return request()->post();
    }

    protected function beforeUpdateTime($update): ?array
    {
        return $update;
    }

    //改（前置）
    protected function beforeUpdate($id): ?array
    {
        $where = $this->beforeWhereUpdate();
        $where = $this->beforeWhereUser($where);//默认关联用户id查询
        $where = $this->beforeWhereId($id,$where);
        $update = $this->beforeUpdateData();
        $update = $this->beforeUpdateOtherData($update);
        $update = $this->beforeUpdateTime($update);
        return [
            'where' => $where,
            'update' => $update
        ];
    }

    /** 关联额外的条件或操作 */
    use ControllerRelationTrait;
}