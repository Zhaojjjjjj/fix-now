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

// SPA 路由支持：所有未匹配的路由都渲染前端入口
Route::miss('app\controller\HomeController@indexAction');
// Route::rule('adm/system/home/list', 'adm.system.home/list');
