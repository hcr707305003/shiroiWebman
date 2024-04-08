<?php
use support\Route;

Route::group('/test', function () {
    Route::any('/test', [app\api\controller\TestController::class, 'test']);
    //测试表单生成
    Route::any('/test_form_design', [app\api\controller\TestController::class, 'test_form_design']);
    //测试加密解密
    Route::any('/test_encode', [app\api\controller\TestController::class, 'test_encode']);
    //测试生成二维码
    Route::any('/test_qrcode', [app\api\controller\TestController::class, 'test_qrcode']);
    //测试think orm读写连贯操作
    Route::any('/test_think_orm', [app\api\controller\TestController::class, 'test_think_orm']);
    //测试laravel orm读写连贯操作
    Route::any('/test_laravel_orm', [app\api\controller\TestController::class, 'test_laravel_orm']);
});