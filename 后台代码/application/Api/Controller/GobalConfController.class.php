<?php
/**
 * 语音红包类
 * author universe.h
 */
namespace Api\Controller;
use Common\Controller\InterceptController;

class GobalConfController extends InterceptController {

    /*
     * 获取平台配置
     */
	function getGobalConfig() {
		$slideModel = M('slide');
		$slideList = $slideModel->where(array('slide_status'=>1))->limit(0,3)->select();
		$this->ajaxReturn(['code'=>20000, 'msg'=>'success', 'data'=>array('advList'=>$slideList, 'hb_commision'=>C('HB_COMMISION'), 'hb_adv_commision'=>C('HB_ADV_COMMISION') )]);
	}

}
