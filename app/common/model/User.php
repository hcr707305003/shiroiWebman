<?php

namespace app\common\model;

use think\model\concern\SoftDelete;

/**
 * @property int $group_id 组id
 * @property string $app_id 用户app_id | 平台id
 * @property string $app_secret 用户app_secret
 * @property string $username 用户名
 * @property string $password 密码
 * @property string $password_salt 密码盐
 * @property string $email 邮箱地址
 * @property string $mobile 手机号
 * @property string $nickname 昵称
 * @property string $avatar 头像
 * @property string $qq QQ
 * @property string $wechat 微信号
 * @property int $sex 性别 0未知，1男，2女
 * @property int $score 积分
 * @property int $money 钱包（单位：元）
 * @property string $address 地址
 * @property int $invite_id 邀请人
 * @property int $status 是否启用 0=>否 1=>是
 */
class User extends CommonBaseModel
{
    use SoftDelete;

    protected $json = ['setting'];

    protected static $oldModel = null;

    /**
     * 插入前进行密码加密
     * @param mixed|User $model
     * @return void
     */
    public static function onBeforeInsert($model): void
    {
        if($model['password']) {
            //设置密码
            $model->password = (new self)->setEncryptPassword($model->password);
            //设置密码盐
            $model->password_salt = PASSWORD_ARGON2I;
        }

        //设置默认唯一昵称
        $default_app_id = rand_str(8, 4);

        //设置默认昵称
        $model->nickname = $model['nickname'] ?? '用户_' . $default_app_id;

        // 生成唯一id
        $model->app_id = $model['app_id'] ?? $default_app_id;

        //用户名
        $model->username = $model['username'] ?? $model->app_id;
    }

    /**
     * 更新前
     * @param mixed|User $model
     */
    public static function onBeforeUpdate($model): void
    {
        self::$oldModel = $old = (new self())->where('id', '=', $model->id)->findOrEmpty();
        /**@var User $old */
        if ($model->password && ($model->password !== $old->password)) {
            $model->password = (new self)->setEncryptPassword($model->password);
        }
    }

    /**
     * 删除后置操作
     * @param $model
     * @return void
     */
    public static function onAfterDelete($model): void
    {
        event('user.delete', $model);
    }

    /**
     * 设置加密密码
     * @param $password
     * @return string
     */
    protected function setEncryptPassword($password): string
    {
        return encrypt_password($password);
    }

    public static function onAfterInsert($model): void
    {
        event('user.register', $model);
    }

    /**
     * User后置行为
     * @param $model
     * @return void
     */
    public static function onAfterUpdate($model): void
    {
        if(($model['status'] !== null) && (self::$oldModel['status'] !== $model['status'])) {
            event('user.' . ($model['status'] ? 'enable': 'disable'), $model);
        }
        event('user.update', $model);
    }

    /**
     * 获取头像
     * @param $value
     * @param mixed $data
     * @return mixed|string
     */
    public function getAvatarAttr($value, $data = [])
    {
        if($value && !l_array_exists(['http://', 'https://', '//'], $value)) {
            $value = (request() ?request()->domain(): server_config('domain')).$value;
        }
        return $value;
    }
}
