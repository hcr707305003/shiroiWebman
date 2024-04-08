<?php
/**
 * 验证登录、权限的trait
 */

namespace app\admin\traits;

use app\admin\model\AdminUser;
use app\admin\service\AdminLogService;
use app\common\exception\HttpResponseException;
use Psr\SimpleCache\InvalidArgumentException;
use support\Response;
use think\facade\Cache;
use app\admin\service\AuthService;

trait AdminAuthTrait
{
    protected string $loginDeviceKey = 'admin_user_current_login_device_id_';

    /**
     * 检查登录
     * @throws HttpResponseException
     */
    protected function checkLogin(): bool
    {
        $login_except = $this->loginExcept ?: [];

        if (in_array($this->path, $login_except, true)) {
            return true;
        }

        // 验证登录
        try {
            $this->user = (new AuthService)->getAdminUserAuthInfo();
        } catch (HttpResponseException $e) {
            throw new HttpResponseException($this->redirect(url('admin/auth/login')));
        }
        return true;
    }

    /**
     * 检查权限
     * @throws HttpResponseException
     */
    public function checkAuth(): bool
    {
        $request = request();

        $login_except = $this->loginExcept ?: [];
        // 如果在无需登录的URL里，直接返回
        if (in_array($this->path, $login_except, true)) {
            return true;
        }

        $auth_except = $this->authExcept ?: [];
        // 如果在无需授权的URL里，直接返回
        if (in_array($this->path, $auth_except, true)) {
            return true;
        }

        // 验证权限
        if ($this->user->id !== 1 && !$this->checkPermission($this->user, $this->path)) {
            throw new HttpResponseException($request->isGet() ? $this->redirect('/admin/error/err403') : admin_error('无权限'));
        }

        return true;
    }

    /**
     * 权限检查
     * @param AdminUser $user
     * @param string $url
     * @return bool
     */
    public function checkPermission(AdminUser $user, string $url): bool
    {
        $auth_except = $this->authExcept ?: [];
        return in_array($url, $auth_except, true) || in_array($url, $user->auth_url, true);
    }

    /**
     * 单设备登录检查
     * @return bool
     * @throws InvalidArgumentException|HttpResponseException
     */
    public function checkOneDeviceLogin(): bool
    {
        $request = request();
        $check   = setting('admin.safe.one_device_login');
        if (!$check) {
            return true;
        }

        $login_except = $this->loginExcept ?: [];

        if (in_array($this->url, $login_except, true)) {
            return true;
        }

        $device_id = (new AuthService)->getDeviceId($this->user);
        $login_device_id = $this->getLoginDeviceId($this->user);

        if ($login_device_id && $login_device_id !== $device_id) {
            $login_url = url('admin/auth/login');
            if ($request->isGet()) {
                throw new HttpResponseException(redirect($login_url));
            }
            throw new HttpResponseException(admin_error('未登录', [
                ['url' => $login_url]
            ], 401));
        }

        return $this->setLoginDeviceId($this->user);
    }

    /**
     * 获取当前登录的设备ID
     * @param $user
     * @return string
     * @throws InvalidArgumentException
     */
    public function getLoginDeviceId($user): string
    {
        $cache_key = $this->loginDeviceKey . $user->id;

        return (string)Cache::get($cache_key);
    }

    /**
     * 设置登录的设备ID
     * @param $user
     * @return bool
     * @throws InvalidArgumentException
     */
    public function setLoginDeviceId($user): bool
    {
        $service   = new AuthService();
        $device_id = $service->getDeviceId($user);
        $cache_key = $this->loginDeviceKey . $user->id;
        return Cache::set($cache_key, $device_id);
    }

    /**
     * 清除当前登录的设备ID
     * @param $user
     * @return bool
     * @throws InvalidArgumentException
     */
    public function clearLoginDeviceId($user): bool
    {
        $cache_key = $this->loginDeviceKey . $user->id;
        return Cache::delete($cache_key);
    }

    /**
     * 创建日志
     * @param $user
     * @param $name
     * @return void
     */
    public function createLog($user, $name)
    {
        (new AdminLogService())->create($user, $name);
    }
}
