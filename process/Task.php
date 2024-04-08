<?php

namespace process;

use support\Db;
use Workerman\Crontab\Crontab;

class Task
{
    public function onWorkerStart()
    {
        //每个一分钟执行一下查库动作，防止mysql go away
        new Crontab('0 */5 * * * *', function(){
            dump(console(to_array(Db::select("show processlist"))));
        });
    }
}