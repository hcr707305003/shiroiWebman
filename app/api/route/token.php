<?php
use support\Route;

Route::group('/token', function () {
    //刷新token
    Route::post('/refresh', [app\api\controller\TokenController::class, 'refresh']);
});