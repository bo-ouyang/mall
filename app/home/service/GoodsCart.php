<?php


namespace app\home\service;


use think\Db;

class GoodsCart {
	public function getCartList($goods){
		$tempGoods =[];
		$total_amount=0;
		//print_r($goods);
		if(!empty($goods)){
			foreach ($goods as $k=>$v){
				$kss = explode('_',$k);
				$goodsId = array_shift($kss);
				$tempGoods[] = [
					'attr'=>$k,
					'goods_id'=>$goodsId,
					'goods_qty'=>$v,
					'goods_optional'=>$kss,

				];
			}
			//print_r($tempGoods);
			foreach ($tempGoods as $k=>$v){
				$tempGoods[$k]['goods']= model('common/Goods')->where(['goods_id'=>$v['goods_id']])->field('goods_id,goods_name,now_price,goods_image,stock_qty')->find()->toArray();
					if(!empty($v['goods_optional'])){
						$opt_price = 0;
						foreach ($v['goods_optional'] as $vv){
							$type_idd    = model('common/GoodsOptional')->where(['id'=>$vv])->value('type_id');
							$type_namee  = model('common/GoodsOptionalType')->where(['type_id'=>$type_idd])->value('name');
							$opt_price += model('common/GoodsOptional')->where(['id'=>$vv])->value('opt_price');
							$tempGoods[$k]['opt'][$type_namee]  = model('common/GoodsOptional')->where(['id'=>$vv])->value('opt_text');
						}
						$tempGoods[$k]['com_price'] = $opt_price+$tempGoods[$k]['goods']['now_price'];
                        $tempGoods[$k]['opt_price'] = $opt_price;
					}
				$total_amount += $tempGoods[$k]['com_price']*$v['goods_qty'];
			}
		}
		return [
			'ret'=>$tempGoods,
			'go'=>$total_amount,
		];
	}
}
