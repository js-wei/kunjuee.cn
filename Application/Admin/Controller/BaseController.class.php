<?php
namespace Admin\Controller;
use Think\Controller;
/**
 *基类 
 */
class BaseController extends Controller {
	protected function _initialize(){

		header('Content-type:text/html;charset=utf-8;');
		set_time_limit(0);

		$this->check_priv();        //判断是否登录

		$nav = $this->auth_list();	//导航
		$this->assign('nav', $nav);
		$site= M('Config')->order('id desc')->find();
		$this->site =$site;

		$this->singel=$singel=$this->getsingle();
		$this->nav_column=$nav_column=$this->get_column();

        //控制器
        $this->rd = $rd =__ROOT__.'/'.MODULE_NAME ;
        $this->control=$control=strtolower(CONTROLLER_NAME);
        $this->action=$action=strtolower(ACTION_NAME);
        //系统信息
        $this->os= $os = $this->_sys();
        //
        $this->articleList = $articleList =  $this->articleList();
		
	}

    /**
     * [articleList 获取文档栏目]
     * @return [type] [description]
     */
    private function articleList(){
        $map['status']=0;
        $map['type']=array('not in','6');
        $list = M('Column')->field('dates',true)->where($map)->select();
        $list =  \Service\Category::unlimitedForLevel($list);
        return $list;
    }
    
    /**
     * [empty description]
     * @return [type] [description]
     */
    public function _empty(){
        $this->error("未完成操作的'".CONTROLLER_NAME.'/'.ACTION_NAME."'".'持续开发中。。。','',5);
    }
    /**
     * [get_version 获取数据库版本]
     * @return [type] [description]
     */
    protected function get_version(){
        $Model = new \Think\Model();
        $version = $Model->query("select version() as ver");
        return $version[0]['ver'];
    }
    /**
     * [_mysql_db_size mysql数据库大小]
     * @return [type] [description]
     */
    protected function get_mysql_db_size(){        
        $Model = new \Think\Model();
        $sql = "SHOW TABLE STATUS FROM ".C('DB_NAME');
        $tblPrefix = C('DB_PREFIX');
        if($tblPrefix != null) {
            $sql .= " LIKE '{$tblPrefix}%'";
        }

        $row = $Model->query($sql);

        $size = 0;
        foreach($row as $value) {
            $size += $value["data_length"] + $value["index_length"];
        }

        return round(($size/1048576),2).'M';
    }
    /**
     * [redirect 重构redirect]
     * @param  string $url [description]
     * @return [type]      [description]
     */
    protected function _redirect($url=''){
        $uri = !empty($url)?$url:ACTION_NAME.'?'.$_SERVER['QUERY_STRING'];
        $url = 'http://'.$_SERVER['HTTP_HOST']. __ROOT__.'/'.MODULE_NAME.'/'.CONTROLLER_NAME.'/'.$uri;
        header('Location:'.$url);
    }

    /**
     * 获取当前操作模块
     * @param $control          控制器
     * @param $action           方法
     * @param string $lang      语言
     */
    protected function _model_name($control,$action,$lang=''){
        $controls=M('model')->where('title="'.$control.'"')->find();
        $this->model=$model=M('model')->where('fid='.$controls["id"].' and title="'.$action.'"')->find();
    }
    /**
     * @param $control 控制器
     * @param $action  操作
     */
    protected function _breadcrumb($control,$action){
        if($control!='single'){
            $map['title']=$control;
            $c =  M('model')->where($map)->find();
            $a =  M('model')->where('fid='.$c['id'].' and title="'.$action.'"')->find();
            $rd =__ROOT__.'/'.MODULE_NAME.'/'.$control;
            $uri="<a href=\"$rd".'/'.$a['title']."\">".$a['name']."</a>";
        }
        return $uri;
    }

    /**
     * 重置上传参数
     * @param $image    上传的图片
     * @param $data     上传的数据
     * @return mixed
     */
    protected function _data($image,$data){
        //获取旧图片参数
        foreach($data as $j=>$i){
            if(strpos($j,'old_')!==false){
                $old[str_replace('old_','',$j)]=$i;
                unset($data[$j]);
            }
        }
        //替换图片
        if(!empty($image)){     //有上传图片
            foreach($image as $k=>$v){
                $data[$k]=$v;
                if($image[$k]){ //删除旧图片
                    unlink(C('DEFAULT_UPLOAD_CONFIG.IMAGES').I('old_'.$k));
                }
            }
        }else{  //无上传图片,使用旧图片
            foreach($old as $o=>$q){
                $data[$o]=$q;
            }
        }
        return $data;
    }
    /**
     * @return array 获取当前系统信息
     */
    protected function _sys(){
        date_default_timezone_set("Etc/GMT-8");
        if (function_exists('gd_info')){
            $gdInfo = gd_info();
            $gd_support = true;
            $gdv_version = $gdInfo['GD Version'];
        }  else {
            $gd_support = false;
            $gdv_version  = '';
        }
        $sys=array(
                'os'=>PHP_OS,
                'os_all'=>php_uname('s'),
                //'server1'=>apache_get_version(),
                'server'=>php_sapi_name(),
                'think_ver'=>THINK_VERSION,
                'php'=> PHP_VERSION,
                'php_dir'=> DEFAULT_INCLUDE_PATH,
                'safe_mode'=>ini_get('safe_mode')?0:1,
                'gd'=>$gd_support,
                'gd_ver'=>$gdv_version,
                'mysql'=>$this->get_version(),
                'mysql_size'=>$this->get_mysql_db_size(),
                'file_size'=>ini_get("file_uploads") ? ini_get("upload_max_filesize") : "Disabled",
                'host'=>$_SERVER['SERVER_NAME'],
                'system_time' => date("Y-m-d",time()).'&nbsp;&nbsp;<span id="item-time">'.date('H:i:s',time()).'</span>',
                //'cpu_num'=>$_SERVER['PROCESSOR_IDENTIFIER'],
                'server'=>$_SERVER['SERVER_SOFTWARE'],
                //'user_group'=>$_SERVER['USERDOMAIN'],
                'server_lang'=>$_SERVER['HTTP_ACCEPT_LANGUAGE'],
                'server_point'=>$_SERVER['SERVER_PORT'],
                // 脚本运行占用最大内存
                'memory_limit' => get_cfg_var("memory_limit") ? get_cfg_var("memory_limit") : '-',
        );
        return $sys;
    }



    /**
	 * [get_column 得到导航栏目栏目]
	 * @return [type]
	 */
	protected function get_column(){
		$where=array('status'=>0,'type'=>array('lt',3));
		$column=M("column")->where($where)->order('id asc')->select();
		$column= \Service\Category::unlimitedForLevel($column);
		return $column;
	}
	/**
     * 获取排序
     * @param type $field排序的字段名（支持数组）
     * @param type $range排序方法
     * @return string
    */
    protected function ordermap($field = '', $range = '') {
        if ($field && $range) {
            if (is_array($field)) {
                for ($i = 0; $i < count($field); $i++) {
                    $arr[] = $field[$i] . " " . $range;
                }
                $ordermap = implode(',', $arr);
            } else {
                $ordermap = $field . " " . $range;
            }
        } else {
            $ordermap = null;
        }
        return $ordermap;
    }

    /**
     * @return mixed 得到单页面
     */
    protected function getsingle(){
        return M('column')->where('type=3')->select();
    }

    /**
     * 是否登录
     */
	protected function check_priv() {
		if(!isset($_SESSION[C('SESSION_PREFIX')]['login']) || empty($_SESSION[C('SESSION_PREFIX')]['login'])){
			$this->redirect('Login/index');
		}else{
            if($_SESSION[C('SESSION_PREFIX')]){
                $user = $_SESSION[C('SESSION_PREFIX')]['login'];
                $user['username']=ucfirst($user['username']);
                $this->user =$user;
            }
        }
	}

    /**
     * @return array
     */
	protected function auth_list(){
        $nav=array();
		$model = M('model')->where('fid=0 and "show"=0 and status=0')->select();

		foreach ($model as  $v) {
			$map['fid']=$v['id'];
			$map['status']=0;
            $map['show']=0;
			$child=M('model')->where($map)->order('sort asc')->select();
			$v['child']=$child;
			$nav[]=$v;
		}

		return $nav;
	}

    /**
     * @param $model 更改状态
     */
    protected function _status($id,$model='',$t='',$img='image'){
        $m=!empty($model)?M($model):M(CONTROLLER_NAME);
        $id = $id?$id:I('k');
        $where['id']=array('in',$id);
        $p=!empty($_GET['p'])?'?p='.I('p'):'';
        $ajax=I('ajax','',intval);
        $ajax=($ajax==1)?1:0;

        switch($t){
            case 'enable':            //启用
                $result = $m->where($where)->save(array('status'=>0));
                if(!$result){
                    if($ajax){
                        $this->ajaxReturn(array(
                            'status'=>1,
                            'message'=>'操作成功'
                        ));
                    }else{
                        $this->error('操作失败');
                    }
                }else{
                   if($ajax){
                       $this->ajaxReturn(array(
                           'status'=>0,
                           'message'=>'操作成功'
                       ));
                   }else{
                       $url = './index?p='.$p.'&id='.I('get.cid');
                       header('location:'.$url);
                   }
                }
                break;
            case 'forbidden':        //禁用
                $result = $m->where($where)->save(array('status'=>1));
                if(!$result){
                   if($ajax){
                       $this->ajaxReturn(array(
                           'status'=>1,
                           'message'=>'操作成功'
                       ));
                   }else{
                       $this->error('操作失败');
                   }
                }else{
                   if($ajax){
                       $this->ajaxReturn(array(
                           'status'=>0,
                           'message'=>'操作成功'
                       ));
                   }else{
                       $url = './index?p='.$p.'&id='.I('get.cid');
                       header('location:'.$url);
                   }
                }
                break;
            case 'delete':           //删除
                $upload = new UploadifyController();
                $flag =true;
                if(!empty($img)){
                    $flag = $upload->delmgByWhere1($m,$where,$img) && $upload->delArticleImage($m,$where,'content');
                }
                if($flag){
                    $result = $m->where($where)->delete();
                    if(!$result){
                        if($ajax){
                            $this->ajaxReturn(array(
                                'status'=>0,
                                'msg'=>'操作失败'
                            ));
                        }else{
                            $this->error('操作失败');
                        }
                    }else{
                        if($ajax){
                            $this->ajaxReturn(array(
                                'status'=>1,
                                'msg'=>'操作成功'
                            ));
                        }else{
                            $url = './index?p='.$p.'&id='.I('get.cid');
                            header('location:'.$url);
                        }
                    }
                }
                break;
        }
    }

    /**
     * @return array
     */
	protected function _search() {
        //处理基本查询
        $map = array();
        //控制器名称
        ($title = I('get.title','', 'trim')) && $map['title'] = array('like', '%' . $title . '%');
        //控制器中文名
        ($name = I('get.name ','','trim')) && $map['name'] = array('like', '%' . $name . '%');
        //
        ($title = I('get.k','', 'trim')) && $map['title'] = array('like', '%' . $title . '%');
        ($name = I('get.k', '','trim')) && $map['name'] = array('like', '%' . $title . '%');
        //状态（正常，禁用）
        if ($_GET['status'] == null) {
            $status = -1;
        } else {
            $status = intval($_GET['status']);
        }
        $status >= 0 && $map['status'] = array('eq', $status);
       

        //输出
        $this->assign('search', array(
            'title' => $title,
            'name' => $name,
            'k' => I('k'),
            'status' => $status,
        ));
        return $map;
    }
    /**
	 * [uploadUEditor 上传图片]
	 * @return [type]
	 */
	protected function UploadsImage(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize      = C('DEFAULT_UPLOAD_CONFIG.IMAGE_SIZE'); ;// 设置附件上传大小
        $upload->exts          = array('jpg', 'gif', 'png', 'jpeg','ico');// 设置附件上传类型
        $upload->savePath     =  C('DEFAULT_UPLOAD_CONFIG.IMAGES');
        $upload->autoSub      = true;
        $upload->subName      = array('date','Ymd');

    	if( $info  =  $upload->upload()){
            foreach ($info as $k => $v) {
                $image[$v['key']]=$v['savepath'].$v['savename'];
            }
            return $image;
        }
     }

     /**
     * [uploadUEditor 上传图片]
     * @return [type]
     */
    protected function uploadsEditor(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize  = 3145728 ;// 设置附件上传大小
        $upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->autoSub=true;
        $upload->subType ='date';
        $upload->subType ='date' ;
        $upload->dateFormat ='ymd' ;
        $upload->savePath =  './Uploads/ueditor/';// 设置附件上传目录
      
        if($upload->upload()){
            $info =  $upload->getUploadFileInfo();
            foreach ($info as $k => $v) {
                
                if($v['key']=='image'){
                    $image1.=$v['savename'].',';
                }else{
                    $image[$v['key']]=$v['savename'];
                }
            }

            $d=array('image'=>substr($image1,0,-1));
            
            if(is_array($image)){
                $data=array_merge($image,$d);
            }else{
                $data=$d;
            }
            return $data;
        }
     }


    /**
	 * [uploadUEditor 上传文件]
	 * @return [type]
	 */
	protected function UploadsFile(){
       	import('ORG.Net.UploadFile');
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize  = 52428800 ;// 设置附件上传大小50M
		$upload->allowExts  = array('zip', 'rar', 'pdf', 'xls','doc','docx','exe');// 设置附件上传类型
        $upload->subType ='date' ;
        $upload->dateFormat ='ymd' ;
        $upload->savePath =  './Uploads/file/';// 设置附件上传目录
        if($upload->upload()){
            $info =  $upload->getUploadFileInfo();
            $file[$info[0]['key']]=$info[0]['savename'];
            return $file;
        }else{
            return array('state'=>$upload->getError());
        }
    }
    /**
     * [makeAttr 重置文章属性]
     * @param  [array] $resetAttr 重置的属性
     * @return [array] 返回重置的属性	
     */
    protected function makeAttr($resetAttr){
    	$attr=array('com'=>0,'new'=>0,'head'=>0,'top'=>0,'img'=>0,'hot'=>0);
    	foreach ($resetAttr as $k => $v) {
    		$attr[$k]=1;
    	}
    	return $attr;
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
    protected function getlist($model = '', $map = '', $order = '', $pagination = '', $field = '*') {
       
        $model=!empty($model)?$model:M(CONTROLLER_NAME);

        $count = $model->where($map)->count('*');
        $pagination = $pagination ? $pagination : C('PAGE_SIZE');

        $p = new \Think\Page($count, $pagination);
        $p->setConfig('header', '<li class="">&nbsp;共有<b>%TOTAL_ROW%</b>条&nbsp;当前第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页</li>');
        $p->setConfig('prev', '上一页');
        $p->setConfig('next', '下一页');
        $p->setConfig('last', '最后一页');
        $p->setConfig('first','第一页');
        $p->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');
        
        $show=$p->show();
        $this->assign('page', str_replace('<div>','',$show));
        $res = $model->where($map)->field($field)->limit($p->firstRow . ',' . $p->listRows)->order($order)->select();

        return $res;
    }
    /**
    * [delArticleImage 删文章除图片]
    * @param  [string] $path 图片路径
    * @return [string] 删除结果
    */
    protected function delArticleImage($path){
    	$path=!empty($path)?$path:I('path');
    	if(!empty($path)){
    		$id=I('id','',intval);
            $index=I('index','',intval);
            $result=M('Article')->find($id);

            $image=array_filter(explode(',', $result['image']));	
            unset($image[$index]); //截取数组，去除空数组
            $image=implode(',', $image);
         
    		$data=array('id'=>$id,'image'=>$image);
    		M('Article')->save($data);

    		if(!unlink(C('DEFAULT_UPLOAD_CONFIG.IMAGES').$path)){
    			$this->ajaxReturn(
                    array(
                        'status'=>0,
                        'msg'=>L('delete').L('fail')
                    )
                );
    		}else{
                $this->ajaxReturn(
                    array(
                        'status'=>1,
                        'msg'=>L('delete').L('success')
                    )
                );
    		}
    	}
    }
    /**
     * [delFile 删除文件]
     * @return [int] [返回结果]
     */
    public function delFile($id=0){
        $id=$id?$id:I('id','',intval);
        $file=!empty($_POST['file'])?$_POST['file']:'';
        if(emnpty($file)){
            $f =M('file')->find($id);
            $file=$f['path'];
        }
        if(!unlink(C('DEFAULT_UPLOAD_CONFIG.FILES').$file)){
            $this->ajaxReturn(array(
                'status'=>0,
                'msg'=>L('delete').L('fail')
            ));
        }else{
            $data=array('id'=>$id,'file'=>'');
            M('file')->save($data);
            $this->ajaxReturn(array(
                'status'=>1,
                'msg'=>L('delete').L('success')
            ));
        }
    }
  	/**
  	* [_setDel 定时删除]
    * @param integer $time [间隔]
    * @param string  $model [模型]
    * @param string  $type [跨度]
  	*/
    protected function _setDel($time=1,$model='',$type='day'){
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
        
        $name=!empty($model)?$model:CONTROLLER_NAME;
        $model=M($name);
        $where['date']=array('lt',$after);
        $result=$model->where($where)->delete(); 
        return $result;
    }
    /**
    * [_param 获取参数信息]
    * @param  string $param [参数]
    * @return [type]        [description]
    */
    protected function _param($param=''){
		$param = $param?$param:$_REQUEST;
        if(!empty($param)){
            foreach ($_REQUEST as $k => $v) {
                if($k!='_URL_'){
                    $param[$k]=htmlspecialchars($v);
                }
            }
        }
        return $param;
    }

    /**
     * 查询快递
     */
    protected function get_express($name='',$no=''){
        $express = new \Service\Express();
        $express->express($name,$no);
    }

    /**
     * [_constant 输出系统常量]
     * @param  integer $type [输出类型：1所有、2系统、3路径、4请求]
     * @return [type]        [description]
     */
    protected function _constant($type=1){
    	$url=array(
    		'URL_TYPE'=>array(
    			array(
	    		'URL_COMMON'=>URL_COMMON,
	    		'information'=>'普通模式(0)'
	    		),
	    		array(
	    		'URL_PATHINFO'=>URL_PATHINFO,
	    		'information'=>'PATHINFO(1)'
	    		),
	    		array(
	    		'URL_REWRITE'=>URL_REWRITE,
	    		'information'=>'REWRITE(2)'
	    		),
	    		array(
	    		'URL_COMPAT'=>URL_COMPAT,
	    		'information'=>'兼容模式(3)'
	    		),
    		)
    	);
    	$path=array(
    		'PATH_TYPE'=>array(
    			array(
	    		'THINK_PATH'=>THINK_PATH,
	    		'information'=>'框架系统目录'
	    		),
	    		array(
	    		'APP_PATH'=>APP_PATH,
	    		'information'=>'应用目录（默认为入口文件所在目录)'
	    		),
	    		array(
	    		'LIB_PATH'=>LIB_PATH,
	    		'information'=>"系统核心类库目录（默认为 THINK_PATH.'Think/'）"
	    		),
	    		array(
	    		'MODE_PATH'=>LIB_PATH,
	    		'information'=>"系统应用模式目录（默认为 THINK_PATH.'Mode/'）"
	    		),
	    		array(
	    		'BEHAVIOR_PATH'=>LIB_PATH,
	    		'information'=>"行为目录（默认为 THINK_PATH.'Behavior/'）"
	    		),
	    		array(
	    		'COMMON_PATH'=>LIB_PATH,
	    		'information'=>"公共模块目录（默认为 THINK_PATH.'Common/'）"
	    		),
	    		array(
	    		'VENDOR_PATH'=>LIB_PATH,
	    		'information'=>"第三方类库目录（默认为 THINK_PATH.'Vendor/'）"
	    		),
	    		array(
	    		'RUNTIME_PATH'=>RUNTIME_PATH,
	    		'information'=>"应用运行时目录（默认为 THINK_PATH.'Runtime/'）"
	    		),
	    		array(
	    		'HTML_PATH'=>HTML_PATH,
	    		'information'=>"应用静态缓存目录（默认为 THINK_PATH.'Html/'）"
	    		),
	    		array(
	    		'CONF_PATH'=>CONF_PATH,
	    		'information'=>"应用公共配置目录（默认为 COMMON_PATH.'Conf/'）"
	    		),
	    		array(
	    		'LANG_PATH'=>LANG_PATH,
	    		'information'=>"公共语言包目录（默认为 COMMON_PATH.'Lang/'）"
	    		),
	    		array(
	    		'LOG_PATH'=>LOG_PATH,
	    		'information'=>"应用日志目录（默认为 RUNTIME_PATH.'Logs/'）"
	    		),
	    		array(
	    		'CACHE_PATH'=>CACHE_PATH,
	    		'information'=>"项目模板缓存目录（默认为 RUNTIME_PATH.'Cache/'）"
	    		),
	    		array(
	    		'TEMP_PATH'=>TEMP_PATH,
	    		'information'=>"应用缓存目录（默认为 RUNTIME_PATH.'Temp/'）"
	    		),
	    		array(
	    		'DATA_PATH'=>DATA_PATH,
	    		'information'=>"应用缓存目录（默认为 RUNTIME_PATH.'Data/'）"
	    		),
    		)
    	);
		
		$system=array(
			'system'=>array(
				array(
				'IS_CGI'=>IS_CGI,
	    		'information'=>"是否属于 CGI模式"
				),
				array(
				'IS_WIN'=>IS_WIN,
	    		'information'=>"是否属于Windows 环境"
				),
				array(
				'IS_CLI'=>IS_CLI,
	    		'information'=>"是否属于命令行模式"
				),
				array(
				'__ROOT__'=>__ROOT__,
	    		'information'=>"网站根目录地址"
				),
				array(
				'__APP__'=>__APP__,
	    		'information'=>"当前应用（入口文件）地址"
				),
				array(
				'__MODULE__'=>__MODULE__,
	    		'information'=>"当前模块的URL地址"
				),
				array(
				'__CONTROLLER__'=>__CONTROLLER__,
	    		'information'=>"当前控制器的URL地址"
				),
				array(
				'__ACTION__'=>__ACTION__,
	    		'information'=>"当前操作的URL地址"
				),
				array(
				'__SELF__'=>__SELF__,
	    		'information'=>"当前URL地址"
				),
				array(
				'__INFO__'=>__INFO__,
	    		'information'=>"当前的PATH_INFO字符串"
				),
				array(
				'__EXT__'=>__EXT__,
	    		'information'=>"当前URL地址的扩展名"
				),
				array(
				'MODULE_PATH'=>MODULE_PATH,
	    		'information'=>"当前模块路径"
				),
				array(
				'CONTROLLER_NAME'=>CONTROLLER_NAME,
	    		'information'=>"当前控制器名"
				),
				array(
				'ACTION_NAME'=>ACTION_NAME,
	    		'information'=>"当前操作名"
				),
				array(
				'APP_DEBUG'=>APP_DEBUG,
	    		'information'=>"是否开启调试模式"
				),
				array(
				'APP_MODE'=>APP_MODE,
	    		'information'=>"当前应用模式名称"
				),
				array(
				'APP_STATUS'=>APP_STATUS,
	    		'information'=>"当前应用状态"
				),
				array(
				'STORAGE_TYPE'=>STORAGE_TYPE,
	    		'information'=>"当前存储类型"
				),
				array(
				'MODULE_PATHINFO_DEPR'=>MODULE_PATHINFO_DEPR,
	    		'information'=>"模块的PATHINFO分割符"
				),
				array(
				'MEMORY_LIMIT_ON'=>MEMORY_LIMIT_ON,
	    		'information'=>"系统内存统计支持"
				),
				array(
				'RUNTIME_FILE'=>RUNTIME_FILE,
	    		'information'=>"项目编译缓存文件名"
				),
				array(
				'THEME_NAME'=>THEME_NAME,
	    		'information'=>"当前主题名称"
				),
				array(
				'THEME_PATH'=>THEME_PATH,
	    		'information'=>"当前模板主题路径"
				),
				array(
				'LANG_SET'=>LANG_SET,
	    		'information'=>"当前浏览器语言"
				),
				array(
				'MAGIC_QUOTES_GPC'=>MAGIC_QUOTES_GPC,
	    		'information'=>"MAGIC_QUOTES_GPC"
				),
				array(
				'NOW_TIME'=>NOW_TIME,
	    		'information'=>"当前请求时间（时间戳）"
				),
				array(
				'REQUEST_METHOD'=>REQUEST_METHOD,
	    		'information'=>"当前请求类型"
				),
				array(
				'IS_GET'=>IS_GET,
	    		'information'=>"当前是否GET请求"
				),
				array(
				'REQUEST_METHOD'=>REQUEST_METHOD,
	    		'information'=>"当前请求类型"
				),
				array(
				'IS_POST'=>IS_POST,
	    		'information'=>"当前是否POST请求"
				),
				array(
				'IS_PUT'=>IS_PUT,
	    		'information'=>"当前是否PUT请求"
				),
				array(
				'IS_DELETE'=>IS_DELETE,
	    		'information'=>"当前是否DELETE请求"
				),
				array(
				'IS_AJAX'=>IS_AJAX,
	    		'information'=>"当前是否AJAX请求"
				),
				array(
				'BIND_MODULE'=>BIND_MODULE,
	    		'information'=>"当前绑定的模块（3.2.1新增）"
				),
				array(
				'BIND_CONTROLLER'=>BIND_CONTROLLER,
	    		'information'=>"当前绑定的控制器（3.2.1新增）"
				),
				array(
				'BIND_ACTION'=>BIND_ACTION,
	    		'information'=>"当前绑定的操作（3.2.1新增）"
				),
				array(
				'CONF_EXT'=>CONF_EXT,
	    		'information'=>"配置文件后缀（3.2.2新增）"
				),
				array(
				'CONF_PARSE'=>CONF_PARSE,
	    		'information'=>"配置文件解析方法（3.2.2新增，暂无实现）"
				),
				array(
				'TMPL_PATH'=>TMPL_PATH,
	    		'information'=>"用于改变全局视图目录（3.2.3新增，暂无实现）"
				),
			)
			);
		$constant=array_merge($system,$path,$url);
		switch ($type) {
			case '1':
				return $constant;
				break;
			case '2':
				return $system;
				break;
			case '3':
				return $path;
				 break;
			case '4':
				return $url;	
				break;
			default:
				return $constant;
				break;
		}
    }

}