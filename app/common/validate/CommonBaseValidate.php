<?php

namespace app\common\validate;

use generate\validate\Color16;
use generate\validate\ComplexPassword;
use generate\validate\MiddlePassword;
use generate\validate\Number6;
use generate\validate\SimplePassword;
use Psr\SimpleCache\InvalidArgumentException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use support\Cache;
use think\facade\Db;
use think\Validate;

class CommonBaseValidate extends Validate
{
    /**
     * 验证规则定义 - 是否存在
     * @param $value
     * @param $field //isExists:数据库表:字段
     * @param $data
     * @return bool
     * @noinspection PhpMultipleClassDeclarationsInspection
     */
    public function isExists($value, $field, $data): bool
    {
        $classPath = explode('\\', self::class);
        $className = small_mount_to_underline(remove_both_str(end($classPath), 'Validate', 2));
        $fieldArr = explode(':', $field);
        $info = Db::table($fieldArr[0] ?? $className)
            ->where('delete_time', 0)
            ->where($fieldArr[1], $value)
            ->findOrEmpty();
        return !empty($info);
    }

    /**
     * 验证规则定义 - 是否为空
     * @param $value
     * @param $field //isEmpty:数据库表:字段
     * @param $data
     * @return bool
     * @noinspection PhpMultipleClassDeclarationsInspection
     */
    public function isEmpty($value, $field, $data): bool
    {
        $classPath = explode('\\', self::class);
        $className = small_mount_to_underline(remove_both_str(end($classPath), 'Validate', 2));
        $fieldArr = explode(':', $field);
        $info = Db::table($fieldArr[0] ?? $className)
            ->where('delete_time', 0)
            ->where($fieldArr[1], $value)
            ->findOrEmpty();
        return empty($info);
    }

    /**
     * 验证规则定义 - 手机验证码校验
     * @param $value
     * @param $rule //自定义手机号字段 格式: checkCode:手机号字段key
     * @param $data
     * @return bool|string
     * @throws InvalidArgumentException
     * @noinspection PhpDynamicAsStaticMethodCallInspection
     */
    protected function checkCode($value, $rule, $data)
    {
        $rules = explode(':', $rule);
        $mobileKey = empty($rules[0]) ? 'mobile' : $rules[0];
        $mobile = (string)($data[$mobileKey] ?? '');

        if(!$mobile){
            return '手机号不能为空';
        }

        if ((Cache::get($mobile) <> $value) && !sms_config('test_mode', false)){
            return false;
        }
        Cache::set($mobile, $value, 15);
        return true;
    }

    /**
     * 验证16进制颜色
     * @param $value
     * @param $rule
     * @param array $data
     * @param string $field
     * @param string $desc
     * @return bool|string
     */
    protected function color16($value, $rule, array $data = [], string $field = '', string $desc = '')
    {
        $pattern = '/^#?([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/';
        return preg_match($pattern, $value) ? true : $desc.(new Color16())->getMsg();
    }

    /**
     * 验证6位数字密码
     * @param $value
     * @param $rule
     * @param array $data
     * @param string $field
     * @param string $desc
     * @return bool|string
     */
    protected function number6($value, $rule, array $data = [], string $field = '', string $desc = '')
    {
        $pattern = '/^\d{6}$/';
        return preg_match($pattern, $value) ? true : $desc.(new Number6())->getMsg();
    }

    /**
     * 验证简单密码
     * @param $value
     * @param $rule
     * @param array $data
     * @param string $field
     * @param string $desc
     * @return bool|string
     */
    protected function simplePassword($value, $rule, array $data = [], string $field = '', string $desc = '')
    {
        $pattern = '/^(?=.*[a-zA-Z])(?=.*\d).{6,16}$/';
        return preg_match($pattern, $value) ? true : $desc.(new SimplePassword())->getMsg();
    }

    /**
     * 验证简单密码
     * @param $value
     * @param $rule
     * @param array $data
     * @param string $field
     * @param string $desc
     * @return bool|string
     */
    protected function middlePassword($value, $rule, array $data = [], string $field = '', string $desc = '')
    {
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,16}$/';
        return preg_match($pattern, $value) ? true : $desc.(new MiddlePassword())->getMsg();
    }

    /**
     * 验证简单密码
     * @param $value
     * @param $rule
     * @param array $data
     * @param string $field
     * @param string $desc
     * @return bool|string
     */
    protected function complexPassword($value, $rule, array $data = [], string $field = '', string $desc = '')
    {
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.[$@!%#?&]).{8,16}$/';
        return preg_match($pattern, $value) ? true : $desc.(new ComplexPassword())->getMsg();
    }

    /**
     * 检查数据库是否存在数据, 格式 => uniqueIsExist:表名,关联键
     * @param $value
     * @param $rule
     * @param array $data
     * @param string $field
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    protected function uniqueIsExist($value, $rule, array $data = [], string $field = ''): bool
    {
        if (is_string($rule)) {
            $rule = explode(',', $rule);
        }
        if (false !== strpos($rule[0], '\\')) {
            // 指定模型类
            $db = new $rule[0];
        } else {
            $db = $this->db->name($rule[0]);
        }
        $key = $rule[1] ?? $field;
        $map = [];
        if (strpos($key, '^')) {
            // 支持多个字段验证
            $fields = explode('^', $key);
            foreach ($fields as $key) {
                if (isset($data[$key])) {
                    $map[] = [$key, '=', $data[$key]];
                }
            }
        } elseif (isset($data[$field])) {
            $map[] = [$key, '=', $data[$field]];
        } else {
            $map = [];
        }
        $pk = !empty($rule[3]) ? $rule[3] : $db->getPk();

        if (is_string($pk)) {
            if (isset($rule[2])) {
                $map[] = [$pk, '<>', $rule[2]];
            } elseif (isset($data[$pk])) {
                $map[] = [$pk, '<>', $data[$pk]];
            }
        }
        if ($db->where($map)->field($pk)->find()) {
            return true;
        }

        return false;
    }
}