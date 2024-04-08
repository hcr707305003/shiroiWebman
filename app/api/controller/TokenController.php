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
     * @Apidoc\Param("refresh_token",type="string",desc="刷新token",require=true)
     */
    public function refresh(TokenValidate $validate): Response
    {
        $param = request()->all();
        //验证
        $check = $validate->scene('refresh')->check($param);
        if (!$check) {
            return api_error($validate->getError());
        }

        try {
            $data = (new TokenService())->refreshToken($param['refresh_token']);
            return api_success($data);
        } catch (HttpResponseException $e) {
            return api_error($e->getMessage(), $e->getData(), $e->getCode());
        }
    }
}