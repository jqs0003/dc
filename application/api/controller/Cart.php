<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/23 0023
 * Time: 22:36
 */
namespace app\api\controller;

use app\common\controller\Restful;
use think\Db;
use think\Session;

class Cart extends Restful
{
    public function get()
    {
        $user_id = 1;
        $data = Db::table('cart')->where(array('user_id' => $user_id, 'merchant_id' => 1))->paginate(10);
        return $data->toArray();
    }

    public function post()
    {
        $params = array(
            'user_id'=>1,
            'table_id'=>$_POST['table_id'],
            'merchant_id'=>$_POST['merchant_id'],
            'dish_id'=>$_POST['dish_id'],
            'dish_num'=>$_POST['dish_num']
        );

        $dish = Db::table('dish')->where(array('id'=>$params['dish_id'], 'status'=>1))->find();
        if ($dish['dish_status'] == 1)
        {
            $params['price'] = $dish['discount_price'];
        } else {
            $params['price'] = $dish['price'];
        }
        $params['total_price'] = $params['dish_num'] * $params['price'];
        $params['dish_name'] = $dish['dish_name'];
        $res = Db::table('cart')->insert($params);
        if(!$res)
        {
            return json(array('code'=>200, 'msg'=>'添加失败'));
        }
        return json(array('code'=>200, 'msg'=>'添加成功'));
    }
}