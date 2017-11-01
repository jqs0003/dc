<?php
namespace app\api\validate;

use think\Validate;

class Merchant extends Validate
{
    protected $rule = [
        'merchant_name'  =>  'require|max:64',
        'consignee'      =>  'max:64',
        'consignee_phone'=>  'number',
        'image_url'      =>  'max:256',
        'address'        =>  'max:256',
        'desc'           =>  'max:256'
    ];

}