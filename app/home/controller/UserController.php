<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/21
 * Time: 11:33
 */
namespace app\home\controller;

use think\captcha\Captcha;
use think\facade\Cookie;
use think\Db;
use think\facade\Session;
use think\facade\Validate;
use think\facade\View;

class UserController extends CheckLogin {
    public function index(){
	    $user_id=session('uid');
	    $uinfo = Db::name('user')->alias('u')
		    ->leftJoin('user_group ug','u.group_id=ug.group_id')
		    ->field('u.*,ug.group_name,ug.discount_rate')
		    ->where(['user_id'=>$user_id])->find();
	    $this->assign(['uinfo'=>$uinfo]);
        return View::fetch();
    }
    public function userbar(){
        if(session('?username')){
            $user = session('username');
	        $avatar = session('avatar');
            $user_id=session('uid');
            return json(['username'=>$user,'user_id'=>$user_id,'status'=>1,'avatar'=>$avatar]);
        }else{
	        return json(['status'=>0]);
        }
    }
    public function cartbar(){
        if(Session::has('username')){
            $user_id=session('uid');
            $count =Db::name('user_cart')->where(array('user_id'=>$user_id))->count('user_id');
            echo $count;
        }else{
        	$count= Cookie::has('cart')?count(cookie('cart')):0;
            echo $count  ;
        }
    }
    //个人资料
    public function profile(){
        //print_r(session());
        $user_id=session('uid');
        $info = Db::name('user_profile')->alias('up')
	            ->leftJoin('user u','up.user_id=u.user_id')
	            ->where(['u.user_id'=>$user_id])->find();
        View::assign('info',$info);
	    return View::fetch();
    }
    public function profile_edit(){
            //var_dump(session());
            $post = input('');
            $user_id = session('user_id');
            $post['user_id']=$user_id;
            $mobile['mobile'] = $post['mobile_no'];
            unset($post['mobile_no']);
            $user = new \app\common\model\UserModel();
            $user_pro = model('common/UserProfile');
            if($user_pro->where(array('user_id'=>$user_id))->value('user_id')){
                if($user_pro->save($post)&&$user->where(array('user_id'=>$user_id))->update(['mobile'=>$mobile])){

                }
            }else{
                if($user_pro->add($post)&&$user->where(array('user_id'=>$user_id))->update(['mobile'=>$mobile])){

                }
            }
	    redirect('User/profile');
    }
    //头像上传
    public function avatar(){
        $user = new \app\common\model\UserModel();
        $path = $user->img_upload();//上传
        $path = json_encode('.'.$path);
        //$callback_func = I('post.callback_func');
        echo "<script type=text/javascript>window.parent.showCrop('1',$path);</script>";
    }
    //头像裁剪
    public function avatar_crop(){
        if($this->request->isAjax()) {
            $user = new \app\common\model\UserModel();
            $param = $this->request->post();
            $user_id = session('user_id');
            $user->img_crop($param['w'],$param['h'],$param['x'],$param['y']);
            $user->where(array('user_id'=>$user_id))->update(array('avatar'=>input('streams')));
            return json(array('status'=>1,'avatar'=>input('streams')));
        }
    }
    //登陆
    public function login(){
    	echo md5("ROOT002");
	    return View::fetch();
    }
    public function loginPost(){
	    if(request()->isPost()){
		    $user = model('common/User');
		    $username = input('username');
		    $password = input('password');
		    $value  = input('captcha');
		    $stay = input('stay','');
		    $captcha  = new Captcha();
		    if( !$captcha->check($value))
		    {
			    $this->error('验证码错误');
		    }
		   // echo $user->where(['username'=>$username])->value('password');die();
		    if(md5($password)==$user->where(['username'=>$username])->value('password')){
			    $user= $user->where(['username'=>$username])->field('user_id,username,avatar')->find();
				if($stay){
					Cookie::set('xx-username',$username);
				}else{
					Session::set('uid',$user['user_id']);
					Session::set('username',$username);
					Session::set('avatar',$user['avatar']);
				}
			    $cart = cookie('cart');
			    if(!empty($cart)){//如果cookie不为空
				    $user_cart = Db::name('user_cart')->field('goods_info,goods_qty')->where(array('user_id'=>$user['user_id']))->select();
				    if(!empty($user_cart)){
					    //判断商品是否已经存在
					    foreach ($cart as $k=>$v){
						    foreach ($user_cart as $vv){
							    if($k==$vv['goods_info']){
								    Db::name('user_cart')->where(array('goods_info'=>$vv['goods_info']))->update(['goods_qty'=>$vv['goods_qty']+$v]);
								    unset($cart[$k]);
							    }
						    }
					    }
					    $i=0;
					    foreach ($cart as $k=>$v){
						    $tem[$i]['user_id'] = $user['user_id'];
						    $tem[$i]['goods_info'] = $k;
						    $tem[$i]['goods_qty'] = $v;
						    $i++;
					    }
				    }else{
					    $i=0;
					    foreach ($cart as $k=>$v){
						    $tem[$i]['user_id'] = $user['user_id'];
						    $tem[$i]['goods_info'] = $k;
						    $tem[$i]['goods_qty'] = $v;
						    $i++;
					    }
				    }
				    Db::name('user_cart')->insertAll($tem);
				    cookie('cart',null);
			    }
			    return redirect('Index/index');
		    }else{
			      $this->error('账号或密码错误');;
		    }

	    }
    }
    public function logout(){
	    Session::clear();
         return   redirect('User/login') ;
    }
    //注册
    public function register(){
	    return View::fetch();
    }
    public function registerPost(){
	    $rule =   [
		    'email'  => 'email',
	    ];
	    $message  =   [
		    'email'        => '邮箱格式错误',
	    ];

	    if(request()->isPost()){
		    $username = input('username');
		    $mobile = input('mobile');
		    $sms_code = input('sms_code');
		    $post = $this->request->post();
		    if($ret = Db::name('user')->where(array('username'=>$username))->find()){
			    return response('用户已存在'   ,201);
		    }
		    if($sms_code==$ret = Db::name('sms')->where(array('mobile'=>$mobile))->value('code')){

		    }
		    if(!$this->validate($post,$rule,$message)){
			    return Validate::getError();
		    }
		    $post['password']=md5($post['password']);
		    if(Db::name('user')->strict(false)->replace(true)->insert($post)){
			    redirect('User/login');
		    }

	    }
    }
    //安全中心
    public function security(){

    }
	public function sendSms(){
    	$url = 'http://v.juhe.cn/sms/send';
    	$mobile = input('mobile');
    	$code = mt_rand(0,9999);
    	//return 111;
    	$conf = [
		    'key'   => '46e4bc0f0e0c578c3a234ee470fde3e2', //您申请的APPKEY
		    'mobile'    => $mobile, //接受短信的用户手机号码
		    'tpl_id'    => 184087, //您申请的短信模板ID，根据实际情况修改
		    'tpl_value' =>"#code#={$code}&#company#=聚合数据" //您设置的模板变量，根据实际情况修改
	    ];
		$content=juhecurl($url,$conf,1);
		if($content){
			$result = json_decode($content,true);
			$error_code = $result['error_code'];

			if($error_code == 0){
				//状态为0，说明短信发送成功
				if(Db::name('sms')->where(['mobile'=>$mobile])->findOrFail()){
					Db::name('sms')->strict(false)->insert(['code'=>$code,'mobile'=>$mobile,'sid'=>$result['result']['sid'],'time'=>time()]);
				}else{
					Db::name('sms')->strict(false)->where(['mobile'=>$mobile])->update(['code'=>$code,'sid'=>$result['result']['sid']]);
				}
				return json(['msg'=>'短信发送成功','code'=>$error_code,'sid'=>$result['result']['sid']]);
				echo "短信发送成功,短信ID：".$result['result']['sid'];
			}else{
				//状态非0，说明失败
				$msg = $result['reason'];
				Db::name('sms')->strict(false)->insert(['code'=>$code,'phone'=>$phone,'msg'=>$msg]);
				return json(['msg'=>'短信发送失败','code'=>1]);
				echo "短信发送失败(".$error_code.")：".$msg;
			}
		}else{
			//返回内容异常，以下可根据业务逻辑自行修改
			return json(['msg'=>'请求发送短信失败']);
		}
	}
}
