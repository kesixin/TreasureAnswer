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

        $cpage = I('request.sEcho');
        if (empty($cpage)) {
            $cpage = 1;
        }
        $iDisplayStart = I('request.iDisplayStart');
        $iDisplayLength = I('request.iDisplayLength');
        if (empty($iDisplayStart)) {
            $iDisplayStart = 0;
        }
        if (empty($iDisplayLength)) {
            $iDisplayLength = 3;
        }

        $where = ['del'=>0];
        //根据user_name 搜索
        if (!empty($keywork)) {
            $where['quest_type_name'] = ['like', "%$keywork%"];
        }

        $queryField = 'id,quest_type_name,remark,FROM_UNIXTIME(`create_time`, "%Y-%m-%d %H:%i:%S") as create_time';
        $count = $this->quest_type_model->where($where)->count();
        //$page = $this->page($count, $iDisplayLength, $cpage);
        $list = $this->quest_type_model
        ->field($queryField)
        ->where($where)
        ->order("create_time DESC")
        ->limit($iDisplayStart, $iDisplayLength)
        ->select();

//         map.put("sEcho", initEcho);
//         map.put("iTotalRecords", list.getTotal());//数据总条数
//         map.put("iTotalDisplayRecords", list.getTotal());//显示的条数
//         map.put("aData", list.getDataList());//数据集合

        echo json_encode(['data'=>$list, 'iTotalRecords'=>$count, 'iTotalDisplayRecords'=>$count, 'sEcho'=>$cpage+1]);
    }

    // 题库列表
    public function questList()
    {
        $page = I('request.sEcho/d');
        $page++;
        $iDisplayStart = I('request.iDisplayStart/d');
        $iDisplayLength = I('request.iDisplayLength/d');

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
        ->limit($iDisplayStart, $iDisplayLength)
        ->select();

        echo json_encode(['data'=>$list, 'sEcho'=>$page, 'iTotalDisplayRecords'=>$count, 'iTotalRecords'=>$count ]);
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
