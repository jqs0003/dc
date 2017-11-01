<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * Email:776329498@qq.com
 * Date: 2017/2/8
 * Time: 11:32
 */

namespace app\api\controller;

use think\controller;
use app\api\controller\Wxpay;
class Wx extends controller
{
    public function index($order_no='2017020453102495'){
        $wxpay = new Wxpay();
        $data=$wxpay->payByOrderNo($order_no);
        $this->assign('amount',$data['amount']);
        $this->assign('order_no',$order_no);
        $this->assign("jsApiParameters" ,$data['jsApiParameters']);
        $this->assign('openid',$this->open_id);
        return $this->fetch('wxpay/pay');
    }
}
