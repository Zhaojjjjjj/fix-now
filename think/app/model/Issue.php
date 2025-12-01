<?php

namespace app\model;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;

/**
 * @property int $id
 * @property string $created_at
 * @property string $updated_at
 * @property int $status
 *
 * @property int $user_id @user@
 * @property int $cur_user_id @user@
 * @property int $project_id @project@
 * @property int $module_id @module@
 * @property int $sn 序号
 * @property int $type 类型
 * @property int $priority 优先级
 * @property string $bug_type Bug类型
 * @property string $environment 开发环境
 * @property string $title 标题
 * @property string $content 问题内容
 *
 * @property User $user
 * @property User $curUser
 * @property Project $project
 * @property Module $module
 * @property IssueLog[] $issueLogList
 * ---------- CUSTOM PROPERTIES ----------
 */
class Issue extends \app\model\AR 
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'issue';

    protected  $schema = [
        'id'           => 'int',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'status'       => 'int',
        'user_id'      => 'int', 
        'cur_user_id'  => 'int', 
        'project_id'   => 'int', 
        'module_id'    => 'int', 
        'sn'           => 'int', 
        'type'         => 'tinyint', 
        'priority'     => 'tinyint',
        'bug_type'     => 'varchar',
        'environment'  => 'varchar',
        'title'        => 'varchar', 
        'content'      => 'text', 
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
        return $this->hasOne(User::class, 'id' , 'user_id');
    }

    public function curUser()
    {
        return $this->hasOne(User::class, 'id' , 'cur_user_id');
    }

    public function project()
    {
        return $this->hasOne(Project::class, 'id' , 'project_id');
    }

    public function module()
    {
        return $this->hasOne(Module::class, 'id' , 'module_id');
    }

    public function issueLogList()
    {
        return $this->hasMany(IssueLog::class, 'issue_id' , 'id');
    }

    // ---------- Custom code below ----------

    const STATUS_NOTSOLVE = 1;
    const STATUS_AUDIT = 2;
    const STATUS_FINISH = 8;

    const STATUS = [
        self::STATUS_NOTSOLVE => '未解决',
        self::STATUS_AUDIT    => '待审核',
        self::STATUS_FINISH   => '已关闭',
    ];

    const STATUS_STYLE = [
        1 => 'danger',
        2 => 'warning',
        8 => 'secondary',
    ];

    const PRIORITY_NORMAL = 1;
    const PRIORITY_HIGH = 2;

    const PRIORITY = [
        self::PRIORITY_NORMAL => '普通',
        self::PRIORITY_HIGH   => '紧急',
    ];

    const DEFECT = 1;
    const NEED = 2;
    const TASK = 3;

    const TYPE = [
        1 => '缺陷',
        2 => '任务',
        3 => '需求',
    ];

    const TYPE_STYLE = [
        1 => 'danger',
        2 => 'warning',
        3 => 'primary',
    ];

    /**
     * @param int    $userId  操作日志用户
     * @param int    $type    类型
     * @param string $content 日志内容
     * @return void
     */
    public function log(int $userId, int $type, string $content)
    {
        $data = [
            'issue_id'     => $this->id,
            'user_id'      => $userId,
            'next_user_id' => $this->cur_user_id,
            'type'         => $type,
            'content'      => $content,
        ];

        // 根据操作类型调整next_user_id
        switch ($type) {
            case 2: // 修复
            case 3: // 驳回
                $data['next_user_id'] = $this->user_id;
                break;
            case 5: // 评论
                $data['next_user_id'] = 0;
                break;
        }

        (new IssueLog())->save($data);
    }

    /**
     * 修改发起状态（复用log方法）
     * @param $userId
     * @param $type
     * @param $content
     */
    public function editLog($userId, $type, $content)
    {
        $this->log($userId, $type, $content);
    }

    /**
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws DataNotFoundException
     */
    public static function onBeforeInsert($data)
    {
        /* @var Issue $max */
        $max = self::where(['project_id' => $data['project_id']])
            ->order('id', 'DESC')
            ->find();
        if ($max) {
            $data['sn'] = $max->sn + 1;
        } else {
            $data['sn'] = 1;
        }

        return parent::onBeforeInsert($data);
    }
}
