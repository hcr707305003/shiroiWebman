<?php /** @noinspection PhpIllegalArrayKeyTypeInspection */

/**
 * User: Shiroi
 * EMail: 707305003@qq.com
 */

namespace app\common\model;

use app\common\plugin\UserSetting as UserSettingPlugin;

/**
 * @property int user_id
 * @property string client
 * @property string code
 * @property string name
 * @property string|array content
 * @property array extra_param
 * @property array option
 * @property string type
 * @property string description
 */
class UserSetting extends CommonBaseModel
{
    protected $json = [
        'extra_param',
        'option'
    ];

    /**
     * 隐藏的字段
     * @var string[] $hidden
     */
    protected $hidden = [
        'create_time',
        'update_time',
        'delete_time'
    ];

    protected $jsonAssoc = true;

    /**
     * @param UserSetting $model
     * @return void
     */
    public static function onBeforeInsert($model): void
    {
        $model->extra_param = (isset($model['extra_param']) && $model['extra_param']) ? $model['extra_param']: [];
        $model->option = (isset($model['option']) && $model['option']) ? $model['option']: [];
    }

    public function getContentAttr($value,$data = [])
    {
        return (new UserSettingPlugin())->getConvertType($value, $data['type'] ?? 'text');
    }

    public function setContentAttr($value,$data = [])
    {
        return (new UserSettingPlugin())->setConvertType($value, $data['type'] ?? 'text');
    }
}