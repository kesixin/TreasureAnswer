<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class PaydetailController extends AdminbaseController{

	protected $payLog_model;

	public function _initialize() {
		parent::_initialize();
		$this->payLog_model = M("pay_log");
	}

    // 支付设置
    public function Paydetail(){
        $this->display();
    }
    
    // 异步支付明细 json
    public function payLogList()
    {
        
        /**搜索条件**/
        $keywork = trim(I('request.sSearch'));
        
        $cpage = I('request.sEcho');
        if (empty($cpage)) {
            $cpage = 1;
        }
        $iDisplayStart = I('request.iDisplayStart');
        $iDisplayLength = I('request.iDisplayLength');
        if (empty($iDisplayStart)) {
            $iDisplayStart = 0;
        }
        if (empty($iDisplayLength)) {
            $iDisplayLength = 3;
        }
        
        //$where = ['del'=>0];
        //根据user_name 搜索
        if (!empty($keywork)) {
            $where['transaction_id'] = ['like', "%$keywork%"];
        }
        
        $queryField = 'a.id,b.nick_name,a.total_fee,FROM_UNIXTIME(a.`add_time`, "%Y-%m-%d %H:%i:%S") as add_time,a.transaction_id,if(a.status=0,"成功","失败") as status';

        $count = $this->payLog_model->where($where)->count();
        $list = $this->payLog_model
        ->alias('a')->join('LEFT JOIN __WX_USER__ b ON a.openid = b.openid')
        ->field($queryField)
        ->where($where)
        ->order("add_time DESC")
        ->limit($iDisplayStart, $iDisplayLength)
        ->select();
        
        foreach ($list as &$v){
            $v['nick_name'] = json_decode($v['nick_name']);
        }
        
        echo json_encode(['data'=>$list, 'iTotalRecords'=>$count, 'iTotalDisplayRecords'=>$count, 'sEcho'=>$cpage+1]);
    }

}