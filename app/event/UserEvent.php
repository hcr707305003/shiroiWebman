<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace app\event;

use think\Model;

class UserEvent
{
    /**
     * 新增用户
     * @param Model|array $user
     * @return void
     */
    function register($user)
    {
        dump('事件：新增用户');
    }

    /**
     * 用户登录
     * @param Model|array $user
     * @return void
     */
    function login($user)
    {
        dump('事件：用户登录');
    }

    /**
     * 用户启用
     * @param Model|array $user
     * @return void
     */
    function enable($user)
    {
        dump('事件：用户启用');
    }

    /**
     * 用户禁用
     * @param Model|array $user
     * @return void
     */
    function disable($user)
    {
        dump('事件：用户禁用');
    }

    /**
     * 删除用户
     * @param Model|array $user
     * @return void
     */
    function delete($user)
    {
        dump('事件：删除用户');
    }

    /**
     * 用户修改
     * @param Model|array $user
     * @return void
     */
    function update($user)
    {
        dump('事件：用户修改');
    }
}
