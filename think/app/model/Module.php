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
 * @property int $sort 排序
 * @property int $project_id @project@
 * @property int $parent_id @module@
 * @property string $name 名称
 *
 * @property Project $project
 * @property Module $parent
 * @property Module[] $children
 * @property Issue[] $issueList
 * ---------- CUSTOM PROPERTIES ----------
 */
class Module extends \app\model\AR 
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'module';

    protected  $schema = [
        'id'           => 'int',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'status'       => 'int',
        'sort'         => 'int', 
        'project_id'   => 'int', 
        'parent_id'    => 'int', 
        'name'         => 'varchar', 
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

    public function project()
    {
        return $this->hasOne(Project::class, 'id' , 'project_id');
    }

    public function parent()
    {
        return $this->hasOne(Module::class, 'id' , 'parent_id');
    }

    public function Children()
    {
        return $this->hasMany(Module::class, 'parent_id' , 'id');
    }

    public function issueList()
    {
        return $this->hasMany(Issue::class, 'module_id' , 'id');
    }

    // ---------- Custom code below ----------
}
