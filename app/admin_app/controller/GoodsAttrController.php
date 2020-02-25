<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/22
 * Time: 11:15
 */

namespace app\admin_app\Controller;

use app\common\model\GoodsAttrModel;
use app\common\model\GoodsCateModel;
use cmf\controller\AdminBaseController;
use think\Db;
use think\facade\Request;
use app\common\model\GoodsModel;
class GoodsAttrController extends AdminBaseController {
    //商品属性
    public function goodsAttr(){
            $goods_id = input('id',0);
            $data['cates'] = getTree(GoodsCateModel::all());
            $data['goods_info'] = GoodsModel::find($goods_id);
            $this->assign('data', $data);
            return $this->fetch('goods/goods_attr');

    }
    public function goodsAttrPost(){
        $goods_id = input('goods_id',0);
        $cate_id = input('cate_id',0);
        $res = service('admin_app/GoodsCateAttr')->getCateAttr($goods_id,$cate_id);
        return json(['list' => $res]);
    }
        //商品属性修改
        public function goodsAttrEdit(){
            if(Request::isPost()){
            $goods_id = input('goods_id');
            //先删除原来的商品属性
            GoodsAttrModel::destroy($goods_id);
            //添加新的商品属性
            $post  = Request::post();
            //print_r($info);
            $count = count($post['attrs']['value']);
            for($i=0;$i<$count;$i++){
                $ret[] = array(
                    'goods_id'=>$goods_id,
                    'attr_id'=>$post['attrs']['id'][$i],
                    'value'=>$post['attrs']['value'][$i],
                );
            }
            //print_r($ret);
            if(!Db::name('goods_attr')->insertAll($ret)){
                $this->error('添加属性失败');
            }
            $this->redirect('GAttr/goods_attr?id='.$goods_id);
        }

    }

}
