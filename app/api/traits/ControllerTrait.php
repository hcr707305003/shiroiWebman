<?php /** @noinspection DuplicatedCode */
/** @noinspection PhpUndefinedFieldInspection */

namespace app\api\traits;

use hg\apidoc\annotation as Apidoc;

/**
 * 控制器的钩子构造器
 * @author shiroi<707305003@qq.com>
 * 定义路由： Route::resource(
 *              '/user',
 *              app\api\controller\UserController::class,
 *              ['list', 'index', 'store', 'save', 'edit', 'update', 'show', 'read', 'destroy', 'delete', 'total']
 *          );//对象下边方法实现
 * 标识	   请求类型	   生成路由规则	 对应操作方法（默认）  作用
 * list 	GET	        当前路由	       list (不分页)    列表
 * index	GET	        当前路由	       index (分页)     列表
 * show	    GET	        当前路由/:id	   show            详情
 * read	    GET	        当前路由/:id	   show            详情
 * store	POST	    当前路由	       store           保存
 * save 	POST	    当前路由	       store           保存
 * update	PUT	        当前路由/:id	   update          更新
 * edit 	PUT	        当前路由 	   edit            修改
 * destroy	DELETE	    当前路由/:id	   destroy         删除
 * delete	DELETE	    当前路由/:id	   destroy         删除
 */
trait ControllerTrait
{
    /** @method list */
    use ControllerListTrait;

    /** @method index */
    use ControllerIndexTrait;

    /** @method total */
    use ControllerTotalTrait;

    /** @method show */
    /** @method read */
    use ControllerShowTrait;

    /** @method store */
    /** @method save */
    use ControllerStoreTrait;

    /** @method edit */
    use ControllerEditTrait;

    /** @method update */
    use ControllerUpdateTrait;

    /** @method destroy */
    /** @method delete */
    use ControllerDestroyTrait;

    /** 关联额外的条件或操作 */
    use ControllerRelationTrait;
}