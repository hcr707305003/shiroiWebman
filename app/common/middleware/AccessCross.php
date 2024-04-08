<?php

namespace app\common\middleware;

use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class AccessCross implements MiddlewareInterface
{
    public function process(Request $request, callable $handler) : Response
    {
        $response = $request->method() == 'OPTIONS' ? response() : $handler($request);
        if($response instanceof Response) {
            // 默认为全部允许跨域
            $header = [
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Max-Age'           => 1800,
                'Access-Control-Allow-Methods'     => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers'     => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With, token, noloading',
                'Access-Control-Allow-Origin'      => $request->header('origin', '*')
            ];
            $response->withHeaders($header);
            return $response;
        }
        return response_error();
    }
}