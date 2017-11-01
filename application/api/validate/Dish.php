<?php
namespace app\api\validate;

use think\Validate;

class Dish extends Validate
{
    protected $rule = [
        'dish_name'         =>  'require|max:25',
        'category_id'       =>  'require|number',
        'price'             =>  'require|float',
        'discount_price'    =>  'float',
        'dish_status'       =>  'number',
        'image_url'         =>  'max:256'
    ];

}