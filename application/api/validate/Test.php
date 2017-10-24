<?php
namespace app\index\validate;

use think\Validate;

class Test extends Validate
{
    protected $rule = [
        'name'  =>  'require|max:25'
    ];

}