<?php

namespace app\admin\controller;

use app\admin\service\TestService;
use app\admin\traits\ControllerTrait;

/**
 * 测试控制器
 * @author shiroi <707305003@qq.com>
 */
class TestController extends AdminBaseController
{
    use ControllerTrait;

    protected array $show_index_input = [
        'username' => "用户名"
    ];

    protected array $show_base_field = [
        'username' => '用户名',
        'mobile' => '手机号',
        'status' => '状态'
    ];

    protected array $show_index_field = [
        'id' => 'ID',
    ];

    protected array $show_type = [
        'status' => 'switch'
    ];

    protected array $show_index_field_conditions = [
        'status' => [
            '0' => "<a style='color: #ff0051;'>关闭</a>",
            '1' => "<a style='color: #1aff00;'>开启</a>",
        ]
    ];

    /** @var string|TestService $service 服务层 */
    protected static string $service = 'app\admin\service\TestService';
}