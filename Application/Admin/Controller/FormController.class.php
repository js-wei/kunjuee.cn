<?php
namespace Admin\Controller;
use Think\Controller;

class FormController extends BaseController{
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
		$arclist=$this->getlist(M('form'), $map, $ordermap);
		foreach ($arclist as $k => $v) {
			$arclist[$k]['type1']=$this->get_field_type($v['type']);
		}
		$this->arclist= $arclist;
		$this->display();
	}
	public function add($aid=0){
		if($aid){
			$m = D('form');
			$article =$m->find($aid);
			$article['items']=explode(',',$article['items']);
			$this->article = $article;
		}
		$this->display();
	}

	public function see($id=0){
		$this->column = $column = M('column')->find($id);
		$map = $this->_search_arc();
        //排序
        $ordermap = $this->ordermap(I('sort'),I('order'));
        $m = D('form');
		$this->article=$article =$m->where(['fid'=>$id,'status'=>0])->select();
        //获取数据
		$this->arclist=$arclist=$this->getlist(M($column['name']), $map, $ordermap);
		$this->display();
	}

	public function formsee($id=0,$mod=''){
		if(!$id || empty($mod)){
			//$this->ajaxReturn(['status'=>0,'msg'=>'获取数据失败']);
		}
		$this->article=$article =M($mod)->find($id);
		$name =M('column')->field('id')->where(['name'=>$mod])->find();
		$this->form=$form =D('form')->where(['fid'=>$name['id'],'status'=>0])->select();
		$this->display();
	}
	public function formdel($id=0,$mod=''){
		if(!$id || empty($mod)){
			$this->ajaxReturn(['status'=>0,'msg'=>'参数错误']);
		}
		$m = M($mod);
		if(!$m->delete($id)){
			$this->ajaxReturn(['status'=>0,'msg'=>'删除失败']);
		}
		$this->ajaxReturn(['status'=>1,'msg'=>'删除成功','redirect'=>__SELF__]);
	}
	public function create($id=0,$f=0){
		if(!$id){
			$this->ajaxReturn(['status'=>0,'msg'=>'创建失败']);
		}
		
		$article =D('form')->where(['fid'=>$id,'status'=>0])->select();
		$column = D('column')->find($id);
		$i = M()->query('SHOW TABLES LIKE "%'.C('DB_PREFIX').$column['name'].'%"');
		if($i && !$f){
			$this->ajaxReturn(['status'=>2,'msg'=>'已经存在,将会清除所有数据,是否重新创建?','redirect'=>U('create?id='.$id."&f=1")]);
		}
		if($i && $f){
			M()->execute('DROP TABLE IF EXISTS '.C('DB_PREFIX').$column['name']);
		}
		$sql = "CREATE TABLE IF NOT EXISTS `".C('DB_PREFIX').$column['name']."` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '规则id,自增主键',";
	    foreach ($article as $k => $v) {
	    	if($v['type']==4){
	    		$sql .=" `".$v['name']."` int(11) NOT NULL DEFAULT 0 COMMENT '".$v['title']."',";
	    	}else{
	    		$sql .=" `".$v['name']."` varchar(500) NOT NULL DEFAULT '' COMMENT '".$v['title']."',";
	    	}
	    }
	    $sql .=" `uid` int(11) NOT NULL DEFAULT 0 COMMENT '用户id',`date` int(11) NOT NULL DEFAULT 0 COMMENT '添加时间',`sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序：越小越靠前',`status` int(1) NOT NULL DEFAULT 0 COMMENT '状态：0正常，1禁用', PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT '".$column['title']."表'";
	    
	    try {   
			M()->execute($sql); 
			$this->ajaxReturn(['status'=>1,'msg'=>'创建成功']);
		} catch (Exception $e) {   
			$this->ajaxReturn(['status'=>0,'msg'=>'创建失败']); 
			exit();   
		} 
	}
	/**
	 * [status 修改状态]
	 * @return [type]
	 */
	public function status(){
		if($_POST['p']!=''){
			if(!$this->_status($_REQUEST['id'],$_POST['p'],$_REQUEST['t'])){
	            $this->error('操作失败');
	        }
		}else{
			if(!$this->_status($_REQUEST['id'],'form',$_REQUEST['t'])){
	            $this->error('操作失败');
	        }
		}
		
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
	public function formhandler(){
		$data=$this->_param();
		$attr=$this->makeAttr($_POST['auth']);	//重置属性
		$data=array_merge($data,$attr);
	
		$m = D('form');
		unset($data['auth']);
		if(I('post.type')==5){
			$data['items'] = implode(',',I('post.items'));
		}
		if(I('post.type')==4){
			$ss = $_POST['items'][0]?$_POST['items'][0].',':'是'.',';
			$ss.=$_POST['items'][1]?$_POST['items'][1]:'否';
			$data['items'] = $ss;
		}
		
		if($data['id']){
			$data['dates']=time();
			if(!$m->save($data)){
				$this->ajaxReturn(['status'=>0,'msg'=>'修改失败']);
			}
			$this->ajaxReturn(['status'=>1,'msg'=>'修改成功','redirect'=>U('index?p='.I('p').'&id='.I('post.fid'))]);
		}else{
			$data['date']=time();
			if(!$m->add($data)){
				$this->ajaxReturn(['status'=>0,'msg'=>'添加失败']);
			}
			$this->ajaxReturn(['status'=>1,'msg'=>'添加成功','redirect'=>U('index?p='.I('p').'&id='.I('post.fid'))]);
		}
	}
	/**
	 * [delete 删除操作]
	 * @return [type]
	 */
	public function delete(){
		if(!M('article')->delete(I('id'))){
			$this->error('操作失败');
		}
		$this->_redirect('index?id='.I('cid').'&p='.I('p'));
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
		return $map;
	}
	/**
	 * [get_field_type 获取类型]
	 * @param  integer $type [description]
	 * @return [type]        [description]
	 */
	protected function get_field_type($type=0){
		if(!$type){
			return '';
		}
		switch ($type) {
			case 1:
				$type = '字符串';
				break;
			case 2:
				$type = '数字';
				break;
			case 3:
				$type = '日期/时间';
				break;
			case 4:
				$type = '单选';
			break;
				case 5:
				$type = '多选';
				break;
			case 6:
				$type = '多行文本';
				break;
			default:
				$type = '';
				break;
		}
		return $type;
	}
}