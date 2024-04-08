<?php

namespace app\api\controller;

use app\api\traits\ApiThrottleTrait;
use app\api\traits\GetSetTrait;
use app\BaseController;
use app\common\exception\HttpResponseException;
use app\common\model\User;
use app\common\traits\MiddlewareTrait;
use app\common\traits\SystemMaintenanceTrait;
use support\Response;
use think\Model;

class ApiBaseController extends BaseController
{
    use GetSetTrait,
        ApiThrottleTrait,
        MiddlewareTrait,
        SystemMaintenanceTrait;

    /** ==================用户相关参数===================== */
    /** @var int|string $uid 当前访问的用户id */
    protected $uid = 0;

    /**
     * @var mixed|Model|User $user 当前访问的用户
     * @noinspection PhpMultipleClassDeclarationsInspection
     */
    protected $user = null;

    /** @var array 无需验证登录的url，禁止在此处修改 */
    protected array $loginExcept = [];
    /** ==================用户相关参数===================== */


    /** @var int|string $is_visitor 是否游客登录（0=>否 1=>是） */
    protected $is_visitor = 0;

    /** @var bool $is_relation_user_id 是否关联用户（0=>否 1=>是）  */
    protected bool $is_relation_user_id = true;

    /** @var array $show_doc 显示的文档方法(选取哪些文档显示在apidoc上面) */
    protected array $show_doc = [];

    /**
     * @throws HttpResponseException
     */
    public function __construct()
    {
        parent::__construct();
        //验证系统维护
        $this->checkSystem();
        //重复提交操作
        $this->checkThrottle();
        //加载用户信息
        foreach (['uid', 'user'] as $property) {
            $this->$property = request()->$property;
        }
        foreach ($this->loginExcept as &$uri) {
            $uri = ltrim(parsePath($uri), '/');
        }
        //设置url地址
        $this->url = $this->parse_url($this->url);
    }

    /**
     * 访问不存在的方法
     * @param string $method
     * @param array $params
     * @return Response
     */
    public function __call(string $method, array $params): Response
    {
        return $this->error_404();
    }
}