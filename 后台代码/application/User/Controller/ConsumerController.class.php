<?php
namespace User\Controller;

use Common\Controller\InterceptController;
use Think\Model;
use Common\Controller\AdvController;
use Common\Lib\Percolate;
use Common\Controller\FormidController;

class ConsumerController extends InterceptController {
    protected $wx_user;

    public function __construct()
    {
        parent::__construct();
        $this->wx_user = M("WxUser");
    }

    //提现用户信息
    public function userInfo(){
        $field = 'amount,zm_points';
        $info = $this->wx_user->field($field)->where('id=%d',array($this->user_id))->find();
        $cash = $info['amount'] < 0 ? '0.00' : $info['amount'];
        $data = [
            'cash_status'=>true,
            'amount'=>$cash,
            'commision'=>C('HB_COMMISION'),//佣金比例
        	'amount_min'=>C('AMOUNT_MIN'),//红包最低金额
        	'receive_amount_min'=>C('RECEIVE_AMOUNT_MIN'),//领单个红包最低金额
        	'hbadv_min'=>C('HBADV_MIN'),//红包最低金额
        	'hbshare_min'=>C('HBSHARE_MIN'),//领单个红包最低金额
        	'hb_max_amount'=>50000,//最大发红包金额
        	'hb_max_num'=>10000,//最大发红包数量
        	'ctime'=>$info['zm_points'],
        'share_text'=>1,
        'nowfrequency'=>0,
        ];
        if( $cash <= 1 ){
            $data['cash_status'] = false;
        }

        //$data['adv'] = AdvController::instance()->getAdv('index');
        //$data['kouling'] = M('kouling')->field('link_description')->where(array('link_status'=>1))->order('listorder asc')->select();
        //获取
        $data['rule']  = C('gamerule');//'挑战口令一共有5条，每条口令都有相应的限制时间，在规定时间内完成挑战.';
        $data['money']  = C('gamefuli');//'颁发王者口令普通话优秀证书';

        //控制更多游戏图标显示
        $data['moreFlag'] = C('moreGameFlag');
        //$data['moreFlag'] = 0 ;

        //答题界面标语

        $data['ruleText'] = C('gameruletxt');//'总分达到'.(  C('jige')??450  ).'分以上即可获得证书';


        $this->ajaxReturn(['code'=>20000,'msg'=>'success', 'data'=>$data]);
    }

    /*
     * 个人中心api 返回广告和金额
     */
    public function moneyAndAdv(){
    	$field = 'amount,frozen_amount';
    	$info = $this->wx_user->field($field)->where('id=%d',array($this->user_id))->find();
    	$cash = bcsub($info['amount'],$info['frozen_amount'],2);
    	$cash = $cash < 0 ? '0.00' : $cash;
    	$adv = AdvController::instance()->getAdv('user_center');
    	$this->ajaxReturn(['code'=>20000,'msg'=>'success', 'amount'=>$cash, 'adv'=>$adv]);
    }

    /**
     * 释放冻结金额
     * time 2017.11.15
     * author xueweijian
     */
    public function rurnFrozenAmount(){
    	$pid = I('post.pid/d');
    	$enve = M('enve')->field('pay_type')->where(['id'=>$pid])->find();
    	if ($enve['pay_type'] == 2 ) {
    		$res = $this->wx_user->where(array('id'=>$this->user_id))->save(['amount'=>['exp', '`amount`+`frozen_amount`'], 'frozen_amount'=>0 ]);
    		if(empty($res)){
    			$this->ajaxReturn(['code'=>40500,'msg'=>'账户异常']);
    		}
    	}
        
        $this->ajaxReturn(['code'=>20000,'msg'=>'success']);
    }
    
    /*
     * 订阅
     */
    public function subscribe() {
    	$state = I('post.subsc/d');
    	
    	$formIds = I('post.formIds', '', 'htmlspecialchars_decode');
    	if (!empty($formIds)) {
    		FormidController::saveFormId($formIds, $this->openid);
    	}
    	
    	if ($state != 0 && $state != 1) {
    		$this->ajaxReturn(['code'=>40500,'msg'=>'操作失败']);
    	}
    	$res = M('wx_user')->where(['openid'=>$this->openid])->save(['subscribe'=>$state]);
    	if (empty($res)){
    		$this->ajaxReturn(['code'=>40500,'msg'=>'操作失败2']);
    	}
    	$this->ajaxReturn(['code'=>20000, 'state'=>$state, 'msg'=>'订阅成功']);
    }
    
    /*
     * 接受from_id
     */
    public function saveFromId() {
    	$fromIds = I('post.fromids', '', 'strip_tags');
    	foreach ($fromIds as $k=>&$v){
    		$v['openid'] = $this->openid;
    	}
    	M('fromids')->save($fromIds);
    }
    // 排序
    public function sortArrByField(&$array, $field, $desc = false){
        $fieldArr=array();
        foreach ($array as $k => $v) {
            $fieldArr[$k] = $v[$field];
        }
        $sort = $desc == false ? SORT_ASC : SORT_DESC;
        array_multisort($fieldArr, $sort, $array);
    }

    /**
     * 获取个人中心数据
     */
    public function getUserInfo(){
        $quarter = C('quarter')??1;
        $data = M('challenge')->where(" quarter = '$quarter' ")->order('point desc')->select();
        static $user_array = [];
        foreach ($data as $k => $v) {
            if( in_array( $v['user_id'], $user_array )  ){
                unset( $data[$k] );
            }else{
                $user_array[] = $v['user_id'];
            }
        } 

/*        //按发包次数排序
        $this->sortArrByField($data,'point' ,SORT_DESC  );*/

        //添加排行数
        static $rankNum = 1;
        foreach ($data as $kkk => $vvv) {

            if( $kkk == 0 ){
                $data[$kkk]['rank'] = 1;
            }else{
                if( $data[$kkk]['point'] == $data[$kkk-1]['point']  ){
                    $data[$kkk]['rank'] = $rankNum;
                }else{
                    $data[$kkk]['rank'] = $rankNum+1;
                    $rankNum++;
                }
            }
            //拿到用户的排名就break
            if( $vvv['user_id'] == $this->user_id ){
                $my['rank'] = $data[$kkk]['rank'];
                break;
            }
        }
        if( empty($my['rank']) ){
            $my['rank'] = '未上榜';
        }


        $my['maxPoint'] = M('challenge')->where(" quarter = '$quarter' and user_id = '{$this->user_id}'")->max( 'point' )??0;


        $my['all_challenge'] = M('challenge')->where(" quarter = '$quarter' and user_id = '$this->user_id' ")->count();
        $my['all_pass'] = M('challenge')->where(" quarter = '$quarter' and user_id = '$this->user_id' and is_pass = 1")->count();
        
        $this->ajaxReturn(['code'=>20000, 'data'=>$my]);
    }


 }
