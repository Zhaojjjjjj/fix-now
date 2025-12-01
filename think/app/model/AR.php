<?php

namespace app\model;

use think\db\BaseQuery;

/**
 * @mixin BaseQuery
 * @method BaseQuery where($field, $op = null, $condition = null) static where
 * @method BaseQuery find($data = null) static find
 */
class AR extends \think\Model
{
    const STATUS = [
        '0'  => '无效',
        '1'  => '正常',
        '9'  => '待审',
        '-1' => '删除',
    ];

    public static function findOrNew($condition): self
    {
        return self::find($condition) ?? new static();
    }


    public static function onBeforeInsert($data)
    {
        $data->created_at = $data->created_at ?? date('Y-m-d H:i:s');
        $data->updated_at = $data->updated_at ?? '0000-00-00 00:00:00';
        $data->status     = $data->status ?? 1;

        foreach ($data->schema as $key => $value) {
            // 过滤 primary key auto_increment 字段
            if ($key == 'id') {
                continue;
            }

            if (is_null($data->$key)) {
                try {
                    switch ($value) {
                        case 'char':
                        case 'varchar':
                        case 'text':
                            $data->$key = '';
                            break;
                        case 'tinyint':
                        case 'int':
                        case 'decimal':
                            $data->$key = 0;
                            break;
                        case 'datetime':
                            $data->$key = '0000-00-00 00:00:00';
                            break;
                        case 'date':
                            $data->$key = '0000-00-00';
                            break;
                        default:
                            break;
                    }
                } catch (\Throwable  $e) {
                    return false;
                }
            }
        }
    }

    public static function onBeforeUpdate($data)
    {
        $data->updated_at = date('Y-m-d H:i:s');
    }

    /**
     * 处理条件查询
     * @param array $filterCond
     * @param array $rawCond
     * @return \think\db\concern\WhereQuery
     */
    public static function filterWhere($filterCond, $rawCond = [])
    {
        foreach ($filterCond as $k => $v) {
            if (is_array($v) && (is_null($v[2]) || $v[2] === '')) {
                unset($filterCond[$k]);
            }
            if (is_null($v) || $v === '') {
                unset($filterCond[$k]);
            }
        }

        return self::where(array_merge($filterCond, $rawCond));
    }

    /**
     * 查询列表, 带分页
     * @param array|null  $with   关联表
     * @param array|null  $where  查询条件
     * @param int|null    $page   页数
     * @param int|null    $limit  每页数
     * @param string|null $order  查询排序
     * @param string|null $fields 查询字段
     * @return array|AR[]
     */
    public function findList(
        array $with = null,
        array $where = null,
        int $page = 1,
        int $limit = 10,
        string $order = null,
        string $fields = null
    )
    {
        $fields = is_null($fields) ? '*' : $fields;
        $where  = is_null($where) ? ['status' => 1] : $where;
        $order  = is_null($order) ? ['id' => SORT_DESC] : $order;

        $total = $this->filterWhere($where)
            ->count();
        if ($with) {
            $list = self::filterWhere($where)
                ->with($with)
                ->field($fields)
                ->order($order)
                ->page($page, $limit)
                ->select()
                ->toArray();
        } else {
            $list = $this->filterWhere($where)
                ->field($fields)
                ->order($order)
                ->page($page, $limit)
                ->select()
                ->toArray();
        }

        return [
            'total' => $total,
            'rows'  => $list,
        ];
    }
}
