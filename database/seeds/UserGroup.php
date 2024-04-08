<?php


use Phinx\Seed\AbstractSeed;

class UserGroup extends AbstractSeed
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
                'id' => 1,
                'name' => '普通用户',
                'description' => '普通用户',
                'img' => '/static/index/images/user_group_1.png',
                'status' => 1
            ],
            [
                'id' => 2,
                'name' => '白银会员',
                'description' => '白银会员',
                'img' => '/static/index/images/user_group_2.png',
                'status' => 1
            ],
            [
                'id' => 3,
                'name' => '黄金会员',
                'description' => '黄金会员',
                'img' => '/static/index/images/user_group_3.png',
                'status' => 1
            ]
        ];
        $msg = '添加用户角色成功.' . "\n";
        $this->execute('truncate user_group');
        $this->insert('user_group', $data);
        print($msg);
    }
}
