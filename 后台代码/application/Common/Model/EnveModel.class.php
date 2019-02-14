<?php
namespace Common\Model;
use Common\Controller\InterceptController;
use Common\Lib\Pinyin;
use Common\Model\CommonModel;

class EnveModel extends CommonModel
{

	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('quest', 'checkQuest', '口令无法识别！',0 ,'callback', CommonModel:: MODEL_BOTH,array(200)),
		array('answer', 'checkQuest', '答案无法识别！', 2 ,'callback', CommonModel:: MODEL_BOTH,array(200) ),
		array('amount', 'checkAmount', '金额不正确', 1,  'callback', CommonModel:: MODEL_BOTH),
		array('num', 'checkNum', '数量不正确', 1,  'callback', CommonModel:: MODEL_BOTH),
		//array('enve_type', 'checkRequire', '缺少参数', 1,  'callback', CommonModel::MODEL_INSERT),
	);

    //自动完成
	protected $_auto = array(
        array('user_id','getUserInfo',CommonModel:: MODEL_INSERT,'callback','user_id'),
        array('user_name','getUserInfo',CommonModel:: MODEL_INSERT,'callback','user_name'),
		array('quest','htmlSpecial',CommonModel:: MODEL_INSERT,'callback'),
		//array('quest_py','toPy',CommonModel:: MODEL_INSERT,'callback','quest_py'),
		array('answer','htmlSpecial',CommonModel:: MODEL_INSERT,'callback'),
		//array('answer_py','toPy',CommonModel:: MODEL_INSERT,'callback','answer_py'),
        array('out_trade_no','getTransaction',CommonModel:: MODEL_INSERT,'callback'),
		array('add_time','getTime',CommonModel:: MODEL_INSERT,'callback'),
		array('show_amount','showAmount',CommonModel:: MODEL_INSERT,'callback'),
	);


    //问题答案
	function checkQuest($val,$length){
        $str_len = mb_strlen($val);
        if( $str_len < 1 || $str_len > $length ){
            return false;
        }
	    return true;
    }

    //检查金额
    function checkAmount($val){
	    $reg = '/^\d+(.\d{1,2})?$/';
        $res = preg_match($reg, $val);
        if(!$res){
            return false;
        }
        return true;
    }

    //检查数量
	function checkNum($val){
        //相除
        $amount = I('post.amount/f');
        $amount = bcdiv($amount, $val,2);
        $ret = preg_match('/^\d*$/',$val);
	    if(!$ret || $val < 1 || $amount < 0.01){
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
    
    //判断红包类型必要的参数是否存在
    function checkRequire() {
    	$enveType = I('post.enve_type/d');
    	$postData = I('post.');
    	switch ($enveType) {
    		case 1:
    			$field = array('voice_url', 'amount', 'num', 'formIds');
    			break;
    			
    		case 2:
    			$field = array('voice_url', 'answer', 'amount', 'num', 'formIds');
    			break;
    			
    		default:
    			$field = array('quest', 'amount', 'num', 'formIds');
    			break;
    	}
    	
    	foreach ($field as $k => $v){
    		if (!isset($postData[$v]) || empty($postData[$v]) ) {
    			return false;
    		}
    	}
    	return true;
    }

    /*===========================自动完成===================================*/
    //获取用户信息
    function getUserInfo($val){
        return  InterceptController::instance()->$val;
    }
    //转义
    function htmlSpecial($val=''){
        if($val){
            return htmlspecialchars($val);
        }
        return '';
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
    //生成订单号
    function getTransaction(){
	    return create_order($this,'out_trade_no');
    }
    //文字转拼音
    function toPy($val){
        $data['quest_py'] = I('post.quest/s');
        $data['answer_py'] = I('post.answer/s');
        $py = new PinYin();
        return $py->getPY($data[$val]);
    }
    
}

