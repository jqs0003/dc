<?php
namespace app\api\validate;

use think\Validate;

class Category extends Validate
{
    protected $rule = [
        'category_name'  =>  'require|max:25',
        'merchant_id'    => 'require|number'
    ];

}