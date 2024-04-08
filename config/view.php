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

return [
    'handler' => \support\Template::class,
    'options' => [
        'tpl_cache'          => false,
        'view_suffix'        => 'html',
        'tpl_begin'          => '{',
        'tpl_end'            => '}',
        'tpl_replace_string' => [
            '__STATIC__'        => '/static',
            '__STATIC_JS__'     => '/static/js',
            '__STATIC_CSS__'    => '/static/css',
            '__STATIC_IMAGES__' => '/static/images',
            '__STATIC_PLUGINS__'=> '/static/plugins',
            '__ADMIN__'         => '/static/admin',
            '__ADMIN_JS__'      => '/static/admin/js',
            '__ADMIN_CSS__'     => '/static/admin/css',
            '__ADMIN_IMAGES__'  => '/static/admin/images',
            '__ADMIN_PLUGINS__' => '/static/admin/plugins',
        ]
    ]
];
