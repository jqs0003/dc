<?php
namespace app\api\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'user_name'  =>  'require|max:25'
    ];

}