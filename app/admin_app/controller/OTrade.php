<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/21
 * Time: 14:09
 */

namespace app\admin\Controller;
class OTrade extends  Common {
    public function trade(){
    	return $this->display('Goods/shipping_list');
    }
}
