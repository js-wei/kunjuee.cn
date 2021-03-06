<?php
/**
 * Created by PhpStorm.
 * User: 魏巍 jswei30@gmail.com
 * Date: 2016/10/11
 * Time: 16:57
 **/

namespace Home\Controller;
use Think\Controller;

class NewsController extends BaseController{
    public function _initialize(){
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this->current = session('now_nav_first');
    }
    /**
     * [index 显示内容]
     * @return [type] [description]
     */
    public function index(){
        $id=$this->current['name'];
        $id = M('column')->cache(true,60,'Xcache')->field('id,title')->where(array('name'=>$id))->find();
        $map['column_id']=$id['id'];
        $map['status']=0;

        switch ($this->current['type']){
            case 1:
            case 2:
                $order = 'sort asc,create_time desc';
                $list = $this->getlist(M('article'),$map,$order,'');
                $this->list = $list;
                break;
            case 3:
                $this->list  = $list =  M('article')->cache(true,60,'Xcache')->field('id,content')->where($map)->find();
                break;
            case 5:
                $form = "<form class=\"form-horizontal\" id=\"form1\" role=\"form\" action=\"".U('/add_forms')."\" method=\"post\" autocomplete=\"off\">\n\t";
                $list =  M('form')->cache(true,60,'Xcache')->field('dates',true)->where($map)->select();
                
                foreach ($list as $k => $v) {
                    if($v['type']==4 || $v['type']==5){
                         $list[$k]['items'] = explode(',',$v['items']);
                    }
                    $placeholder = !empty($v['tooltips'])?$v['tooltips']:$v['title'];

                    $form .="<div class=\"form-group\">\t\n\t\t<label class=\"col-sm-3 control-label\">{$v['title']}</label>";
                    if($v['type']==1){
                        $form .= "\t\n\t\t<div class=\"col-sm-9\">\t\n\t\t\t<input type=\"text\" name=\"{$v['name']}\" placeholder=\"{$placeholder}\" class=\"form-control\">\n\t\t</div>\n\t";
                    }else if($v['type']==2){
                        $form .= "\t\n\t\t<div class=\"col-sm-9\">\t\n\t\t\t<input type=\"number\" name=\"{$v['name']}\" placeholder=\"{$placeholder}\" class=\"form-control\">\n\t\t</div>\n\t";
                    }else if($v['type']==3){
                        $form .= "\t\n\t\t<div class=\"col-sm-9\">\t\n\t\t\t<input type=\"number\" name=\"{$v['name']}\" placeholder=\"{$placeholder}\" class=\"form-control\">\n\t\t</div>\n\t";
                    }else if($v['type']==4){
                        $_temp = explode(',',$v['items']);
                        $form .= "\n\t\t<div class=\"col-sm-9\">\t\t";
                        foreach ($_temp as $k1 => $v1) {
                            $form .="\n\t\t\t<label class=\"checkbox-inline\"><input type=\"radio\" value=\"{$k1}\" name=\"{$v['name']}\"";
                            if(!$k1){
                                $form .=" checked=\"checked\"";
                            }
                            $form .=">{$v1}</label>";
                        }
                        $form .= "\n\t\t</div>\t\n\t";
                    }else if($v['type']==5){
                        $_temp = explode(',',$v['items']);
                        $form .= "\n\t\t<div class=\"col-sm-9\">\t\t";
                        foreach ($_temp as $k1 => $v1) {
                            $form .="\n\t\t\t<label class=\"checkbox-inline\"><input type=\"checkbox\" value=\"{$v1}\" name=\"{$v['name']}[]\"";
                            $form .=">{$v1}</label>";
                        }
                        $form .= "\n\t\t</div>\t\n\t";
                    }else{
                        $form .= "<div class=\"col-sm-9\">\t\n\t\t\t<textarea  class=\"form-control\" name=\"{$v['name']}\" placeholder=\"\" rows=\"5\"></textarea>\n\t\t</div>\n\t";
                    }
                    $form .="</div>\n\t";
                }
                $form .= "<input type=\"hidden\" name=\"m\" value=\"".I('get.id')."\">";
                $form .= "\n\t<div class=\"form-group\">\n\t\t<div class=\"col-sm-offset-3 col-sm-9\">\n\t\t\t<button type=\"submit\" class=\"btn btn-info\">提交</button>\n\t\t</div>\n\t</div>\n</form>
<script type=\"text/javascript\">
$(function(){
    $(\"#form1\").validate({
        rules:{\n\t\t";
            foreach ($list as $k => $vo) {
                $t = ($k<count($list)-1)?"\t\t":'';
                $form .= ($vo['type']!=5)?"'{$vo['name']}':{\n\t\t":"'{$vo['name']}[]':{\n\t\t";
                if($vo['requird']==1){
                    $form .= "\trequired:true";
                }
                if($vo['connect']==1){
                    $form .= ",\n\t\t\tisPhone:true";
                }
                if($vo['email']==1){
                    $form .= ",\n\t\t\temail:true";
                }
                if($vo['url']==1){
                    $form .= ",\n\t\t\turl:true";
                }
                if($vo['type']==2){
                    $form .= ",\n\t\t\tnumber:true";
                }
                if($vo['type']==3){
                    $form .= ",\n\t\t\tdate:true";
                }
                $form .="\n\t\t},\n".$t;
            }
            $form .="\t},
        messages:{\n\t\t";
            foreach ($list as $k => $vo) {
                $t = ($k<count($list)-1)?"\t\t":'';
                $form .= ($vo['type']!=5)?"'{$vo['name']}':{\n\t\t":"'{$vo['name']}[]':{\n\t\t";
                if($vo['requird']==1){
                    if(!empty($vo['tooltips'])){
                        $form .= "\trequired:'请填写{$vo['tooltips']}'";
                    }else{
                        $form .= "\trequired:'请填写{$vo['title']}'";
                    }
                    
                }
                if($vo['connect']==1){
                    $form .= ",\n\t\t\tisPhone:'手机/固话号码不正确'";
                }
                if($vo['email']==1){
                   $form .= ",\n\t\t\temail:'电子邮箱格式不正确'";
                }
                if($vo['url']==1){
                   $form .= ",\n\t\t\turl:'网址格式不正确'";
                }
                if($vo['number']==1){
                   $form .= ",\n\t\t\tnumber:'请填写数字'";
                }
                if($vo['date']==1){
                  $form .= ",\n\t\t\tdate:'请填写正确的日期格式'";
                }
                $form .="\n\t\t},\n".$t;
            }
            $form .="\t},
        errorPlacement: function (error, element) { //指定错误信息位置
            if(element.is(':radio') || element.is(':checkbox')) { //如果是radio或checkbox
               var eid = element.attr('name'); //获取元素的name属性
               error.appendTo(element.parent().parent()); //将错误信息添加当前元素的父结点后面
            }else{
               error.insertAfter(element);
            }
        }
    });
    $('#form1').submit(function(e){
        e.preventDefault();
        if($('#form1').valid()){
            var index = layer.load(2, {
                shade: [0.4,'#000'] //0.1透明度的白色背景
            });
            $.post($('#form1').attr('action'),$('#form1').serialize(),function (data) {
                if(data.status==1){
                    layer.close(index);
                    layer.alert(data.msg,{icon:6,end:function () {
                        $('#form1')[0].reset();
                    }});
                }else {
                    layer.alert(data.msg,{icon:5});
                }
            });
        }
    })";
            $form .= "\n});\n</script>\n
            <link rel=\"stylesheet\" type=\"text/css\" href=\"{$this->site['url']}/Public/plug/jquery.validate/jquery.validate.css\">
            <script type=\"text/javascript\" src=\"{$this->site['url']}/Public/plug/layer/layer.js\"></script>
            <script type=\"text/javascript\" src=\"{$this->site['url']}/Public/plug/jquery.validate/jquery.metadata.js\"></script>
            <script type=\"text/javascript\" src=\"{$this->site['url']}/Public/plug/jquery.validate/jquery.validate.min.js\"></script>
            <script type=\"text/javascript\" src=\"{$this->site['url']}/Public/plug/jquery.validate/jQuery.validate.message_cn.js\"></script>
            <script type=\"text/javascript\" src=\"{$this->site['url']}/Public/plug/jquery.validate/my.rules.js\"></script> 
";

                $this->assign('submit_forms',$form);
                $this->list = $list;
                break;
        }
        $this->carousel = $this->get_site_carousel();
        $this->display($this->tpl);
    }
    /**
     * [detail 详细内容]
     * @param  integer $aid [description]
     * @return [type]       [description]
     */
    public function detail($aid=0){
        if($aid){
            $m = D('Article');
            $detail = $m->cache(true,60,'Xcache')->field('gid',true)->find($aid);
            $m->set_hits($detail['id']);
            $this->prev = $prev = $m->get_pre($detail['id'],$detail['column_id']);
            $this->next =$next = $m->get_next($detail['id'],$detail['column_id']);
            $c = $m->get_colunm($detail['column_id']);
            $detail['column'] = $c['title'];
            $this->article=$detail;
            $this->display('details');
        }
        
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
        $count = $model->cache(true,60,'Xcache')->where($map)->group($group)->count('*');
        $pagination = $pagination ? $pagination : C('PAGE_SIZE');

        $p = new \Think\Page($count, $pagination,array('url'=>'/'.$_GET['id']?$_GET['id']:'news'));
        $p->setConfig('header', '<a class="rows">当前 %NOW_PAGE%/%TOTAL_PAGE%页</a>');
        $p->setConfig('prev', '上一页');
        $p->setConfig('next','下一页');
        $p->setConfig('last', '最后一页');
        $p->setConfig('first','第一页');
        $p->setConfig('theme', '%UP_PAGE%%HEADER%%DOWN_PAGE%');
        $p->prevClass="pull-left";
        $p->nextClass="pull-right";
        $p->pageTheme="pagination";
        $p->allShow = 1;
        $show=$p->show1();
        $this->assign('page', $show);
        $res = $model->cache(true,60,'Xcache')->where($map)->field($field)->group($group)->limit($p->firstRow . ',' . $p->listRows)->order($order)->select();
        return $res;
    }
}