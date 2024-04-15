<?php

namespace process;

use Workerman\Connection\TcpConnection;

class Rpc
{
    public function onMessage(TcpConnection $connection, $data)
    {
        static $instances = [];
        if(is_json($data)) {
            $data = json_decode($data, true);
            $class = $data['class'] ?? null;
            $method = $data['method'] ?? null;
            $args = $data['args'] ?? [];
            $key = $data['key'] ?? null;
            //用于验证key
            if($class && $method && ($key == rpc_config('key')) && class_exists($class)) {
                if (!isset($instances[$class])) {
                    $instances[$class] = new $class; // 缓存类实例，避免重复初始化
                }
                $connection->send(call_user_func_array([$instances[$class], $method], $args));
            }
        }
    }
}