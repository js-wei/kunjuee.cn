<?php
namespace Home\Controller;
use Think\Controller;
use Service\Category;

class BaseController extends Controller {
  	protected function _initialize(){
		header('Content-type:text/html;charset=utf-8;');
		set_time_limit(0);
        //加载网站配置
        $this->site =  $this->get_site_config();
        if(IS_GET){
            $this->nav_list = $nav_list = $this->get_nav_list();
        }
		//获取推荐的案子
		$this->case = $case = $this->get_case_com();
	}

    /**
     * 获取网站基本配置
     */
    protected function get_site_config(){
        $config = M('config')->field('id',true)->find();
        return $config;
    }
	//获取推荐案子
	protected function get_case_com($l=12){
		$l=(I('get.lth')!='')?I('get.lth'):$l;
		$column = M('column')->field('id,fid,title,name,type,uri,last,tpl')->where('status=0')->order('sort asc')->select();
		$child = Category::getChildrenById($column,5);
		$child = implode(',',$child);
		$article = M('article')->field('sort',true)->where(['com'=>1,'status'=>0,'colunm_id'=>['in',$child]])->limit($l)->select();
		return $article;
	}
	/**
     * 获取轮播图
     */
    protected function get_site_carousel(){
		if($this->site['carousel']==1){
			return '';
		}
        $carousel = M('carousel')->field('id',true)->where(['com'=>0])->limit($this->site['sum'])->order('sort asc')->select();
        return $carousel;
    }
    /**
     * 获取栏目
     * @return \Service\Array
     */
    protected function get_nav_list(){
        $column = M('column')->field('id,fid,title,name,type,uri,last,tpl')->where('status=0')->order('sort asc')->select();
        session('column_list',$column);
        $column_child=array();
        if(I('get.id')!='') {
            $column1 = M('column')->field('id,fid,title,type,uri,name,last,tpl')->where(['status' => 0, 'name' => I('get.id')])->order('sort asc')->find();
            $parent = Category::getparents($column, $column1);
            if (!empty($parent)) {
                //获取模板
                if ($parent[0]['tpl'] == '0') {
                    $tpl = 'list';
                } else if ($parent[0]['tpl'] == '1') {
                    $tpl = $parent[0]['name'];
                } else {
                    $tpl = $parent[0]['tpl'];
                }

                if ($column1['last'] == 1) {    //最终页面
                    if (count($parent) == 1) {
                        if ($column1['tpl'] == '0') {
                            $this->tpl = $tpl;
                        } else if ($column1['tpl'] == '1') {
                            $tpl = $column1['name'];
                            $this->tpl = $tpl;
                        } else {
                            $this->tpl = $tpl = $column1['tpl'];
                        }
                        session('tpl', $this->tpl);
                        $column1['id'] = $parent[0]['id'];
                        $column_child = M('column')->field('id,fid,title,name,type,uri,last,tpl')->where('status=0 and fid=' . $column1['id'])->order('sort asc')->select();
                    }

                    session('now_nav_first', $column1);
                    session('now_nav_first1', $column1);
                } else {  //非最终页面

                    $column_child = M('column')->field('id,fid,title,name,type,uri,last,tpl')->where('status=0 and fid=' . $column1['id'])->order('sort asc')->select();
                    foreach ($column_child as $k => $v) {
                        if ($v['last'] == 1 && $v['tpl'] == '0') {
                            $column_child[$k]['tpl'] = $tpl;
                        } else if ($v['last'] == 1 && $v['tpl'] == '1') {
                            $column_child[$k]['tpl'] = $v['name'];
                        } else {
                            $column_child[$k]['tpl'] = $v['tpl'];
                        }
                    }
                    session('now_nav_first', $column_child[0]);
                    session('now_nav_first1', $column_child[0]);
                    $this->tpl = $column_child[0]['tpl'];
                    session('tpl', $this->tpl);
                }
            } else {

                if ($column1['tpl'] == '0') {
                    $tpl = 'list';
                } else if ($column1['tpl'] == '1') {
                    $tpl = $column1['name'];
                } else {
                    $tpl = $column1['tpl'];
                }
                $column_child = M('column')->field('id,fid,title,name,type,uri,last,tpl')->where('status=0 and fid=' . $column1['id'])->order('sort asc')->select();
                foreach ($column_child as $k => $v) {
                    if ($v['last'] == 1 && $v['tpl'] == '0') {
                        $column_child[$k]['tpl'] = $tpl;
                    } else if ($v['last'] == 1 && $v['tpl'] == '1') {
                        $column_child[$k]['tpl'] = $v['name'];
                    } else {
                        $column_child[$k]['tpl'] = $v['tpl'];
                    }
                }
                session('now_nav_first', $column_child[0]);
                session('now_nav_first1', $column_child[0]);
                $this->tpl = $column_child[0]['tpl'];
                session('tpl', $this->tpl);
            }
            $this->assign('navbar', $column_child);
        }
        
		
        $this-> location = $location = $this->get_location($column);
        return Category::unlimitedForLevel($column);
    }

    protected function getColumnByAid(){
        $a =  M('article')->field('column_id')->find(I('get.aid'));
        $column_child = M('column')->field('id,fid,title,name,type,uri,last,tpl')->find($a['column_id']);
        return $column_child;
    }

    /**
     * 获取路径导航
     * @param $column
     * @return string
     */
    protected function get_location($column){
        $column1 = (I('get.aid')>0)?$this->getColumnByAid():session('now_nav_first1');
        $parent = Category::get_location($column, $column1);
        session('now_nav_first1',null);
        return $parent;
    }

    /**
     * 获取分页数据
     * @param type $model模型名(默认获取当前model)
     * @param type $map条件
     * @param type $order排序
     * @param type $field需要查询的字段，默认全部
     * @param type $pagination为每页显示的数量，默认为配置中的值
     * @return type返回结果数组
     */
    protected function getlist($model = '', $map = '', $order = '',$group = '', $pagination = '', $field = '*') {

        $model=!empty($model)?$model:M(CONTROLLER_NAME);

        $count = $model->where($map)->cache(true)->group($group)->count('*');
        $pagination = $pagination ? $pagination : C('PAGE_SIZE');

        $p = new \Think\Page($count, $pagination);
        $p->setConfig('header', '<a class="rows">当前 %NOW_PAGE%/%TOTAL_PAGE%页</a>');
        $p->setConfig('prev', '上一页');
        $p->setConfig('next','下一页');
        $p->setConfig('last', '最后一页');
        $p->setConfig('first','第一页');
        $p->setConfig('theme', '%UP_PAGE%%HEADER%%DOWN_PAGE%');
        $p->allShow = 1;
        $show=$p->show();
        $this->assign('page', $show);
        $res = $model->where($map)->cache(true)->field($field)->group($group)->limit($p->firstRow . ',' . $p->listRows)->order($order)->select();

        return $res;
    }

    protected function get_ajax_list($model = '', $map = '', $order = '', $pagination = '', $field = '*') {

        $model=!empty($model)?$model:M(CONTROLLER_NAME);

        $count = $model->where($map)->cache(true)->count('*');
        $pagination = $pagination ? $pagination : C('PAGE_SIZE');

        $p = new \Think\Page($count, $pagination);
        $p->setConfig('header', '<li class="rows">共%TOTAL_ROW%条记录&nbsp;当前%NOW_PAGE%页/共%TOTAL_PAGE%页</li>');
        $p->setConfig('prev', '上一页');
        $p->setConfig('next','下一页');
        $p->setConfig('last', '最后一页');
        $p->setConfig('first','第一页');
        $p->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');
		$this->allShow = 1;
        $p->lastSuffix = true;  //最后一页不显示为总页数
        $show=$p->show();
        $res = $model->where($map)->cache(true)->field($field)->limit($p->firstRow . ',' . $p->listRows)->order($order)->select();

        return array(
            'status'=>1,
            'count'=>$count,
            'list'=>$res,
            'page'=>$show
        );
    }

    /**
     * 修改密码
     */
    public function check_password(){
        if(IS_POST){
            $id =  session('mid');
            if(empty($id)){
                $this->ajaxReturn(array('status'=>0,'msg'=>'还未登录,请登录修改'));
            }

            $member = M('member')->find($id);

            if(empty($member)){
                $this->ajaxReturn(array('status'=>1,'msg'=>'没有此用户'));
            }

            if($member['password'] != md5(I('post.old_pwd'))){
                $this->ajaxReturn(array('status'=>2,'msg'=>'原密码错误'));
            }

            if(I('post.new_pwd')!=I('post.cofrim_pwd')){
                $this->ajaxReturn(array('status'=>3,'msg'=>'两次输入密码不一样码错误'));
            }
            if(I('post.new_pwd')==I('post.old_pwd')){
                $this->ajaxReturn(array('status'=>4,'msg'=>'原来密码不能和新密码一致'));
            }

            $data=array(
                'id'=>$member['id'],
                'password'=>md5(I('post.new_pwd')),
            );

            if(!M('member')->save($data)){
                $this->ajaxReturn(array('status'=>5,'msg'=>'密码修改失败,请重试!'));
            }
            session('mid',null);
            session('mname',null);
            session('jifen',null);
            $this->ajaxReturn(array('status'=>6,'msg'=>'密码修改成功','redirect'=>U('/login')));
        }
    }


    /**
     * 删除图片
     * @param $path 图片路径
     */
    protected function delImage($path){
    	$path=!empty($path)?$path:I('path');
    	
    	if(!empty($path)){
    		$id=I('id','',intval);
            $index=I('index','',intval);
            $result=M('Article')->find($id);

            $image=array_filter(explode(',', $result['image']));
            unset($image[$index]); 
            $image=implode(',', $image);
         
    		$data=array('id'=>$id,'image'=>$image);
    		$result=M('Article')->save($data);

    		if(!unlink('./Uploads/ueditor/'.$path) || !$result){
    			if(!$result){
    				echo 1;
    			}else{
    				echo 2;
    			}
    		}else{
    			echo 0;
    		}
    	}
    }

    /**
     * 删除文件
     * @param int $id
     */
    public function delFile($id=0){
        $id=$id?$id:I('id','',intval);
        $file=!empty($_POST['file'])?$_POST['file']:'';
       
        if(!unlink('./Uploads/file/'.$file)){  
            echo 0;
        }else{
            $data=array('id'=>$id,'file'=>'');
            $result=M('Article')->save($data);
            echo 1;
        }
    }
  	/**
  	* [_setDel 定时删除]
    * @param integer $time [间隔]
    * @param string  $model [模型]
    * @param string  $type [跨度]
  	*/
    protected function _setDel($time=10,$model='',$type='day'){	
        switch ($type) {
        	case 'day':
        		$after=time()- $time*24*60*60;
        		break;
        	case 'week':
        		$after=time()- $time*24*60*60*7;
        		break;
        	case 'hour':
        		$after=time()- $time*60*60;
        		break;
        	default:
        		$after=time()- $time*24*60*60;
        		break;
        }
        
        $name=!empty($model)?$model:$this->getActionName();
        $model=M($name);
        $where['create_time']=array('lt',$after);
        $result=$model->where($where)->delete(); 
        return $result;
    }

    /**
     * 获取参数信息
     * @param string $param 参数
     * @return string
     */
    protected function _param($param=''){
        if(empty($param)){
            foreach ($_REQUEST as $k => $v) {
                if($k!='_URL_'){
                    $param[$k]=$v;
                }
            }
        }
        return $param;
    }

    public function lists(){
        $map['status']=0;
        if(empty($_GET['cid'])){
            $map['cid']  = array('in','4,5,6');
        }else{
            $map['cid']  = array('in',I('get.cid'));
        }
        if(!empty($_GET['q'])){
            $map['title'] = array('like','%'.I('get.q').'%');
        }

        if(!empty($_GET['attr'])){
            $attr = explode(',',$_GET['attr']);

            foreach ($attr as $v){
                $map[$v] =1;
            }
        }

        $size = $_GET['sz']?I('get.sz'):5;
        $list = $this->get_ajax_list(M('article'),$map,'create_time desc',$size,"id,title,description,create_time");
        unset($list['page']);
        foreach ($list['list'] as $v){
            $content = htmlspecialchars_decode($v['description']);
            if($_GET['sp']!=0){
                $v['description']=substr($content,0,$_GET['sp']);
            }
            $v['create_time']=date('Y-m-d',$v['create_time']);
            $v['uri']=U('/notices/'.$v['id']);
            $list1[]=$v;
        }
        $list['list']=$list1;
        $this->ajaxReturn($list);
    }

    public function detail($id=0){
        if($id){
            $article =  M('article')->field('id,title,description,content,create_time')->find($id);
            $article['create_time']=date('Y-m-d',$article['create_time']);
            if(!empty($article)){
                $this->ajaxReturn(array('status'=>1,'content'=>$article));
            }else{
                $this->ajaxReturn(array('status'=>0,'msg'=>'没有数据'));
            }

        }
    }

    /**
     * 用户登出
     */
    public function logout(){
        session('mid',null);
        session('mname',null);
        session('jifen',null);
        $this->ajaxReturn(array('status'=>1,'msg'=>'退出成功','redirect'=>U('/')));
    }

    /**
     * 用户登录状态
     */
    public function is_login(){
        $mid = session('mid');
        if(empty($mid)){
            $this->ajaxReturn(array('status'=>0,'msg'=>'您还未登陆,请登录网站','redirect'=>U('/login')));
        }

        $username = M('member')->field('id,username,tel,type,email,real_name,jifen')->where(array("id"=>$mid))->find();
        //p($username);die;
        $username['ptype']='';
        if(empty($username)){
            $this->ajaxReturn(array('status'=>0,'msg'=>'您还未登陆,请登录网站','redirect'=>U('/login')));
        }
        $username['personal']=U('/private');
        $this->ajaxReturn(array('status'=>1,'data'=>$username));
    }


    /**
     * 用户登录
     */
    public function user_login(){
        if(empty($_POST['code'])){
            $this->ajaxReturn(array('status'=>0,'msg'=>'验证码不能为空'));
        }
        $username = M('member')->field('id,username,password,email,real_name,jifen,status')->where(array("username"=>I('post.username')))->find();

        if(empty($username)){
            $this->ajaxReturn(array('status'=>0,'msg'=>'用户名不存在'));
        }
        $pwd = md5(trim(I('post.password')));
        if($pwd!=$username['password']){
            $this->ajaxReturn(array('status'=>1,'msg'=>'密码错误'));
        }

        session('mid',$username['id'],3600*5);
        session('mname',$username['username'],3600*5);
        session('mtype',$username['type'],3600*5);
        $this->ajaxReturn(array('status'=>2,'data'=>$username,'redirect'=>U('/private')));
    }
    /**
     * 用户注册
     */
    public function register_handler(){

        $User = D("Member"); // 实例化User对象
        $pay = D('payway');
        $utype = ($_POST['puserType']==2)?1:0;
        $data = array(
            'username'=>I('post.username'),
            'password'=>I('post.password_md5'),
            'real_name'=>I('post.contacts'),
            'email'=>I('post.email'),
            'tel'=>I('post.phonenumber'),
            'type'=>$utype,
            'create_time'=>time(),
        );

        if($_POST['makeMoney']==3){
            $data1['pay_type']=1;
            $data1['bank_type']=I('post.accountIfor');                 //开户银行
            $data1['bank_name']=I('post.bankAddress');             //支行
            $data1['pay_account']=I('post.bankNumber');            //账户
            $data1['pay_getname']=I('post.getMoney');             //开户人
            $data1['pay_default']=1;
        }
        if($_POST['makeMoney']==2){
            $data1['pay_type']=0;
            $data1['pay_account']=I('post.AliPayName');
            $data1['pay_getname']=I('post.AliGetMoney');
            $data1['pay_default']=1;
        }
        
        if (!$User->create($data)){
            // 如果创建失败 表示验证没有通过 输出错误提示信息
            $this->ajaxReturn(array('status'=>0,'msg'=>$User->getError()));
        }
        if (!$pay->create($data1)){
            // 如果创建失败 表示验证没有通过 输出错误提示信息
            $this->ajaxReturn(array('status'=>0,'msg'=>$pay->getError()));
        }
        $id = $User->add($data);
        if($id){
            $data1['mid']=$id;
            $pay->add($data1);
            $this->ajaxReturn(array('status'=>1,'msg'=>'恭喜您注册成功','redirect'=>U('/login')));
        }else{
            $this->ajaxReturn(array('status'=>0,'msg'=>'注册失败请重试','redirect'=>U('/register')));
        }
    }
    /**
     * 检查用户名是否存在
     */
    public function check_username(){
        $username= I('post.username');
        $user = M('member')->where(array('username'=>$username))->find();

        if(!empty($user)){
            $this->ajaxReturn(array('status'=>1,'msg'=>'用户名已存在'));
        }
        $this->ajaxReturn(array('status'=>0,'msg'=>'恭喜您可以注册'));
    }
    /**
     * 验证码
     */
    public function verify(){
        $verify = new \Think\Verify();
        //$verify->imageW=180;
        $verify->imageH=50;
        $verify->length   = 4;
        $verify->entry();
    }

    /**
     * 校验验证码
     * @param $code
     */
    public function check_code($code){
        if($this->check($code)){
            $this->ajaxReturn(array('status'=>1,'msg'=>'验证码输入正确'));
        }else{
            $this->ajaxReturn(array('status'=>0,'msg'=>'验证码输入错误'));
        }
    }

    /**
     * 验证码检查
     */
    protected function check($code, $id =""){
        $verify = new \Think\Verify();
        return $verify->check($code,$id);
    }

    public function kefu($qq=0){
        if(!$qq){
            exit('QQ号不能为空');
        }
        $url = 'http://wpa.qq.com/msgrd?v=3&uin='.$qq.'&site=qq&menu=yes';
        header('Location:'.$url);
    }

    /**
     * 获取友情链接
     * @param int $l        长度
     * @param int $sort     排序
     */
    public function get_flink($l=10,$sort=0){
        if($sort){
            $sort='sort desc';
        }else{
            $sort='sort asc';
        }
        $list =  M('flink')->field('id,title,description,ico,uri')->where('status=0')->order($sort)->limit($l)->select();
        foreach ($list as $v){
            $v['ico'] ='/Uploads/'.$v['ico'];
            $list1[]=$v;
        }

        if(empty($list)){
            $this->ajaxReturn(array('status'=>0,'msg'=>'没有添加友情链接'));
        }
        $this->ajaxReturn(array('status'=>1,'msg'=>'查询成功','list'=>$list1));
    }

    /**
     * 获取客服列表
     * @param int $lg       长度
     */
    public function get_kefu($lg=3){
        $list = M('kefu')->field('id,name,qq')->where('status=0')->limit($lg)->select();
        if(empty($list)){
            $this->ajaxReturn(array('status'=>0,'msg'=>'没有可用的客服'));
        }
        foreach ($list as $v){
            $v['uri']=U('Public/kefu?qq='.$v['qq']);
            $list1[]=$v;
        }
        $this->ajaxReturn(array('status'=>1,'msg'=>'查询成功','list'=>$list1));
    }
    protected function _error(){
        header("HTTP/1.0 404 Not Found");
        $this->assign('home',C('DOMAIN'));
        $this->display("Common:404");
    }
}