<?php
/**
 * phinx的执行命令
 * @example
 *    php vendor/bin/phinx seed:create
 *    php vendor/bin/phinx seed:run -s AdminMenus
 *    php vendor/bin/phinx migrate
 *    php vendor/bin/phinx create
 */

return [
    "paths" => [
        "migrations" => "database/migrations",
        "seeds"      => "database/seeds"
    ],
    "environments" => [
        "default_migration_table" => "migrations",
        "default_environment"     => "mysql",
        "mysql" => [
            "adapter" => DATABASE['type'] ?? 'mysql',
            "host"    => explode(',', DATABASE['host'])[0] ?? '127.0.0.1',
            "name"    => explode(',', DATABASE['database'])[0] ?? 'test',
            "user"    => explode(',', DATABASE['username'])[0] ?? 'root',
            "pass"    => explode(',', DATABASE['password'])[0] ?? 'root',
            "port"    => explode(',', DATABASE['port'])[0] ?? "3306",
            "charset" => explode(',', DATABASE['charset'])[0] ?? "utf8mb4"
        ]
    ]
];