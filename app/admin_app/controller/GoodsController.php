<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/22
 * Time: 12:30
 */

namespace app\admin_app\Controller;

use app\common\model\GoodsModel as GoodsModel;
use app\common\model\BrandModel;
use app\common\model\GoodsAlbumModel;
use app\common\model\GoodsCateModel;
use app\common\model\GoodsOptionalModel;
use app\common\model\GoodsOptionalTypeModel;
use cmf\controller\AdminBaseController;
use think\facade\Request;

class GoodsController extends AdminBaseController
{
    public function index()
    {
        $page = input('page', 1);
        return $this->fetch();
    }

    public function index_data()
    {
        $GCate = model('common/GoodsCate');
        $data['cates'] = getTree($GCate->field('cate_id,cate_name,parent_id')->select());
        $data['brand'] = model('common/Brand')->field('brand_id,brand_name')->select();
        $goods = GoodsModel::select()->withAttr('cate_id', function ($value, $data) {
            return GoodsCateModel::where(['cate_id' => $value])->value('cate_name');
        })->withAttr('brand_id', function ($value, $data) {
            return BrandModel::where(['brand_id' => $value])->value('brand_name');
        });
        return json(['rows' => $goods, 'total' => count($goods)]);
    }

    //商品添加
    public function goods_add()
    {
        $data['option'] = GoodsOptionalTypeModel::select();
        $data['brand'] = BrandModel::field('brand_id,brand_name')->select();
        $data['cates'] = getTree(GoodsCateModel::field('cate_id,cate_name,parent_id')->select());
        $this->assign('data', $data);
        return $this->fetch();
    }

    public function addPost()
    {
        if (Request::isPost()) {

            $data = Request::post();
            $album = input('album');
            $goods_opts = input('goods_opts', '');
            $goods = GoodsModel::create($data);
            //添加图片
            if (!empty($album)) {
                foreach ($album as $v) {
                    if (!GoodsAlbumModel::insert(array('goods_id' => $goods->goods_id, 'image' => $v))) {
                        $this->error('图片插入失败');
                    }
                    $v = str_replace('\\', '/', $v);
                    service('admin_app/Goods')->resize($v);
                }
            }
            //添加商品类型
            if (is_array($goods_opts)) {
                $text = $goods_opts['text'];
                $price = $goods_opts['price'];
                $type = $goods_opts['type'];
                for ($i = 0; $i < count($type); $i++) {
                    $opts['type_id'] = $type[$i];
                    $opts['opt_text'] = $text[$i];
                    $opts['opt_price'] = $price[$i];
                    $opts['goods_id'] = $goods->goods_id;
                    GoodsOptionalModel::insert($opts);
                }
            }
            $this->success('商品添加成功', url('Goods/index'));
        }
    }

    //商品编辑
    public function goods_edit()
    {
        $goods_id = input('id');
        $type_id = GoodsOptionalModel::distinct(true)->field('type_id')->where(array('goods_id' => $goods_id))->select();
        $type_info = [];
        foreach ($type_id as $k => $v) {
            $type_info[$k]['type_info'] = GoodsOptionalModel::where(array('goods_id' => $goods_id, 'type_id' => $v['type_id']))->select();
            $type_info[$k]['type_id'] = $v['type_id'];
            $type_info[$k]['type_name'] = GoodsOptionalTypeModel::where(array('type_id' => $v['type_id']))->value('name');
        }
        $data['type_info'] = $type_info;
        $data['goods'] = GoodsModel::find($goods_id);
        $data['cates'] = getTree(GoodsCateModel::field('cate_id,cate_name,parent_id')->select());
        $data['brand'] = BrandModel::field('brand_id,brand_name')->select();
        $data['option'] = GoodsOptionalTypeModel::select();
        $data['goods_album'] = GoodsAlbumModel::where('goods_id', $goods_id)->select();
        $this->assign('data', $data);
        return $this->fetch();
    }

    public function goodsEditPost()
    {
        if (Request::isPost()) {
            $data = Request::post();
            $goods_id = input('id');
            $album = input('album');
            if($album){
                array_push($album,$data['goods_image']);
            }
            $goods_opts = input('goods_opts', '');
            $data['goods_image'] = str_replace('\\', '/', $data['goods_image']);
            //如果有图片添加,删除原来的图片
            $imgs = GoodsAlbumModel::where(['goods_id' => $goods_id])->select();
            //删除原来的图片
           /*if(!empty($imgs)){
               foreach ($imgs as $v) {
                   service('admin_app/Goods')->delThumb($v['image']);
               }
           }*/
            //删除数据库中的信息
            GoodsAlbumModel::where(['goods_id' => $goods_id])->delete();
            GoodsModel::where(['goods_id' => $goods_id])->update(['goods_image' => '']);
            if($album){
                //把图片添加到图片库
                foreach ($album as $v) {
                    if (!GoodsAlbumModel::insert(['image' => $v, 'goods_id' => $goods_id])) {
                        $this->error('图片插入失败');
                    }
                    $v = str_replace('\\', '/', $v);
                    service('admin_app/Goods')->resize($v);
                }
            }


            //添加商品类型
            if (is_array($goods_opts)) {
                //删除原来的类型
                $types = GoodsOptionalModel::where(['goods_id' => $goods_id])->select();
                if (!empty($types)) {
                    GoodsOptionalModel::where(['goods_id' => $goods_id])->delete();
                }
                //添加新类型
                $text = $goods_opts['text'];
                $price = $goods_opts['price'];
                $type = $goods_opts['type'];
                $max = max(max(count($text), count($price)), count($type));
                for ($i = 0; $i < $max; $i++) {
                    $opts['type_id'] = $type[$i];
                    $opts['opt_text'] = $text[$i];
                    $opts['opt_price'] = $price[$i];
                    $opts['goods_id'] = $goods_id;
                    GoodsOptionalModel::insert($opts);
                }
            }
            $goodsModel = new GoodsModel();
            $ret = $goodsModel->allowField(true)->save($data, ['goods_id' => $goods_id]);
            if ($ret) {
                $this->success('修改成功', url('Goods/index'));
            }
            $this->error('修改失败');
        }
    }

    //关联商品
    public function goods_related()
    {

        return $this->display();
    }


    public function uploadGoodsImage()
    {
        $info = service('admin_app/Goods')->uploadfile();
        return json($info);
    }

    public function upload()
    {

    }

    public function changeStatus()
    {
        $post = input();
        GoodsModel::where(['goods_id' => $post['id'],])->setField([$post['field'] => $post['on']]);
        dump($post);
    }
}
