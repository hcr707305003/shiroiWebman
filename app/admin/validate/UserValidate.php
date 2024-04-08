<?php

namespace app\admin\validate;

class UserValidate extends AdminBaseValidate
{
    protected $rule = [
        'group_id|用户等级'     => 'require',
        'username|账号'        => 'require',
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
        'password.require'      => '密码不能为空',
        'mobile.require'        => '手机号不能为空',
        'nickname.require'      => '昵称不能为空',
        'avatar.require'        => '头像不能为空',
        'status.require'        => '是否启用不能为空',

    ];

    protected $scene = [
        'admin_add'     => ['group_id', 'username', 'password', 'mobile', 'nickname', 'avatar', 'status',],
        'admin_edit'    => ['id', 'group_id', 'username', 'mobile', 'nickname', 'avatar', 'status',],
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
}