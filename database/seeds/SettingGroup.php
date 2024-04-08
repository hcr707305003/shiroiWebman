<?php


use Phinx\Seed\AbstractSeed;

class SettingGroup extends AbstractSeed
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
                "id" => 1,
                "module" => "admin",
                "name" => "后台设置",
                "description" => "后台管理方面的设置",
                "code" => "admin",
                "sort_number" => 1000,
                "icon" => "fa fa-adjust",
                "auto_create_menu" => 1,
                "auto_create_file" => 0,
                "is_forced_update" => false, //seed配置- 选填 - 是否覆盖更新原有数据
            ],
            [
                "id" => 2,
                "module" => "index",
                "name" => "前台设置",
                "description" => "前台方面的设置",
                "code" => "index",
                "sort_number" => 1000,
                "icon" => "fa fa-list",
                "auto_create_menu" => 0,
                "auto_create_file" => 0,
                "is_forced_update" => false, //seed配置- 选填 - 是否覆盖更新原有数据
            ],
            [
                "id" => 3,
                "module" => "cloud",
                "name" => "对象存储设置",
                "description" => "对象存储方面的设置",
                "code" => "cloud",
                "sort_number" => 1000,
                "icon" => "fa fa-cloud",
                "auto_create_menu" => 0,
                "auto_create_file" => 0,
                "is_forced_update" => false, //seed配置- 选填 - 是否覆盖更新原有数据
            ],
            [
                "id" => 4,
                "module" => "wechat",
                "name" => "微信设置",
                "description" => "微信方面的设置",
                "code" => "wechat",
                "sort_number" => 1000,
                "icon" => "fa fa-comment",
                "auto_create_menu" => 0,
                "auto_create_file" => 0,
                "is_forced_update" => false, //seed配置- 选填 - 是否覆盖更新原有数据
            ],
            [
                "id" => 5,
                "module" => "config",
                "name" => "基本设置",
                "description" => "前后台方面的基本设置",
                "code" => "config",
                "sort_number" => 1000,
                "icon" => "fa fa-wrench",
                "auto_create_menu" => 0,
                "auto_create_file" => 0,
                "is_forced_update" => false, //seed配置- 选填 - 是否覆盖更新原有数据
            ]
        ];
        $table = $this->table('setting_group');
        foreach ($data as $item) {
            $is_forced_update = $item['is_forced_update'] ?? false;
            unset($item['is_forced_update']);
            $setting_group_info = $this->fetchRow('select * from setting_group where `module` = "'.$item['module'].'"');
            if(!$setting_group_info || $is_forced_update) {
                $this->query('delete from setting_group where `id` = ' . $item['id']);
                $table->insert($item);
            }
        }
        $table->saveData();
    }
}
