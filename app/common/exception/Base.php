<?php
/**
 * 自定义异常基类
 * @author bcxgame
 * @version 1.0
 */
namespace app\common\exception;
use think\Exception;
use think\facade\Config;
use think\facade\Lang;

abstract class Base extends Exception
{
    /**
     * 初始化
     */
    public function __construct($message, $code)
    {
        $this->message = $message;
        $this->code    = $code;

        parent::__construct($message, $code);
    }

    /**
     * 获取异常描述
     * @param $errorCode
     * @param string $message
     * @param bool $cover
     * @return array|mixed|string
     */
    public static function getErrorMsg($errorCode, $message = '', $cover = false)
    {
        $originalMessage = '';
        if (Config::get('app.lang_switch_on')) {
            $originalMessage = Lang::get(static::$messageList[$errorCode]);
        } else {
            $originalMessage = static::$messageList[$errorCode];
        }

        if ($cover) {
            return $message;  // 原始返回
        } else {
            // 获取自定义异常信息,判断是否有自定义msg
            if ($message) {
                if (is_array($message)) {
                    array_unshift($message, $originalMessage);
                    return call_user_func_array('sprintf', $message);
                } else {
                    $message = sprintf($originalMessage, $message);
                }
            } else {
                $message = $originalMessage;
            }

            return $message;
        }
    }
}
