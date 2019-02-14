<?php
/**
 * 用户统计类
 * author hhz 2017.10.10
 */
namespace Statis\Controller;
use Common\Controller\AdminbaseController;
use Common\Controller\WeixinController;
use Think\Controller;

class UserController extends AdminbaseController {

    //用户显示界面
    public function index()
    {
        $this->display();
    }
    //微信用户数据调用
    public function get_usersummary(){
        $begin_date = strtotime(I('begin_date'));
        $end_date = strtotime(I('end_date'));
        if(($end_date - $end_date)/86400 > 7 ){
            $this->ajaxReturn(['code'=>40000,'msg'=>'区间不能大7']);
        }
        if((time() - $end_date)/86400 < 1){
            $this->ajaxReturn(['code'=>40000,'msg'=>'只能统计'.date('Y-m-d',time()-86400).'以内的数据']);
        }
        //获取公众号数据
        $begin_date = date('Y-m-d',$begin_date);
        $end_date = date('Y-m-d',$end_date);
        $resInfo = WeixinController::instance()->get_user_wx(['begin_date'=>$begin_date,'end_date'=>$end_date]);
        if($resInfo['errcode']){
            $this->ajaxReturn(['msg'=>$resInfo['errmsg']]);
        }
        $data = ['status'=>20000, 'code'=>20000];
        $data += $resInfo;
        $this->ajaxReturn($data);
    }

    /**
     * 红包用户数据统计
     * @return bool
     * time 2017.10.15
     */
    public function envelop_user(){
        $begin_date = strtotime(I('begin_date'));
        $end_date = strtotime(I('end_date'));
        if((time() - $end_date)/86400 < 0.1){
            $this->ajaxReturn(['code'=>40000,'msg'=>'只能统计'.date('Y-m-d',time()-86400).'以内的数据']);
        }
        //时间为空测模板赋值
        if(!$begin_date && !$end_date){
            $this->display();
            return true;
        }
        $user = M('WxUser');
        $enve = M('Enve');
        $EnveReceive = M('EnveReceive');
        $where = 'add_time > ' .$begin_date. ' and add_time <= ' .$end_date;
        $data['user_num'] = $user->where($where)->count();
        $data['enve_receive_num'] = $EnveReceive->where($where)->count();
        $whereOk = $where.' and pay_status = "ok"';
        $data['enve_num'] = $enve->where($whereOk)->count();
        $where = $where.' and pay_status != "ok"';
        $data['nopay_enve'] = $enve->where($where)->count();
        $data = ['user_num'=>56313,'enve_receive_num'=>35012,'enve_num'=>28034,'nopay_enve'=>29323];
        $data += ['status'=>20000, 'code'=>20000];
        $this->ajaxReturn($data);
    }
}
