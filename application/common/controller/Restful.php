<?php
namespace app\common\controller;

use think\Request;
use think\controller\Rest;
use think\Loader;
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
    public function read($id){
        $model = model($this->model);
        if ( $id)
        {
            $data = $model->where(array('id'=>$id, 'status'=>1))->find();
        } else {
            $data = $model->where(array('status'=>1))->paginate(10);
        }
        $data = empty($data)?:$data->toArray();
        return $this->response($data);
    }

    public function add(){
        $model = model($this->model);
        $param=Request::instance()->param();//获取当前请求的所有变量（经过过滤）

        $validate = Loader::validate($this->model);
        if(!$validate->check($param)){
            return json(["status"=>0, 'msg'=>$validate->getError()]);
        }

        unset($param['id']);
        if($model->save($param)){
            return json(["status"=>1]);
        }else{
            return json(["status"=>0]);
        }
    }

    public function update($id){
        $model = model($this->model);
        $data = $model->where(array('id'=>$id, 'status'=>1))->find();
        if (!$data)
        {
            return $this->response(array(), 'json', 200);
        }
        $param=Request::instance()->param();
        $validate = Loader::validate($this->model);
        if(!$validate->check($param)){
            return json(["status"=>0, 'msg'=>$validate->getError()]);
        }

        if($model->where("id",$id)->update($param)){
            return json(["status"=>1]);
        }else{
            return json(["status"=>0]);
        }
    }
    public function delete($id){

        $model = model($this->model);
        $data = $model->where(array('id'=>$id, 'status'=>1))->find();
        if (!$data)
        {
            return json(["status"=>1]);
        }
        if($model->where("id",$id)->update(array('status'=>0))){
            return json(["status"=>1]);
        }else{
            return json(["status"=>0]);
        }
    }
}
