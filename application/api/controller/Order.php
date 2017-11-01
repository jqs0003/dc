<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/23 0023
 * Time: 22:36
 */
namespace app\api\controller;
use app\common\controller\Restful;
use lib\Func;
use think\Db;
use think\Log;

class Order extends Restful
{
    public function get($id='')
    {
        $model = model($this->model);
        if ( $id)
        {
            $data = $model->where(array('id'=>$id, 'status'=>1))->find();
        } else {
            //TODO 查询订单限制
//            $params = array();
//            if ( isset($_GET['']) && $_GET[''])
//            {
//
//            }
            $data = $model->where(array('status'=>1))->paginate(10);
        }
        $data = empty($data)?$data:$data->toArray();
        return json(array('data'=>$data, 'code'=>200, 'msg'=>'查询成功'));
    }

    public function post()
    {
        $params = array(
            'merchant_id'=>$_POST['merchant_id'],
            'table_id'=>$_POST['table_id'],
            'total_price'=>$_POST['total_price'],
            'create_time'=>time(),
            'order_status'=>1,
            'pay_status'=>1
        );

        $table = Db::table('table')->where(array('id'=>$params['table_id'], 'status'=>1))->find();
        if(!$table || $table['merchant_id'] != $params['merchant_id'])
        {
            return json(array('data'=>array(), 'code'=>-1, 'msg'=>'未找到对应的桌位'));
        }

        $dish_info = json_decode($_POST['dish_info'], true);
        $ids = Func::getFieldDataFrom('dish_id', $dish_info);
        $dish = Db::table('dish')->where('id', 'in', $ids)->where(array('status'=>1))->select();
        if (count($dish) != count($ids))
        {
            return json(array('data'=>array(), 'code'=>-1, 'msg'=>'部分菜品可能已经下线，请从新下单'));
        }
        $total_price = 0;
        $dish_info = Func::indexByField('dish_id', $dish_info);

        foreach ($dish as $d)
        {
            if ($d['dish_status'] == 1){
                $total_price += $d['discount_price'] * $dish_info[$d['id']]['dish_num'];
            } else {
                $total_price += $d['price'] * $dish_info[$d['id']]['dish_num'];
            }
        }

        if ($params['total_price'] != $total_price)
        {
            return json(array('data'=>array(), 'code'=>-1, 'msg'=>'菜品总价异常，请从新下单'));
        }

        Db::startTrans();
        try {
            $res = Db::table('order')->insertGetId($params);
            if (!$res) {
                return json(array('data' => array(), 'code' => -1, 'msg' => '订单生成失败'));
            }

            $items = array();
            foreach ($dish as $d) {
                $temp = array(
                    'order_id' => $res,
                    'dish_id' => $d['id'],
                    'dish_name' => $d['dish_name'],
                    'dish_num' => $dish_info[$d['id']]['dish_num'],
                    'create_time' => time()
                );
                if ($d['dish_status'] == 1) {
                    $temp['dish_price'] = $d['discount_price'];
                } else {
                    $temp['dish_price'] = $d['price'];
                }
                $temp['total_price'] = $temp['dish_price'] * $temp['dish_num'];
                $items[] = $temp;
            }
            Db::table('order_item')->insertAll($items);
            Db::commit();
        }catch (\Exception $e) {
            Log::error($e->getMessage());
            // 回滚事务
            Db::rollback();
            return json(array('data' => array(), 'code' => -1, 'msg' => $e->getMessage()));
        }

        return json(array('code' => 200, 'msg' => '订单生成成功'));
    }
}