<?php
namespace Admin\Controller;
use Think\Controller;

class ArticleController extends BaseController{
	/**
	 * [index 文章列表]
	 * @return [type]
	 */
	public function index(){
		$id=!empty($_REQUEST['column_id'])?I('column_id'):I('cid');
		$id = !empty($id) ? $id: I('id');
		$map = $this->_search_arc();
        $map['column_id']=$id;
        //排序
        $ordermap = $this->ordermap(I('sort'),I('order'));
        //获取数据
		$this->arclist=$arclist=$this->getlist(M('article'), $map, $ordermap);
		$this->display();
	}
	/**
	 * [recycle 回收站]
	 * @return [type]
	 */
	public function recycle(){
		$this->display('index');
	} 

	public function add(){
		$this->column_list = $list = \Service\Category::LimitForLevel(M('column')->where($map)->select());
		$this->display();
	}

	public function edit(){
		$id= I('aid');
		$this->column_list = $list = \Service\Category::LimitForLevel(M('column')->where($map)->select());
		$this->article = $article = M('article')->find($id);
		$this->display();
	}


	/**
	 * [status 修改状态]
	 * @return [type]
	 */
	public function status(){
		if(!$this->_status(I('id'),'article',I('t'))){
			$this->error('操作失败');
		}
		$this->_redirect('index?id='.I('aid'));
	}
	/**
	 * [Insert 添加文章]
	 */
	public function Insert(){
		
		$data=$_POST;
		$data['create_time']=time();
		$attr=$this->makeAttr($_POST['attr']);	//重置属性
		$data['content']=I('content',htmlspecialchars);
		$data=array_merge($data,$attr);

		$data['startTime']=!empty($data['startTime'])?strtotime($data['startTime']):0;
		$data['endTime']=!empty($data['endTime'])?strtotime($data['endTime']):0;


		$data['column_id']=!empty($data['column_id'])?$data['column_id']:$data['cid'];
		
		
		if(!M('article')->add($data)){
			//p(M('article')->getlastsql());die;			
			//$this->error('操作失败');
			$this->ajaxReturn(['status'=>0,'msg'=>'添加失败请重试']);
		}
		$this->ajaxReturn(['status'=>1,'msg'=>'添加成功','redirect'=>U('index?id='.I('cid').'&p='.I('p'))]);
		//$this->_redirect('index?id='.I('cid').'&p='.I('p'));
		//$this->redirect();	
	}
	

	/**
	 * [update 更新视图]
	 * @return [type]
	 */
	public function update(){
		$this->article=$article=M('article')->find(I('id'));
		if($article['image']){
			$this->images=$images=array_filter(explode(',',$article['image']));
		}
		$this->display();
	}
	
	/**
	 * [delete 更新处理函数]
	 * @return [type]
	 */
	public function updatehandler(){
	
		$data=$_POST;
		$data['create_time']=time();
		$attr=$this->makeAttr($_POST['attr']);	//重置属性
		$data['content']=I('content',htmlspecialchars);
		$data=array_merge($data,$attr);
        $image='';

		$data['startTime']=!empty($data['startTime'])?strtotime($data['startTime']):0;
		$data['endTime']=!empty($data['endTime'])?strtotime($data['endTime']):0;
		
		if(!M('article')->save($data)){
			//p(M('article')->getlastsql());die;
			//$this->error('操作失败');
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
			$map['create_time'] = array(array('egt',strtotime($date[0])),array('elt',strtotime($date[1].' 24:00:00')),'and');
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