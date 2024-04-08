<?php
use support\Route;

Route::group('/auth', function () {
    //用户登录
    Route::post('/login', [app\api\controller\AuthController::class, 'login']);

    //用户注册
    Route::post('/register', [app\api\controller\AuthController::class, 'register']);

    //手机验证码登录
    Route::post('/phone_login', [app\api\controller\AuthController::class, 'phoneLogin']);
});