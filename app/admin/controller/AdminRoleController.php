<?php

declare (strict_types=1);

namespace app\admin\controller;

use Exception;
use support\Request;
use support\Response;
use think\db\Query;
use app\admin\model\AdminMenu;
use app\admin\model\AdminRole;
use app\admin\validate\AdminRoleValidate;

/**
 * 后台角色控制器
 * @author shiroi <707305003@qq.com>
 */
class AdminRoleController extends AdminBaseController
{

    /**
     * 列表
     * @param Request $request
     * @param AdminRole $model
     * @return Response
     * @throws Exception
     */
    public function index(Request $request, AdminRole $model): Response
    {
        $param = $request->get();
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
     * @param AdminRole $model
     * @param AdminRoleValidate $validate
     * @return Response
     */
    public function add(Request $request, AdminRole $model, AdminRoleValidate $validate): Response
    {
        if ($request->isPost()) {
            $param = $request->post();
            $check = $validate->scene('admin_add')->check($param);
            if (!$check) {
                return admin_error($validate->getError());
            }

            $result = $model::create($param);

            $redirect = isset($param['_create']) && (int)$param['_create'] === 1 ? URL_RELOAD : URL_BACK;

            return $result ? admin_success('添加成功', [], $redirect) : admin_error('添加失败');
        }
        return $this->fetch();
    }

    /**
     * 修改
     * @param Request $request
     * @param AdminRole $model
     * @param AdminRoleValidate $validate
     * @return Response
     */
    public function edit(Request $request, AdminRole $model, AdminRoleValidate $validate): Response
    {
        $data = $model->findOrEmpty(request()->get('id'));
        if ($request->isPost()) {
            $param = $request->post();
            $check = $validate->scene('admin_edit')->check($param);
            if (!$check) {
                return admin_error($validate->getError());
            }
            $result = $data->save($param);
            return $result ? admin_success('修改成功', [], URL_BACK) : admin_error('修改失败');
        }

        $this->assign([
            'data' => $data,
        ]);

        return $this->fetch();
    }

    /**
     * 授权
     * @param Request $request
     * @param AdminRole $model
     * @return Response
     */
    public function access(Request $request, AdminRole $model): Response
    {
        $data = $model->findOrEmpty(request()->get('id'));
        if ($request->isPost()) {
            $param = $request->post();
            if (!isset($param['url'])) {
                return admin_error('请至少选择一项权限');
            }
            $param['url'] = array_map('intval', $param['url']);
            asort( $param['url']);

            if (false !== $data->save($param)) {
                return admin_success('操作成功',[],URL_BACK);
            }
            return admin_error();
        }

        $menu = (new AdminMenu)->order('sort_number', 'asc')
            ->order('id', 'asc')
            ->column('*', 'id');
        $html = $this->authorizeHtml($menu, $data->url);

        $this->assign([
            'data' => $data,
            'html' => $html,
        ]);

        return $this->fetch();
    }

    /**
     * 删除
     * @param AdminRole $model
     * @return Response
     */
    public function del(AdminRole $model): Response
    {
        $id = request()->post('id');
        $result = $model::destroy(static function ($query) use ($id) {
            /** @var Query $query */
            $query->whereIn('id', $id);
        });

        return $result ? admin_success('删除成功', [], URL_RELOAD) : admin_error('删除失败');
    }

    /**
     * 启用
     * @param AdminRole $model
     * @return Response
     */
    public function enable(AdminRole $model): Response
    {
        $result = $model->whereIn('id', request()->post('id'))->update(['status' => 1]);
        return $result ? admin_success('操作成功', [], URL_RELOAD) : admin_error();
    }

    /**
     * 禁用
     * @param AdminRole $model
     * @return Response
     */
    public function disable(AdminRole $model): Response
    {
        $result = $model->whereIn('id', request()->post('id'))->update(['status' => 0]);
        return $result ? admin_success('操作成功', [], URL_RELOAD) : admin_error();
    }
}
