<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/17
 * Time: 14:35
 */

namespace app\common\model;
use think\Db;
use think\Model;
class OrderModel extends Model {
        public function getOrderId(){
            $order_id = date('ymd',time()).mt_rand(00000,99999);
            $oid = $this->where(array('order_sn'=>$order_id))->find()?$this->getOrderId():$order_id;
            return $oid;
        }
        //查订单
        public function getOrderByUserId($user_id,$order_id=''){
            if($order_id!=''){
                $og['order'] = $this->where(['user_id'=>$user_id,'order_sn'=>$order_id])->find();
                $og['consignee'] = Db::name('order_consignee')->find();
            }else{
                $og = $this->where(['user_id'=>$user_id])->select();
            }
            return $og;
        }
        //订单商品信息
        public function getOrderGoods($order_id){
            $field = 'goods_optional,goods_id,goods_name,goods_image,goods_qty,goods_price';
            $og = Db::name('order_goods')->field($field)->where(['order_sn'=>$order_id])->select();
            /*foreach ($og as $k=>$v){
                $og[$k]['goods_optional'] = empty($v['goods_optional'])?'':json_decode($v['goods_optional'],true);
            }*/
            return $og;
        }
        //订单详情

}
