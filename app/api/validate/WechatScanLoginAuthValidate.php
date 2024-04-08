<?php

namespace app\api\validate;

class WechatScanLoginAuthValidate extends ApiBaseValidate
{
    protected $rule = [
        'scene|场景'                 => 'require|isExists:wechat_scan_login_auth:scene',
    ];

    protected $message = [
        'scene.require'            => 'scene不能为空',
        'scene.isExists'            => 'scene不存在',
    ];

    protected $scene = [
        'qrcode'                    => ['scene'],
        'check_scan_status'         => ['scene']
    ];
}