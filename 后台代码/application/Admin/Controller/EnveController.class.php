<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;
use Common\Lib\Pinyin;

class EnveController extends AdminbaseController
{

	protected $enves_model;

	public function _initialize() {
		parent::_initialize();
		$this->enves_model = D("Enve");
	}

	// 红包列表
	public function index()
    {

        /**搜索条件**/
        $openid = I('request.openid');
        $keywork = trim(I('request.keywork'));

        $where = array('del'=>0);
		//根据openid 搜索
        if ($openid) {
            $where['openid'] = $openid;
        }
		//根据user_name 搜索
        if ($keywork) {
            $where['user_name'] = array('like', "%$keywork%");
			$where['quest'] = array('like', "%$keywork%");
            $where['_logic'] = 'OR';
        }

        $count = $this->enves_model->where($where)->count();
        $page = $this->page($count, 20);
        $enves = $this->enves_model
            ->where($where)
            ->order("audit ASC,add_time DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();

		//$arr = [1=>'微信支付', 2=>'余额支付', 3=>'混合支付'];
        foreach ($enves as &$v){
        	$v['adv_url'] = empty($v['adv_url'])?'':__ROOT__.$v['adv_url'];
        	$v['video_url'] = empty($v['video_url'])?'':__ROOT__.$v['video_url'];
			//$v['pay_type'] = $arr[$v['pay_type']];
            $v['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
			if (strlen($v['quest']) > 24) {
				$v['quest'] = substr($v['quest'], 0, 24).".....";
			}
			$v['user_name'] = json_decode($v['user_name']);
        }

        $this->assign("page", $page->show('Admin'));
        $this->assign("enves", $enves);
        $this->display();
    }
	//根据id删除一条红包表(enve)的数据
	public function removeEnve()
	{
		//获取Id
        $id = I('post.id');

		$bool = C('WEALTHY');
		//检查id是否存在这个表
		if (!C('WEALTHY')){
            $this -> ajaxReturn(['msg' => '该用户不存在']);
        }

		$Enve = D("Enve");

		$bool = $Enve->where(array('id'=>$id))->setField('del', 1);

		if ($bool) {
			$this -> ajaxReturn(['msg' => '删除成功']);
		}
	}
	
	//审核口令红包
	public function enveAudit() {
		//获取Id
		$id = I('post.id');
		$Enve = D("Enve");
		
		$bool = $Enve->where(array('id'=>$id))->save(array('audit'=>1));
		
		if ($bool) {
			$this -> ajaxReturn(['result'=>true, 'msg' => '审核成功']);
		}
		$this -> ajaxReturn(['result'=>false, 'msg' => '审核失败']);
	}
	
	/*添加平台红包*/
	public function add( ) {
		
		if (!IS_POST) {
			$pid = M('settings')->where(['key'=>'cpid'])->getField('value');
			$enveInfo = M('Enve')->where(['id'=>$pid])->find();
			
			$challengeModel = M('challenge');
			$pass = $challengeModel->where(['pid'=>$pid, 'is_pass'=>1])->count();//通过次数
			$notpass = $challengeModel->where(['pid'=>$pid, 'is_pass'=>0])->count();//失败次数
			$enveAmount = $challengeModel->where(['pid'=>$pid, 'is_pass'=>1])->sum('amount');//已被领金额
			
			$allEnveInfo = M()->query('SELECT COUNT(IF(b.`is_pass`=1,TRUE,NULL)) AS pass,COUNT(IF(b.`is_pass`=0,TRUE,NULL)) AS notpass,a.num,a.enve_name,FROM_UNIXTIME(a.add_time,\'%Y-%m-%d %H:%i\') add_time,a.id,a.be_overdue,a.receive_num,a.status,a.end_time FROM __PREFIX__enve a LEFT JOIN __PREFIX__challenge b ON a.id=b.pid WHERE a.enve_type=1 GROUP BY a.id');
			//var_dump($allEnveInfo);exit();
			$this->assign('allEnve', $allEnveInfo);
			$this->assign('totalEnveAmount', $enveAmount);
			$this->assign('pass', $pass);
			$this->assign('notpass', $notpass);
			$this->assign('enveInfo', $enveInfo);
			$this->display();
			exit();
		}
		
		$num = I('post.num/d');
		$enveName = I('post.enve_name/s');
		$link_model = D("Common/Kouling");
		$enveModel = M('Enve');
		
		$kouLingArr = $link_model->field('link_description')->order(array("listorder"=>"ASC"))->limit(5)->select();
		
		$kouLingPy = [];
		$kouLing = [];
		$py = new Pinyin();
		foreach ($kouLingArr as $v){
			$kouLing[] = $v['link_description'];
			$kouLingPy[] = $py->getPY($v['link_description']);
		}
		
		$enveId = $enveModel->add(['quest'=>json_encode($kouLing), 'quest_py'=>json_encode($kouLingPy), 'num'=>$num, 'enve_name'=>$enveName, 'enve_type'=>1, 'audit'=>1, 'pay_type'=>2, 'pay_status'=>0, 'add_time'=>time()]);
		
		if (!empty($enveId)) {
			$res = M('settings')->where(['key'=>'cpid'])->setField('value', $enveId);
			if (!empty($res)) {
				$this -> ajaxReturn(['result'=>true, 'msg' => '添加成功', 'info'=>'添加成功', 'cpid'=>$enveId]);
			}
			
		}
		
		$this -> ajaxReturn(['result'=>false, 'msg' => '添加失败', 'info'=>'添加成功']);
	}
	
	/*题库导入 */
	public function active(){
	    $this->display();
	}
	
	//根据id删除一条红包表(enve)的数据
	public function disableEnve()
	{
		//获取Id
		$id = I('post.id');
		
		$Enve = D("Enve");
		
		$bool = $Enve->where(array('id'=>$id))->setField('status', 0);
		
		if ($bool) {
			$this -> ajaxReturn(['result'=>true, 'msg' => '操作成功']);
		}
		$this -> ajaxReturn(['result'=>false, 'msg' => '操作失败']);
	}
	//开始活动
	public function startupEnve()
	{
		//获取Id
		$id = I('post.id');
		
		$Enve = D("Enve");
		
		$bool = $Enve->where(array('id'=>$id))->setField('status', 1);
		
		if ($bool) {
			$this -> ajaxReturn(['result'=>true, 'msg' => '操作成功']);
		}
		$this -> ajaxReturn(['result'=>false, 'msg' => '操作失败']);
	}
	//暂停活动
	public function pauseEnve()
	{
		//获取Id
		$id = I('post.id');
		
		$Enve = D("Enve");
		
		$bool = $Enve->where(array('id'=>$id))->setField('status', 2);
		
		if ($bool) {
			$this -> ajaxReturn(['result'=>true, 'msg' => '操作成功']);
		}
		$this -> ajaxReturn(['result'=>false, 'msg' => '操作失败']);
	}

}
