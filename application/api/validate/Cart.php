<?php
namespace app\api\validate;

use think\Validate;

class Cart extends Validate
{
    protected $rule = [
        'merchant_id'    => 'require|number',
        'table_id'       => 'require|number',
        'dish_id'   => 'require|number',
        'dish_num'     => 'require|number'
    ];

}