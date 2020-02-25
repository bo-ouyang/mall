<?php


namespace app\home\controller;


use think\Controller;
use think\Db;
use think\Model;

class Test extends Controller {
			public function orders() {
				$origin = Db::name('zzzzz')->column('price', 'order_sn');
				$zfb = Db::name('zzzz')->where(['order_status' => '成功'])->column('order_price', 'order_sn');
				foreach ($origin as $k => $v) {
					$k = trim($k);
					$v = trim($v);
					$origins[$k] = $v;
				}
				foreach ($zfb as $k => $v) {
					$k = trim($k);
					$v = trim($v);
					$zfbs[$k] = $v;
				}
				//$originKey = array_keys($origin);
				//$zfbKey    = array_keys($zfb);

				//dump($origin);
				echo '原订单数:' . count($origins) . "<br>";
				echo '支付宝成功订单数:' . count($zfbs) . "<br>";
				echo "<br>";
				echo "<br>";
				echo "<br>";
				//$origin = Db::name('zzzzz')->field('price,order_sn')->select();
				//$ret = Db::name('zzzz')->field('f8,order_sn')->select();
				//dump($ret);
				$i = 0;
				foreach ($origins as $k => $v) {
					$k = trim($k);
					$v = trim($v);
					if (!array_key_exists($k, $zfbs)) {
						$zfbNotExist[$k] = $v;
					}
				}
				foreach ($zfbs as $k => $v) {
					$k = trim($k);
					$v = trim($v);
					if (!array_key_exists($k, $origins)) {
						$originNotExist[$k] = $v;
					}
					//echo $origin = Db::name('zzzzz')->where(['order_sn'=>$v['order_sn']])->field('order_sn,price')->find();
				}
				echo '支付宝不存在的订单';
				dump($zfbNotExist);
				echo '后台不存在的订单,支付宝有';
				dump($originNotExist);
				//echo $i;
				//dump(array_diff_assoc($origin,$ret));

				$o_19 = $this->timesOrder('2019-9-19','2019-9-20');
				$o_20 = $this->timesOrder('2019-9-20','2019-9-21');
				$o_21 = $this->timesOrder('2019-9-21','2019-9-22');
				$o_22 = $this->timesOrder('2019-9-22','2019-9-23');
				$o_23 = $this->timesOrder('2019-9-23','2019-9-24');
				$o_24 = $this->timesOrder('2019-9-24','2019-9-25');
				$o_25 = $this->timesOrder('2019-9-25','2019-9-26');
				$o_26 = $this->timesOrder('2019-9-26','2019-9-27');
				array_sum(array_column($o_19,'order_price'));
				echo count($o_19).",总额:".array_sum(array_column($o_19,'order_price')).'------'.(612315+37204-42082)."<br>";
				echo count($o_20).",总额:".array_sum(array_column($o_20,'order_price')).'------'.(346910+64746)."<br>";
				echo count($o_21).",总额:".array_sum(array_column($o_21,'order_price')).'------'.(300197+55044)."<br>";
				echo count($o_22).",总额:".array_sum(array_column($o_22,'order_price')).'------'.(215936+43970)."<br>";
				echo count($o_23).",总额:".array_sum(array_column($o_23,'order_price')).'------'.(215938+51016)."<br>";
				echo count($o_24).",总额:".array_sum(array_column($o_24,'order_price')).'------'.(244130+50990)."<br>";
				echo count($o_25).",总额:".array_sum(array_column($o_25,'order_price')).'------'.(217542+49060)."<br>";
				//dump($o_21);
			}

			function timesOrder($start,$end){
				$order = Db::name('zzzz')->withAttr('create_time', function ($value, $data) {
					return strtotime($value);
				})->where(['order_status'=>'成功'])->select();
				foreach ($order as $v) {
					if ($v['create_time'] > strtotime($start) && $v['create_time'] < strtotime($end)) {
						$ret[] = $v;
					}
				}
				return $ret;
			}
			public function lock(){
				$rand = rand(0,10);
				 $ret =Db::name('goods')->where(['goods_id'=>339])->lock(true)->fetchSql(false)->find();
				 //dump($ret);
				 if($ret['stock_qty']==0){
				 	 echo '000';
				 }else{
					 Db::name('goods')->where(['goods_id'=>339])->dec('stock_qty',2)->update();
					 $id = Db::name('test')->insert(['goods_id'=>339,'stock_qty'=>2]);
					 //echo $id;
					 //Db::commit();
				 }


				 //dump($ret) ;
			}
}
