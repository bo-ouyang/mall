<?php
namespace app\admin\Controller;
use cmf\controller\AdminBaseController;

class IndexController extends AdminBaseController {
    public function index() {
       return $this->fetch();
    }
    public function dashboard(){
       return  $this->fetch();
    }
    public function login(){
	    return  $this->fetch();
    }
}
