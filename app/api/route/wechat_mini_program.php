<?php
use support\Route;

Route::group('/wechat_mini_program', function () {
    //创建小程序码登录场景
    Route::add(['OPTIONS','GET'],'/create_qrcode', [app\api\controller\WechatMiniProgramController::class, 'createQrCode']);

    //验证扫码登录的状态
    Route::add(['OPTIONS','GET'],'/check_scan_status', [app\api\controller\WechatMiniProgramController::class, 'checkScanStatus']);

    //生成用户登录临时qrcode
    Route::add(['OPTIONS','GET'],'/qrcode', [app\api\controller\WechatMiniProgramController::class, 'qrcode']);
});