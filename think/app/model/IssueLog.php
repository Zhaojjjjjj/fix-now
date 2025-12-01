<?php

namespace app\model;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use Carbon\Carbon;

/**
 * @property int $id
 * @property string $created_at
 * @property string $updated_at
 * @property int $status
 *
 * @property int $issue_id @issue@
 * @property int $user_id @user
 * @property int $next_user_id @user
 * @property int $type 类型
 * @property string $content 内容
 *
 * @property Issue $issue
 * @property User $user
 * @property User $nextUser
 * ---------- CUSTOM PROPERTIES ----------
 */
class IssueLog extends \app\model\AR 
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'issue_log';

    protected  $schema = [
        'id'           => 'int',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'status'       => 'int',
        'issue_id'     => 'int', 
        'user_id'      => 'int', 
        'next_user_id' => 'int', 
        'type'         => 'tinyint', 
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

    public function issue()
    {
        return $this->hasOne(Issue::class, 'id' , 'issue_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id' , 'user_id');
    }

    public function nextUser()
    {
        return $this->hasOne(User::class, 'id' , 'next_user_id');
    }

    // ---------- Custom code below ----------

    const TYPE_ADD = 1;
    const TYPE_FIX = 2;
    const TYPE_REJECT = 3;
    const TYPE_VER_REJECT = 4;
    const TYPE_COMMENT = 5;
    const TYPE_ASSIGN = 6;
    const TYPE_FINISH = 8;

    const TYPE = [
        1 => '发起',
        2 => '修复',
        3 => '驳回',
        4 => '不通过',
        5 => '评论',
        6 => '指派',
        8 => '审核通过',
        9 => '修改',
    ];

    const TYPE_STYLE = [
        1 => 'primary',
        2 => 'warning',
        3 => 'info',
        4 => 'danger',
        5 => 'primary',
        6 => 'info',
        8 => 'success',
    ];

    /**
     * 设置时间
     */
    public function setCreatedAtAttr()
    {
        $time = Carbon::parse('now')
            ->toDateTimeString();

        $this->set('created_at', $time);
    }

    /**
     * 将时间转换为人工可视化
     * @param $date
     * @return Carbon|string
     */
    public function getCreatedAtAttr($date)
    {
        if (Carbon::now() > Carbon::parse($date)
                ->addDays(7)) {
            return Carbon::parse($date)
                ->toDateTimeString();
        }

        return Carbon::parse($date)
            ->diffForHumans();
    }
}
