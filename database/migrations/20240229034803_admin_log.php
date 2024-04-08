<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdminLog extends AbstractMigration
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
        $table = $this->table('admin_log', ['comment' => '后台操作日志', 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
        $table
            ->addColumn('admin_user_id', 'integer', ['signed' => false, 'limit' => 10, 'comment' => '用户'])
            ->addColumn('name', 'string', ['limit' => 30, 'default' => '', 'comment' => '操作'])
            ->addColumn('url', 'string', ['limit' => 100, 'default' => '', 'comment' => 'URL'])
            ->addColumn('log_method', 'string', ['limit' => 8, 'default' => '不记录', 'comment' => '记录日志方法'])
            ->addColumn('log_ip', 'string', ['limit' => 20, 'default' => '', 'comment' => '操作IP'])
            ->addColumn('create_time', 'integer', ['signed' => false, 'limit' => 10, 'default' => 0, 'comment' => '创建时间'])
            ->addColumn('update_time', 'integer', ['signed' => false, 'limit' => 10, 'default' => 0, 'comment' => '更新时间'])
            ->addColumn('delete_time', 'integer', ['signed' => false, 'limit' => 10, 'default' => 0, 'comment' => '删除时间'])
            ->create();
    }
}
