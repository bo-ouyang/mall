<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/30
 * Time: 12:14
 */

namespace app\home\Controller;
use think\Db;
use think\facade\View;

class CartController extends CheckLogin {
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
	/**
	 * @throws \think\Exception
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 * @throws \think\exception\PDOException
	 */
        //订单
        public function confirm(){
            //print_r(cookie('cart'));
            $user_id = session('uid');//用户id
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
	            $UserCart = model('common/UserCart');
                $order_id = model('common/Order')->getOrderId();//获取订单id
                $goods = $UserCart->where(array('user_id'=>$user_id))->select();
                foreach ($goods as $v){
                    $go[$v['goods_info']] = $v['goods_qty'];
                }
                $data['cart'] = $UserCart->getInfo($go);
                //运费获取
                $fee =  Db::name('shipping_method')->where(array('id'=>$info['shipping_method']))->find();
                $fee['params']=json_decode($fee['params'],true);
                //print_r($data['cart']);print_r($info);
                //$order_goods->startTrans();
                //订单添加到数据库
                $oi = [
                    'order_id'=>$order_id,
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
                ];
                //订单联系人信息
                $consignee = Db::name('user_consignee')->where(array('id'=>$info['consignee_id']))->find();
                $consignee['order_id'] = $order_id;
                unset($consignee['user_id']);
                unset($consignee['id']);
                unset($consignee['is_default']);
	            Db::name('order_consignee')->insert($consignee);
                //订单商品信息添加

                    foreach ($data['cart']['ret'] as $v){
                        $og = [
                            'order_id'=>$order_id,
                            'goods_id'=>$v['goods']['goods_id'],
                            'goods_optional'=>isset($v['opts'])?json_encode($v['opts']):'',
                            'goods_name'=>$v['goods']['goods_name'],
                            'goods_image'=>$v['goods']['goods_image'][0],
                            'goods_qty'=>$v['qty'],
                            'goods_price'=>$v['goods']['now_price']+$v['opt_price']+$fee['params'][0]['charges'],
                        ];
                        $where = [
                            'goods_id'=>$v['goods']['goods_id'],
                            'stock_qty'=>array('EGT',$v['qty'])
                        ];
                        //更新库存
                        if(!$Goods->where($where)->setDec('stock_qty',$v['qty'])){
                            //$order_goods->rollback();
	                        $orderGoods->where(['order_id'=>$order_id])->delete();
                            $this->error('商品'.$v['goods']['goods_name'].'库存不足');
                        }
	                    $orderGoods->save($og);
                }
                if(!$order->insert($oi)){
                    $this->error('订单失败');
                }
	            $orderGoods->commit();
                cookie('cart',null);
                $UserCart->where(['user_id'=>$user_id])->delete();
                $this->redirect('Pay/index?id='.$order_id);
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
