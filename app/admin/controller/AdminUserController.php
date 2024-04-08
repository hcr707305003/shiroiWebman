<?php

declare (strict_types=1);

namespace app\admin\controller;

use app\admin\exception\AdminServiceException;
use app\admin\service\AdminRoleService;
use app\admin\service\AdminUserService;
use Exception;
use support\Request;
use support\Response;
use think\db\Query;
use app\admin\model\AdminUser;
use app\admin\validate\AdminUserValidate;

/**
 * 后台用户控制器
 * @author shiroi <707305003@qq.com>
 */
class AdminUserController extends AdminBaseController
{

    /**
     * 列表
     * @param Request $request
     * @param AdminUser $model
     * @return Response
     * @throws Exception
     */
    public function index(Request $request, AdminUser $model): Response
    {
        $param = $request->all();
        $data  = $model->scope('where', $param)
            ->paginate([
                'list_rows' => $this->admin['admin_list_rows'],
                'var_page'  => 'page',
                'query'     => $request->get()
            ]);

        // 关键词，排序等赋值
        $this->assign($request->get());

        $this->assign([
            'data'  => $data,
            'page'  => $data->render(),
            'total' => $data->total(),
        ]);

        return $this->fetch();
    }

    /**
     * 添加
     * @param Request $request
     * @param AdminUserService $service
     * @param AdminUserValidate $validate
     * @return Response
     */
    public function add(Request $request, AdminUserService $service, AdminUserValidate $validate): Response
    {
        if ($request->isPost()) {
            $param = $request->post();
            $check = $validate->scene('admin_add')->check($param);
            if (!$check) {
                return admin_error($validate->getError());
            }

            try {
                $result   = $service->create($param);
                $redirect = isset($param['_create']) && (int)$param['_create'] === 1 ? URL_RELOAD : URL_BACK;

                return $result ? admin_success('添加成功', [], $redirect) : admin_error('添加失败');
            } catch (AdminServiceException $e) {
                return admin_error($e->getMessage());
            }
        }

        $this->assign([
            'role_list' => (new AdminRoleService())->getAll(),
        ]);

        return $this->fetch();
    }

    /**
     * 修改
     * @param Request $request
     * @param AdminUser $model
     * @param AdminUserService $service
     * @param AdminUserValidate $validate
     * @return Response
     */
    public function edit(Request $request, AdminUser $model, AdminUserService $service, AdminUserValidate $validate): Response
    {
        $data = $model->findOrEmpty(request()->get('id'));
        if ($request->isPost()) {
            $param = $request->post();
            if(!($param['password'] ?? '')) {
                unset($param['password']);
            }
            $check = $validate->scene('admin_edit')->check($param);
            if (!$check) {
                return admin_error($validate->getError());
            }

            try {
                $result = $service->update($data, $param);
            } catch (AdminServiceException $e) {
                return admin_error($e->getMessage());
            }

            return $result ? admin_success('修改成功', [], URL_BACK) : admin_error('修改失败');
        }

        $this->assign([
            'data'            => $data,
            'role_list'       => (new AdminRoleService())->getAll(),
            'password_config' => $service->getCurrentPasswordLevel()
        ]);

        return $this->fetch();
    }

    /**
     * 删除
     * @param AdminUser $model
     * @return Response
     */
    public function del(AdminUser $model): Response
    {
        $check = $model->inNoDeletionIds($id = request()->post('id'));

        if (false !== $check) {
            return admin_error('ID 为' . $check . '的数据无法删除');
        }

        $result = $model::destroy(static function ($query) use ($id) {
            /** @var Query $query */
            $query->whereIn('id', $id);
        });

        return $result ? admin_success('删除成功', [], URL_RELOAD) : admin_error('删除失败');
    }

    /**
     * 启用
     * @param AdminUser $model
     * @return Response
     */
    public function enable(AdminUser $model): Response
    {
        $result = $model->whereIn('id', request()->post('id'))->update(['status' => 1]);
        return $result ? admin_success('操作成功', [], URL_RELOAD) : admin_error();
    }

    /**
     * 禁用
     * @param AdminUser $model
     * @return Response
     */
    public function disable(AdminUser $model): Response
    {
        $has_admin = false;
        $id = request()->post('id');
        if (is_array($id)) {
            $id = array_map('intval', $id);
            if (in_array(1, $id, true)) {
                $has_admin = true;
            }
        } else if ((int)$id === 1) {
            $has_admin = true;
        }
        if($has_admin){
            return admin_error('超级管理员不能禁用');
        }

        $result = $model->whereIn('id', $id)->update(['status' => 0]);
        return $result ? admin_success('操作成功', [], URL_RELOAD) : admin_error();
    }

    /**
     * 个人资料
     * @param Request $request
     * @param AdminUserValidate $validate
     * @return Response
     */
    public function profile(Request $request, AdminUserValidate $validate): Response
    {
        if ($request->isPost()) {
            $param = $request->post();

            if ($param['update_type'] === 'password') {

                $validate_result = $validate->scene('admin_password')->check($param);
                if (!$validate_result) {
                    return admin_error($validate->getError());
                }

                if (!password_verify($param['current_password'], base64_decode($this->user->password))) {
                    return admin_error('当前密码不正确');
                }
                $param['password'] = $param['new_password'];
            } elseif ($param['update_type'] === 'avatar') {
                unset($param['update_type']);
            }

            return $this->user->save($param) ? admin_success('修改成功', [], URL_RELOAD) : admin_error();
        }

        $this->assign([
            'data' => $this->user,
        ]);
        return $this->fetch();
    }
}
