<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace app\common\model;

use think\db\exception\DbException;
use think\db\Query;
use think\Model;

/**
 * @property int $id
 * @method getFieldType(string $key)
 * @method array getTableFields
 */
class CommonBaseModel extends Model
{
    // 日志记录的主键名称
    public string $logKey = 'id';

    /**
     * @var bool $jsonAssoc
     */
    protected $jsonAssoc = true;

    /**
     * @var mixed
     */
    protected $defaultSoftDelete = 0;

    /** @var array $searchField 可作为搜索关键词的字段 */
    public array $searchField = [];
    /** @var array $whereField 可作为条件查询的字段 */
    public array $whereField = [];
    /** @var array $multiWhereField 可作为多选查询的字段 */
    public array $multiWhereField = [];
    /** @var array $timeField 可作为时间范围查询的字段 */
    public array $timeField = [];
    /** @var array $noDeletionIds 不可删除的数据ID */
    public array $noDeletionIds = [];
    /** @var array|string[] $ignoreLogFields 日志生成忽略的字段 */
    public array $ignoreLogFields = [
        'create_time',
        'update_time',
    ];
    /** @var string $createTime 创建时间 */
    protected $createTime = 'create_time';
    /** @var string $updateTime 更新时间 */
    protected $updateTime = 'update_time';

    /**
     * 查询处理
     * @param Query $query
     * @var array $param
     */
    public function scopeWhere(Query $query, array $param): void
    {
        //关键词like搜索
        $keywords = $param['_keywords'] ?? '';
        if ('' !== $keywords && count($this->searchField) > 0) {
            $searchField = implode('|', $this->searchField);
            $query->where($searchField, 'like', '%' . $keywords . '%');
        }

        //字段条件查询
        if (count($this->whereField) > 0 && count($param) > 0) {
            foreach ($param as $key => $value) {
                if ($value !== '' && in_array($key, $this->whereField, true)) {
                    $query->where($key, $value);
                }
            }
        }

        //字段条件查询
        if (count($this->multiWhereField) > 0 && count($param) > 0) {
            foreach ($param as $key => $value) {
                if (is_array($value) && !empty($value) && in_array($key, $this->multiWhereField, true)) {
                    $where = '';
                    foreach ($value as $item) {
                        $str   = "FIND_IN_SET('" . $item . "'," . $key . ") ";
                        $where .= empty($where) ? $str : ' OR ' . $str;
                        $query->where($where);
                    }
                }
            }
        }

        //时间范围查询
        if (count($this->timeField) > 0 && count($param) > 0) {
            foreach ($param as $key => $value) {
                if ($value !== '' && in_array($key, $this->timeField, true)) {
                    $field_type = $this->getFieldType($key);
                    $time_range = explode(' - ', $value);
                    [$start_time, $end_time] = $time_range;
                    //如果是int，进行转换
                    if (false !== strpos($field_type, 'int')) {
                        $start_time = strtotime($start_time);
                        if (strlen($end_time) === 10) {
                            $end_time .= ' 23:59:59';
                        }
                        $end_time = strtotime($end_time);
                    }
                    $query->whereBetweenTime($key, $start_time, $end_time);
                }
            }
        }
        //排序
        $order = $param['_order'] ?? '';
        $by    = $param['_by'] ?? 'desc';
        $query->order($order ?: 'id', $by ?: 'desc');
    }

    /**
     * api模块相关scope
     * @param Query $query
     * @param array $param
     */
    public function scopeApiWhere(Query $query, array $param): void
    {
        $this->scopeWhere($query, $param);
    }

    /**
     * 当前ID是否包含在不可删除的ID中
     * @param $id
     * @return false|string
     */
    public function inNoDeletionIds($id)
    {
        if (count($this->noDeletionIds) > 0) {
            if (is_array($id)) {
                if (array_intersect($this->noDeletionIds, $id)) {
                    return implode(',', $id);
                }
            } else if (in_array((int)$id, $this->noDeletionIds, true)) {
                return $id;
            }
        }
        return false;
    }

    /**
     * 查询或新增
     * @param array $data
     * @param array $where
     * @return array|mixed|Query|Model|self
     */
    public function findOrInsert(array $data = [], array $where = [])
    {
        $where = $where ?: $data;
        $result = $this->where($where)->findOrEmpty();
        if($result->isEmpty()) {
            $result = $this->create($data);
        }
        return $result;
    }

    /**
     * 查询事件更新
     * @param array $data
     * @param array $where
     * @return CommonBaseModel|array|mixed|Query|Model
     * @throws DbException
     */
    public function findOrUpdate(array $data = [], array $where = [])
    {
        $where = $where ?: $data;
        $result = $this->where($where)->findOrEmpty();
        if($result->isExists()) {
            $result = $this->where($where)->update($data);
        }
        return $result;
    }
}
