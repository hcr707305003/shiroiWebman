<?php

namespace app\api\validate;

class UserSettingValidate extends ApiBaseValidate
{
    protected $rule = [
        'code|代码'               => 'require',
        'content|内容'            => 'require'
    ];

    protected $message = [
        'code.require'           => '代码不能为空',
        'content.require'        => '内容不能为空'
    ];

    protected $scene = [
        'infoUserSetting'        => ['code'],
        'storeUserSetting'       => ['code', 'content'],
    ];
}