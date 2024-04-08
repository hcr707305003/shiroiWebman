<?php /** @noinspection DuplicatedCode */

namespace app\api\service;

use app\common\model\User;
use app\common\traits\ServiceTrait;
use app\common\exception\HttpResponseException;
use app\common\service\TokenService;

class AuthService extends ApiBaseService
{
    use ServiceTrait;

    /** @var string|User $userModel 用户模型层  */
    public static string $userModel = 'app\common\model\User';

    /**
     * 用户登录
     * @param $username
     * @param $password
     * @return array
     * @throws HttpResponseException
     */
    public function userLogin($username, $password): array
    {
        /** @var User $user */
        $user = static::$userModel::where('username|mobile|unique_id', '=', $username)->findOrEmpty();

        //用户是否存在
        if ($user->isEmpty()) {
            throw new HttpResponseException(api_error('用户不存在'));
        }

        //密码是否正确
        $verify = verify_password($password, $user->password);
        if (!$verify) {
            throw new HttpResponseException(api_error('密码错误'));
        }

        // 检查是否被冻结
        if ($user->status != 1) {
            throw new HttpResponseException(api_error('账号被冻结'));
        }

        //设置登录事件
        event('user.login', $user);

        //实例化
        $service = new TokenService();

        //返回token
        return array_merge(
            $service->isEnableRefreshToken() ? [
            'refresh_token' => $service->getRefreshToken($user->id)]: []
        ,[
            'access_token' => $service->getAccessToken($user->id),
            'exp_time' => $service->getExpTime(),
            'is_register' => 0
        ]);
    }

    /**
     * 用户注册 （验证器已过滤）
     * @param array $data
     * @return array
     * @throws HttpResponseException
     */
    public function userRegister(array $data = []): array
    {
        /** @var User $user */
        $user = static::$userModel::where('username' ,'=', $data['username'])->findOrEmpty();

        if($user->isExists()) {
            throw new HttpResponseException(api_error('用户已存在'));
        } else {
            if(static::$userModel::where('mobile' ,'=', $data['mobile'])->findOrEmpty()->isExists()) {
                throw new HttpResponseException(api_error('手机号码已被注册'));
            }
        }

        /** @var User $user */
        $user = static::$userModel::create($data);

        //设置注册事件
        event('user.register', $user);

        //实例化
        $service = new TokenService();

        //返回token
        return array_merge(
            $service->isEnableRefreshToken() ? [
                'refresh_token' => $service->getRefreshToken($user->id)]: []
            ,[
            'access_token' => $service->getAccessToken($user->id),
            'exp_time' => $service->getExpTime(),
            'is_register' => 1
        ]);
    }

    /**
     * 用户手机号登录
     * @param array $data
     * @return array
     * @throws HttpResponseException
     */
    public function phoneLogin(array $data = []): array
    {
        /** @var User $user */
        $user = static::$userModel::where('mobile|username' ,'=', $data['mobile'])->findOrEmpty();

        //存在则登录
        if($user->isEmpty()) {
            /** @var User $user */
            $user = static::$userModel::create(array_merge($data, [
                'username' => $data['mobile'], //使用手机号做用户名使用
            ]));
        }

        // 检查是否被冻结
        if ($user->status != 1) {
            throw new HttpResponseException(api_error('账号被冻结'));
        }

        //设置登录事件
        event('user.login', $user);

        //实例化
        $service = new TokenService();

        //返回token
        return array_merge(
            $service->isEnableRefreshToken() ? [
                'refresh_token' => $service->getRefreshToken($user->id)]: []
            ,[
            'access_token' => $service->getAccessToken($user->id),
            'exp_time' => $service->getExpTime(),
            'is_register' => 0
        ]);
    }

    /**
     * 微信登录|注册
     * @param string $unionid
     * @param array $saveData
     * @param array $option
     * @return array
     * @throws HttpResponseException
     * @noinspection SpellCheckingInspection
     */
    public function wechatMiniProgramLogin(string $unionid, array $saveData = [], array $option = []): array
    {
        /** @var User $user */
        $user = static::$userModel::where('unionid', $unionid)->findOrEmpty();

        //不存在则创建
        if($user->isEmpty()) {
            throw new HttpResponseException(api_error('用户不存在'));
        }

        //设置登录事件
        event('user.login', $user);

        //实例化
        $service = new TokenService();
        //返回token
        return array_merge(
            $service->isEnableRefreshToken() ? [
                'refresh_token' => $service->getRefreshToken($user->id)]: []
            ,[
            'access_token' => $service->getAccessToken($user->id),
            'exp_time' => $service->getExpTime()
        ], $option);
    }
}