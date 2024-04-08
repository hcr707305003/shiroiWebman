<?php


use Phinx\Seed\AbstractSeed;

class AdminUser extends AbstractSeed
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
        $develop_password = '123456';
        $admin_password   = '123456';

        $data = [
            [
                'id'       => 1,
                'username' => 'develop_admin',
                'nickname' => '开发管理员',
                'password' => base64_encode(password_hash($develop_password, 1)),
                'role'     => 1
            ],
            [
                'id'       => 2,
                'username' => 'super_admin',
                'nickname' => '超级管理员',
                'password' => base64_encode(password_hash($admin_password, 1)),
                'role'     => 1
            ]
        ];

        $msg = '开发管理员创建成功.' . "\n" . '用户名:develop_admin' . "\n" . '密码:'.$develop_password . "\n";
        $msg .= '超级管理员创建成功.' . "\n" . '用户名:super_admin' . "\n" . '密码:'.$admin_password . "\n";

        $this->execute('truncate admin_user');
        $table = $this->table('admin_user');
        foreach ($data as $item) {
            $table->insert($item);
        }
        $table->saveData();
        print($msg);
    }
}
