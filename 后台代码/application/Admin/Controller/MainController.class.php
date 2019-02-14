<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;
use Common\Controller\WeixinController;

class MainController extends AdminbaseController {
	/*
    public function index(){
    	
    	$mysql= M()->query("select VERSION() as version");
    	$mysql=$mysql[0]['version'];
    	$mysql=empty($mysql)?L('UNKNOWN'):$mysql;
    	
    	//server infomaions
    	$info = array(
    			L('OPERATING_SYSTEM') => PHP_OS,
    			L('OPERATING_ENVIRONMENT') => $_SERVER["SERVER_SOFTWARE"],
    	        L('PHP_VERSION') => PHP_VERSION,
    			L('PHP_RUN_MODE') => php_sapi_name(),
				L('PHP_VERSION') => phpversion(),
    			L('MYSQL_VERSION') =>$mysql,
    			L('PROGRAM_VERSION') => THINKCMF_VERSION . "&nbsp;&nbsp;&nbsp; [<a href='http://gzzh.co' target='_blank'>技术支持-比邻科技</a>]",
    			L('UPLOAD_MAX_FILESIZE') => ini_get('upload_max_filesize'),
    			L('MAX_EXECUTION_TIME') => ini_get('max_execution_time') . "s",
    			L('DISK_FREE_SPACE') => round((@disk_free_space(".") / (1024 * 1024)), 2) . 'M',
    	);
    	$this->assign('server_info', $info);
    	$this->display();
    }*/
	
	public function index(){
		
		$data = WeixinController::instance()->get_wx_data();
		$this->assign('data', $data);
		$this->display();
	}
	
	public function get_day_data(){
		$wxClass = WeixinController::instance();
		$date = strtotime( 'today' ) -1;
		for( $i=0 ; $i<15 ; $i++ ){
			$time = $date -( $i * 86400);
			
			$data['date'][$i] = json_decode($wxClass->get_day_user_num( $time ),true)['list'][0]['ref_date'];
			$data['num'][$i] = json_decode($wxClass->get_day_user_num( $time ),true)['list'][0]['visit_total'];
		}
		if( $data ){
			
			$data['date'] = array_reverse($data['date']);
			$data['num'] = array_reverse($data['num']);
			$this->ajaxReturn(['code'=>1,'msg'=>'获取成功','data'=>$data]);die;
		}
		$this->ajaxReturn(['code'=>0,'msg'=>'网络繁忙']);die;
	}
}