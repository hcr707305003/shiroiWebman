<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class UserSetting extends AbstractMigration
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
        $table = $this->table('user_setting', ['comment' => '用户设置表', 'engine' => 'InnoDB', 'encoding' => 'utf8mb4']);
        $table
            //user_id
            ->addColumn('user_id', 'integer', ['signed' => false, 'limit' => 10, 'default' => 0, 'comment' => '用户id'])
            //client
            ->addColumn('client', 'string', ['limit' => 5, 'default' => 'pc', 'comment' => '客户端'])
            //code
            ->addColumn('code', 'string', ['limit' => 50, 'default' => '', 'comment' => '代码'])
            //名称
            ->addColumn('name', 'string', ['limit' => 50, 'default' => '', 'comment' => '名称'])
            ->addColumn('content', 'text', ['null' => true, 'limit' => MysqlAdapter::TEXT_LONG, 'comment' => 'json内容'])
            ->addColumn('extra_param', 'text', ['null' => true, 'limit' => MysqlAdapter::TEXT_LONG, 'comment' => '额外参数'])
            ->addColumn('option', 'text', ['null' => true, 'limit' => MysqlAdapter::TEXT_LONG, 'comment' => '选项：select,multi_select等类型该选项会存在值'])
            ->addColumn('type', 'string', ['limit' => 20, 'default' => 'text', 'comment' => '类型 select multi_select text list'])
            ->addColumn('description', 'string', ['limit' => 255, 'default' => '', 'comment' => '描述'])
            //默认字段
            ->addColumn('create_time', 'integer', ['signed' => false, 'limit' => 10, 'default' => 0, 'comment' => '创建时间'])
            ->addColumn('update_time', 'integer', ['signed' => false, 'limit' => 10, 'default' => 0, 'comment' => '更新时间'])
            ->addColumn('delete_time', 'integer', ['signed' => false, 'limit' => 10, 'default' => 0, 'comment' => '删除时间'])
            ->addIndex('user_id')
            ->addIndex('client')
            ->addIndex('code')
            ->addIndex('type')
            ->addIndex(['user_id','client','code'],['unique' => true])
            ->addIndex('delete_time')
            ->create();
    }
}
