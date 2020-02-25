<?php
/**
 * Jwt操作类
 * @author bcxgame
 * @version 1.0
 */
namespace app\common\library;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;
use random\Random;
use token\Token;
use think\facade\Config;
use think\facade\Request;

final class Jwt
{
    /**
     * 配置
     * @var null
     */
    private static $_option = null;

    /**
     * 初始化相关配置
     */
    public static function init(): void
    {
        if (is_null(self::$_option)) {
            self::$_option = Config::get('app.jwt');
        }
    }

    /**
     * 获取客户端特征码
     * @return array
     */
    private static function _clientSignature()
    {
        $agent = Request::server('Uuid');
        if (empty($agent)) {
            $agent = Request::server('HTTP_USER_AGENT');
        }

        return 'default';
    }

    /**
     * 创建Token
     * @param $uid 需要保存的用户身份标识
     * @return String
     */
    public static function createToken($uid)
    {
        if (!$uid) {
            return false;
        }

        self::init();
        $nowTime = time();

        // 设置Token
        $token = Random::uuid();
        Token::set($token, $uid * 520, self::$_option['expire']);
        // 加密用户名
        $audience = hash_hmac('sha256', $uid . $token . $nowTime, self::$_option['key']);

        // 获取当前客户端
        // list($agent, $encoding, $language) = self::_clientSignature();
        $client = hash_hmac('ripemd256', self::_clientSignature(), self::$_option['key']);

        $signer = new Sha256();
        $jwtToken = (new Builder())->setIssuer(crc32(SITE_TOP_DOMAIN))
            ->setAudience($audience)
            ->setId($client, true) //自定义标识
            ->setIssuedAt($nowTime) //当前时间
            ->setNotBefore($nowTime) //jwt开始生效时间
            ->setExpiration($nowTime + self::$_option['expire']) //jwt有效期时长
            ->set('uid', $token)
            ->sign($signer, self::$_option['key'])
            ->getToken();

        return $jwtToken->__toString();
    }

    /**
     * 检测Token是否过期与篡改
     * @param  string|object $jwtToken 原始token
     * @return boolean
     */
    public static function validateToken($jwtToken, $validUser = false, $model = '')
    {
        if (!$jwtToken) {
            return false;
        }

        if (!is_string($jwtToken)) {
            $jwtToken = (string)$jwtToken;
        }

        self::init();

        $jwtToken = (new Parser())->parse($jwtToken);
        $signer = new Sha256();
        if (!$jwtToken->verify($signer, self::$_option['key'])) {
            return false;
        }

        // 获取token数据
        $token = $jwtToken->getClaim('uid', false);
        if (!$token) {
            return false;
        }

        $tokenData = Token::get($token);
        if (!$tokenData || empty($tokenData['user_id'])) {
            return false;
        }

        // 获取当前客户端
        $client = hash_hmac('ripemd256', self::_clientSignature(), self::$_option['key']);

        $uid = $tokenData['user_id']/520;
        // 加密用户名
        $audience = hash_hmac('sha256', $uid . $token . $jwtToken->getClaim('iat'), self::$_option['key']);

        $validationData = new ValidationData();
        $validationData->setIssuer(crc32(SITE_TOP_DOMAIN));
        $validationData->setAudience($audience);
        $validationData->setId($client);//自字义标识

        if (!$jwtToken->validate($validationData)) {
            return false;
        }

        if ($validUser && !empty($model) && empty($model::get($uid))) {
            return false;
        }

        return $token;
    }

    /**
     * 获取jwt列名
     */
    public static function getColumn($jwtToken = '', $key = '', $type = 'claim')
    {
        if (!$jwtToken) {
            $jwtToken = Request::header('Authorization');
        }

        $token = (new Parser())->parse((string)$jwtToken);
        if (!$key) {
            return ($type == 'claim') ? $token->getClaims() : $token->getHeaders();
        }

        return ($type == 'claim') ? $token->getClaim($key, false) : $token->getHeader($key, false);
    }
}
