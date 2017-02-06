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
		$config = M('config')->field('dates',true)->find($id);
		$this->conf = str_replace('}', '', str_replace("{", '',str_replace('"','',$config)));
		$this->display();
		
    }
	/**
	 * @author 魏巍
	 * @descrition 高级信息配置
	 */
	public function advanced($id=1){
		$config = M('config')->field('dates',true)->find($id);
		$this->display();
	}
	/**
	 * 基本设置
	 */
	public function addhandler(){
		$m = M('config');
		$data = $this->_param($_POST);
		$data['dates']=time();
		$data['keywords']=I('post.keyword');
		unset($data['keyword']);
		$data['extends'] = $this->get_extends_conf();
		$u = new UploadifyController();
		$u->delmgByWhere1($m,['id'=>$data['id']],'logo');		//删除原先图片
        $data['thumb']=$this->get_thumb_path($data['logo']);
		if(!$m->save($data)){
			$this->ajaxReturn(['status'=>0,'msg'=>'基本设置失败']);
		}
		$this->ajaxReturn(['status'=>1,'msg'=>'基本设置成功','redirect'=>U('basic')]);
	}
	/**
	 * @author 魏巍
	 * @descrition 高级信息配置处理函数
	 */
	public function advancedhandler(){
		$m = M('config');
		$data = $this->_param($_POST);
		if(!$m->save($data)){
			$this->ajaxReturn(['status'=>0,'msg'=>'基本设置失败']);
		}
		$this->ajaxReturn(['status'=>1,'msg'=>'基本设置成功','redirect'=>U('basic')]);
	}
	/**
	 * @alias 扩展参数
	 * @description 获取扩展配置参数
	 * @example $this->get_extends_conf();
	 */
	private function get_extends_conf(){
		$conf = explode(',', I('post.extends'));
		if(!$conf) return '';
		foreach($conf as $k =>$v){
			$temp = explode(':', $v);
			$conf1[$temp[0]]=$temp[1];
		}
		return json_encode($conf1);
	}
}