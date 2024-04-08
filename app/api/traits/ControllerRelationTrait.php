<?php

namespace app\api\traits;

use app\common\exception\HttpResponseException;
use ReflectionClass;
use ReflectionException;
use think\Validate;

trait ControllerRelationTrait
{
    /**
     * 公共验证器
     * @param $data (要验证的数据)
     * @return void
     * @throws ReflectionException
     * @throws HttpResponseException
     */
    protected function loadValidate($data, $method = '')
    {
        if(property_exists(static::class,'validate')) {
            /** @var Validate $validate 验证器 */
            $validate = new static::$validate;
            //场景方法
            $method = $method ?: $this->getAction() . $this->getController(true);
            //获取属性
            $property = (new ReflectionClass(static::$validate))->getProperty('scene');
            // 设置私有属性为可访问
            $property->setAccessible(true);
            //获取所有场景
            $sceneList = $property->getValue($validate);
            if(isset($sceneList[$method])) {
//                $check = \validate(static::$validate)->scene($method)->check($data);
                $check = $validate->scene($method)->check($data);
                if (!$check) {
                    throw new HttpResponseException(api_error(strval($validate->getError())));
                }
            }
        }
    }

    /**
     * 前置where条件
     * @param $where
     * @return array|null
     */
    protected function beforeWhere($where): ?array
    {
        return $where;
    }

    /**
     * 前置关联id
     * @param $id
     * @param $where
     * @return array|null
     */
    protected function beforeWhereId($id,$where): ?array
    {
        $where[] = ['id', '=', $id];
        return $where;
    }

    /**
     * 前置默认where用户id(涉及业务相关，某些业务不需要关联用户id查询)
     * @param array|mixed $where
     * @return array|null
     */
    protected function beforeWhereUser($where = []): ?array
    {
        if($this->is_relation_user_id) {
            $where[] = ['user_id' , '=', $this->uid];
        }
        return $where;
    }
}