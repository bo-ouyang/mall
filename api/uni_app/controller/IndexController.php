<?php
namespace api\uni_app\controller;

use app\common\model\GoodsModel;
use app\common\model\UserModel;
use cmf\controller\RestAdminBaseController;
use think\Controller;
use think\Db;

class IndexController extends Controller {
		public function index(){
			//$ret = GoodsModel::with('GoodsCate')->select();
			$ret = UserModel::with('userprofile')->select();
			//echo $ret->goodscate->cate_name;
			dump($ret);
		}
}
