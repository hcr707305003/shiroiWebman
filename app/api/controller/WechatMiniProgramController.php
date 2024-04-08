<?php

namespace app\api\controller;

use app\api\model\User;
use app\common\exception\HttpResponseException;
use app\common\model\WechatScanLoginAuth;
use app\api\service\AuthService;
use app\api\validate\WechatScanLoginAuthValidate;
use app\common\traits\WechatTrait;
use hg\apidoc\annotation\After;
use hg\apidoc\annotation\NotHeaders;
use hg\apidoc\annotation\Query;
use hg\apidoc\annotation\Returned;
use hg\apidoc\annotation\Title;
use support\Response;

/**
 * @Title("微信小程序相关")
 */
class WechatMiniProgramController extends ApiBaseController
{
    protected array $loginExcept = [
        'api/wechat_mini_program/createQrcode', //创建小程序码
        'api/wechat_mini_program/checkScanStatus', //验证小程序扫码状态
        'api/wechat_mini_program/qrcode', //生成临时的小程序码
    ];

    use WechatTrait;

    public function __construct()
    {
        $this->initWechatMiniProgram();
        parent::__construct();
    }

    /**
     * 用户扫码登录
     * @param WechatScanLoginAuth $auth
     * @NotHeaders()
     * @Returned("scene",type="string",desc="场景")
     * @Returned("qrcode",type="string",desc="二维码url地址")
     * @After(event="setGlobalQuery",key="scene",value="res.data.data.scene",desc="场景")
     * @return Response
     */
    public function createQrcode(WechatScanLoginAuth $auth): Response
    {
        $scene = $auth->create_new_scene();
        return $scene ? api_success([
            'scene' => $scene,
            'qrcode' => request()->domain() . url('qrcode') . '?' . http_build_query([
                    'scene' => $scene
                ])
        ]) : api_error('创建场景失败');
    }

    /**
     * 验证扫码登录的状态
     * @NotHeaders()
     * @Query("scene",type="string",desc="场景")
     * @throws Response|HttpResponseException
     */
    public function checkScanStatus(WechatScanLoginAuth $auth, User $user, AuthService $service, WechatScanLoginAuthValidate $validate): Response
    {
        //验证
        $check = $validate->scene('check_scan_status')->check(input());
        if (!$check) {
            return api_error($validate->getError());
        }
        //查看scene状态
        $info = $auth->where('scene', input('scene'))->findOrEmpty();
        switch ($info['status']) {
            case 1:
                return api_result('请使用微信扫码', [], 202, [], false);
            case 2:
                return api_result('已扫码，请点击授权登录', [], 203, [], false);
            case 3:
                if ($user->where('unionid', $info['unionid'])->findOrEmpty()->isExists()) {
                    return api_success($service->wechatMiniProgramLogin($info['unionid']), '登录成功');
                } else {
                    return api_error('用户不存在');
                }
            case 4:
                return api_result('已取消授权', [], 204, [], false);
            default:
                return api_error('系统异常');
        }
    }

    /**
     * 用户登录临时qrcode
     * @Query("scene",type="string",desc="场景")
     * @NotHeaders()
     */
    public function qrcode(WechatScanLoginAuthValidate $validate): Response
    {
        //验证
        $check = $validate->scene('qrcode')->check(input());
        if (!$check) {
            return api_error($validate->getError());
        }
        //场景
        $scene = input('scene');
        //要跳转的小程序路径(默认首页)
        $path = input('path', 'pages/tabBar/login');
        //定义宽度
        $width = input('width');
        //获取临时小程序码
        $result = $this->wechat_mini_program->app_code->getUnlimit($scene, array_merge([
            'page' => $path, // 小程序扫码页面的路径
            "check_path" => false, // 是否验证你的路径是否正确
            'is_hyaline' => true, //是否透明背景
            'env_version' => input('version', WECHAT['version'] ?: 'release')
        ], $width ? ['width' => $width] : []));
        //转换成文件流
        return response($result->getBody()->getContents(), 200, [
            'Content-Disposition' => 'attachment; filename="' . $scene . '.jpg"',
            'Content-Type' => $result->getHeader('Content-Type'),
        ]);
    }
}