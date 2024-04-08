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

global $argv;

return [
    // File update detection and automatic reload
    'monitor' => [
        'handler' => process\Monitor::class,
        'reloadable' => false,
        'constructor' => [
            // Monitor these directories
            'monitorDir' => array_merge([
                app_path(),
                config_path(),
                base_path() . '/process',
                base_path() . '/support',
                base_path() . '/resource',
                base_path() . '/.env',
            ],
                glob(base_path() . '/application*.yml'),
                glob(base_path() . '/extend/*/*.php'),
                glob(base_path() . '/extend/*/*/*.php'),
                glob(base_path() . '/plugin/*/app'),
                glob(base_path() . '/plugin/*/config'),
                glob(base_path() . '/plugin/*/api')
            ),
            // Files with these suffixes will be monitored
            'monitorExtensions' => [
                'php', 'html', 'htm', 'env', 'yml'
            ],
            'options' => [
                'enable_file_monitor' => !in_array('-d', $argv) && DIRECTORY_SEPARATOR === '/',
                'enable_memory_monitor' => DIRECTORY_SEPARATOR === '/',
            ]
        ]
    ],
    //定时器文件
    'crontab'  => [
        'handler'  => process\Task::class
    ],

    //开启一个websocket服务
//    'websocket' => [
//        // 这里指定进程类，就是上面定义的Pusher类
//        'handler' => \app\socket\SocketServer::class,
//        'listen'  => 'websocket://0.0.0.0:' . (SOCKET['port'] ?? 8888),
//        'count'   => 1,
//    ]
];
