<?php
namespace app\common\controller;

use think\Request;
use think\controller\Rest;
use think\Loader;
use think\Response;
use think\Session;

class Restful extends Rest
{
    protected $model='';

    public function rest($id=''){
        $this->model = Request::instance()->controller();
        switch ($this->method){
            case 'get':     //查询
                $this->read($id);
                break;
            case 'post':    //新增
                $this->add();
                break;
            case 'put':     //修改
                $this->update($id);
                break;
            case 'delete':  //删除
                $this->delete($id);
                break;
        }
    }
    public function read($id)
    {
        $model = model($this->model);
        if ( $id)
        {
            $data = $model->where(array('id'=>$id, 'status'=>1))->find();
        } else {
            $data = $model->where(array('status'=>1))->paginate(10);
        }
        $data = empty($data)?$data:$data->toArray();
        Response::create(array('data'=>$data, 'code'=>200, 'msg'=>'查询成功'), 'json', 200)->send();
    }

    public function add()
    {
        $model = model($this->model);
        $param=Request::instance()->param();//获取当前请求的所有变量（经过过滤）

        $validate = Loader::validate($this->model);
        if(!$validate->check($param)){
            Response::create(array('code'=>-1, 'msg'=>$validate->getError()), 'json', 200)->send();
            exit;
        }

        $param['create_time'] = time();
        unset($param['id']);
        if($model->save($param)){
            Response::create(array('code'=>200, 'msg'=>'添加成功'), 'json', 200)->send();
        }else{
            Response::create(array('code'=>-1, 'msg'=>'添加失败'), 'json', 200)->send();
        }
    }

    public function update($id)
    {
        $model = model($this->model);
        $data = $model->where(array('id'=>$id, 'status'=>1))->find();
        if (!$data)
        {
            Response::create(array('code'=>-1, 'msg'=>'没有找到需要修改的数据'), 'json', 200)->send();
            exit;
        }
        $param=Request::instance()->param();
        $validate = Loader::validate($this->model);
        if(!$validate->check($param)){
            Response::create(array('code'=>-1, 'msg'=>$validate->getError()), 'json', 200)->send();
            exit;
        }

        if($model->where("id",$id)->update($param)){
            Response::create(array('code'=>200, 'msg'=>'修改成功'), 'json', 200)->send();
        }else{
            Response::create(array('code'=>-1, 'msg'=>'修改失败'), 'json', 200)->send();
        }
    }
    public function delete($id)
    {
        $model = model($this->model);
        $data = $model->where(array('id'=>$id, 'status'=>1))->find();
        if (!$data)
        {
            Response::create(array('code'=>-1, 'msg'=>'没有找到需要删除的数据'), 'json', 200)->send();
            exit;
        }
        if($model->where("id",$id)->update(array('status'=>0))){
            Response::create(array('code'=>200, 'msg'=>'删除成功'), 'json', 200)->send();
        }else{
            Response::create(array('code'=>-1, 'msg'=>'删除失败'), 'json', 200)->send();
        }
    }
}
