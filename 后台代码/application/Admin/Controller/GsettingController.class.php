<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class GsettingController extends AdminbaseController{

    protected $options_model;

	public function _initialize() {
		parent::_initialize();
		$this->options_model = D("Common/Options");
	}


    // 游戏设置
    public function Gsetting(){
        $option=$this->options_model->where("option_name='customer_options'")->find();
        $cConfig = $this->options_model->where("option_name='switch_options'")->find();
        if($option){
            $this->assign(json_decode($option['option_value'],true));
            $this->assign('switchs', $cConfig['option_value']);
            $this->assign("option_id",$option['option_id']);
        }
        $this->display();
    }


    //保存小程序参数
    public function saveSetting() {
        $Gamount = I('post.Gamount/d');//游戏题目数量
        $giveAmount = I('post.giveAmount/d');//每日赠送答题数
        $giftAmount = I('post.giftAmount/s');//每日领奖次数上限
        
        $resAmount = I('post.resAmount/d');//每次挑战转发群复活次数上限
        $resCarAmount = I('post.resCarAmount/d');//每次挑战使用复活卡次数上限
        $resCarJE = I('post.resCarJE/f');//复活卡价格
        $excAmount = I('post.excAmount/d');//奖品可兑换挑战次数
        $rule = I('post.rule/s');//首页规则文字
        $explain = I('post.explain/s');//开始游戏说明文字
        
        $configs["Gamount"] = $Gamount;
        $configs["giveAmount"] = $giveAmount;
        $configs["giftAmount"] = $giftAmount;
        
        $configs["resAmount"] = $resAmount;
        $configs["resCarAmount"] = $resCarAmount;
        $configs["resCarJE"] = $resCarJE;
        $configs["excAmount"] = $excAmount;
        $configs["rule"] = explode(',', $rule);
        $configs["explain"] = explode(',', $explain);
        
        sp_set_dynamic_config($configs);//sae use same function
        
        $data['option_name'] = "customer_options";
        $data['option_value'] = json_encode(I('post.'));
        $cConfig = $this->options_model->where("option_name='customer_options'")->find();
        
        if ($cConfig) {
            if (!empty($cConfig['option_value'])) {
                $data['option_value'] = json_encode(array_merge(json_decode($cConfig['option_value'],true), I('post.')));
            }
            
            $result = $this->options_model->where("option_name='customer_options'")->save($data);
        } else {
            $result = $this->options_model->add($data);
        }
        
        if ($result !== false) {
            $this->success("保存成功！");
        } else {
            $this->error("保存失败！");
        }
    }
    
    public function switchSetting() {
        $type = I('post.t/s');//按钮类型
        $value = I('post.v/d');//按钮值
		
        if (empty($type)) {
            $this->error("保存失败！",'',true);
        }
        
        
        
        sp_set_dynamic_config($configs);//sae use same function
        
        $data['option_name'] = "switch_options";
        $data['option_value'] = json_encode([$type=>$value]); 
        
        if ($type == 'share_btn') {
            $cdata['share_btn'] = $value;
			$cdata['fail_share'] = $value;//挑战失败
            $cdata['zl_share'] = $value;
            $cdata['zj_share'] = $value;
            $cdata['jh_share'] = $value;
            $cdata['ind_share'] = $value;
			
            $data['option_value'] = json_encode($cdata); 
        }
        
        $cConfig = $this->options_model->where("option_name='switch_options'")->find();
        
        if ($cConfig) {
            $configs = json_decode($cConfig['option_value'], true);
            $configs[$type] = $value;
            if ($type == 'share_btn') {
                $configs['share_btn'] = $value;
				$configs['fail_share'] = $value;//挑战失败
                $configs['zl_share'] = $value;
                $configs['zj_share'] = $value;
                $configs['jh_share'] = $value;
                $configs['ind_share'] = $value;
				
            }elseif ($type != 'card_btn'){
                $configs['share_btn'] = 1;
            }
            $data['option_value'] = json_encode($configs);//var_dump($data);exit();
            $result = $this->options_model->where(['option_name'=>'switch_options'])->save($data);
        } else {
            $result = $this->options_model->add($data);
        }
        
        if ($result !=   false){
            $this->success("保存成功！",'',true);
        }else{
            $this->error("保存失败！",'',true);
        }
        
    }

}