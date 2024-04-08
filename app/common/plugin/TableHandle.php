<?php

namespace app\common\plugin;

use Phinx\Db\Adapter\{AdapterFactory, AdapterInterface, MysqlAdapter};
use DateTime;
use Exception;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Phinx\Db\Table;
use support\Db;

/**
 * 数据库table助手类
 * User: Shiroi
 * EMail: 707305003@qq.com
 */
class TableHandle
{
    /** @var array|string[][] 表单类型 */
    protected array $typeArr = [
        'integer' => [
            'date'
        ],
        'string' => [
            'text',
            'multiple_select'
        ],
        'text' => [
            'textarea',
            'image',
            'file'
        ],
        'decimal' => [
            'number'
        ],
        'boolean' => [
            'select'
        ]
    ];

    /** @var array|string[][] 数据库类型 */
    protected array $sqlTypeArr = [
        'integer' => [
            'int'
        ],
        'string' => [
            'varchar'
        ],
        'text' => [
            'text'
        ],
        'decimal' => [
            'decimal'
        ],
        'boolean' => [
            'tinyint'
        ]
    ];

    /** @var array|string[] 根据form类型设置多字段(例：字段名_设置的文本)  */
    protected array $formSetColumn = [
        'select' => [
            //field为数据库字段名(字段名_键名 = 新的字段名)
            'text' => [
                'key' => 'default.[int].name', //检索的键名
                'type' => 'string', //设置的数据库类型
                'option' => ['limit' => 255, 'default' => '', 'after' => 'id']
            ],
        ],
        'multiple_select' => [
            //field为数据库字段名(字段名_键名 = 新的字段名)
            'text' => [
                'key' => 'default.[int].name', //检索的键名
                'type' => 'string', //设置的数据库类型
                'option' => ['limit' => 255, 'default' => '', 'after' => 'id']
            ],
        ]
    ];

    /** @var array|string[] 忽略字段（用于更新字段时进行过滤） */
    protected array $ignoreColumns = [
        'id',
        'create_time',
        'update_time',
        'delete_time'
    ];

    /** @var array|array[] 默认字段 */
    protected array $defaultColumns = [
        'create_time' => [
            'type' => 'integer',
            'option' => ['limit' => 10, 'default' => 0, 'comment' => '创建时间']
        ],
        'update_time' => [
            'type' => 'integer',
            'option' => ['limit' => 10, 'default' => 0, 'comment' => '更新时间']
        ],
        'delete_time' => [
            'type' => 'integer',
            'option' => ['limit' => 10, 'default' => 0, 'comment' => '删除时间']
        ]
    ];

    /** @var array|string[] $dateType 时间格式对应的key */
    protected array $dateType = [
        'date' => 'Y-m-d H:i:s',
        'day' => 'Y-m-d',
        'month' => 'Y-m',
        'year' => 'Y'
    ];

    /** @var array|string[] $mysqlDateType mysql时间格式对应key */
    protected array $mysqlDateType = [
        'date' => '%Y-%m-%d %H:%i:%s',
        'day' => '%Y-%m-%d',
        'month' => '%Y-%m',
        'year' => '%Y'
    ];

    /** @var string $createTime 创建时间字段 */
    protected string $createTime = 'create_time';

    /** @var string $updateTime 更新时间字段 */
    protected string $updateTime = 'update_time';

    /** @var string $deleteTime 删除时间字段 */
    protected string $deleteTime = 'delete_time';

    /** @var TableHandle|null 静态调用类 */
    protected static ?TableHandle $tableHandle = null;

    protected ?FormDesign $formDesign = null;

    /** @var Builder 基础表 */
    protected Builder $mode;

    /** @var string 需要操作的表 */
    protected string $tableName = '';

    /** @var int 默认表id */
    protected int $tableId = 0;

    /** @var string $primaryKey 主键 */
    protected string $primaryKey = 'id';

    /** @var string 连接库 */
    protected string $connection = 'report'; //设置连接库，用于创建基础表

    /**
     * @param string $tableName (表名)
     */
    public function __construct(string $tableName = '')
    {
        if($tableName) {
            //实例化基础表
            $this->mode = $this->dbConfig()->table($tableName);
            //设置表名
            $this->tableName = $tableName;
        }

        $this->formDesign = FormDesign::getInstance();
    }

    /**
     * 静态调用
     * @param $tableName
     * @return TableHandle|null
     */
    public static function getInstance($tableName): ?TableHandle
    {
        if(self::$tableHandle == null){
            self::$tableHandle = new TableHandle($tableName);
        }
        return self::$tableHandle;
    }

    /** @var array $tagHeadType 标签头参与的类型 */
    protected array $tagViewType = [
        'select'
    ];

    /**
     * 动态获取标签头
     * @param array $formContent
     * @param array $tags (需要作为标签的field数组, field为key时)
     * @example
     * //案例一
     * $tags = ['field','field_1','field_2']
     * //案例二
     * $tags = [
     *     'field' => [ //field作为key时有更多丰富的操作
     *          'ignore' => [ //忽略的值|不作为tag显示的值 => 对应value
     *              1
     *          ]
     *      ]
     * ]
     * @return array
     */
    public function getTagView(array $formContent = [], array $tags = []): array
    {
        //初始化标签数据
        $tagData = [];
        //转换成带key值的表头
        $formContent = $this->formDesign->content($formContent, ['field', 'default', 'type', 'name'], 'all', 0, null, true, 'default', true);
        foreach ($tags as $key => $tag) {
            $field = is_int($key)? $tag: $key;
            $value = is_int($key)? []: $tag;
            //是否存在该字段
            if (isset($formContent[$field])) {
                //字段所有属性
                $fieldOption = $formContent[$field];
                //判断类型是否参与tag类型且default字段存在
                if(in_array($fieldOption['type'], $this->tagViewType) && isset($fieldOption['default']))
                    foreach ($fieldOption['default'] as $defaultKey => &$default) {
                        //过滤的|忽略的标签值
                        if(isset($value['ignore']) && in_array($default['value'], $value['ignore'])) {
                            unset($fieldOption['default'][$defaultKey]);continue;
                        }
                        //回显关联数据
                        if(($value['is_relation'] ?? false) && isset($default['relation']) && is_array($default['relation']))
                            foreach ($default['relation'] as &$v)
                                if (isset($formContent[$v])) $v = $formContent[$v];

                        //追加属性
                        if(isset($value[$default['value']])) {
                            $default = array_merge($default, $value[$default['value']]);
                        }
                    }
                //处理好的标签数据
                $tagData[] = $fieldOption;
            }
        }
        return $tagData;
    }

    /**
     * 获取查询结果
     * @param array $formContent
     * @param array $search
     * @return array
     * @throws Exception
     */
    public function getSearchWhere(array $formContent = [], array $search = []): array
    {
        //初始化搜索数据
        $searchWhereData = [];
        //转换成带key值的表头
        $formContent = $this->formDesign->content($formContent, ['field', 'default', 'type', 'name'], 'all', 0, null, true, 'default', true);
        foreach ($search as $field => $content) {
            if (isset($formContent[$field])) {
                //判断type
                //type为date时
                if(($formContent[$field]['type'] == 'date') && isset($content['between'])) {
                     if($content['between']['start_time'] ?? false) {
                         $start_time = input("{$field}_start_time", 0);
                         if(isset($content['start_time'])) {
                             $start_time = $this->getTimestamp($start_time,$content['start_time']['mode'] ?? 'month', $content['start_time']['mode_handle'] ?? 'start');
                         }
                         //大于等于开始时间
                         $searchWhereData[] =  [
                             $field,
                             '>=',
                             $start_time
                         ];
                     }
                     if($content['between']['end_time'] ?? false) {
                         $end_time = input("{$field}_end_time", strtotime(date('Y-m-d 23:59:59')));
                         if(isset($content['end_time'])) {
                             $end_time = $this->getTimestamp($end_time,$content['end_time']['mode'] ?? 'month', $content['end_time']['mode_handle'] ?? 'end');
                         }
                         //小于结束时间
                         $searchWhereData[] =  [
                             $field,
                             '<=',
                             $end_time
                         ];
                     }
                }

            }
        }
        return $searchWhereData;
    }

    /**
     * 时间条件后置处理，通过条件返回对应的时间戳
     * @param $time
     * @param string $mode
     * @param string $mode_handle
     * @return int|mixed
     * @throws Exception
     */
    protected function getTimestamp($time, string $mode = 'month', string $mode_handle = 'start')
    {
        if($time) {
            $dataTime = new DateTime(date($this->dateType[$mode], $time));
            switch ($mode_handle) {
                case 'start':
                    if($mode == 'month') $dataTime->modify('first day of this month');
                    if($mode == 'year') $dataTime->modify('first day of January');
                    $time = $dataTime->setTime(0,0,0)->getTimestamp();
                    break;
                case 'end':
                    if($mode == 'month') $dataTime->modify('last day of this month');
                    if($mode == 'year') $dataTime->modify('last day of December');
                    $time = $dataTime->setTime(23,59,59)->getTimestamp();
                    break;
            }
        }
        return $time;
    }

    /**
     * 动态获取搜索头
     * @param array $formContent
     * @param array $search
     * @return array
     */
    public function getSearchView(array $formContent = [], array $search = []): array
    {
        //初始化搜索数据
        $searchData = [];
        //转换成带key值的表头
        $formContent = $this->formDesign->content($formContent, ['field', 'default', 'type', 'name'], 'all', 0, null, true, 'default', true);
        foreach ($search as $field => $s) {
            if (isset($formContent[$field])) {
                $searchData[$field] = array_merge($formContent[$field], $s);
            }
        }
        foreach ($searchData as $s_key => $s) {
            //如果开启关联的话，则
            if($s['is_relation'] ?? false) {
                foreach ($s['default'] as $s_default_key => $s_default) {
                    foreach ($s_default['relation'] as $s_default_relation_key => $s_default_relation) {
                        $searchData[$s_key]['default'][$s_default_key]['relation'][$s_default_relation_key] = $formContent[$s_default_relation];
                    }
                }
            }
        }

        foreach ($searchData as $s1) {
            //如果开启关联的话，则
            if($s1['is_relation'] ?? false) {
                foreach ($s1['default'] as $s1_default) {
                    foreach ($s1_default['relation'] as $s_default_relation) {
                        if(isset($searchData[$s_default_relation['field']])) {
                            unset($searchData[$s_default_relation['field']]);
                        }
                    }
                }
            }
        }
        return array_values($searchData);
    }

    /**
     * 动态获取字段头
     * @param array $formContent
     * @param array $fields
     * @param array $where
     * @param array $fieldHead (根据formContent字段进行规则筛选)
     * //字段头内容
     * [
     *     'field',
     *     'mode', //额外补充的字段
     *     'sort_order', //额外补充的字段
     *     'type',
     *     'name',
     *     'alias',
     * ]
     * @param bool $is_show
     * @param string $hidden_field
     * @return array
     */
    public function getFieldView(array $formContent = [], array $fields = [], array $where = [], array $fieldHead = ['field', 'mode', 'sort_order', 'type', 'name', 'alias'], bool $is_show = true, string $hidden_field = 'after_show'): array
    {
        //初始化字段数据
        $fieldData = [];
        //字段默认值
        $defaultData = [];
        //转换成带key值的表头
        $formContent_1 = [];
        foreach ($formContent as $content) $formContent_1[$content['field']] = $content;
        $formContent = $formContent_1;

        //合并额外字段
        foreach ($fields as $field => $content) {
            $field_1 = $field;
            $field = is_int($field_1)? $content: $field_1;
            $content = is_int($field_1)? []: $content;

            if (isset($formContent[$field])) {
                if(isset($content['default'])) $defaultData[$field] = $content['default'];
                $fieldData[$field] = array_merge($content,$formContent[$field]);
            } else {
                $fieldData[$field] = array_merge(['field' => $field, 'type' => 'text'], $content);
            }
            $fieldData[$field] = array_merge($fieldData[$field],[
                'mode' => ($content['mode'] ?? 'field'),
                'sort_order' => ($content['sort_order'] ?? 'default')
            ]);
        }

        //默认全展示
        foreach ($fieldData as $field => $content) {
            if(!($content['is_show'] ?? true)) unset($fieldData[$field]);

            if(isset($fields[$field]['self_delete_where'])) {
                $is_delete_self = false;
                foreach ($fields[$field]['self_delete_where'] as $f_where => $d_where) {
                    if(($where[$f_where] ?? '') != $d_where) {
                        $is_delete_self = false;break;
                    } else {
                        $is_delete_self = true;
                    }
                }
                if($is_delete_self) {
                    unset($fieldData[$field]);
                }
            }

            //后置处理表头字段的显示隐藏
            if(!$is_show) {
                if (!($fields[$field][$hidden_field] ?? true)) {
                    unset($fieldData[$field]);
                }
            }
        }

        // 排序
        $sortValues = array_column($fieldData, 'sort', 'field');
        if($sortValues && (count($sortValues) > 1)) {
            // 如果某个元素没有 sort 键，将其 sort 值设置为最大值
            $maxSortValue = max($sortValues);
            foreach ($fieldData as $key => $item) if (!isset($item['sort'])) {
                $maxSortValue = $maxSortValue + 1;
                $item[$key]['sort'] = $maxSortValue;
                $sortValues[$key] = $maxSortValue;
            }
            // 根据 sort 键和数组的键进行排序
            array_multisort($sortValues, SORT_ASC, $fieldData, SORT_ASC);
        }
        return $this->formDesign->content(array_values($fieldData), $fieldHead);
    }

    /**
     * 前置列表
     * @param $content
     * @param $fields
     * @return array[]
     */
    protected function beforeGetList($content, $fields): array
    {
        $fieldData = [];
        $groupData = [];
        $orderData = [];
        $afterFieldData = [];
        $handleFieldData = [];
        foreach ($content as $field) {
            //处理查询的字段
            switch ($field['mode']) {
                case 'date':
                case 'day':
                case 'month':
                case 'year':
                    $fieldData[$field['field']] = "FROM_UNIXTIME({$field['field']}, '{$this->mysqlDateType[$field['mode']]}')";
                    break;
                case 'name':
                    //适用范围 select 插件
                    if($field['type'] == 'select') {
                        foreach ($field['default'] as $v) {
                            $afterFieldData[$field['field']]['value'][$v['value']] = $v['name'];
                        }

                        if($fields[$field['field']]['is_relation'] ?? false) {
                            foreach ($field['default'] as $v) {
                                $afterFieldData[$field['field']]['relation'][$v['value']] = $v['relation'] ?? [];
                                $afterFieldData[$field['field']]['all_relation'] = array_merge(
                                    $v['relation'] ?? [],
                                    $afterFieldData[$field['field']]['all_relation'] ?? []
                                );
                            }
                        }
                    }
                    $fieldData[$field['field']] = $field['field'];
                    break;
                case 'sum':
                case 'max':
                case 'min':
                    if($fields[$field['field']]['is_virtual'] ?? false) {
                        $whereField = [];
                        $where = $fields[$field['field']]['where'] ?? [];
                        $sumFunction = function ($key,$value) use (&$whereField) {
                            $k_1 = (count($value) > 1) ? $value[0]: '=';
                            $k_2 = (count($value) > 1) ? $value[1]: $value[0];
//                            if($k_2 instanceof \think\db\Raw) {
//                                $k_2 = $k_2->getValue();
//                            } else {
//                                $k_2 = "'{$k_2}'";
//                            }
                            $whereField[] = "{$key} {$k_1} {$k_2}";
                        };
                        if($where) {
                            foreach ($where as $k => $v) {
                                //判断字段
                                if(is_array($v)) {
                                    //一维数组处理方式
                                    if(count($v) == count($v, 1)){
                                        $sumFunction($k, $v);
                                    }else{ //二维数组处理方式
                                        foreach ($v as $value) {
                                            $sumFunction($k, $value);
                                        }
                                    }
                                } else {
                                    $whereField[] = "{$k} = {$v}";
                                }

                            }
                        } else {
                            $whereField[] = $field['field'];
                        }
                        $whereFieldStr = implode(' and ', $whereField);
                        if(isset($fields[$field['field']]['table'])) {
                            $field_table = "{$fields[$field['field']]['table']} as " . ($fields[$field['field']]['table_as'] ?? $fields[$field['field']]['table']);
                            $fieldData[$field['field']] = "COALESCE((select {$field['mode']}({$fields[$field['field']]['mode_field']}) from {$field_table} where ({$whereFieldStr})), 0)";
                        } else {
                            $fieldData[$field['field']] = "{$field['mode']}(CASE WHEN ({$whereFieldStr}) THEN {$fields[$field['field']]['mode_field']} ELSE 0 END)";
                        }
                    } else {
                        $fieldData[$field['field']] = "{$field['mode']}({$field['field']})";
                    }
                    break;
                case 'field_handle':
                    $handleFieldData[$field['field']] = $fields[$field['field']]['handle'];
                    break;
                default:
                    if(isset($fields[$field['field']]['cover_value'])) {
                        $fieldData[$field['field']] = "'{$fields[$field['field']]['cover_value']}'";
                    } else {
                        $fieldData[$field['field']] = $field['field'];
                    }
            }
            //处理分组的字段
            if($fields[$field['field']]['is_group'] ?? false) {
                switch ($field['mode']) {
                    case 'date':
                    case 'day':
                    case 'month':
                    case 'year':
                    $groupData[$field['field']] = "FROM_UNIXTIME({$field['field']}, '{$this->mysqlDateType[$field['mode']]}')";
                        break;
                    default:
                        $groupData[$field['field']] = $field['field'];
                }
            }

            //处理字段排序
            if($fields[$field['field']]['is_order'] ?? false) {
                $orderData[$field['field']] = $fields[$field['field']]['order_sort'] ?? 'asc';
            }
        }



        //处理后置字段处理
        foreach ($handleFieldData as $f => $h) {
            $f_arr = explode(' ', $h);
            foreach ($f_arr as $k => $v) {
                $f_arr[$k] = $fieldData[$v] ?? $v;
            }
            $fieldData[$f] = implode(' ', $f_arr);
        }
        //处理字段起别名
        foreach ($fieldData as $f => $h) {
            $fieldData[$f] = "({$h}) as {$f}";
        }

        return [
            'field' => $fieldData,
            'group' => $groupData,
            'order' => $orderData,
            'after_field' => $afterFieldData
        ];
    }

    /**
     * 后置列表
     * @param $content
     * @param $fields
     * @param $beforeList
     * @param $list
     * @return mixed
     */
    protected function afterGetList($content, $fields, $beforeList, $list)
    {

        //转换成带key值的表头
        $formContent_1 = [];
        foreach ($content as $c) $formContent_1[$c['field']] = $c;
        $content = $formContent_1;
//        dd($content,$fields, $list);

        //处理数据回值问题
        foreach (($list = $list->toArray()) as $k => $v) {
            foreach ($v as $key => $value) {
                //插件 select 参与计算
                if(($content[$key]['type'] ?? '') == 'select') {
                    $list[$k][$key] = $beforeList['after_field'][$key]['value'][$value] ?? '';
                }


                $list[$k][$key] = $this->getFiledTypeValue($list[$k][$key], $fields[$key]['field_type'] ?? 'string');
                //后置处理表头字段的显示隐藏
                if(!($fields[$key]['after_show'] ?? true))  unset($list[$k][$key]);
            }
        }
        return $list;
    }

    protected function getFiledTypeValue($value, $field_type = 'string')
    {
        switch ($field_type){
            case 'int':
            case 'integer':
                return intval($value);
            case 'float':
                return floatval($value);
        }

        return $value;
    }

    public function getList($content, $field, $where = [], $page = 1, $limit = 10)
    {
        //前置处理
        $beforeList = $this->beforeGetList(
            $this->getFieldView($content,$field,$where,['field', 'default', 'mode', 'sort_order', 'type', 'name', 'alias']),
            $field
        );

//        dd($this->mode->field(array_values($beforeList['field'] ?? []))->where($where)->page($page)->limit($limit)->order($beforeList['order'] ?? [])->group($beforeList['group'] ?? [])->fetchsql(true)->select());
        //后置处理
        return $this->afterGetList(
            $content,
            $field,
            $beforeList,
            \think\facade\Db::connect($this->connection)->name($this->tableName)->field(array_values($beforeList['field'] ?? []))->where($where)->page($page)->limit($limit)->order($beforeList['order'] ?? [])->group($beforeList['group'] ?? [])->select()
        );
    }

    public function getTotal($content, $field, $where = [])
    {
        return \think\facade\Db::connect($this->connection)->name($this->tableName)->where($where)->count();
    }

    /**
     * 获取时间区间
     * @param string $startTime 开始时间
     * @param string|null $endTime 结束时间 (默认当前天，起效类型:day)
     * @param string $type 类型 （day=>天 mouth=>月 year=>年）
     * @return array
     * @throws Exception
     */
    public function getTimeZones(string $startTime, string $endTime = null, string $type = 'mouth'): array
    {
        // 初始化结果数组
        $timestamps = [];

        // 设置开始时间和结束时间
        $startDateTime = (new DateTime($startTime))->setTime(0, 0);
        $endDateTime = (new DateTime($endTime ?: date('Y-m-d H:i:s')))->setTime(23,59,59);

        // 循环计算每天的开始时间和结束时间的时间戳
        while ($startDateTime <= $endDateTime) {
            $date = $startDateTime->format($this->dateType[$type]);
            //开始时间戳
            $startTimestamp = $startDateTime->setTime(0, 0)->getTimestamp();
            //结束时间戳
            if($type == 'mouth') $startDateTime->modify('last day of this month');
            if($type == 'year') $startDateTime->modify('last day of December');
            $endTimestamp = $startDateTime->setTime(23, 59, 59)->getTimestamp();
            // 添加到结果数组
            $timestamps[$date] = [
                'start' => $startTimestamp,
                'end' => $endTimestamp
            ];

            switch ($type){
                case 'day':
                    $startDateTime->modify('+1 day');
                    break;
                case 'mouth':
                    $startDateTime->modify('first day of next month');
                    break;
                case 'year':
                    $startDateTime->modify('+1 year')->modify('first day of January');
                    break;
            }
        }
        return $timestamps;
    }

    /**
     * 根据表单设计类生成表
     * @param array $formContent
     * @param string $comment 表注释
     * @param true $isUpdateAll 是否更新全字段 (true => 是, false => 否)
     * @return void
     */
    public function generate(array $formContent = [], string $comment = '', bool $isUpdateAll = true)
    {
        $table = (new Table($this->tableName, [
            'comment' => $comment ?: $this->tableName,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4'
        ], $this->setAdapter()));
        $columns = [];
        $formColumns = [];
        foreach ($formContent as $content) {
            $formColumns[$content['field']] = $content;
            $columns = array_merge($columns, $this->getColumns($content));
        }
        if($this->tableExists()) {
            //获取所有字段
            $existsColumns = $this->tableColumn();
            //删除id主键字段
            foreach ($this->ignoreColumns as $i) {
                if(isset($existsColumns[$i])) unset($existsColumns[$i]);
            }
            //获取差集字段(新增字段)
            foreach ($columns as $field => $column) {
                $method = $isUpdateAll ? 'changeColumn': 'addColumn';
                if($isUpdateAll) {
                    //全字段更新
                    if(!isset($existsColumns[$field])) $method = 'addColumn';
                } else {
                    //只更新不存在的字段
                    if(isset($existsColumns[$field])) {
                        if($column['type'] != $this->getFieldType($existsColumns[$field], $this->sqlTypeArr)) {
                            $method = 'changeColumn';
                        } else {
                            continue;
                        }
                    }
                }
                //更新或新增字段
                $table->{$method}($field, $column['type'], $column['option']);
            }
            //获取差集字段(删除字段)
            foreach (array_diff(array_keys($existsColumns), array_keys($columns)) as $field) {
                $table->removeColumn($field);
            }
        } else {
            foreach (array_merge($columns, $this->defaultColumns) as $field => $column) {
                $table = $table->addColumn($field, $column['type'], $column['option']);
            }
        }
        $table->save();
    }

    /**
     * 保存表单数据
     * @param array $formContent
     * @param array $data 存储的数据
     * @return false|int
     */
    public function save(array $formContent = [], array $data = [])
    {
        //设置表单更新时间
        $data[$this->updateTime] = time();
        //设置字段内容
        foreach ($formContent as $content) {
            $data[$content['field']] = $value = $this->getOption($content, 'save');
            //处理额外字段数据插入
            if(isset($this->formSetColumn[$content['type']])) {
                foreach ($this->formSetColumn[$content['type']] as $field_name => $option) {
                    $data["{$content['field']}_{$field_name}"] = $this->getFormColumnKey($value, $option['key'] ?? null, $content, $option['type']);
                }
            }
        }
        if($this->tableExists()) {
            //获取表字段
            $columns = $this->tableColumn();
            //只保存数据库存在的字段
            foreach ($data as $k => $v) {
                if(!isset($columns[$k])) unset($data[$k]);
            }
            if($this->tableId) {
                $info = $this->mode->where($this->primaryKey, $this->tableId)->first();
            }
            //创建
            if(empty($info ?? []) || empty($this->tableId)) {
                //设置表单创建时间
                $data[$this->createTime] = time();
                //新增并返回插入id
                return $this->mode->insertGetId($data);
            } else { //更新
                //更新数据
                return $this->mode->where($this->primaryKey, $this->tableId)->update($data) ? $this->tableId: 0;
            }
        }
        return false;
    }

    public function getColumns($content): array
    {
        //设置默认字段
        $columns = [
            $content['field'] => [
                'type' => $this->getFieldType($content),
                'option' => array_merge(
                    $this->getOption($content),
                    $this->getOption($content, 'comment'),
                    $this->getOption($content, 'limit'),
                    $this->getOption('id', 'after')
                )
            ]
        ];

        //根据类型获取字段
        if(isset($this->formSetColumn[$content['type']])) {
            foreach ($this->formSetColumn[$content['type']] as $field_name => $option) {
                $columns["{$content['field']}_{$field_name}"] = [
                    'type' => $option['type'],
                    'option' => $option['option'],
                ];
            }
        }
        return $columns;
    }

    /**
     * 获取选项，分发到不同的表单类型（用于生成数据库结构）
     * @param $option
     * @param string $method
     * @return string|array|array[]
     */
    public function getOption($option, string $method = 'default')
    {
        if(is_array($option) && !empty($option)) {
            if(method_exists(self::class,$option['type'])) {
                return $this->{$option['type']}($option, $method);
            }
        }
        return [$method => $option];
    }

    protected function text($content, string $method)
    {
        switch ($method) {
            case 'default':
                return [$method => $content['default'] ?? ''];
            case 'comment':
                return [$method => $content['name'] ?? ''];
            case 'limit':
                return [$method => 255];
            case 'save':
                return $content['default'] ?? '';
        }
        return [];
    }

    protected function textarea($content, string $method)
    {
        switch ($method) {
            case 'default':
                return [];
            case 'comment':
                return [$method => $content['name'] ?? ''];
            case 'limit':
                return ['null' => true, $method => MysqlAdapter::TEXT_REGULAR];
            case 'save':
                return $content['default'] ?? '';
        }
        return [];
    }

    protected function number($content, string $method)
    {
        switch ($method) {
            case 'default':
                return [$method => floatval($content['default'] ?? 0)];
            case 'comment':
                return [$method => $content['name'] ?? ''];
            case 'limit':
                return ['precision' => 15, 'scale' => 4];
            case 'save':
                return floatval($content['default'] ?? 0);
        }
        return [];
    }

    protected function select($content, string $method)
    {
        switch ($method) {
            case 'default':
                foreach ($content['default'] ?? [] as $select) {
                    if($select['checked'] ?? false) {
                        return [$method => $select['value']];
                    }
                }
                return [$method => 0];
            case 'comment':
                $comment = [];
                foreach ($content['default'] ?? [] as $select) {
                    $comment[] = "{$select['value']}=>{$select['name']}";
                }
                return [$method => $content['name'] . ':' . implode(',', $comment)];
            case 'limit':
                return ['signed' => false, $method => 1];
            case 'save':
                $value = 0;
                foreach ($content['default'] ?? [] as $select) {
                    if($select['checked'] ?? false) $value = $select['value'];
                }
                return $value;
        }
        return [];
    }

    protected function multiple_select($content, string $method)
    {
        switch ($method) {
            case 'default':
                $default = [];
                foreach ($content['default'] ?? [] as $select) {
                    if($select['checked'] ?? false) $default[] = $select['value'];
                }
                return [$method => implode(',', $default)];
            case 'comment':
                $comment = [];
                foreach ($content['default'] ?? [] as $select) {
                    $comment[] = "{$select['value']}=>{$select['name']}";
                }
                return [$method => $content['name'] . ':' . implode(',', $comment)];
            case 'limit':
                return [$method => 255];
            case 'save':
                $value = [];
                foreach ($content['default'] ?? [] as $select) {
                    if($select['checked'] ?? false) $value[] = $select['value'];
                }
                return implode(',', $value);
        }
        return [];
    }

    protected function date($content, string $method)
    {
        switch ($method) {
            case 'default':
                return [$method => ($content['default'] ?? 0) ? strtotime($content['default']): 0];
            case 'comment':
                return [$method => $content['name'] ?? ''];
            case 'limit':
                return ['signed' => false, $method => 10];
            case 'save':
                return ($content['default'] ?? 0) ? strtotime($content['default']): 0;
        }
        return [];
    }

    protected function image($content, string $method)
    {
        switch ($method) {
            case 'default':
                return [];
            case 'comment':
                return [$method => $content['name'] ?? ''];
            case 'limit':
                return ['null' => true, $method => MysqlAdapter::TEXT_REGULAR];
            case 'save':
                return json_encode($content['default']);
        }
        return [];
    }

    protected function file($content, string $method)
    {
        switch ($method) {
            case 'default':
                return [];
            case 'comment':
                return [$method => $content['name'] ?? ''];
            case 'limit':
                return ['null' => true, $method => MysqlAdapter::TEXT_REGULAR];
            case 'save':
                return json_encode($content['default']);
        }
        return [];
    }

    protected function getFieldType($option = [], $typeArr = []): string
    {
        $type = 'string';
        foreach ($typeArr ?: $this->typeArr as $k => $v) {
            if(in_array($option['type'], $v)) {
                $type = $k;break;
            }
        }
        return $type;
    }

    protected function tableExists($name = ''): bool
    {
        return !empty($this->dbHandle("show tables like '". ($name ?: $this->tableName) . "'"));
    }

    protected function tableColumn(): array
    {
        $columnArr = [];
        foreach (to_array($this->dbHandle("DESC {$this->tableName}")) as $v) {
            $type = "";
            preg_replace_callback('/^(\w+)/',function ($matches) use (&$type) {
                $type = $matches[0];
            },$v['Type']);
            $columnArr[$v['Field']] = [
                'field' => $v['Field'],
                'type' => $type,
                'default' => $v['Default']
            ];
        }
        return $columnArr;
    }

    public function dbHandle($sql)
    {
        return $this->dbConfig()->select($sql);
    }

    public function dbConfig(): Connection
    {
        return Db::connection($this->getConnection());
    }

    private function setAdapter(): AdapterInterface
    {
        $options = $this->getDbConfig();
        $adapter = AdapterFactory::instance()->getAdapter($options['adapter'], $options);
        if ($adapter->hasOption('table_prefix') || $adapter->hasOption('table_suffix')) {
            $adapter = AdapterFactory::instance()->getWrapper('prefix', $adapter);
        }
        return $adapter;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @param mixed|string $tableName
     * @return TableHandle
     */
    public function setTableName($tableName): self
    {
        $this->tableName = $tableName;
        //实例化基础表
        $this->mode = $this->dbConfig()->table($tableName);
        return $this;
    }

    /**
     * 获取数据库配置
     * @return array
     */
    protected function getDbConfig(): array
    {
        $config = config("thinkorm.connections.{$this->getConnection()}");

        return [
            'adapter'         => 'mysql',
            'host'            => explode(',', $config['hostname'])[0],
            'name'            => explode(',', $config['database'])[0],
            'user'            => explode(',', $config['username'])[0],
            'pass'            => explode(',', $config['password'])[0],
            'port'            => explode(',', $config['hostport'])[0],
            'charset'         => explode(',', $config['charset'])[0],
            'table_prefix'    => explode(',', $config['prefix'])[0],
            'migration_table' => 'migrations'
        ];
    }

    /**
     * @return string
     */
    public function getConnection(): string
    {
        return $this->connection;
    }

    /**
     * @param string $connection
     */
    protected function setConnection(string $connection): void
    {
        $this->connection = $connection;
    }

    /**
     * @return Builder
     */
    public function getMode(): Builder
    {
        return $this->mode;
    }

    /**
     * @param Builder $mode
     * @return TableHandle
     */
    public function setMode(Builder $mode): self
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * @return int
     */
    public function getTableId(): int
    {
        return $this->tableId;
    }

    /**
     * @param int $tableId
     * @return TableHandle
     */
    public function setTableId(int $tableId): self
    {
        $this->tableId = $tableId;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * @param string $primaryKey
     * @return TableHandle
     */
    public function setPrimaryKey(string $primaryKey): self
    {
        $this->primaryKey = $primaryKey;
        return $this;
    }

    /**
     * 获取字段索引
     */
    protected function getFormColumnKey($value = null, $keys = null, $content = [], $type = 'string')
    {
        //获取类型
        $columnType = $content['type'];
        //设置默认值
        $default = $this->getFiledTypeValue('',$type);
        foreach (explode('.', $keys) as $key) {
            if(in_array($key, ['[int]', ['string']])) {
                switch ($columnType) {
                    case 'select':
                    case 'multiple_select':
                        foreach ($content as $v) if(isset($v['value']) && ($v['value'] == $value)) {
                            $content = $v;break;
                        }
                        break;
                }
            } else {
                if (!isset($content[$key])) {
                    $content = $default;
                    break;
                }
                $content = $content[$key];
            }
        }
        return $content;
    }

    /**
     * @param array $content
     * @param int|null $value
     * @return array|mixed
     */
    public function getSelectOption(array $content, int $value = null): array
    {
        $options = $content['default'] ?? [];
        $select_option = [];
        foreach ($options as $option){
            if (($value === null && ($option['checked'] ?? false) == true) || ($value == $option['value'] ?? -1)){
                $select_option = $option;
            }
        }
        return $select_option;
    }
}
