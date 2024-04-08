<?php /** @noinspection PhpUnused */
/** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 * User: Shiroi
 * EMail: 707305003@qq.com
 */

namespace app\common\plugin;

use think\Model;
use app\common\model\UserSetting as UserSettingModel;

/**
 * 处理用户设置插件
 * User: Shiroi
 * EMail: 707305003@qq.com
 */
class UserSetting
{
    /** @var int|string $user_id 用户id */
    protected $user_id = 0;

    /** @var ?string $client client */
    protected ?string $client = null;

    /** @var string $code code */
    protected string $code = '';

    /** @var string $name 名称 */
    protected string $name = '';

    /** @var string|array $content 内容 */
    protected $content;

    /** @var array $extra_param 额外参数 */
    protected array $extra_param = [];

    /** @var array $option 选项 */
    protected array $option = [];

    /** @var string $description 描述 */
    protected string $description = '';

    /** @var string $mode 操作方式默认覆盖(append=>追加 cover=>覆盖) */
    protected string $mode = 'cover';

    /** @var ?string $type 类型 */
    protected ?string $type = null;

    /** @var Model $model 设置默认名称（有新增的用户设置可以加在这里） */
    protected Model $model;

    protected array $defaultSetting = [
        //测试
        'test' => [
            'name' => '测试',
            'description' => '测试',
        ],
    ];

    public function __construct($user_id = 0)
    {
        $this->user_id = $user_id;
        $this->model = new UserSettingModel();
    }

    public function getSetting($field = [], $where = [])
    {
        /** @var UserSettingModel $setting */
        return $this->model->where([
            'user_id' => $this->user_id,
            'code' => $this->code
        ])->field($field)->where($where)->findOrEmpty();
    }

    public function saveSetting($where = [])
    {
        if($this->code) {
            /** @var UserSettingModel $setting */
            $setting = $this->getSetting([], array_merge(
                $this->client ? ['client' => $this->client]: [], $where));

            if ($setting->isEmpty()) {
                $setting->type = $this->type ?? 'text';
                $setting->user_id = $this->user_id;
                $setting->client = $this->client ?? 'pc';
                $setting->code = $this->code;
                $setting->name = $this->defaultSetting[$this->code]['name'] ?? $this->name;
                $setting->description = $this->defaultSetting[$this->code]['description'] ?? $this->description;
                $setting->content = $this->getConvertType($this->content, $this->type);
            } else {
                $setting->type = empty($this->type) ? $setting->type: ($this->type == $setting->type ? $setting->type: $this->type);
                if ($this->mode == 'append') {
                    //处理新数据追加时，左数据为null的情况
                    $setting->content = $this->settingAppend($setting->content, $this->content, $setting->type);
                } else {
                    $setting->content = $this->getConvertType($this->content, $setting->type);
                }
            }

            if ($this->extra_param) {
                $setting->extra_param = $this->extra_param;
            }

            if ($this->option) {
                $setting->option = $this->option;
            }

            return $setting->save();
        }
        return [];
    }

    /**
     * @return int|mixed|string
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param int|mixed|string $user_id
     */
    public function setUserId($user_id): self
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getClient(): string
    {
        return $this->client;
    }

    public function setClient($client): self
    {
        $this->client = $client;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array|string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param array|string $content
     */
    public function setContent($content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setMode(string $mode): self
    {
        $this->mode = $mode;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function setModel(Model $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function getConvertType($data, $type = 'text')
    {
        switch ($type) {
            case 'list':
                $data = to_array($data);
                if(!is_array($data)) $data = [$data];
                break;
            default:
        }
        return $data;
    }

    public function setConvertType($data, $type = 'text')
    {
        switch ($type) {
            case 'list':
                $data = to_json($data);
                break;
            default:
        }
        return $data;
    }

    public function settingAppend($leftData, $rightData, $type = 'text', $mode = 'right')
    {
        //向右追加还是向左追加
        if($mode == 'left') {
            $data = $leftData;
            $leftData = $rightData;
            $rightData = $data;
        }
        switch ($type) {
            case 'list':
                //类型不是数组对象则默认转一维数组
                $leftData = to_array($leftData);
                $rightData = to_array($rightData);
                if(!is_array($leftData)) $leftData = [$leftData];
                if(!is_array($rightData)) $rightData = [$rightData];
                $leftData = array_merge($leftData,$rightData);
                break;
            default:
                $leftData .= $rightData;
                break;
        }
        return $leftData;
    }

    public function getExtraParam(): array
    {
        return $this->extra_param;
    }

    public function setExtraParam(array $extra_param): self
    {
        $this->extra_param = $extra_param;
        return $this;
    }

    public function getOption(): array
    {
        return $this->option;
    }

    public function setOption(array $option): self
    {
        $this->option = $option;
        return $this;
    }
}
