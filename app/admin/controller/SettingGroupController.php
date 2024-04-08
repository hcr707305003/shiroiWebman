<?php

namespace app\admin\controller;

use Exception;
use support\Request;
use support\Response;
use think\db\Query;
use app\admin\model\SettingGroup;
use app\admin\validate\SettingGroupValidate;

/**
 * 设置分组控制器
 * @author shiroi <707305003@qq.com>
 */
class SettingGroupController extends AdminBaseController
{
    protected array $codeBlacklist = [
        'app', 'api', 'cache', 'database', 'console', 'cookie', 'log', 'middleware', 'session', 'template', 'trace',
        'attachment', 'geetest', 'generate', 'admin', 'paginate', 'abstract', 'and', 'array',
        'as', 'break', 'callable', 'case', 'catch', 'class', 'clone', 'const', 'continue', 'declare', 'default', 'die',
        'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile',
        'eval', 'exit', 'extends', 'final', 'finally', 'for', 'foreach', 'function', 'global', 'goto', 'if',
        'include', 'include_once', 'instanceof', 'insteadof', 'interface', 'isset', 'list', 'namespace', 'new',
        'or', 'print', 'private', 'protected', 'public', 'require', 'require_once', 'return', 'static', 'switch',
        'throw', 'trait', 'try', 'unset', 'use', 'var', 'while', 'xor', 'yield', 'int', 'float', 'bool', 'string', 'true',
        'false', 'null', 'index',

    ];

    /**
     * @param Request $request
     * @param SettingGroup $model
     * @return Response
     * @throws Exception
     */
    public function index(Request $request, SettingGroup $model): Response
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
     * @param Request $request
     * @param SettingGroup $model
     * @param SettingGroupValidate $validate
     * @return Response
     * @throws Exception
     */
    public function add(Request $request, SettingGroup $model, SettingGroupValidate $validate): Response
    {
        if ($request->isPost()) {
            $param           = $request->post();
            $validate_result = $validate->scene('add')->check($param);
            if (!$validate_result) {
                return admin_error($validate->getError());
            }

            if (in_array($param['code'], $this->codeBlacklist, true)) {
                return admin_error('代码 ' . $param['code'] . ' 在黑名单内，禁止使用');
            }

            $redirect = isset($param['_create']) && (int)$param['_create'] === 1 ? URL_RELOAD : URL_BACK;

            return $model::create($param) ? admin_success('添加成功', [], $redirect) : admin_error('添加失败');
        }

        $this->assign([
            'module_list' => $this->getModuleList(),
        ]);

        return $this->fetch();
    }

    /**
     * @param Request $request
     * @param SettingGroup $model
     * @param SettingGroupValidate $validate
     * @return Response
     * @throws Exception
     */
    public function edit(Request $request, SettingGroup $model, SettingGroupValidate $validate): Response
    {
        /** @var SettingGroup $data */
        $data = $model->findOrEmpty($request->get('id'));
        if ($request->isPost()) {
            $param           = $request->post();
            $validate_result = $validate->scene('edit')->check($param);
            if (!$validate_result) {
                return admin_error($validate->getError());
            }

            return $data->save($param) ? admin_success('修改成功', [], URL_BACK) : admin_error('修改失败');
        }

        $this->assign([
            'data'        => $data,
            'module_list' => $this->getModuleList(),

        ]);
        return $this->fetch();

    }

    /**
     * @param SettingGroup $model
     * @return Response
     * @throws Exception
     */
    public function del(SettingGroup $model): Response
    {
        $id = request()->post('id');
        $check = $model->inNoDeletionIds($id);
        if (false !== $check) {
            return admin_error('ID 为' . $check . '的数据无法删除');
        }
        // 删除限制
        $relation_name    = 'setting';
        $relation_cn_name = '设置';
        $tips             = '下有' . $relation_cn_name . '数据，请删除' . $relation_cn_name . '数据后再进行删除操作';
        if (is_array($id)) {
            foreach ($id as $item) {
                /** @var SettingGroup $data */
                $data = $model->find($item);
                if ($data->$relation_name->count() > 0) {
                    return admin_error($data->name . $tips);
                }
            }
        } else {
            /** @var SettingGroup $data */
            $data = $model->find($id);
            if ($data->$relation_name->count() > 0) {
                return admin_error($data->name . $tips);
            }
        }

        $result = $model::destroy(static function ($query) use ($id) {
            /** @var Query $query */
            $query->whereIn('id', $id);
        });

        return $result ? admin_success('删除成功', [], URL_RELOAD) : admin_error('删除失败');
    }

    /**
     * 获取所有项目模块
     * @return array
     */
    protected function getModuleList(): array
    {
        $app_path    = base_path() . DIRECTORY_SEPARATOR . 'app/';
        $module_list = [];
        $all_list    = scandir($app_path);

        foreach ($all_list as $item) {
            if ($item !== '.' && $item !== '..' && is_dir($app_path . $item)) {
                $module_list[] = $item;
            }
        }
        return $module_list;
    }
}
