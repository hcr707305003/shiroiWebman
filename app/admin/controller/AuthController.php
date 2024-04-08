<?php

namespace app\admin\controller;


use app\admin\service\AuthService;
use app\admin\validate\AdminUserValidate;
use app\common\exception\HttpResponseException;
use Exception;
use Psr\SimpleCache\InvalidArgumentException;
use support\Request;
use support\Response;
use Webman\Captcha\CaptchaBuilder;

/**
 * 登录验证控制器
 * @author shiroi <707305003@qq.com>
 */
class AuthController extends AdminBaseController
{
    // 无需登录的url
    protected array $loginExcept = [
        'admin/auth/login',
        'admin/auth/captcha',
        'admin/auth/geetest',
        'admin/auth/token',
    ];

    // 无需判断权限的url
    protected array $authExcept = [
        'admin/auth/logout',
    ];

    /**
     * 登录
     * @param Request $request
     * @param AuthService $service
     * @param AdminUserValidate $validate
     * @return Response
     */
    public function login(Request $request, AuthService $service, AdminUserValidate $validate): Response
    {
        $redirect = $request->all()['redirect'] ?? url('admin/index/index');

        $login_config = setting('admin.login');

        if ($request->isPost()) {
            $param = $request->post();
            try {
                // 验证码形式，0为不验证，1为图形验证码，2为极验
                $captcha = (int)$login_config['captcha'];

                if(($captcha === 1)) {
                    if (strtolower($request->post('captcha')) !== $request->session()->get('captcha')) {
                        return admin_error('验证码错误');
                    }
                }

                $validate->scene('login')->failException(true)->check($param);
                // 检查单设备登录
                $service->checkLoginLimit();

                $username = $param['username'];
                $password = $param['password'];
                $remember = $param['remember'] ?? 0;
                $redirect = $param['redirect'] ?? url('admin/index/index');

                $admin_user = $service->login($username, $password);
                $service->setAdminUserAuthInfo($admin_user, (bool)$remember);
                // 设置当前登录设备标识
                $this->setLoginDeviceId($admin_user);
            } catch (HttpResponseException|InvalidArgumentException $e) {
                return admin_error($e->getMessage());
            }
            return admin_success('登录成功', [], $redirect);
        }

        $this->assign([
            'redirect'     => $redirect,
            'login_config' => $login_config,
            'geetest_id'   => setting('config.geetest.geetest_id', ''),
        ]);

        return $this->fetch();
    }

    /**
     * 退出
     * @param AuthService $service
     * @return Response
     */
    public function logout(AuthService $service): Response
    {
        $result = $service->logout($this->user);
        $data = [
            'redirect' => url('admin/auth/login'),
        ];

        return $result ? admin_success('退出成功', $data) : admin_error();
    }

    /**
     * 输出验证码图像
     * @throws HttpResponseException
     */
    public function captcha(Request $request): Response
    {
        // 初始化验证码类
        try {
            $builder = new CaptchaBuilder;
            // 生成验证码
            $builder->build();
            // 将验证码的值存储到session中
            $request->session()->set('captcha', strtolower($builder->getPhrase()));
            // 获得验证码图片二进制数据
            $img_content = $builder->get();
            // 输出验证码二进制数据
            return response($img_content, 200, ['Content-Type' => 'image/jpeg']);
        } catch (Exception $exception) {
            throw new HttpResponseException(admin_error($exception->getMessage()));
        }

    }
}