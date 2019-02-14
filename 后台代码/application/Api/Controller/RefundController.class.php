<?php
namespace Api\Controller;
use Think\Controller;
use Common\Controller\WeixinController;
use Common\Lib\Queue;

class RefundController extends Controller{

    /**
     * 退款脚本
     */
    public function index(){
        $enve = M('enve');
        $payLogModel = M('pay_log');
        $wx_user=M('wx_user');
        //查询满足到期未领完的红包
        $res = $enve->where('pay_status=1 and amount > 0 and be_overdue = 0 and UNIX_TIMESTAMP(CURRENT_TIMESTAMP)-add_time > 86400')->select();
        if(!$res){
            echo '暂无满足退款条件红包';
            exit;
        }
        foreach($res as $v){
        	
        	//开始事务
        	M()->startTrans();
        	$res_enve = $enve->where(array('id'=>$v['id']))->setField('be_overdue', 1); // 更新红包金额
        	if(!$res_enve){
        		$enve->rollback();
        		break;
        	}
        	$res_user = $wx_user->where(array('id'=>$v['user_id']))->setInc('amount',$v['amount']); // 更新用户金额
        	if(!$res_user){
        		$wx_user->rollback();
        		break;
        	}
        	
        	//插log
        	$log_data = [
        			'pay_type'=> -1,
        			'transaction_id'=>$v[transaction_id],
        			'status'=> 0,
        			'desc'=>'红包未领完退款',
        			'action'=>__ACTION__,
        			'content'=>json_encode($v,JSON_FORCE_OBJECT),
        			'money_type'=>3,
        			'add_time'=>time(),
        			'finish_time'=>time(),
        			'pid'=>$v['id'],
        			'openid'=>$v['openid'],
        	];
        	
        	$pay_log = $payLogModel->add($log_data); // 更新用户金额
        	if(empty($pay_log)){
        		$wx_user->rollback();
        		break;
        	}
        	Queue::getInstance('enve'.$v['id'])->flushQueue();//删除队列
        	M()->commit();
        	//$info = $enve -> where('transaction_id="'.$v['transaction_id'] . '"') -> find();
        	//判断支付方式，根据支付方式选择传递form_id或prepay_id
        	$formId = empty($v['prepay_id']) ? $v['form_id'] : $v['prepay_id'];
        	$enveStr = '口令红包“'.$v['quest'].'”';
        	
        	$tplData = [
        			'keyword1'=>[
        					'value'=> $v['amount'] . '元',
        					'color'=>'#173177',
        			],
        			'keyword2'=>[
        					'value'=> $enveStr.'未抢完',
        					'color'=>'#173177',
        			],
        			'keyword3'=>[
        					'value'=> date('m月d日 H:i', time()),
        					'color'=>'#173177',
        			],
        			'keyword4'=>[
        					'value'=> '语音红包账户余额',
        					'color'=>'#173177',
        			],
        			'keyword5'=>[
        					'value'=> '点击此处查看红包详情',
        					'color'=>'#173177',
        			],
        	];
        	$param = [
        			'touser' => $v['openid'],
        			'template_id' => C('news_tpl_refund'),
        			'page' => 'pages/recordDetails/recordDetails?pid='.$v['id'],
        			'form_id' => $formId,
        			'data' => $tplData,
        	];
        	WeixinController::instance()->send_template($param);
        	echo $v['out_trade_no'].'成功';
        	
        }
    }
    
    /*
     * 每天凌晨补给芝麻分
     */
    /*
    public function supplyZmPoints() {
    	//M('wx_user')->where('UNIX_TIMESTAMP(ADDDATE(DATE(SYSDATE()),1)) > supply_time and zm_points < 3')->save(['zm_points'=>3, 'supply_time'=>time() ] );timestamp(date(sysdate()))
       M('wx_user')->where('UNIX_TIMESTAMP(DATE(SYSDATE())) > supply_time and zm_points < 3')->save(['zm_points'=>3, 'supply_time'=>time() ] ); //
    	//M('settings')->where(['key'=>'access_vol'])->setField('value', 33000);
    	//F('access_vol', 33000);
    }*/

    public function supplyZmPoints() {
        $giveAmount = C('giveAmount');
        M('wx_user')->where('UNIX_TIMESTAMP(DATE(SYSDATE())) > supply_time and zm_points < '.$giveAmount)->save(['zm_points'=>$giveAmount, 'supply_time'=>time() ] );
    }
    
    /*
     * 获取所有可推送的用户列表放进队列
     */
    public function saveToQueue(){
    	$queue_send = Queue::getInstance('send_subscribe');
    	if ($queue_send->len() == 0) {
    		$res = M('wx_user')->field('f.openid,f.formid,f.id')->join('__FORMIDS__ f ON __WX_USER__.openid = f.openid', 'LEFT')->where(['subscribe'=>1, 'f.openid'=>['exp', 'IS NOT NULL'], 'f.expire'=>['gt',time()]])->group('f.openid')->select();
    		if (empty($res)) {
    			exit('无需推送用户');
    		}
    		foreach ($res as $v){
    			$queue_send->push(serialize($v));
    		}
    		dump($res);
    		exit($queue_send->len());
    	}
    	exit('队列有内容');
    }
    
    /*
     *  推送消息
     */
    public function sendSubscribe() {
    	$queue_send = Queue::getInstance('send_subscribe');
    	
    	$enveInfo = M('enve')->field('user_name,show_amount,num')->where(['del'=>0, 'audit'=>1, 'share2square'=>1, 'add_time'=>['gt', time()-900]])->find();
    	//exit($enveInfo);
    	if (empty($enveInfo)) {
    		exit('无新广场红包');
    	}
    	$formidModel = M('formids');
    	$enveInfo['user_name'] = json_decode($enveInfo['user_name']);
    	for ($i = 0; $i < 10; $i++){
    		
    		if ($queue_send->len() != 0) {
    			$info = $queue_send->pop();
    			if (!empty($info)) {
    				$info = unserialize($info);
    			}else {
    				exit('已推送完成');
    			}
    			
    			$formidModel->where(['id'=>$info['id']])->delete();
    			
    			$tplData = [
    					'keyword1'=>[
    							'value'=> $enveInfo['user_name'].'在红包广场发了'.$enveInfo['show_amount'].'元红包，共'.$enveInfo['num'].'份，快来抢吧~',
    							'color'=>'#173177',
    					],
    					'keyword2'=>[
    							'value'=> '点击此处到红包广场抢红包',
    							'color'=>'#173177',
    					],
    			];
    			$param = [
    					'touser' => $info['openid'],
    					'template_id' => C('subscribe_tpl_send'),
    					'page' => 'pages/square/square',
    					'form_id' => $info['formid'],
    					'data' => $tplData,
    			];
    			WeixinController::instance()->send_template($param);
    		}else {
    			exit('无需推送用户') ;
    		}
    		
    	}
    	
    }

}
