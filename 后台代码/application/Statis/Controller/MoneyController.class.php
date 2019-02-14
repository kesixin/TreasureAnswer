<?php
/**
 * 资金统计
 * author hhz 2017.10.10
 */
namespace Statis\Controller;
use Common\Controller\AdminbaseController;
use Common\Controller\WeixinController;
use Think\Controller;

class MoneyController extends AdminbaseController {

    //用户显示界面
    public function index()
    {
        $this->display();
    }

    /**
     * 统计佣金总额
     * @return bool
     * time 2017.10.17
     */
    public function get_sum(){
    	$begin_date = strtotime(I('begin_date'));
    	$end_date = strtotime(I('end_date'));
    	if(!$end_date && !$begin_date){
    		$this->ajaxReturn(['code'=>40000,'msg'=>'时间不正确']);
    	}
    	if($end_date - $begin_date < 0){
    		$this->ajaxReturn(['code'=>40000,'msg'=>'开始时间不能大于结束时间']);
    	}
    	if((time() - $end_date)< 0){
    		$this->ajaxReturn(['code'=>40000,'msg'=>'只能统计'.date('Y-m-d',time()-86400).'以内的数据']);
    	}
    	$enve = M('Enve');
    	$paylog = M('pay_log');
    	$where = 'add_time > ' .$begin_date. ' and add_time <= ' .$end_date;
    	$whereOk = $where. ' and status = 0 and money_type=1';
    	$wcPayOk = $where. ' and status = 0 and (pay_type=1 or pay_type=2)';
     	$balancePayOk = $where. ' and status = 0 and (pay_type=2 or pay_type=0)';
    	/*平台红包总金额*/
     	$enveTotal = $paylog->where($whereOk)->sum('amount');
     	$enveTotal1 = $paylog->where($whereOk)->sum('total_fee');
     	$hbpay_amount= number_format($enveTotal+$enveTotal1, 2);
    	/*平台红包抽佣总金额*/
     	$hbpay_comm= number_format($paylog->where($whereOk)->sum('commission'),2);
    	
    	/*微信支付总金额*/
    	$wcPay = number_format($paylog->where($wcPayOk)->sum('total_fee'), 2);
    	
//     	/*余额支付总金额*/
     	$balancePay = number_format($paylog->where($balancePayOk)->sum('amount'), 2);

    	/*用户余额总金额*/
    	$userBalance = number_format(M('wx_user')->sum('amount'), 2);
    	
    	$data = ['hbsum_amount' => $hbpay_amount, 'hbsum_comm' => $hbpay_comm, 'wcPay' => $wcPay, 'balancePay'=>$balancePay, 'userBalance'=>$userBalance];
    	$data = ['status'=>20000, 'code'=>20000, 'data'=>$data];
    	$this->ajaxReturn($data);
    }
}
