<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/21
 * Time: 12:46
 */

namespace app\admin_app\Controller;


use cmf\controller\AdminBaseController;

class OrderController extends AdminBaseController {
        public function order_list(){
            echo 111;
        	return $this->fetch();
        }
        public function order_detial(){
	        return $this->display('Order/order_detial');
        }
}
