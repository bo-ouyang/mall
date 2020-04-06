<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/27
 * Time: 20:59
 */
namespace app\common\model;
use think\Db;
use think\db\Where;
use think\Model;
class GoodsModel extends Model {

        const IMAGE_RUL = '/static/Uploads/';

		protected $pk = 'goods_id';
        protected $table = 'verydows_goods';
		public function goodscate(){
			return $this->hasMany('GoodsCateModel');
		}

		public function ordergoods(){
			return $this->hasMany('OrderGoodsModel');
		}

        public function getGoodsCate($params,$cate_id,$p=1){
            $field = 'g.goods_id,g.goods_name,g.goods_image,g.now_price,g.cate_id,g.created_date';
            //查找所有子分类
	        $sort =input('sort');
            $category = Db::name('goods_cate')->select();
            $subids= [];
            $subids= getCate_ChildId($category,$subids,$cate_id);
            $subids= array_unique($subids);
            $where['g.cate_id'] =['in',$subids];
            //获取品牌id 组合where条件
            $brandId = input('brand_id',0);
            if($brandId!=0){
                $where['g.brand_id']= ['in',$brandId];
            }
            //根据分类id查找最高价格
            $max_price = $this->where('cate_id','in',$subids)->order('now_price','desc')->value('now_price');
            print_r($max_price);
            //获取属性id 通过属性id 查找 商品id 拼接where条件
            if(!empty($params['attrs'])){
                foreach ($params['attrs'] as $k=>$v){
                    if($v!=-1){
                        $attr_id[] = $k;
                        $value[]   =$v;
                    }else{
                        $attr_id[] = $k;
                    }
                }
                empty($attr_id)?:$where['ga.attr_id'] = ['in',$attr_id];
                empty($value)?:$where['ga.value'] = ['in',$value];
            }
            //价格 区间
            $pc = explode('-', input('get.price'));
            if(count($pc)==2) {
                $where['g.now_price'] = [
                    ['EGT', $pc[0]],
	                ['ELT', $pc[1]],
                ];
            }
            //排序
            $sortField = ' goods_id ';

            if($sort){
            	$sortField= [
            		1=>'g.now_price',
		            2=>'g.now_price',
		            3=>'g.create_dated',
		            4=>'g.create_dated'
	            ];
            	$sort = $sort/2==0?'desc':'asc';
            }
	        $data['goods'] = GoodsModel::alias('g')
		        ->leftJoin('goods_attr ga','g.goods_id=ga.goods_id')
		        ->field($field)
		        ->where(new Where($where))
		        ->order($sortField,$sort)
		        ->select();
            $data['price'] = $this->getPriceList($max_price);
            return $data;
        }
        public function getPriceList($maxPrice,$step=4){
            $end = $avg = ceil($maxPrice/$step);
            $start = 0;
            for($i=0;$i<$step;$i++){
                $ret[] = "$start-$end";
                $start = $end+1;
                $end  += $avg;
            }
            return $ret;
        }

}
