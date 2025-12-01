<?php

declare(strict_types=1);

namespace app\middleware;

use think\Request;
use think\Response;

class CheckLogin
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle(Request $request, \Closure $next)
    {
        //验证是否登录
        if (!session('?user') && !in_array($request->controller(), [
                'Login',
                'Home',
                'Common',
                'Test',
            ])) {
            $data['code'] = 0;
            $data['msg']  = '登录已过期';
            $data['data'] = '';

            return json($data);
        }

        return $next($request);
    }
}
