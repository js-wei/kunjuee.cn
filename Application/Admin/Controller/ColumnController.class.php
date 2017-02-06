<?php
namespace Admin\Controller;
/**
 * 栏目操作
 * @author 魏巍
 */
class ColumnController extends BaseController {
    /**
     * 列表页
     */
	public function index(){
        $list = M('Column')->select();

        $this->list= $list =\Service\Category::unlimitForLevel($list);
       
		$this->display();
	}

    /**
     * 添加页
     */
	public function add(){
        $this->column_list = $list = \Service\Category::LimitForLevel(M('column')->select());
		$this->display();
	}

    /**
     * 删除页
     */
	public function del(){
        $this->display();
    }

    public function edit(){
        $this->column_list = $list = \Service\Category::LimitForLevel(M('column')->select());
        $this->column=$column=M('column')->find(I('id','',intval));
        $this->display();
    }

    /**
     * 修改操作
     */
    public function status(){
        if(!$this->_status(I('id'),'column',I('t'))){
            $this->error('操作失败');
        }
    }
    /**
     * 添加处理函数
     */
    public function addhandler(){
        $image = $this->UploadsImage();
        $data =!empty($image)?array_merge($image,$this->_param()):$this->_param();
        $data['title']=trim_all(I('title'));
        $data['effective']=strtotime(I('effective'));
        $data['date']=$data['dates']=time();
        $data['name']=trim_all($data['name']);
        
        ksort($data);
        if(!M('column')->add($data)){
            $this->error('操作失败');
        }
        $this->redirect('index');
    }

    /**
     * 修改处理函数
     */
    public function edithandler(){
        $this->lang=$lang=!empty($_COOKIE['think_language'])?strtolower(cookie('think_language')):'zh-cn';
        $map['lang']=$lang;
        $this-> column_list = $list = \Service\Category::LimitForLevel(M('column')->where($map)->select());
        $image = $this->UploadsImage();
        $data =!empty($image)?array_merge($image,$this->_param()):$this->_param();
        $data['title']=trim_all(I('title'));
        $data['effective']=strtotime(I('effective'));

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
        $data['date']=time();
		
        if(!M('column')->save($data)){
            $this->error('操作失败');
        }
        $this->redirect('index');
    }
}
