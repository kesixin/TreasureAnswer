<?php
/**
 * 余额提现
 * author universe.h
 */
namespace Api\Controller;
use Common\Controller\InterceptController;
use Common\Lib\Wxpay\Wxpay;
use Common\Controller\AdvController;
use Common\Controller\FormidController;

class WithdrawalsController extends InterceptController {

    /**
     * 提现
     * time 2017.10.30
     */
    public function cash() {

        $amount = I('post.amount/f');
        //$isxf = I('post.sxf/f');
        $user_model = M("WxUser");
        $info = $user_model->where("openid='%s'",array($this->openid))->find();
        if (empty($info)) {
        	$this->ajaxReturn(['code'=>40500, 'msg'=>'用户信息异常']);
        }

        $minWithdrawals = C('MIN_WITHDRAWALS');
        $max_withdrawal_time= C('MAX_WITHDRAWAL_TIME');
        
        $withdrawaltime = getWithdrawalTime($this->openid);
        if ($withdrawaltime >= $max_withdrawal_time) {
        	$this->ajaxReturn(['code'=>40500, 'msg'=>'每天提现次数不能大于'.$max_withdrawal_time.'次']);
        }
        
        $cash_amount = $info['amount'];

        if($amount < $minWithdrawals ){
            $this->ajaxReturn(['code'=>50000, 'msg'=>'提现金额不能小于'.$minWithdrawals.'元']);
        }
        /*
        $withdrawals_rate = C('WITHDRAWAL_COMMISSION');
        $sxf = ceil($amount*100 * $withdrawals_rate)/100;
        
        if ($sxf != $isxf) {
        	$this->ajaxReturn(['code'=>40500, 'msg'=>'提现异常']);
        }
        
        $ramount = $amount + $sxf;*/
        $ramount = $amount;

        if($amount > $cash_amount){
            $this->ajaxReturn(['code'=>40500, 'msg'=>'提现金额不能大于余额']);
        }

        M()->startTrans();
        $res_user = $user_model->where("openid='{$this->openid}'")->setDec('amount', $ramount);
        if(!$res_user){
            $user_model->rollback();
            $this->ajaxReturn(['code'=>40500, 'msg'=>'1提现失败']);
        }

        $out_trade_no = create_order(M('pay_log'),'out_trade_no',C('CASH_FIX'));
        
        $log_data = [
        		'user_id'=> $this->user_id,
        		'amount'=>$amount,
        		'sxf'=>$sxf,
        		/*        		'appid'=> $res['mch_appid'],
        		 'openid'=>$res['openid'],
        		 'check_name'=>$res['check_name'],
        		 're_user_name'=>$res['re_user_name'],
        		 'id_card'=>$res['id_card']??'',*/
        		'pay_desc'=>'余额提现',
        		'err_code_des'=>$res['err_code_des']??'',
        		/*        		'nonce_str'=>$res['nonce_str'],
        		 'partner_trade_no'=>$res['partner_trade_no'],
        		 'spbill_create_ip'=>$res['spbill_create_ip'],
        		 'status'=>$res['result_code'],*/
        		'add_time'=>time(),
        ];
        
        $logId = M('pay_log')->add($log_data);
        
        if(empty($logId)){
        	M('Withdrawals')->rollback();
        	$this->ajaxReturn(['code'=>40500, 'msg'=>'2提现失败']);
        }
        
        $data =[
            'openid'=>$this->openid,
        	'partner_trade_no'=>$out_trade_no,
            're_user_name'=>'',
            'amount'=> bcmul($amount, 100),
            'desc'=>'语音口令余额提现',
        ];
/*
        $wx =  new Wxpay();
        $res =  $wx->transfers($data);
        if($res['result_code']!='SUCCESS'){
            $user_model->rollback();
            $this->ajaxReturn(['code'=>40500, 'msg'=>'3提现失败']);
        }
*/
        //提交事务
        M()->commit();
        
        $log_data = [
        		/*            'user_id'=> $this->user_id,
        		 'amount'=>$amount,
        		 'sxf'=>$sxf,*/
        		'appid'=> $res['mch_appid'],
        		'openid'=>$res['openid'],
        		'check_name'=>$res['check_name'],
        		're_user_name'=>$res['re_user_name'],
        		'id_card'=>$res['id_card']??'',
        		//            'pay_desc'=>'余额提现',
        		'err_code_des'=>$res['err_code_des']??'',
        		'nonce_str'=>$res['nonce_str'],
        		'partner_trade_no'=>$res['partner_trade_no'],
        		'spbill_create_ip'=>$res['spbill_create_ip'],
        		'status'=>$res['result_code'],
        		//            'add_time'=>time(),
        ];
        $res_user= M('Withdrawals')->where(['id'=>$logId])->save($log_data);

        $this->ajaxReturn(['code'=>2000, 'msg'=>'提现成功', 'data'=>$info]);
    }
    
    /*
     * 获余额和提现参数
     */
    public function amountAndAdv() {
    	$info = M("WxUser")->field($field)->where('id=%d',array($this->user_id))->find();
    	$cash =$info['amount'];
    	$cash = $cash < 0 ? '0.00' : $cash;
    	$adv = AdvController::instance()->getAdv('withdrawal');
    	$withdrawaltime = getWithdrawalTime($this->openid);
    	$this->ajaxReturn(['code'=>20000, 'msg'=>'success','amount'=>$cash, 'adv'=>$adv, 'withdrawal_commission'=>C('WITHDRAWAL_COMMISSION'), 'min_withdrawals'=>C('MIN_WITHDRAWALS'),'max_withdrawals'=>1000, 'max_withdrawal_time'=>C('MAX_WITHDRAWAL_TIME'), 'withdrawal_time'=>C('MAX_WITHDRAWAL_TIME')-$withdrawaltime]);
    }
    
    /*
     * 用户提现列表
     */
    public function withdrawalList() {
    	$page = I('post.page/d');
    	$page = $page ? $page : 0;
    	$pageSize = 30;
    	$field= 'FROM_UNIXTIME(add_time,\'%Y-%m-%d %H:%i\') add_time, amount, status';
    	$wdList = M('pay_log')->field($field)->where(array('openid'=>$this->openid, 'pay_type'=>0, 'money_type'=>1, 'status'=>0 ))->order('add_time desc')->page($page, $pageSize)->select();
    	if (empty($wdList)) {
    		if ($page != 1) {
    			$this->ajaxReturn(['code'=>40500, 'msg'=>'没有更多了']);
    		}
    	}
    	
    	$this->ajaxReturn(['code'=>20000, 'msg'=>'success','data'=>$wdList]);
    }


    /**
     * 提现
     * time 2017.10.30
     */
    public function cash() {

        $info = $user_model->where("openid='%s'",array($this->openid))->find();
        if (empty($info)) {
            $this->ajaxReturn(['code'=>40500, 'msg'=>'用户信息异常']);
        }
        $amount = M('uuser')->where( "unionid = '{$info['unionid']}'" )->find()['amount'];

        if($amount < 1 ){
            $this->ajaxReturn(['code'=>40500, 'msg'=>'提现金额不能小于1元']);
        }

        $log_data = [
                'user_id'=> $this->user_id,
                'amount'=>$amount,
                'openid'=>$this->openid,
                'pay_desc'=>'余额提现',
                'add_time'=>time(),
        ];
        $logId = M('pay_log')->add($log_data);

        $log_data = [
                'user_id'=> $this->user_id,
                'amount'=>$amount,
                'openid'=>$this->openid,
                'pay_desc'=>'余额提现',
                'status'=>'wait',
                'add_time'=>time(),
        ];
        $withId = M('Withdrawals')->add($log_data);

        
        if( empty($logId) || empty($withId)){
            $this->ajaxReturn(['code'=>40500, 'msg'=>'2提现失败']);
        }
        

        M()->startTrans();

        $res_user = M('uuser')->where( "unionid = '{$info['unionid']}'" )->setDec('amount', $ramount);
        if(!$res_user){
            $user_model->rollback();
            $this->ajaxReturn(['code'=>40500, 'msg'=>'网络繁忙,请稍后再试']);
        }

        $out_trade_no = create_order(M('pay_log'),'out_trade_no',C('CASH_FIX'));
        
        $data =[
            'openid'=>$this->openid,
            'partner_trade_no'=>$out_trade_no,
            're_user_name'=>'',
            'amount'=> bcmul($amount, 100),
            'desc'=>'余额提现',
        ];
        
        $wx =  new Wxpay();
        $res =  $wx->transfers($data);
        if($res['result_code']!='SUCCESS'){
            $user_model->rollback();
            $this->ajaxReturn(['code'=>40500, 'msg'=>'3提现失败']);
        }

        //提交事务
        M()->commit();

        $log_data = [
                'appid'=> $res['mch_appid'],
                'openid'=>$res['openid'],
                'check_name'=>$res['check_name'],
                're_user_name'=>$res['re_user_name'],
                'id_card'=>$res['id_card']??'',
                'err_code_des'=>$res['err_code_des']??'',
                'nonce_str'=>$res['nonce_str'],
                'partner_trade_no'=>$res['partner_trade_no'],
                'spbill_create_ip'=>$res['spbill_create_ip'],
                'status'=>$res['result_code'],
                'finish_time'=>time(),
        ];
        $res_user = M('Withdrawals')->where(['id'=>$withId])->save($log_data);
        if( false === $res_user ){
            $content = ['content'=>json_encode( $log_data )];
            M('errorDrawal')->add($content);
        }

        $this->ajaxReturn(['code'=>2000, 'msg'=>'提现成功', 'data'=>$info]);
    }

}

