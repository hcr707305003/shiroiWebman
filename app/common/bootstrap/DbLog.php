<?php

namespace app\common\bootstrap;

use Chance\Log\orm\illuminate\MySqlConnection;
use Illuminate\Database\Connection;
use Webman\Bootstrap;

class DbLog implements Bootstrap
{
    public static function start($worker)
    {
        Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
            return new MySqlConnection($connection, $database, $prefix, $config);
        });
    }
}