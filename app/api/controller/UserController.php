<?php /** @noinspection DuplicatedCode */

namespace app\api\controller;

use app\api\traits\ControllerEditTrait;
use app\api\traits\ControllerRelationTrait;
use app\api\service\UserService;
use app\api\validate\UserValidate;
use hg\apidoc\annotation as Apidoc;
use support\Response;

/**
 * @Apidoc\Title ("用户相关")
 */
class UserController extends ApiBaseController
{
    /** @var string|UserService $service 用户服务层  */
    protected static string $service = 'app\api\service\UserService';

    /** @var string|UserValidate $validate 用户验证器  */
    protected static string $validate = 'app\api\validate\UserValidate';

    use ControllerEditTrait;
    use ControllerRelationTrait;


    /**
     * 当前用户详情
     * @Apidoc\Method("get")
     */
    public function info(array $data = []): Response
    {
        //是否设置了密码
        $data['is_set_password'] = !empty($this->user->password);
        return api_success(array_merge($this->user->visible([
            'id', 'unique_id', 'username', 'nickname', 'mobile', 'avatar', 'sex'
        ])->toArray(), $data));
    }

    //前置默认where用户id
    protected function beforeWhereUser($where = []): ?array
    {
        $where[] = ['id' , '=', $this->uid];
        return $where;
    }

    protected function beforeEditData(): ?array
    {
        return request()->only(['nickname', 'password', 'avatar', 'sex']);
    }
}