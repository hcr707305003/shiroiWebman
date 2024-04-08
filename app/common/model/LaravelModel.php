<?php

namespace app\common\model;

use app\common\traits\VSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use support\Model;

class LaravelModel extends Model
{
    protected $guarded = [];

    /** @var string CREATED_AT 创建时间 */
    const CREATED_AT = 'create_time';

    /** @var string UPDATED_AT 更新时间 */
    const UPDATED_AT = 'update_time';

    /** @var string DELETED_AT 删除时间 */
    const DELETED_AT = 'delete_time';

    /** @var int DEFAULT_SOFT_DELETE 默认软删除值 */
    const DEFAULT_SOFT_DELETE = 0;

    /** @var array|string[] $defaultOrder 默认排序 */
    protected array $defaultOrder = ['id' => 'desc'];

    public $timestamps = false;

    /** @var array|string[] $ignoreLogFields 日志生成忽略的字段 */
    public array $ignoreLogFields = [
        'create_time',
        'update_time',
    ];

    protected static function booted()
    {
        parent::boot();

        //设置默认排序
        static::addGlobalScope('order', function (Builder $builder) {
            if(property_exists(static::class, 'defaultOrder')) foreach ((new static())->defaultOrder as $field => $order)
                $builder->orderBy($field, $order);
        });

        static::creating(function ($model) {
            $model->{static::CREATED_AT} = time();
            $model->{static::UPDATED_AT} = time();
        });

        static::saving(function ($model) {
            $model->{static::UPDATED_AT} = time();
        });
    }

    public function getCreateTimeAttribute($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    public function getUpdateTimeAttribute($value)
    {
        return date('Y-m-d H:i:s', $value);
    }
}
