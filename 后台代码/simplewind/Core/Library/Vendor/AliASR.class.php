<?php
set_time_limit(800);
define('ENABLE_HTTP_PROXY', false);
define('HTTP_PROXY_IP', '127.0.0.1');
define('HTTP_PROXY_PORT', '8888');

// date_default_timezone_set('Asia/Shanghai');
// header("Content-type: text/html; charset=utf-8");

interface ISigner
{
    public function getSignatureMethod();
    public function getSignatureVersion();
    public function signString($source, $accessSecret);
}
class ShaHmac1Signer implements ISigner
{
    public function signString($source, $accessSecret)
    {
        return    base64_encode(hash_hmac('sha1', $source, $accessSecret, true));
    }
    public function getSignatureMethod()
    {
        return "HMAC-SHA1";
    }
    public function getSignatureVersion()
    {
        return "1.0";
    }
}
class ShaHmac256Signer implements ISigner
{
    public function signString($source, $accessSecret)
    {
        return    base64_encode(hash_hmac('sha256', $source, $accessSecret, true));
    }
    public function getSignatureMethod()
    {
        return "HMAC-SHA256";
    }
    public function getSignatureVersion()
    {
        return "1.0";
    }
}

if (!defined('ALIASR_ROOT')) {
	define('ALIASR_ROOT', dirname(__FILE__) . '/');
	require(ALIASR_ROOT. 'aliyun/HttpHelper.php');
}
//include_once 'aliyun/HttpHelper.php';
/*
 * 发送POST请求
 */
class AliASR{
	const AccessKeyId = 'LTAIFnYaC2SkVVxc';
	const AccessKeySecret = 'yNt370J1mfRaJ02qHpIpQUDch0vDUn';
	private $to_url = "http://nlsapi.aliyun.com/recognize?model=english";
public function sendAsrPost($audioData, $audioFormat, $sampleRate, $url='') {
	$url = empty($url)?$this->to_url : $url;
    $result = "";
    $request = new HttpHelper();
        $realUrl = $url;
        $method = "POST";
        $accept = "application/json";
        $content_type = "audio/".$audioFormat.";samplerate=".$sampleRate;
        $length = strlen($audioData);
        $date = date("D, d M Y H:m:s e",time());
        // 1.对body做MD5+BASE64加密
        $bodyMd5 = $this->encode_body($audioData);
        $stringToSign = $method."\n".$accept."\n".$bodyMd5."\n".$content_type."\n".$date ;
        // 2.计算 HMAC-SHA1
        $sig = new ShaHmac1Signer();
        $signature = $sig->signString($stringToSign, self::AccessKeySecret);
        // 3.得到 authorization header
        $authHeader = "Dataplus ".self::AccessKeyId.":".$signature;
        // 打开和URL之间的连接
        $headers["accept"] = $accept;
        $headers["content-type"] = $content_type;
        $headers["date"] = $date;
        $headers["Authorization"] = $authHeader;
        $headers["Content-Length"] = $length;
     	$response = $request->curl($realUrl,$method,$audioData,$headers);
		return $response;
}

public function encode_body($body){
	return base64_encode(md5(base64_encode(md5($body,true)),true));
}
public function signature ($source){
	$signer = new ShaHmac1Signer();
	return $signer->signString($source, AccessKeySecret);
}

}


// $response = $client->getAcsResponse($request); 
// print_r($response); 
