<?php

namespace app\api\traits;

use app\common\exception\HttpResponseException;
use support\Cache;

trait ApiThrottleTrait
{
    /** @var array 需要限制重复提交的action */
    protected array $throttleAction = [];

    protected string $apiThrottleKeyPrefix = 'api_throttle_';

    /**
     * 检查重复提交
     * @throws HttpResponseException
     */
    protected function checkThrottle()
    {
        // 如果当前方法要限制重复提交
        if (array_key_exists($this->path, $this->throttleAction)) {
            $key = $this->getThrottleKey();
            $cache_time = $this->throttleAction[$this->path];
            if (Cache::get($key)) {
                throw new HttpResponseException(api_error('重复提交'));
            } else {
                Cache::set($key, 2, $cache_time);
            }
        }
    }

    /**
     * 获取key
     * @return string
     */
    protected function getThrottleKey(): string
    {
        if ($this->uid > 0) {
            $key = $this->apiThrottleKeyPrefix . sha1($this->uid . $this->url);
        } else {
            $key = $this->apiThrottleKeyPrefix . sha1(request()->getRemoteIp() . request()->header('user-agent'));
        }
        return $key;
    }
}