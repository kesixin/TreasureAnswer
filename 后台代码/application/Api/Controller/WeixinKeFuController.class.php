<?php

/**
 * WeixinKeFuController 获取微信接口信息控制器
 * author hhz 2017.10.12
 */
namespace Api\Controller;

use Think\Controller;
use Common\Controller\WeixinController;

class WeixinKeFuController extends Controller {
	protected static $_instance = null;
	private $token = 'fHYSM6RaNJA4e784';
	// 方法静态化
	public function instance() {
		if (! self::$_instance) {
			self::$_instance = new self ();
		}
		return self::$_instance;
	}
	public function check_server() { // 校验服务器地址URL
		if (isset ( $_GET ['echostr'] )) {
			$this->valid ();
		} else {
			$this->responseMsg ();
		}
	}
	public function valid() {
		$echoStr = $_GET ["echostr"];
		if ($this->checkSignature ()) {
			header ( 'content-type:text' );
			echo $echoStr;
			exit ();
		} else {
			echo $echoStr . '+++' . $this->token;
			exit ();
		}
	}
	private function checkSignature() {
		$signature = $_GET ["signature"];
		$timestamp = $_GET ["timestamp"];
		$nonce = $_GET ["nonce"];
		
		$token = $this->token;
		$tmpArr = array (
				$token,
				$timestamp,
				$nonce 
		);
		sort ( $tmpArr, SORT_STRING );
		$tmpStr = implode ( $tmpArr );
		$tmpStr = sha1 ( $tmpStr );
		
		if ($tmpStr == $signature) {
			return true;
		} else {
			return false;
		}
	}
	public function responseMsg() {
		$postStr = file_get_contents("php://input");
		//file_put_contents('kufu.txt', $postStr);
		if (! empty ( $postStr ) && is_string ( $postStr )) {
			// 禁止引用外部xml实体
			// libxml_disable_entity_loader(true);
			
			// $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$postArr = json_decode ( $postStr, true );
			if (! empty ( $postArr ['MsgType'] ) && $postArr ['MsgType'] == 'text') { // 文本消息
			    /*
				$fromUsername = $postArr ['FromUserName']; // 发送者openid
				$toUserName = $postArr ['ToUserName']; // 小程序id
				$textTpl = array (
						"ToUserName" => $fromUsername,
						"FromUserName" => $toUserName,
						"CreateTime" => time (),
						"MsgType" => "transfer_customer_service" 
				);
				exit ( json_encode ( $textTpl ) );
				*/
			    //$fromUsername = $postArr ['FromUserName']; // 发送者openid
			    $toUserName = $postArr ['ToUserName']; // 小程序id
			    $fromUsername = $postArr ['FromUserName']; // 发送者openid
			    $linkCont = S('linkCont');
			    if (empty($linkCont)) {
			        $customerOptions = M('Options')->where("option_name='customer_options'")->find();
			        $linkCont= json_decode($customerOptions['option_value'], true);
			        S('linkCont', $linkCont);
			    }
			    $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
			    $content = '您好，有什么能帮助你?';
			    $data = array (
			        "touser" => $fromUsername,
			        "msgtype" => "link",
			        "link" => array (
			            "title" => $linkCont['title'],
			            "description" => $linkCont['description'],
			            "url" => $linkCont['url'],
			            "thumb_url" => $http_type.$_SERVER["HTTP_HOST"].C('TMPL_PARSE_STRING.__UPLOAD__').$linkCont['thumb_url']
			        )
			    );
			    $json = json_encode ( $data, JSON_UNESCAPED_UNICODE ); // php5.4+
			    
			    $access_token = $this->get_accessToken ();
			    /*
			     * POST发送https请求客服接口api
			     */
			    $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $access_token;
			    // 以'json'格式发送post的https请求
			    $this->post($url, $json);
			    exit();
			} elseif (! empty ( $postArr ['MsgType'] ) && $postArr ['MsgType'] == 'image') { // 图文消息
				$fromUsername = $postArr ['FromUserName']; // 发送者openid
				$toUserName = $postArr ['ToUserName']; // 小程序id
				$textTpl = array (
						"ToUserName" => $fromUsername,
						"FromUserName" => $toUserName,
						"CreateTime" => time (),
						"MsgType" => "transfer_customer_service" 
				);
				exit ( json_encode ( $textTpl ) );
			} elseif ($postArr ['MsgType'] == 'event' && $postArr ['Event'] == 'user_enter_tempsession') { // 进入客服动作
				$fromUsername = $postArr ['FromUserName']; // 发送者openid
				$linkCont = S('linkCont');
				if (empty($linkCont)) {
					$customerOptions = M('Options')->where("option_name='customer_options'")->find();
					$linkCont= json_decode($customerOptions['option_value'], true);
					S('linkCont', $linkCont);
				}
				$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
				$content = '您好，有什么能帮助你?';
				$data = array (
						"touser" => $fromUsername,
						"msgtype" => "link",
						"link" => array (
								"title" => $linkCont['title'], 
								"description" => $linkCont['description'],
								"url" => $linkCont['url'],
								"thumb_url" => $http_type.$_SERVER["HTTP_HOST"].C('TMPL_PARSE_STRING.__UPLOAD__').$linkCont['thumb_url']
						) 
				);
				$json = json_encode ( $data, JSON_UNESCAPED_UNICODE ); // php5.4+
				
				$access_token = $this->get_accessToken ();
				/*
				 * POST发送https请求客服接口api
				 */
				$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $access_token;
				// 以'json'格式发送post的https请求
				$this->post($url, $json);
			} else {
				exit ( '' );
			}
		} else {
			echo "";
			exit ();
		}
	}
// 	/* 调用微信api，获取access_token，有效期7200s -xzz0704 */
// 	public function get_accessToken() {
// 		/* 在有效期，直接返回access_token */
// 		if (S( 'access_token' )) {
// 			return S( 'access_token' );
// 		}		/* 不在有效期，重新发送请求，获取access_token */
// 		else {
// 			$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.C('C_APPID').'&secret='.C('C_APPSECRET');
// 			$result = curl_get_https ( $url );
// 			$res = json_decode ( $result, true ); // json字符串转数组
			
// 			if ($res) {
// 				S( 'access_token', $res ['access_token'], 7100 );
// 				return S( 'access_token' );
// 			} else {
// 				return 'api return error';
// 			}
// 		}
// 	}

	public function get_accessToken() {
		return WeixinController::instance()->set_token()['access_token'];
// 		$access_token = S('access_token');
// 		$tokenInfo = json_decode($access_token,true);
// 		//判断access_token是否失效
// 		if($tokenInfo['expires_in'] - time() < 600){
// 			//重新获取access_token
// 			$access_token = WeixinController::instance()->get_access_token(C('G_APPID'),C('G_APPSECRET'));
// 			$tokenInfo = json_decode($access_token,true);
// 			$tokenInfo['expires_in'] = $tokenInfo['expires_in']+time();
// 			if($tokenInfo['errcode']){
// 				return $tokenInfo['access_token'];
// 			}
// 			S('access_token',json_encode($tokenInfo,JSON_FORCE_OBJECT));
// 		}
// 		return $tokenInfo['access_token'];
	}
	
	/*
	 * 请求微信接口
	 */
	public function post($url, $json) {
		$curl = curl_init ();
		curl_setopt ( $curl, CURLOPT_URL, $url );
		curl_setopt ( $curl, CURLOPT_POST, 1 ); // 发送一个常规的Post请求
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
		if (! empty ( $json )) {
			curl_setopt ( $curl, CURLOPT_POSTFIELDS, $json );
		}
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
		// curl_setopt($curl, CURLOPT_HTTPHEADER, $headers );
		$output = curl_exec ( $curl );
		if (curl_errno ( $curl )) {
			echo 'Errno' . curl_error ( $curl ); // 捕抓异常
		}
		curl_close ( $curl );
		if ($output == 0) {
			echo 'success';
			exit ();
		}
	}
}