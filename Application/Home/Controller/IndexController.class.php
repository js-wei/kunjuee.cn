<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function _initialize(){
        parent::_initialize(); // TODO: Change the autogenerated stub
		$this->carousel = $this->get_site_carousel();
    }

    public function index() {
        $this->display();
    }
}