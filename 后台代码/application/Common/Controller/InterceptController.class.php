<?php
namespace Common\Controller;

use Think\Controller;

class InterceptController extends Controller {
    protected $user_info=null;
    protected static $_instance = null;
	public function __construct() {
		parent::__construct();
        $userInfo = S( I('token') );
        $this->user_info = $userInfo;
 		$this->check();
	}
    private function __clone(){}

    /**
     * 获取对象实例
     */
    static function instance()
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __get($name)
    {
        if (isset($this->user_info[$name])) {
            return $this->user_info[$name];
        }
        return null;
    }

    //检查用户是否登录
	function check()
    {
        
        if(!$this->user_info){
             $this->ajaxReturn(['code'=>40100,'msg'=>'登录后才能操作哦！']);
        };
    }

}