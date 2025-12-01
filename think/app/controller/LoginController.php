<?php

namespace app\controller;

use app\model\User;
use think\facade\Session;

class LoginController extends _Controller
{
    /**
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\DataNotFoundException
     */
    public function loginAction()
    {
        if ($post = $this->request->post()) {
            if (!$post['username'] || !$post['password']) {
                return $this->fail('参数不能为空');
            }

            $where['username'] = $post['username'];
            $where['pwd']      = $post['password'];

            $user = User::where($where)
                ->find();

            if (!$user) {
                return $this->fail('账号或密码错误');
            }

            if ($user['status'] != 1) {
                return $this->fail('账号禁用');
            }

            session('user', $user);

            return $this->vueSuccess('登录成功', ['SID' => Session::getId()]);
        }
    }

    public function logoutAction()
    {
        session('user', null);

        return $this->success('退出成功');
    }
}