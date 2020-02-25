<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/3
 * Time: 9:45
 */

namespace app\common\model;
use think\Db;
use think\Model;

class UserModel extends Model {
	protected $pk = 'id';

	public function userprofile(){
		return $this->hasOne('UserProfileModel','user_id','id');
	}




    public function getInfo($user_id){
        $nickname = Db::name('user_profile')->where(array('user_id'=>$user_id))->value('nickname');
        $info = $this->field('username,avatar')->where(array('user_id'=>$user_id))->find();
        $info['name'] = $nickname;
        $info['user_id']=$user_id;
        return array(
            'info'=>$info,
            'status'=>1,
        );
    }
    public function getUnique(){
        return md5(uniqid(microtime(true),true));
    }

    /*public function img_upload(){
        $tem = './Public/Uploads/User/temp/';
        file_exists($tem) or mkdir($tem);
        $config = array(
            'maxSize'    =>    3145728,
            'rootPath'   =>    $tem,
            'exts'       =>    array('jpg', 'gif', 'png', 'jpeg'),
            'subName'    =>    session('username'),
        );
        $upload = new \Think\Upload($config);// 实例化上传类
        $info   =   $upload->uploadOne($_FILES['avatar_file']);
        if(!$info) {// 上传错误提示错误信息
            $this->error('图片上传失败');
        }
        // 上传成功 获取上传文件信息
        //$path = $info['savepath'].$info['savename'];
        //echo $path;
        $path = $tem.session('username').'/'.$info['savename'];
        //echo $path;
        $image = new \Think\Image();
        $image->open($path);
        $image->thumb(300, 300)->save($path);
        return $path;
    }
    public function img_crop($w,$h,$x,$y){
        $username = session('username');
        $crop_src = './Public/Uploads/User/crop/';
        $file = './Public/Uploads/User/temp/'.$username.'/'.I('post.streams');
        file_exists($crop_src) or mkdir($crop_src);
        file_exists($file) or mkdir($file);
        //echo $file;
        $image = new \Think\Image();
        $image->open($file);
        $crop_src = './Public/Uploads/User/crop/'.I('post.streams');
        $image->crop($w,$h,$x,$y)->save($crop_src);
    }*/

}
