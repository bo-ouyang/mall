<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/23
 * Time: 11:10
 */

namespace app\admin_app\service;




use app\common\model\GoodsAttrModel;
use app\common\model\GoodsCateAttrModel;
class GoodsCateAttr {


            protected $table = 'verydows_goods_cate_attr';
            public function getCateAttr($goods_id,$cate_id){
                $goodsAttr = GoodsAttrModel::where(['goods_id'=>$goods_id])->select()->toArray();
                foreach ($goodsAttr as $k=>$v){
                    $goodsAttr[$k]['opts'] = GoodsCateAttrModel::where(['attr_id'=>$v['attr_id'],'cate_id'=>$cate_id])->field('opts')->find();
                    $goodsAttr[$k]['name'] = GoodsCateAttrModel::where(['attr_id'=>$v['attr_id'],'cate_id'=>$cate_id])->value('name');
                }
                return $goodsAttr;
            }

}
