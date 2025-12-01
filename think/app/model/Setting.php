<?php

namespace app\model;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * @property int $id
 * @property string $created_at
 * @property string $updated_at
 * @property int $status
 *
 * @property string $m 模块
 * @property string $g 组别
 * @property string $k 名称
 * @property string $v 内容
 * @property int $is_load 自动加载
 *
 * ---------- CUSTOM PROPERTIES ----------
 */
class Setting extends \app\model\AR 
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'setting';

    protected  $schema = [
        'id'           => 'int',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'status'       => 'int',
        'm'            => 'varchar', 
        'g'            => 'varchar', 
        'k'            => 'varchar', 
        'v'            => 'varchar', 
        'is_load'      => 'tinyint', 
    ];


    public static function findOne($data = null)
    {
        if (is_numeric($data)) {
            $data = ['id' => $data];
        }

        try {
            return self::where($data)
                ->find();
        } catch (DataNotFoundException | ModelNotFoundException | DbException $e) {
            return null;
        }
    }

    // ---------- Custom code below ----------
}

