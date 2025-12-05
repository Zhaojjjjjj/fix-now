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
        // 处理 OPTIONS 请求（跨域预检）
        if ($request->method(true) === 'OPTIONS') {
            return response('', 200)->header([
                'Access-Control-Allow-Origin'  => $request->header('origin') ?: '*',
                'Access-Control-Allow-Headers' => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With, SID, Cookie',
                'Access-Control-Allow-Methods' => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Credentials' => 'true',
            ]);
        }

        $path = $request->pathinfo();
        $controller = $request->controller();
        
        // 调试模式：可以临时启用查看请求信息
        // file_put_contents('/tmp/middleware_debug.log', date('Y-m-d H:i:s') . " | Path: {$path} | Controller: {$controller}\n", FILE_APPEND);
        
        // 1. 跳过静态资源路径
        if ($path === '' || $path === '/' || 
            str_starts_with($path, 'assets/') || 
            str_starts_with($path, 'static/') ||
            str_starts_with($path, 'storage/')) {
            return $next($request);
        }
        
        // 2. 跳过静态资源文件扩展名
        $ext = $request->ext();
        if ($ext && in_array($ext, ['js', 'css', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot', 'map', 'json', 'txt', 'html'])) {
            return $next($request);
        }

        // 3. 识别前端路由和后端API
        $controllerLower = strtolower($controller);
        
        // 基于路径的白名单（防止 controller 尚未解析时误拦截）
        if (str_contains($path, 'login/') || str_contains($path, '/login') ||
            str_contains($path, 'common/') || str_contains($path, '/common')) {
            return $next($request);
        }

        // 白名单控制器（完全公开）
        if ($controllerLower && in_array($controllerLower, ['login', 'common'])) {
            return $next($request);
        }
        
        // 前端页面控制器（只放行特定 Action，或者全部放行如果是纯前端入口）
        // 如果是 HomeController，通常是前端页面
        if ($controllerLower === 'homecontroller' || $controllerLower === 'home' || str_contains($controller, 'HomeController')) {
             return $next($request);
        }

        // 4. 其他情况默认为需要登录的后端 API
        // (包括 v1/user/edit 或 user/edit)
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
