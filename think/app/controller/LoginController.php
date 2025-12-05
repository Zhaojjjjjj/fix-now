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
        // 尝试获取 POST 数据，如果是 JSON 格式，ThinkPHP 的 input() 会自动处理
        // 但为了稳妥，我们检查一下原始输入
        $post = $this->request->post();
        
        // 兼容 application/json
        if (empty($post)) {
            $json = file_get_contents('php://input');
            $post = json_decode($json, true);
        }

        // 调试日志 (如果需要调试，请取消注释)
        // file_put_contents(runtime_path() . 'login_debug.log', date('Y-m-d H:i:s') . " | Input: " . json_encode($post) . "\n", FILE_APPEND);

        if (empty($post)) {
             return $this->fail('请求参数为空');
        }
            
        if (empty($post['username']) || empty($post['password'])) {
            return $this->fail('账号或密码不能为空');
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

    public function logoutAction()
    {
        session('user', null);

        return $this->success('退出成功');
    }
}
