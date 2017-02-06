<?php
/**
 * 上传文件
 * @access public
 * @version 1.0
 * @author WangXin<wx@ddny365.com>
 */
namespace Admin\Controller;
use Think\Controller;

class UploadifyController extends BaseController {
	/**
	 * 此处为解决Uploadify在火狐下出现http 302错误 重新设置SESSION
	 */
	public function _initialize(){
		$session_name = session_name();
		if (isset($_POST[$session_name])) {
			session_id($_POST[$session_name]);
			session_start();
		}
	}

	/**
	 * 上传图片
	 */
	public function uploadImages(){
		$upload = new \Think\Upload();
		//上传配置
		$upload -> maxSize		=	32922000; //设置上传文件类型
		$upload -> rootPath		=	'Uploads/';
		$upload -> savePath		=	'images/';
		$upload -> allowExts	=	explode(',', 'jpg,jpeg,png,gif'); //设置附件上传目录
		$upload->subName    	=   array('date','Ymd');
		$image  				=	getimagesize($_FILES['Filedata']['tmp_name']);
		$upload -> autoSub 		=	true;
		
		if(!$upload -> upload()) {// 上传错误提示错误信息
			$this -> error($upload->getError());
		}else{// 上传成功 获取上传文件信息!
			$info =  $upload->getImgInfo();

			//缩略图配置
			$thumb 				=	true;//是否缩略图
			$thumbPrefix		=	'm_,s_';// 缩略图前缀
			$thumbNum			=	'2';//缩略图数量
			$thumbMaxWidth		=	'200,100';// 缩略图最大宽度
			$thumbMaxHeight		=	'200,100';// 缩略图最大高度
			$thumbRemoveOrigin	=	false;// 是否移除原图
				
			//是否缩略图
			if($thumb){
				$thumbWidth			=	explode(',',$thumbMaxWidth);
				$thumbHeight		=	explode(',',$thumbMaxHeight);
				$thumbPrefix		=	explode(',',$thumbPrefix);
			
				// 生成缩略图
				$image = new \Think\Image();
				$image->open($info['savepath'].$info['savename']);
				for($i=0; $i<$thumbNum; $i++) {
					$image->thumb($thumbWidth[$i], $thumbHeight[$i])->save($info['savepath'].$thumbPrefix[$i].$info['savename']);
				}
				if($thumbRemoveOrigin) {
					// 生成缩略图之后删除原图
					unlink($info['savepath'].$info['savename']);
				}
			}
			
			exit($info['savepath'].$info['savename']);
		}
	}
	/**
	 * @todo 上传文件（源码 勿动）
	 */
	public function UploadFile(){
		$upload = new \Think\Upload();
		
		//上传配置
		$upload -> maxSize		=	32922000; //设置上传文件类型
		$upload -> rootPath		=	'Uploads/';
		$upload -> savePath		=	'files/';
		$upload -> allowExts	=	explode(',', 'xls,xlsx,et,doc,docx,ppt,pptx,pps,pdf'); //设置附件上传目录
		$upload->subName    	=   array('date','Ymd');
		$upload -> autoSub 		=	true;
		
		if(!$upload -> upload()) {// 上传错误提示错误信息
			$this->error($upload->getError());
		}else{// 上传成功 获取上传文件信息!
			$info =  $upload->getImgInfo();			
			exit($info['savepath'].$info['savename'].';'.$_FILES['Filedata']['name']);
		}
	}
	/**
	 * @todo 删除原图（源码 勿动）
	 * @param  img_url  要删除的图片(多个用 , 分割);
	 */
	public function DelImg($img_url){
		if($img_url == ''){
			//获取参数
			$img_url = I('post.img_url');
		}
		//分割图片为数组
		$img_array = explode(',',$img_url);
		
		//图片处理
		foreach($img_array as $k => $v){
			//分割参数获取中图小图url
			$m_array[$k] = $s_array[$k] = explode('/',$v);
			$count[$k] = count($m_array[$k])-1;
			$m_array[$k][$count[$k]] = 'm_'.$m_array[$k][$count[$k]];
			$s_array[$k][$count[$k]] = 's_'.$s_array[$k][$count[$k]];
			
			//合并url
			$m_img_url[$k] = implode('/',$m_array[$k]);
			$s_img_url[$k] = implode('/',$s_array[$k]);
			
			//删除小图
			if(file_exists($v)){
				if(!unlink($v)){
					$ok == '0';
					$img_err[] = $v;
				}
			}
			
			//删除中图
			if(file_exists($m_img_url[$k])){
				if(!unlink($m_img_url[$k])){
					$ok == '0';
					$img_err[] = $v;
				}
			}
			
			//删除原图
			if(file_exists($s_img_url[$k])){
				if(!unlink($s_img_url[$k])){
					$ok == '0';
					$img_err[] = $v;
				}
			}
		}
		
		if($ok != '0'){
			$this->ajaxReturn('',ZN_1002,'1');
		}else{
			$img_err = implode(',',$img_err);
			$this->ajaxReturn('',ZN_1003.'(图片'.$img_err.'删除失败)','0');
		}
	}
	/**
	 * @todo 用户头像
	 */
	public function HeadPic(){
		$image_info = getimagesize($_FILES['Filedata']['tmp_name']);
		
		$upload = new \Think\Upload();
	
		//上传配置
		$upload -> maxSize		=	32922000; //设置上传文件类型
		$upload -> rootPath		=	'Uploads/';
		$upload -> savePath		=	'HeadPic/';
		$upload -> allowExts	=	explode(',', 'jpg,jpeg,png,gif'); //设置附件上传目录
		$upload->subName    	=   array('date','Ymd');
		$image  				=	getimagesize($_FILES['Filedata']['tmp_name']);
		$upload -> autoSub 		=	true;
	
		if(!$upload -> upload()) {// 上传错误提示错误信息
			$this -> error($upload->getError());
		}else{// 上传成功 获取上传文件信息!
			$info =  $upload->getImgInfo();
	
			//缩略图配置
			$thumb 				=	false;//是否缩略图
			$thumbPrefix		=	'm_,s_';// 缩略图前缀
			$thumbNum			=	'1';//缩略图数量
			$thumbMaxWidth		=	'149';// 缩略图最大宽度
			$thumbMaxHeight		=	'149';// 缩略图最大高度
			$thumbRemoveOrigin	=	false;// 是否移除原图
	
			//是否缩略图
			if($thumb){
				$thumbWidth			=	explode(',',$thumbMaxWidth);
				$thumbHeight		=	explode(',',$thumbMaxHeight);
				$thumbPrefix		=	explode(',',$thumbPrefix);
					
				// 生成缩略图
				$image = new \Think\Image();
				$image->open($info['savepath'].$info['savename']);
				for($i=0; $i<$thumbNum; $i++) {
					$image->thumb($thumbWidth[$i], $thumbHeight[$i])->save($info['savepath'].$thumbPrefix[$i].$info['savename']);
				}
				if($thumbRemoveOrigin) {
					// 生成缩略图之后删除原图
					unlink($savepath.$savename);
				}
			}
			
			exit($info['savepath'].$info['savename'].','.$image_info[0].','.$image_info['1']);
		}
	}
	/**
	 * @todo 裁剪图片
	 */
	public function CropPic(){
		//接收参数
		$interim_img = I('post.interim_img');
		$pic_width = I('post.pic_width');
		$pic_x = I('post.pic_x');
		$pic_y = I('post.pic_y');
		$pic_w = I('post.pic_w');
		
		$crop_pic = explode('/',$interim_img);
		$pic_count = count($crop_pic) - 1;
		$crop_pic[$pic_count] = 'c_'.$crop_pic[$pic_count];
		$crop_pic = implode('/',$crop_pic);
		
		$pic_width = ceil($pic_width / 400 * $pic_w);
		$pic_x = ceil($pic_x / 400 * $pic_w);
		$pic_y = ceil($pic_y / 400 * $pic_w);
		
		//实例化
		$image = new \Think\Image();
		//裁剪图片
		$image->open($interim_img);
		$image->crop($pic_width,$pic_width,$pic_x,$pic_y)->save($crop_pic);
		$image->open($crop_pic);
		$image->thumb(149,149)->save($crop_pic);
		if(unlink($interim_img)){
			echo $crop_pic;
		}
	}
	/**
	 * @todo 生成400*400缩略图
	 
	public function ThumbPic(){
		//接收参数
		$crop_pic = I('post.crop_pic');
		$thumb_pic = I('post.interim_img');
		
		//实例化
		$image = new \Think\Image();
		//生成缩略图
		
		if(unlink($crop_pic)){
			echo $thumb_pic;
		}
	}*/
	/**
	 * @todo 上传图片
	 */
	public function uploadImg(){
		/* $img_count = I('post.img_count');
			if($img_count == '5'){
		exit($img_count);
		} */
	
		/* $image = getimagesize($_FILES['Filedata']['tmp_name']);
		 if($image[1] != $image[0]){
		exit('0');
		} */
	
		$upload = new \Think\Upload();
	
		//上传配置
		$upload -> maxSize		=	32922000; //设置上传文件类型
		$upload -> rootPath		=	'Uploads/';
		$upload -> savePath		=	'Images/';
		$upload -> allowExts	=	explode(',', 'jpg,jpeg,png,gif'); //设置附件上传目录
		$upload->subName    	=   array('date','Ymd');
		$image  				=	getimagesize($_FILES['Filedata']['tmp_name']);
		$upload -> autoSub 		=	true;
	
		if(!$upload -> upload()) {// 上传错误提示错误信息
			$this -> error($upload->getError());
		}else{// 上传成功 获取上传文件信息!
			$info =  $upload->getImgInfo();
	
			//缩略图配置
			$thumb 				=	true;//是否缩略图
			$thumbPrefix		=	'm_,s_';// 缩略图前缀
			$thumbNum			=	'1';//缩略图数量
			$thumbMaxWidth		=	'149';// 缩略图最大宽度
			$thumbMaxHeight		=	'149';// 缩略图最大高度
			$thumbRemoveOrigin	=	false;// 是否移除原图
			$fullPath = '/'.$info['savepath'].$info['savename'];
			//是否缩略图
			if($thumb){
				$thumbWidth			=	explode(',',$thumbMaxWidth);
				$thumbHeight		=	explode(',',$thumbMaxHeight);
				$thumbPrefix		=	explode(',',$thumbPrefix);
					
				// 生成缩略图
				$image = new \Think\Image();
				$image->open($info['savepath'].$info['savename']);
				for($i=0; $i<$thumbNum; $i++) {
					$image->thumb($thumbWidth[$i], $thumbHeight[$i])->save($info['savepath'].$thumbPrefix[$i].$info['savename']);
					//$image->water('./Uploads/water.png',\Think\Image::IMAGE_WATER_SOUTHEAST)->save($info['savepath'].$thumbPrefix[$i].$info['savename']);
				}
				if($thumbRemoveOrigin) {
					// 生成缩略图之后删除原图
					unlink('.'.$fullPath);
				}
			}

			exit($fullPath);
		}
	}

	public function KindEditorUploadImage(){
	
		$upload = new \Think\Upload();
		//上传配置
		$upload -> maxSize		=	32922000; //设置上传文件类型
		$upload -> rootPath		=	'Uploads/';
		$upload -> savePath		=	'kindeditor/images/';
		$upload -> allowExts	=	explode(',', 'jpg,jpeg,png,gif'); //设置附件上传目录
		//$upload -> subType		=	'date';//子目录方式
		$upload->subName    	=   array('date','Ymd');
		$image  				=	getimagesize($_FILES['Filedata']['tmp_name']);
		$upload -> autoSub 		=	true;
	
		if(!$upload -> upload()) {// 上传错误提示错误信息
			$this->ajaxReturn(array('error'=>0,'message'=>$upload->getError(),'url'=>''));
		}else{// 上传成功 获取上传文件信息!
			$info =  $upload->getImgInfo();
	
			//缩略图配置
			$thumb 				=	false;//是否缩略图
			$thumbPrefix		=	'm_';// 缩略图前缀
			$thumbNum			=	'1';//缩略图数量
			$thumbMaxWidth		=	'149';// 缩略图最大宽度
			$thumbMaxHeight		=	'149';// 缩略图最大高度
			$thumbRemoveOrigin	=	false;// 是否移除原图
	
			//是否缩略图
			if($thumb){
				$thumbWidth			=	explode(',',$thumbMaxWidth);
				$thumbHeight		=	explode(',',$thumbMaxHeight);
				$thumbPrefix		=	explode(',',$thumbPrefix);
					
				// 生成缩略图
				for($i=0; $i<$thumbNum; $i++) {
					$image->thumb($thumbWidth[$i], $thumbHeight[$i])->save($info['savepath'].$thumbPrefix[$i].$info['savename']);
					//$image->water('./Uploads/water.png',\Think\Image::IMAGE_WATER_SOUTHEAST)->save($info['savepath'].$thumbPrefix[$i].$info['savename']);
				}
				if($thumbRemoveOrigin) {
					// 生成缩略图之后删除原图
					unlink($savepath.$savename);
				}
			}
			$path = $info['savepath'].$info['savename'];
			$image1 = new \Think\Image();
			$image1->open($path);
			//$image1->water('./Uploads/water.png',\Think\Image::IMAGE_WATER_SOUTHEAST)->save($path);
			$this->ajaxReturn(array('error'=>0,'url'=>"/".$path));
			//exit($info['savepath'].$info['savename']);
		}
	}

	/**
	 * [delmg 删除文件]
	 * @param  [type] $src [description]
	 * @return [type]      [description]
	 */
	public function delmg($src){
		if(empty($src)){
			$this->ajaxReturn(array('status'=>0,'msg'=>'图片地址不能为空'));
		}
		if(strpos($src,'.')){
			$src = ".".$src;
		}
		$ii = explode('/', $src);
		$ii[4]="m_".$ii[4];
		$ii1 = implode('/', $ii);

		if(file_exists($src)){
			if(!unlink($src)){
				$this->ajaxReturn(array('status'=>0,'msg'=>'图片删除失败，请重试！'));
			}
		}
		if(file_exists($ii1)){
			if(!unlink($ii1)){
				$this->ajaxReturn(array('status'=>0,'msg'=>'缩略图删除失败，请重试！'));
			}
		}
		$this->ajaxReturn(array('status'=>1,'msg'=>'删除成功！'));
	}

    /**
     * 删除文件
     * @param $model
     * @param $where
     * @param $file
     */
    public function delmgByWhere($model,$where,$file){
        $m = $model->where($where)->find();
        $src = $m[$file];

        if(empty($src)){
            $this->ajaxReturn(array('status'=>0,'msg'=>'图片地址不能为空'));
        }
        if(strpos($src,'.')!==true){
            $src = ".".$src;
        }

        $ii = explode('/', $src);
        $ii[4]="m_".$ii[4];
        $ii1 = implode('/', $ii);

        if(file_exists($src)){
            if(!unlink($src) || !unlink($ii1)){
                $this->ajaxReturn(array('status'=>0,'msg'=>'图片删除失败，请重试！'));
            }
        }
        if(file_exists($ii1)){
            if(!unlink($ii1)){
                $this->ajaxReturn(array('status'=>0,'msg'=>'缩略图删除失败，请重试！'));
            }
        }
        return true;
    }
	/**
	 * 删除文件
	 * @param $model
	 * @param $where
	 * @param $file
	 */
	public function delmgByWhere1($model,$where,$file){
		$m = $model->where($where)->find();
		$src = $m[$file];

		$flag = true ;
		if(empty($src)){
			return $flag;
		}
		if(strpos($src,'.')!==true){
			$src = ".".$src;
		}

		$ii = explode('/', $src);
		$ii[4]="m_".$ii[4];
		$ii1 = implode('/', $ii);

		if(file_exists($src)){
			if(!unlink($src) || !unlink($ii1)){
				$flag = false;
			}
		}
		if(file_exists($ii1)){
			if(!unlink($ii1)){
				$flag = false;
			}
		}
		return $flag;
	}


    /**
     * 删文章除图片
     * @param $model
     * @param $where
     * @param $field
     * @return bool
     */
    public function delArticleImage($model,$where,$field){
        $a = $model->where($where)->find();
		$flag =true;
		if($a[$field]){
			return $a;
		}
        $images = get_images(htmlspecialchars_decode($a[$field]));

        foreach ($images[1]  as $k=>$v){
            $v =".".$v;
            if(!unlink($v)){
                $flag = false;
                break;
            }
        }
        return $flag;
    }

    /**
	 * [delmg 删除文件]
	 * @param  [type] $src [description]
	 * @return [type]      [description]
	 */
	public function delmg1($src){
		if(empty($src)){
			$this->ajaxReturn(array('status'=>0,'msg'=>'图片地址不能为空'));
		}
		if(strpos($src,'.')!==true){
			$src = ".".$src;
		}

		if(!unlink($src)){
			$this->ajaxReturn(array('status'=>0,'msg'=>'删除失败，请重试！'));
		}
		$this->ajaxReturn(array('status'=>1,'msg'=>'删除成功！'));
	}

	/**
	 * @todo 上传回复图片
	 */
	public function ReplyPic(){
		$img_count = I('post.img_count');
		if($img_count == '10'){
			exit($img_count);
		}
		
		$upload = new \Think\Upload();
	
		//上传配置
		$upload -> maxSize		=	32922000; //设置上传文件类型
		$upload -> rootPath		=	'Uploads/';
		$upload -> savePath		=	'ReplyPic/';
		$upload -> allowExts	=	explode(',', 'jpg,jpeg,png,gif'); //设置附件上传目录
		$upload->subType    	=   array('date','Ymd');
		$image  				=	getimagesize($_FILES['Filedata']['tmp_name']);
		$upload -> autoSub 		=	true;
	
		if(!$upload -> upload()) {// 上传错误提示错误信息
			$this -> error($upload->getError());
		}else{// 上传成功 获取上传文件信息!
			$info =  $upload->getImgInfo();
	
			//缩略图配置
			$thumb 				=	true;//是否缩略图
			$thumbPrefix		=	'm_,s_';// 缩略图前缀
			$thumbNum			=	'2';//缩略图数量
			$thumbMaxWidth		=	'200,100';// 缩略图最大宽度
			$thumbMaxHeight		=	'200,100';// 缩略图最大高度
			$thumbRemoveOrigin	=	false;// 是否移除原图
	
			//是否缩略图
			if($thumb){
				$thumbWidth			=	explode(',',$thumbMaxWidth);
				$thumbHeight		=	explode(',',$thumbMaxHeight);
				$thumbPrefix		=	explode(',',$thumbPrefix);
					
				// 生成缩略图
				$image = new \Think\Image();
				$image->open($info['savepath'].$info['savename']);
				for($i=0; $i<$thumbNum; $i++) {
					$image->thumb($thumbWidth[$i], $thumbHeight[$i])->save($info['savepath'].$thumbPrefix[$i].$info['savename']);
				}
				if($thumbRemoveOrigin) {
					// 生成缩略图之后删除原图
					unlink($savepath.$savename);
				}
			}
				
			exit($info['savepath'].$info['savename']);
		}
	}
}