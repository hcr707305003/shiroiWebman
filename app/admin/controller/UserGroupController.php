<?php

namespace app\admin\controller;

use Exception;
use support\Request;
use support\Response;
use think\db\Query;
use app\admin\model\UserGroup;
use app\admin\validate\UserGroupValidate;

/**
 * 用户组控制器
 * @author shiroi <707305003@qq.com>
 */
class UserGroupController extends AdminBaseController
{

    /**
     * 列表
     * @param Request $request
     * @param UserGroup $model
     * @return Response
     * @throws Exception
     */
    public function index(Request $request, UserGroup $model): Response
    {
        $param = $request->get();
        $data = $model->scope('where', $param)
            ->paginate([
                'list_rows' => $this->admin['admin_list_rows'],
                'var_page' => 'page',
                'query' => $request->get(),
            ]);
        // 关键词，排序等赋值
        $this->assign($request->get());

        $this->assign([
            'data' => $data,
            'page' => $data->render(),
            'total' => $data->total(),

        ]);
        return $this->fetch();
    }

    /**
     * 添加
     * @param Request $request
     * @param UserGroup $model
     * @param UserGroupValidate $validate
     * @return Response
     */
    public function add(Request $request, UserGroup $model, UserGroupValidate $validate): Response
    {
        if ($request->isPost()) {
            $param = $request->post();
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

        return $this->fetch();
    }

    /**
     * 修改
     * @param Request $request
     * @param UserGroup $model
     * @param UserGroupValidate $validate
     * @return Response
     */
    public function edit(Request $request, UserGroup $model, UserGroupValidate $validate): Response
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
     * 删除
     * @param UserGroup $model
     * @return Response
     */
    public function del(UserGroup $model): Response
    {
        $check = $model->inNoDeletionIds($id = request()->post('id'));
        if (false !== $check) {
            return admin_error('ID为' . $check . '的数据不能被删除');
        }

        $result = $model::destroy(static function ($query) use ($id) {
            /** @var Query $query */
            $query->whereIn('id', $id);
        });

        return $result ? admin_success('删除成功', [], URL_RELOAD) : admin_error('删除失败');
    }
}