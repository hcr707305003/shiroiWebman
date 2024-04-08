<?php
return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            // 服务器地址
            'hostname'        => DATABASE['host'] ?? '127.0.0.1',
            // 数据库名
            'database'        => DATABASE['database'] ?? 'test',
            // 数据库用户名
            'username'        => DATABASE['username'] ?? 'root',
            // 数据库密码
            'password'        => DATABASE['password'] ?? 'root',
            // 数据库连接端口
            'hostport'        => strval(DATABASE['port']) ?? '3306',
            // 数据库连接参数
            'params'          => [
                // 连接超时3秒
                \PDO::ATTR_TIMEOUT => 3,
            ],
            // 数据库编码默认采用utf8mb4
            'charset'         => DATABASE['charset'] ?? 'utf8mb4',
            // 数据库编码格式
            'collation'       => DATABASE['collation'] ?? 'utf8mb4_unicode_ci',
            // 数据库表前缀
            'prefix'          => DATABASE['prefix'] ?? '',
            // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
            'deploy'          => DATABASE['deploy'] ?? 0,
            // 数据库读写是否分离 主从式有效
            'rw_separate'     => DATABASE['rw_separate'] ?? false,
            // 读写分离后 主服务器数量
            'master_num'      => DATABASE['master_num'] ?? 1,
            // 指定从服务器序号
            'slave_no'        => DATABASE['slave_no'] ?? '',
            // 断线重连
            'break_reconnect' => DATABASE['break_reconnect'] ?? true,
            // 关闭SQL监听日志
            'trigger_sql'     => DATABASE['trigger_sql'] ?? true,
            // 开启字段缓存
            'fields_cache'    => DATABASE['fields_cache'] ?? false,
            // 字段缓存路径
            'schema_cache_path'=> runtime_path('schema') . DIRECTORY_SEPARATOR,
            // 从主库读取数据(查询时可能未插入从库数据，导致查询不到数据)
            'read_master'	  => DATABASE['read_master'] ?? true,
            // 自定义分页类
            'bootstrap'       =>  '',
            // 数据库类型
            'type'            => \Chance\Log\orm\think\MySqlConnection::class,
            // 指定查询对象
            "query"           => \Chance\Log\orm\think\Query::class,
            // Builder类
            "builder"         => \think\db\builder\Mysql::class,
            // 日志记录的主键
            "logKey"          => "id",
        ],
    ],
];
