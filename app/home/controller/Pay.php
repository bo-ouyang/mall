<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/17
 * Time: 14:29
 */

namespace app\home\Controller;
use Yansongda\Pay\Pay as YPay;
use Yansongda\Pay\Log as YLog;
class Pay extends CheckLogin {
        public function index(){
            $order_id = input('id');
            $order = model('common/Order');
            $oi = $order->field('order_amount,payment_method')->where(['order_id'=>$order_id])->find();
            $oi['order_id'] = $order_id;
            print_r($oi);
            if($this->request->isPost()){
                $pid = input('change_payment');
                $order->where(array('order_id'=>$order_id))->setField(array('payment_method'=>$pid));
                $this->redirect('Pay/index',array('id'=>$order_id));
            }
            $this->assign('order',$oi);
            $this->display();
        }
    public function alipay(){
        $Order = model('common/Order');
        $order_id = input('id');
        $order_amount =$Order->where(array('order_id'=>$order_id))->value('order_amount')  ;
        //构造参数
        $aliConfig = config('alipay');
        $order = array(
            'out_trade_no' => $order_id,
            'total_amount' => $order_amount,
            'subject' => 'test 测试',
        );
        $alipay = Pay::alipay($aliConfig)->web($order);
        return $alipay->send();// laravel 框架中请直接 `return $alipay`
    }
    public function notify() {
        $Order = D('order');
        $aliConfig = C('alipay');
        $alipay = Pay::alipay($aliConfig);
        try{
            $data = $alipay->verify(); // 是的，验签就这么简单！
            // 请自行对 trade_status 进行判断及其它逻辑进行判断，在支付宝的业务通知中，只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
            // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
            // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
            // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）；
            // 4、验证app_id是否为该商户本身。
            // 5、其它业务逻辑情况
            if($data['trade_status'] == 'TRADE_FINISHED') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
                //如果有做过处理，不执行商户的业务程序
                //注意：
                //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
            }elseif ($data['trade_status'] == 'TRADE_SUCCESS') {
                $Order->where(array('id'=>$data['out_trade_no']))->save(array('payment'=>'yes','trade_no'=>$data['trade_no']));
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
                //如果有做过处理，不执行商户的业务程序
                //注意：
                //付款完成后，支付宝系统发送该交易状态通知
            }
            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
            echo "success";	//请不要修改或删除
            Log::debug('Alipay notify', $data->all());
        }   catch (Exception $e) {
            $e->getMessage();
        }
        return $alipay->success()->send();// laravel 框架中请直接 `return $alipay->success()`
    }
}
