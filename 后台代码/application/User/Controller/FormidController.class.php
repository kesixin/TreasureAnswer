<?php
namespace User\Controller;

use Common\Controller\WeixinController;
use Common\Lib\WXBizDataCrypt;


class FormidController extends WeixinController
{
	
	public function get_accesstoken(){
	
		
		$result=$this->set_token();
		$info['expire_time']=$result['expires_in'];
		$info['access_token']=$result['access_token'];
		$info['appid']=C('C_APPID');
		
		 $this->ajaxReturn($info);
		
	}
	
   public function qunfa1()
    {    
		$result=$this->set_token();
        $access_token = $result['access_token'];
        $url = 'http://120.79.83.165:8080/miniprogram/templatenotify';
        $this->qunfa_post($url, $access_token);
    }
    public function sendFormid()
    {
        $url = 'http://120.79.83.165:8080/miniprogram/templatenotify/postformid';
        $data['openid'] = $_GET['openid'];
        $data['formid'] = $_GET['formid'];
        if ($data['formid'] !== 'undefined') {
            $this->data_post($url, $data);
        }
    }
public function qunfa_post($url, $access_token)
    {
       
        $data_json = array(
            // "appid" => $this->_appid,
            //"secret" => $this->secret,
            "templateid" =>'ZKUKqEs2VJJkpVXnvaaSswdN5MXAeIqgCVdcw618Pz4',
            "page" =>'pages/index/index',
            "emphasis_keyword" => 'keyword1.DATA',
            "data" => array(
			array("keyname" => "keyword1", "value" =>'挑战', "color" => "#FF0000"),
			array("keyname" => "keyword2", "value" =>'挑战', "color" => "#436EEE"), 
			array("keyname" => "keyword3", "value" =>'挑战', "color" => "#436EEE")),
        );
        $headers = array('AppId:' . C('C_APPID'), 'Content-Type:application/json', 'Auth-Token:eyJhcHBpZCI6Ind4ZDYzYzgzM2JhNDAyYzA4MSJ9Cg==', 'access_token:' . $access_token);
        $ch = curl_init();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_json));
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
    public function data_post($url, $data)
    {
        $headers = array('AppId:' . C('C_APPID'), 'Content-Type:application/json', 'Auth-Token:eyJhcHBpZCI6Ind4ZDYzYzgzM2JhNDAyYzA4MSJ9Cg==');
        $data_json = array('data' => array(array('openid' => $data['openid'], 'formids' => array(array('formid' => $data['formid'])))));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_json));
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
	}
 }
