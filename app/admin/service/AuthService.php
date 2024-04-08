<?php

namespace app\admin\service;

use app\admin\model\AdminUser;
use app\common\exception\HttpResponseException;
use app\common\service\StringService;
use Exception;
use support\Cache;

class AuthService extends AdminBaseService
{
    protected AdminUser $model;

    protected string $limitKeyPrefix = 'admin_login_count_';

    /**
     * @var string 保存登录用户信息cookie和session的[ID]key值
     */
    protected $store_uid_key = 'admin_user_id';
    /**
     * @var string 保存登录用户信息cookie和session的[签名]key值
     */
    protected $store_sign_key = 'admin_user_sign';
    /**
     * @var mixed|string 用来签名加密/解密的key
     */
    protected $admin_key = '_ThisClassDefaultKey_';

    public function __construct()
    {
        $this->model          = new AdminUser();
        $config               = setting('admin.safe', []);
        $this->admin_key      = $config['admin_key'] ?? $this->admin_key;
        $this->store_uid_key  = $config['store_uid_key'] ?? $this->store_uid_key;
        $this->store_sign_key = $config['store_sign_key'] ?? $this->store_sign_key;
    }

    /**
     * 用户登录
     * @param $username
     * @param $password
     * @return AdminUser
     * @throws HttpResponseException
     */
    public function login($username, $password): AdminUser
    {
        $admin_user = $this->model->where('username', '=', $username)->findOrEmpty();
        if ($admin_user->isEmpty()) {
            throw new HttpResponseException(response_error('用户不存在'));
        }

        /** @var AdminUser $admin_user */
        $verify = password_verify($password, base64_decode($admin_user->password));
        if (!$verify) {
            throw new HttpResponseException(response_error('密码错误'));
        }

        // 检查是否被冻结
        if ($admin_user->status !== 1) {
            throw new HttpResponseException(response_error('账号被冻结'));
        }

        return $admin_user;
    }

    /**
     * 检测登录限制
     * @throws HttpResponseException
     */
    public function checkLoginLimit(): bool
    {
        $setting              = setting('admin.login');
        $is_limit             = (int)$setting['login_limit'];
        if ($is_limit) {
            // 最大错误次数
            $max_count        = (int)$setting['login_max_count'];
            $login_limit_hour = (int)$setting['login_limit_hour'];
            // 缓存key
            $cache_key        = $this->limitKeyPrefix . md5(request()->getRealIp());
            $have_count       = (int)Cache::get($cache_key);
            if ($have_count >= $max_count) {
                throw new HttpResponseException(response_error('连续' . $max_count . '次登录失败，请' . $login_limit_hour . '小时后再试'));
            }
            return true;
        }
        return true;
    }

    /**
     * 设置登录限制
     * @return bool
     */
    public function setLoginLimit(): bool
    {
        $setting              = setting('admin.login');
        $is_limit             = (int)$setting['login_limit'];
        if ($is_limit) {
            // 最大错误次数
            $login_limit_hour = (int)$setting['login_limit_hour'];
            // 缓存key
            $cache_key        = $this->limitKeyPrefix . md5(request()->getRealIp());
            if (Cache::has($cache_key)) {
                Cache::set($cache_key, Cache::get($cache_key) + 1);
                return true;
            }
            Cache::set($cache_key, 1, $login_limit_hour * 3600);
        }
        return true;
    }

    /**
     * 临时手动清除某个ip的限制
     * @param $ip
     * @return bool
     */
    public function clearLoginLimit($ip): bool
    {
        $cache_key = $this->limitKeyPrefix . md5($ip);
        return Cache::delete($cache_key);
    }

    /**
     * 设置用户登录信息
     * @param $admin_user
     * @param bool $remember
     */
    public function setAdminUserAuthInfo($admin_user, bool $remember): void
    {
        request()->session()->set($this->store_uid_key, $admin_user->id);
        if ($remember) {
            response()->cookie($this->store_uid_key, $admin_user->id);
            response()->cookie($this->store_sign_key, $this->getUserSign($admin_user));
        }
    }

    /**
     * @return AdminUser
     * @throws HttpResponseException
     */
    public function getAdminUserAuthInfo(): AdminUser
    {
        //  当前管理员ID
        $admin_user_id = request()->session()->get($this->store_uid_key, 0);

        if ($admin_user_id === 0) {
            throw new HttpResponseException(response_error('未找到登录信息'));
        }

        $admin_user = $this->model
            ->where('id', '=', $admin_user_id)
            ->findOrEmpty();

        /** @var AdminUser $admin_user */
        if (!$admin_user) {
            throw new HttpResponseException(response_error('用户不存在'));
        }

        if ($admin_user->status !== 1) {
            throw new HttpResponseException(response_error('用户被冻结'));
        }

        return $admin_user;
    }

    /**
     * 退出
     * @param AdminUser $admin_user
     * @return bool
     */
    public function logout(AdminUser $admin_user): bool
    {
        $this->clearAuthInfo();
        return true;
    }

    /**
     * 清除登录用户信息
     */
    public function clearAuthInfo(): void
    {
        Cache::delete($this->store_uid_key);
        Cache::delete($this->store_sign_key);
        request()->session()->flush();
    }

    /**
     * 获取签名
     * @param $admin_user
     * @return string
     */
    public function getUserSign($admin_user): string
    {
        return md5(md5($this->admin_key . $admin_user->id) . $this->admin_key);
    }

    /**
     * 获取当前设备ID
     * @param $admin_user
     * @return string
     */
    public function getDeviceId($admin_user): string
    {
        $key       = 'device_id_uid_' . $admin_user->id;
        $device_id = request()->cookie($key);
        if (!$device_id) {
            try {
                $rand_text = StringService::getRandString(20);
            } catch (Exception $e) {
                $rand_text = time() . $admin_user->id . microtime();
            }

            $device_id = sha1('admin_user_' . $admin_user->id . $rand_text . time());
            request()->cookie($key, $device_id);
        }

        return $device_id;
    }
}