<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdminMenu extends AbstractMigration
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
        $table = $this->table('admin_menu', ['comment' => '后台菜单', 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
        $table
            ->addColumn('hash', 'string', ['limit' => 255, 'default' => '', 'comment' => '唯一标识'])
            ->addColumn('parent_id', 'integer', ['signed' => false, 'limit' => 10, 'default' => 0, 'comment' => '父级菜单'])
            ->addColumn('name', 'string', ['limit' => 50, 'default' => '', 'comment' => '名称'])
            ->addColumn('url', 'string', ['limit' => 100, 'default' => '', 'comment' => 'url'])
            ->addColumn('icon', 'string', ['limit' => 50, 'default' => 'fas fa-list', 'comment' => '图标'])
            ->addColumn('is_show', 'boolean', ['signed' => false, 'limit' => 1, 'default' => 1, 'comment' => '是否显示'])
            ->addColumn('is_top', 'boolean', ['signed' => false, 'limit' => 1, 'default' => 0, 'comment' => '是否为顶部菜单'])
            ->addColumn('sort_number', 'integer', ['signed' => false, 'limit' => 10, 'default' => 1000, 'comment' => '排序号'])
            ->addColumn('log_method', 'string', ['limit' => 8, 'default' => '不记录', 'comment' => '记录日志方法'])
            ->addColumn('create_time', 'integer', ['signed' => false, 'limit' => 10, 'default' => 0, 'comment' => '创建时间'])
            ->addColumn('update_time', 'integer', ['signed' => false, 'limit' => 10, 'default' => 0, 'comment' => '更新时间'])
            ->addColumn('delete_time', 'integer', ['signed' => false, 'limit' => 10, 'default' => 0, 'comment' => '删除时间'])
            ->addIndex(['url'], ['name' => 'index_url'])
            ->create();
    }
}
