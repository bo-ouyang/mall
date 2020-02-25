<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/21
 * Time: 14:06
 */

namespace app\admin_app\Controller;


use app\common\model\BrandModel;
use cmf\controller\AdminBaseController;

class GoodsBrandController extends AdminBaseController {
    public function index(){
        $data['brand'] = BrandModel::field('brand_id,brand_name')->select();
        $this->assign('data',$data);
       return $this->display('goods/brand_list');
    }
    public function brand_add(){$this->display('goods/brand_add');}
    public function brand_edit(){$this->display('goods/brand_edit');}
    public function del(){$this->display('goods/del');}
}
