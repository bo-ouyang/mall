<?php
/**
 * 系统输出类
 * @author chat
 * @version 1.0
 */
namespace app\common\library;
use think\facade\Config;
use think\facade\Request;
use think\Exception;
use think\facade\Response;
/*use Config;
use Request;
use Exception;
use Response;*/
use app\common\exception\System as SystemException;

final class Output
{
	/**
	 * 默认数据返回类型
	 * @var null
	 */
	private static $responseType = NULL;

	/**
	 * 设置返回数据类型
	 * @return void
	 */
	public static function setResponseType(string $type): void
	{
		if (is_null(self::$responseType)) {
			self::$responseType = $type;
		}
	}

    /**
     * 获取响应类型
     * @return string
     */
    public static function getResponseType()
    {
        return self::$responseType;
    }

	/**
	 * 对象克隆
	 * @return void
	 */
	private function __clone()
	{
		// 禁止克隆
	}

	/**
     * 操作成功返回的数据
     * @param string $msg   提示信息
     * @param mixed $data   要返回的数据
     * @param int   $code   错误码，默认为1
     * @param string $type  输出类型
     * @param array $header 发送的 Header 信息
     */
    public static function success($data = null, $code = SystemException::E_SUCCESS, $msg = '', $type = null, array $header = []): object
    {
        if ($msg == '') {
            $msg = SystemException::getErrorMsg(SystemException::E_SUCCESS);
        }

        return self::make($msg, $data, $code, $type, $header);
    }

    /**
     * 操作失败返回的数据
     * @param string $msg   提示信息
     * @param mixed $data   要返回的数据
     * @param int   $code   错误码，默认为0
     * @param string $type  输出类型
     * @param array $header 发送的 Header 信息
     */
    public static function error($code = SystemException::E_DEFAULT, $msg = '', $data = null, $type = null, array $header = []): object
    {
        if ($msg == '') {
            $msg = SystemException::getErrorMsg(SystemException::E_DEFAULT);
        }

        return self::make($msg, $data, $code, $type, $header);
    }

    /**
     * 返回封装后的 API 数据到客户端
     * @param mixed  $msg    提示信息
     * @param mixed  $data   要返回的数据
     * @param int    $code   错误码，默认为0
     * @param string $type   输出类型，支持json/xml/jsonp
     * @param array  $header 发送的 Header 信息
     * @return void
     * @throws HttpResponseException
     */
    private static function make($msg, $data = null, $code = SystemException::E_SUCCESS, $type = null, array $header = []): object
    {
        $result = [
            'status' => ($code == SystemException::E_SUCCESS) ? 'success' : 'error',
            'code'   => $code,
            'msg'    => $msg,
            'time'   => Request::server('REQUEST_TIME'),
            'data'   => $data,
        ];

        // 如果未设置类型则自动判断
        $type = $type ? $type : (Request::param(Config::get('var_jsonp_handler')) ? 'jsonp' : self::$responseType);

        if (isset($header['statuscode']))
        {
            $code = $header['statuscode'];
            unset($header['statuscode']);
        }
        else
        {
            //未设置状态码,根据code值判断
            $code = ($code >= 1000 || $code < 200) ? 200 : $code;
        }
		return json($result,$code,$header);
        return Response::create($result, $type, $code)->header($header);
    }
}
