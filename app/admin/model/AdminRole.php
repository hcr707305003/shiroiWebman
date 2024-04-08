<?php

namespace app\admin\model;

use think\model\concern\SoftDelete;

/**
 * @property array $url
 */
class AdminRole extends AdminBaseModel
{
    use SoftDelete;

    public array $searchField = [
        'name'
    ];

    /**
     * 角色初始权限
     * @param AdminRole $model
     * @return void
     */
    public static function onBeforeInsert($model): void
    {
        $model->url = empty($model->url) ? [1, 2, 18] : $model->url;
    }

    protected function getUrlAttr($value)
    {
        return !empty($value) ? explode(',', $value) : [];
    }

    protected function setUrlAttr($value): string
    {
        return !empty($value) ? implode(',', $value) : '';
    }
}