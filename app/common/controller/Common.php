<?php
/**
 * 顶级公共控制器
 * @version 1.0
 */
namespace app\common\controller;
use think\exception\HttpResponseException;
use think\Controller;
use think\facade\Config;;
use think\facade\Response;
use think\facade\Session;
use think\facade\Lang;
/*use Config;
use Response;
use Session;
use Lang;*/
use app\common\exception\System as SystemException;
use app\common\library\Output;
use think\exception\ValidateException;
use think\Validate;

class Common extends Controller
{
	/**
     * 默认响应输出类型,支持json/xml
     * @var string
     */
    protected static $responseType = NULL;

    /**
     * 同一个接口最小间隔访问时间[秒]
     * @var integer
     */
    protected $interval = 1;

    /**
     * 同一个接口1秒钟之内请求次数
     * @var integer
     */
    protected $requestQty = 4;

    /**
     * 需要限制访问频率的模块
     */
    protected $limitModule = ['index', 'admin'];

    /**
     * 公共方法初始化
     */
	protected function initialize()
	{
		//return true;
		// 防止多次调用
		static $commonInit = false;
		if ($commonInit) {
			return;
		}

        // 返回值类型
        Output::setResponseType(Config::get('default_return_type'));

        // 禁用options请求
        $this->_denyRequest();

        // 应用程序状态
        $this->_systemValid();

        // 访问来源控制[线上环境]
        $this->_refererLimit();

        // 限制访问频率[index,admin]
        $this->_accessLimit();

        // 接口安全验证
         //$this->_apiSecert();

        // 防止session劫持
         $this->_filterSession();

        // 多语言设置
        if (Config::get('app.lang_switch_on')) {
            $this->_setLanguage();
        }

		// 线上https跳转
		$sslFlag = $this->request->isSsl();
		$isAjax  = $this->request->isAjax();

		if (Config::get('is_https') && !$sslFlag && !$isAjax) {
			header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
            exit();
		}

        // 设置版权
        header('X-Powered-By: ' . SITE_TOP_DOMAIN);
        header_remove('Server');
        $commonInit = true;
	}

    /**
     * 禁用请求
     */
    private function _denyRequest(): void
    {
        if (!in_array(strtoupper($this->request->method()), ['GET', 'POST'])) {
            $response = Output::error(SystemException::E_METHOD, SystemException::$messageList[SystemException::E_METHOD]);
            throw new HttpResponseException($response);
        }
    }

    /**
     * 访问来源限制
     */
    private function _refererLimit(): void
    {
        if (SITE_ENVIROMENT == 'productive') {
            // 访问来源控制
            $referer = $this->request->server('HTTP_REFERER');
            if (!empty($referer) && strpos($referer, '.' . SITE_TOP_DOMAIN) === false) {
                $response = Output::error(SystemException::E_REFERER, SystemException::$messageList[SystemException::E_REFERER]);
                throw new HttpResponseException($response);
            }
        }
    }

    /**
     * 限制前台访问频率
     */
    private function _accessLimit(): void
    {
        $modulename = strtolower($this->request->module());
        if (in_array($modulename, $this->limitModule)) {
            $controllername = strtolower($this->request->controller());
            $actionname = strtolower($this->request->action());
            $sessKey = 'user_access';
            $access = Session::get($sessKey);

            $nowTime = $this->request->server('REQUEST_TIME');
            if ($access) {
                if(($nowTime - $access['time'] < $this->interval) && ($access['uri'] == $controllername . '/' . $actionname) && $access['qty'] > $this->requestQty) {
                    Session::set($sessKey, ['time' => $nowTime, 'uri' => $controllername . '/' . $actionname, 'qty' => $access['qty']+1]);

                    $response = Output::error(SystemException::E_FREQUENT, SystemException::$messageList[SystemException::E_FREQUENT]);
                    throw new HttpResponseException($response);
                } else {
                    Session::delete($sessKey);
                }
            } else {
                Session::set($sessKey, ['time' => $nowTime, 'uri' => $controllername . '/' . $actionname, 'qty' => 1]);
            }
        }
    }

    /**
     * 防止劫持Session
     */
    private function _filterSession(): void
    {
        $browserTagKey   = md5('browserTag');
        $browserTagValue = md5($this->request->server('Uuid'));

        $browserTag = Session::get($browserTagKey);
        if (empty($browserTag)) {
            Session::set($browserTagKey, $browserTagValue);
            $browserTag = $browserTagValue;
        }

        // session攻击
        if ($browserTagValue != $browserTag) {
            Session::clear();
            Session::destroy();

            $response = Output::error(SystemException::E_SESSION, SystemException::$messageList[SystemException::E_SESSION]);
            throw new HttpResponseException($response);
        }
    }

    /**
     * 系统状态
     */
    private function _systemValid(): void
    {
        if (!Config::get('app.system_status')) {
            $response = Output::error(SystemException::E_CLOSE, SystemException::$messageList[SystemException::E_CLOSE]);
            throw new HttpResponseException($response);
        }
    }

    /**
     * 404页面
     */
    public function _empty($name = '')
    {
        if ($this->request->isAjax()) {
            return Output::error(404, '请求不存在');
        } else {
            // 404页面
            $port = $this->request->isSsl() ? 'https://' : 'http://';
            $this->redirect($port . $this->request->host() . '/404.html', 302);
        }
    }

    /**
     * 公用验证方法
     * @return void
     */
    protected function validateData(Validate $validate, $scene, $data)
    {
        if (!is_object($validate) || !$scene || !$data) {
            return false;
        }

        if (!$validate->scene($scene)->check($data)) {
            $response = Output::error(SystemException::E_VALIDATE, $validate->getError());
            throw new HttpResponseException($response);
        }

        return true;
    }

    /**
     * 设置多语言
     */
    private function _setLanguage()
    {
        $modelConf = Config::get('app.model_status');
        $langList = model('common/Language')->getLanguageList($modelConf['enable']);

        $systemLang = [];
        foreach ($langList as $info) {
            $systemLang[] = $info['code'];
        }

        $systemLang = !empty($systemLang) ? $systemLang : ['zh-cn', 'en-us'];
        // 设置允许的语言
        Lang::setAllowLangList($systemLang);
    }
}
