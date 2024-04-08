<?php

namespace app\admin\validate;

use app\admin\model\User;

class UserValidate extends AdminBaseValidate
{
    protected $rule = [
        'group_id|用户等级'     => 'require',
        'username|账号'        => 'require|isExists:user:username',
        'password|密码'        => 'require',
        'repassword|再次密码'   => 'require|isSamePassword',
        'mobile|手机号'         => 'require',
        'nickname|昵称'        => 'require',
        'avatar|头像'          => 'require',
        'status|是否启用'       => 'require',

    ];

    protected $message = [
        'group_id.require'      => '用户组不能为空',
        'username.require'      => '账号不能为空',
        'username.isExists'     => '账号已被使用',
        'password.require'      => '密码不能为空',
        'mobile.require'        => '手机号不能为空',
        'nickname.require'      => '昵称不能为空',
        'avatar.require'        => '头像不能为空',
        'status.require'        => '是否启用不能为空',

    ];

    protected $scene = [
        'admin_add'     => ['group_id', 'username', 'password', 'mobile', 'nickname', 'status',],
        'admin_edit'    => ['id', 'group_id', 'username', 'mobile', 'nickname', 'status',],
        'admin_del'     => ['id',],
        'admin_disable' => ['id',],
        'admin_enable'  => ['id',],
    ];

    public function isSamePassword($value,$value1,$data): bool
    {
        if($data['password'] != $data['repassword']) {
            return false;
        }
        return true;
    }

    /**
     * 验证规则定义 - 是否存在
     * @param $value
     * @param $field //isExists:数据库表:字段
     * @param $data
     * @return bool
     */
    public function isExists($value, $field, $data): bool
    {
        $fieldArr = explode(':', $field);
        $where = [
            ['delete_time', '=', 0],
            [$fieldArr[1], '=', $value],
        ];
        if(isset($data['id'])){
            $where[] = ['id', '<>', $data['id']];
        }
        $info = (new User())->where($where)->findOrEmpty();
        return $info->isEmpty();
    }
}