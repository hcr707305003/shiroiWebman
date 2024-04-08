<?php


use Phinx\Seed\AbstractSeed;

class AdminRole extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        $data = [
            [
                'id'          => 1,
                'name'        => '管理员',
                'description' => '后台管理员角色',
                'url'         => implode(',', range(1, 58)),
                'status'      => 1,
            ],
        ];
        $msg = '添加管理员角色成功.' . "\n";
        $this->execute('truncate admin_role');
        $this->insert('admin_role', $data);
        print($msg);
    }
}
