<?php /** @noinspection DuplicatedCode */

namespace app\api\middleware;

use app\common\exception\HttpResponseException;
use app\common\model\User;
use app\common\service\TokenService;
use app\common\traits\MiddlewareTrait;
use ReflectionException;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class ApiAuth implements MiddlewareInterface
{
    use MiddlewareTrait;

    /**
     * @param Request|\support\Request $request
     * @param callable $handler
     * @return Response
     * @throws ReflectionException
     * @throws HttpResponseException
     */
    public function process(Request $request, callable $handler): Response
    {
        //处理请求路径
        $url = $this->parse_url($request->path());
        //实例化token服务
        $tokenService = new TokenService();
        //获取所有反射的属性
        $reflectionPropertyList = $this->reflectionPropertyList($request);
        /** =================================用户登录token验证-start================================= */
        $user_token = $this->loadToken();
        if (!in_array($url, $reflectionPropertyList['login_except'], true) && !$reflectionPropertyList['is_visitor']) {
            // 缺少token
            if (empty($user_token)) {
                return response_unauthorized('未登录');
            }

            //验证token
            $userTokenResult = $this->checkUserToken($tokenService, $user_token, $request);
            if($userTokenResult[0] === false) {
                return response_unauthorized('验证token失败');
            }

            /** @var User $user */
            $user = (new User)->findOrEmpty($request->uid);
            if ($user->isEmpty()) {
                return response_unauthorized('用户不存在', [], 402);
            }

            if ($user->status === 0) {
                return response_unauthorized('账号被冻结', [], 402);
            }
            $request->user = $user;
        } else if ($user_token) {
            $userTokenResult = $this->checkUserToken($tokenService, $user_token, $request);
            if($userTokenResult[0] !== false) {
                $request->user = (new User)->findOrEmpty($request->uid);
            }
        }
        /** =================================用户登录token验证-end================================= */
        return $handler($request);
    }
}