<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/30
 * Time: 12:14
 */

namespace app\home\controller;
use think\Db;
use think\facade\View;

class CartController extends CheckLogin {
    public function __construct()
    {
        return parent::__construct();
    }

    public function cart(){
        	    $UserCart = model('common/UserCart');
                if(session('?username')){
                    $user_id = session('uid');
	                $goods =  $UserCart->field('goods_info,goods_qty')->where(['user_id'=>$user_id])->column('goods_qty','goods_info');
                }else{
                    $goods = cookie('cart');
                }
                $step = input('step','');
                if($step==''){
                    if(!empty($goods)) {
                        $cart = service('home/GoodsCart')->getCartList($goods);
                        //print_r($cart);
                        View::assign('cart', $cart);
                    }
                    //dump($cart);
                    return View::fetch();
                }else {
                    if($step == 'checkout') {
                    	/*$post = request()->post();
                    	dump($post);
                    	die();*/
                        if(session('?username')){
                            $user_id = session('user_id');
                            $tem = $UserCart->field('goods_info,goods_qty')->where(['user_id'=>$user_id])->select();
                            $i=0;
                            foreach ($tem as $v){
	                            $UserCart->where(['goods_info'=>$_POST['key'][$i]])->update(['goods_qty'=>$_POST['qty'][$i]]);
                                $i++;
                            }
                            echo 1;
                        }else{
                            echo 1;
                        }

                    } elseif($step == 'remove') {
                        $goods = cookie('cart');
                        if(request()->isPost()) {
                            unset($goods[$_POST['key']]);
                            if(session('?username')){
	                            $UserCart->where(array('goods_info'=>$_POST['key']))->delete();
                            }
                            cookie('cart',$goods);
                            echo 1;
                        }
                    } elseif($step == 'clear') {
                        if(session('?username')){
                            $user_id = session('user_id');
	                        $UserCart->where(array('user_id'=>$user_id))->delete();
                        }
                        cookie('cart', null);
                        echo 1;
                    }
                }

        }
        //添加收货信息
        public function addinfo(){
            $user_id = session('user_id');
            $consignee = Db('user_consignee');
            $info = input();
            unset($info['/']);
            unset($info['__hash__']);
            if(input('act')=='add'){
                unset($info['act']);
                unset($info['id']);
                $info['user_id'] = $user_id;
                if(($id=$consignee->insert($info))===false) {
                    $this->error('地址添加失败');
                }
                $info['id'] = $id;
                echo json_encode(['status'=>'success','data'=>$info]);
            }elseif(I('get.act')=='edit'){
                unset($info['act']);
                $info['user_id'] = $user_id;
                if(!$consignee->where(array('id'=>$info['id']))->save($info)) {
                    $this->error('地址添加失败');
                }
                //print_r($info);
                echo json_encode(array('status'=>'success','data'=>$info));
            }
        }

        //订单
        public function confirm(){
            //print_r(cookie('cart'));
            $user_id = session('uid');//用户id

            if(!$user_id){
                return redirect('user/login');
            }
            if(input('step')=='shipping'){//运费获取
                $ship = request()->post();
                $fee = Db::name('shipping_method')->where(array('id'=>$ship['shipping_method']))->find();
                $fee['params']=json_decode($fee['params'],true);
                print_r($fee['params'][0]['charges']);
            }elseif(input('step')=='submit'){//订单提交

                $info = request()->post();
                $order = model('common/Order');//订单表
                $orderGoods = model('common/order_goods');//订单商品表
                $Goods = model('common/Goods');//商品表
	            //$UserCart = model('common/UserCart');
                $order_sn = get_order_sn();
                $goods = Db::name('user_cart')->where(array('user_id'=>$user_id))->select()->toArray();
                foreach ($goods as $v){
                    $go[$v['goods_info']] = $v['goods_qty'];
                }
               // print_r($goods);
                $data['cart'] =  service('home/GoodsCart')->getCartList($go);
                //运费获取
                $fee =  Db::name('shipping_method')->where(array('id'=>$info['shipping_method']))->find();
                $fee['params']=json_decode($fee['params'],true);

                //订单添加到数据库
                $oi = [
                    'order_sn'=>$order_sn,
                    'user_id'=>$user_id,
                    'shipping_method'=>$info['shipping_method'],
                    'payment_method'=>$info['payment_method'],
                    'order_status'=>0,
                    'goods_amount'=>$data['cart']['go'],
                    'shipping_amount'=>$fee['params'][0]['charges'],
                    'order_amount'=>$data['cart']['go']+$fee['params'][0]['charges'],
                    'memos'=>$info['memos'],
                    'created_date'=>time(),
                    'payment_date'=>'',
                    'thirdparty_trade_id'=>'',
                    'expired_date'=>time()+15*60
                ];
                //订单联系人信息
                $consignee = Db::name('user_consignee')->where(array('id'=>$info['consignee_id']))->find();
                $consignee['order_sn'] = $order_sn;
                unset($consignee['user_id']);
                unset($consignee['id']);
                unset($consignee['is_default']);
                print_r($consignee);
	            Db::name('order_consignee')->insert($consignee);
	            $order->startTrans();
	            $orderGoods->startTrans();
                //订单商品信息添加
                    print_r($data['cart']['ret']);
                    //die();
                    foreach ($data['cart']['ret'] as $v){
                        $og[] = [
                            'order_id'=>$order_sn,
                            'goods_id'=>$v['goods']['goods_id'],
                            'goods_optional'=>empty($v['opt']) ? '' : implode('-',$v['opt']),
                            'goods_name'=>$v['goods']['goods_name'],
                            'goods_image'=>$v['goods']['goods_image'],
                            'goods_qty'=>$v['goods_qty'],
                            'goods_price'=>$v['goods']['now_price']+$v['opt_price']+$fee['params'][0]['charges'],
                            'discount_price'=>0,
                            'goods_optional_price'=>$v['opt_price']
                        ];
                        $where = [
                            'goods_id'=>$v['goods']['goods_id'],
                            'stock_qty'=>array('EGT',$v['goods_qty'])
                        ];
                        //更新库存
                        if(!Db::name('goods')->where($where)->dec('stock_qty',$v['goods_qty'])){
                            $order_goods->rollback();
	                        $orderGoods->where(['order_sn'=>$order_sn])->delete();
                            $this->error('商品'.$v['goods']['goods_id'].$v['goods']['goods_name'].'库存不足');
                        }

                }
                $orderGoods->saveAll($og);
                if(!$order->insert($oi)){
                    $order->rollback();
                    $this->error('订单失败');
                }
	            $orderGoods->commit();
                cookie('cart',null);
                Db::name('user_cart')->where(['user_id'=>$user_id])->delete();
                $this->redirect('Pay/index?id='.$order_sn);
            }else{
                //订单确认
                $consignee = Db::name('user_consignee');
                $data['address'] = $consignee->where(['user_id'=>$user_id])->select();
                $goods = Db::name('user_cart')->where(['user_id'=>$user_id])->select();
                foreach ($goods as $v){
                    $go[$v['goods_info']] = $v['goods_qty'];
                }
                $data['cart'] = service('home/GoodsCart')->getCartList($go);
               // print_r($data['cart']);
                $data['shipping'] = Db::name('shipping_method')->find();
                $data['shipping']['params'] = json_decode($data['shipping']['params'],true);
                $data['user_id'] = $user_id;
                $this->assign('data',$data);
                return $this->fetch();
            }
        }
}
