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

use Webman\Session\FileSessionHandler;
use Webman\Session\RedisSessionHandler;
use Webman\Session\RedisClusterSessionHandler;

return [
    'type' => CACHE['driver'] ?? 'file', // or redis or redis_cluster
    'handler' => cache_config('driver', 'file') == 'redis' ? RedisSessionHandler::class: FileSessionHandler::class,
    'config' => [
        'file' => [
            'save_path' => runtime_path() . '/sessions',
        ],
        'redis' => [
            'host' => CACHE['host'] ?? '127.0.0.1',
            'port' => CACHE['port'] ?? 6379,
            'database' => CACHE['select'] ?? 0,
            'auth' => CACHE['password'] ?? '',
            'prefix' => CACHE['prefix'] ?? '', // session key prefix
            'timeout' => CACHE['timeout'] ?? 2,
        ],
        'redis_cluster' => [
            'host' => CACHE['host'] ?? ['127.0.0.1:7000', '127.0.0.1:7001', '127.0.0.1:7001'],
            'timeout' => CACHE['timeout'] ?? 2,
            'auth' => CACHE['password'] ?? '',
            'prefix' => CACHE['password'] ?? '',
        ]
    ],

    'session_name' => 'SESSION_ID',
    'auto_update_timestamp' => false,
    'lifetime' => 7 * 24 * 60 * 60,
    'cookie_lifetime' => 365 * 24 * 60 * 60,
    'cookie_path' => '/',
    'domain' => '',
    'http_only' => false,
    'secure' => false,
    'same_site' => '',
    'gc_probability' => [1, 1000],
];
