<?php

namespace app\api\controller;

use app\api\traits\ControllerRelationTrait;
use app\api\validate\UserSettingValidate;
use app\common\exception\HttpResponseException;
use app\common\plugin\UserSetting as UserSettingPlugin;
use app\common\traits\UserSettingTrait;
use hg\apidoc\annotation as Apidoc;
use ReflectionException;
use support\Response;

/**
 * @Apidoc\Title("用户设置")
 */
class UserSettingController extends ApiBaseController
{
    use ControllerRelationTrait;

    /** @var string|UserSettingValidate $validate 用户设置验证器  */
    protected static string $validate = 'app\api\validate\UserSettingValidate';

    /**
     * 获取用户设置
     * @Apidoc\Query("code",type="string",require=true,desc="设置代码")
     * @return Response
     */
    public function info(): Response
    {
        //数据
        $data = request()->all();
        //加载验证器
        try {
            $this->loadValidate($data);
        } catch (ReflectionException|HttpResponseException $e) {}
        //后置操作(对接收的参数进行处理)
        return api_success((new UserSettingPlugin())
            ->setUserId($this->uid)
            ->setCode($data['code'])
            ->getSetting(['code', 'name', 'content', 'extra_param', 'description']));
    }

    /**
     * 新增|修改用户设置
     * @Apidoc\Param("code",type="string",require=true,desc="设置代码"))
     * @Apidoc\Param("content",type="string",require=true,desc="设置内容"))
     * @Apidoc\Url("/api/user_setting")
     * @Apidoc\Method("post")
     * @return Response
     */
    public function store(): Response
    {
        //数据
        $data = request()->post();
        //加载验证器
        try {
            $this->loadValidate($data);
        } catch (ReflectionException|HttpResponseException $e) {}
        //返回结果
        return api_success((new UserSettingPlugin())
            ->setUserId($this->uid)
            ->setCode($data['code'])
            ->setClient('pc')
            ->setType($data['type'] ?? 'text')
            ->setContent($data['content'])
            ->saveSetting());
    }
}