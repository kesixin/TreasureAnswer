<?php

/**
 * 语音红包类
 * author universe.h
 */
namespace Api\Controller;

use Common\Controller\AdvController;
use Common\Controller\InterceptController;
use Common\Controller\WeixinController;
use Common\Lib\Baidu\Sample;
use Common\Lib\Queue;
use Common\Lib\WXBizDataCrypt;
use Common\Lib\Wxpay\Wxpay;
use Common\Lib\LCS;
use Think\Exception;
use Admin\Controller\MailerController;
use Common\Lib\iAuth;

class EnveController extends InterceptController {
    
    /*
     * 首页接口
     */
    public function getGlobalInfo() {
        
        $globalInfoService = D('GlobalInfo', 'Service');
       
        $returnData = $globalInfoService->doAction($this->openid);
        
        $this->ajaxReturn(['code'=>20000, 'msg'=>'获取成功', 'data'=>$returnData]);
    }
    
    /**
     * 挑战答题
     *获取每局游戏的标识
     */
    public function getGameFlag(){
        $wxUserModel = M('wx_user');
        try {
            $res = $wxUserModel->where(['openid'=>$this->openid])->setDec('zm_points', 1);
            
            if (empty($res)) {
                $this->ajaxReturn ( [
                    'code' => 40500,
                    'msg' => '数据异常',
                    'ctime' => -1
                ]);
            }
        } catch (Exception $e) {
            $this->ajaxReturn ( [
                'code' => 40500,
                'msg' => '数据异常',
                'ctime' => -1
            ]);
        }
        
        $ctimes = $wxUserModel->where(['openid'=>$this->openid])->getField('zm_points');
        
        $this->ajaxReturn ( [
            'code' => 20000,
            'msg' => '获取成功',
            'flag' => 'flag',
            'ctime' => $ctimes
        ]);
    }
    
    /*
     * 开始游戏
     * 随机获取x道题
     * 
     */
    //TODO  处理获取题库 有些题库给删除的问题  判断今天挑战次数或领奖次数是否达上限
    public function startGame() {
        //exit('aa');
        try {
            /*
            //减去用户挑战次数
            $wxUserModel = M('wx_user');
            $wxUserModel->where(['openid'=>$this->openid])->setDec('zm_points', 1);
            $wxUserModel->where(['openid'=>$this->openid])->setInc('challenge_time', 1);
            //减去用户挑战次数 end*/
            
            $num = C('Gamount');
            $questModel = M('questions');
            //获取题库数量
            $questions = S('questions');
            //$questions = null;
            if (empty($questions)) {
                $questions = $questModel->where(['del'=>0])->select();
                S('questions', $questions, 3600);
            }
            
            if ($num > count($questions)) {
                throw_exception('题库数量不足');
            }
            
            $amount = count($questions);
            //随机五个题目的id
            $i = 0;
            $ids = [];
            $quests = [];
            while ($i < $num){
                $rid = mt_rand(1, $amount) - 1;
                if (!in_array($rid, $ids)) {
                    $ids[] = $rid;
                    $quests[] = $questions[$rid];
                    $i++;
                }
            }
            
            $this->ajaxReturn ( [
                'code' => 20000,
                'msg' => '获取成功',
                'quests' => $quests,
            ]);
            
        }catch (Exception $e){
            $this->ajaxReturn ( [
                'code' => 40500,
                'msg' => '数据异常',
                'quests' => $quests,
            ]);
        }
        
    }
    
    /*
     * 复活获取题目
     */
    public function getOneQuestion(){
        $quests = I('post.quests/s');
        $quests = json_decode($quests);
        
        $ids = [];
        
        foreach ($quests as $v){
            $ids[] = $quests['id'];
        }
        
        $questModel = M('questions');
        //获取题库数量
        $questions = S('questions');
        if (empty($questions)) {
            $questions = $questModel->where(['del'=>1])->select();
            S('questions', $questions);
        }
        $amount = count($questions);
        $i = 0;
        $num = 1;
        while ($i < $num){
            $rid = mt_rand(1, $amount) - 1;
            if (!in_array($rid, $ids)) {
                $this->ajaxReturn ( [
                    'code' => 20000,
                    'msg' => '获取成功',
                    'quests' => $questions[$rid],
                ]);
            }
        }
        
        
    }
    
    /*
     * 支付购买复活卡
     */
    //TODO 获取后台设置复活卡金额  区分是否在游戏中  isPlaying
    public function buyRevive() {
        $isPlaying = I('post.isPlaying/d');
        $amount = C('resCarJE');
        $wxpay = new Wxpay ();
        $out_trade_no = create_order(M('pay_log'),'out_trade_no');
        // 调起支付
        $pay = array (
            'body' => '语音红包支付',
            'out_trade_no' => $out_trade_no,
            'total_fee' => $amount,
            'notify_url' => "https://" . $_SERVER ['HTTP_HOST'] . U ( 'Api/Wxpay/OrderChange' )
        );
        $respay = $wxpay->get_code ( $pay, $this->instance ()->openid );
        $respay = json_decode ( $respay, true );

        /* 支付记录 */
        
        $log_data = [
            'pay_type' => 1,
            'status' => - 1,
            'desc' => '语音红包微信支付',
            'action' => __ACTION__,
            'content' => json_encode ( I('post.'), JSON_UNESCAPED_UNICODE ),
            'add_time' => time (),
            'total_fee' => $amount,
            'out_trade_no' => $out_trade_no,
            'money_type' => 0,
            'openid' => $this->openid,
            'isPlaying'=>$isPlaying
        ];
        
        $res_log = M ( 'pay_log' )->add ( $log_data );
        
        $this->ajaxReturn ( [
            'code' => 20000,
            'msg' => '操作成功',
            'paymentInfo' => $respay,
        ]);
    }
    
    /*
     * 分享获得挑战机会
     * 如果在游戏中不会记录在数据库和增加挑战机会
     */
    public function shareGetTimes() {
//        $post_data = I('post.');
//
          $isPlaying = I('post.isPlaying/d');
//        $shareTicket = I('post.shareTicket/s');
//
//        // 解密信息获取unionid
//        $wXBizDataCrypt = new WXBizDataCrypt ( C ( 'C_APPID' ), $this->session_key );
//        $data = [ ];
//        $errCode = $wXBizDataCrypt->decryptData ( $post_data ['encryptedData'], $post_data ['iv'], $data );
//        if ($errCode != 0) {file_put_contents('shareErr.txt', $this->session_key);
//            $this->ajaxReturn ( [
//                'code' => 40500,
//                'msg' => '操作失败',
//            ]);
//        }
//
//        $data = json_decode ( $data, true );
//
//
        $wxUserModel = M('wx_user');
//        $shareTicketModel = M('sharetickets');
//
//        $count = $shareTicketModel->where(['openid'=>$this->openid, 'shareticket'=>$data['openGId']])->count();
//        if ($count == 0 ) {
//            if ($isPlaying == 0) {
//                $res = $wxUserModel->where(['openid'=>$this->openid])->setInc('zm_points', 1);
//
//                if (empty($res)) {
//                    $this->ajaxReturn ( [
//                        'code' => 40500,
//                        'msg' => '操作失败',
//                    ]);
//                }
//
//                try {
//                    $res = $shareTicketModel->add(['openid'=>$this->openid, 'shareticket'=>$data['openGId'], 'add_time'=>time()]);
//
//                    if (empty($res)) {
//                        throw new Exception('操作失败');
//                    }
//
//                    $this->ajaxReturn ( [
//                        'code' => 20000,
//                        'msg' => '已获得一次挑战机会',
//                        'gid' => $data['openGId'],
//                    ]);
//                }catch (Exception $e){
//                    $this->ajaxReturn ( [
//                        'code' => 40500,
//                        'msg' => '操作失败',
//                    ]);
//                }
//
//            }
//
//
//        }
//
//        if ($isPlaying == 1) {
//            $this->ajaxReturn ( [
//                'code' => 20000,
//                'msg' => '操作成功',
//                'gid' => $data['openGId'],
//            ]);
//        }
//
//        $this->ajaxReturn ( [
//            'code' => 40050,
//            'msg' => '请分享到不同的群',
//        ]);
        if ($isPlaying == 0) {
                $res = $wxUserModel->where(['openid'=>$this->openid])->setInc('zm_points', 1);
        }

        $this->ajaxReturn ( [
                'code' => 20000,
                'msg' => '操作成功',
                //'gid' => $data['openGId'],
            ]);
        
    }   
    /*
     * 使用复活卡
     */
    public function useRevive() {
        $wxUserModel = M('wx_user');
        try {
            $res = $wxUserModel->where(['openid'=>$this->openid])->setDec('revive_time', 1);
            
            if (empty($res)) {
                $this->ajaxReturn ( [
                    'code' => 40500,
                    'msg' => '数据异常',
                    'life' => -1
                ]);
            }
        } catch (Exception $e) {
            $this->ajaxReturn ( [
                'code' => 40500,
                'msg' => '数据异常',
                'life' => -1
            ]);
        }
        
        $revive_time = $wxUserModel->where(['openid'=>$this->openid])->getField('revive_time');
        
        $this->ajaxReturn ( [
            'code' => 20000,
            'msg' => '获取成功',
            'life' => $revive_time
        ]);
    }
    
    
    /*
     * 结果处理
     */
    public function dealResult(){
        //防止单个用户过于频繁访问接口
        $invokeTime = S('dealResult'.$this->user_id);
        $nowTime = time();
        S('dealResult'.$this->user_id, $nowTime);
        if (!empty($invokeTime)) {
            $diffTime = $nowTime - $invokeTime;
            if ($diffTime < 10 ) {
                exit();
            }
        }
        //防止单个用户过于频繁访问接口 end
        
        $is_pass = I('post.is_pass/d');
        $use_revive_time = I('post.money/d');
        $share_revive_time = I('post.share_revive_time/d');
        $challengeModel = M('challenge');
        //$post_data = $challengeModel->create($post_data);
        $getInfo = [];
        //判断是否答题过关 过关则送一个礼品
        if ($is_pass == 1) {
            $prizeInfo= $this->getPrize();
            $post_data['prize_id'] = $prizeInfo['id'];
        }
        
        //数据插入挑战记录
        $challengeData = [
            'user_id' =>  $this->user_id,
            'create_time' => time(),
            'openid'  =>$this->openid,
            'is_pass' =>$is_pass,
            'share_time'=>$share_revive_time,
            'revive_time'=>$use_revive_time,
            'prize_id'=>$prizeInfo['id'],
        ];
        
        
        $res = $challengeModel->add( $challengeData );
        
        if (empty($res) ) {
            $this->ajaxReturn(['code'=>40500, 'msg'=>'操作失败' ]);
        }
        
        $this->ajaxReturn(['code'=>20000, 'msg'=>'操作成功', 'is_pass' =>$is_pass, 'challengeId'=>$res, 'prize'=>['src'=>'data/upload/'.$prizeInfo['prize_img'], 'text'=>$prizeInfo['prize_name']]  ]);
    }
    
    /*
     * 随机分配礼物
     */
    private function getPrize(){
        $prizeModel = M('prizes');
        
        $prizes = $prizeModel->where(['prize_num'=>['exp', '>0']])->select();
        
        $count = count($prizes); 
        
        $prizeIndex = mt_rand(0, $count-1);
        
        
        
        return $prizes[$prizeIndex];
    }
    
    /*
     * 排行榜
     * type   1智力排行榜  2毅力排行榜
     */
    public function getRankData(){
        $type = I('post.type/d');
        
        $page_size = 10;
        $page = I('post.page/d');
        if (empty($page)) {
            $page = 1;
        }
        $offset = $page - 1;
        $firstRow = $offset * $page_size;
        
        $model = M();
        //$pid = M('settings')->where(['key'=>'cpid'])->getField('value');
        $sql = 'select openid,nick_name,head_img,(select count(id) FROM `hb_challenge` WHERE prize_id>0 and openid=a.openid ) as prize_amount from `hb_wx_user` a ORDER BY prize_amount desc,id asc limit '.$firstRow.','.$page_size;
        $sql2 = 'select openid,nick_name,head_img,(select count(id) FROM `hb_challenge` WHERE openid=a.openid ) as challenge_times from `hb_wx_user` a ORDER BY challenge_times desc,id asc limit '.$firstRow.','.$page_size;
  
        
        //查询当前排名
        $rank = [];
        $prize_amount = 0;
        if ($page == 1) {
            $mysql1 = 'SELECT * FROM (SELECT (@rownum:=@rownum+1) AS rownum, a.* FROM (select @rownum:=0,u.*,(SELECT count(id) FROM `hb_challenge` WHERE openid=u.openid and prize_id>0 ) as prize_amount from `hb_wx_user` as u ORDER BY prize_amount desc,id asc ) a) as b where id = ';
            
            $mysql2 = 'SELECT * FROM (SELECT (@rownum:=@rownum+1) AS rownum, a.* FROM (select @rownum:=0,u.*,(SELECT count(id) FROM `hb_challenge` WHERE openid=u.openid ) as challenge_times from `hb_wx_user` as u ORDER BY challenge_times desc,id asc ) a) as b where id = ';
            
            $myrank['rank1'] = $model->query($mysql1.$this->user_id);
            $myrank['rank2'] = $model->query($mysql2.$this->user_id);
            
            $myrank['rank1'] = $myrank['rank1'][0]['rownum'];//当前用户排名
            $myrank['rank2'] = $myrank['rank2'][0]['rownum'];//当前用户排名
            
            $challengeModel = M('challenge');
            $prize_amount = $challengeModel->where(['openid'=>$this->openid, 'is_pass'=>1, 'prize_id'=>['exp', '>0']])->count();//当前用户领到的娃娃数量
            $challengeTimes = $challengeModel->where(['openid'=>$this->openid ])->count();//当前用户总挑战次数
            
            $rank['list1'] = $model->query($sql, true);
            $rank['list2'] = $model->query($sql2, true);
            
            //昵称解码
            foreach ($rank['list1'] as $k => &$v){
                $v['nick_name'] = json_decode($v['nick_name']);
            }
            
            //昵称解码
            foreach ($rank['list2'] as $k => &$v){
                $v['nick_name'] = json_decode($v['nick_name']);
            }
            
            $this->ajaxReturn ( [
                'code' => 20000,
                'msg' => '获取成功',
                'data' => $rank,
                'myRank' => $myrank,
                'myPrizeAmount'=>$prize_amount,
                'challengeTimes'=>$challengeTimes,
                'page'=>$page
            ] );
        }
        
        
        if ($type == 2) {
            $sql = $sql2;
        }
        
        $res = $model->query($sql, true);
        //昵称解码
        foreach ($res as $k => &$v){
            $v['nick_name'] = json_decode($v['nick_name']);
        }
        
        $this->ajaxReturn ( [
            'code' => 20000,
            'msg' => '获取成功',
            'data' => $res,
            'myRank' => $rank,
            'myPrizeAmount'=>$prize_amount,
            'page'=>$page,
            'type'=>$type
        ] );
        
    }
    
    /*
     * 奖品列表
     */
    public function prizeList(){
        $page = I('post.page/d');
        if (empty($page) || $page < 0) {
            $page = 1;
        }
        $pageSize = 5;
        
        $prizesModel = M('prizes');
        
        $prizeList = $prizesModel->field('prize_name,prize_img')->where(['del'=>['exp', '=0']],['prize_num'=>['exp', '>0']])->page($page, $pageSize)->select();
        
        foreach ($prizeList as &$v){
            $v['prize_img'] = 'https://'.$_SERVER['HTTP_HOST'].C('TMPL_PARSE_STRING.__UPLOAD__').$v['prize_img'];
        }
        
        $this->ajaxReturn ( [
            'code' => 20000,
            'msg' => '操作成功',
            'data'=>$prizeList
        ] );
    }
    
    /*
     * 保存物流信息
     */
    
    public function saveExpressInfo() {
        $challengeId = I('post.challengeId/d');
        $name = I('post.name/s');
        $phone = I('post.phone/s');
        $address = I('post.address/s');
        
        $data = [
            'challengeId'=>$challengeId,
            'name'=>$name,
            'phone'=>$phone,
            'address'=>$address,
            'create_time'=>time(),
            'openid'=>$this->openid,
            'user_id'=>$this->user_id,
        ];
        
        $express_infoModel = M('express_info');
        $express_infoModel->startTrans();
        try {
            $res = M('express_info')->add($data);
            
            if (empty($res)) {
                throw_exception('操作失败');
            }
            
            $res = M('challenge')->where(['id'=>$challengeId])->setField('status', 1);
            
            if (empty($res)) {
                throw_exception('操作失败');
            }
            
        }catch (Exception $e){
            $this->ajaxReturn ( [
                'code' => 40500,
                'msg' => $e->getMessage(),
            ] );
        }
        
        $express_infoModel->commit();
        $this->ajaxReturn ( [
            'code' => 20000,
            'msg' => '操作成功',
        ] );
        
    }
    
    /*
     * 我的记录
     */
    //TODO 复活卡金额
    public function myRecord() {
        
        $page = I('post.page/d');
        $pageSize = 5;
        
        if (empty($page)) {
            $page = 1;
        }
        
        $challengeModel = M('challenge');
        $userModel = M('wx_user');
        $appListModel = M('applist');
        
        //推荐小程序列表
        $applist = $appListModel->where(['status'=>1])->page($page, $pageSize)->select();
        
        foreach ($applist as &$v){
            $v['logo'] = 'https://'.$_SERVER['HTTP_HOST'].C('TMPL_PARSE_STRING.__UPLOAD__').$v['logo'];
        }
        
        if ($page > 1) {
            $this->ajaxReturn ( [
                'code' => 20000,
                'msg' => '操作成功',
                'data'=>['applist'=>$applist, 'page'=>$page ]
            ] );
        }
        
        //我获得的娃娃数量
        $prizeAmount = $challengeModel->where(['openid'=>$this->openid, 'prize_id'=>['exp', '>0']])->count();
        //复活卡数量
        $challenge_time = $challengeModel->where(['openid'=>$this->openid ])->count();
        //挑战次数和复活卡次数
        $userInfo = $userModel->field('zm_points,revive_time')->where(['openid'=>$this->openid])->find();
        
        //复活卡金额
        $reviveMoney = 1;
        
        $this->ajaxReturn ( [
            'code' => 20000,
            'msg' => '操作成功',
            'data'=>['prizeAmount'=>$prizeAmount, 'challenge_time'=>$challenge_time, 'play_time'=>$userInfo['zm_points'], 'revive_time'=>$userInfo['revive_time'], 'reviveMoney'=>$reviveMoney, 'applist'=>$applist, 'page'=>$page ]
        ] );
    }
    
    
    /*
     * 奖品记录
     */
    public function prizeRecord() {
        
        $page = I('post.page/d');
        $pageSize = 10;
        if (empty($page)) {
            $page = 1;
        }
        
        $challengeModel = M('challenge');
        
        $prizeList = $challengeModel->field('a.id,FROM_UNIXTIME(a.create_time,\'%Y-%m-%d %H:%i\') create_time,b.prize_name,b.prize_img,a.status')->alias('a')->join('LEFT JOIN __PRIZES__ b ON a.prize_id = b.id ')->where(['openid'=>$this->openid, 'prize_id'=>['exp', '>0']])->order('create_time desc')->page($page, $pageSize)->select();
        
        foreach ($prizeList as &$v){
            $v['prize_img'] = 'https://'.$_SERVER['HTTP_HOST'].C('TMPL_PARSE_STRING.__UPLOAD__').$v['prize_img'];
        }
        
        $this->ajaxReturn ( [
            'code' => 20000,
            'msg' => '操作成功',
            'data'=>['prizeList'=>$prizeList ]
        ] );
    }
    
    /*
     * 兑换3次挑战机会
     */
    public function exchangeChallengeTimes() {
        $challengeId = I('post.challengeId/d');
        
        $challengeModel = M('challenge');
        $userModel = M('wx_user');
        
        /*开始事务*/
        $challengeModel->startTrans();
        
        try {
            /*挑战记录设置已兑换3次挑战机会*/
            $res = $challengeModel->where(['id'=>$challengeId])->setInc('status', 2);
            if (empty($res)) {
                throw_exception('兑换失败');
            }
            /*用户增加三次挑战机会*/
            $res = $userModel->where(['openid'=>$this->openid ])->setInc('zm_points', C('excAmount'));
            if (empty($res)) {
                throw_exception('兑换失败');
            }
            
            $challengeModel->commit();
        }catch (Exception $e){
            $challengeModel->rollback();
            $this->ajaxReturn ( [
                'code' => 40500,
                'msg' => '兑换失败',
            ] );
        }
        
        $ctime = $userModel->where(['openid'=>$this->openid ])->getField('zm_points');
        $this->ajaxReturn ( [
            'code' => 20000,
            'msg' => '操作成功',
            'ctime'=>$ctime
        ] );
        
    }
    
    /*
     * 群内排行
     */
    public function rankInGroup() {
        $inRankPage = I('post.inRankPage/d');
        $post_data = I('post.');
        
        // 解密信息获取unionid
         $wXBizDataCrypt = new WXBizDataCrypt ( C ( 'C_APPID' ), $this->session_key );
         $data = [ ];
         $errCode = $wXBizDataCrypt->decryptData ( $post_data ['encryptedData'], $post_data ['iv'], $data );
         
         if ($errCode == 0) {
            $data = json_decode ( $data, true );
            $gid_relate_openidModel = M('gid_relate_openid');
            $info = $gid_relate_openidModel->where(['opengid'=>$data ['openGId'], 'openid'=>$this->openid ])->find();
            if (empty($info)){
                $gid_relate_openidModel->add(['opengid'=>$data ['openGId'], 'openid'=>$this->openid, 'create_time'=>time() ]);
            }
            
            if (empty($inRankPage)){
                $this->ajaxReturn ( [
                    'code' => 20000,
                    'msg' => '操作成功',
                ] );
            }else{
                $page = I('post.page/d');
                $pageSize = 4;
                if (empty($page)) {
                    $page = 1;
                }
                $offset = $page - 1;
                $firstRow = $offset * $pageSize;
                
                //返回群内排行
                $sql = 'select b.nick_name,b.head_img,(select count(id) FROM `hb_challenge` WHERE prize_id>0 and openid=a.openid ) as prize_amount from `hb_gid_relate_openid` a left join `hb_wx_user` b on a.openid=b.openid where `opengid`="'.$data ['openGId'].'" order by prize_amount desc,a.id asc limit '.$firstRow.','.$pageSize;
                
                $rankList = M()->query($sql);
                
                foreach ($rankList as &$v){
                    $v['nick_name'] = json_decode($v['nick_name']);
                }
                
                //TODO 加多两个返回字段  标题（答对12题随机送娃娃）  总参赛次数  都是首页接口的
                $challengeModel = M('challenge');
                //总挑战次数
                $totalChallenge = $challengeModel->count();
                
                $right = '答对12题随机送娃娃'; //标题
                
                $this->ajaxReturn ( [
                    'code' => 20000,
                    'msg' => '操作成功',
                    'data' => ['rankList'=>$rankList, 'total'=>$totalChallenge, 'right'=>$right ],
                ] );
            }
            
         } else {
             $this->ajaxReturn ( [
                 'code' => 40500,
                 'msg' => '操作失败',
             ] );
         }
    }
    
    
    /*now end*/
	
	/**
	 * 发红包列表
	 * time 2017.9.30
	 */
	public function index() {
		$enve_model = M ( "Enve" );
		$page = I ( 'post.page/d' ) ?: 1;
		$page_size = 10;
		$field = 'id,quest,answer,show_amount,num,receive_num,enve_type,FROM_UNIXTIME(add_time,\'%m月%d日 %H:%i\') add_time';
		$info = $enve_model->field ( $field )->page ( $page, $page_size )->where ( "user_id='%d' and del=0 and pay_status='%d'", array (
				$this->user_id,
				1 
		) )->order ( 'id desc' )->select ();
		$sum_info = $enve_model->field ( 'sum(show_amount) sum_money' )->where ( "user_id='%d' and del=0 and pay_status=%d", array (
				$this->user_id,
				1 
		) )->find ();
		$sum_info ['sum_num'] = $enve_model->where ( "user_id='%d' and pay_status=%d", array (
				$this->user_id,
				1 
		) )->count ();
		if (! $info) {
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '没有更多了' 
			] );
		}
		$this->ajaxReturn ( [ 
				'code' => 20000,
				'msg' => 'success',
				'data' => $info,
				'sum_info' => $sum_info 
		] );
	}
	
	/**
	 * 领取红包列表
	 * time 2017.9.30
	 */
	public function reciveList() {
		$enve_receive_model = M ( "EnveReceive" );
		$page = I ( 'post.page/d' ) ?: 1;
		$page_size = 10;
		$field = 'pid as id,receive_answer,receive_amount,durat,FROM_UNIXTIME(a.add_time,\'%m月%d日 %H:%i\') add_time,best,enve_type,c.head_img,c.nick_name';
		$info = $enve_receive_model->alias ( 'a' )->join ( 'LEFT JOIN ' . C ( 'DB_PREFIX' ) . 'enve b ON a.pid=b.id' )->join ( 'LEFT JOIN ' . C ( 'DB_PREFIX' ) . 'wx_user c ON b.openid=c.openid' )->field ( $field )->page ( $page, $page_size )->where ( "a.user_id='%d'", array (
				$this->user_id 
		) )->order ( 'id desc' )->select ();
		$sum_info = $enve_receive_model->field ( 'sum(receive_amount) sum_money,sum(receive_num) sum_num' )->where ( "user_id='%d'", array (
				$this->user_id 
		) )->find ();
		
		if (! $info) {
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '没有更多了' 
			] );
		}
		
		foreach ( $info as &$v ) {
			$v ['nick_name'] = json_decode ( $v ['nick_name'] );
		}
		
		$this->ajaxReturn ( [ 
				'code' => 20000,
				'msg' => 'success',
				'data' => $info,
				'sum_info' => $sum_info 
		] );
	}
	
	/**
	 * 添加红包信息
	 * time 2017.10.2
	 */
	public function saveEnve() {
		$post_data = I ( 'post.' );
		if (isset ( $post_data ['pay_status'] )) {
			unset ( $post_data ['pay_status'] );
		}
		$enve_model = D ( "Enve" );
		$wx_user = M ( "WxUser" );
		$post_data = $enve_model->create ( $post_data );
		if (! $post_data) {
			$msg = $enve_model->getError () ?: '系统繁忙';
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => $msg 
			] );
		}
		
		// 判断是否符合最低领取红包金额要求
		if ($post_data ['amount'] < bcmul ( C ( 'RECEIVE_AMOUNT_MIN' ), $post_data ['num'], 2 )) {
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '不符合领红包最低金额要求' 
			] );
		}
		// 判断是否符合最低红包金额要求
		if ($post_data ['amount'] < C ( 'AMOUNT_MIN' )) {
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '不符合红包最低金额要求' 
			] );
		}
		
		// 查询用户余额
		$userAmount = $wx_user->field ( 'amount,frozen_amount' )->where ( "openid='%s'", array (
				$this->openid 
		) )->find ();
		
		// 加上佣金四舍五入
		// 判断是否广告红包+百分之一
		$commission = C ( 'HB_COMMISION' );
		$post_data ['commission'] = calculate_commission ( $post_data ['amount'], $commission );
		/* 红包需要支付金额 */
		$payAmount = bcadd ( $post_data ['amount'], $post_data ['commission'], 2 );
		
		// 用户余额
		$userMoney = $userAmount ['amount'];
		
		// 减去用户余额
		$rpayamount = bcsub ( $userMoney, $payAmount, 2 );
		
		// 开始事务
		$enve_model->startTrans ();
		
		// 解冻用户余额
		// $wx_user->where("openid='%s' and frozen_amount>0", array($this->openid))->save(['amount'=>array('exp', '`amount`+`frozen_amount`'), 'frozen_amount'=>0]);
		// 余额不足的情况
		$res_user = true;
		if ($rpayamount < 0) {
			$wxpay = new Wxpay ();
			$out_trade_no = $post_data ['out_trade_no'];
			// 调起支付
			$pay = array (
					'body' => '语音红包支付',
					'out_trade_no' => $out_trade_no,
					'total_fee' => abs ( $rpayamount ),
					'notify_url' => "https://" . $_SERVER ['HTTP_HOST'] . U ( 'Api/Wxpay/OrderChange' ) 
			);
			$respay = $wxpay->get_code ( $pay, $this->instance ()->openid );
			$respay = json_decode ( $respay, true );
			// 支付类型
			$respay ['pay_type'] = 1;
			if ($userMoney > 0) {
				$respay ['pay_type'] = 2;
			}
			/* 支付记录 */
			$log_data = [ ];
			// 冻结用户所有余额
			if ($userAmount ['amount'] > 0) {
				$res_user = $wx_user->where ( "openid='%s' and amount>0", array (
						$this->openid 
				) )->save ( [ 
						'frozen_amount' => [ 
								'exp',
								'`amount`+`frozen_amount`' 
						],
						'amount' => 0 
				] );
				if (empty ( $res_user )) {
					$enve_model->rollback ();
					$this->ajaxReturn ( [ 
							'code' => 40500,
							'msg' => '账号异常' 
					] );
				}
			}
			$log_data = [ 
					'pay_type' => $respay ['pay_type'],
					'status' => - 1,
					'desc' => '语音红包微信支付',
					'action' => __ACTION__,
					'content' => json_encode ( $post_data, JSON_UNESCAPED_UNICODE ),
					'add_time' => time (),
					'total_fee' => abs ( $rpayamount ),
					'amount' => $userAmount ['amount'],
					'out_trade_no' => $out_trade_no,
					'money_type' => 0,
					'commission' => $post_data ['commission'] 
			];
			
			$post_data ['pay_type'] = $respay ['pay_type'];
			$post_data ['prepay_id'] = trim ( substr ( $respay ['package'], strpos ( $respay ['package'], '=' ) + 1 ) );
			/* 判断预支付id是否能获取到或长度是否过短 */
			if (empty ( $post_data ['prepay_id'] ) || strlen ( $post_data ['prepay_id'] ) < 11) {
				$this->ajaxReturn ( [ 
						'code' => 40500,
						'msg' => '支付异常' 
				] );
			}
			// unset($respay['prepay_id']);
		} else { // 余额支付
			$res_user = $wx_user->where ( "openid='%s'", array (
					$this->openid 
			) )->setDec ( 'amount', $payAmount ); // 修改用户余额
			if (! $res_user) {
				$enve_model->rollback ();
				$this->ajaxReturn ( [ 
						'code' => 40500,
						'msg' => '口令生成失败(user)' 
				] );
			}
			// 插log
			$log_data = [ 
					'pay_type' => 0,
					'amount' => $payAmount,
					'pay_status' => 1,
					'desc' => '余额支付',
					'action' => __ACTION__,
					'content' => json_encode ( $post_data, JSON_UNESCAPED_UNICODE ),
					'add_time' => time (),
					'money_type' => 0,
					'commission' => $post_data ['commission'] 
			];
			
			$respay ['pay_type'] = $post_data ['pay_type'] = 0;
			$post_data ['pay_status'] = 1;
			$post_data ['prepay_id'] = '';
		}
		
		// 红包入库
		$post_data ['openid'] = $this->openid;
		// 判断是否需要审核红包
		if (C ( 'KL_AUDIT' ) == 0) {
			$post_data ['audit'] = 1;
		}
		$enve_model->add ( $post_data );
		$last_id = $enve_model->getLastInsID ();
		if (! $last_id) {
			$enve_model->rollback ();
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '口令生成失败(in)' 
			] );
		}
		
		/* 微信支付或余额支付插入支付log */
		$log_data ['pid'] = $last_id;
		$log_data ['openid'] = $this->openid;
		$res_log = M ( 'pay_log' )->add ( $log_data );
		if (! $res_log) {
			$enve_model->rollback ();
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '口令生成失败(log)' 
			] );
		}
		
		// 新建一个队列将分出来的红包加入
		$queue = Queue::getInstance ( 'enve' . $last_id );
		$queue->flushQueue ();
		$res = $this->redpacket ( $queue, $last_id, $post_data ['amount'], $post_data ['num'], C ( 'RECEIVE_AMOUNT_MIN' ) );
		if (! $res) {
			$enve_model->rollback ();
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '单个红包不能低于最小金额' 
			] );
		}
		// 如果队列长度跟红包数量不对，则生成红包失败
		if ($queue->len () != $post_data ['num']) {
			$enve_model->rollback ();
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '口令生成失败(队列)' 
			] );
		}
		
		// 提交事务
		$enve_model->commit ();
		$respay ['quest'] = $post_data ['enve_type'] == 0 ? $post_data ['quest'] : $post_data ['answer'];
		$respay ['enve_type'] = $post_data ['enve_type'];
		$respay ['prepay_id'] = $post_data ['prepay_id'];
		$respay ['pid'] = $last_id;
		$respay ['audit'] = isset ( $post_data ['audit'] ) ? 1 : 0;
		$this->ajaxReturn ( [ 
				'code' => 20000,
				'msg' => 'success',
				'data' => $respay 
		] );
	}
	
	/*
	 * 广场红包列表
	 */
	function enve2SquareList() {
		$post_data = I ( 'post.' );
		$page = I ( 'post.page/d' ) ?: 1;
		$page_size = 10;
		
		if ($page > 1) {
			$enveList_s = S ( 'enveList' . $this->openid );
			if (empty ( $enveList_s )) {
				$this->ajaxReturn ( [ 
						'code' => 40500,
						'msg' => '没有更多了' 
				] );
			}
			$enveList_s = unserialize ( $enveList_s );
			$offset = $page - 1;
			$enveList = array_slice ( $enveList_s, $offset * $page_size, $page_size );
			
			if (empty ( $enveList )) {
				$this->ajaxReturn ( [ 
						'code' => 40500,
						'msg' => '没有更多了' 
				] );
			}
			
			$this->ajaxReturn ( [ 
					'code' => 20000,
					'msg' => 'success',
					'data' => $enveList 
			] );
		}
		
		// $conditions = array('pay_status'=>1, 'a.amount'=>array('gt',0), 'share2square'=>1, 'del'=>0, 'audit'=>1, 'be_overdue'=>0);
		$conditions = array (
				'pay_status' => 1,
				'share2square' => 1,
				'del' => 0,
				'audit' => 1,
				'be_overdue' => 0 
		);
		// 默认最新发布排序
		$orderStr = 'id desc';
		if (isset ( $post_data ['orderby'] ) && ! empty ( $post_data ['orderby'] )) {
			$orderbyArr = array (
					'show_amount_asc' => 'show_amount asc',
					'show_amount_desc' => 'show_amount desc',
					'add_time_asc' => 'add_time asc',
					'add_time_desc' => 'add_time desc' 
			);
			$ordertmp = $orderbyArr [$post_data ['orderby']];
			if (! empty ( $ordertmp )) {
				$orderStr = $ordertmp;
			}
		}
		
		$enveModel = M ( 'enve' );
		$field = 'a.quest,a.num,a.receive_num,a.id,a.show_amount,FROM_UNIXTIME(a.add_time,\'%m月%d日 %H:%i\') add_time,b.head_img,b.user_name,a.enve_type';
		$enveList = $enveModel->alias ( 'a' )->join ( 'LEFT JOIN ' . C ( 'DB_PREFIX' ) . 'wx_user b ON a.user_id=b.id' )->field ( $field )->where ( $conditions )->order ( $orderStr )->select ();
		
		if (empty ( $enveList )) {
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '没有更多了' 
			] );
		}
		
		foreach ( $enveList as &$v ) {
			$v ['user_name'] = json_decode ( $v ['user_name'] );
		}
		
		$enveLists = array_slice ( $enveList, 0, $page_size );
		
		S ( 'enveList' . $this->openid, serialize ( $enveList ) );
		
		$this->ajaxReturn ( [ 
				'code' => 20000,
				'msg' => 'success',
				'data' => $enveLists,
		] );
	}
	
	/**
	 * post 参数 前端获取到的respay中的package和pid
	 * 前端确认是否支付成功，支付成功发送生成口令通知
	 */
	public function sendCreateEnveNotify() {
		$post_data = I ( 'post.' );
		// 发送消息
		$enveStr = '口令红包“' . $post_data ['quest'] . '”';
		/*
		 * switch ($post_data['enve_type']) {
		 * case 1:
		 * $enveStr = '祝福红包';
		 * break;
		 *
		 * case 2:
		 * $enveStr = '问答红包“'.$post_data['quest'].'”';
		 * break;
		 * }
		 */
		$tplData = [ 
				'keyword1' => [ 
						'value' => $enveStr,
						'color' => '#173177' 
				],
				'keyword2' => [ 
						'value' => date ( 'm月d日 H:i' ),
						'color' => '#173177' 
				],
				'keyword3' => [ 
						'value' => '点击此处查看红包详情',
						'color' => '#173177' 
				] 
		];
		$param = [ 
				'touser' => $this->openid,
				'template_id' => C ( 'news_tpl_send' ),
				'page' => 'pages/recordDetails/recordDetails?pid=' . $post_data ['pid'],
				'form_id' => $post_data ['prepay_id'],
				'data' => $tplData 
		];
		// $weixinController = new WeixinController();
		// $weixinController -> send_template($param);
		WeixinController::instance ()->send_template ( $param );
	}
	/**
	 * 领取红包
	 * time 2017.9.30
	 */
	public function saveEnveReceive() {
		$post_data = I ( 'post.' );
		$pid = I ( 'post.pid/d' );
		// 红包信息
		$enve_model = M ( "Enve" );
		
		$field = 'quest,quest_py,answer,answer_py,openid,amount,num,receive_num,form_id,prepay_id,be_overdue,add_time,enve_type';
		$info = $enve_model->where ( "id='%d' and pay_status=%d and del=0", array (
				$pid,
				1 
		) )->find ();
		
		if (! $info) {
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '不是有效红包!' 
			] );
		}
		if ($info ['be_overdue'] == 1) {
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '红包已失效!' 
			] );
		}
		
		// 检测是否领取过红包
		$enve_receive_model = D ( "EnveReceive" );
		$count = $enve_receive_model->where ( "pid=%d and user_id=%d", array (
				$pid,
				$this->user_id 
		) )->count ();
		if ($count > 0) {
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '您已领此取过红包' 
			] );
		}
		
		/* 语音识别 */
		$post_data ['voice_url'] = trim ( $post_data ['voice_url'], '/' );
		
		/* 防作弊 */
		$res = M ( 'enve_receive' )->where ( [ 
				'voice_url' => $post_data ['voice_url'] 
		] )->find ();
		if (! empty ( $res )) {
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '作弊可耻' 
			] );
		}
		/*
		 * $path = $post_data['voice_url'];
		 * $data = file_get_contents($path);
		 * $base64 = base64_encode($data);
		 *
		 * // 设置请求数据
		 * $appkey = 'vdkNYGsakm50OMts';
		 * $params = array(
		 * 'app_id' => '1106633073',
		 * 'format' => '4',
		 * 'rate' => '16000',
		 * 'speech' => $base64,
		 * 'time_stamp' => strval(time()),
		 * 'nonce_str' => strval(rand()),
		 * 'sign' => '',
		 * );
		 * $params['sign'] = $this->getReqSign($params, $appkey);
		 *
		 * // 执行API调用
		 * $url = 'https://api.ai.qq.com/fcgi-bin/aai/aai_asr';
		 * $response = $this->doHttpPost($url, $params);
		 * $response = json_decode($response, true);
		 * if ($response['ret'] > 0) {
		 * $this->ajaxReturn(['code'=>40500, 'msg'=>'识别失败']);
		 * }
		 * $userTalk = $response['data']['text'];
		 */
		// 调用百度语音识别接口转文字start
		$sample = new Sample ();
		// $post_data['voice_url'] = trim($post_data['voice_url'],'&quot;');
		// 获取识别结果
		$aa = $resText = $sample->identify ( $post_data );
		$resText = json_decode ( $resText, true );
		$sampleText = str_replace ( ',', '', $resText ['result'] [0] );
		
		$userTalk = strtolower ( $sampleText );
		/* 语音识别 end */
		
		if (empty ( $userTalk )) {
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '您没有说话哟' 
			] );
		}
		/*
		 * if ($info['enve_type'] == 2) {
		 * $info['quest'] = $info['answer'];
		 * $info['quest_py'] = $info['answer_py'];
		 * }
		 */
		
		// 判断字数是否一致
		$diff = abs(mb_strlen ( $info ['quest'] ) - mb_strlen ( $userTalk ));
		if ( $diff > 4 ) {
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '语音不正确',
					'identify' => $userTalk,
					'quest' => $info ['quest'] 
			] );
		}
		$lcs = new LCS ();
		$similarPercent = $lcs->getSimilar ( $info ['quest'], $userTalk );
		if ($similarPercent < 0.8) {
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '语音不正确',
					'identify' => $userTalk,
					'quest' => $info ['quest'],
					'percent' => $similarPercent 
			] );
		}
		/*
		 * $py = new Pinyin ();
		 * $userTalkpy = $py->getPY ( $userTalk );
		 * $percent = 0;
		 * similar_text ( $userTalkpy, $info ['quest_py'], $percent );
		 * if ($percent < 80) {
		 * $this->ajaxReturn ( [
		 * 'code' => 40500,
		 * 'msg' => '语音不正确',
		 * 'identify' => $userTalk,
		 * 'quest' => $info ['quest'],
		 * 'percent' => $percent
		 * ] );
		 * }
		 */
		
		// 通过判断后领取红包
		$queue = Queue::getInstance ( 'enve' . $pid );
		
		$receive_amount = $queue->pop ();
		if (empty ( $receive_amount )) {
			
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '哎呀你手慢了!' 
			] );
		}
		
		// 插入领红包选项检测
		$post_data ['receive_amount'] = $receive_amount;
		$post_data = $enve_receive_model->create ( $post_data );
		if (! $post_data) {
			$queue->push ( $receive_amount );
			$msg = $enve_receive_model->getError () ?: '系统繁忙';
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => $msg 
			] );
		}
		;
		
		// 开始事务
		M ()->startTrans ();
		// 更新红包数量和金额
		$data = array (
				'amount' => array (
						'exp',
						'amount-' . $receive_amount 
				),
				'receive_num' => array (
						'exp',
						'receive_num+1' 
				),
				'update_time' => time () 
		);
		$res = $enve_model->where ( 'id=' . $pid )->save ( $data );
		if (! $res) {
			$queue->push ( $receive_amount );
			$enve_model->rollback ();
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '1红包领取失败!（en）' 
			] );
		}
		
		// 领取表入库
		$last_id = $enve_receive_model->add ( $post_data );
		if (empty ( $last_id )) {
			$queue->push ( $receive_amount );
			$enve_receive_model->rollback ();
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '2红包领取失败!(rece)' 
			] );
		}
		
		// 更新用户余额
		try {
			$res_user = M ( 'WxUser' )->where ( 'openid="%s"', [ 
					$this->openid 
			] )->setInc ( 'amount', $receive_amount ); // 增加money到余额
			                                                                                                   // 如果领取自己的红包不消耗芝麻分
			
			if (empty ( $res_user )) {
				$queue->push ( $receive_amount );
				M ( 'WxUser' )->rollback ();
				$this->ajaxReturn ( [ 
						'code' => 40500,
						'msg' => '33红包领取失败!(user)' 
				] );
			}
			
		} catch ( \Exception $e ) {
			$queue->push ( $receive_amount );
			M ( 'WxUser' )->rollback ();
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '4红包领取失败!(user)' 
			] );
		}
		
		// 插入领红包入账记录
		$log_data = [ 
				'pay_type' => - 1,
				'amount' => $receive_amount,
				'status' => 0,
				'desc' => get_log_tpl ( 'receive_enve' ),
				'action' => __ACTION__,
				'add_time' => time (),
				'finish_time' => time (),
				'money_type' => 2,
				'pid' => $pid,
				'openid' => $this->openid 
		];
		$res_log = M ( 'pay_log' )->add ( $log_data );
		if (! $res_log) {
			$queue->push ( $receive_amount );
			M ( 'pay_log' )->rollback ();
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '5红包领取失败!' 
			] );
		}
		
		S ( $pid, null );
		// 提交事务
		M ()->commit ();
		
		// 判断如果红包已经领完了 发送通知
		$enveInfo = $enve_model->field ( 'num,receive_num' )->where ( array (
				'id' => $pid 
		) )->find ();
		if ($enveInfo ['num'] == $enveInfo ['receive_num']) {
			$enve_model->where ( array (
					'id' => $pid 
			) )->setField ( 'finish', 1 );
			Queue::getInstance ( 'enve' . $pid )->flushQueue (); // 删除队列
			                                               // 计算领取最多的并更新best标记
			$tempTable = M ( 'enve_receive' )->buildSql ();
			$subsql = M ()->table ( $tempTable . ' tempTable' )->where ( array (
					'pid' => $pid 
			) )->field ( 'id' )->order ( 'receive_amount desc' )->limit ( 0, 1 )->buildSql ();
			M ( 'enve_receive' )->where ( 'id=' . $subsql )->save ( array (
					'best' => 1 
			) );
			
			// 判断支付方式
			$formId = empty ( $info ['prepay_id'] ) ? $info ['form_id'] : $info ['prepay_id'];
			$costTimeStamp = time () - $info ['add_time'];
			$time_s = $costTimeStamp % 60;
			$time_m = floor ( $costTimeStamp / 60 % 60 );
			$time_h = floor ( $costTimeStamp / 60 / 60 % 24 );
			$costTime = '';
			if ($time_h > 0) {
				$time_m = $time_m < 10 ? '0' . $time_m : $time_m;
				$time_s = $time_s < 10 ? '0' . $time_s : $time_s;
				$costTime = $time_h . '小时' . $time_m . '分' . $time_s . '秒';
			} else if ($time_m > 0) {
				$time_s = $time_s < 10 ? '0' . $time_s : $time_s;
				$costTime = $time_m . '分' . $time_s . '秒';
			} else {
				$costTime = $time_s . '秒';
			}
			
			/*
			 * 判断红包类型显示内容
			 */
			$content = '语音口令 “' . $info ['quest'] . '”';
			
			// 发送消息
			$tplData = [ 
					'keyword1' => [ 
							'value' => $content,
							'color' => '#173177' 
					],
					'keyword2' => [ 
							'value' => $costTime . '内被抢完',
							'color' => '#173177' 
					],
					'keyword3' => [ 
							'value' => '点击此处查看口令红包详情',
							'color' => '#173177' 
					] 
			];
			$param = [ 
					'touser' => $info ['openid'],
					'template_id' => C ( 'news_tpl_finish' ),
					'page' => 'pages/recordDetails/recordDetails?pid=' . $pid,
					'form_id' => $formId,
					'data' => $tplData 
			];
			WeixinController::instance ()->send_template ( $param );
		}
		$this->ajaxReturn ( [ 
				'code' => 20000,
				'msg' => 'success',
				'data' => [ 
						'id' => $last_id,
						'amount' => $receive_amount 
				] 
		] );
	}
	
	/**
	 * 详情
	 * time 2017.10.1
	 */
	public function enveDetail() {
		$id = I ( 'post.id/d' );
		$enve_model = M ( "Enve" );
		$wx_model = M ( "WxUser" );
		$field = 'voice_url,voice_dura,quest,user_id,show_amount,be_overdue,amount,num,receive_num,add_time,update_time,del,audit,openid,enve_type';
		$info = $enve_model->field ( $field )->where ( "id='%d' and del=0 and pay_status=%d", array (
				$id,
				1 
		) )->find ();
		$userInfo = $wx_model->field ( 'nick_name,head_img' )->where ( array (
				'openid' => $info ['openid'] 
		) )->find ();
		$userInfo ['nick_name'] = json_decode ( $userInfo ['nick_name'] );
		
		if (empty ( $info ) || empty ( $userInfo )) {
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '红包失效' 
			] );
		}
		
		$info += $userInfo;
		// 红包是否被领完 领完则显示时长
		$info ['status'] = true;
		if ($info ['num'] == $info ['receive_num']) {
			$info ['status'] = false;
			
			$costTimeStamp = $info ['update_time'] - $info ['add_time'];
			$time_s = $costTimeStamp % 60;
			$time_m = floor ( $costTimeStamp / 60 % 60 );
			$time_h = floor ( $costTimeStamp / 60 / 60 % 24 );
			
			if ($time_h > 0) {
				$time_m = $time_m < 10 ? '0' . $time_m : $time_m;
				$time_s = $time_s < 10 ? '0' . $time_s : $time_s;
				$info ['costTime'] = $time_h . '小时' . $time_m . '分' . $time_s . '秒';
			} else if ($time_m > 0) {
				$time_s = $time_s < 10 ? '0' . $time_s : $time_s;
				$info ['costTime'] = $time_m . '分' . $time_s . '秒';
			} else {
				$info ['costTime'] = $time_s . '秒';
			}
		}
		
		$receive_model = M ( 'enve_receive' );
		$field = 'best,receive_answer,user_id,nick_name,head_img,receive_amount,receive_num,voice_url,durat,FROM_UNIXTIME(add_time,\'%m月%d日 %H:%i\') add_time';
		
		$info ['receive'] = $receive_model->field ( $field )->order ( 'add_time asc' )->where ( array (
				'pid' => $id 
		) )->select ();
		foreach ( $info ['receive'] as &$v ) {
			if ($v ['user_id'] == $this->user_id) {
				$info ['recive_status'] = true;
				$info ['receive_amount'] = $v ['receive_amount'];
			}
			;
			$v ['nick_name'] = json_decode ( $v ['nick_name'] );
		}
		/* 判断是否红包主 */
		$info ['owner'] = 0;
		if ($this->user_id == $info ['user_id']) {
			$info ['owner'] = 1;
		}
		$this->ajaxReturn ( [ 
				'code' => 20000,
				'msg' => 'success',
				'data' => $info 
		] );
	}
	
	/**
	 * 主题列表
	 * time 2017.10.9
	 */
	public function getTheme() {
		$type = I ( 'type/d', 1 );
		$res = M ( 'Theme' )->where ( 'type=%d', array (
				$type 
		) )->select ();
		if (! $res) {
			$this->ajaxReturn ( [ 
					'code' => 20400,
					'msg' => '没有更多数据' 
			] );
		}
		$this->ajaxReturn ( [ 
				'code' => 20000,
				'msg' => 'success',
				'data' => $res 
		] );
	}
	
	/*
	 * 常见问题广告
	 */
	public function getQuesAdv() {
		$adv = AdvController::instance ()->getAdv ( 'cquestion' );
		$this->ajaxReturn ( [ 
				'code' => 20000,
				'msg' => 'success',
				'adv' => $adv 
		] );
	}
	
	/*
	 * 获取提示信息
	 */
	public function getTips() {
		$pos = I ( 'post.pos/s' );
		if (empty ( $pos )) {
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '参数错误' 
			] );
		}
		$tip = M ( 'ad' )->where ( array (
				'ad_name' => $pos 
		) )->find ();
		if (empty ( $pos )) {
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '获取失败' 
			] );
		}
		$this->ajaxReturn ( [ 
				'code' => 20000,
				'msg' => 'success',
				'tips' => $tip ['ad_content'] 
		] );
	}
	
	/*
	 * 获取常见问题
	 */
	public function getFAQ() {
		$faq = M ( 'links' )->field ( 'link_name,link_description' )->order ( 'listorder asc' )->select ();
		if (empty ( $faq )) {
			$this->ajaxReturn ( [ 
					'code' => 40500,
					'msg' => '获取失败' 
			] );
		}
		$this->ajaxReturn ( [ 
				'code' => 20000,
				'msg' => 'success',
				'faqList' => $faq 
		] );
	}
	
	
	
	/*
	 * 分拆红包并进入队列
	 */
	private function redpacket(&$queue, $pid, $total, $num, $min = 0.01) {
		/* 总金额减去保底金额 剩下的随机 */
		$sur = $total * 100 - $num * $min * 100;
		
		$arr = array ();
		
		if ($sur < 0) {
			return false;
		}
		
		for($i = 0; $i < $num; $i ++) {
			
			$max = $sur / ($num - $i) * 2;
			
			$arr1 = $max > 0 ? $this->randFloat () * $max : 0;
			
			$arr1 = floor ( $arr1 );
			
			if ($i == $num - 1) {
				$ones = ($sur + $min * 100) / 100;
			} else {
				$ones = ($arr1 + $min * 100) / 100;
				$sur -= $arr1;
			}
			$arr [] = $ones;
		}
		shuffle ( $arr );
		foreach ( $arr as $v ) {
			$queue->push ( $v );
		}
		return true;
	}
	private function randFloat($min = 0, $max = 1) {
		return $min + mt_rand () / mt_getrandmax () * ($max - $min);
	}
	
	/*首页获取红包信息*/
	public function getEnve(){
	    //通过ID查数据库 没有ID则返回平台红包和id，
	    $pid = I('post.pid/d');
	    //如果没有红包则pid = -1
	    $data['pid'] = $pid;//$pid = 2;//
	    //如果没有pid 则返回平台红包
	    if( empty($pid) || $pid < 0){
	        $data['pid'] = M('settings')->where(['key'=>'cpid'])->getField('value');
	    }
	    
	    //$data['koulin'] = ['第一','第二','第三','第4','第5'];
	    //$data['koulin'] = json_decode( M('enve')->find( $data['pid'] )['quest'] ,true );
	    //红包发送人的信息
	    $data['userInfo'] = ['user_id'=>-1,'user_img'=>'https://hb.gzzh.co/envelop_wzwd/data/upload/wzwd2.jpg','user_nickname'=>C('pt_nick_name'),'user_info'=>C('pt_challenge_txt')];
// 	    if( !$data['koulin'] ){
// 	        $this->ajaxReturn(['code'=>40500, 'msg'=>'获取口令失败','data'=>$data]);
// 	    }
	    $this->ajaxReturn(['code'=>20000, 'msg'=>'获取成功','data'=>$data]);
	}
	
	

    /**
     * 红包领取
     */
    private function getEnveInfo( $pid ){
		// 红包信息
		$enve_model = M ( "Enve" );file_put_contents('cpid.txt', $pid);
		$receive_amount = 0;
		
		$info = $enve_model->where ( "id='%d' ", array (
				$pid
		) )->find ();
		
		if (! $info) {
			return ['code'=>3,'msg'=>'不是有效红包'];
		}
		if ($info ['be_overdue'] == 1) {
			return ['code'=>3,'msg'=>'红包已失效'];
		}
		
		// 检测是否领取过红包
		$enve_receive_model = D ( "EnveReceive" );
		/*
		$count = $enve_receive_model->where ( "pid=%d and user_id=%d", array (
				$pid,
				$this->user_id 
		) )->find();
		if ( $count ) {
			return ['code'=>2,'money'=>$count['receive_amount']];
		}*/
		// 通过判断后领取红包
			//不是平台广告的红包有数量限制
		if( $info['enve_type'] != 1 ){
			$queue = Queue::getInstance ( 'enveb' . $pid );
		
			$receive_amount = $queue->pop ();
			if (empty ( $receive_amount )) {
				return ['code'=>3,'msg'=>$pid.'#来晚一步，赏金被领光了'];
			}
			$post_data ['receive_amount'] = $receive_amount;
		}else if( $info['enve_type'] == 1 ){
			if ($info['num'] == $info['receive_num']) {
				return ['code'=>3,'msg'=>$pid.'来晚一步，赏金被领光了'];
			}
			//平台广告直接领取钱
			//$receive_amount =  $this->pintaiGetMoney( $setp, $second);
			$money = C('hb_amount_min')+ mt_rand() / mt_getrandmax() * (C('hb_amount_max') - C('hb_amount_min'));

			$receive_amount = round($money, 2);
			$post_data ['receive_amount'] = $receive_amount ;
		}
		// 开始事务
		M ()->startTrans ();
		// 更新红包数量和金额
		if( $info['enve_type'] != 1 ){
			$data = array (
				'amount' => array (
						'exp',
						'amount-' . $receive_amount 
				),
				'receive_num' => array (
						'exp',
						'receive_num+1' 
				),
				'update_time' => time () 
			);
		}else  if( $info['enve_type'] == 1 ){
			$data = array (
				'receive_num' => array (
						'exp',
						'receive_num+1' 
				),
				'update_time' => time () 
			);
		}
		$res = $enve_model->where ( 'id=' . $pid )->save ( $data );
		if (! $res) {
			if(  $info['enve_type'] != 1 ){
				$queue->push ( $receive_amount );
			}
			$enve_model->rollback ();
			return ['code'=>-1,'msg'=>'红包领取失败!（en）'];
			
		}
		$post_data['pid'] = $pid;
		$post_data['user_id'] = $this->user_id;
		// 领取表入库
		$last_id = $enve_receive_model->add ( $post_data );
		if (empty ( $last_id )) {
			if(  $info['enve_type'] != 1 ){
				$queue->push ( $receive_amount );
			}
			$enve_receive_model->rollback ();
			return ['code'=>-1,'msg'=>'红包领取失败!（en）'];
		}
		// 更新用户余额
		/*$unionid = M('wxUser')->where( "id = '$this->user_id'" )->field('unionid');
*/
		try {
			
			$payLogData = [
					'status'=>-1,
					'desc'=>'王者问答红包'.$receive_amount.'元',
					'add_time'=>time(),
					'amount'=>$receive_amount,
					'money_type'=>1,
					'openid'=>$this->openid,
					'pid'=>$pid,
			];
			$logId = M('pay_log')->add($payLogData);
			
			//发金额给用户
			$out_trade_no = create_order(M('pay_log'),'out_trade_no',C('CASH_FIX'));
			$data =[
					'openid'=>$this->openid,
					'partner_trade_no'=>$out_trade_no,
					're_user_name'=>'',
					'amount'=> bcmul($receive_amount, 100),
					'desc'=>'王者问答红包',
			];
			
			 $wx =  new Wxpay();
			 $res =  $wx->transfers($data);file_put_contents('tixian.txt', json_encode($res).$data['amount']);
			 if($res['result_code']=='SUCCESS'){
			 	//$this->ajaxReturn(['code'=>40500, 'msg'=>$receive_amount.'提现失败']);
			 	M('pay_log')->where(['id'=>$logId])->save(['status'=>0, 'finish_time'=>time()]);
			 }
			 
			//将余额存入到uuser表

			/*$res_user = M ( 'uuser' )->where ( 'unionid="%s"', [ 
					$unionid 
			] )->setInc ( 'amount', $receive_amount );*/ // 增加money到余额
			//这里要提现
			$res_user = 1;
			if (empty ( $res_user )) {
				if(  $info['enve_type'] != 1 ){
					$queue->push ( $receive_amount );
				}
				$enve_receive_model->rollback ();
				return ['code'=>-1,'msg'=>'33红包领取失败!user'];
			}

		} catch ( \Exception $e ) {
			if(  $info['enve_type'] != 1 ){
					$queue->push ( $receive_amount );
				}
			$enve_receive_model->rollback ();
			return ['code'=>-1,'msg'=>'4红包领取失败!user'];
		}
		// 提交事务
		M()->commit ();

		//领取成功 返回数据
		return ['code'=>1,'msg'=>'领取成功','money'=>$receive_amount];
    }

    /**
     * 获取平台领取到的红包额度
     */
/*
    public function pintaiGetMoney( $point ){
    	//后台设置一条线，超过这条线才有几率获得大额红包
    	$bigLine = C('bigline')??480;
    	if( $point > $bigLine ){
    		//后台设置最高红包今额
    		$top = C('big')??5;
    	}else{
    		$top = C('small')??2;
    	}

    	$money = mt_rand( 1,$top );
    	$money = bcmul( $money , rand( 0.6,0.9 ) ,2 );
    	$money = $money >= 1 ? $money :1.01;
    	return $money;
    }
    */
    public function pintaiGetMoney( $setp, $second ){
    	$linePoint_min = 0;
    	$linePoint_max = 0;
    	if ($setp >= C('stepLevel1') && $second >= C('secondLevel1') && $setp < C('stepLevel2') && $second < C('secondLevel2') ) {
    		$linePoint_min = C('linePoint1_min');
    		$linePoint_max = C('linePoint1_max');
    	}elseif ($setp >= C('stepLevel2') && $second >= C('secondLevel2') && $setp < C('stepLeve3') && $second < C('secondLevel3')){
    		$linePoint_min = C('linePoint2_min');
    		$linePoint_max = C('linePoint2_max');
    	}elseif ($setp >= C('stepLevel3') && $second >= C('secondLevel3') && $setp < C('stepLevel4') && $second < C('secondLevel4')){
    		$linePoint_min = C('linePoint3_min');
    		$linePoint_max = C('linePoint3_max');
    	}elseif ($setp >= C('stepLevel4') && $second >= C('secondLevel4') && $setp < C('stepLevel5') && $second < C('secondLevel5')){
    		$linePoint_min = C('linePoint4_min');
    		$linePoint_max = C('linePoint4_max');
    	}else{
    		$linePoint_min = C('linePoint5_min');
    		$linePoint_max = C('linePoint5_max');
    	}
    	
    	$money = $linePoint_min+ mt_rand() / mt_getrandmax() * ($linePoint_max- $linePoint_min);
    	return round($money, 2);
    }


    

    /**
     * 获取排名信息,并插入排行榜
     */
    public function getRank( $point  ){
    	$quarter = C('quarter')??1;
    	$data = D('challenge')->where(" quarter = '$quarter' ")->order('setp,second asc')->select();
    	static $user_array = [];
    	foreach ($data as $k => $v) {
	 		if( in_array( $v['user_id'], $user_array )  ){
	 			unset( $data[$k] );
	 		}else{
	 			$user_array[] = $v['user_id'];
	 		}
    	} 

    	/*//按发包次数排序
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
            if( $point >= $vvv['point'] ){
            	if( $kkk == 0 ){
					$rank = 1; 
            	}else{
            		$rank = $data[$kkk-1]['rank'];
            	}
            }
        }
        if( empty($rank) ){
        	$rank = ++$rankNum;
        }
        return $rank;
    }


    /**
     * 获取排名信息,并插入排行榜
     *
    public function getRankData(){

    	$quarter = C('quarter')??1;
    	$data = M('challenge')->field('user_id,setp,second')->where(" quarter = '$quarter' ")->order('setp,second asc')->select();
    	static $user_array = [];
    	foreach ($data as $k => $v) {
	 		if( in_array( $v['user_id'], $user_array ) ){
	 			unset( $data[$k] );
	 		}else{
	 			$user_array[] = $v['user_id'];
	 		}
    	} 


    	//按发包次数排序
        $this->sortArrByField($data,'point' ,SORT_DESC  );


        //添加排行数
        static $rankNum = 1;
        foreach ($data as $kkk => $vvv) {

        	$data[$kkk]['sendNum']  = M('challenge')->where(" quarter = '$quarter'  and user_id = '{$vvv['user_id']}'")->count();
        	$info = M('wxUser')->where( "id =  '{$vvv['user_id']}'" )->find();
        	$data[$kkk]['head_img'] = $info['head_img'];
        	$data[$kkk]['user_name'] = json_decode($info['nick_name'],true);

            if( $kkk == 0 ){
                $data[$kkk]['idx'] = 1;
            }else{
                if( $data[$kkk]['point'] == $data[$kkk-1]['point']  ){


                    $data[$kkk]['idx'] = $rankNum;
                }else{
                    $data[$kkk]['idx'] = $rankNum+1;
                    $rankNum++;
                }
                
            }
            if( $vvv['user_id'] == $this->user_id ){
                $my = $data[$kkk];
            }
        }

        //
        if( !$my  ){
        	$my['sendNum'] = 0 ;
        	$my['head_img'] = 'https://hb.gzzh.co/envelop_wz/data/upload/wzkl.jpg' ;
        	$my['user_name'] = '王者口令' ;
        	$my['idx'] = '999+' ;
        	$my['point'] = 0 ;
        }


        $this->ajaxReturn ( [
            'code' => 20000,
            'msg' => '获取成功',
            'data' => $data,
            'my'   => $my
        ] );
    }*/
    
    



    /**
     * 我的挑战列表
     */
    public function getMyChallengeList(){
        $model = M();
        $pid = M('settings')->where(['key'=>'cpid'])->getField('value');
        $page = I('post.page/d');
        if (empty($page)) {
            $page = 1;
        }
        $pageSize = 6;
        $startIndex = ($page-1)*$pageSize;
        $sql = 'select a.openid,b.nick_name,b.head_img,FROM_UNIXTIME(a.add_time,\'%Y-%m-%d %H:%i\') add_time,a.amount from `hb_challenge` a LEFT JOIN `hb_wx_user` b ON a.openid=b.openid WHERE a.openid="'.$this->openid.'" and a.pid='.$pid.' ORDER BY a.add_time DESC limit '.$startIndex.','.$pageSize;
        
        $num = M('challenge')->where(['openid'=>$this->openid])->count();
        $passNum = M('challenge')->where(['openid'=>$this->openid, 'is_pass'=>1])->count();
        $amount = M('challenge')->where(['openid'=>$this->openid, 'is_pass'=>1])->sum('amount');
        $amount = empty($amount) ? 0 : $amount;
        
        $res = $model->query($sql, true);
        //昵称解码
        foreach ($res as $k => &$v){
            $v['nick_name'] = json_decode($v['nick_name']);
            if ($v['openid'] == $this->openid) {
            }
        }
        
        $this->ajaxReturn ( [
            'code' => 20000,
            'msg' => '获取成功',
            'data' => $res,
            'num' => $num,
            'passNum' => $passNum,
            'amount' => $amount
        ] );
    }

    /**
     * 获取挑战详情的领取记录
     */
    public function challengeDetail(){
    	$pid = I('post.pid');

    	$quarter = C('quarter')??1;
    	$data = D('challenge')->field('user_id,point,add_time')->where(" pid = '$pid' and quarter = '{$quarter}' ")->order('point desc')->group('user_id')->select();

    	//按发包次数排序
        $this->sortArrByField($data,'point' ,SORT_DESC  );

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
            if( $vvv['user_id'] == $this->user_id ){
                $my = $data[$kkk];
            }

        }
        if( empty( $my ) ){
        	$returnData['rank'] = ++$rankNum; 
        }else{
        	$returnData['rank'] = $my['rank']; 
        }

        /*//按发包次数排序
        $this->sortArrByField($data,'add_time' ,SORT_DESC  );*/
        $returnData['getList'] = D('challenge')->field('a.user_id,FROM_UNIXTIME(a.add_time,\'%m月%d日 %H:%i\') add_time,a.amount,b.nick_name,b.head_img')->join(' a left join hb_wx_user b on a.user_id = b.id ')->where(" a.pid = '$pid' and a.quarter = '{$quarter}' and a.amount > 0")->order('a.add_time desc')->group('a.user_id')->select();
        //按发包次数排序
        $this->sortArrByField($data,'add_time' ,SORT_DESC  );
        foreach ( $returnData['getList'] as &$v ) {
	        $v['nick_name'] = json_decode($v['nick_name'],true) ;
        }

        $this->ajaxReturn ( [
            'code' => 20000,
            'msg' => '获取成功',
            'data' => $returnData,
        ] );
    }

    
    
    
    
    
    

}
