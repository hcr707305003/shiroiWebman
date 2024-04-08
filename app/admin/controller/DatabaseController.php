<?php

declare (strict_types=1);

namespace app\admin\controller;

use Exception;
use support\Response;
use think\facade\Db;

/**
 * 数据库控制器(没有加备份/恢复数据库的功能，防止数据泄漏或误操作)
 * @author shiroi <707305003@qq.com>
 */
class DatabaseController extends AdminBaseController
{

    /**
     * 数据表
     * @throws Exception
     */
    public function table(): Response
    {
        $data = Db::query('SHOW TABLE STATUS');
        $data = array_map('array_change_key_case', $data);
        $this->assign([
            'data'  => $data,
            'total' => count($data),
        ]);
        return $this->fetch();
    }

    /**
     * 查看表信息
     * @return Response
     * @throws Exception
     */
    public function view(): Response
    {
        if (!$name = request()->get('name')) {
            return admin_error('请指定要查看的表');
        }

        $field_list = Db::query('SHOW FULL COLUMNS FROM `' . $name . '`');

        $data = [];
        foreach ($field_list as $value) {
            $data[] = [
                'name'       => $value['Field'],
                'type'       => $value['Type'],
                'collation'  => $value['Collation'],
                'null'       => $value['Null'],
                'key'        => $value['Key'],
                'default'    => $value['Default'],
                'extra'      => $value['Extra'],
                'privileges' => $value['Privileges'],
                'comment'    => $value['Comment'],
            ];
        }

        $this->assign([
            'data' => $data,
        ]);
        return $this->fetch();
    }

    /**
     * 优化表
     * @return Response
     * @throws Exception
     */
    public function optimize(): Response
    {
        if (!$name = request()->post('name')) {
            return admin_error('请指定要优化的表');
        }
        $name   = is_array($name) ? implode('`,`', $name) : $name;
        $result = Db::query("OPTIMIZE TABLE `$name`");
        if ($result) {
            return admin_success("数据表`$name`优化成功");
        }
        return admin_error("数据表`$name`优化失败");
    }

    /**
     * 修复表
     * @return Response
     * @throws Exception
     */
    public function repair(): Response
    {
        if (!$name = request()->post('name')) {
            return admin_error('请指定要修复的表');
        }
        $name   = is_array($name) ? implode('`,`', $name) : $name;
        $result = Db::query("REPAIR TABLE `$name`");
        if ($result) {
            return admin_success("数据表`$name`修复成功");
        }
        return admin_error("数据表`$name`修复失败");
    }
}
