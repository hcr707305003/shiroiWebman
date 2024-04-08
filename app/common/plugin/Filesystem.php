<?php

namespace app\common\plugin;

use Shopwwi\WebmanFilesystem\Facade\Storage;
use Shopwwi\WebmanFilesystem\FilesystemFactory;
use Throwable;

class Filesystem extends Storage
{
    protected string $bucket_name = 'oa-disk';

    public function getConfig($adapter = 'public')
    {
        //设置默认config
        $config = config('plugin.shopwwi.filesystem.app');

        //查询数据库配置信息
        $setting = setting('cloud', []);

        //查询配置信息
        foreach ($config['storage'] as $a => $c) {
            if($adapter == 'oss') {
                $config['storage'][$a]['accessId'] = $setting['aliyun_oss']['appId'];
                $config['storage'][$a]['accessSecret'] = $setting['aliyun_oss']['appKey'];
                $config['storage'][$a]['endpoint'] = $setting['aliyun_oss']['region'];
                $config['storage'][$a]['bucket'] = $this->getBucketName();
                $config['storage'][$a]['url'] = "//{$setting['aliyun_oss']['host']}";
            }
        }
        return $config;
    }


    public static function instance($adapter = 'public')
    {
        if (!static::$_instance) {
            static::$_instance = (new \Shopwwi\WebmanFilesystem\Storage((new self)->getConfig($adapter)))
                ->adapter($adapter);
        }
        return static::$_instance;
    }

    /**
     * 压缩上传图片
     * @throws Throwable
     */
    public static function delete($filePath , $adapter = 'public'): void
    {
        $filesystem = FilesystemFactory::get($adapter, (new self)->getConfig($adapter));
        $filesystem->delete($filePath);
    }

    /**
     * @return string
     */
    public function getBucketName(): string
    {
        return $this->bucket_name;
    }
}