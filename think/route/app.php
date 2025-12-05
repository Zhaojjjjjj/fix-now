<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

Route::rule('<version>/<controller>/<action>', '<version>.<controller>/<action>');

// 显式注册 API 路由
Route::post('login/login', 'app\controller\LoginController@loginAction');
Route::post('login/logout', 'app\controller\LoginController@logoutAction');

// 恢复两段式路由支持，确保 API 接口如 /project/list 能被正确路由
Route::rule('<controller>/<action>', '<controller>/<action>');

// SPA 路由支持：所有未匹配的路由都渲染前端入口
Route::miss('app\controller\HomeController@indexAction');
// Route::rule('adm/system/home/list', 'adm.system.home/list');
