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

use app\common\middleware\AccessCross;
use Webman\Http\Request;
use Webman\Route;

//加载路由
foreach ([
    'admin' => '/admin/route/*.php', //admin模块
    'api' => '/api/route/*.php', //api模块
    'm_api' => '/m_api/route/*.php', //小程序api模块
] as $module => $routes) Route::group("/{$module}", function () use ($module, $routes) {
    //自动加载admin控制器
    if($module == 'admin') {
        //获取所有子类
        $childClasses = get_directory_child_classes(\app\admin\controller\AdminBaseController::class, '/admin/controller/*.php');
        foreach ($childClasses as $class) {
            $className = explode('\\', $class);
            $shortClass = small_mount_to_underline(remove_both_str(end($className), 'Controller', 2));
            foreach (get_class_access_methods($class) as $method) {
                try {
                    Route::any("/{$shortClass}/{$method}", [$class, $method]);
                } catch (\Exception $exception) {}
            }
        }
    }
    //加载路由文件
    foreach (glob(app_path() . $routes) as $route) {
        require_once $route;
//        try {} catch (\Exception $exception) {}
    }
});

//性能分析
Route::get('/xhprof', function ($request) {
    return \Shiroi\Xhprof\Webman\Xhprof::index();
});

//默认加载跨域中间件
Route::fallback(function (Request $request) {
    $accessCross = new AccessCross();
    return $accessCross->process($request, function () {});
});

Route::disableDefaultRoute();