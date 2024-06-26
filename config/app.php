<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

use support\Request;

return [
    'debug' => true,
    'error_reporting' => E_ALL,
    'default_timezone' => 'Asia/Shanghai',
    'request_class' => Request::class,
    'public_path' => base_path() . DIRECTORY_SEPARATOR . 'public',
    'runtime_path' => base_path(false) . DIRECTORY_SEPARATOR . 'runtime',
    'controller_suffix' => 'Controller',
    'controller_reuse' => false,
    'deny_app_list'      => ['common'],
    'exception_template' => [
        401 => app_path() . '/admin/view/error/401.html',
        404 => app_path() . '/admin/view/error/404.html',
        500 => app_path() . '/admin/view/error/500.html',
    ],

    'dispatch_error'   => app_path() . '/admin/view/public/jumptpl.html',
    'dispatch_success' => app_path() . '/admin/view/public/jumptpl.html',
    'exception_tpl'    => app_path() . '/admin/view/error/500.html',
    'error_message'    => '页面错误！请稍后再试～',
    'version'          => 'v1.1.9',
    'cors_domain'      => ['*', '127.0.0.1'],
    'api_url'          => 'https://api.swiftadmin.net/',
    'show_error_msg'   => false,
];
