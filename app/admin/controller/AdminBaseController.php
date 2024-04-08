<?php

namespace app\admin\controller;

use app\admin\model\AdminMenu;
use app\admin\model\AdminUser;
use app\admin\traits\AdminAuthTrait;
use app\admin\traits\AdminFieldGenerateTrait;
use app\admin\traits\AdminSettingForm;
use app\admin\traits\AdminTreeTrait;
use app\BaseController;
use app\common\exception\HttpResponseException;
use Psr\SimpleCache\InvalidArgumentException;
use support\Response;
use support\View;
use think\helper\Str;

/**
 * 基础控制器
 * @author shiroi <707305003@qq.com>
 */
class AdminBaseController extends BaseController
{
    // 引入权限判断相关trait
    use AdminAuthTrait;

    // 引入树相关trait
    use AdminTreeTrait;

    // 引入字段相关trait
    use AdminFieldGenerateTrait;

    // 引入生成器
    use AdminSettingForm;

    /**
     * 后台主变量
     * @var array
     */
    protected array $admin = [];

    /**
     * 当前访问的菜单
     * @var mixed
     */
    protected $menu;

    /**
     * 无需验证登录的url，禁止在此处修改
     * @var array
     */
    protected array $loginExcept = [

    ];

    /**
     * 无需验证权限的URL
     * @var array
     */
    protected array $authExcept = [
        'admin/error/err403',
        'admin/error/err404',
        'admin/error/err500',
    ];

    /**
     * 当前后台用户
     * @var AdminUser
     */
    protected AdminUser $user;

    /**
     * 构造函数
     * @throws HttpResponseException
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        parent::__construct();
        // 初始化
        $this->initialize();
    }

    /**
     * 初始化方法
     * @throws HttpResponseException
     * @throws InvalidArgumentException
     */
    public function initialize(): void
    {
        //初始化后台用户
        $this->user = new AdminUser();
        // 检查登录
        $this->checkLogin();
        // 检查权限
        $this->checkAuth();
        // 单设备登录检查
        $this->checkOneDeviceLogin();
        // 后台列表展示的字段和值
        $this->convertShowField();
        // 后台列表input展示
        $this->convertShowInput();
        // 分页每页数量
        $this->admin['admin_list_rows'] = request()->cookie('admin_list_rows') ?: 10;
        // 限制每页数量最多不超过100
        $this->admin['admin_list_rows'] = min($this->admin['admin_list_rows'], 100);
        /** @var AdminMenu $menu */
        $this->menu = (new AdminMenu)->where(['url' => $this->url])->findOrEmpty();
        // 创建log
        if (isset($this->user) && $this->menu->isExists() && request()->method() === $this->menu->log_method) {
            // 如果用户登录了而且符合菜单记录日志方式，记录操作日志
            $this->createLog($this->user, $this->menu->name);
        }
    }

    /**
     * 模板赋值
     * @param $name
     * @param $value
     * @return void
     */
    protected function assign($name, $value = null)
    {
        View::assign($name, $value);
    }

    /**
     * 自动获取view模板
     */
    protected function fetch(string $template = '', array $vars = [], $app = null): Response
    {
        $request = explode('/',
            small_mount_to_underline(remove_both_str(request()->getController(), 'Controller', 2))
        );

        $parseArr = array_map(function ($item) {
            return Str::snake($item);
        }, $request);
        if (empty($template)) {
            $template = implode('/', $parseArr) . '/' . Str::snake(request()->getAction());
        } elseif (count($templateArr = explode('/', $template)) === 1) {
            $template = implode('/', $parseArr) . '/' . $templateArr[0];
        }

        // 顶部导航
        $this->admin['top_nav'] = (int)setting('admin.display.top_nav');
        // 后台基本信息配置
        $this->admin['base'] = setting('admin.base');
        // 当前顶部导航ID
        $current_top_id = 0;

        if ($this->menu->isExists()) {
            $menu = $this->menu;
            $menu_all = (new AdminMenu)->field('id,parent_id,name,icon')->select()->toArray();
            // 当前页面标题
            $this->admin['title'] = $menu->name;
            // 当前面包屑
            $this->admin['breadcrumb'] = $this->getBreadCrumb($menu_all, $menu->id);
            if ($this->admin['top_nav'] === 1) {
                // 顶部导航id
                $current_top_id = $this->getTopParentIdById($menu_all, $menu->id);
            }
        }
        // 当前是否为pjax访问
        $this->admin['is_pjax'] = request()->isPjax();
        // 上传文件url
        $this->admin['upload_url'] = url('admin/file/upload');
        // 退出url
        $this->admin['logout_url'] = url('admin/auth/logout');

        if ('admin/auth/login' !== $this->url && !$this->admin['is_pjax']) {
            // 展示菜单
            $show_menu = $this->user->getShowMenu($this->admin['top_nav']);
            // 顶部导航
            $this->admin['top_menu'] = $show_menu['top'];
            // 左侧菜单
            $this->admin['left_menu'] = $this->getLeftMenu($show_menu['left'][$current_top_id], $menu->id ?? 0);
        }
        // 是否开启debug
        $this->admin['debug'] = server_config('debug', false);
        // 顶部导航
        $this->admin['top_nav'] = 1;
        // 顶部搜索
        $this->admin['top_search'] = 0;
        // 顶部消息
        $this->admin['top_message'] = 0;
        // 顶部通知
        $this->admin['top_notification'] = 0;
        // 文件删除url
        $this->admin['file_del_url'] = url('admin/file/del');
        // 地图配置
        $this->admin['map'] = config('map');
        // 当前用户
        $this->admin['user'] = $this->user ?? new AdminUser();

        // 赋值后台变量
        $this->assign([
            'admin' => $this->admin,
        ]);

        return view($template, $vars, $app);
    }

    /**
     * URL重定向
     * @access protected
     * @param string $url 跳转的URL表达式
     * @param integer $code http code
     * @param array $headers
     * @return Response
     */
    protected function redirect(string $url, int $code = 302, array $headers = []): Response
    {
        return redirect($url, $code, $headers);
    }

    /**
     * 访问不存在的方法
     * @param $name
     * @param $arguments
     * @return Response
     */
    public function __call($name, $arguments)
    {
        if (request()->isPost()) {
            return admin_error('页面未找到');
        }
        return $this->fetch('/admin/error/404');
    }
}
