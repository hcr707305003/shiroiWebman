<?php
use support\Route;

Route::group('/file', function () {
    //保存图片
    Route::any('/save_image', [app\api\controller\FileController::class, 'saveImage']);
    //保存文件
    Route::any('/save_file', [app\api\controller\FileController::class, 'saveFile']);
    //保存base64图片
    Route::any('/save_base64_image', [app\api\controller\FileController::class, 'saveBase64Image']);
});