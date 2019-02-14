<?php
/**
 * WeixinController 获取微信接口信息控制器
 * author hhz 2017.10.12
 */
namespace Common\Controller;

use Think\Controller;

class FormidController extends Controller{

    /*
     * 接受from_id
     */
    public static function saveFormId($formIds, $openid) {
    	//$formIds = trim($formIds, '"');
    	$formIds= json_decode($formIds, true);
    	//dump($formIds);die;
    	foreach ($formIds as $k=>&$v){
    		$v['openid'] = $openid;
    	}
    	//dump($formIds);die;
    	M('formids')->addAll($formIds);
    }

}