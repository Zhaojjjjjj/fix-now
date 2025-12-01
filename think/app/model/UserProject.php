<?php

namespace app\model;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * @property int     $id
 * @property string  $created_at
 * @property string  $updated_at
 * @property int     $status
 *
 * @property int     $user_id             @user@
 * @property int     $project_id          @project@
 * @property int     $user_project_status 用户项目状态
 *
 * @property User    $user
 * @property Project $project
 * ---------- CUSTOM PROPERTIES ----------
 */
class UserProject extends \app\model\AR
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'user_project';

    protected $schema = [
        'id'                  => 'int',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
        'status'              => 'int',
        'user_id'             => 'int',
        'project_id'          => 'int',
        'user_project_status' => 'tinyint',
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

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function project()
    {
        return $this->hasOne(Project::class, 'id', 'project_id');
    }

    // ---------- Custom code below ----------
}