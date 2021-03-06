<?php
namespace Home\Controller;
use Think\Controller;
class PublicController extends BaseController {
    public function _initialize(){
        parent::_initialize(); // TODO: Change the autogenerated stub
		$this->carousel = $this->get_site_carousel();
    }

    public function addform() {
    	if(!IS_POST){
    		$this->ajaxReturn(['status'=>0,'msg'=>'非法的请求方式']);
    	}
        $param = $this->_param();
        $param['date']=time();
        $m = M($param['m']);
        unset($param['m']);
        if(!$m->add($param)){
			$this->ajaxReturn(['status'=>0,'msg'=>'操作失败请稍后重试']);
        }
        $this->ajaxReturn(['status'=>1,'msg'=>'操作成功,谢谢您提出的宝贵意见']);
    }

    protected function _param(){
    	$_result = [];
    	foreach ($_POST as $k => $v) {
    		if(!is_array($v)){
				$_result[$k] = htmlspecialchars($v);
    		}else{
    			$_result[$k] = implode(',',$v);
    		}
	    	
	    }
	    return $_result;
    }
}