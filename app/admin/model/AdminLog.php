<?php

namespace app\admin\model;

use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;
use think\model\relation\HasOne;

class AdminLog extends AdminBaseModel
{
    use SoftDelete;

    // 不生成该表的日志
    public bool $doNotRecordLog = true;

    /**
     * @var array 搜索的字段：操作，URL
     */
    public array $searchField = [
        'name',
        'url',
    ];

    public array $whereField = [
        'admin_user_id'
    ];

    /**
     * 关联用户
     * @return BelongsTo
     */
    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class);
    }

    /**
     * 关联详情
     * @return HasOne
     */
    public function adminLogData(): HasOne
    {
        return $this->hasOne(AdminLogData::class);
    }
}