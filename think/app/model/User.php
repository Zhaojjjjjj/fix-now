<?php

namespace app\model;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * @property int           $id
 * @property string        $created_at
 * @property string        $updated_at
 * @property int           $status
 *
 * @property int           $role_id     角色
 * @property string        $username    用户名
 * @property string        $pwd         密码
 * @property string        $wxmp_openid 公众号ID
 * @property string        $nickname    昵称
 * @property string        $img_avatar  头像
 * @property string        $mobile      手机
 *
 * @property UserProject[] $userProjectList
 * @property Issue[]       $issueList
 * @property Issue[]       $curIssueList
 * ---------- CUSTOM PROPERTIES ----------
 */
class User extends \app\model\AR
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'user';

    protected $schema = [
        'id'          => 'int',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'status'      => 'int',
        'role_id'     => 'int',
        'username'    => 'varchar',
        'pwd'         => 'varchar',
        'wxmp_openid' => 'varchar',
        'nickname'    => 'varchar',
        'img_avatar'  => 'varchar',
        'mobile'      => 'varchar',
    ];

    // 追加属性到模型数组
    protected $append = ['avatar'];

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

    public function userProjectList()
    {
        return $this->hasMany(UserProject::class, 'user_id', 'id');
    }

    public function issueList()
    {
        return $this->hasMany(Issue::class, 'user_id', 'id');
    }

    public function curIssueList()
    {
        return $this->hasMany(Issue::class, 'cur_user_id', 'id');
    }

    /**
     * 头像访问器 - 将 img_avatar 映射为 avatar
     */
    public function getAvatarAttr($value, $data)
    {
        return $data['img_avatar'] ?? '';
    }

    /**
     * 头像修改器 - 将 avatar 映射为 img_avatar
     */
    public function setAvatarAttr($value)
    {
        return ['img_avatar' => $value];
    }
}
