<?php

namespace app\controller;

use app\model\User;

class UserController extends _Controller
{
    public function pwdAction()
    {
        if ($post = $this->request->post()) {
            $user = $this->curUser;

            if ($user['pwd'] != $post['old_pwd']) {
                return $this->fail('旧密码错误');
            }

            $user->updated_at = date('Y-m-d H:i:s');
            $user->pwd        = $post['new_pwd'];

            if (!$user->save()) {
                return $this->fail('修改失败');
            }

            session('user', null);

            return $this->vueSuccess('操作成功');
        }
    }

    public function editAction()
    {
        $row = User::findOrNew(session('user')['id']);

        if ($post = $this->request->post()) {
            $row->save($post);
            
            // 更新session中的用户信息
            session('user', $row->toArray());

            return $this->vueSuccess('操作成功', $row);
        }

        return $this->vueSuccess($row);
    }

    public function listAction()
    {
        $staffList  = User::where(['role_id' => 1])
            ->select()
            ->toArray();
        $clientList = User::alias('u')
            ->join('user_project up', "up.user_id=u.id")
            ->join('project p', "p.id=up.project_id AND p.id=" . $this->reqId)
            ->field('u.*')
            ->select()
            ->toArray();

        $row = array_merge($staffList, $clientList);

        return $this->vueSuccess($row);
    }
}
