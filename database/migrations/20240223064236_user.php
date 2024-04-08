<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class User extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('user', ['comment' => '用户', 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
        $table
            ->addColumn('group_id', 'integer', ['signed' => false, 'limit' => 10, 'default' => 1, 'comment' => '组id'])
            ->addColumn('app_id', 'string', ['limit' => 11, 'default' => '', 'comment' => '用户app_id | 平台id'])
            ->addColumn('app_secret', 'string', ['limit' => 32, 'default' => '', 'comment' => '用户app_secret'])
            ->addColumn('username', 'string', ['limit' => 50, 'default' => '', 'comment' => '账号'])
            ->addColumn('password', 'string', ['limit' => 255, 'default' => '', 'comment' => '密码'])
            ->addColumn('password_salt', 'string', ['limit' => 255, 'default' => '', 'comment' => '密码盐'])
            ->addColumn('email', 'string', ['limit' => 50, 'default' => '', 'comment' => '邮箱地址'])
            ->addColumn('mobile', 'string', ['limit' => 11, 'default' => '', 'comment' => '手机号'])
            ->addColumn('nickname', 'string', ['limit' => 20, 'default' => '', 'comment' => '昵称'])
            ->addColumn('avatar', 'string', ['limit' => 255, 'default' => '/static/index/images/avatar.png', 'comment' => '头像'])
            ->addColumn('qq', 'string', ['limit' => 20, 'default' => '', 'comment' => 'QQ'])
            ->addColumn('wechat', 'string', ['limit' => 64, 'default' => '', 'comment' => '微信号'])
            ->addColumn('sex', 'boolean', ['signed' => false, 'limit' => 1, 'default' => 0, 'comment' => '性别 0未知，1男，2女'])
            ->addColumn('score', 'integer', ['signed' => false, 'limit' => 30, 'default' => 0, 'comment' => '积分'])
            ->addColumn('money', 'decimal', ['precision' => 30, 'scale' => 4, 'default' => 0, 'comment' => '钱包（单位：元）'])
            ->addColumn('address', 'string', ['limit' => 255, 'default' => '', 'comment' => '地址'])
            ->addColumn('invite_id', 'integer', ['signed' => false, 'limit' => 10, 'default' => 0, 'comment' => '邀请人'])
            ->addColumn('status', 'boolean', ['signed' => false, 'limit' => 1, 'default' => 1, 'comment' => '是否启用'])
            ->addColumn('create_time', 'integer', ['signed' => false, 'limit' => 10, 'default' => 0, 'comment' => '创建时间'])
            ->addColumn('update_time', 'integer', ['signed' => false, 'limit' => 10, 'default' => 0, 'comment' => '更新时间'])
            ->addColumn('delete_time', 'integer', ['signed' => false, 'limit' => 10, 'default' => 0, 'comment' => '删除时间'])
            ->addIndex('group_id')
            ->addIndex(['app_id'], ['unique' => true])
            ->addIndex(['username'], ['unique' => true])
            ->addIndex('score')
            ->addIndex('money')
            ->addIndex('invite_id')
            ->addIndex('status')
            ->addIndex('create_time')
            ->addIndex('update_time')
            ->addIndex('delete_time')
            ->create();
    }
}
