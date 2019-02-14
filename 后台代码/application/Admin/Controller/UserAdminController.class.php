<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;
use Common\Model\OptionsModel;

class UserAdminController extends AdminbaseController{

	protected $users_model,$role_model;

	public function _initialize() {
		parent::_initialize();
		$this->users_model = D("Common/WxUser");
		$this->role_model = D("Common/Role");
	}

	// 管理员列表
	public function userAdmin(){
		
		$this->display();
	}
	//微信用户列表
	public function wxUserList() {
	    /**搜索条件**/
	    $keywork = trim(I('request.sSearch'));
	    $sortIndex = I('request.iSortCol_0');
	    $sSortDir_0 = I('request.sSortDir_0');
	    
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
	    
	    $where = [];
	    //根据user_name 搜索
	    if (!empty($keywork)) {
	        $where['u_name'] = ['like', "%$keywork%"];
	    }
	    
	    $sortField = ['id', 'nick_name', 'ctimes', 'prize_amount', 'zm_points', 'add_time', 'update_time'];
	    $orderStr = "id DESC";
	    if ($sortIndex > 0) {
	        $orderStr = $sortField[$sortIndex].' '.$sSortDir_0.','.$orderStr;
	    }else{
	        $orderStr = $sortField[$sortIndex].' '.$sSortDir_0;
	    }
	    
	    $queryField = '(select count(id) from hb_challenge where openid=u.openid ) as ctimes,(select count(id) from hb_challenge where openid=u.openid and prize_id>0 ) as prize_amount,id,nick_name,head_img,FROM_UNIXTIME(`add_time`, "%Y-%m-%d %H:%i:%S") as add_time,FROM_UNIXTIME(`update_time`, "%Y-%m-%d %H:%i:%S") as update_time,zm_points,status';
	    $count = $this->users_model->where($where)->count();
	    //$page = $this->page($count, $iDisplayLength, $cpage);
	    $list = $this->users_model->alias('u')
	    ->field($queryField)
	    ->where($where)
	    ->order($orderStr)
	    ->limit($iDisplayStart, $iDisplayLength)
	    ->select();
	    
	    foreach ($list as &$v){
	        $v['nick_name'] = json_decode($v['nick_name']);
	    }
	    
	    echo json_encode(['data'=>$list, 'iTotalRecords'=>$count, 'iTotalDisplayRecords'=>$count, 'sEcho'=>$cpage+1]);
	}
	
	//更改用户状态
	public function changeStatus() {
	    $id = I('post.id/d');
	    $status = I('post.status/d');
	    
	    $res = $this->users_model->where(['id'=>$id])->setField('status', $status);
	    if (!empty($res)) {
	        $this->success("修改成功！",'',true);
	    } else {
	        $this->error("修改失败！",'',true);
	    }
	}
	
}