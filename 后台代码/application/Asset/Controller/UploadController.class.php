<?php
namespace Asset\Controller;


use Common\Controller\InterceptController;
use Think\Controller;

class UploadController extends InterceptController {

    // 文件上传
    public function plupload(){
        $upload_setting=sp_get_upload_setting();
        $filetypes=array(
//            'image'=>array('title'=>'Image files','extensions'=>$upload_setting['image']['extensions']),
//            'video'=>array('title'=>'Video files','extensions'=>$upload_setting['video']['extensions']),
            'audio'=>array('title'=>'Audio files','extensions'=>$upload_setting['audio']['extensions']),
//            'file'=>array('title'=>'Custom files','extensions'=>$upload_setting['file']['extensions'])
        );

        $image_extensions=explode(',', $upload_setting['image']['extensions']);

        if (IS_POST) {
            $all_allowed_exts=array();
            foreach ($filetypes as $mfiletype){
                array_push($all_allowed_exts, $mfiletype['extensions']);
            }
            $all_allowed_exts=implode(',', $all_allowed_exts);
            $all_allowed_exts=explode(',', $all_allowed_exts);
            $all_allowed_exts=array_unique($all_allowed_exts);

            $file_extension=sp_get_file_extension($_FILES['file']['name']);
            $upload_max_filesize=$upload_setting['upload_max_filesize'][$file_extension];
            $upload_max_filesize=empty($upload_max_filesize)?2097152:$upload_max_filesize;//默认2M

            $app=I('post.app/s','');
            if(!in_array($app, C('MODULE_ALLOW_LIST'))){
                $app='default';
            }else{
                $app= strtolower($app);
            }

			$savepath=$app.'/'.date('Ymd').'/';
            //上传处理类
            $config=array(
            		'rootPath' => './'.C("UPLOADPATH"),
            		'savePath' => $savepath,
            		'maxSize' => $upload_max_filesize,
            		'saveName'   =>    array('uniqid',''),
            		'exts'       =>    $all_allowed_exts,
            		'autoSub'    =>    false,
            );
			$upload = new \Think\Upload($config);//
			$info=$upload->upload();
            //开始上传
            if ($info) {
                //上传成功
                $oriName = $_FILES['file']['name'];
                //远程资源获取写入附件数据库信息
                $first=array_shift($info);
                if(!empty($first['url'])){
                	$url=$first['url'];
                	$storage_setting=sp_get_cmf_settings('storage');
                	$qiniu_setting=$storage_setting['Qiniu']['setting'];
                	$url=preg_replace('/^https/', $qiniu_setting['protocol'], $url);
                	$url=preg_replace('/^http/', $qiniu_setting['protocol'], $url);

                	$preview_url=$url;

                	if(in_array($file_extension, $image_extensions)){
                	    if(C('FILE_UPLOAD_TYPE')=='Qiniu' && $qiniu_setting['enable_picture_protect']){
                	        $preview_url = $url.$qiniu_setting['style_separator'].$qiniu_setting['styles']['thumbnail300x300'];
                	        $url= $url.$qiniu_setting['style_separator'].$qiniu_setting['styles']['watermark'];
                	    }
                	}else{
                	    $preview_url='';
                	    $url=sp_get_file_download_url($first['savepath'].$first['savename'],3600*24*365*50);//过期时间设置为50年
                	}

                }else{
                	$url=C("TMPL_PARSE_STRING.__UPLOAD__").$savepath.$first['savename'];
                	//判断应用是否放在子目录 在文件路径去掉子目录
                	if (__ROOT__ != '') {
                		$url = str_replace(__ROOT__, '', $url);
                	}
                	$preview_url=$url;
                	//文件路径写入数据库
                    $data = [
                        'src'=>$url,
                        'user_id'=>$this->user_id ?? '',
                        'openid'=>$this->openid ?? '',
                        'add_time'=>time(),
                        ];
                    $res = M('Upload')->add($data);
                    if(!$res){
                        //入库失败则删除文件
                        unlink($config['rootPath'].$first['savepath'].$first['savename']);
                        $this->ajaxReturn(['code'=>50000,'msg'=>'上传失败']);
                    }
                }
                $filepath = $savepath.$first['savename'];

//				$this->ajaxReturn(['code'=>20000,'preview_url'=>$preview_url,'filepath'=>$filepath,'url'=>$url,'name'=>$oriName,'status'=>1,'message'=>'success']);
                $this->ajaxReturn(array('file_url'=>$url),'JSON');
            } else {
                $this->ajaxReturn(array('name'=>'','status'=>0,'message'=>$upload->getError()));
            }
        } else {
            $filetype = I('get.filetype/s','image');
            $mime_type=array();
            if(array_key_exists($filetype, $filetypes)){
                $mime_type=$filetypes[$filetype];
            }else{
                $this->error('上传文件类型配置错误！');
            }

            $multi=I('get.multi',0,'intval');
            $app=I('get.app/s','');
            $upload_max_filesize=$upload_setting[$filetype]['upload_max_filesize'];
            $this->assign('extensions',$upload_setting[$filetype]['extensions']);
            $this->assign('upload_max_filesize',$upload_max_filesize);
            $this->assign('upload_max_filesize_mb',intval($upload_max_filesize/1024));
            $this->assign('mime_type',json_encode($mime_type));
            $this->assign('multi',$multi);
            $this->assign('app',$app);
            $this->display(':plupload');
        }
    }

    // 广告上传
    public function advpPlupload(){
    	$upload_setting=sp_get_upload_setting();
    	$filetypes=array(
    	            'image'=>array('title'=>'Image files','extensions'=>$upload_setting['image']['extensions']),
    	            'video'=>array('title'=>'Video files','extensions'=>$upload_setting['video']['extensions']),
    	//		'audio'=>array('title'=>'Audio files','extensions'=>$upload_setting['audio']['extensions']),
    			//            'file'=>array('title'=>'Custom files','extensions'=>$upload_setting['file']['extensions'])
    	);

    	$image_extensions=explode(',', $upload_setting['image']['extensions']);

    	if (IS_POST) {
    		$all_allowed_exts=array();
    		foreach ($filetypes as $mfiletype){
    			array_push($all_allowed_exts, $mfiletype['extensions']);
    		}
    		$all_allowed_exts=implode(',', $all_allowed_exts);
    		$all_allowed_exts=explode(',', $all_allowed_exts);
    		$all_allowed_exts=array_unique($all_allowed_exts);

    		$file_extension=sp_get_file_extension($_FILES['file']['name']);
    		$upload_max_filesize=$upload_setting['upload_max_filesize'][$file_extension];
    		$upload_max_filesize=empty($upload_max_filesize)?2097152:$upload_max_filesize;//默认2M

    		$app=I('post.app/s','');
    		if(!in_array($app, C('MODULE_ALLOW_LIST'))){
    			$app='adv';
    		}else{
    			$app= strtolower($app);
    		}

    		$savepath=$app.'/'.date('Ymd').'/';
    		//上传处理类
    		$config=array(
    				'rootPath' => './'.C("UPLOADPATH"),
    				'savePath' => $savepath,
    				'maxSize' => $upload_max_filesize,
    				'saveName'   =>    array('uniqid',''),
    				'exts'       =>    $all_allowed_exts,
    				'autoSub'    =>    false,
    		);
    		$upload = new \Think\Upload($config);//
    		$info=$upload->upload();
    		//开始上传
    		if ($info) {
    			//上传成功
    			$oriName = $_FILES['file']['name'];
    			//远程资源获取写入附件数据库信息
    			$first=array_shift($info);
    			if(!empty($first['url'])){
    				$url=$first['url'];
    				$storage_setting=sp_get_cmf_settings('storage');
    				$qiniu_setting=$storage_setting['Qiniu']['setting'];
    				$url=preg_replace('/^https/', $qiniu_setting['protocol'], $url);
    				$url=preg_replace('/^http/', $qiniu_setting['protocol'], $url);

    				$preview_url=$url;

    				if(in_array($file_extension, $image_extensions)){
    					if(C('FILE_UPLOAD_TYPE')=='Qiniu' && $qiniu_setting['enable_picture_protect']){
    						$preview_url = $url.$qiniu_setting['style_separator'].$qiniu_setting['styles']['thumbnail300x300'];
    						$url= $url.$qiniu_setting['style_separator'].$qiniu_setting['styles']['watermark'];
    					}
    				}else{
    					$preview_url='';
    					$url=sp_get_file_download_url($first['savepath'].$first['savename'],3600*24*365*50);//过期时间设置为50年
    				}

    			}else{
    				$url=C("TMPL_PARSE_STRING.__UPLOAD__").$savepath.$first['savename'];
    				//判断应用是否放在子目录 在文件路径去掉子目录
    				if (__ROOT__ != '') {
    					$url = str_replace(__ROOT__, '', $url);
    				}

    				$preview_url=$url;
    				//文件路径写入数据库
    				$data = [
    						'src'=>$url,
    						'user_id'=>$this->user_id ?? '',
    						'openid'=>$this->openid ?? '',
    						'add_time'=>time(),
    				];

    				$res = M('upload')->add($data);
    				if(!$res){
    					//入库失败则删除文件
    					unlink($config['rootPath'].$first['savepath'].$first['savename']);
    					$this->ajaxReturn(['code'=>50000,'msg'=>'上传失败']);
    				}
    			}
    			$filepath = $savepath.$first['savename'];

    			//				$this->ajaxReturn(['code'=>20000,'preview_url'=>$preview_url,'filepath'=>$filepath,'url'=>$url,'name'=>$oriName,'status'=>1,'message'=>'success']);
    			$this->ajaxReturn(array('file_url'=>$url),'JSON');
    		} else {
    			$this->ajaxReturn(array('name'=>'','status'=>0,'message'=>$upload->getError()));
    		}
    	}
    }

    // 用户头像 上传
    public function userUpload()
    {
    	$upload_setting=sp_get_upload_setting();
    	$filetypes=array(
    	            'image'=>array('title'=>'Image files','extensions'=>$upload_setting['image']['extensions']),
    	            // 'video'=>array('title'=>'Video files','extensions'=>$upload_setting['video']['extensions']),
    	//		'audio'=>array('title'=>'Audio files','extensions'=>$upload_setting['audio']['extensions']),
    			//            'file'=>array('title'=>'Custom files','extensions'=>$upload_setting['file']['extensions'])
    	);

    	$image_extensions=explode(',', $upload_setting['image']['extensions']);

    	if (IS_POST) {
    		$all_allowed_exts=array();
    		foreach ($filetypes as $mfiletype){
    			array_push($all_allowed_exts, $mfiletype['extensions']);
    		}
    		$all_allowed_exts=implode(',', $all_allowed_exts);
    		$all_allowed_exts=explode(',', $all_allowed_exts);
    		$all_allowed_exts=array_unique($all_allowed_exts);

    		$file_extension=sp_get_file_extension($_FILES['file']['name']);
    		$upload_max_filesize=$upload_setting['upload_max_filesize'][$file_extension];
    		$upload_max_filesize=empty($upload_max_filesize)?2097152:$upload_max_filesize;//默认2M

    		$app=I('post.app/s','');
    		if(!in_array($app, C('MODULE_ALLOW_LIST'))){
    			$app='userPortrait';
    		}else{
    			$app= strtolower($app);
    		}

    		$savepath=$app.'/'.date('Ymd').'/';
    		//上传处理类
    		$config=array(
    				'rootPath' => './'.C("UPLOADPATH"),
    				'savePath' => $savepath,
    				'maxSize' => $upload_max_filesize,
    				'saveName'   =>    array('uniqid',''),
    				'exts'       =>    $all_allowed_exts,
    				'autoSub'    =>    false,
    		);
    		$upload = new \Think\Upload($config);//
    		$info=$upload->upload();
    		//开始上传
    		if ($info) {
    			//上传成功
    			$oriName = $_FILES['file']['name'];
    			//远程资源获取写入附件数据库信息
    			$first=array_shift($info);
    			if(!empty($first['url'])){
    				$url=$first['url'];
    				$storage_setting=sp_get_cmf_settings('storage');
    				$qiniu_setting=$storage_setting['Qiniu']['setting'];
    				$url=preg_replace('/^https/', $qiniu_setting['protocol'], $url);
    				$url=preg_replace('/^http/', $qiniu_setting['protocol'], $url);

    				$preview_url=$url;

    				if(in_array($file_extension, $image_extensions)){
    					if(C('FILE_UPLOAD_TYPE')=='Qiniu' && $qiniu_setting['enable_picture_protect']){
    						$preview_url = $url.$qiniu_setting['style_separator'].$qiniu_setting['styles']['thumbnail300x300'];
    						$url= $url.$qiniu_setting['style_separator'].$qiniu_setting['styles']['watermark'];
    					}
    				}else{
    					$preview_url='';
    					$url=sp_get_file_download_url($first['savepath'].$first['savename'],3600*24*365*50);//过期时间设置为50年
    				}

    			}else{
    				$url=C("TMPL_PARSE_STRING.__UPLOAD__").$savepath.$first['savename'];
    				//判断应用是否放在子目录 在文件路径去掉子目录
    				if (__ROOT__ != '') {
    					$url = str_replace(__ROOT__, '', $url);
    				}

    				$preview_url=$url;
    				//文件路径写入数据库
    				$data = [
    						'src'=>$url,
    						'user_id'=>$this->user_id ?? '',
    						'openid'=>$this->openid ?? '',
    						'add_time'=>time(),
    				];

    				$res = M('upload')->add($data);
    				if(!$res){
    					//入库失败则删除文件
    					unlink($config['rootPath'].$first['savepath'].$first['savename']);
    					$this->ajaxReturn(['code'=>50000,'msg'=>'上传失败']);
    				}
    			}
    			$filepath = $savepath.$first['savename'];

    			//				$this->ajaxReturn(['code'=>20000,'preview_url'=>$preview_url,'filepath'=>$filepath,'url'=>$url,'name'=>$oriName,'status'=>1,'message'=>'success']);
    			$this->ajaxReturn(array('file_url'=>$url),'JSON');
    		} else {
    			$this->ajaxReturn(array('name'=>'','status'=>0,'message'=>$upload->getError()));
    		}
    	}
    }



}
