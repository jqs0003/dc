<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;
//Route::get('rest/:id','index/Restful/rest');   //查询
//Route::post('rest','index/Restful/rest');       //新增
//Route::put('rest/:id','index/Restful/rest'); //修改
//Route::delete('rest/:id','index/Restful/rest');
Route::any('rest/:id', 'index/Restful/rest');
Route::any('test/[:id]', 'index/Test/rest');
