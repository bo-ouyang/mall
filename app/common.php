<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use think\Db;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/21
 * Time: 18:25
 */
//获取分类列表
function getTree($arr, $pid = 0, $lev = 0) {
	$data = array();
    //print_r($arr);
	foreach ($arr as $v) {
		if ($v['parent_id'] == $pid) {
			$v['lev'] = $lev;
			$data[] = $v;

			$data = array_merge($data, getTree($arr, $v['cate_id'], $lev + 1));
		}
	}
	return $data;
}

//根据id获取所有子类id
function getCate_ChildId($data, &$ret, $id = 0) {
	$ret[] = (int)$id;
	foreach ($data as $v) {
		if ($v['parent_id'] == $id) {
			$ret[] = (int)$v['cate_id'];
			getCate_ChildId($data, $ret, $v['cate_id']);
		}
	}
	return $ret;
}

//按照父子关系转换为多维数组
function cate_tree($data, $pid = 0, $lev = 0) {
	$temp = array();
	foreach ($data as $v) $temp[$v['cate_id']] = $v;//取出所有分类
	//print_r($temp);
	foreach ($temp as $v) {
		if (isset($temp[$v['parent_id']])) {//如果有父级分类,就重新分配给父级分类的子类
			$temp[$v['parent_id']]['child'][] =& $temp[$v['cate_id']];
		} else {
			$ret[] = &$temp[$v['cate_id']];
		}
	}
	return $ret;
}

//分类家朴树
function cate_family($data, $id) {
	$ret = cate_parent($data, $id);
	foreach (array_reverse($ret['pids']) as $v) {
		foreach ($data as $vv) {
			($vv['parent_id'] == $v) && $ret['parent'][$v][] = $v;
		}
	}
	return $ret;
}

//根据任意分类id查找父类
function cate_parent($data, $id = 0) {
	$ret = array('pact' => array(), 'pids' => array($id));
	for ($i = 0; $id && $i < 10; ++$i) {
		foreach ($data as $v) {
			if ($v['cate_id'] == $id) {
				$ret['pact'][] = $v;
				$ret['pids'][] = $id = $v['parent_id'];
			}
		}
	}
	return $ret;
}

function mkUrl($param, $value = null) {
	//拿到上一个链接的所有get
	$get = $_GET;
	//print_r($_GET);
	if ($param == 'brand_id' && $value == null) {
		unset($get['brand_id']);
	}
	if ($param == 'price' && $value == null) {
		unset($get['price']);
	}
	if ($param == 'attr') {
		//属性筛选
		$temp = array();
		if (isset($get['attr_id']) && isset($get['value'])) {//如果前一次有属性值
			//前一次的参数
			$aids = explode('_', $get['attr_id']);//所有属性id  1 6 7
			$vas = explode('_', $get['value']);//所有属性值
			//print_r($vas);
			//根据键值重组数组
			foreach ($vas as $k => $v) {
				$temp[$aids[$k]] = $v;
			}
			/*//不设置属性
			if($value['value']==-1){
				unset($temp[$value['attr_id']]);
			}*/
			if (in_array($value['attr_id'], $aids)) {//如果原来的参数中有传过来的attr_id,则替换属性值
				//交换属性值
				$get['attr_id'] = implode('_', $aids);//1 6 7
				$temp[$value['attr_id']] = $value['value'];
				$get['value'] = implode('_', $temp);
			} else {
				$get['attr_id'] = $value['attr_id'] . '_' . $_GET['attr_id'];
				$get['value'] = $value['value'] . '_' . $_GET['value'];
			}
		} else {

			$get['attr_id'] = $value['attr_id'];
			$get['value'] = $value['value'];
		}
	} else {
		$get[$param] = $value;
	}
	unset($get['attr']);
	return url('Index/cate', $get);
}

//
//获取唯一字符串
function getUnique() {
	return md5(uniqid(microtime(true), true));
}

//获取文件后缀
function getExt($file) {
	return substr(strrchr($file, '.'), 1);
}

/**
 * 请求接口返回内容
 * @param string $url [请求的URL地址]
 * @param string $params [请求的参数]
 * @param int $ipost [是否采用POST形式]
 * @return  string
 */
function juhecurl($url, $params = false, $ispost = 0) {
	$httpInfo = array();
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22');
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	if ($ispost) {
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_URL, $url);
	} else {
		if ($params) {
			curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
		} else {
			curl_setopt($ch, CURLOPT_URL, $url);
		}
	}
	$response = curl_exec($ch);
	if ($response === FALSE) {
		//echo "cURL Error: " . curl_error($ch);
		return false;
	}
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$httpInfo = array_merge($httpInfo, curl_getinfo($ch));
	curl_close($ch);
	return $response;
}

/**
 * 实例化服务类
 * service('common/Order'); 实例化common模块的Order服务类
 * @param string $name 格式 [模块名]/接口名
 */
function service($name, $layer = '') {
	if (!$name) {
	    //echo 111;
		return false;
	}

	static $_service = [];

	$array = explode('/', $name);
	$classname = array_pop($array);
	$module = $array ? array_pop($array) : 'common'; // 默认是common分组下的service
	$class = '\\app\\' . $module . '\\service\\' . $classname . ucfirst($layer);
   // echo $class;
	if (isset($_service[$class]) && is_object($_service[$class])) {
		return $_service[$class];
	}

	if (class_exists($class)) {

		$_service[$class] = new $class();
		//dump($_service[$class]);
		return $_service[$class];
	}
   // echo 11122;
	return false;
}

if (!function_exists('model')) {
	/**
	 * 实例化Model
	 * @param string $name Model名称
	 * @param string $layer 业务层名称
	 * @param bool $appendSuffix 是否添加类名后缀
	 * @return \think\Model
	 */
	function model($name = '', $layer = 'model', $appendSuffix = false) {
		return app()->model($name, $layer, $appendSuffix);
	}
}




