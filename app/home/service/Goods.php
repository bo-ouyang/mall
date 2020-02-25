<?php

namespace app\home\service;
use app\common\model\GoodsAlbumModel;
use app\common\model\GoodsAttrModel;
use app\common\model\GoodsModel;
use think\Db;
use think\facade\Request;


class Goods {
	public   function getGoodsList(){
		$field = 'goods_id,goods_name,goods_image,now_price,
                    goods_brief,new,recommend,bargain,status';
		$new        = ['status'=>'1','new'=>'1'];
		$recommend  = ['status'=>'1','recommend'=>'1'];
		$bargain    = ['status'=>'1','bargain'=>'1'];
		$data['new'] = GoodsModel::field($field)->where($new)->limit(5)->select()->withAttr('goods_image',function ($value,$data){
		    return 'http://'.Request::host().'/static/Uploads/temp/'.$value;
        });
		$data['recommend'] = GoodsModel::field($field)->where($recommend)->limit(5)->select()->withAttr('goods_image',function ($value,$data){
            return 'http://'.Request::host().'/static/Uploads/temp/'.$value;
        });
		$data['bargain'] = GoodsModel::field($field)->where($bargain)->limit(5)->select()->withAttr('goods_image',function ($value,$data){
            return 'http://'.Request::host().'/static/Uploads/temp/'.$value;
        });
		return $data;
	}
	public  function getGoodsAttr($goods_id){
		$data =GoodsAttrModel::alias('ga')->join('goods_cate_attr gca','ga.attr_id=gca.attr_id')->
			where(['goods_id'=>$goods_id])->field('ga.value,gca.name')->fetchSql(false)->select();
		return $data;
	}
	public  function getGoodsOptional($good_id){
		$type = model('common/GoodsOptional')::where(['goods_id'=>$good_id])->distinct(true)->column('type_id');
		$type = model('common/GoodsOptionalType')::where('type_id','in',$type)->fetchSql(false)->select()->toArray();
		foreach ($type as $k=>$v){
			$type[$k]['info'] = Db::name('goods_optional')->where(['goods_id'=>$good_id,'type_id'=>$v['type_id']])->select();
		}
		return $type;
	}
	public  function getGoodsOptionalType($good_id){

	}


}
