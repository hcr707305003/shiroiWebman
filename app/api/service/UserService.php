<?php

namespace app\api\service;

use app\api\model\User;
use app\common\traits\ServiceTrait;

class UserService extends ApiBaseService
{
    use ServiceTrait;

    /** @var string|User $model 用户模型层  */
    public static string $model = 'app\admin\model\User';
}