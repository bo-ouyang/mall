<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/21
 * Time: 14:06
 */

namespace app\admin_app\controller;
use app\common\model\GoodsOptionalTypeModel;
use cmf\controller\AdminBaseController;

class GTypeController extends AdminBaseController {
    public function index(){
        $data['option']= GoodsOptionalTypeModel::select();
        $this->assign('data',$data);
         return  $this->fetch('optional_type_list');}
    public function optional_type_add(){
	    return  $this->fetch('Goods/optional_type_add');}
    public function optional_type_edit(){
	    return $this->fetch('Goods/optional_type_edit');}
}
