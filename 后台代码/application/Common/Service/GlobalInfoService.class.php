<?php
namespace Common\Service;
use Common\Model\CommonModel;
class GlobalInfoService extends CommonModel{
    
    // 是否自动检测数据表字段信息
    protected $autoCheckFields  =   false;
    
    public function doAction($openid) {
        $wxUserModel = M('wx_user');
        $sInfo = $wxUserModel->field('revive_time,zm_points')->where(['openid'=>$openid])->find();
        
        $challengeModel = M('challenge');
        //总挑战次数
        $totalChallenge = $challengeModel->count();
        //获取前五个获奖记录
        $prizeList = $challengeModel->field('b.prize_name,c.head_img,c.nick_name')->alias('a')->join('LEFT JOIN __PRIZES__ b ON a.prize_id = b.id ')->join('LEFT JOIN __WX_USER__ c ON a.openid = c.openid ')->where(['a.prize_id'=>['exp', '>0']])->order('a.create_time desc')->limit(0,5)->select();
        //解密昵称
        foreach ($prizeList as &$v){
            $v['nick_name'] = json_decode($v['nick_name']);
        }
        
        //开关
        $options_model = D("Common/Options");
        $cConfig = $options_model->where("option_name='switch_options'")->getField('option_value');
        $cConfig = json_decode($cConfig, true);
        
        $returnData = [
            'rule_text'=>C('rule'),  //规则数组
            'game_text'=>C('explain'),
            'total'=>$totalChallenge, //总参赛挑战次数
            'right'=>'答对12题随机送娃娃', //标题
            //'prize'=>[['text'=>'两只熊熊', 'src'=>'https://hb.gzzh.co/envelop_tzdt/prize1.jpg'],['text'=>'两只兔兔', 'src'=>'https://hb.gzzh.co/envelop_tzdt/prize2.jpg']],   //奖品列表
            'title'=>'答对了送娃娃',    //转发标题
            'flag'=>$cConfig['ind_share'],   //分享群按钮开关
			'flag1'=>$cConfig['fail_share'],   //挑战失败分享群按钮开关
            'flag2'=>$cConfig['card_btn'],  //购买复活卡开关
            'flag3'=>$cConfig['zl_share'],  //群内智力
            'flag4'=>$cConfig['zj_share'],  //炫耀战绩
            'flag5'=>$cConfig['jh_share'],  //获得挑战机会
            'flag6'=>1,//游戏中分享到群
            'revive_money'=>C('resCarJE'),//复活卡金额
            'ctime'=>$sInfo['zm_points'], //用户当前可挑战次数
            'life'=>$sInfo['revive_time'],  //  当前用户复活卡数量
            'share_revive_time'=>C('resAmount'), //分享复活次数
            'buy_revive_time'=>C('resCarAmount'), //购买复活次数
            'max_challenge_time'=>10,   //最大挑战次数
            'max_get_prize_time'=>C('giftAmount'),    //最大领奖品次数
            'excAmount'=>C('excAmount'),
            'guide_txt'=>'邀请好友，帮忙答题',
            'sildeTxt'=>$prizeList,//[['head_img'=>'https://wx.qlogo.cn/mmopen/vi_32/DYAIOgq83epkDmbTFnMWh0qsjDjw0tFlpJw4CibOXgbr6bhdRhTwhjxHGhsSznJmoYAqnOWB7bVZzk2iaTicyIrWQ/0', 'sildeText'=>'1恭喜xx领到xx奖品'], ['head_img'=>'https://wx.qlogo.cn/mmopen/vi_32/DYAIOgq83epkDmbTFnMWh0qsjDjw0tFlpJw4CibOXgbr6bhdRhTwhjxHGhsSznJmoYAqnOWB7bVZzk2iaTicyIrWQ/0', 'sildeText'=>'2恭喜xx领到xx奖品'],  ]//获取最近领到娃娃奖品的信息
        ];
        
        return $returnData;
    }
    
}