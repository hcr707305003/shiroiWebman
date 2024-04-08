<?php

/** 不做任何操作 */

use support\Response;

const URL_CURRENT = 'url://current';
/** 刷新页面 */
const URL_RELOAD = 'url://reload';
/** 返回上一个页面 */
const URL_BACK = 'url://back';
/** 关闭当前layer弹窗 */
const URL_CLOSE_LAYER = 'url://close_layer';
/** 关闭当前弹窗并刷新父级 */
const URL_CLOSE_REFRESH = 'url://close_refresh';
/** 刷新整个页面 */
const URL_REFRESH = 'url://refresh';
/** 关闭当前弹窗并刷UI 针对企业编辑页面*/
const URL_CLOSE_REFRESH_UI = 'url://close-refresh-UI';

if (!function_exists('admin_success')) {

    /**
     * 后台返回成功
     * @param mixed $msg
     * @param mixed $data ,
     * @param string $url
     * @param int $code
     * @param array $header
     * @param array $options
     * @return Response
     */
    function admin_success($msg = '操作成功', $data = [], string $url = URL_CURRENT, int $code = 200, array $header = [], array $options = []): Response
    {
        return admin_result($msg, $data, $url, $code, $header, $options);
    }
}

if (!function_exists('admin_error')) {
    /**
     * 后台返回错误
     * @param mixed $msg
     * @param mixed $data ,
     * @param string $url
     * @param int $code
     * @param array $header
     * @param array $options
     * @return Response
     */
    function admin_error($msg = '操作失败', $data = [], string $url = URL_CURRENT, int $code = 500, array $header = [], array $options = []): Response
    {
        return admin_result($msg, $data, $url, $code, $header, $options);
    }
}

if (!function_exists('admin_result')) {
    /**
     * 后台返回结果
     * @param mixed $msg
     * @param mixed $data
     * @param string $url
     * @param int $code
     * @param array $header
     * @param array $options
     * @return Response
     */
    function admin_result($msg = '', $data = [], string $url = URL_CURRENT, int $code = 200, array $header = [], array $options = []): Response
    {
        $data = array_merge([
            'msg'  => $msg,
            'code' => $code,
            'data' => empty($data) ? (object)$data : $data,
            'url'  => $url,
        ],$options);
        return response_result($data, server_config('response.http_code_sync') ? $code : 200, $header);
    }
}