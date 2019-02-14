<?php
namespace Common\Lib\Baidu;
use Common\Lib\Ffmpeg;

define('AUDIO_FILE', "./test.pcm");
/**
 * 百度语音识别
 * author universe.h 2017.10.27
 */
class Sample {

    public function __construct(){}

    /**
     * 获取百度token
     * @return mixed
     */
    public function get_token(){
       $url = 'https://openapi.baidu.com/oauth/2.0/token?grant_type=client_credentials&client_id=rQVF507ts09Dpqbjl0yeOsti&client_secret=4e65fcc492f1c44f09fa19f9ecacca3d&';
        $res = curl_data($url);
        $res = json_decode($res,true);
        S('BD_TOKEN',$res['access_token'],['expire'=>$res['expires_in']]);
        return $res;
    }

    /**
     *
     * 语音转文字
     * @param $data
     *
     */
    public function identify($data){
        $token = S('BD_TOKEN');
        if(!$token){
            $token = $this->get_token()['access_token'];
        }

        //初始化语音转换
        $ffmpeg = new  Ffmpeg(['file'=>$data['voice_url']]);
        $res = $ffmpeg->audioTotext();
        $content = file_get_contents($res['vedio']);
        //音频长度
        $len = strlen($content);
        $speech = base64_encode($content) ;
        $post_data = [
            "format"=>"pcm",
            "rate"=>16000,
            "channel"=>1,
            "token"=>$token,
            "cuid"=>"test_zhihuiyun_hb",
            "len"=>$len,
            "speech"=>$speech, // xxx为 base64（FILE_CONTENT）
            'lan'=>'en',
        ];

        $url = 'http://vop.baidu.com/server_api';
        $res = $this->execute('POST',$url,json_encode($post_data,JSON_FORCE_OBJECT),'','Content-Type:application/json');
        if($res['err_no']!=0){
            return false;
        }
        return $res;
    }
    /**
     *
     * @param type $method 请求方式
     * @param type $url 地址
     * @param type $fields 附带参数，可以是数组，也可以是字符串
     * @param type $userAgent 浏览器UA
     * @param type $httpHeaders header头部，数组形式
     * @param type $username 用户名
     * @param type $password 密码
     * @return boolean
     */
    public  function execute($method, $url, $fields = '', $userAgent = '', $httpHeaders = '', $username = '', $password = '') {
        $ch = $this->create();
        if (false === $ch) {
            return false;
        }
        if (is_string($url) && strlen($url)) {
            $ret = curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            return false;
        }
        //是否显示头部信息
        curl_setopt($ch, CURLOPT_HEADER, false);
        //
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($username != '') {
            curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
        }
        
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        
        $method = strtolower($method);
        if ('post' == $method) {
            curl_setopt($ch, CURLOPT_POST, true);
            if (is_array($fields)) {
                $sets = array();
                foreach ($fields AS $key => $val) {
                    $sets[] = $key . '=' . urlencode($val);
                }
                $fields = implode('&', $sets);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        } else if ('put' == $method) {
            curl_setopt($ch, CURLOPT_PUT, true);
        }
        //curl_setopt($ch, CURLOPT_PROGRESS, true);
        //curl_setopt($ch, CURLOPT_VERBOSE, true);
        //curl_setopt($ch, CURLOPT_MUTE, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); //设置curl超时秒数
        if (strlen($userAgent)) {
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        }
        if (is_array($httpHeaders)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);
        }
        $ret = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            return array(curl_error($ch), curl_errno($ch));
        } else {
            curl_close($ch);
            if (!is_string($ret) || !strlen($ret)) {
                return false;
            }
            return $ret;
        }
    }

    /**
     * 发送POST请求
     * @param type $url 地址
     * @param type $fields 附带参数，可以是数组，也可以是字符串
     * @param type $userAgent 浏览器UA
     * @param type $httpHeaders header头部，数组形式
     * @param type $username 用户名
     * @param type $password 密码
     * @return boolean
     */
    public function post($url, $fields, $userAgent = '', $httpHeaders = '', $username = '', $password = '') {
        $ret = $this->execute('POST', $url, $fields, $userAgent, $httpHeaders, $username, $password);
        if (false === $ret) {
            return false;
        }
        if (is_array($ret)) {
            return false;
        }
        return $ret;
    }

    /**
     * GET
     * @param type $url 地址
     * @param type $userAgent 浏览器UA
     * @param type $httpHeaders header头部，数组形式
     * @param type $username 用户名
     * @param type $password 密码
     * @return boolean
     */
    public function get($url, $userAgent = '', $httpHeaders = '', $username = '', $password = '') {
        $ret = $this->execute('GET', $url, "", $userAgent, $httpHeaders, $username, $password);
        if (false === $ret) {
            return false;
        }
        if (is_array($ret)) {
            return false;
        }
        return $ret;
    }

    /**
     * curl支持 检测
     * @return boolean
     */
    public function create() {
        $ch = null;
        if (!function_exists('curl_init')) {
            return false;
        }
        $ch = curl_init();
        if (!is_resource($ch)) {
            return false;
        }
        return $ch;
    }

}

