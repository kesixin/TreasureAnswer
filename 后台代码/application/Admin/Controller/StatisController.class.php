<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class StatisController extends AdminbaseController{

	protected $user_model;
	protected $challenge_model;
	protected $payLog_model;

	public function _initialize() {
		parent::_initialize();
		$this->user_model = D("Common/WxUser");
		$this->challenge_model = M('challenge');
		$this->payLog_model = M('pay_log');
	}

    // 概况统计
    public function index(){
        //总用户数量
        $totolUser = $this->user_model->count();
        //昨天新增用户数量
        $yesterdayUser = $this->user_model->where(['add_time'=>[['EGT', strtotime('yesterday')],['ELT', strtotime('today')]], ])->count();
        //总参赛数量
        $totalChallenge = $this->challenge_model->count();
        //总挑战成功次数
        $totalPass = $this->challenge_model->where(['is_pass'=>1])->count();
        //复活卡总收入
        $totalIncome = $this->payLog_model->where(['status'=>0])->sum('total_fee');
        
        $this->assign('staisData', ['totolUser'=>$totolUser, 'yesterdayUser'=>$yesterdayUser, 'totalChallenge'=>$totalChallenge, 'totalPass'=>$totalPass, 'totalIncome'=>$totalIncome]);
        
        $this->display();
    }
    
    //图表统计
    public function chatStatis(){
        $type = I('post.type/d');
        $startTime = I('post.startTime/d');
        $endTime = I('post.endTime/d');
        
        $timeArr = [];
        $dataArr = [];
        while ($startTime < $endTime){
            $endt = $startTime + 86400;
            $timeArr[] = date("Y/m/d",$startTime);
            switch ($type) {
                case 0:
                    //用户增长
                    $dataArr[] = $this->user_model->where(['add_time'=>[['EGT', $startTime],['ELT', $endt]] ])->count();
                break;
                
                case 1:
                    //参赛次数增长
                    $dataArr[] = $this->challenge_model->where(['create_time'=>[['EGT', $startTime],['ELT', $endt]] ])->count();
                    break;
                
                default:
                    //复活卡次数增长
                    $dataArr[] = $this->payLog_model->where(['add_time'=>[['EGT', $startTime],['ELT', $endt]], 'status'=>0 ])->count();
                break;
            }
            
            $startTime = $endt;
        }
        
        $this->ajaxReturn(['result'=>true, 'timeArr'=>$timeArr, 'dataArr'=>$dataArr]);
    }

    // 答对率统计
    public function statsReply(){
        $this->display();
    }
	

}