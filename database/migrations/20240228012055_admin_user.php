<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdminUser extends AbstractMigration
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
        $table = $this->table('admin_user', ['comment' => '后台用户', 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
        $table
            ->addColumn('username', 'string', ['limit' => 30, 'default' => '', 'comment' => '用户名'])
            ->addColumn('password', 'string', ['limit' => 255, 'default' => encrypt_password('123456'), 'comment' => '密码'])
            ->addColumn('nickname', 'string', ['limit' => 30, 'default' => '', 'comment' => '昵称'])
            ->addColumn('avatar', 'string', ['limit' => 255, 'default' => '/static/admin/images/avatar.png', 'comment' => '头像'])
            ->addColumn('role', 'string', ['limit' => 3210, 'default' => '', 'comment' => '角色'])
            ->addColumn('status', 'boolean', ['signed' => false, 'limit' => 1, 'default' => 1, 'comment' => '是否启用'])
            ->addColumn('create_time', 'integer', ['signed' => false, 'limit' => 10, 'default' => 0, 'comment' => '创建时间'])
            ->addColumn('update_time', 'integer', ['signed' => false, 'limit' => 10, 'default' => 0, 'comment' => '更新时间'])
            ->addColumn('delete_time', 'integer', ['signed' => false, 'limit' => 10, 'default' => 0, 'comment' => '删除时间'])
            ->addIndex(['username'], ['name' => 'index_username'])
            ->addIndex('status')
            ->create();
    }
}
