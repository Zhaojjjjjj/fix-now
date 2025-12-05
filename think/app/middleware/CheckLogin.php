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
        $path = $request->pathinfo();
        $controller = $request->controller();
        
        // 调试模式：可以临时启用查看请求信息
        // file_put_contents('/tmp/middleware_debug.log', date('Y-m-d H:i:s') . " | Path: {$path} | Controller: {$controller}\n", FILE_APPEND);
        
        // 1. 跳过静态资源路径
        if (str_starts_with($path, 'assets/') || 
            str_starts_with($path, 'static/') ||
            str_starts_with($path, 'storage/')) {
            return $next($request);
        }
        
        // 2. 跳过静态资源文件扩展名
        $ext = $request->ext();
        if ($ext && in_array($ext, ['js', 'css', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot', 'map', 'json', 'txt', 'html'])) {
            return $next($request);
        }

        // 3. 跳过前端路由（HomeController 处理所有前端页面，包括 Route::miss）
        // 以及登录相关的控制器
        $controllerLower = strtolower($controller);
        if (in_array($controllerLower, ['homecontroller', 'home', 'login', 'common', 'test']) ||
            str_contains($controller, 'HomeController')) {
            return $next($request);
        }

        // 4. 验证其他后端 API 的登录状态
        if (!session('?user')) {
            return json([
                'code' => 0,
                'msg'  => '登录已过期',
                'data' => '',
            ]);
        }

        return $next($request);
    }
}
