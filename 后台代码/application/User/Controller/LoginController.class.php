<?php
namespace User\Controller;

use Common\Controller\WeixinController;
use Common\Lib\WXBizDataCrypt;


class LoginController extends WeixinController
{

	public function get_accesstoken(){
		
		
		$result=$this->set_token();
		var_dump(json_encode($result));
		
		
	}
	 public function login_jieping(){ 
		 
		 
		$wx_user = D("WxUser");
		 $res_wx = $this->send_url(['code'=>$_GET['code']]);
		 $res_wx = json_decode($res_wx,true);
		 $user_info = $wx_user->field('jieping')->where("openid='%s' ",array($res_wx['openid']))->find();
		 
		 
		 $this->ajaxReturn($user_info); 
	 }
    // 登录验证提交
    public function dologin(){
        $wx_user = D("WxUser");
        $post_data = I('post.');
        
        
        
        $code =  I('post.code/s');/*获取前端传过来的code*/
        if(!$code){
            $this->ajaxReturn(['code' => 40000, 'msg'=>'code不能为空']);
        }
        $res_wx = $this->send_url(['code'=>$code]);/*调用微信接口用code获取openid和unionid*/
        $res_wx = json_decode($res_wx,true);
        if($res_wx['errcode']){
            $this->ajaxReturn(['code' => 50000, 'msg'=>'code失效']);
        }
        $post_data['openid'] = $res_wx['openid']??'';
        $post_data['unionid'] = $res_wx['unionid'] ?? '';
        
        //获取分享人openid
        $scene = $post_data['scene'];
        
    	//检查用户是否存在
        $user_info = $wx_user->field('id,zm_points,supply_time,status')->where("openid='%s' ",array($post_data['openid']))->find();

        $post_data ['unionid'] = $res_wx ['unionid'] ?? '';
        $post_data ['session_key'] = $res_wx ['session_key'] ?? '';
        
        /*// 解密信息获取unionid
        $wXBizDataCrypt = new WXBizDataCrypt ( C ( 'C_APPID' ), $post_data ['session_key'] );
        $data = [ ];
        $errCode = $wXBizDataCrypt->decryptData ( $post_data ['encryptedData'], $post_data ['iv'], $data );
        $data = json_decode ( $data, true );
        if ($errCode == 0) {
            $post_data ['unionid'] = $data ['unionId'];
        } else {
           
        }*/

        /*检查用户是否被禁用*/
        if (!empty($user_info)) {
        	if ($user_info['status'] == 0) {
        		$this->ajaxReturn(['code'=>40500, 'msg'=>'fail']);
        	}
        }


        $type = !empty($user_info) ? 2 : 1 ;
        $post_data = $wx_user->create($post_data,$type);
        if(!$post_data){
            $this->ajaxReturn(['code' => 40000, 'msg'=>$wx_user->getError()]);
        }

        $user_name = $post_data['user_name'];
        $nick_name = $post_data['nick_name'];
        $post_data['u_name'] = $post_data['user_name'];
        $post_data['user_name']= json_encode($post_data['user_name']);
        $post_data['nick_name']= json_encode($post_data['nick_name']);
        
        if($user_info['id']){
            $last_id = $user_info['id'];
            $wx_user->where(array('id'=>$last_id))->save($post_data);/*更新用户信息*/
        }else{
            //新用户进来给1次答题机会
            $post_data['zm_points'] = C('giveAmount');
            $last_id = $wx_user->add ( $post_data );
        }
        //$session_user_info = md5($post_data['openid'].$last_id);
        $token = md5($post_data['openid'].$last_id.time().microtime());
        //设置登录信息
        $post_data['user_id'] = $last_id;
        //S($token,$session_user_info,['expire'=>86400*30]);
        S($token,$post_data,['expire'=>86400*30]);
        $this->ajaxReturn(['code'=>20000, 'msg'=>'success', 'token'=>$token,'openid'=>$post_data['openid']]);
    }

 }
