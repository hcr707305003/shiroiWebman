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

$_get_illuminate_database_config = function ($name, $index, $default) {
    $usernames = explode(',', DATABASE[$name]);
    return $usernames[$index] ?? $usernames[0] ?? $default;
};

return [
    // 默认数据库
    'default' => 'mysql',

    // 各种数据库配置
    'connections' => [
        'mysql' => [
            'write' => [
                [
                    'host' => explode(',', DATABASE['host'] ?? 'test')[0],
                    'username' => explode(',', DATABASE['username'] ?? 'root')[0],
                    'password' => explode(',', DATABASE['password'] ?? 'root')[0],
                ],
            ],
            'read' => [
                [
                    'host' => $_get_illuminate_database_config('host', 1, 'test'),
                    'username' => $_get_illuminate_database_config('username', 1, 'root'),
                    'password' => $_get_illuminate_database_config('password', 1, 'root'),
                ],
                [
                    'host' => $_get_illuminate_database_config('host', 2, 'test'),
                    'username' => $_get_illuminate_database_config('username', 2, 'root'),
                    'password' => $_get_illuminate_database_config('password', 2, 'root'),
                ]
            ],
            'driver' => DATABASE['type'] ?? 'mysql',
            'port' => DATABASE['port'] ?? 3306,
            'database' => DATABASE['database'] ?? 'test',
            'charset' => DATABASE['charset'] ?? 'utf8',
            'collation' => DATABASE['collation'] ?? 'utf8_unicode_ci',
            'prefix' => DATABASE['prefix'] ?? '',
            'strict' => DATABASE['strict'] ?? true,
            'engine' => DATABASE['engine'] ?? null,
            'options' => [
                \PDO::ATTR_TIMEOUT => 3
            ],
            // 日志记录的主键
            "logKey" => "id",
        ],
    ],
];
