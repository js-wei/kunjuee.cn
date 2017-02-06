<?php

/**
 * Created by PhpStorm.
 * User: dong
 * Date: 2016/5/4
 * Time: 15:18
 */
namespace Home\Model;
use Think\Model;

class ArticleModel extends Model{
    protected $_validate = array(
        array('title','require','标题不能为空！'), //默认情况下用正则进行验证
        array('content','require','标题不能为空！')
    );
    public function get_pre($id,$cid){
        $where['id']=array('gt',$id);
        $where['del_id']=array('eq',0);
        $where['column_id']=$cid;
        return M('Article')->field('id,title')->where($where)->limit(1)->order('id asc')->find();
    }
    public function get_next($id,$cid){
        $where['id']=array('lt',$id);
        $where['del_id']=array('eq',0);
        $where['column_id']=$cid;
        $next = M('Article')->field('id,title')->where($where)->limit(1)->order('id desc')->find();
        return $next;
    }
    public function get_colunm($cid){
        return M('column')->field('id,title,name')->find($cid);
    }
    public function set_hits($id=0){
        M('Article')->where(array('id'=>$id))->setInc('hits',1);
    }
}