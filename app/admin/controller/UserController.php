<?php

namespace app\admin\controller;

use app\admin\model\AdminBaseModel;
use app\admin\model\UserGroup;
use app\admin\service\UserService;
use app\admin\traits\ControllerTrait;
use app\admin\validate\UserValidate;
use app\admin\model\User;
use Exception;
use support\Request;
use support\Response;
use think\db\Query;

/**
 * 用户控制器
 * @author shiroi <707305003@qq.com>
 */
class UserController extends AdminBaseController
{
    use ControllerTrait;

    /** @var string|UserService $service 服务层 */
    protected static string $service = 'app\admin\service\UserService';

    /**
     * 列表
     * @param Request $request
     * @param User $model
     * @return Response
     * @throws Exception
     */
    public function index(Request $request, User $model): Response
    {
        $param = $request->all();
        $data  = $model->with('user_group')->scope('where', $param)
            ->paginate([
                'list_rows' => $this->admin['admin_list_rows'],
                'var_page'  => 'page',
                'query'     => $request->get(),
            ]);
        // 关键词，排序等赋值
        $this->assign($request->get());

        $this->assign([
            'data'            => $data,
            'page'            => $data->render(),
            'total'           => $data->total(),
            'user_group_list' => UserGroup::select(),
            'status_list'     => AdminBaseModel::BOOLEAN_TEXT,
        ]);
        return $this->fetch();
    }

    /**
     * userSearch input插件生成器
     * @param Request $request
     * @param User $model
     * @param array $where
     * @return Response
     * @throws Exception
     */
    public function userList(Request $request, User $model, array $where = []): Response
    {
        if($id = input('id')) {
            $where[] = [
                'id','=',$id
            ];
        }
        if($name = input('username','','trim')) {
            $where[] = [
                'username|nickname','like',$name."%"
            ];
        }
        if(($invite_user_id = input('invite_user_id')) !== null) {
            $where[] = [
                'invite_user_id','=',$invite_user_id
            ];
        }
        if($mobile = input('mobile','','trim')) {
            $where[] = [
                'mobile','like',$mobile."%"
            ];
        }
        $data = $model->where($where)
            ->paginate([
                'var_page'  => 'page',
                'query'     => $request->get(),
            ]);
        $this->assign([
            'get'             => $request->get(),
            'data'            => $data,
            'page'            => $data->render(),
            'total'           => $data->total()
        ]);
        $template = $request->input('template', 'user_list');

        return $this->fetch($template);
    }

    /**
     * userMultiSearch input插件生成器
     * @throws Exception
     */
    public function userMultiList(): Response
    {
        return $this->fetch(request()->input('template', 'user_multi_list'));
    }

    /**
     * userMultiSearch ajax请求
     * @throws Exception
     */
    public function ajaxUserList(Request $request, User $model, array $where = []): Response
    {
        if($id = input('id')) {
            $where[] = [
                'id','=',$id
            ];
        }
        if($name = input('username','','trim')) {
            $where[] = [
                'username|nickname','like',$name."%"
            ];
        }
        if(($invite_user_id = input('invite_user_id')) !== null) {
            $where[] = [
                'invite_user_id','=',$invite_user_id
            ];
        }
        if($mobile = input('mobile','','trim')) {
            $where[] = [
                'mobile','like',$mobile."%"
            ];
        }
        $result = $model->where($where);
        return admin_success('操作成功', $result->limit(input('limit', 10))->page(input('page', 1))->select(),URL_CURRENT, 200, [], [
            'total' => $result->count()
        ]);
    }

    /**
     * 添加
     * @param Request $request
     * @param User $model
     * @param UserValidate $validate
     * @return Response
     * @throws Exception
     */
    public function add(Request $request, User $model, UserValidate $validate): Response
    {
        if ($request->isPost()) {
            $param           = $request->post();
            $validate_result = $validate->scene('admin_add')->check($param);
            if (!$validate_result) {
                return admin_error($validate->getError());
            }

            $result = $model::create($param);

            $url = URL_BACK;
            if (isset($param['_create']) && (int)$param['_create'] === 1) {
                $url = URL_RELOAD;
            }
            return $result ? admin_success('添加成功', [], $url) : admin_error();
        }
        $this->assign([
            'user_group_list' => UserGroup::select(),

        ]);
        return $this->fetch();
    }

    /**
     * 修改
     * @param Request $request
     * @param User $model
     * @param UserValidate $validate
     * @return Response
     * @throws Exception
     */
    public function edit(Request $request, User $model, UserValidate $validate): Response
    {
        $id = request()->get('id');
        $data = $model->findOrEmpty($id);
        if ($request->isPost()) {
            $param = $request->post();
            if(!($param['password'] ?? '')) {
                unset($param['password']);
            }
            $check = $validate->scene('admin_edit')->check(array_merge($param, ['id' => $id]));
            if (!$check) {
                return admin_error($validate->getError());
            }
            $result = $data->save($param);

            return $result ? admin_success('修改成功', [], URL_BACK) : admin_error('修改失败');
        }

        $this->assign([
            'data'            => $data,
            'user_group_list' => UserGroup::select(),

        ]);
        return $this->fetch();
    }

    /**
     * 删除
     * @param User $model
     * @return Response
     */
    public function del(User $model): Response
    {
        $id = request()->post('id');
        $check = $model->inNoDeletionIds($id);
        if (false !== $check) {
            return admin_error('ID为' . $check . '的数据不能被删除');
        }

        $result = $model::destroy(static function ($query) use ($id) {
            /** @var Query $query */
            $query->whereIn('id', $id);
        });

        return $result ? admin_success('删除成功', [], URL_RELOAD) : admin_error('删除失败');
    }

    /**
     * 导入
     * @throws Exception
     */
    public function import(): Response
    {
        return $this->importData(new User(), ['id', 'group_id', 'username', 'mobile', 'nickname', 'avatar', 'status', 'create_time']);
    }

    /**
     * 导出
     * @throws Exception
     */
    public function export(Request $request): Response
    {
        return $this->exportDataToBinary('user', ['id', 'group_id', 'username', 'mobile', 'nickname', 'avatar', 'status', 'create_time'], $request->all());
    }
}