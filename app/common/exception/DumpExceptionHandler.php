<?php

namespace app\common\exception;

use Next\VarDumper\Dumper;
use Next\VarDumper\DumperHandler;
use support\exception\Handler;
use Throwable;
use Webman\Http\Request;
use Webman\Http\Response;

class DumpExceptionHandler extends Handler
{
    use DumperHandler;

    public function render(Request $request, Throwable $exception): Response
    {
        // ApiDoc异常处理响应
        if ($exception instanceof \hg\apidoc\exception\HttpException) {
            return response(json_encode([
                "code" => $exception->getCode(),
                "message" => $exception->getMessage(),
            ],JSON_UNESCAPED_UNICODE), $exception->getStatusCode());
        }

        if ($exception instanceof Dumper) {
            return \response(self::convertToHtml($exception));
        }
        return parent::render($request, $exception);
    }
}