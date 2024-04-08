<?php

namespace support;

use Webman\Route as RouteObject;

class Route extends RouteObject
{
    /**
     * @param string $name
     * @param string $controller
     * @param array $options
     * @return void
     */
    public static function resource(string $name, string $controller, array $options = [])
    {
        $name = trim($name, '/');
        if (is_array($options) && !empty($options)) {
            $diffOptions = array_diff($options, ['list', 'index', 'store', 'save', 'edit', 'update', 'show', 'read', 'destroy', 'delete', 'total']);
            if (!empty($diffOptions)) {
                foreach ($diffOptions as $action) {
                    static::any("/$name/{$action}", [$controller, $action])->name("$name.{$action}");
                }
            }
            //列表(不分页)
            if (in_array('list', $options)) static::get("/$name/list", [$controller, 'list'])->name("$name.list");
            //列表(分页)
            if (in_array('index', $options)) static::get("/$name", [$controller, 'index'])->name("$name.index");
            //保存
            if(in_array('store', $options)) {
                static::post("/$name", [$controller, 'store'])->name("$name.store");
            } elseif (in_array('save', $options)) {
                static::post("/$name", [$controller, 'save'])->name("$name.save");
            }
            //修改
            if (in_array('update', $options)) static::put("/$name/{id}", [$controller, 'update'])->name("$name.update");
            if (in_array('edit', $options)) static::put("/$name", [$controller, 'edit'])->name("$name.edit");
            //详情
            if(in_array('show', $options)) {
                static::get("/$name/{id}", [$controller, 'show'])->name("$name.show");
            } elseif (in_array('read', $options)) {
                static::get("/$name/{id}", [$controller, 'read'])->name("$name.read");
            }
            //删除
            if (in_array('destroy', $options)) static::delete("/$name/{id}", [$controller, 'destroy'])->name("$name.destroy");
            if (in_array('delete', $options)) static::delete("/$name/{id}", [$controller, 'delete'])->name("$name.delete");
        }
    }
}