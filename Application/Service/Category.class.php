<?php
namespace Service;

class Category{
	/**
    * 返回一维栏目
    * @param $cate Array 栏目
    * @param $level  int 等级
    * @param $html string 分隔符
    * @param $margin int 空隙
    * @return Array 栏目
    */
	public static function unlimitForLevel($cate,$fid=0,$level=0,$html='|--',$margin=15){
		$arr=array();
		foreach ($cate as  $v) {
			if($v['fid']==$fid){
				$v['level']=$level+1;
				$v['html']=$html;
				$v['margin']=$level*$margin;
				$arr[]=$v;
				$arr=array_merge($arr,self::unlimitForLevel($cate,$v['id'],$level+1,$html,$margin));
			}
		}
		return $arr;
	}

	public static function limitForLevel($cate,$fid=0,$level=0,$html='--',$margin=15){
		$arr=array();
		foreach ($cate as  $v) {
			if($v['fid']==$fid){
				$v['level']=$level+1;
				$v['html']=str_repeat($html,$level);
				$arr[]=$v;
				$arr=array_merge($arr,self::unlimitForLevel($cate,$v['id'],$level+1,$html,$margin));
			}
		}
		return $arr;
	}
	/**
    * 返回多维栏目
    * @param $cate Array 栏目
    * @param $fid int 父亲id
    * @param $name string 分隔符
    * @return Array 栏目
    */
	public static function unlimitedForLevel($cate,$fid=0,$name='child'){
		$arr=array();
		foreach ($cate as  $v) {
			if($v['fid']==$fid){
				$v[$name]=self::unlimitedForLevel($cate,$v['id'],$name='child');
				$arr[]=$v;	
			}
		}
		return $arr;
	}

	public static function getparents($cate,$curent){
		$arr=array();
		if(is_array($curent)){
			foreach ($cate as $v) {
				if($v['id']==$curent['fid']){
					$arr[]=$v;
					$arr=array_merge($arr,self::getparents($cate,$v));
				}
			}
		}else{
			foreach ($cate as $v) {
				if($v['id']==$curent){
					$arr[]=$v;
					$arr=array_merge($arr,self::getparents($cate,$v));
				}
			}
		}
		$arr=array_reverse($arr);
		return $arr;
	}

	public static function get_location($cate,$curent){
		if(empty($curent)){
			return '';
		}
		$arr[0]=[
			'title'=>'首页',
			'name'=>'',
		];
		if(is_array($curent)){
			foreach ($cate as $k => $v) {
				if($v['id']==$curent['fid']){
					$arr[++$k]=$v;
					$arr=array_merge($arr,self::getparents($cate,$v));
				}
			}
		}else{
			foreach ($cate as $k => $v) {
				if($v['id']==$curent){
					$arr[++$k]=$v;
					$arr=array_merge($arr,self::getparents($cate,$v));
				}
			}
		}

		$html = '<ol class="breadcrumb location">';
		foreach ($arr as $v){
			$html.="<li><a href=\"".U('/'.$v['name'])."\">".$v['title']."</a></li>";
		}
		$html.='<li class="active">'.$curent['title'].'</li></ol>';
		return $html;
	}
	/**
	 * [getchildrenid 获取子栏目ID]
	 * @param  [type] $cate [栏目]
	 * @param  [type] $fid  [父栏目id]
	 * @return [type]       [结果]
	 */
	public static function getChildrenById($cate,$id){
		$arr=array();
		foreach ($cate as $v) {
			if($v['fid']==$id){
				$arr[]=$v['id'];
				$arr=array_merge($arr,self::getChildrenById($cate,$v['id']));
			}
		}
		
		return $arr;
	}

	public static function getchildrens($cate,$fid){
		$arr=array();
		foreach ($cate as $v) {
			if($v['fid']==$id){
				$arr[]=$v;
				$arr=array_merge($arr,self::getposition($v['id']));
			}
		}
		return $arr;
	}
}