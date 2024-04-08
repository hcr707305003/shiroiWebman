<?php

namespace app\common\middleware;

use support\Log;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;
use function app_path;

class LoadCommon implements MiddlewareInterface
{

    public function process(Request $request, callable $handler): Response
    {
        //当前模块
        $pathArr = explode('/', ltrim(request()->path(), '/'));
        //引入公共函数文件
        if(file_exists($file_path = app_path($pathArr[0]) . DIRECTORY_SEPARATOR . 'common.php')) {
            require_once $file_path;
        }
        $response = $handler($request); // 继续向洋葱芯穿越，直至执行控制器得到响应

        //响应之前先输出数据库操作日志
        if($log = \Chance\Log\facades\OperationLog::getLog()) {
            //加入自定义日志
            Log::channel('sql_log')->info($log);
            //输入日志
            dump($log);
        }

        return $response;
    }
}