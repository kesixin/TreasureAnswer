<?php
namespace Common\Model;
use Common\Controller\InterceptController;
use Common\Model\CommonModel;
class EnveReceiveModel extends CommonModel
{

	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('pid', 'checkId', '缺少必传参数！',1 ,'callback', CommonModel:: MODEL_BOTH,array(200)),
		array('receive_answer', 'checkQuest', '口令在1-20个字符之间！',1 ,'callback', CommonModel:: MODEL_BOTH,array(500)),
	);

	protected $_auto = array(
		array('receive_answer','htmlSpecial',CommonModel:: MODEL_INSERT,'callback'),
		array('user_id','getUser',CommonModel:: MODEL_INSERT,'callback','user_id'),
		array('receive_num','1',CommonModel:: MODEL_INSERT),
        array('nick_name','getUser',CommonModel:: MODEL_INSERT,'callback','nick_name'),
        array('head_img','getUser',CommonModel:: MODEL_INSERT,'callback','head_img'),
        array('add_time','getTime',CommonModel:: MODEL_INSERT,'callback'),
	);


    //问题答案
	function checkId($val){
       return (int)$val < 1 ? false : true;
    }
    //问题答案
	function checkQuest($val,$length){
        $str_len = mb_strlen($val);
        if( $str_len < 1 && $str_len > $length ){
            return false;
        }
	    return true;
    }
    //检查金额
    function checkAmount($val){
        if((float)$val < 0.01 ){
            return false;
        }
        return true;
    }

    //最大值
    function max($val,$length){
        return mb_strlen($val) > $length ?  false : true;
    }

    function min($val,$length){
        return mb_strlen($val) < $length ?  false : true;
    }

    //验证是不是数字
	function isNum($val){
        return is_numeric($val) ? true : false;
    }
    function isInt($val){
        return is_integer($val) ? true : false;
    }
    //获取用户id
    function getUser($val){
        return  InterceptController::instance()->$val;
    }
    //转义
    function htmlSpecial($val=''){
        return htmlspecialchars($val);
    }
    //前端显示金额
    function showNum(){
        return I('num/d');
    }
    //显示金额
    function showAmount(){
        return I('post.amount/f');
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

