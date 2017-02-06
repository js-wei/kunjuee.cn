<?php
namespace Admin\Controller;
use Service;
/**
 * 控制器操作
 * @author 魏巍
 *
 */
class ControlController extends BaseController {
    /**
     * 列表页
     */
    public function index(){
        $list = M('model')->order('sort asc')->select();
        //$this->list= $list =\Service\Category::unlimitedForLevel($list);
        $this->list= $list =\Service\Category::LimitForLevel($list);

        $this->display();
    }

    /**
     * 添加页
     */
    public function add(){
        $this->lang=$lang=!empty($_COOKIE['think_language'])?strtolower(cookie('think_language')):'zh-cn';
        $map['lang']=$lang;
        //获取父级控制器
        $this->model=$model = M('model')->find(I('id'));
        $this->model_list = $list = \Service\Category::LimitForLevel(M('model')->where($map)->select());
        $this->display();
    }

    /**
     * 编辑页
     */
    public function edit(){
        $id=I('id');
        $this-> model = M('model')->find($id);
        $this-> model_list = $list = \Service\Category::LimitForLevel(M('model')->where($map)->select());
        $this->display();
    }

    /**
     * 删除页
     */
    public function del(){
        $this->display();
    }

    /**
     * 修改状态
     */
    public function status(){
        if(!$this->_status($_REQUEST['id'],'model',$_REQUEST['t'],'')){
            $this->error('操作失败');
        }
    }

    /**
     * 修改处理函数
     */
    public function edithandler(){
        $data=$this->_param();
        $data['date']=time();
        if(!M('model')->save($data)){
           $this->ajaxReturn(['status'=>0,'msg'=>'修改失败']);
        }
        $this->ajaxReturn(['status'=>1,'msg'=>'修改成功','redirect'=>U('index')]);
    }

    /**
     * 添加处理函数
     */
    public function addhandler(){
        $data=$this->_param();
        $data['date']=$data['dates']=time();
        if(!M('model')->add($data)){
           $this->ajaxReturn(['status'=>0,'msg'=>'添加失败']);
        }
        $this->ajaxReturn(['status'=>1,'msg'=>'添加成功','redirect'=>U('index')]);
    }
    /**
     * 搜索
     */
    public function search(){
        $data=$_REQUEST;
        $from =  $data['from'];
        $to =  $data['to'];
        if(!empty($from) || !empty($to)){
            if(!empty($from)){
                $map['date'] = array('gt',strtotime($from));
            }
            if(!empty($to)){
                $map['date'] = array('lt',strtotime($to));
            }
        }

        if(!empty($from) && !empty($to)){
            $map['date'] = array('between',strtotime($from).','.strtotime($to));
        }

        if(!empty($data['name'])){
            $map['name'] = array('like', '%' . $data['name'] . '%');
        }
       ;
        if($data['status']==1 || $data['status']==0){
            $map['status'] = array('eq', $data['status']);
        }

        $this->search=$data;
        $this->list= $list =\Service\Category::unlimitForLevel($this->getlist(M('model'),$map));
        $this->display('index');
    }
}
