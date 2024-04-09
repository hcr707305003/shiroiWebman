<?php /** @noinspection DuplicatedCode */

namespace app\api\controller;

use app\api\service\AuthService;
use app\api\validate\UserValidate;
use app\common\exception\HttpResponseException;
use hg\apidoc\annotation as Apidoc;
use support\Response;
use Webman\Http\Request;

/**
 * @Apidoc\Title("用户登录")
 */
class AuthController extends ApiBaseController
{
    protected array $loginExcept = [
        'api/auth/login',
        'api/auth/register',
        'api/auth/phoneLogin',
    ];

    /**
     * 用户登录
     * @Apidoc\Method("post")
     * @Apidoc\Param("username",type="string",require=true,desc="账号")
     * @Apidoc\Param("password",type="string",require=true,desc="密码")
     * @Apidoc\NotHeaders()
     * @Apidoc\Returned("refresh_token",type="string",desc="刷新token")
     * @Apidoc\Returned("access_token",type="string",desc="验证token")
     * @Apidoc\Returned("exp_time",type="int",desc="token有效期")
     * @Apidoc\After(event="setGlobalHeader",key="token",value="res.data.data.access_token",desc="Token")
     * @Apidoc\After(event="setGlobalHeader",key="refresh_token",value="res.data.data.refresh_token",desc="refresh Token")
     */
    public function login(Request $request, UserValidate $validate, AuthService $service): Response
    {
        $param = $request->all();
        $check = $validate->scene('userLogin')->check($param);
        if (!$check) {
            return api_error($validate->getError());
        }
        try {
            return api_success($service->userLogin($param['username'], $param['password']));
        } catch (HttpResponseException $e) {
            return api_error($e->getMessage(), $e->getData(), $e->getCode());
        }
    }

    /**
     * 用户注册
     * @Apidoc\Method("post")
     * @Apidoc\Param("username",type="string",require=true,desc="账号",mock="shiroi")
     * @Apidoc\Param("password",type="string",require=true,desc="密码",mock="123456")
     * @Apidoc\Param("repassword",type="string",require=true,desc="再次密码",mock="123456")
     * @Apidoc\Param("mobile",type="string",require=true,desc="手机号码",mock="@phone")
     * @Apidoc\NotHeaders()
     * @Apidoc\Returned("refresh_token",type="string",desc="刷新token")
     * @Apidoc\Returned("access_token",type="string",desc="验证token")
     * @Apidoc\Returned("exp_time",type="int",desc="token有效期")
     * @Apidoc\After(event="setGlobalHeader",key="token",value="res.data.data.access_token",desc="Token")
     */
    public function register(Request $request, UserValidate $validate, AuthService $service): Response
    {
        $param = $request->all();
        $check = $validate->scene('userRegister')->check($param);
        if (!$check) {
            return api_error($validate->getError());
        }
        try {
            return api_success($service->userRegister($param));
        } catch (HttpResponseException $e) {
            return api_error($e->getMessage(), $e->getData(), $e->getCode());
        }
    }

    /**
     * 手机验证码登录
     * @Apidoc\Method("post")
     * @Apidoc\Param("mobile",type="string",require=true,desc="手机号码")
     * @Apidoc\Param("code",type="string",require=true,desc="验证码")
     * @Apidoc\NotHeaders()
     * @Apidoc\Returned("refresh_token",type="string",desc="刷新token")
     * @Apidoc\Returned("access_token",type="string",desc="验证token")
     * @Apidoc\Returned("exp_time",type="int",desc="token有效期")
     * @Apidoc\After(event="setGlobalHeader",key="token",value="res.data.data.access_token",desc="Token")
     */
    public function phoneLogin(Request $request, UserValidate $validate, AuthService $service): Response
    {
        $param = $request->all();
        $check = $validate->scene('phoneLogin')->check($param);
        if (!$check) {
            return api_error($validate->getError());
        }

        try {
            return api_success($service->phoneLogin($param));
        } catch (HttpResponseException $e) {
            return api_error($e->getMessage(), $e->getData(), $e->getCode());
        }
    }
}