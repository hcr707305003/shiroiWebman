<?php

namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * @property string $scene
 */
class WechatScanLoginAuth extends CommonBaseModel
{
    use SoftDelete;

    /**
     * 创建一个新的scene场景
     * @return mixed|null
     */
    public function create_new_scene()
    {
        $fuc = function ($scene) use (&$fuc) {
            if($this->where('scene', $scene)->findOrEmpty()->isExists()) {
                return $fuc(rand_str(32));
            }
            return $scene;
        };
        //创建场景
        return $this->create([
            'scene' => $scene = $fuc(rand_str(32))
        ])->isExists()? $scene: null;
    }
}