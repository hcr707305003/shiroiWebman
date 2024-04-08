<?php

namespace app\admin\service;

use app\admin\model\User;
use app\common\traits\ServiceTrait;

class UserService extends AdminBaseService
{
    use ServiceTrait;

    /** @var string|User $model 用户模型层 */
    public static string $model = 'app\admin\model\User';
}