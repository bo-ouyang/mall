<?php


namespace app\common\exception;


class System extends Base {
	const E_SUCCESS  = '10000';
	const E_DEFAULT  = '10001';
	const E_MAINTAIN = '10002';
	const E_VALIDATE = '10003';
	const E_METHOD   = '10004';
	const E_REFERER  = '10005';
	const E_FREQUENT = '10006';
	const E_SESSION  = '10007';
	const E_CLOSE    = '10008';
	const E_FAIL     = '10009';
	public static $messageList = [
		self::E_SUCCESS  => 'success',
		self::E_DEFAULT  => 'system error, please contact the administrator',
		self::E_MAINTAIN => '系统维护',
		self::E_VALIDATE => '数据验证不通过',
		self::E_METHOD   => '请求方法异常',
		self::E_REFERER  => '非法来源',
		self::E_FREQUENT => '访问太频繁',
		self::E_SESSION  => 'session异常',
		self::E_CLOSE    => '系统关闭',
		self::E_FAIL     => '失败',
	];
}
