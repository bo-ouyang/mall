<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/3
 * Time: 15:07
 */

namespace app\home\controller;
use think\facade\View;

class OrderController extends CheckLogin {
        public function index(){
            $trade= input('out_trade_no','');
            $user_id = session('uid');
	        $Order = new \app\common\model\OrderModel();
            $od = $Order->getOrderByUserId($user_id);
            foreach ($od as $k=>$v){
                $od[$k]['goods_info'] = $Order->getOrderGoods($v['order_sn'])->toArray();
            }
            if($trade!=''){
	            $Order->where(['order_sn'=>$trade])->setField(array('order_status'=>1));
            }
            print_r($od);
            $this->assign('order',$od);
            return View::fetch();
        }
        public function view(){
            $order_id = input('id');
	        $Order = model('common/Order');
            $user_id = session('user_id');
            $og = $Order->getOrder($user_id,$order_id);
            $og['goods_info'] = $Order->getOrder_goods($order_id);
            $og['shipping']   = M('shipping_method')->where(array('id'=>$og['order']['shipping_method']))->getField('name');
            $og['pay_method'] = M('payment_method')->where(array('id'=>$og['order']['payment_method']))->getField('name');
            //print_r($og);
            $this->assign('order',$og);
            $this->display('Order/view');
        }
        public function rebuy(){

        }
        public function cancel(){
            $order_id = I('get.id');
            $order = D('order');
            $order->where(array('order_id'=>$order_id))->setField(array('order_status'=>2));
            $goods = M('order_goods')->field('goods_id,goods_qty')->where(array('order_id'=>$order_id))->select();
                foreach ($goods as $v){
                    M('goods')->where(array('goods_id'=>$v['goods_id']))->setInc('stock_qty',$v['goods_qty']);
                }
            $this->redirect('Order/view',array('id'=>$order_id));
        }
}
