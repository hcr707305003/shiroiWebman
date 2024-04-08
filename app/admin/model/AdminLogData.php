<?php

namespace app\admin\model;

use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;

class AdminLogData extends AdminBaseModel
{
    protected $json = ['data'];

    // 不生成该表的日志
    public bool $doNotRecordLog = true;

    use SoftDelete;
    /**
     * 关联log
     * @return BelongsTo
     */
    public function adminLog(): BelongsTo
    {
        return $this->belongsTo(AdminLog::class);
    }
}