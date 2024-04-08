<?php

namespace app\admin\service;

use app\admin\model\Test;
use app\common\traits\ServiceTrait;

class TestService extends AdminBaseService
{
    /** @var string|Test $model 模型 */
    public static string $model = 'app\admin\model\Test';

    use ServiceTrait;
}