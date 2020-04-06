<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/21
 * Time: 14:10
 */

namespace app\admin\Controller;


class OrderLog extends Common {
    public function index(){$this->display('Order/log_list');}
}
