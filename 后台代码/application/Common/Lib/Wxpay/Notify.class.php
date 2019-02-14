<?php
namespace Common\Lib\Wxpay;

use Think\Exception;
/**
 * 通用通知接口demo
 * ====================================================
 * 支付完成后，微信会把相关支付和用户信息发送到商户设定的通知URL，
 * 商户接收回调信息后，根据需要设定相应的处理流程。
 *
 * 这里举例使用log文件形式记录回调信息。
 * author  universe.h
 */
class Notify{
	//返回数据初始化
	protected  $postStr;
	//xml格式
	protected  $xml_arr;
	public function __construct()
	{
		$this->postStr = file_get_contents("php://input");
		$this->xml_arr = array(
				'return_code' => 'SUCCESS',
		);
	}
	public function notifySet(){
		try {
			if (!$this->postStr) {
				\Think\Log::write('无数据返回','','','./WXpaylog/Callback'.date('y_m_d').'.log');
				return false;
			}
			$postdata = json_decode(json_encode(simplexml_load_string($this->postStr, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
			
			\Think\Log::write(json_encode($postdata),'','','./WXpaylog/Callback'.date('y_m_d').'.log');
			
			#====先判断上述的xml信息是否是微信服务器给的，即进行签名比对==============
			$wxsign = $postdata['sign'];
			unset($postdata['sign']);#将签名先剖除
			
			$wxpay = new Wxpay();
			$sign = $wxpay->getSign($postdata);
			if ($wxsign != $sign) {
				\Think\Log::write('签名失败','','','./WXpaylog/Callback'.date('y_m_d').'.log');
				return false;
			}
			
			if ($postdata['result_code'] != 'SUCCESS') {
				\Think\Log::write('支付失败','','','./WXpaylog/Callback'.date('y_m_d').'.log');
				
				//log入库
				$log_where = [
						'status'=>-1,
						'total_fee'=>$postdata['total_fee']/100,
						'out_trade_no'=>$postdata['out_trade_no'],
				];
				
				$log_update= [
						'status'=>1,
						'desc'=>'用户取消支付或者支付失败',
						'finish_time'=>time(),
				];
				M('PayLog')->where($log_where)->save($log_update);
				return false;
			}
			//订单异常
			$result = $wxpay->orderQuery($postdata);
			if(!array_key_exists("return_code", $result)
					&& !array_key_exists("result_code", $result)
					&& $result["return_code"] != "SUCCESS"
					&& $result["result_code"] != "SUCCESS")
			{
				\Think\Log::write('订单异常','','','./WXpaylog/abnormal'.date('y_m_d').'.log');
				//log入库
				$log_where = [
						'status'=>-1,
						'total_fee'=>$postdata['total_fee']/100,
						'out_trade_no'=>$postdata['out_trade_no'],
				];
				$log_data = [
						'status'=>2,
						'desc'=>'订单在商户里无法查询',
						'finish_time'=>time(),
				];
				M('PayLog')->where($log_where)->save($log_data);
				return false;
			}
			
			/*更新日志*/
			//更新paylog
			$log_where = [
			    'status'=>-1,
			    'total_fee'=>$postdata['total_fee']/100,
			    'out_trade_no'=>$postdata['out_trade_no'],
			];
			$log_update = [
			    'status'=>0,
			    'transaction_id'=>$postdata['transaction_id'],
			    'desc'=>'微信支付交易成功',
			    'finish_time'=>time(),
			];
			M('pay_log')->where($log_where)->save($log_update);
			$log_where['isPlaying'] = 0;
			$log_where['status'] = 0;
			$openid = M('pay_log')->where($log_where)->getField('openid');
			
			if (!empty($openid)) {
			    M('wx_user')->where(['openid'=>$openid])->setInc('revive_time', 1);
			}
			
			/*
			//开始处理业务
			$str = substr($postdata['out_trade_no'],0,1);
			//入库操作函数
			//            $postdata = json_encode($postdata,JSON_UNESCAPED_UNICODE);
			call_user_func_array(array(&$this, $str.'update'),array($postdata) );
			*/
			
		} catch (Exception $e) {
			$this->xml_arr['return_code'] = 'FAIL';
			$this->xml_arr['return_msg'] = $e->getMessage();
		}
		
		// 数组转化为xml
		$xml = "<xml>";
		foreach ($this->xml_arr as $key => $val) {
			if (is_numeric($val)) {
				$xml .= "<" . $key . ">" . $val . "</" . $key . ">";
			} else
				$xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
		}
		$xml .= "</xml>";
		
		echo $xml;
		exit();
	}
	
	/**
	 * 更新订单状态操作
	 * time 2017.10.25
	 */
	public function Hupdate($data){
		//        \Think\Log::write(json_encode($data,JSON_UNESCAPED_UNICODE),'','','./WXpaylog/abnormal'.date('y_m_d').'.log');
		
		$res = M('PayLog')->where("transaction_id='%s'",array($data['transaction_id']))->find();
		if($res['content']){
			$content = json_decode($res['content'],true);
		}
		
		//log的状态
		if($content['result_code']!='SUCCESS'){
			$enve = M('Enve')->field('user_id,pay_type')->where("out_trade_no='%s'",array($data['out_trade_no']))->find();
			//开始事务p
			M('Enve')->startTrans();
			
			//重置用户余额为0
			if ($enve['pay_type'] == 2) {
				M('WxUser')->where(array('id' =>$enve['user_id'] ))->setField('frozen_amount', 0);
			}
			
			
			//更新paylog
			$log_where = [
					'status'=>-1,
					'total_fee'=>$data['total_fee']/100,
					'out_trade_no'=>$data['out_trade_no'],
			];
			$log_update = [
					'status'=>0,
					'transaction_id'=>$data['transaction_id'],
					'desc'=>'微信支付交易成功',
					'finish_time'=>time(),
			];
			M('pay_log')->where($log_where)->save($log_update);
			$enveDtata = [
					'pay_status'=>1,
					'transaction_id'=>$data['transaction_id'],
					'update_time'=>time(),
			];
			$res = M('Enve')->where("out_trade_no='%s'",array($data['out_trade_no']))->save($enveDtata);
			if(!$res){
				M('Enve')->rollback();
				return false;
			}
			M('Enve')->commit();
		}
		
	}
	
}