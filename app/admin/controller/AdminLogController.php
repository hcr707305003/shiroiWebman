<?php

declare (strict_types=1);

namespace app\admin\controller;

use Exception;
use support\Request;
use app\admin\model\AdminLog;
use app\admin\model\AdminUser;
use support\Response;

/**
 * 后台操作日志控制器
 * @author shiroi <707305003@qq.com>
 */
class AdminLogController extends AdminBaseController
{
    protected array $authExcept=[
        'admin/admin_log/position'
    ];

    /**
     * 列表
     * @param Request $request
     * @param AdminLog $model
     * @return Response
     * @throws Exception
     */
    public function index(Request $request, AdminLog $model): Response
    {
        $param = $request->all();
        $data  = $model->with('adminUser')->scope('where', $param)
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
            'admin_user_list'=>(new AdminUser())->select(),
        ]);
        return $this->fetch();
    }

    /**
     * @param AdminLog $model
     * @return Response
     */
    public function detail(AdminLog $model): Response
    {
        $data = $model->with('adminLogData')->findOrEmpty(request()->get('id'));

        $this->assign([
            'data' => $data,
        ]);

        return $this->fetch();
    }

    /**
     * 获取操作IP的城市
     * @param AdminLog $model
     * @return Response
     * @throws Exception
     */
    public function position(AdminLog $model): Response
    {
        $data = $model->findOrEmpty(request()->post('id'));
        $json = file_get_contents('https://restapi.amap.com/v3/ip?ip=' . $data->log_ip . '&key=' . config('map.amap.web_api_key'));
        $arr  = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        if (isset($arr['status']) && $arr['status'] === '1') {
            return admin_success('', ['city' => !empty($arr['city']) ? $arr : '']);
        }

        return admin_error('获取定位失败');
    }
}
