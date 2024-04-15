<?php

use PhpOffice\PhpSpreadsheet\{IOFactory, Shared\Date, Spreadsheet, Style\Alignment};
use support\{Log, Cache, Response};
use think\facade\Db;
use think\Model;
use think\Validate;
use Webman\Event\Event;

if (!function_exists('_input')) {
    /**
     * Get request parameters, if no parameter name is passed, an array of all values is returned, default values is supported
     * @param string|null $param param's name
     * @param mixed|null $default default value
     * @return mixed|null
     */
    function _input(string $param = null, $default = null, $filter = null)
    {
        $input = is_null($param) ? request()->all() : request()->input($param, $default);
        if ($filter && function_exists($filter)) {
            $input = $filter($input);
        }
        return $input;
    }
}

if (!function_exists('load_yaml')) {
    /**
     * load yaml file
     * @param string $filename
     * @param string $suffix
     * @return array
     */
    function load_yaml(string $filename = 'application', string $suffix = 'yml'): array
    {
        //加载yaml文件
        $config = \Symfony\Component\Yaml\Yaml::parseFile(BASE_PATH . DIRECTORY_SEPARATOR . $filename . '.' . $suffix);
        //加载链接库
        if(isset($config['profile']['active']) && file_exists($active = BASE_PATH . DIRECTORY_SEPARATOR . $filename . '-' . $config['profile']['active'] . '.'  . $suffix)) {
            $config = array_merge($config, \Symfony\Component\Yaml\Yaml::parseFile($active));
        }
        return $config;
    }
}

if (!function_exists('all_config')) {
    /**
     * all config
     * @param string|null $config
     * @param $default
     * @return null
     */
    function all_config(string $config = null, $default = null)
    {
        $env = ENV ?? $default;
        if($env !== null) foreach (explode('.',strtolower($config)) as $c) {
            if($env !== null) {
                $env = $env[$c] ?? $env[strtoupper($c)] ?? $default;
            }
        }
        return $env;
    }
}

if (!function_exists('server_config')) {
    /**
     * server config
     * @param string|null $config
     * @param null|string|integer|object|array $default
     * @return null|string|integer|object|array
     */
    function server_config(string $config = null, $default = null)
    {
        return all_config($config ? ("server.{$config}"): 'server', $default);
    }
}

if (!function_exists('database_config')) {
    /**
     * database config
     * @param string|null $config
     * @param $default
     * @return mixed|null
     */
    function database_config(string $config = null, $default = null)
    {
        return all_config($config ? ("datasource.{$config}"): 'datasource', $default);
    }
}

if (!function_exists('redis_config')) {
    /**
     * redis config
     * @param string|null $config
     * @param $default
     * @return mixed|null
     */
    function redis_config(string $config = null, $default = null)
    {
        return all_config($config ? ("redis.{$config}"): 'redis', $default);
    }
}

if (!function_exists('cache_config')) {
    /**
     * cache config
     * @param string|null $config
     * @param $default
     * @return mixed|null
     */
    function cache_config(string $config = null, $default = null)
    {
        return all_config($config ? ("cache.{$config}"): 'cache', $default);
    }
}

if (!function_exists('socket_config')) {
    /**
     * socket config
     * @param string|null $config
     * @param $default
     * @return mixed|null
     */
    function socket_config(string $config = null, $default = null)
    {
        return all_config($config ? ("socket.{$config}"): 'socket', $default);
    }
}

if (!function_exists('rpc_config')) {
    /**
     * rpc config
     * @param string|null $config
     * @param $default
     * @return mixed|null
     */
    function rpc_config(string $config = null, $default = null)
    {
        return all_config($config ? ("rpc.{$config}"): 'rpc', $default);
    }
}

if (!function_exists('auth_config')) {
    /**
     * auth config
     * @param string|null $config
     * @param $default
     * @return mixed|null
     */
    function auth_config(string $config = null, $default = null)
    {
        return all_config($config ? ("auth.{$config}"): 'auth', $default);
    }
}

if (!function_exists('wechat_config')) {
    /**
     * WeChat config
     * @param string|null $config
     * @param $default
     * @return mixed|null
     */
    function wechat_config(string $config = null, $default = null)
    {
        return all_config($config ? ("wechat.{$config}"): 'wechat', $default);
    }
}

if (!function_exists('encrypt_password')) {
    /**
     * 密码加密
     * @param $password
     * @return string
     */
    function encrypt_password($password): string
    {
        return base64_encode(password_hash($password, PASSWORD_ARGON2_DEFAULT_THREADS));
    }
}

if (!function_exists('verify_password')) {
    /**
     * 验证密码
     * @param $password
     * @param $encryptedPassword
     * @return bool
     */
    function verify_password($password, $encryptedPassword): bool
    {
        return password_verify($password, base64_decode($encryptedPassword));
    }
}

if (!function_exists('url')) {
    /**
     * url函数
     * @param string $url
     * @param array $vars
     * @param bool $suffix
     * @param bool $domain
     * @return string
     */
    function url(string $url = '', array $vars = [], bool $suffix = false, bool $domain = false): string
    {
        if(count(explode('/', $url = ltrim(str_replace(['\\'], '/', $url), '/'))) === 1) {
            $urlPathArr = explode('/', ltrim(request()->path(), '/'));
            $urlPathArr[count($urlPathArr) - 1] = $url;
            $url = implode('/', $urlPathArr);
        }
        return ($domain ? request()->domain(): '') . '/' . $url . ($suffix ? '.html': '') . ($vars ? '?' . http_build_query($vars): '');
    }
}

if (!function_exists('parse_name')) {
    /**
     * 字符串命名风格转换
     * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
     * @param string $name 字符串
     * @param int $type 转换类型
     * @param bool $ucFirst
     * @return string
     */
    function parse_name(string $name, int $type = 0, bool $ucFirst = true): string
    {
        if ($type) {
            $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            }, $name);

            return $ucFirst ? ucfirst($name) : lcfirst($name);
        }

        return strtolower(trim(preg_replace('/[A-Z]/', '_\\0', $name), '_'));
    }
}

if (!function_exists('get_directory_child_classes')) {
    /**
     * 获取指定目录下的所有子类
     * @throws ReflectionException
     */
    function get_directory_child_classes($parentClassName, $directory = null): array
    {
        // 存储继承自指定父类的子类
        $childClasses = [];
        // 遍历每个 PHP 文件
        foreach (glob(app_path() . ($directory ?? '/*.php')) as $file) {
            // 导入 PHP 文件
            require_once $file;
            // 获取当前文件中定义的所有类
            $classes = get_declared_classes();
            // 遍历每个类
            foreach ($classes as $class) {
                // 使用反射获取类的信息
                $classReflection = new ReflectionClass($class);
                // 检查类是否是指定父类的子类
                if ($classReflection->isSubclassOf($parentClassName)) {
                    // 子类继承自指定父类，将其添加到结果数组中
                    $childClasses[] = $class;
                }
            }
        }
        return $childClasses;
    }
}

if (!function_exists('get_class_access_methods')) {
    /**
     * 获取类所有方法
     * @throws ReflectionException
     */
    function get_class_access_methods($className, $accessMode = ReflectionMethod::IS_PUBLIC): array
    {
        $classMethods = [];
        // 使用反射获取类
        $classReflection = new ReflectionClass($className);
        // 获取类的所有方法
        $methods = $classReflection->getMethods($accessMode);
        // 遍历所有公共方法
        foreach ($methods as $method) {
            // 排除以双下划线开头的方法（魔术方法）
            if (strpos($method->getName(), '__') !== 0) {
                $classMethods[] = $method->getName();
            }
        }
        return $classMethods;
    }
}

if (!function_exists('setting')) {
    /**
     * 设置相关助手函数
     */
    function setting(string $name, $default = null)
    {
        $name = explode('.', $name);
        if($name) {
            $settingGroupInfo = (new \app\common\model\SettingGroup())->where('code', $name[0])->findOrEmpty();
            $where = [
                'setting_group_id' => $settingGroupInfo['id']
            ];
            $settingModel = new \app\common\model\Setting();
            if(isset($name[1])) {
                $where['code'] = $name[1];
                $setting = $settingModel->where($where)->findOrEmpty();
                if(isset($name[2])) {
                    return array_column($setting->toArray()['content'] ?? [], 'content', 'field')[$name[2]] ?? $default;
                }
                return array_column($setting->toArray()['content'], 'content', 'field') ?? $default;
            } else {
                $data = [];
                $setting = $settingModel->where($where)->select()->toArray();
                foreach ($setting as $s) {
                    $data[$s['code']] = array_column($s['content'], 'content', 'field');
                }
                return $data;
            }
        }
        return $default;
    }

}

if (!function_exists('console')) {
    /**
     * 控制台数组转成可视化表格供查看
     * @param array|string|object $data
     * @return string
     * @example
     * 输出：
     * $data = [
     *      ['id' => 1, 'name' => 'shiroi'],
     *      ['id' => 2, 'name' => '卢本伟']
     * ];
     * 结果：
     * ┌────┬────────────────────┐
     * │ ID │         NAME       │
     * ├────┼────────────────────┤
     * │ 1  │ shiroi             │
     * │ 2  │ 卢本伟              │
     * └────┴────────────────────┘
     */
    function console($data): string
    {
        return (new \MathieuViossat\Util\ArrayToTextTable(to_array($data)))->getTable();
    }
}

if (!function_exists('to_array')) {
    /**
     * json字符串转换数组
     */
    function to_array($data)
    {
        if (is_json($data)) {
            $dataInfo = json_decode($data, true, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
        }
        if (is_object($data) || is_array($data)) {
            $dataInfo = json_decode(json_encode($data), true);
        }

        return $dataInfo ?? $data;
    }
}

if (!function_exists('is_json')) {
    /**
     * 校验json字符串
     */
    function is_json($data): bool
    {
        if (empty($data)) return false;
        if (is_numeric($data)) return false;
        if(gettype($data) !== 'string') return false;
        try {
            //校验json格式
            json_decode($data, true);
            return JSON_ERROR_NONE === json_last_error();
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('to_json')) {
    /**
     * json数组转字符串
     */
    function to_json($data)
    {
        if (is_array($data)) {
            $dataInfo = json_encode($data, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
        }
        if (is_object($data)) {
            $dataInfo = json_encode($data);
        }
        return $dataInfo ?? $data;
    }
}

if (!function_exists('to_object')) {
    /**
     * 转换成object
     * @return void
     */
    function to_object($data)
    {
        if (!$data) {
            $data = (object)[];
        } else {
            if (is_array($data)) {
                $data = (object)$data;
            }
            if (is_json($data)) {
                $data = (object)to_array($data);
            }
        }
        return $data;
    }
}

if (!function_exists('is_datetime')) {
    /**
     * 判断是不是日期时间
     * @param string $datetime
     * @return false|int
     */
    function is_datetime(string $datetime)
    {
        return strtotime($datetime);
    }
}

if (!function_exists('is_timestamp')) {
    /**
     * 判断是不是时间戳
     * @param int $timestamp
     * @return false|int
     */
    function is_timestamp(int $timestamp)
    {
        if (strtotime(date('Y-m-d H:i:s', $timestamp)) === $timestamp) {
            return $timestamp;
        } else {
            return false;
        }
    }
}

if (!function_exists('auth_code')) {
    /**
     * @param string $string (字符串)
     * @param string $operation (DECODE=>解密 ENCODE=>加密)
     * @param string $key (密匙 默认平台building)
     * @param int $expiry (失效期) 秒:单位
     * @example
     * 加密: $encode = auth_code('测试','ENCODE','oa',30) //加密的字符串为:测试 加密类型:ENCODE 密钥为项目名:oa 失效期为:30秒
     * 解密: auth_code($encode,'DECODE','oa') //解密的字符串为:$encode 加密类型:ENCODE 密钥为项目名:oa
     * @return false|string
     */
    function auth_code(string $string, string $operation = 'DECODE', string $key = '', int $expiry = 0)
    {
        // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
        $CKeyLength = 4;
        // 密匙
        $key = md5($key);
        // 密匙a会参与加解密
        $keyA = md5(substr($key, 0, 16));
        // 密匙b会用来做数据完整性验证
        $keyB = md5(substr($key, 16, 16));
        // 密匙c用于变化生成的密文
        $keyC = ($operation == 'DECODE' ? substr($string, 0, $CKeyLength) : substr(md5(microtime()), -$CKeyLength));
        // 参与运算的密匙
        $cryptKey = $keyA . md5($keyA . $keyC);
        $key_length = strlen($cryptKey);
        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyB(密匙b)，
        //解密时会通过这个密匙验证数据完整性
        // 如果是解码的话，会从第$CKeyLength位开始，因为密文前$CKeyLength位保存 动态密匙，以保证解密正确
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $CKeyLength)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyB), 0, 16) . $string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rnDKey = array();
        // 产生密匙簿
        for ($i = 0; $i <= 255; $i++) {
            $rnDKey[$i] = ord($cryptKey[$i % $key_length]);
        }
        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rnDKey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        // 核心加解密部分
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            // 从密匙簿得出密匙进行异或，再转成字符
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation == 'DECODE') {
            // 验证数据有效性，请看未加密明文的格式
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyB), 0, 16))
                return substr($result, 26);
            else
                return '';
        } else {
            // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
            // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
            return $keyC . str_replace('=', '', base64_encode($result));
        }
    }
}

if (!function_exists('create_tree')) {
    /**
     * 创建树型结构
     * @param $array
     * @param string $childKey
     * @param string $id
     * @param string $parent_id
     * @return array
     * @noinspection PhpArrayAccessCanBeReplacedWithForeachValueInspection
     */
    function create_tree($array, string $childKey = 'son', string $id = 'id', string $parent_id = 'parent_id'): array
    {
        //第一步 构造数据
        $items = [];
        foreach ($array as $value) {
            $items[$value[$id]] = $value;
        }
        //第二部 遍历数据 生成树状结构
        $tree = [];
        foreach ($items as $key => $value) {
            if (isset($items[$value[$parent_id]])) {
                $items[$value[$parent_id]][$childKey][] = &$items[$key];
                if (isset($items[$value[$parent_id]][$childKey])) {
                    $items[$value[$parent_id]][$childKey] = array_values($items[$value[$parent_id]][$childKey]);
                }
            } else {
                $tree[] = &$items[$key];
            }
        }
        return $tree;
    }
}

if(!function_exists('both_field_exists')) {
    /**
     * 判断文本是否在(头部|尾部|当前文本)存在
     * @param string $string (文本内容)
     * @param string $subString （是否存在该字段）
     * @param int $type (0=>不指定头部或者尾部, 1=>头部, 2=>尾部)
     * @return array
     */
    function both_field_exists(string $string, string $subString, int $type = 0): array
    {
        $bool = false;
        $cut_content = $string;
        $cut_function = function () use ($string, $subString, $type, &$cut_content, &$bool) {
            if ($type == 0) {
                if ($bool = mb_strpos($string, $subString)) {
                    $cut_content = str_replace($subString, '', $string);
                }
            } elseif ($type == 1) {
                if ($bool = (mb_substr($string, 0, mb_strlen($subString)) === $subString)) {
                    $cut_content = mb_substr($string, mb_strlen($subString), (mb_strlen($string) - mb_strlen($subString)));
                }
            } elseif ($type == 2) {
                if ($bool = (mb_substr($string, mb_strpos($string, $subString)) === $subString)) {
                    $cut_content = mb_substr($string, 0, mb_strpos($string, $subString));
                }
            }
        };
        $cut_function();
        return compact('bool','cut_content');
    }
}

if (!function_exists('remove_both_str')) {
    /**
     * 判断文本是否在(头部|尾部|当前文本)存在
     * @param string $string (文本内容)
     * @param string $subString （是否存在该字段）
     * @param int $type (0=>不指定头部或者尾部, 1=>头部, 2=>尾部)
     * @return string
     */
    function remove_both_str(string $string, string $subString, int $type = 0): string
    {
        return both_field_exists($string, $subString, $type)['cut_content'];
    }
}

if (!function_exists('mk_dirs')) {
    /**
     * 递归创建文件夹
     * @param $path
     * @param int $mode 文件夹权限
     * @return bool
     */
    function mk_dirs($path, int $mode = 0777): bool
    {
        if (!is_dir(dirname($path))) {
            mk_dirs(dirname($path));
        }

        if (!file_exists($path)) {
            return mkdir($path, $mode);
        }

        return true;
    }
}

if (!function_exists('recursive_delete')) {
    /**
     * 递归删除目录
     */
    function recursive_delete($dir)
    {
        // 打开指定目录
        if ($handle = @opendir($dir)) {

            while (($file = readdir($handle)) !== false) {
                if (($file == ".") || ($file == "..")) {
                    continue;
                }
                if (is_dir($dir . '/' . $file)) { // 递归
                    recursive_delete($dir . '/' . $file);
                } else {
                    unlink($dir . '/' . $file); // 删除文件
                }
            }

            @closedir($handle);
            @rmdir($dir);
        }
    }
}

if (!function_exists('traverse_scanDir')) {
    /**
     * 递归遍历文件夹
     * @param bool $bool 是否递归
     * @param string $dir 文件夹路径
     * @return array
     */
    function traverse_scanDir(string $dir, bool $bool = true): array
    {
        $array = [];
        $handle = opendir($dir);
        while (($file = readdir($handle)) !== false) {
            # code...
            if ($file != '.' && $file != '..') {
                $child = $dir . '/' . $file;
                if (is_dir($child) && $bool) {
                    $array[$file] = traverse_scanDir($child);
                } else {
                    $array[] = $file;
                }
            }
        }

        return $array;
    }
}

if (!function_exists('copydirs')) {
    /**
     * 复制文件夹
     * @param string $source 源文件夹
     * @param string $dest 目标文件夹
     */
    function copy_dirs(string $source, string $dest)
    {

        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $handle = opendir($source);
        while (($file = readdir($handle)) !== false) {
            if ($file != "." && $file != "..") {
                if (is_dir($source . "/" . $file)) {
                    copy_dirs($source . "/" . $file, $dest . "/" . $file);
                } else {
                    copy($source . "/" . $file, $dest . "/" . $file);
                }
            }
        }

        closedir($handle);
    }
}

if (!function_exists('remove_empty_dir')) {
    /**
     * 删除空目录
     * @param string $dir 目录
     */
    function remove_empty_dir(string $dir)
    {
        try {
            if (is_dir($dir)) {
                $handle = opendir($dir);
                while (($file = readdir($handle)) !== false) {
                    if ($file != "." && $file != "..") {
                        remove_empty_dir($dir . "/" . $file);
                    }
                }

                if (!readdir($handle)) {
                    @rmdir($dir);
                }

                closedir($handle);
            }
        } catch (\Exception $e) {
        }
    }
}

if (!function_exists('response_unauthorized')) {
    /**
     * 未认证（未登录）
     * @param string|array $msg
     * @param mixed $data
     * @param int $code
     * @param array $header
     * @param array $options
     * @return Response
     */
    function response_unauthorized($msg = 'unauthorized', $data = [], int $code = 401, array $header = [], array $options = []): Response
    {
        return response_result(array_merge([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ], $options), server_config('response.http_code_sync') ? $code : 200, $header);
    }
}

if (!function_exists('response_forbidden')) {
    /**
     * 无权限
     * @param string|array $msg
     * @param mixed $data
     * @param int $code
     * @param array $header
     * @param array $options
     * @return Response
     */
    function response_forbidden($msg = 'forbidden', $data = [], int $code = 403, array $header = [], array $options = []): Response
    {
        return response_result(array_merge([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ], $options), server_config('response.http_code_sync') ? $code : 200, $header);
    }
}

if (!function_exists('response_success')) {
    /**
     * 操作成功
     * @param mixed $data
     * @param string|array $msg
     * @param int $code
     * @param array $header
     * @param array $options
     * @return Response
     */
    function response_success($data = [], $msg = 'success', int $code = 200, array $header = [], array $options = []): Response
    {
        return response_result(array_merge([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ], $options), server_config('response.http_code_sync') ? $code : 200, $header);
    }
}

if (!function_exists('response_error')) {
    /**
     * 操作失败
     * @param string|array $msg
     * @param mixed $data
     * @param int $code
     * @param array $header
     * @param array $options
     * @return Response
     */
    function response_error($msg = 'fail', $data = [], int $code = 500, array $header = [], array $options = []): Response
    {
        return response_result(array_merge([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ], $options), server_config('response.http_code_sync') ? $code : 200, $header);
    }
}

if (!function_exists('response_service_unavailable')) {
    /**
     * 系统维护中
     * @param string|array $msg
     * @param mixed $data
     * @param int $code
     * @param array $header
     * @param array $options
     * @return Response
     */
    function response_service_unavailable($msg = 'service unavailable', $data = [], int $code = 503, array $header = [], array $options = []): Response
    {
        return response_result(array_merge([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ], $options), server_config('response.http_code_sync') ? $code : 200, $header);
    }
}

if (!function_exists('response_error_client')) {
    /**
     * 客户端错误 例如提交表单的时候验证不通过，是因为客户填写端错误引起的
     * @param string|array $msg
     * @param mixed $data
     * @param int $code
     * @param array $header
     * @param array $options
     * @return Response
     */
    function response_error_client($msg = 'client error', $data = [], int $code = 400, array $header = [], array $options = []): Response
    {
        return response_result(array_merge([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ], $options), server_config('response.http_code_sync') ? $code : 200, $header);
    }
}

if (!function_exists('response_error_server')) {
    /**
     * 服务端错误
     * @param string|array $msg
     * @param mixed $data
     * @param int $code
     * @param array $header
     * @param array $options
     * @return Response
     */
    function response_error_server($msg = 'server error', $data = [], int $code = 500, array $header = [], array $options = []): Response
    {
        return response_result(array_merge([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ], $options), server_config('response.http_code_sync') ? $code : 200, $header);
    }
}

if (!function_exists('response_error_404')) {
    /**
     * 资源或接口不存在
     * @param string|array $msg
     * @param mixed $data
     * @param int $code
     * @param array $header
     * @param array $options
     * @return Response
     */
    function response_error_404($msg = '404 not found', $data = [], int $code = 404, array $header = [], array $options = []): Response
    {
        return response_result(array_merge([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ], $options), server_config('response.http_code_sync') ? $code : 200, $header);
    }
}

if (!function_exists('response_result')) {
    /**
     * Json response
     * @param $data
     * @param int $code
     * @param array $header
     * @param int $options
     * @return Response
     */
    function response_result($data, int $code = 200, array $header = [], int $options = JSON_UNESCAPED_UNICODE): Response
    {
        return new Response($code, array_merge(['Content-Type' => 'application/json'], $header), json_encode($data, $options));
    }
}

if (!function_exists('small_mount_to_underline')) {
    /**
     * 小驼峰转下划线
     * @param string $value
     * @return string
     */
    function small_mount_to_underline(string $value): string
    {
        return strtolower(preg_replace('/(?<=[a-z0-9])([A-Z])/', '_$1', $value));
    }
}


if (!function_exists('underline_to_small_mount')) {
    /**
     * 下划线转小驼峰
     * @param string $value
     * @param bool $is_capitalize (首字母是否大写)
     * @return string
     */
    function underline_to_small_mount(string $value, bool $is_capitalize = false): string
    {
        $value = ltrim(str_replace(" ", "", ucwords('_' . str_replace('_', " ", strtolower($value)))), '_');
        return $is_capitalize ? ucwords($value) : $value;
    }
}

if (!function_exists('like_data')) {
    /**
     * 传入你的数据，并设置你想要转换的字段，就可以将你的数据转换成你想要的格式
     * @param array|null|string|int|float|object $data
     * @param $analysis
     * @return array|null|string|int|float|object
     * @example
     *  -> array (一维数组)
     *     likeData($data, [
     *         'convert' => ['name' => 'body', 'no' => 'out_trade_no', 'cent_price' => 'total_fee'],
     *         'retain' => ['body', 'out_trade_no', 'total_fee', 'openid', 'notify_url', 'trade_type'],
     *     ])
     */
    function like_data($data, $analysis = null)
    {
        if (is_array($data)) {
            if ($analysis && is_array($analysis)) {
                //设置转换字段键名
                if ($analysis['convert'] ?? []) foreach ($data as $k => $d) if (isset($analysis['convert'][$k])) {
                    $data[$analysis['convert'][$k]] = $d;
                    unset($data[$k]);
                }
                //设置保留字段
                if ($analysis['retain'] ?? null) {
                    $data = array_intersect_key($data, array_flip($analysis['retain']));
                }
            }
        }
        return $data;
    }
}

if (!function_exists('rand_str')) {
    /**
     * 随机数
     * @param int $len
     * @param int $type (类型
     *      0 => 数字 + 大小写字母
     *      1 => 数字
     *      2 => 大写字母
     *      3 => 小写字母
     *      4 => 数字 + 大写字母
     *      5 => 数字 + 小写字母
     *      6 => 数字 + 特殊符号
     *      7 => 大写字母 + 特殊符号
     *      8 => 小写字母 + 特殊符号
     *      9 => 数字 + 大小写字母 + 特殊符号
     *     10 => 大小写字母 + 特殊符号
     * )
     * @return string
     */
    function rand_str(int $len = 6, int $type = 0): string
    {
        $n1 = [0,1,2,3,4,5,6,7,8,9];
        $n2 = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'];
        $n3 = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
        $n4 = ['~','!','@','#','$','%','^','&','*','(',')','_','-','+','=','{','}','[',']',';',':',"'",'"','|','\\','/','?','<',',','>','.'];
        $data = [];
        switch ($type) {
            case 0:
                $data = array_merge($n1,$n2,$n3);break;
            case 1:
                $data = $n1;break;
            case 2:
                $data = $n3;break;
            case 3:
                $data = $n2;break;
            case 4:
                $data = array_merge($n1,$n3);break;
            case 5:
                $data = array_merge($n1,$n2);break;
            case 6:
                $data = array_merge($n1,$n4);break;
            case 7:
                $data = array_merge($n3,$n4);break;
            case 8:
                $data = array_merge($n2,$n4);break;
            case 9:
                $data = array_merge($n1,$n2,$n3,$n4);break;
            case 10:
                $data = array_merge($n2,$n3,$n4);break;
        }
        $str = "";
        $data_length = count($data);
        for ($i = 0; $i < $len; $i++) {
            $str .= $data[rand(0,$data_length - 1)];
        }
        return $str;
    }
}

if (!function_exists('import')) {
    /**
     * 导入数据
     * @param string|Model $table
     * @param string $filePath
     * @param array $fields
     * @param string $columnType
     * @return array
     * @throws Exception
     */
    function import($table, string $filePath, array $fields = [], string $columnType = 'comment'): array
    {
        try {
            // 实例化Excel对象
            $fileType = IOFactory::identify($filePath);
            $reader = IOFactory::createReader($fileType);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
        } catch (\Exception $e) {
            return ['code' => 404, 'msg' => $e->getMessage()];
        }

        if($table instanceof Model) {
            $tableName = $table->getTable();
        } else {
            $tableName = $table;
        }

        // 默认获取第一张表
        $currentSheet = $spreadsheet->getSheet(0);
        $listSheetData = $currentSheet->toArray();

        // 数据量最小为1条
        $listRows = count($listSheetData);
        if ($listRows < 2) {
            return ['code' => 404, 'msg' => '数据行最小为2！'];
        }

        // 获取Excel首行预处理
        $excelFields = $listSheetData[0];

        array_shift($listSheetData);

        // 获取数据表字段注释
        $columns = Db::query("SHOW FULL COLUMNS FROM {$tableName}");
        $comments = array_column($columns, 'Comment', 'Field');
        //处理field=>value处理
        $excelValue = function ($field, $value) {
            if (in_array($field, ['create_time', 'update_time']) && !empty($value)) {
                $time = Date::excelToTimestamp($value);
                $value = strlen((string)$time) >= 12 ? $value : $time;
                if ($value <= 1) { // 负值时间戳
                    $value = time();
                }
            }
            return $value;
        };

        // 循环处理要插入的row
        $inserts = [];
        foreach ($listSheetData as $row => $item) {
            foreach ($excelFields as $key => $value) {
                // 默认首行为注释模式
                $field = array_search($value, $comments);
                if (strtolower($columnType) == 'comment') {
                    //判断字段是否存在
                    if(empty($fields) || array_search($value, $fields)) {
                        $inserts[$row][$field] = $excelValue($field, $item[$key]);
                    }
                } else {
                    if(empty($fields) || in_array($field, $fields)) {
                        $inserts[$row][$field] = $excelValue($value, $item[$key]);
                    }
                }
            }
        }

        // 判断是否有可导入的数据
        if (count($inserts) == 0) {
            return ['code' => 404, 'msg' => '没有可导入的数据！'];
        }

        try {
            // 批量插入数据
            if($table instanceof Model) {
                $table->saveAll($inserts);
            } else {
                Db::table($tableName)->insertAll($inserts);
            }
        } catch (\Exception $e) {
            return ['code' => 404, 'msg' => $e->getMessage()];
        }
        return ['code' => 200, 'msg' => '导入成功！'];
    }
}

if (!function_exists('export')) {
    /**
     * 导出数据
     * @param string $tableName
     * @param array $showColumn
     * @param array $where
     * @param int $page
     * @param int $limit
     * @param bool $isBinary
     * @return false|string
     * @throws Exception
     */
    function export(string $tableName, array $showColumn = [], array $where = [], int $page = 1, int $limit = 10, bool $isBinary = false, $uploadPath = null)
    {
        // 查询表数据
        $table = Db::table($tableName);
        $columns = Db::query("SHOW FULL COLUMNS FROM {$tableName}");
        $titles = like_data(array_column($columns, 'Comment', 'Field'), [
            'retain' => $showColumn,
        ]);
        // 支持导出空白数据 用于数据导入模板
        $data = $table->limit($limit)->page($page)->field($showColumn)->where(array_filter($where))->select()->toArray();
        // 使用表注释为文件名称
        $tableInfo = Db::query("SHOW TABLE STATUS LIKE '{$tableName}'");
        $Comment = $tableInfo[0]['Comment'] ?: '数据_';
        //定义文件名
        $fileName = $Comment . '-' . uniqid() . '.xlsx';
        //文件路径
        if($uploadPath) {
            $filePath = public_path($uploadPath . DIRECTORY_SEPARATOR);
        } else {
            $filePath = public_path('upload/file' . DIRECTORY_SEPARATOR . date('Y-m-d', time()) . DIRECTORY_SEPARATOR);
        }

        //是否导出为文件流
        if ($isBinary) {
            $content = export_thread($titles, $data, 'php://output');
        } else {
            $content = export_thread($titles, $data, $filePath . $fileName);
        }
        return $content;
    }
}


if (!function_exists('export_thread')) {

    /**
     * 导出
     * @param array $titles
     * @param array $data
     * @param string $filePath
     * @param string $columnType
     * @return false|string
     */
    function export_thread(array $titles, array $data, string $filePath, string $columnType = 'comment')
    {
        // 实例化Xls接口
        $spreadSheet = new Spreadsheet();
        $activeSheet = $spreadSheet->getActiveSheet();

        // 设表列头样式居中
        $activeSheet->getStyle('A1:AZ1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        try {
            $titCol = 'A';
            foreach ($titles as $key => $value) {
                $value = $columnType == 'comment' ? $value : $key;
                $activeSheet->setCellValue($titCol . '1', $value);
                $titCol++;
            }
            $rowLine = 2;
            foreach ($data as $item) {
                $rowCol = 'A';
                foreach ($item as $value) {
                    $activeSheet->setCellValue($rowCol . $rowLine, $value);
                    $rowCol++;
                }
                $rowLine++;
            }

            $writer = IOFactory::createWriter($spreadSheet, 'Xlsx');
            //输出流
            if ($filePath == 'php://output') {
                ob_start();
                $writer->save($filePath);
                $content = ob_get_clean();
            } else {
                mk_dirs(dirname($filePath));
                $writer->save($content = $filePath);
            }
            $spreadSheet->disconnectWorksheets();
            unset($spreadsheet);
            return $content;
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return false;
        }
    }
}

if (!function_exists('validate')) {
    /**
     * 验证数据
     * @access protected
     * @param $validate
     * @param array $message 提示信息
     * @param bool $batch 是否批量验证
     * @param bool $failException
     * @return Validate
     */
    function validate($validate, array $message = [], bool $batch = false, bool $failException = true): Validate
    {
        if (is_array($validate) || '' === $validate) {
            $v = new Validate();
            if (is_array($validate)) {
                $v->rule($validate);
            }
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }

            $class = str_contains($validate, '\\') ? $validate : parseClass('validate', $validate);

            $v = new $class();

            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        return $v->message($message)->batch($batch)->failException($failException);
    }
}

if (!function_exists('parseClass')) {
    /**
     * 解析应用类的类名
     * @access public
     * @param string $layer 层名 controller model ...
     * @param string $name 类名
     * @return string
     */
    function parseClass(string $layer, string $name): string
    {
        $name = str_replace(['/', '.'], '\\', $name);
        $array = explode('\\', $name);
        $class = underline_to_small_mount(array_pop($array), true);
        $path = $array ? implode('\\', $array) . '\\' : '';
        return 'app' . '\\' . $layer . '\\' . $path . $class;
    }
}

if (!function_exists('parsePath')) {
    /**
     * 解析路径(处理路径不相符问题)
     * @param string $name
     * @param array $parseSymbol
     * @return string
     */
    function parsePath(string $name, array $parseSymbol = ['\\']): string
    {
        return str_replace($parseSymbol, '/', $name);
    }
}

if (!function_exists('format_size')) {
    /**
     * 格式化文件大小单位
     * @param $size
     * @param string $delimiter
     * @return string
     */
    function format_size($size, string $delimiter = ''): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 5; $i++) {
            $size /= 1024;
        }
        return round($size, 2) . $delimiter . $units[$i];
    }
}

if (!function_exists('createTree')) {
    /**
     * 创建树型结构
     * @param array $array
     * @param string $childKey
     * @return array
     * @noinspection PhpArrayAccessCanBeReplacedWithForeachValueInspection
     */
    function createTree(array $array, string $childKey = 'son'): array
    {
        //第一步 构造数据
        $items = [];
        foreach($array as $value){
            $items[$value['id']] = $value;
        }
        //第二部 遍历数据 生成树状结构
        $tree = [];
        foreach($items as $key => $value){
            if(isset($items[$value['parent_id']])){
                $items[$value['parent_id']][$childKey][] = &$items[$key];
                if(isset($items[$value['parent_id']][$childKey])) {
                    $items[$value['parent_id']][$childKey] = array_values($items[$value['parent_id']][$childKey]);
                }
            }else{
                $tree[] = &$items[$key];
            }
        }
        return $tree;
    }
}

if(!function_exists('uuid')) {
    /**
     * 生成uuid
     * @return string
     */
    function uuid(): string
    {
        $chars = md5(uniqid(mt_rand(), true));
        return substr ( $chars, 0, 8 ) . '-'
            . substr ( $chars, 8, 4 ) . '-'
            . substr ( $chars, 12, 4 ) . '-'
            . substr ( $chars, 16, 4 ) . '-'
            . substr ( $chars, 20, 12 );
    }
}

if (!function_exists('user')) {
    /**
     * 获取当前用户
     * @property int $id
     * @noinspection PhpMultipleClassDeclarationsInspection
     * @return mixed|Model|\app\common\model\User $user
     */
    function user()
    {
        return request()->user();
    }
}

if (!function_exists('event')) {
    /**
     * 发布事件
     * @param string $event_name
     * @param $data
     * @return array|mixed|null
     */
    function event(string $event_name, $data)
    {
        return Event::emit($event_name, $data);
    }
}

if (!function_exists('l_array_exists')) {
    /**
     * 左侧是否存在指定数组值
     * @param array $array
     * @param string $characters
     * @return bool
     */
    function l_array_exists(array $array, string $characters = ''): bool
    {
        $isContained = false;
        foreach ($array as $v) {
            if(mb_strpos($characters, $v) === 0) {
                $isContained = true;
                break;
            }
        }
        return $isContained;
    }
}

if (!function_exists('r_array_exists')) {
    /**
     * 右侧是否存在指定数组值
     * @param array $array
     * @param string $characters
     * @return bool
     */
    function r_array_exists(array $array, string $characters = ''): bool
    {
        $isContained = false;
        foreach ($array as $v) {
            if (mb_substr($characters, -mb_strlen($v)) === $v) {
                $isContained = true;
                break;
            }
        }
        return $isContained;
    }
}

//load yaml file
define('ENV', load_yaml());

//database config
const DATABASE = ENV['datasource'] ?? [];

//server config
const SERVER = ENV['server'] ?? [];

//redis config
const REDIS = ENV['redis'] ?? [];

//cache config
const CACHE = ENV['cache'] ?? [];

//socket config
const SOCKET = ENV['socket'] ?? [];

//rpc config
const RPC = ENV['rpc'] ?? [];

//auth config
const AUTH = ENV['auth'] ?? [];

//WeChat config
const WECHAT = ENV['wechat'] ?? [];