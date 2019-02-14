<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class JBsettingController extends AdminbaseController{

    protected $options_model;

	public function _initialize() {
		parent::_initialize();
		$this->options_model = D("Common/Options");
	}

    // 支付设置
    public function Paysetting(){
        $option=$this->options_model->where("option_name='customer_options'")->find();
        if($option){
            $this->assign(json_decode($option['option_value'],true));
            $this->assign("option_id",$option['option_id']);
        }
        $this->display();
    }
    
    //保存小程序参数
    public function savePaySetting() {
        $mchId = I('post.mchid/s');
        $key = I('post.key/s');
        
        $configs["G_MCHID"] = $mchId;
        $configs["G_KEY"] = $key;
        
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

    // 客服设置
    public function Custsetting(){
        $option=$this->options_model->where("option_name='customer_options'")->find();
        if($option){
            $option = json_decode($option['option_value'],true);
            $option['thumb_url'] = C('TMPL_PARSE_STRING.__UPLOAD__').$option['thumb_url'];
            $this->assign($option);
            $this->assign("option_id",$option['option_id']);
        }

        $this->display();
    }


    //保存小程序参数
    public function saveSetting() {
        
        $postData = I('post.');
        $postData['url'] = trim($postData['url']);
        if (empty($postData['thumb_url'])) {
            unset($postData['thumb_url']);
        }
        
        $data['option_name'] = "customer_options";
        $data['option_value'] = json_encode($postData);
        $cConfig = $this->options_model->where("option_name='customer_options'")->find();
        
        if ($cConfig) {
            if (!empty($cConfig['option_value'])) {
                $data['option_value'] = json_encode(array_merge(json_decode($cConfig['option_value'],true), $postData));
            }
            
            $result = $this->options_model->where("option_name='customer_options'")->save($data);
        } else {
            $result = $this->options_model->add($data);
        }
        
        if ($result !== false) {
            S('linkCont', null);
            $this->success("保存成功！");
        } else {
            $this->error("保存失败！");
        }
        
        
    }
    
    //更改密码
    public function Changepwd() {
        $this->display();
    }

    // 密码修改提交
    public function password_post(){
        if (IS_POST) {
            if(empty($_POST['old_password'])){
                $this->error("原始密码不能为空！",'',true);
            }
            if(empty($_POST['password'])){
                $this->error("新密码不能为空！",'',true);
            }
            $user_obj = D("Common/Users");
            $uid=sp_get_current_admin_id();
            $admin=$user_obj->where(array("id"=>$uid))->find();
            $old_password=I('post.old_password');
            $password=I('post.password');
            if(sp_compare_password($old_password,$admin['user_pass'])){
                if($password==I('post.repassword')){
                    if(sp_compare_password($password,$admin['user_pass'])){
                        $this->error("新密码不能和原始密码相同！",'',true);
                    }else{
                        $data['user_pass']=sp_password($password);
                        $data['id']=$uid;
                        $r=$user_obj->save($data);
                        if ($r!==false) {
                            $this->success("修改成功！",'',true);
                        } else {
                            $this->error("修改失败！",'',true);
                        }
                    }
                }else{
                    $this->error("密码输入不一致！",'',true);
                }
                
            }else{
                $this->error("原始密码不正确！",'',true);
            }
        }
    }
}