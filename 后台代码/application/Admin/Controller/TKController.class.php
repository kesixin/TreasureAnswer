<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class TKController extends AdminbaseController{

    protected $quests_model;
    protected $quest_type_model;
    protected $options_model;

	public function _initialize() {
		parent::_initialize();
		$this->quests_model = M('questions');
		$this->quest_type_model = M('quest_type');
		$this->options_model = D("Common/Options");
	}

    // 题库展示
    public function TKdisplay(){
        $this->display();
    }
    
    // 题库列表
    public function questList()
    {
        $page = I('request.sEcho/d');
        $page++;
        $iDisplayStart = I('request.iDisplayStart/d');
        $iDisplayLength = I('request.iDisplayLength/d');
        
        /**搜索条件**/
        $keywork = trim(I('request.sSearch'));
        
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

    // 题库设置
    public function typeIndex(){
        $queryField = 'id,quest_type_name';
        $list = $this->quest_type_model
        ->field($queryField)
        ->where(['del'=>0])
        ->order("create_time DESC")
        ->select();
        //print_r(C('Gamount'));
        //exit();
        
        $cConfig = $this->options_model->where("option_name='quest_options'")->getField('option_value');
        
        $this->assign('questSettings', json_encode($cConfig));
        $this->assign('typeList', json_encode($list));
        $this->assign('questAmount', C('Gamount')+1);
        
        $this->display();
    }
    
    //每道题目设置题库
    public function questSettings(){
        $questSettings = I('post.option/a');
        //var_dump($questSettings);exit();
        
        $data['option_name'] = "quest_options";
        $data['option_value'] = json_encode($questSettings);
        $cConfig = $this->options_model->where("option_name='quest_options'")->find();
        
        if ($cConfig) {
            $result = $this->options_model->where("option_name='quest_options'")->save($data);
        } else {
            $result = $this->options_model->add($data);
        }
        
        if ($result !== false) {
            $this->success("保存成功！",'',true);
        } else {
            $this->error("保存失败！",'',true);
        }
    }
    
    // 题库分类列表
    public function typeList()
    {
        
        /**搜索条件**/
        $keywork = trim(I('request.sSearch'));
        
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
    
    //根据id删除一条数据
    public function typeDelete()
    {
        //获取Id
        $id = I('post.id/d');

        $res = $this->quest_type_model->where(['id'=>$id])->setField('del', 1);
        $res1 = $this->quests_model->where(['tid'=>$id])->setField('del', 1);
        
        if (!empty($res)) {

            $this -> ajaxReturn(['result'=>true, 'msg' => '删除成功']);
        }

        $this -> ajaxReturn(['result'=>false, 'msg' => '删除失败']);
    }
    
    // 题库导入
    public function TKinto(){
        $list = $this->quest_type_model
        ->field('id,quest_type_name')
        ->where(['del'=>0])
        ->order("create_time DESC")
        ->select();
        $this->assign('typeList', $list);
        $this->display();
    }

    
    //上传excel并将数据插入数据库
    public function upload()
    {
        
        header("Content-Type:text/html;charset=utf-8");
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('xls', 'xlsx');// 设置附件上传类
        $upload->savePath  =      'excels/'; // 设置附件上传目录
        $upload->rootPath  =   './'.C("UPLOADPATH");
        // 上传文件
        $info   =   $upload->uploadOne($_FILES['excelData']);
        $filename = $upload->rootPath.$info['savepath'].$info['savename'];
        $exts = $info['ext'];
        //print_r($info);exit;
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功
            $this->goods_import($filename, $exts);
        }
    }
    
    //导入数据方法
    protected function goods_import($filename, $exts='xls')
    {
        $tid = I('post.questType/d');
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
        import("Org.Util.PHPExcel",'','.php');
        //创建PHPExcel对象，注意，不能少了\
        $PHPExcel=new \PHPExcel();
        //如果excel文件后缀名为.xls，导入这个类
        if($exts == 'xls'){
            import("Org.Util.PHPExcel.Reader.Excel5");
            $PHPReader=new \PHPExcel_Reader_Excel5();
        }else if($exts == 'xlsx'){
            import("Org.Util.PHPExcel.Reader.Excel2007");
            $PHPReader=new \PHPExcel_Reader_Excel2007();
        }
        
        
        //载入文件
        $PHPExcel=$PHPReader->load($filename);
        //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
        $currentSheet=$PHPExcel->getSheet(0);
        //获取总列数
        $allColumn=$currentSheet->getHighestColumn();
        //获取总行数
        $allRow=$currentSheet->getHighestRow();
        //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从1开始
        $allData = [];
        for($currentRow=3;$currentRow<=$allRow;$currentRow++){
            //从哪列开始，A表示第一列
            $rowData = [];
            for($currentColumn='B';$currentColumn<=$allColumn;$currentColumn++){
                //数据坐标
                $address=$currentColumn.$currentRow;
                //读取到的数据，保存到数组$arr中
                $cell =$currentSheet->getCell($address)->getValue();
                //exit($currentColumn.'1');
                $fieldName =$currentSheet->getCell($currentColumn.'1')->getValue();
                
                if($cell instanceof PHPExcel_RichText){
                    $cell  = $cell->__toString();
                }
                //print_r($cell);
                $rowData[$fieldName] = $cell;
            }
            $rowData['tid'] = $tid;
            $allData[] = $rowData;
        }
        $amount = count($allData);
        //echo json_encode($allData);exit();
        $res = M('questions')->addAll($allData);
        
        if (empty($res)) {
            $this->error('导入失败');
        }else {
            $this->success('成功导入'.$amount.'条数据');
        }
        
        //$this->save_import($data);
    }

}