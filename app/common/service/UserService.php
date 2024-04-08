<?php

namespace app\common\service;

use app\common\model\User;
use app\common\traits\ServiceTrait;

class UserService extends CommonBaseService
{
    use ServiceTrait;

    /** @var string|User $model 用户模型层 */
    public static string $model = 'app\common\model\User';
}