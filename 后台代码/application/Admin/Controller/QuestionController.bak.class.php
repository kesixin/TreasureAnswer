<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;
use Common\Lib\Pinyin;

class QuestionController extends AdminbaseController
{

	protected $quests_model;
	protected $quest_type_model;

	public function _initialize() {
		parent::_initialize();
		$this->quests_model = D("questions");
		$this->quest_type_model = D("quest_type");
	}

    // 概况统计
    public function index()
    {
        $this->display();
    }
    public function typeIndex3()
    {
        $this->display();
    }

    // 答对率统计
    public function statsReply()
    {
        $this->display();
    }

    // 游戏设置
    public function Gsetting()
    {
        $this->display();
    }

    // 题库展示
    public function TKdisplay()
    {

        $this->display();
    }
    public function typeIndex2()
    {

        $this->display();
    }
    // 题库列表
    public function questList()
    {

        /**搜索条件**/
        $keywork = trim(I('request.keywork'));

        $where = ['del'=>0];
        //根据user_name 搜索
        if (!empty($keywork)) {
            $where['quiz'] = ['like', "%$keywork%"];
        }

        $queryField = 'id,contributor,quiz,school,type';
        $count = $this->quests_model->where($where)->count();
        //$page = $this->page($count, 10);
        $list = $this->quests_model
        ->field($queryField)
        ->where($where)
        ->order("id DESC")
       // ->limit($page->firstRow, $page->listRows)
        ->select();

        echo json_encode(['data'=>$list]);
    }

    // 题库导入
    public function TKinto()
    {
        $this->display();
    }

    // 奖品设置
    public function Psetting()
    {
        $this->display();
    }

    // 中奖明细
    public function Pdetail()
    {
        $this->display();
    }

    // 物流管理
    public function Plogic()
    {
        $this->display();
    }

    // 支付明细
    public function Paydetail()
    {
        $this->display();
    }

    // 推广设置
    public function TGsetting()
    {
        $this->display();
    }

    // 小程序设置
    public function APPsetting()
    {
        $this->display();
    }

    // 支付设置
    public function Paysetting()
    {
        $this->display();
    }

    // 客服自动回复设置
    public function Custsetting()
    {
        $this->display();
    }

	// 题库分类列表
	public function typeIndex()
    {

        /**搜索条件**/
        $keywork = trim(I('request.keywork'));

        $where = ['del'=>0];
		//根据user_name 搜索
        if (!empty($keywork)) {
            $where['quest_type_name'] = ['like', "%$keywork%"];
        }

        $queryField = 'id,quest_type_name,remark,FROM_UNIXTIME(create_time, "%Y-%m-%d %H:%i:%S")';
        $count = $this->quest_type_model->where($where)->count();
        $page = $this->page($count, 10);
        $list = $this->quest_type_model
            ->field($queryField)
            ->where($where)
            ->order("create_time DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();

        $this->assign("page", $page->show('Admin'));
        $this->assign("list", $list);
        $this->display();
    }

    //查找一条题库分类数据
    public function getTypeInfo(){
        $tid = I('post.tid/d');

        $typeInfo = $this->quest_type_model->where(['id'=>$tid])->find();

        if (empty($typeInfo)) {
            $this -> ajaxReturn(['result'=>false, 'msg' => '添加失败']);
        }

        $this -> ajaxReturn(['result'=>true, 'msg' => '添加成功', 'data'=>$typeInfo]);
    }

    //添加或编辑分类
    public function addOrEditTypeInfo(){
        $tid = I('post.typeId/d');
        $quest_type_name = I('post.quest_type_name/s');
        $remark = I('post.remark/s');

        $res = null;
        if (empty($tid)) {
            $res = $this->quest_type_model->add(['quest_type_name'=>$quest_type_name, 'remark'=>$remark, 'create_time'=>$_SERVER['REQUEST_TIME']]);
        }else{
            $res = $this->quest_type_model->where(['id'=>$tid])->save(['quest_type_name'=>$quest_type_name, 'remark'=>$remark ]);
        }

        if (empty($res)) {
            $this -> ajaxReturn(['result'=>false, 'msg' => '操作失败']);
        }

        $this -> ajaxReturn(['result'=>true, 'msg' => '操作成功' ]);
    }

    // 题库分类列表
    public function typeList()
    {

        /**搜索条件**/
        $keywork = trim(I('request.keywork'));

        $where = ['del'=>0];
        //根据user_name 搜索
        if (!empty($keywork)) {
            $where['quest_type_name'] = ['like', "%$keywork%"];
        }

        $queryField = 'id,quest_type_name,remark,FROM_UNIXTIME(`create_time`, "%Y-%m-%d %H:%i:%S") as create_time';
        $count = $this->quest_type_model->where($where)->count();
       // $page = $this->page($count, 10);
        $list = $this->quest_type_model
        ->field($queryField)
        ->where($where)
        ->order("create_time DESC")
     //   ->limit($page->firstRow, $page->listRows)
        ->select();

        echo json_encode(['data'=>$list]);
    }

	//根据id删除一条红包表(enve)的数据
	public function typeDelete()
	{
		//获取Id
        $id = I('post.id/d');

        $res = $this->quest_type_model->where(['id'=>$id])->setField('del', 1);

		if (!empty($res)) {
			$this -> ajaxReturn(['result'=>true, 'msg' => '删除成功']);
		}
		$this -> ajaxReturn(['result'=>false, 'msg' => '删除失败']);
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
