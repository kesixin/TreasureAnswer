<?php
namespace Common\Model;
use Common\Model\CommonModel;
class WxUserModel extends CommonModel
{

	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('user_name', 'max', '用户名称长度不正确！', 0, 'callback', CommonModel:: MODEL_BOTH,array(100) ),
		array('head_img', 'max', '头像地址不正确！', 0, 'callback', CommonModel:: MODEL_BOTH,array(255) ),
		array('sex', 'max', '参数错误（性别）', 0, 'callback', CommonModel:: MODEL_BOTH,array(1)  ),
		array('coutry', 'max', '参数错误', 0, 'callback', CommonModel:: MODEL_BOTH,array(100)  ),
		array('province', 'max', '参数错误', 0, 'callback', CommonModel:: MODEL_BOTH,array(100)  ),
		array('city', 'max', '参数错误', 0, 'callback', CommonModel:: MODEL_BOTH,array(100)  ),
		array('phone', '/^1[3|4|5|8][0-9]\d{4,8}$/', '电话号码不正确', 2, 'regex', CommonModel:: MODEL_BOTH  ),
	);

	protected $_auto = array(
		array('ip_addr','getIp',CommonModel:: MODEL_INSERT,'callback'),
			array('update_time','getTime',CommonModel:: MODEL_BOTH,'callback'),
		array('add_time','getTime',CommonModel:: MODEL_INSERT,'callback')
	);

	function max($val,$length){
		if(mb_strlen($val) > $length){
			return false;
		};
	}

	//用于获取时间，格式为2012-02-03 12:12:12,注意,方法不能为private
	function getTime() {
		return time();
	}
	//获取登录ip
	function getIp(){
		return get_client_ip();
	}

}

