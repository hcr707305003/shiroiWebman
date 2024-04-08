<?php
/**
 * @noinspection PhpUndefinedFieldInspection
 * @noinspection PhpExpressionResultUnusedInspection
 */

/**
 * controller trait类
 * User: Shiroi
 * EMail: 707305003@qq.com
 * @example
 *  - 在控制器中定义 `protected static $service = 'app\admin\service\服务'`
 */

namespace app\admin\traits;

use app\common\exception\HttpResponseException;
use ReflectionClass;
use ReflectionException;
use support\Response;
use think\Validate;

trait ControllerTrait
{
    //获取get参数
    protected function getParam(): array
    {
        return $this->filterParam(request()->get());
    }

    //获取post参数
    protected function postParam(): array
    {
        return $this->filterParam(request()->post(), 'post');
    }

    //获取所有参数
    protected function allParam(): array {
        return request()->all();
    }

    protected function filterParam($params = [], $action = 'get'): array
    {
        foreach ($params as $k => $param) {
            if(in_array($k, $this->{"filter_{$action}_param"})) {
                unset($params[$k]);
            }
        }
        return array_filter($params);
    }

    protected function getAdmin($param = "")
    {
        return isset($this->admin) ? ($param ? $this->admin[$param] : null) : null;
    }

    /**
     * 列表
     */
    public function index(): Response
    {
        //前置操作
        $before = $this->beforeIndex();
        //前置order操作
        $order = $this->beforeOrderIndex();
        //数据库处理
        $data = (new static::$service)->getPaginate($this->beforeWhereIndex(), $this->show_index_field_key, [
            'list_rows' => $this->getAdmin('admin_list_rows'),
            'var_page'  => 'page',
            'query'     => $before['get']
        ], $order);

//        dd($before);
        //返回参数
        $this->assign($before);

        $data_arr = $this->afterIndex($data->toArray()['data']);

        $this->assign((function () use ($before, $data, &$data_arr) {
            $table = [];
            foreach ($data_arr as $k => $v) {
                $this->afterRaw('index', $v);
                $table[$k] = $this->show_index_raw;
                $this->show_index_raw = [];
            }
            return ['raw_data' => $table, 'data' => $data_arr, 'data_array'=>$data_arr, 'page'  => $data->render(), 'total' => $data->total()];
        })());

        //返回规范的翻页参数
        $this->assign([
            'data'  => $data_arr,
            'page'  => $data->render(),
            'total' => $data->total()
        ]);

        return $this->indexFetch();//默认同目录视图
    }

    protected function indexFetch($date = 'index'): Response
    {
        return $this->fetch($date);
    }

    protected function beforeOrderIndex(): array
    {
        return [];
    }

    protected function beforeWhereIndex($where = []): array
    {
        return $where;
    }

    protected function beforeIndexField($assignParam = []): ?array
    {
        return array_merge($assignParam,[
            'show_index_field_key' => $this->show_index_field_key,
            'show_index_field_value' => $this->show_index_field_value,
            'show_index_field' => &$this->show_index_field
        ]);
    }

    protected function beforeIndexInput($assignParam = []): ?array
    {
        return array_merge($assignParam,[
            'show_index_field_conditions' => &$this->show_index_field_conditions
        ]);
    }

    protected function beforeIndexConditions($assignParam = []): ?array
    {
        return array_merge($assignParam,[
            'show_index_input' => &$this->show_index_input
        ]);
    }

    //前置（参数的获取或者验证）
    protected function beforeIndex($assignParam = []): ?array
    {
        $assignParam = $this->beforeIndexField($assignParam);
        $assignParam = $this->beforeIndexInput($assignParam);
        $assignParam = $this->beforeIndexConditions($assignParam);

        return array_merge($assignParam,[
            'get' => $this->getParam(),
            'post' => $this->postParam(),
            'param' => $this->allParam()
        ]);
    }

    //后置（请求数据库后的数据处理）
    protected function afterIndex($data) {
        return $data;
    }


    /**
     * 详情
     */
    public function detail($id = null): Response
    {
        //设置id
        $id = input('id',$id);

        //前置操作
        $before = $this->beforeDetail($id);

        //返回参数
        $this->assign($before);

        //数据库处理
        $data = (new static::$service)->getInfo($before['where']);

        $data = $this->afterDetail($data);

        $this->assign((function () use ($data,$id) {
            $this->afterRaw('detail', $data);
            return ['data' => $data, 'raw_data' => $this->show_detail_raw];
        })());

        return $this->fetch();
    }

    protected function beforeDetailField($assignParam = []): ?array
    {
        return array_merge($assignParam,[
            'show_detail_field_key' => $this->show_detail_field_key,
            'show_detail_field_value' => $this->show_detail_field_value
        ]);
    }

    public function beforeDetailWhereId($id, $where = []): ?array
    {
        $where['id'] = $id;
        return $where;
    }

    //前置
    protected function beforeDetail($id): ?array
    {
        $assignParam = $this->beforeDetailField();

        return array_merge($assignParam,[
            'get' => $this->getParam(),
            'post' => $this->postParam(),
            'param' => $this->allParam(),
            'where' => $this->beforeDetailWhereId($id)
        ]);
    }

    //后置
    protected function afterDetail($data): array
    {
        return $data->toArray();
    }

    /**
     * 添加（get请求为页面，post请求为提交数据）
     */
    public function add(): Response
    {
        //前置操作
        $before = $this->beforeAdd();
        if (request()->isPost()) {
            $data = $before['data'];
            //加载验证器
            try {
                $this->loadValidate($data);
            } catch (ReflectionException|HttpResponseException $e) {}
            $result = $this->afterAdd((new static::$service)->create($data));
            return $this->addFetch($result);
        } else {
            $before['data'] = $this->afterAddView($this->beforePageHandle('add', $before['data']));
            //返回参数
            $this->assign((function () use ($before) {
                $this->afterRaw('add', $before['data']);
                return array_merge($before,['raw_data' => $this->show_add_raw]);
            })());
            //返回标签
            $this->assign([
                'tab' => $this->show_add_tab,
                'tab_content' => $this->show_add_tab_content,
            ]);
        }

        return $this->addFetch(__FUNCTION__);
    }

    protected function addFetch($date = 'add'): Response
    {
        if(request()->post()) {
            return $this->afterAddMessage($date);
        } else {
            return $this->fetch(input('template',$date));
        }
    }

    protected function afterAddView($data)
    {
        return $data;
    }

    public function afterAddMessage($result): Response
    {
        return $result ? admin_success('添加成功','',URL_RELOAD) : admin_error('添加失败', '', URL_CURRENT);
    }

    protected function beforeAddField($assignParam = []): ?array
    {
        return array_merge($assignParam,[
            'show_add_field_key' => $this->show_add_field_key,
            'show_add_field_value' => $this->show_add_field_value
        ]);
    }

    protected function beforeAddData(): ?array
    {
        return $this->postParam();
    }

    protected function beforeAdd(): array
    {
        $assignParam = $this->beforeAddField();
        return array_merge($assignParam,[
            'get' => $this->getParam(),
            'post' => $this->postParam(),
            'param' => $this->allParam(),
            'data' => $this->beforeData($this->beforeAddData())
        ]);
    }

    /**
     * @param string $fun
     * @param array $post_param
     * @return array
     */
    protected function beforePageHandle(string $fun = 'edit', array $post_param = []): array
    {
        //定义的field字段
        foreach ($post_param as $key => $value) {
            if(!in_array($key,$this->{"show_{$fun}_field_key"})) {
                unset($post_param[$key]);
            }
        }
        return $post_param;
    }

    protected function beforeAddRaw(): array
    {
        if(method_exists(self::class,'getFieldForm')) {
            $show_type = $this->show_add_type?:$this->show_type;
            foreach ($this->show_add_field_key as $k => $v) {
                $type = $show_type[$v]??'text';
                $conditions_str = "";

                if($type == 'select') foreach ($this->show_add_field_conditions[$v] as $ks => $vs)
                    $conditions_str .= "$ks||$vs"."\r\n";

                $this->show_add_raw[] = $this->getFieldForm($type,$this->show_add_field_value[$k],$v,'',$conditions_str);
            }
        }
        return $this->show_add_raw;
    }

    protected function afterAdd($data) {
        return $data->toArray();
    }

    /**
     * 修改
     * @param null $id
     * @return Response
     * @throws HttpResponseException|ReflectionException
     */
    public function edit($id = null): Response
    {
        //设置id
        $id = input('id',$id);

        //前置操作
        $before = $this->beforeEdit($id);

        if (request()->isPost()) {
            $data = $before['data'];
            //加载验证器
            $this->loadValidate(array_merge($data,compact('id')));
            //修改
            (new static::$service)->update($before['where'],$data);
            //后置修改post
            $this->afterEditData($before);
            //返回
            return $this->editFetch('修改成功');
        } else {
            $data = $this->afterEdit((new static::$service)->getInfo($this->beforeEditWhereId($id)));
            $this->assign((function () use ($data,$id) {
                $this->afterRaw('edit', $data);
                return ['data' => $data, 'raw_data' => $this->show_edit_raw, 'id' => $id];
            })());
            if (request()->isAjax()){
                //返回用户信息
                $res = [
                    'tab' => $this->show_edit_tab,
                    'tab_content' => $this->show_edit_tab_content,
                    'data' => $data,
                    'raw_data' => $this->show_edit_raw,
                    'id' => $id
                ];

                return admin_success('',$res);
            }else{
                //返回用户信息
                //返回标签
                $this->assign([
                    'tab' => $this->show_edit_tab,
                    'tab_content' => $this->show_edit_tab_content,
                ]);
            }
        }
        return $this->editFetch(__FUNCTION__);
    }


    protected function editFetch($date = 'edit'): Response
    {
        if(request()->post()) {
            return admin_success('修改成功', [], URL_RELOAD);
        } else {
            return $this->fetch(input('template',$date));
        }
    }

    protected function beforeEditField($assignParam = []): ?array
    {
        return array_merge($assignParam,[
            'show_edit_field_key' => $this->show_edit_field_key,
            'show_edit_field_value' => $this->show_edit_field_value
        ]);
    }

    protected function beforeEditData(): ?array
    {
        return $this->postParam();
    }

    protected function beforeEditWhereId($id, $where = []): ?array
    {
        $where['id'] = $id;
        return $where;
    }

    protected function beforeEdit($id): ?array
    {
        $assignParam = $this->beforeEditField();

        return array_merge($assignParam,[
            'get' => $this->getParam(),
            'post' => $this->postParam(),
            'param' => $this->allParam(),
            'where' => $this->beforeEditWhereId($id),
            'data' => $this->beforeData($this->beforeEditData()
            ),
        ]);
    }

    protected function afterEditData($before)
    {

    }

    protected function afterEdit($data): array
    {
        return $data->toArray();
    }

    protected function afterRaw($fun = 'edit',$data = []) {
        if(method_exists(self::class,'getFieldForm')) {
            $show_type = $this->{"show_{$fun}_type"}?:$this->show_type;
            foreach ($this->{"show_{$fun}_field_key"} as $k => $v) {
                $type = $show_type[$v]??'text';
                $conditions = $this->afterConditionsRaw($type,$fun,$v,$data[$v]??'');
                $this->{"show_{$fun}_raw"}[$v] = $this->{$fun == 'index' ? 'getFieldTable': 'getFieldForm'}($type,$this->{"show_{$fun}_field_value"}[$k],$v,$data[$v]??'',$conditions,$this->url_condition[$v]??'', $v, $data);
            }
        }
    }

    protected function afterConditionsRaw($type,$fun,$key,$value='')
    {
        $conditions_str = "";
        $conditions = $this->{"show_{$fun}_field_conditions"} ? array_merge($this->show_field_conditions, $this->{"show_{$fun}_field_conditions"}): $this->show_field_conditions;
        if($fun == 'index') {
            //select,switch
            if((($type == 'switch') || ($type == 'select')) && isset($conditions[$key])) {
                $conditions_str = $conditions[$key][strval($value)] ?? '';
            } else {
                $conditions_str = $value;
            }
        } else {
            if(isset($conditions[$key])) $conditions_str = $conditions[$key];
        }
        return $conditions_str;
    }

    /**
     * @param null $id
     * @return Response
     * @throws HttpResponseException|ReflectionException
     */
    public function del($id = null): Response
    {
        //设置id
        $id = input('id',$id);
        //前置操作
        $before = $this->beforeDel($id);
        //加载验证器
        $this->loadValidate(compact('id'));
        //获取删除id
        $del_ids = [];
        (new static::$service)->getLists($before['where'])->each(function ($info) use (&$del_ids) {
            //删除
            if ($info->delete()) {
                $del_ids[] = $info['id'];
                //删除后置
                $this->afterDel($info);
            }
        });
        return $del_ids ? admin_success('删除成功', [],URL_CLOSE_REFRESH_UI) : admin_error('删除失败');
    }

    protected function beforeDel($id): array
    {
        return [
            'get' => $this->getParam(),
            'post' => $this->postParam(),
            'param' => $this->allParam(),
            'where' => $this->beforeDelWhere($id)
        ];
    }

    protected function beforeDelWhere($id): array
    {
        return [
            'id' => $id
        ];
    }

    protected function afterDel($result) {
        return $result;
    }

    protected function beforeData($data = []): ?array
    {
        return $data;
    }

    protected function statusProperty(): string
    {
        return 'status';
    }

    protected function beforeStatusWhere($id): array
    {
        return [
            ['id', 'in', input('id',$id)]
        ];
    }

    /**
     * 开启
     */
    public function enable(): Response
    {
        $result = 0;
        (new static::$service)->getLists([[
            'id', 'in', request()->post('id')
        ]])->each(function ($item) use (&$result) {
            if($item->save(['status' => input('status', 1)])) {
                $result++; //自增
            }
        });
        return $result ? admin_success('操作成功', [], URL_RELOAD): admin_error();
    }

    /**
     * 禁用
     */
    public function disable(): Response
    {
        $result = 0;
        (new static::$service)->getLists([[
            'id', 'in', request()->post('id')
        ]])->each(function ($item) use (&$result) {
            if($item->save(['status' => input('status', 0)])) {
                $result++; //自增
            }
        });
        return $result ? admin_success('操作成功', [], URL_RELOAD): admin_error();
    }

    /**
     * 公共验证器
     * @param $data (要验证的数据)
     * @param string $method
     * @return void
     * @throws ReflectionException|HttpResponseException
     */
    protected function loadValidate($data, string $method = '')
    {
        if(property_exists(static::class,'validate')) {
            /** @var Validate $validate 验证器 */
            $validate = new static::$validate;
            //场景方法
            $method = $method ?: $this->getAction() . $this->getController(true);
            //获取属性
            $property = (new ReflectionClass(static::$validate))->getProperty('scene');
            // 设置私有属性为可访问
            $property->setAccessible(true);
            //获取所有场景
            $sceneList = $property->getValue($validate);
            if(isset($sceneList[$method])) {
//                $check = \validate(static::$validate)->scene($method)->check($data);
                $check = $validate->scene($method)->check($data);
                if (!$check) {
                    throw new HttpResponseException(admin_error(strval($validate->getError())));
                }
            }
        }
    }
}