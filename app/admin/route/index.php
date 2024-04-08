<?php
use support\Route;

//设置默认请求控制器
Route::any('', [app\admin\controller\IndexController::class, 'index']);
Route::any('/', [app\admin\controller\IndexController::class, 'index']);