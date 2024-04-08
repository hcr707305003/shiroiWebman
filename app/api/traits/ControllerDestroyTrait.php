<?php

namespace app\api\traits;

use app\common\exception\HttpResponseException;
use hg\apidoc\annotation as Apidoc;
use ReflectionException;
use support\Response;

trait ControllerDestroyTrait
{
    /**
     * 删除
     * @Apidoc\RouteParam ("id",type="string",desc="ID",require=true)
     * @Apidoc\Method ("delete")
     */
    public function delete($id): Response
    {
        //前置操作（获取删除的条件参数）
        $data = $this->beforeDelete($id);
        //判断验证器类是否存在
        try {
            $this->loadValidate(compact('id'));
        } catch (ReflectionException|HttpResponseException $e) {}
        //后置操作
        return $this->afterDelete(
            static::$service::delete($data['where'])
        ) ? api_success([],'删除成功'): api_error('删除失败');
    }
    public function destroy($id): Response
    {
        //前置操作（获取删除的条件参数）
        $data = $this->beforeDelete($id);
        //判断验证器类是否存在
        try {
            $this->loadValidate(compact('id'));
        } catch (ReflectionException|HttpResponseException $e) {}
        //后置操作
        return $this->afterDelete(
            static::$service::delete($data['where'])
        ) ? api_success([],'删除成功'): api_error('删除失败');
    }

    protected function beforeWhereDelete($where = []): ?array
    {
        return $where;
    }

    //删（前置）
    protected function beforeDelete($id): ?array
    {
        $where = $this->beforeWhereDelete();
        $where = $this->beforeWhereUser($where);//默认关联用户id查询
        $where = $this->beforeWhereId($id,$where);
        return [
            'where' => $where
        ];
    }

    //删（后置）
    protected function afterDelete($data) {
        //处理查询返回的数据
        return $data;
    }

    /** 关联额外的条件或操作 */
    use ControllerRelationTrait;
}