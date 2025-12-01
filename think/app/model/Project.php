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
 * @property string $img_cover 封面图
 * @property string $description 项目描述
 * @property string $name 项目名称
 *
 * @property Module[] $moduleList
 * @property UserProject[] $userProjectList
 * @property Issue[] $issueList
 * ---------- CUSTOM PROPERTIES ----------
 */
class Project extends \app\model\AR 
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'project';

    protected  $schema = [
        'id'           => 'int',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'status'       => 'int',
        'img_cover'    => 'varchar', 
        'description'  => 'varchar', 
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

    public function moduleList()
    {
        return $this->hasMany(Module::class, 'project_id' , 'id');
    }

    public function userProjectList()
    {
        return $this->hasMany(UserProject::class, 'project_id' , 'id');
    }

    public function issueList()
    {
        return $this->hasMany(Issue::class, 'project_id' , 'id');
    }

    // ---------- Custom code below ----------

    /**
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function toArray(): array
    {
        $tmp = parent::toArray();

        // 只有在项目已存在时才查询用户列表
        if (!empty($this->id)) {
            $staffList  = User::where(['role_id' => 1])
                ->select()
                ->toArray();
            $clientList = User::alias('u')
                ->join('user_project up', "up.user_id=u.id")
                ->join('project p', "p.id=up.project_id AND p.id=" . $this->id)
                ->field('u.*')
                ->select()
                ->toArray();

            $tmp['userList'] = array_merge($staffList, $clientList);
        }

        return $tmp;
    }
}

