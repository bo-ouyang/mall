<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/28
 * Time: 17:27
 */

namespace app\common\model;

use think\Model;

class GoodsCateModel extends Model {

			/*public function GoodsCateAttr(){
				$this->belongsTo('common/GoodsCate');
			}*/
			public static  function init(){
                self::event('before_insert', function ($goodscate) {
                    $ret = GoodsCateModel::where(['cate_name'=>$goodscate->cate_name])->find();
                    if($ret){
                        return false;
                    }
                });
            }
            public function getBrand($cate_id){

            }
            public function toArray() {

            }


}
