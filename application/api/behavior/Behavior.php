<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/23 0023
 * Time: 22:36
 */
namespace app\api\behavior;
use think\Db;
use think\Session;

class Behavior
{
    private $check_cont = array('category', 'merchant', 'table');
    public function run()
    {
        $this->checkLogin();
    }
    public function checkLogin()
    {
        echo request()->controller();
        exit;
    }
}