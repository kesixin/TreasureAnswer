<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class CustomerController extends AdminbaseController{

	protected $users_model;

	public function _initialize() {
		parent::_initialize();
		$this->users_model = D("WxUser");
	}

	// 管理员列表
	public function index()
    {
        /**搜索条件**/
        $openid = I('request.openid');
        $keywork = trim(I('request.keywork'));
        if ($openid) {
            $where['openid'] = $openid;
        }

        if ($keywork) {
            $where['user_name'] = array('like', "%$keywork%");
            $where['nick_name'] = array('like', "%$keywork%");
            $where['phone'] = array('like', "%$keywork%");
            $where['_logic'] = 'OR';
        }

        $count = $this->users_model->where($where)->count();
        $page = $this->page($count, 20);
        $users = $this->users_model
            ->where($where)
            ->order("add_time DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        foreach ($users as &$v){
            $v['sex'] = $v['sex']==1 ? L('MALE'):L('FEMALE');
            $v['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
            $v['update_time'] = date('Y-m-d H:i:s',$v['update_time']);
            $v['user_name'] = json_decode($v['user_name']);
            $v['nick_name'] = json_decode($v['nick_name']);
        }

        $this->assign('total', $count);
        $this->assign("page", $page->show('Admin'));
        $this->assign("users", $users);
        $this->display();
    }
}