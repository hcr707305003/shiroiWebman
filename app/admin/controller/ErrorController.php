<?php

declare (strict_types=1);

namespace app\admin\controller;

use support\Response;

/**
 * 错误控制器
 * @author shiroi <707305003@qq.com>
 */
class ErrorController extends AdminBaseController
{
    /**
     * 403 没有权限访问
     * @return Response
     */
    public function err403(): Response
    {
        return $this->fetch('error/403');
    }
}
