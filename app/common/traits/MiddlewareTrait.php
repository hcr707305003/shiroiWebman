<?php

namespace app\common\traits;

use app\common\exception\HttpResponseException;
use ReflectionClass;
use ReflectionException;

trait MiddlewareTrait
{
    /**
     * 格式化url地址
     * @param $url
     * @return string
     */
    protected function parse_url($url): string
    {
        $pathArr = explode('/', ltrim($url, '/'));
        $pathArr[2] = request()->getAction(); //转换为真实方法
        return implode('/', array_slice($pathArr,0,3));
    }

    /**
     * 反射获取指定属性
     * @param $request
     * @return array
     * @throws ReflectionException
     */
    protected function reflectionPropertyList($request): array
    {
        // 使用反射获取类的属性值
        $reflectionClass = new ReflectionClass($request->controller);
        $instance = $reflectionClass->newInstanceWithoutConstructor();
        //获取是否游客登录
        $is_visitor_property = $reflectionClass->getProperty('is_visitor');
        $is_visitor_property->setAccessible(true);
        /** @var int|string $is_visitor 是否游客登录（0=>否 1=>是） */
        $is_visitor = $is_visitor_property->getValue($instance) ?: 0;

        // 获取属性loginExcept的值
        $publicProperty = $reflectionClass->getProperty('loginExcept');
        $publicProperty->setAccessible(true); // 设置属性为可访问
        //处理登录忽略
        $login_except = $publicProperty->getValue($instance) ?: [];
        foreach ($login_except as $k => $uri) {
            $login_except[$k] = ltrim(parsePath($uri), '/');
        }

        return [
            'is_visitor' => $is_visitor,
            'login_except' => $login_except
        ];
    }

    /**
     * 检测用户权限
     * @param $tokenService
     * @param $token
     * @param $request
     * @return array|false[]
     */
    protected function checkUserToken($tokenService, $token, &$request): array
    {
        try {
            $result = $tokenService->checkToken($token);
            // 验证通过赋值用户ID
            $request->uid = (int)$result->getUid();
            return [$result];
        } catch (HttpResponseException $e) {
            return [false, $e->getMessage(), $e->getData(), $e->getCode()];
        }
    }

    protected function loadToken($field = 'token_field', $module = 'api')
    {
        $token_position = config("auth.{$module}.token_position");
        $token_field    = config("auth.{$module}.{$field}");
        if ($token_position === 'header') {
            $token = request()->header($token_field);
        } else {
            $token = request()->all()[$token_field] ?? request()->all()['token'] ?? null;
        }
        return $token;
    }
}