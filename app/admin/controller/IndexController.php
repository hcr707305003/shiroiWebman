<?php

namespace app\admin\controller;

use support\Response;

/**
 * 首页
 * @author shiroi <707305003@qq.com>
 */
class IndexController extends AdminBaseController
{
    public function index(): Response
    {
        return $this->fetch();
    }
}
