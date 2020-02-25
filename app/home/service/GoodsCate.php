<?php


namespace app\home\service;


use app\common\model\BrandModel;
use think\Db;

class GoodsCate {
		public static function getGoodsCate(){

		}
		public static function getCategory(){

		}
		public static function getCategoryTree(){

		}
		public function getCateAttr($cate_id){

		$field = 'gca.opts,gca.attr_id,gca.name,gca.filtrate,gc.cate_id';
		$attrs = model('common/GoodsCate')->alias('gc')
			->leftJoin('goods_cate_attr gca','gc.cate_id=gca.cate_id')
			->field($field)
			->where(['gc.cate_id'=>$cate_id,'gca.filtrate'=>'1'])
			->select();
			foreach ($attrs as $k=>$v){
			$attrs[$k]['opts'] = json_decode($v['opts']);
			}
			return $attrs;
		}
		public static function getCategoryBrand($cate_id){
			$data=[];
			$brands = model('common/GoodsCate')->alias('gc')
				->leftJoin('goods_cate_brand gcb','gc.cate_id=gcb.cate_id')
				->where(['gcb.cate_id'=>$cate_id])
				->select();
			if(!empty($brands)) {
				foreach ($brands as $v) {
					$bs[] = $v['brand_id'];
				}
				$where['brand_id'] = ['IN',$bs];
				$data =BrandModel::field('brand_id,brand_name')->where($where)->select();
			}
			//print_r($data);
			return $data;
		}
}
