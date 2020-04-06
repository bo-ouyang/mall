<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/4
 * Time: 9:01
 */

namespace app\home\Controller;


use think\Db;

class Consignee extends CheckLogin {
    //收货地址
    public function consignee(){
        //print_r($_POST);
        $consignee = Db::name('user_consignee');
        $user_id = session('uid');
        $address = $consignee->where(array('user_id'=>$user_id))->select();
        if(!empty($address)){
            $count = count($address);
            $this->assign('address',$address);
            $this->assign('count',$count);
        }
        //添加修改
        if(request()->isPost()) {
            $info = request()->post();
                //如果有id 则为修改
                if(!empty($info['id'])){
                    if(!$consignee->where(array('id'=>$_POST['id']))->update($info)) {
                        $this->error('地址修改失败');
                    }
                    $this->redirect('Consignee/consignee');
                }else {
                    $info['user_id'] = $user_id;
                    if(!$consignee->insert($info)) {
                        $this->error('地址添加失败');
                    }
                    $this->redirect('Consignee/consignee');
                }
        }
        return $this->fetch('User/consignee');
    }
    public function consignee_edit(){
        $id = input('id');
        $address = Db::name('user_consignee')->where(['id'=>$id])->find();
        $ret['status'] = 'success';
        $ret['data']   = $address;
        if(!empty($address)){
            $this->assign('address',$address);
        }
        echo json_encode($ret);
        //print_r($assign);
    }
    public function consignee_as_default(){
        $id = input('id');
        $ret['is_default'] = 1;
        $consignee = Db::name('user_consignee')->where(array('id'=>$id))->setField($ret);
        if($consignee){
            $this->redirect('Consignee/consignee');
        }

    }
    public function consignee_del(){
        $id = input('id');
        $id = Db::name('user_consignee')->delete($id);
        if($id){
            $this->redirect('Consignee/consignee');
        }
    }
	public function city()
	{
		$pid = input('pid',0);
		if($pid){
			$ret =db('china')->where(['parentId'=>$pid])->select();
		}else{
			$ret =db('china')->where(['areaType'=>0])->select();
		}
		return json($ret)->header('Access-Control-Allow-Origin', "*");
	}
}
