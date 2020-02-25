<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/21
 * Time: 15:58
 */
namespace app\admin_app\service;
use think\facade\Request;

class Goods {
        /*protected $_validate=array(
            array(),
            array(),
            array(),
            array(),
            array(),
            array(),
            array(),
        );*/
        public function getList($where='',$order='',$p=1){
            $fields = 'goods_id, goods_name, goods_sn, now_price, stock_qty, created_date, new, recommend, bargain, status';
            $pagesize = 10;
            if($where==''){
                $count = $this->count();
                $data  =$this->field($fields)->order($order)->select();
            }elseif($order==''){
                $count = $this->where($where)->count();
                $data  =$this->field($fields)->where($where)->select();
            }else {
                $order = 'goods_id ';
            }
            $totle_page = ceil($count/$pagesize);
            $totle_page = $totle_page>1?$totle_page:1;
            for ($i=1;$i<=$totle_page;$i++){$all_page[] = $i;}
            $current_page = $p>1?$p:1;
            $pre_page = $current_page-1>0?$current_page-1:1;
            $next_page= $current_page<$totle_page?$current_page+1:$current_page;
            $offset = $pagesize*($current_page-1)>0?$pagesize*($current_page-1):0;
            $data = $this->field($fields)->where($where)->order($order)->page($current_page,$pagesize)->select();
            return array(
                'page' =>array(
                    'total_count'=>$count,
                    'page_size'=>$pagesize,
                    'total_page'=>$totle_page,
                    'first_page'=>1,
                    'prev_page'=>$pre_page,
                    'next_page'=>$next_page,
                    'all_pages'=>$all_page,
                    'current_page'=>$current_page,
                    'last_page'=>$totle_page,
                    'limit'=>$pagesize,
                    'offset'=>$offset,
                    'scope' =>10,
                ),
                'data' => $data,
            );
        }
        //fenlei
        public function getCate(){
            $data = $this->distinct('true')->alias('g')->join('__GOODS_CATE__ c ON c.cate_id= g.cate_id')->
            field('c.cate_id,c.cate_name,c.parent_id')->select();
            return $data;
        }
        //品牌
        public function getBrand(){
            static $data=null;
            if(!$data) $data = $this->distinct('true')->alias('g')->join('__BRAND__  b  ON b.brand_id= g.brand_id')->
            field('b.brand_id,b.brand_name')->select();
            //print_r($data);
            return $data;
        }
        public function getCateTrees(){
            return getTree($this->getCate());
        }
        //图片上传
        public function uploadfile(){
            // 获取表单上传文件
            $files = request()->file('file');

                //缓存文件夹
                $fileTemp = 'static/Uploads/temp/';
                file_exists($fileTemp) or mkdir($fileTemp);
                // 移动到框架应用根目录/uploads/ 目录下
                $info = $files->rule('uniqid')->move( 'static/Uploads/temp/');
                $flag='success';
                if(!$info){
                    // 成功上传后 获取上传信息
                   // echo $info->getSaveName();
                  //  echo $info->getFilename();
                    $flag = 'FAIL';
                   // echo $files->getError();
                }else{
                    // 上传失败获取错误信息

                }
            return array(
                'error'=>$flag,
                'ext'=> $info->getExtension(),
                'name'=>$info->getSaveName(),
                'path'=>$fileTemp.$info->getSaveName(),
                'url'=>'http://'.Request::host().'/'.$fileTemp.$info->getSaveName(),
                'size'=>$info->getSize()
            );
        }
        public function resize($filename){
            $file['temp'] = 'static/Uploads/temp/';
            //缩略图上传路径
            $file['ret']='static/Uploads/';
            $file['path_600'] = $file['ret'].'600/';
            $file['path_350'] = $file['ret'].'350/';
            $file['path_150'] = $file['ret'].'150/';
            $file['path_50']  = $file['ret'].'50/';
            //保存目录
            file_exists($file['path_600']) or mkdir($file['path_600'],0777,true);
            file_exists($file['path_350']) or mkdir($file['path_350'],0777,true);
            file_exists($file['path_150']) or mkdir($file['path_150'],0777,true);
            file_exists($file['path_50'])  or mkdir($file['path_50'],0777,true);
            //图片缩放


            $image =  \think\Image::open($file['temp'].$filename);
            //echo $temp_name;die();
            $image->thumb(600,600,2)->save($file['path_600'].$filename);

            $image->thumb(350,350,2)->save($file['path_350'].$filename);

            $image->thumb(150,150,2)->save($file['path_150'].$filename);
            $image->thumb(50,50,2)->save($file['path_50'].$filename);
        }
        //删除图片
        public function delThumb($filename){

                $path = 'static/Uploads/temp/'.$filename;
                file_exists($path) && unlink($path);
                file_exists($path) && unlink($path);
                file_exists($path) && unlink($path);
                file_exists($path) && unlink($path);
                file_exists($path) &&  unlink($path);
        }
        public function _before_insert(){
            $this->data['created_date'] = time();
        }
}
