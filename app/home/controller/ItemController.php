<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/21
 * Time: 11:34
 */
namespace app\home\Controller;
use app\common\model\GoodsAlbumModel;
use app\common\model\GoodsModel;
use think\facade\Cookie;
use think\Db;
use think\facade\Request;
use think\facade\View;

class ItemController extends CheckLogin {
        public function index(){
        	$cart = Cookie::get('cart');
        	//dump($cart);
            $goods_id = input('goods_id');
            $cates =Db::name('goods_cate')->select();
            $family = cate_tree($cates);
            $data['album'] = GoodsAlbumModel::where(['goods_id'=>$goods_id])->select()->withAttr('image',function ($value,$data){
                return Request::domain().GoodsModel::IMAGE_RUL.'350/'.$value;
            });
            $data['family'] = $family;
            $data['info'] = GoodsModel::get($goods_id)->withAttr('goods_image',function ($value,$data){
                return Request::domain().GoodsModel::IMAGE_RUL.'350/'.$value;
            });
          //  print_r($data['info']);
            $data['type'] = service('home/Goods')->getGoodsOptional($goods_id);
            $data['attr'] = service('home/Goods')->getGoodsAttr($goods_id);
            $data['cate'] = cate_parent($cates,$data['info']['cate_id']);
            View::assign('data',$data);
            return View::fetch('index');
        }
        public function add(){
            if(request()->isPost()){
                $info = Request::post();
	            $UserCart = model('common/UserCart');
                $cookie =cookie('cart');
                //先追加上次的信息
                if(!empty($info['opts'])){
                    array_unshift($info['opts'],$info['id']);
                    $key=implode('_',$info['opts']);
                }else{
                    $key=$info['id'].'_';
                }
                //print_r($key);
                //修改数据库里的数量
                if(session('?username')){
                    $user_id = session('uid');
                    //echo $key;
                    $userCardGoods = $UserCart->where(['user_id'=>$user_id,'goods_info'=>$key])->findOrEmpty();
	                $user_cart = $UserCart->where(['user_id'=>$user_id,'goods_info'=>$key])->select();
                    if(!empty($user_cart)){//如果原先购物车有商品
                        if(!empty($userCardGoods)){
	                        $UserCart->where(['goods_info'=>$key,'user_id'=>$user_id])->setInc('goods_qty',$info['qty']);
                        }else{
                            $temp = [
                                'user_id'=>$user_id,
                                'goods_qty'=>$info['qty'],
	                            'goods_info'=>$key
                            ];
                            if(!$UserCart->save($temp)){
                            	echo "<script>alert('购物车添加失败') </script>";
                            }
                            echo 1;
                        }
                    }else{//购物车为空添加商品
                        $arr = [
                            'user_id'=>$user_id,
                            'goods_info'=>$key,
                            'goods_qty'=>$info['qty'],
                        ];
                        if(!$UserCart->save($arr)){
	                        echo "<script>alert('购物车添加失败') </script>";
                        }
                        echo 1;
                    }
                }else{
                    if(!empty($cookie)) {
                        //修改cookie里的数量
                        if(array_key_exists($key, $cookie)) {//如果键值存在
                            $cookie[$key] += $info['qty'];
                        } else {
                            $cookie = array_merge($cookie, array($key => $info['qty']));
                        }
                        cookie('cart', $cookie);
                        echo 0;
                    } else {
                    	Cookie::set('cart','');
                        cookie('cart', [$key => $info['qty']]);
                        echo 1;
                    }
                }

            }
        }

        // ['8_89_90'=>20]
}
