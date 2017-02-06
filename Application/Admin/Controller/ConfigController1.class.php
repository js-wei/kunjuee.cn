<?php
namespace Admin\Controller;
use Think\Controller;
/**
 * 
 */
class ConfigController extends BaseController {
  	/**
	 * [index 首页]
	 * @return [type] [description]
	 */
    public function basic($id=1){
		$this->conf = $config = M('config')->find($id);
		$this->display();
		
    }

	public function addhandler(){
		$data = $this->_param($_POST);
		$data['date']=time();
		$data['keywords']=I('post.keyword');
		unset($data['keyword']);

		if(!M('config')->save($data)){
			$this->error('基本设置失败');
		}
		$this->redirect('basic');
	}
}