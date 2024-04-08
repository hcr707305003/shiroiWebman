<?php

namespace support;

use support\view\ThinkPHP;

class Template extends ThinkPHP
{
    /**
     * @param string $template
     * @param array $vars
     * @param string|null $app
     * @param string|null $plugin
     * @return string
     */
    public static function render(string $template, array $vars, string $app = null, string $plugin = null): string
    {
        $request = request();
        $plugin = $plugin === null ? ($request->plugin ?? '') : $plugin;
        $app = $app === null ? $request->app : $app;
        $configPrefix = $plugin ? "plugin.$plugin." : '';
        $viewSuffix = config("{$configPrefix}view.options.view_suffix", 'html');
        $baseViewPath = $plugin ? base_path() . "/plugin/$plugin/app" : app_path();
        $viewPath = $app === '' ? "$baseViewPath/view/" : "$baseViewPath/$app/view/";
        $defaultOptions = [
            'view_path' => $viewPath,
            'cache_path' => runtime_path() . '/views/',
            'view_suffix' => $viewSuffix
        ];
        $options = array_merge($defaultOptions, config("{$configPrefix}view.options", []));
        $views = new \think\Template($options);
        ob_start();
        $vars = array_merge(static::$vars, $vars);
        $views->fetch($template, $vars);
        $content = ob_get_clean();
        static::$vars = [];
        return $content;
    }
}