<?php /** @noinspection DuplicatedCode */

namespace app\api\controller;

use app\api\validate\TokenValidate;
use app\common\exception\HttpResponseException;
use app\common\service\TokenService;
use hg\apidoc\annotation as Apidoc;
use support\Response;

/**
 * @Apidoc\Title("token服务")
 */
class TokenController extends ApiBaseController
{
    protected array $loginExcept = [
        'api/token/refresh',
    ];

    /**
     * 刷新token
     * @Apidoc\Method("post")
     * @Apidoc\Header("refresh_token",type="string",desc="刷新token")
     * @Apidoc\Param("refresh_token",type="string",desc="刷新token")
     * @Apidoc\After(event="setGlobalHeader",key="token",value="res.data.data.access_token",desc="Token")
     * @Apidoc\After(event="setGlobalHeader",key="refresh_token",value="res.data.data.refresh_token",desc="refresh Token")
     */
    public function refresh(TokenValidate $validate): Response
    {
        $refresh_token = request()->header('refresh_token') ?? request()->input('refresh_token');
        //验证
        $check = $validate->scene('refresh')->check(compact('refresh_token'));
        if (!$check) {
            return api_error($validate->getError());
        }

        try {
            $data = (new TokenService())->refreshToken($refresh_token);
            return api_success($data);
        } catch (HttpResponseException $e) {
            return api_error($e->getMessage(), $e->getData(), $e->getCode());
        }
    }
}