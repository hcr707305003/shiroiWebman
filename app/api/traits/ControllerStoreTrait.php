<?php /** @noinspection DuplicatedCode */

namespace app\api\traits;

use app\common\exception\HttpResponseException;
use hg\apidoc\annotation as Apidoc;
use ReflectionException;
use support\Response;

trait ControllerStoreTrait
{
    /**
     * 新增
     * @Apidoc\Method ("post")
     */
    public function save(): Response
    {
        //前置操作（获取要保存的参数）
        $data = $this->beforeSave();
        //判断验证器类是否存在
        try {
            $this->loadValidate($data);
        } catch (ReflectionException|HttpResponseException $e) {}
        //后置操作(对接收的参数进行处理)
        return api_success(
            $this->afterSave(
                static::$service::create($data)
            ));
    }
    public function store(): Response
    {
        //前置操作（获取要保存的参数）
        $data = $this->beforeStore();
        //判断验证器类是否存在
        try {
            $this->loadValidate($data);
        } catch (ReflectionException|HttpResponseException $e) {}
        //后置操作(对接收的参数进行处理)
        return api_success(
            $this->afterStore(
                static::$service::create($data)
            ));
    }

    protected function beforeStoreData($data = []): ?array
    {
        return array_merge($data,request()->post());
    }

    protected function beforeStoreOtherData($data = []): ?array
    {
        return $data;
    }

    /**
     * 前置默认Store用户id(涉及业务相关，某些业务不需要关联用户id查询)
     * @param array $data
     * @return array|null
     */
    protected function beforeStoreUser(array $data = []): ?array
    {
        $data['user_id'] = $this->uid;
        return $data;
    }

    //增（前置）
    protected function beforeStore(): ?array
    {
        //默认接受所有参数
        $data = $this->beforeStoreData();
        //保存其他的参数
        $data = $this->beforeStoreOtherData($data);
        //默认保存当前用户id
        return $this->beforeStoreUser($data);
    }

    //增（后置）
    protected function afterStore($data) {
        //处理查询返回的数据
        return is_object($data)? $data->toArray(): $data;
    }

    //增（前置）
    protected function beforeSave(): ?array
    {
        //默认接受所有参数
        $data = $this->beforeSaveData();
        //保存其他的参数
        $data = $this->beforeSaveOtherData($data);
        //默认保存当前用户id
        return $this->beforeSaveUser($data);
    }

    protected function beforeSaveData($data = []): ?array
    {
        return array_merge($data,request()->post());
    }

    protected function beforeSaveOtherData($data = []): ?array
    {
        return $data;
    }

    /**
     * 前置默认Store用户id(涉及业务相关，某些业务不需要关联用户id查询)
     * @param mixed $data
     * @return array|null
     */
    protected function beforeSaveUser($data = []): ?array
    {
        $data['user_id'] = $this->uid;
        return $data;
    }

    //增（后置）
    protected function afterSave($data) {
        //处理查询返回的数据
        return is_object($data)? $data->toArray(): $data;
    }

    /** 关联额外的条件或操作 */
    use ControllerRelationTrait;
}