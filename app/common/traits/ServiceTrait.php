<?php
/**
 * @noinspection PhpMultipleClassDeclarationsInspection
 * @noinspection PhpUnhandledExceptionInspection
 * @noinspection PhpMissingReturnTypeInspection
 * @noinspection PhpUnused
 */

namespace app\common\traits;

use think\Model;

/**
 * @property string|Model $model
 */
trait ServiceTrait
{
    public static function getLists($where = [],$field = '*',$order = [],$group = [],$having = '')
    {
        return (new static::$model)->where($where)->field($field)->order($order)->group($group)->having($having)->select();
    }

    public static function getIndex($where = [],$field = '*',$page = 1,$limit = 10,$order = [],$group = [],$having = '')
    {
        return (new static::$model)->where($where)->field($field)->page($page)->limit($limit)->order($order)->group($group)->having($having)->select();
    }

    public static function getPaginate($where = [], $field = [], $paginate = [], $order = [],$group = [],$having = '')
    {
        return (new static::$model)->scope('where', $where)->where($where)->field($field)->order($order)->group($group)->having($having)->paginate($paginate);
    }

    public static function getTotal($where = [])
    {
        return (new static::$model)->where($where)->count();
    }

    public static function getInfo($where = [], $field = [],$order = [])
    {
        return (new static::$model)->where($where)->field($field)->order($order)->findOrEmpty();
    }

    public static function create($data = [])
    {
        return (new static::$model)->create($data);
    }

    public static function update($where = [],$update = [])
    {
        return (new static::$model)->update($update, $where);
    }

    public static function updateAll($where = [],$update = [])
    {
        return (new static::$model)->where($where)->update($update);
    }

    public static function delete($where = [], $isDelete = null)
    {
        $info = (new static::$model)->where($where)->findOrEmpty();
        if($isDelete !== null) {
            $info = $info->force($isDelete);
        }
        return $info->delete();
    }

    public static function inc($where = [], $field ='', $value=1)
    {
        return (new static::$model)->where($where)->Inc($field, $value)->update();
    }

    public static function dec($where = [], $field ='', $value=1)
    {
        return (new static::$model)->where($where)->Dec($field, $value)->update();
    }

    public static function value($where = [], $value = '')
    {
        return (new static::$model)->where($where)->value($value);
    }
}