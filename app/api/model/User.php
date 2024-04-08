<?php

namespace app\api\model;

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
class User extends ApiBaseModel
{
    use SoftDelete;

    protected $json = ['setting'];

    /**
     * 插入前
     * @param mixed|User $model
     * @return void
     */
    public static function onBeforeInsert($model): void
    {
        \app\common\model\User::onBeforeInsert($model);
    }

    /**
     * 更新前
     * @param $model
     */
    public static function onBeforeUpdate($model): void
    {
        \app\common\model\User::onBeforeUpdate($model);
    }

    /**
     * 插入后
     * @param mixed|User $model
     * @return void
     */
    public static function onAfterUpdate($model): void
    {
        \app\common\model\User::onAfterUpdate($model);
    }

    /**
     * 获取头像
     * @param $value
     * @param mixed $data
     * @return mixed|string
     */
    public function getAvatarAttr($value, $data = [])
    {
        return (new \app\common\model\User())->getAvatarAttr($value,$data);
    }
}