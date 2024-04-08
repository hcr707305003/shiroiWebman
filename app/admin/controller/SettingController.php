<?php

namespace app\admin\controller;

use Exception;
use support\Request;
use support\Response;
use think\db\Query;
use RuntimeException;
use app\admin\model\Setting;
use app\admin\model\SettingGroup;
use app\admin\traits\SettingContent;
use app\admin\validate\SettingValidate;

/**
 * 设置控制器
 * @author shiroi <707305003@qq.com>
 */
class SettingController extends AdminBaseController
{
    // 引入form相关trait
    use SettingContent;

    /**
     * 设置列表
     * @param Request $request
     * @param Setting $model
     * @return Response
     * @throws Exception
     */
    public function index(Request $request, Setting $model): Response
    {
        $param = $request->all();
        $data = $model->with('setting_group')
            ->scope('where', $param)
            ->paginate([
                'list_rows' => $this->admin['admin_list_rows'],
                'var_page' => 'page',
                'query' => $request->get()
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
     * 添加设置
     * @param Request $request
     * @param Setting $model
     * @param SettingValidate $validate
     * @return Response
     * @throws Exception
     */
    public function add(Request $request, Setting $model, SettingValidate $validate): Response
    {
        if ($request->isPost()) {
            $param = $request->post();
            $validate_result = $validate->scene('add')->check($param);
            if (!$validate_result) {
                return admin_error($validate->getError());
            }

            try {
                $param['content'] = $this->getContent($param);
            } catch (RuntimeException $exception) {
                return admin_error($exception->getMessage());
            }

            $result = $model::create($param);

            $url = URL_BACK;
            if (isset($param['_create']) && ((int)$param['_create']) === 1) {
                $url = URL_RELOAD;
            }

            return $result->isExists() ? admin_success('添加成功', $url) : admin_error();
        }

        $this->assign([
            'setting_group_list' => (new SettingGroup)->select(),
        ]);

        return $this->fetch();
    }

    /**
     * 修改设置
     * @param Request $request
     * @param Setting $model
     * @param SettingValidate $validate
     * @return Response
     * @throws Exception
     */
    public function edit(Request $request, Setting $model, SettingValidate $validate): Response
    {

        $data = $model->findOrEmpty(request()->get('id'));
        if ($request->isPost()) {
            $param = $request->post();
            $validate_result = $validate->scene('edit')->check($param);
            if (!$validate_result) {
                return admin_error($validate->getError());
            }

            try {
                $param['content'] = $this->getContent($param);
            } catch (RuntimeException $exception) {
                return admin_error($exception->getMessage());
            }

            return $data->save($param) ? admin_success() : admin_error();
        }

        $this->assign([
            'data' => $data,
            'setting_group_list' => (new SettingGroup())->select(),

        ]);
        return $this->fetch();
    }

    /**
     * 删除设置
     * @param Setting $model
     * @return Response
     */
    public function del(Setting $model): Response
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
     * @param null $id
     * @return Response
     * @throws Exception
     */
    protected function show($id = null): Response
    {
        $id = request()->get('id', $id);
        $data = (new Setting)->where('setting_group_id', '=', $id)->select();
        foreach ($data as $value) {
            $content_new = [];
            foreach ($value->content as $content) {

                $content['form'] = $this->getFieldForm($content['type'], $content['name'], $content['field'], $content['content'], $content['option']);
                $content_new[] = $content;
            }
            $value->content = $content_new;
        }

        //自动更新配置文件
        $group = (new SettingGroup)->findOrEmpty($id);
        $this->admin['title'] = $group->name;

        $this->assign([
            'data_config' => $data,
        ]);

        return $this->fetch('setting/show');
    }

    /**
     * 更新配置
     * @param Request $request
     * @param Setting $model
     * @return Response
     */
    public function update(Request $request, Setting $model): Response
    {
        $param = $request->all();
        $id = $param['id'];
        $config = $model->findOrEmpty($id);

        $content_data = [];
        foreach ($config->content as $value) {
            if ($value['type'] === 'map' || $value['type'] === 'multi_select') {
                $param[$value['field']] = implode(',', $param[$value['field']]);
            }

            $value['content'] = $param[$value['field']];
            $content_data[] = $value;
        }

        $config->content = $content_data;

        return $config->save() ? admin_success('修改成功', [], URL_RELOAD) : admin_error();
    }

    /**
     * @param Request $request
     * @param SettingGroup $model
     * @return Response
     * @throws Exception
     */
    public function all_setting(Request $request, SettingGroup $model): Response
    {
        $data = $model->scope('where', $request->all())
            ->paginate([
                'list_rows' => $this->admin['admin_list_rows'],
                'var_page' => 'page',
                'query' => $request->get()
            ]);

        // 关键词，排序等赋值
        $this->assign($request->get());
        $this->assign([
            'data' => $data,
            'page' => $data->render(),
            'total' => $data->total(),
        ]);
        return $this->fetch('setting/all');
    }

    /**
     * @throws Exception
     */
    public function info(): Response
    {
        return $this->show(request()->get('id'));
    }

    /**
     * 后台设置
     * @throws Exception
     */
    public function admin_setting(): Response
    {
        return $this->show(1);
    }

    /**
     * 前台设置
     * @throws Exception
     */
    public function index_setting(): Response
    {
        return $this->show(2);
    }

    /**
     * 对象存储设置
     * @throws Exception
     */
    public function cloud_setting(): Response
    {
        return $this->show(3);
    }

    /**
     * 微信设置
     * @throws Exception
     */
    public function wechat_setting(): Response
    {
        return $this->show(4);
    }

    /**
     * 基本设置
     * @throws Exception
     */
    public function config_setting(): Response
    {
        return $this->show(5);
    }
}
