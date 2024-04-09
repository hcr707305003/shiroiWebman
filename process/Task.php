<?php

namespace process;

use support\Db;
use Workerman\Crontab\Crontab;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\ConsoleWriter;

class Task
{
    public function onWorkerStart()
    {
        $data = 'https://www.baidu.com';

        //控制台生成二维码
        echo(Builder::create()
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->writer(new ConsoleWriter())
            ->data($data)
            ->build()
            ->getString());


        //每个一分钟执行一下查库动作，防止mysql go away
//        new Crontab('0 */5 * * * *', function(){
//            dump(console(to_array(Db::select("show processlist"))));
//        });
    }
}