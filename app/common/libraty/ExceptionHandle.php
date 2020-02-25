<?php
namespace app\common\library;

use Exception;
use think\exception\Handle;
use app\common\library\Output;
use app\common\exception\System as SystemException;
use Config;

/**
 * 自定义错误显示
 */
class ExceptionHandle extends Handle
{

    public function render(Exception $e)
    {
        // 在生产环境下返回code信息
        if (!Config::get('app.app_debug'))
        {
            $code = SystemException::E_DEFAULT;
            $statuscode = 500;
            $msg = SystemException::getErrorMsg(SystemException::E_DEFAULT);

            // 验证异常
            if ($e instanceof \think\exception\ValidateException)
            {
                $code = SystemException::E_VALIDATE;
                $statuscode = 200;
                $msg = $e->getError();
            }
            // Http异常
            if ($e instanceof \think\exception\HttpException)
            {
                $statuscode = $e->getStatusCode();
            }

            return Output::error($code, $msg, null, Config::get('app.default_return_type'), ['statuscode' => $statuscode]);
        }

        //其它此交由系统处理
        return parent::render($e);
    }
}
