<?php
use support\Route;

Route::group('/user', function () {
    //用户个人信息
    Route::any('/info', [app\api\controller\UserController::class, 'info']);
});

//包含 index(列表),show(个人详情),store(保存),update(修改),destroy(删除)
Route::resource('/user', app\api\controller\UserController::class, [
    'edit'
]);