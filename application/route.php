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
Route::any('/', function(){ echo 'hello world'; });
//Route::get('rest/:id','index/Restful/rest');   //查询
//Route::post('rest','index/Restful/rest');       //新增
//Route::put('rest/:id','index/Restful/rest'); //修改
//Route::delete('rest/:id','index/Restful/rest');
Route::any('rest/:id', 'api/Restful/rest');
Route::any('user/[:id]', 'api/User/rest');
Route::any('merchant/[:id]', 'api/Merchant/rest');
Route::any('category/[:id]', 'api/Category/rest');
Route::any('dish/[:id]', 'api/Dish/rest');
Route::any('table/[:id]', 'api/Table/rest');

//order
Route::get('order/[:id]','api/Order/get');   //查询
Route::post('order','api/Order/post');       //新增
Route::put('order/:id','api/Order/rest'); //修改
Route::delete('order/:id','api/Order/rest');

//cart
Route::get('cart/[:id]','api/Cart/get');   //查询
Route::post('cart','api/Cart/post');       //新增
Route::put('cart/:id','api/Cart/put'); //修改
Route::delete('cart/:id','api/Cart/rest');

//table
//Route::get('table/[:id]','api/Table/get');   //查询
//Route::post('table','api/Table/post');       //新增
//Route::put('table/:id','api/Table/put'); //修改
//Route::delete('table/:id','api/Table/rest');
