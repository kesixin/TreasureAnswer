<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class TGsettingController extends AdminbaseController{

	protected $applist_model;

	public function _initialize() {
		parent::_initialize();
		$this->applist_model = M("applist");
	}

    // 推广设置
    public function TGsetting(){
        $this->display();
    }
    
    // 异步小程序列表 json
    public function appList()
    {
        
        /**搜索条件**/
        $keywork = trim(I('request.keywork'));
        
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
        
        $where = ['del'=>0];
        //根据user_name 搜索
        if (!empty($keywork)) {
            $where['app_name'] = ['like', "%$keywork%"];
        }
        
        $queryField = 'id,logo,app_name,app_desc,app_desc1,app_id,FROM_UNIXTIME(`create_time`, "%Y-%m-%d %H:%i:%S") as create_time';
        $count = $this->applist_model->where($where)->count();
        //$page = $this->page($count, $iDisplayLength, $cpage);
        $list = $this->applist_model
        ->field($queryField)
        ->where($where)
        ->order("create_time DESC")
        ->limit($iDisplayStart, $iDisplayLength)
        ->select();
        
        foreach ($list as &$v){
            $v['logo'] = C('TMPL_PARSE_STRING.__UPLOAD__').$v['logo'];
        }
        
        echo json_encode(['data'=>$list, 'iTotalRecords'=>$count, 'iTotalDisplayRecords'=>$count, 'sEcho'=>$cpage+1]);
    }
    
    //添加或编辑分类
    public function addOrEditInfo(){
        $id = I('post.id/d');
        $app_name = I('post.app_name/s');
        $app_id = I('post.app_id/s');
        $logo = I('post.logo/s');
        $app_desc = I('post.app_desc/s');
        $app_desc1 = I('post.app_desc1/s');
        
        $data = ['app_name'=>$app_name, 'app_id'=>$app_id, 'app_desc'=>$app_desc, 'app_desc1'=>$app_desc1];
        if (!empty($logo)) {
            $data['logo'] = $logo;
        }
        if (empty($id)) {
            $data['create_time'] = $_SERVER['REQUEST_TIME'];
        }
        $res = null;
        if (empty($id)) {
            $res = $this->applist_model->add($data);
        }else{
            $res = $this->applist_model->where(['id'=>$id])->save($data);
        }
        
        if (empty($res)) {
            $this -> ajaxReturn(['result'=>false, 'msg' => '操作失败']);
        }
        
        $this -> ajaxReturn(['result'=>true, 'msg' => '操作成功' ]);
    }
    
    //删除一个奖品
    public function prizeDelete()
    {
        //获取Id
        $id = I('post.id/d');
        
        $res = $this->prize_model->where(['id'=>$id])->setField('del', 1);
        
        if (!empty($res)) {
            
            $this -> ajaxReturn(['result'=>true, 'msg' => '删除成功']);
        }
        
        $this -> ajaxReturn(['result'=>false, 'msg' => '删除失败']);
    }


    /*****************************AJAX***************************/
    public function ajaxDelTG(){
        $id = I('post.id/d');
        if( !$id ){
            $this -> ajaxReturn(['result'=>false, 'msg' => 'id错误','code'=>0]);
        }
        $res = $this->applist_model->delete( $id );
        if( !$res ){
            $this -> ajaxReturn(['result'=>$res, 'msg' => '网络繁忙','code'=>0]);
        }
        $this -> ajaxReturn(['result'=>$res, 'msg' => '删除成功','code'=>1]);

    }

}