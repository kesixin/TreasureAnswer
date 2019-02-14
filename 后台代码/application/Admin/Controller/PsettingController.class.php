<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class PsettingController extends AdminbaseController{

	protected $prize_model;
	protected $challenge_model;
	protected $express_info_model;

	public function _initialize() {
		parent::_initialize();
		$this->prize_model = M("prizes");
		$this->challenge_model = M('challenge');
		$this->express_info_model = M('express_info');
	}

    // 中奖设置
    public function Psetting(){
        $this->display();
    }
    
    // 异步奖品列表 json
    public function prizeList()
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
        
        $where = ['del'=>0];
        //根据user_name 搜索
        if (!empty($keywork)) {
            $where['prize_name'] = ['like', "%$keywork%"];
        }
        
        $queryField = 'id,prize_name,prize_img,FROM_UNIXTIME(`create_time`, "%Y-%m-%d %H:%i:%S") as create_time,prize_price,prize_num';
        $count = $this->prize_model->where($where)->count();
        //$page = $this->page($count, $iDisplayLength, $cpage);
        $list = $this->prize_model
        ->field($queryField)
        ->where($where)
        ->order("create_time DESC")
        ->limit($iDisplayStart, $iDisplayLength)
        ->select();
        
        foreach ($list as &$v){
            $v['prize_img'] = C('TMPL_PARSE_STRING.__UPLOAD__').$v['prize_img'];
        }
        
        echo json_encode(['data'=>$list, 'iTotalRecords'=>$count, 'iTotalDisplayRecords'=>$count, 'sEcho'=>$cpage+1]);
    }
    
    //添加或编辑分类
    public function addOrEditPrizeInfo(){
        $pid = I('post.pid/d');
        $gift_name = I('post.gift_name/s');
        $p_amount = I('post.p_amount/d');
        $p_price = I('post.p_price/f');
        $gift_img = I('post.images/s');
        
        $data = ['prize_name'=>$gift_name, 'prize_num'=>$p_amount, 'prize_price'=>$p_price];
        if (!empty($gift_img)) {
            $data['prize_img'] = $gift_img;
        }
        if (empty($pid)) {
            $data['create_time'] = $_SERVER['REQUEST_TIME'];
        }
        $res = null;
        if (empty($pid)) {
            $res = $this->prize_model->add($data);
        }else{
            $res = $this->prize_model->where(['id'=>$pid])->save($data);
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

    // 中奖明细
    public function Pdetail(){
        $this->display();
    }
    
    // json 获奖明细列表
    public function exchangeInfoList()
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
        
        $where = ['prize_id'=>['exp', '>0'], 'del'=>0];
        //根据user_name 搜索
        if (!empty($keywork)) {
            $where['nick_name'] = ['like', "%$keywork%"];
        }
        
        $queryField = 'a.id,u.nick_name,p.prize_name,a.status,FROM_UNIXTIME(a.`create_time`, "%Y-%m-%d %H:%i:%S") as create_time';
        $count = $this->challenge_model->where($where)->count();
        unset($where['del']);
        $where['a.del'] = 0;
        //$page = $this->page($count, $iDisplayLength, $cpage);
        $list = $this->challenge_model
        ->alias('a')->join('LEFT JOIN __WX_USER__ u ON a.openid=u.openid')
        ->join('LEFT JOIN __PRIZES__ p ON a.prize_id=p.id')
        ->field($queryField)
        ->where($where)
        ->order("create_time DESC")
        ->limit($iDisplayStart, $iDisplayLength)
        ->select();
        
        foreach ($list as &$v){
            $v['nick_name'] = json_decode($v['nick_name']);
        }
        
        //         map.put("sEcho", initEcho);
        //         map.put("iTotalRecords", list.getTotal());//数据总条数
        //         map.put("iTotalDisplayRecords", list.getTotal());//显示的条数
        //         map.put("aData", list.getDataList());//数据集合
        
        echo json_encode(['data'=>$list, 'iTotalRecords'=>$count, 'iTotalDisplayRecords'=>$count, 'sEcho'=>$cpage+1]);
    }
    
    //中奖明细删除 ajax
    public function delExchangeInfo(){
        //获取Id
        $id = I('post.id/d');
        
        $res = $this->challenge_model->where(['id'=>$id])->setField('del', 1);
        
        if (!empty($res)) {
            
            $this -> ajaxReturn(['result'=>true, 'msg' => '删除成功']);
        }
        
        $this -> ajaxReturn(['result'=>false, 'msg' => '删除失败']);
    }
    
    
    // 物流管理
    public function Plogic(){
        $this->display();
    }
    
    // json 物流列表
    public function expressInfoList()
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
        
        $where = ['a.del'=>0];
        //根据user_name 搜索
        if (!empty($keywork)) {
            $where1['a.name'] = ['like', "%$keywork%"];
            $where1['a.express_num'] = ['like', "%$keywork%"];
            $where1['_logic'] = 'OR';
            $where['_complex'] = $where1;
        }
        
        $queryField = 'a.id,u.nick_name,p.prize_name,a.status,a.name,a.phone,a.address,a.express_num,a.status,a.express_co,FROM_UNIXTIME(a.`create_time`, "%Y-%m-%d %H:%i:%S") as create_time';
        $count = $this->express_info_model
        ->alias('a')->join('LEFT JOIN __WX_USER__ u ON a.openid=u.openid')
        ->join('LEFT JOIN __CHALLENGE__ c ON c.id=a.challengeId')
        ->join('LEFT JOIN __PRIZES__ p ON c.prize_id=p.id')
        ->field($queryField)
        ->where($where)
        ->count();
        //$page = $this->page($count, $iDisplayLength, $cpage);
        $list = $this->express_info_model
        ->alias('a')->join('LEFT JOIN __WX_USER__ u ON a.openid=u.openid')
        ->join('LEFT JOIN __CHALLENGE__ c ON c.id=a.challengeId')
        ->join('LEFT JOIN __PRIZES__ p ON c.prize_id=p.id')
        ->field($queryField)
        ->where($where)
        ->order("a.create_time DESC")
        ->limit($iDisplayStart, $iDisplayLength)
        ->select();
        
        foreach ($list as &$v){
            $v['nick_name'] = json_decode($v['nick_name']);
        }
        
        echo json_encode(['data'=>$list, 'iTotalRecords'=>$count, 'iTotalDisplayRecords'=>$count, 'sEcho'=>$cpage+1]);
    }
    
    //更改物流信息状态
    public function changeExpressStatus() {
        //获取Id
        $id = I('post.id/d');
        $status = I('post.status/d');
        if ($status <= 0) {
            $this -> ajaxReturn(['result'=>false, 'msg' => '参数错误']);
        }
        
        $res = $this->express_info_model->where(['id'=>$id])->setField('status', $status);
        
        if (!empty($res)) {
            
            $this -> ajaxReturn(['result'=>true, 'msg' => '操作成功']);
        }
        
        $this -> ajaxReturn(['result'=>false, 'msg' => '操作失败']);
    }
    
    //逻辑删除物流信息
    public function delExpressInfo() {
        //获取Id
        $id = I('post.id/d');
        
        $res = $this->express_info_model->where(['id'=>$id])->setField('del', 1);
        
        if (!empty($res)) {
            
            $this -> ajaxReturn(['result'=>true, 'msg' => '删除成功']);
        }
        
        $this -> ajaxReturn(['result'=>false, 'msg' => '删除失败']);
    }
    
    //填写快递信息
    public function fillExpressInfo() {
        $id = I('post.pid/d');
        $expressCo = I('post.express_co/s');
        $expressNo = I('post.express_num/s');
        
        if (empty($id) || empty($expressCo) || empty($expressNo)) {
            $this -> ajaxReturn(['result'=>false, 'msg' => '参数不完整']);
        }
        
        $res = $this->express_info_model->where(['id'=>$id])->save(['express_co'=>$expressCo, 'express_num'=>$expressNo, 'status'=>2]);
        
        if (!empty($res)) {
            
            $this -> ajaxReturn(['result'=>true, 'msg' => '操作成功']);
        }
        
        $this -> ajaxReturn(['result'=>false, 'msg' => '操作失败']);
    }

}