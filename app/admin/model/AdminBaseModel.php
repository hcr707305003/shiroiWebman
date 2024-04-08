<?php

namespace app\admin\model;

use app\common\model\CommonBaseModel;

class AdminBaseModel extends CommonBaseModel
{
    // 是否字段，使用场景：用户的是否冻结，文章是否为热门等等。
    const BOOLEAN_TEXT = [0 => '否', 1 => '是'];

    /**
     * 是否状态获取器
     * @param $value
     * @param mixed $data
     * @return string
     */
    public function getStatusTextAttr($value, $data = []): string
    {
        return isset($data['status'])? self::BOOLEAN_TEXT[$data['status']]: '未知';
    }
}