<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;
use Common\Lib\Pinyin;

class QuestsController extends AdminbaseController
{

	protected $quests_model;

	public function _initialize() {
		parent::_initialize();
		$this->quests_model = M("questions");
	}
	
	//题库列表
	public function index()
	{
	    /**搜索条件**/
	    $keywork = trim(I('request.keywork'));
	    
	    $where = array('del'=>0);
	    //根据题目 搜索
	    if ($keywork) {
	        $where1['quiz'] = array('like', "%$keywork%");
	        $where1['options'] = array('like', "%$keywork%");
	        $where1['_logic'] = 'OR';
	        $where['_complex'] = $where1;
	    }
	    
	    $count = $this->quests_model->where($where)->count();
	    $page = $this->page($count, 20);
	    $quests = $this->quests_model
	    ->where($where)
	    //->order("audit ASC,add_time DESC")
	    ->limit($page->firstRow, $page->listRows)
	    ->select();
	    
	    $this->assign("page", $page->show('Admin'));
	    $this->assign("quests", $quests);
	    $this->display();
	}
	
	//根据id删除一条数据
	public function removeQuest()
	{
	    //获取Id
	    $id = I('post.id');
	    
	    $bool = $this->quests_model->where(array('id'=>$id))->setField('del', 1);
	    
	    if ($bool) {
	        $this -> ajaxReturn(['error_no'=>0, 'msg' => '删除成功']);
	    }
	    $this -> ajaxReturn(['error_no'=>100, 'msg' => '删除失败']);
	}
	
	//导入数据页面
	public function import()
	{
	    $this->display('quests_import');
	}
	
	//上传方法
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
	        exit($upload->getError());
	        $this->error($upload->getError());
	    }else{// 上传成功
	        $this->goods_import($filename, $exts);
	    }
	}
	
	//导入数据方法
	protected function goods_import($filename, $exts='xls')
	{
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
