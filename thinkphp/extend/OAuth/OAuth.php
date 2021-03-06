<?php

/**
 * 第三方登陆实例抽象类
 *
 * @author Coeus <r.anerg@gmail.com>
 */

namespace OAuth;
use think\Request;
use think\Cookie;
abstract class OAuth {

    /**
     * 第三方配置属性
     * @var type String
     */
    protected $config = '';

    /**
     * 获取到的第三方Access Token
     * @var type Array
     */
    protected $accessToken = null;

    /**
     * 请求授权页面展现形式
     * @var type String
     */
    protected $display = 'default';

    /**
     * 获取到的Token信息
     * @var type Array
     */
    protected $token;

    /**
     * 接口渠道
     * @var type String
     */
    private $channel   = '';

    /**
     * 当前时间戳
     * @var type String
     */
    protected $timestamp = '';

    private function __construct($config = null) {
        if (empty($config) || !array_key_exists('app_key', $config) || !array_key_exists('app_secret', $config) || !array_key_exists('callback', $config) || !array_key_exists('scope', $config)) {
            exception('请配置申请的APP_KEY和APP_SECRET');
        }
        $class           = get_class($this);
        $cls_arr         = explode('\\', $class);
        $this->channel   = strtoupper(end($cls_arr));
        $_config         = array('response_type' => 'code', 'grant_type' => 'authorization_code');
        $this->config    = array_merge($config, $_config);
        $this->timestamp = time();
    }

    /**
     * 设置授权页面样式，PC或者Mobile
     * @param type $display
     */
    public function setDisplay($display) {
        if (in_array($display, array('default', 'mobile'))) {
            $this->display = $display;
        }
    }

    /**
     * 获取第三方OAuth登陆实例
     */
    static function getInstance($config, $type = '') {
        static $_instance = array();

        $type = strtolower($type);
        if (!isset($_instance[$type])) {
            $class            = '\\OAuth\\Driver\\' . $type;
            $_instance[$type] = new $class($config);
        }
        return $_instance[$type];
    }

    /**
     * 初始化一些特殊配置
     */
    protected function initConfig() {
        $this->config['callback'] = $this->config['callback'][$this->display];
    }

    /**
     * 合并默认参数和额外参数
     * @param array $params  默认参数
     * @param array/string $param 额外参数
     * @return array:
     */
    protected function param($params, $param) {
        if (is_string($param)) {
            parse_str($param, $param);
        }
        return array_merge($params, $param);
    }

    /**
     * 默认的AccessToken请求参数
     * @return type
     */
    protected function _params() {
        $params = array(
            'client_id'     => $this->config['app_key'],
            'client_secret' => $this->config['app_secret'],
            'grant_type'    => $this->config['grant_type'],
            'code'          => $_GET['code'],
            'redirect_uri'  => $this->config['callback'],
        );
        return $params;
    }

    /**
     * 获取指定API请求的URL
     * @param  string $api API名称
     * @param  string $fix api后缀
     * @return string      请求的完整URL
     */
    protected function url($api, $fix = '') {
        return $this->ApiBase . $api . $fix;
    }
	protected function http($url, $params, $method = 'GET', $header = array(), $multi = false){
		$opts = array(
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_HTTPHEADER     => $header
		);

		/* 根据请求类型设置特定参数 */
		switch(strtoupper($method)){
			case 'GET':
				$opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
				break;
			case 'POST':
				//判断是否传输文件
				$params = $multi ? $params : http_build_query($params);
				$opts[CURLOPT_URL] = $url;
				$opts[CURLOPT_POST] = 1;
				$opts[CURLOPT_POSTFIELDS] = $params;
				break;
			default:
				throw new Exception('不支持的请求方式！');
		}
		
		/* 初始化并执行curl请求 */
		$ch = curl_init();
		curl_setopt_array($ch, $opts);
		$data  = curl_exec($ch);
		$error = curl_error($ch);
		curl_close($ch);
		if($error) throw new Exception('请求发生错误：' . $error);
		return  $data;
	}
		

    /**
     * 获取access_token
     */
    public function getAccessToken($ignore_stat = false) {
        if ($ignore_stat === false && isset($_COOKIE['A_S']) && $_GET['state'] != $_COOKIE['A_S']) {
            exception('传递的STATE参数不匹配！');
        } else {
            $this->initConfig();
            $params      = $this->_params();
            $data        = $this->http($this->AccessTokenURL, $params,'POST');
            $this->token = $this->parseToken($data);
            setcookie('A_S', $this->timestamp, $this->timestamp - 600, '/');
            return $this->token;
        }
    }

    /**
     * 抽象方法
     * 得到请求code的地址
     */
    abstract public function getAuthorizeURL();

    /**
     * 抽象方法
     * 组装接口调用参数 并调用接口
     */
    abstract protected function call($api, $param = '', $method = 'GET');

    /**
     * 抽象方法
     * 解析access_token方法请求后的返回值
     */
    abstract protected function parseToken($result);

    /**
     * 抽象方法
     * 获取当前授权用户的SNS标识
     */
    abstract public function openid();
}
