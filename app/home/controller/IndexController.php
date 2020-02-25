<?php
namespace app\home\controller;
use app\home\service\Goods;

use think\captcha\Captcha;
use think\Db;
use think\facade\Session;
use think\facade\View;

class IndexController extends CheckLogin {
    public function index() {

        return View::fetch();
    }
    public function cate(){
        $cate_id = input('cate_id');
        $GC = new GoodsCate();
        $G  = new Goods();
        //所有参数
        $params = input();
        $attr_id = input('attr_id',0);
        $value   = input('value','');
        print_r($attr_id);
        print_r($value);
        if($attr_id!=0&&$value!=''){
            $aids = explode('_',$attr_id);//所有属性id
            $vas  = explode('_',$value);//所有属性值
            foreach ($vas as $k=>$v){
                $values[$aids[$k]]=$v;
            }
        }
        print_r($values);
        $params['attrs']=$values;
        //查找分类下的品牌
        $brand = $GC->getBrand($cate_id);
        $data= $G->getGoodsCate($params,$cate_id);
        $data['cate_id']=$cate_id;
        $cates = $GC->select();
        $family = cate_tree($cates);
        //获取分类属性
        $data['attr']   = $GC->getCateAttr($cate_id);
        $data['values'] = $values;
        //分类树
        $data['family'] = $family;
        //传参
        $data['params'] = $params;
        //品牌
        $data['brand']  = $brand;
        //商品总数
        $data['goods_count']=count($data['goods']);
        //print_r($data['attr']);
        //print_r($data);
        View::assign('data',$data);
       return View::fetch();
    }
    public function search(){

    }
    public function captcha(){
	    $captcha = new Captcha();
	    return $captcha->entry();
    }
}
