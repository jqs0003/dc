<?php
namespace app\api\validate;

use think\Validate;

class Order extends Validate
{
    protected $rule = [
        'merchant_id'  =>  'require|number',
        'total_price'  =>  'require|float'
    ];

}