<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class AppsettingController extends AdminbaseController{

    protected $options_model;

	public function _initialize() {
		parent::_initialize();
		$this->options_model = D("Common/Options");
	}

    // 小程序设置
    public function APPsetting(){
        $option=$this->options_model->where("option_name='customer_options'")->find();
        if($option){
            $this->assign(json_decode($option['option_value'],true));
            $this->assign("option_id",$option['option_id']);
        }
        $this->display();
    }
    
    //保存小程序参数
    public function saveSetting() {
        $appId = I('post.app_id/s');
        $appSecret = I('post.app_secret/s');
        $appName = I('post.app_name/s');
        
        $configs["C_APPID"] = $appId;
        $configs["C_APPSECRET"] = $appSecret;
        $configs["C_APPNAME"] = $appName;
        
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

}