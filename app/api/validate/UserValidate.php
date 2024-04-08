<?php

namespace app\api\validate;

class UserValidate extends ApiBaseValidate
{
    protected $rule = [
        'username|账号'            => 'require',
        'password|密码'            => 'require',
        'repassword|再次密码'       => 'require|isSamePassword',
        'mobile|手机号'            => 'require|mobile',
        'code|验证码'              => 'require|checkCode:mobile'
    ];

    protected $message = [
        'username.require'         => '账号不能为空',
        'password.require'         => '密码不能为空',
        'repassword.require'       => '再次密码不能为空',
        'repassword.isSamePassword'=> '两次密码不一致',
        'mobile.require'           => '手机号不能为空',
        'mobile.mobile'            => '无效的手机号',
        'code.require'             => '验证码不能为空',
        'code.checkCode'           => '验证码输入错误'
    ];

    protected $scene = [
        'userLogin'             => ['username', 'password'],
        'userRegister'          => ['username', 'password', 'repassword'],
        'phoneLogin'            => ['mobile', 'code']
    ];

    public function isSamePassword($value,$bindValue,$data): bool
    {
        if($data['password'] != $data['repassword']) {
            return false;
        }
        return true;
    }
}