<?php

namespace app\common\traits;

use app\common\exception\HttpResponseException;

/**
 * 系统维护
 */
trait SystemMaintenanceTrait
{
    /**
     * @throws HttpResponseException
     */
    protected function checkSystem()
    {
        //获取系统维护配置
        $system_maintenance = setting('index.system_maintenance');
        //是否系统维护
        if($system_maintenance['is_maintenance']) {
            throw new HttpResponseException(response_error($system_maintenance['maintenance_msg']));
        }
    }
}