<?php
namespace Admin\Controller;
use Think\Controller;

class BlogrollController extends BaseController{
	/**
	 * [index 文章列表]
	 * @return [type]
	 */
	public function index($id=0){
	
		$map = $this->_search_arc();
        $map['column_id']=$id;
        //排序
        $ordermap = $this->ordermap(I('sort'),I('order'));
        //获取数据
		$this->arclist=$arclist=$this->getlist(M('Blogroll'), $map, $ordermap);
		$this->display();
	}

	public function add($id=0){
		if($id){
			$m = D('Blogroll');
			$this->article = $article =$m->find($id);
		}
		$this->display();
	}
	/**
	 * [status 修改状态]
	 * @return [type]
	 */
	public function status(){
		if(!$this->_status($_REQUEST['id'],'Blogroll',$_REQUEST['t'],'logo')){
            $this->error('操作失败');
        }
	}
	/**
	 * [add_carousel 添加]
	 */
	public function add_carousel(){
		$m = D('Blogroll');
		if(!$m->create($_POST)){
			$this->ajaxReturn(['status'=>0,'msg'=>$m->getError()]);
		}
		$data = $_POST;
		$data['time']=time();
		$u = new UploadifyController();
		$u->delmgByWhere1($m,['id'=>$data['id']],'logo');		//删除原先图片
        $data['thumb']=$this->get_thumb_path($data['logo']);
		if($data['id']){
			if(!$m->save($data)){
				$this->ajaxReturn(['status'=>0,'msg'=>'修改失败']);
			}
			$this->ajaxReturn(['status'=>1,'msg'=>'修改成功','redirect'=>U('index?p='.I('p'))]);
		}else{
			if(!$m->add($data)){
				$this->ajaxReturn(['status'=>0,'msg'=>'添加失败']);
			}
			$this->ajaxReturn(['status'=>1,'msg'=>'添加成功','redirect'=>U('index?p='.I('p'))]);
		}
	}
	/**
	 * [_search_arc 搜索]
	 * @return [type]
	 */
	protected function _search_arc(){
		$map=array();
		$username=I('k');
		$status=I('q');
		if($status>-1&&$status!=""){
			$map['status']=array('eq',$status);
		}
		
		$map['title']=array('like','%'.I('k').'%');
		$this->search=array(
			'k'=>$username,
			'q'=>$status
			);
		//p($map);die;
		return $map;
	}
}