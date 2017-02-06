<?php
namespace Admin\Controller;
use Think\Controller;

class UserController extends BaseController {
    /**
     * 列表
     */
    public function index(){
        $map=$this->_search();
        //排序
        $ordermap = $this->ordermap(I('sort'),I('order'));
        $this->list=$this->getlist(M('member'),$map,$ordermap);
        $this->display();
    }
    public function setPassword(){
        $this->display();
    }

    /**
     * 修改密码
     * @param string $old_password
     * @param string $new_password
     * @param string $comfr_password
     */
    public function setPasswordHandler($old_password='',$new_password='',$comfr_password=''){
        if(empty($old_password)){
            $this->ajaxReturn(['status'=>0,'msg'=>'原始密码不能为空']);
        }
        if(empty($new_password)){
            $this->ajaxReturn(['status'=>0,'msg'=>'新密码不能为空']);
        }
        if(empty($comfr_password)){
            $this->ajaxReturn(['status'=>0,'msg'=>'确认密码不能为空']);
        }
        $id =  session(C('SESSION_PREFIX').'_id');
        $admin = M('admin')->find($id);
        if($admin['password']!=md5($old_password)){
            $this->ajaxReturn(['status'=>0,'msg'=>'原始密码不正确']);
        }

      
        if(!M('admin')->save([
            'id'=>$id,
            'password'=>md5($new_password),
            'date'=>time()
        ])){
            $this->ajaxReturn(['status'=>0,'msg'=>'修改失败请重试'.M('admin')->getlastsql()]);
        }
        $this->ajaxReturn(['status'=>1,'msg'=>'修改成功']);
    }
    /**
     * 修改状态
     */
    public function  status(){
        $this->_status('member');
    }

    public function check(){
        $data=$this->_param();
        $this->vo=$member=M('member')->find($data['id']);
        $this->display();
    }

    /**
     *搜素方法
     */
    protected function _search(){
        $this->lang=$lang=!empty($_COOKIE['think_language'])?strtolower(cookie('think_language')):'zh-cn';
        //处理基本查询
        $map = array();
        //参数
        ($name = I('get.name','','trim')) && $map['username'] = array('like', '%' . $name . '%');
        //状态（正常，禁用）
        if ($_GET['status'] == null) {
            $status = -1;
        } else {
            $status = intval($_GET['status']);
        }
        $status >= 0 && $map['status'] = array('eq', $status);
        //$map['lang']=$lang;

        //输出
        $this->assign('search', array(
            'name' => $name,
            'status' => $status,
        ));
        return $map;
    }
}
