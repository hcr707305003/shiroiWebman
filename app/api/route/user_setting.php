<?php
use support\Route;

Route::group('/user_setting', function () {
    //获取用户设置
    Route::any('/info', [app\api\controller\UserSettingController::class, 'info']);
});

//包含 index(列表),show(个人详情),store(保存),update(修改),destroy(删除)
Route::resource('/user_setting', app\api\controller\UserSettingController::class, [
    'store', //设置用户设置
]);