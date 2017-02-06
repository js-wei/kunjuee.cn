<?php
namespace Admin\Controller;
use Think\Controller;

class AtlasController extends BaseController{
	/**
	 * [index 文章列表]
	 * @return [type]
	 */
	public function index($id){
		$map = $this->_search_arc();
        $map['fid']=$id;
        //排序
        $ordermap = $this->ordermap(I('sort'),I('order'));
        //获取数据
		$arclist=$this->getlist(M('atlas'), $map, $ordermap);
		$this->arclist= $arclist;
		$this->display();
	}
	public function add($aid=0){
		if($aid){
			$m = D('atlas');
			$article =$m->find($aid);
			$article['items']=explode(',',$article['items']);
			$this->article = $article;
		}
		$this->display();
	}

	public function addatlas($id=0){
		$this->display();
	}

	/**
	 * [status 修改状态]
	 * @return [type]
	 */
	public function status(){
		if(!$this->_status(I('id'),'atlas',I('t'))){
			$this->error('操作失败');
		}
		$this->_redirect('index?id='.I('aid'));
	}
	/**
	 * [Insert 添加文章]
	 */
	public function Insert(){
		$data=$_POST;
		$data['date']=time();
		$attr=$this->makeAttr($_POST['attr']);	//重置属性
		$data['content']=I('content',htmlspecialchars);
		$data=array_merge($data,$attr);
		
		if(empty($data['id'])){
			if(!M('atlas')->add($data)){
				$this->ajaxReturn(['status'=>0,'msg'=>'添加失败请重试']);
			}
			$this->ajaxReturn(['status'=>1,'msg'=>'添加成功','redirect'=>U('index?id='.I('cid').'&p='.I('p'))]);
		}else{
			if(!M('atlas')->save($data)){
				$this->ajaxReturn(['status'=>0,'msg'=>'修改失败请重试']);
			}
			$this->ajaxReturn(['status'=>1,'msg'=>'修改成功','redirect'=>'./index?id='.I('cid').'&p='.I('p')]);
		}
		//$this->_redirect('index?id='.I('cid').'&p='.I('p'));
		//$this->redirect();	
	}
	

	/**
	 * [update 更新视图]
	 * @return [type]
	 */
	public function update(){
		$this->article=$article=M('atlas')->find(I('id'));
		if($article['image']){
			$this->images=$images=array_filter(explode(',',$article['image']));
		}
		$this->display('edit');
	}
	
	/**
	 * [delete 更新处理函数]
	 * @return [type]
	 */
	public function updatehandler(){
	
		$data=$_POST;
		$data['date']=time();
		$attr=$this->makeAttr($_POST['attr']);	//重置属性
		$data['content']=I('content',htmlspecialchars);
		$data=array_merge($data,$attr);
       
		if(!M('atlas')->save($data)){
			$this->ajaxReturn(['status'=>0,'msg'=>'修改失败请重试']);
		}
		$this->ajaxReturn(['status'=>1,'msg'=>'修改成功','redirect'=>'./index?id='.I('cid').'&p='.I('p')]);
		
	}
	/**
	 * [_search_arc 搜索]
	 * @return [type]
	 */
	protected function _search_arc(){
		$map=array();
		$username=I('name');
		$status=I('status');
		if($status>-1&&$status!=""){
			$map['status']=array('eq',$status);
		}
		if(I('date')!=''){
			$date = explode('-',I('date'));
			$map['date'] = array(array('egt',strtotime($date[0])),array('elt',strtotime($date[1].' 24:00:00')),'and');
		}

		if($username){
			$map['title']=array('like','%'.$username.'%');
		}
		
		$this->search=array(
			'name'=>$username,
			'status'=>$status,
			'date' =>I('date')
			);
		return $map;
	}

}