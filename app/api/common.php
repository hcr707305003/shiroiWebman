<?php

use support\Response;

if (!function_exists('api_unauthorized')) {
    /**
     * 未认证（未登录）
     * @param mixed $msg
     * @param mixed $data
     * @param int $code
     * @param array $header
     * @return Response
     */
    function api_unauthorized($msg = 'unauthorized', $data = [], int $code = 401, array $header = []): Response
    {
        return api_result($msg, $data, $code, $header);
    }
}

if (!function_exists('api_forbidden')) {
    /**
     * 无权限
     * @param mixed $msg
     * @param mixed $data
     * @param int $code
     * @param array $header
     * @return Response
     */
    function api_forbidden($msg = 'forbidden', $data = [], int $code = 403, array $header = []): Response
    {
        return api_result($msg, $data, $code, $header);
    }
}


if (!function_exists('api_success')) {
    /**
     * 操作成功
     * @param mixed $data
     * @param mixed $msg
     * @param int $code
     * @param array $header
     * @return Response
     */
    function api_success($data = [], $msg = 'success', int $code = 200, array $header = []): Response
    {
        return api_result($msg, $data, $code, $header);
    }
}

if (!function_exists('api_error')) {
    /**
     * 操作失败
     * @param mixed $msg
     * @param mixed $data
     * @param int $code
     * @param array $header
     * @return Response
     */
    function api_error($msg = 'fail', $data = [], int $code = 500, array $header = []): Response
    {
        return api_result($msg, $data, $code, $header);
    }
}


if (!function_exists('api_service_unavailable')) {
    /**
     * 系统维护中
     * @param mixed $msg
     * @param mixed $data
     * @param int $code
     * @param array $header
     * @return Response
     */
    function api_service_unavailable($msg = 'service unavailable', $data = [], int $code = 503, array $header = []): Response
    {
        return api_result($msg, $data, $code, $header);
    }
}


if (!function_exists('api_error_client')) {
    /**
     * 客户端错误 例如提交表单的时候验证不通过，是因为客户填写端错误引起的
     * @param mixed $msg
     * @param mixed $data
     * @param int $code
     * @param array $header
     * @return Response
     */
    function api_error_client($msg = 'client error', $data = [], int $code = 400, array $header = []): Response
    {
        return api_result($msg, $data, $code, $header);
    }
}

if (!function_exists('api_error_server')) {
    /**
     * 服务端错误
     * @param mixed $msg
     * @param mixed $data
     * @param int $code
     * @param array $header
     * @return Response
     */
    function api_error_server($msg = 'server error', $data = [], int $code = 500, array $header = []): Response
    {
        return api_result($msg, $data, $code, $header);
    }
}

if (!function_exists('api_error_404')) {
    /**
     * 资源或接口不存在
     * @param string $msg
     * @param mixed $data
     * @param int $code
     * @param array $header
     * @return Response
     */
    function api_error_404(string $msg = '404 not found', $data = [], int $code = 404, array $header = []): Response
    {
        return api_result($msg, $data, $code, $header);
    }
}

if (!function_exists('api_result')) {
    /**
     * 返回json结果
     * @param string $msg
     * @param mixed $data
     * @param int $code
     * @param array $header
     * @param bool $refused_code_sync
     * @return Response
     */
    function api_result(string $msg = 'success', $data = [], int $code = 200, array $header = [], bool $refused_code_sync = true): Response
    {
        $data = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ];
        return response_result($data, (server_config('response.http_code_sync') && $refused_code_sync) ? $code : 200, $header);
    }
}