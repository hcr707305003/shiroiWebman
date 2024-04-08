<?php

namespace app\api\validate;

class TokenValidate extends ApiBaseValidate
{
    protected $rule = [
        'refresh_token|刷新token'   => 'require',
    ];

    protected $message = [
        'refresh_token.require'     => 'refresh_token不能为空',
    ];

    protected $scene = [
        'refresh'                   => ['refresh_token'],
    ];
}