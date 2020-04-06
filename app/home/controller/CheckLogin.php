<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/21
 * Time: 11:31
 */

namespace app\home\controller;

use app\home\service\Goods;
use think\App;
use think\Controller;
use think\Db;
use think\facade\View;
class CheckLogin extends Controller {
            public function __construct(App $app=null) {
                parent::__construct();
                $controller = ['Index','Item','User'];
                $action = ['login','register','cart'];
	            $cates = Db::name('goods_cate')->select();
	            $family = cate_tree($cates);
	            $data = (new Goods())->getGoodsList();
	            $data['family'] = $family;
	            View::share('data',$data);
                if(!in_array(request()->controller(),$controller)&&!in_array(request()->action(),$action)){
                    print_r(request()->action());
                       return redirect('user/login');
                }else{
	                $username = session('username');
	                $avatar = session('avatar');
	                View::share('username',$username);
	                View::share('avatar',$avatar);
                }
                //dump($family);
            }
}
