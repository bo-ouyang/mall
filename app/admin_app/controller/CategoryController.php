<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/21
 * Time: 14:05
 */

namespace app\admin_app\Controller;

use app\common\model\BrandModel;
use app\common\model\GoodsCateModel;
use app\common\model\GoodsCateAttrModel;
use app\common\model\GoodsCateBrandModel;
use cmf\controller\AdminBaseController;
use think\Db;
use think\facade\Request;

class CategoryController extends AdminBaseController {
    //商品分类
        public function index(){
        $data['cates'] = getTree(GoodsCateModel::all());
        $this->assign('data',$data);
	    return $this->fetch('goods/cate_list');
    }
    public function cate_add(){

        $data['cates'] = getTree(Db::name('goods_cate')->select());
        $data['brand'] =BrandModel::field('brand_id,brand_name')->select();
        $this->assign('data', $data);
        return  $this->fetch('goods/cate_add');
    }
    public function categoryAddPost(){
	    if(Request::isPost()) {
		    $data = Request::post();
		    $brands = input('brands');
		    unset($_POST['brands']);
		    GoodsCateModel::init();
		    $ret = GoodsCateModel::insert($data);
		    dump($ret);
		    if($ret){
		        if(!empty($brands)){
                    foreach ($brands as $v){
                        GoodsCateBrandModel::insert(array('cate_id'=>$ret,'brand_id'=>$v));
                    }
                }

			    $this->success('分类添加成功',url('category/index'));
		    }else{
			    $this->error('分类添加失败');
		    }
	    }
    }
    public function cate_edit(){

        $cate_id = input('id');

        $data['cates'] = getTree(GoodsCateModel::field('cate_id,cate_name,parent_id')->select());
        $data['brand'] = BrandModel::field('brand_id,brand_name')->where(array('cate_id'=>$cate_id))->select();
        $data['cate']  = GoodsCateModel::find($cate_id);
        $this->assign('data', $data);
       return $this->fetch('goods/cate_edit');
    }
    //分类属性
    public function cate_attr(){
        $cate_id   =input('cate_id');
        $data['cate_name']=GoodsCateModel::where(['cate_id'=>$cate_id])->value('cate_name');
        $data['list'] = GoodsCateAttrModel::where(['cate_id'=>$cate_id])->select();
        $data['cate_id'] = $cate_id;
        $this->assign('data',$data);
       return $this->fetch('goods/cate_attr');
    }
    //分类属性添加
    public function cateAttrAdd(){
        $cate_id = input('cate_id');
        $this->assign('id',$cate_id);
        $this->fetch('goods/cate_attr_add');
    }
    public function cateAttrAddPost(){
	    if($this->request->isPost()) {
		    $post = request()->post();
		    $ret = GoodsCateAttrModel::create($post);
		    if(!$ret->attr_id){
				$this->error('分类属性修改失败');
		    }
		    $this->redirect('GCate/cate_attr',array('cate_id'=>$ret->cate_id));
	    }
    }
    //分类属性编辑
    public function cate_attr_edit(){
        $id = input('id');
        $info = GoodsCateAttrModel::find($id);
        $data['attr'] = $info;
        $this->assign('data',$data);
        $this->fetch('goods/cate_attr_edit');
    }
    public function cateAttrEditPost(){
	    if(Request::isPost()){
	    	$post = Request::post();
		    $attr_id = input('attr_id');
		    $cate_id = input('cate_id');
		    $ret = GoodsCateAttrModel::where(['attr_id'=>$attr_id])->update($post);
		    if($ret->attr_id){
			    $this->error('属性修改失败');
		    }
		    $this->success('修改成功',url('GCate/cate_attr',array('cate_id'=>$cate_id)));
	    }
    }
}
