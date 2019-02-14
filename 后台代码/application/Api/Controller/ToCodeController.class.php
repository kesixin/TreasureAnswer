<?php
/**
 * 获取二维码
 * author universe.h
 */
namespace Api\Controller;
use Common\Controller\InterceptController;
use Common\Controller\WeixinController;
use Common\Controller\AdvController;

class ToCodeController extends InterceptController {
	
	/**
	 * 小程序获取二维码
	 * time 2017.9.30
	 */
	public function get_code() {
		$data['page'] = I('post.page/s');
		$data['width'] = I('post.width/d',430);
		$data['auto_color'] = I('post.auto_color/s');
		$data['line_color'] = I('post.line_color/s');
		$pid = I('post.pid/d');
		$data['scene'] = $pid;
		if(!$data['page']){
			$this->ajaxReturn(['code'=>40000, 'msg'=>'跳转链接不能为空']);
		}
		
		/*
		$share_pic = M('share_pic')->where(array('pid'=>$pid, 'user_id'=>$this->user_id))->find();
		if (!empty($share_pic)) {
			$this->ajaxReturn(['code'=>20000, 'msg'=>'success', 'data'=>$share_pic['purl'], 'content'=>$content ],false,JSON_UNESCAPED_SLASHES);
		}*/
		$info = WeixinController::instance()->get_wxa_code($data);
		$path = C('UPLOADPATH').'code/';
		if(!is_dir($path)){
			mkdir(iconv("UTF-8", "GBK", $path),0777,true);
		}
		$file = rand(10000000,99999999).'.png';
		$paths = $path . $file;
		$res = file_put_contents( $paths,$info );
		$path_head = $path.'head'.$file;
		
		if($res){
			$res = file_put_contents( $path_head, file_get_contents($this->head_img));
			//$res = file_put_contents( $path_head, file_get_contents('https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTK45iap26R41QyO0cH0QCDzhHRZVghH5RDONiaREwfgTALwiaQRkEu3R2grzxv7nBvexsHhbxsrRecfQ/0'));
			
			$imgs = array(
					'dst' => 'data/upload/back.png',//str_replace(__ROOT__.'/', '', C('BACKIMGURL')),//
					'src' => $paths,
					'head' => $path_head,
			);
			$this->tosize($imgs['head'],125,true);
			$roundImg = $this->toround($imgs);
			$this->mergerImg($imgs,$roundImg);
			
				//M('share_pic')->add(array('pid'=>$pid, 'user_id'=>$this->user_id, 'purl'=>$paths, 'createtime'=>time()));
				$this->ajaxReturn(['code'=>20000, 'msg'=>'success', 'data'=>$paths, 'content'=>$content ],false,JSON_UNESCAPED_SLASHES);
			
			
		}
		$this->ajaxReturn(['code'=>40000, 'msg'=>'生成失败']);
		
		
	}
	
	//改变图片大小
	public function tosize($url,$max = 200,$is_pic = false){
		//因为PHP只能对资源进行操作，所以要对需要进行缩放的图片进行拷贝，创建为新的资源
		$src=imagecreatefromjpeg($url);
		
		//取得源图片的宽度和高度
		$size_src=getimagesize($url);
		$w=$size_src['0'];
		$h=$size_src['1'];
		if($max >= $w){
			return false;
		}
		
		//根据最大值为300，算出另一个边的长度，得到缩放后的图片宽度和高度
		if($w > $h){
			$w=$max;
			$h=$h*($max/$size_src['0']);
		}else{
			$h=$max;
			$w=$w*($max/$size_src['1']);
		}
		//声明一个$w宽，$h高的真彩图片资源
		$image=imagecreatetruecolor($w, $h);
		
		//关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h）
		imagecopyresampled($image, $src, 0, 0, 0, 0, $w, $h, $size_src['0'], $size_src['1']);
		
		if(!$is_pic){
			return $image;
		}
		
		//告诉浏览器以图片形式解析
		header('content-type:image/png');
		
		imagepng($image,$url);
		
		//销毁资源
		imagedestroy($image);
	}
	
	//合并
	public function mergerImg($imgs,$oth) {
		//生成原型图
		imagepng($oth, $imgs['src']);
		list($max_width, $max_height) = getimagesize($imgs['dst']);
		$dests = imagecreatetruecolor($max_width, $max_height);
		
		$dst_im = imagecreatefrompng($imgs['dst']);
		imagecopy($dests,$dst_im,0,0,0,0,$max_width,$max_height);
		imagedestroy($dst_im);
		
		//合成二维码
		$src_im = imagecreatefrompng($imgs['src']);
		imagealphablending($src_im,true);
		$src_info = getimagesize($imgs['src']);
		imagecopy($dests, $src_im,$max_width/2-$src_info[0]/2,$max_height/2+125,0,0,$src_info[0],$src_info[1]);
		
		//合成头像
		//$head_img = imagecreatefrompng($imgs['head']);
		//将头像处理圆角
		$a['src'] = $imgs['head'];
		$head_img = $this->toround($a);
		//获取头像长宽等信息
		$head_info = getimagesize($imgs['head']);
		
		imagecopy($dests, $head_img,$max_width/2-$head_info[0]/2,45,0,0, $head_info[0],$head_info[1]);
		imagedestroy($src_im);
		
		header("Content-type: image/png");
		imagepng($dests,$imgs['src']);
		//        imagepng($dests);
		unlink($imgs['head']);
	}
	
	//添加文字
	public function totxt($src,$textArr){
		
		//获取图片信息
		$info = getimagesize($src['src']);
		//        var_dump($info);die;
		//获取图片扩展名
		$type = image_type_to_extension($info[2],false);
		//动态的把图片导入内存中
		$fun = "imagecreatefrom{$type}";
		$image = $fun($src['src']);
		//指定字体颜色
		$col = imagecolorallocatealpha($image,0,0,0,1);
		//指定字体颜色
		$col1 = imagecolorallocatealpha($image,0,0,0,1);
		$font_file = 'simplewind/Core/Library/Think/Verify/zhttfs/1.ttf';
		

		//等级
		$b = imagettfbbox(15,0, $font_file,$textArr['tit'] );
		$textX=ceil(($info[0] - $b[2]) / 2);
		$lengb = abs(b[0] - $b[2]);
		//指定字体内容
		imagefttext($image, 15, 0,  $textX+255, $info[1]/2.29, $col1, $font_file,mb_convert_encoding($textArr['tit'],'html-entities','UTF-8'));
		

		//上面的年月
		$b = imagettfbbox(20,0, $font_file,$textArr['con'] );
		//指定字体内容
		imagefttext($image, 20, 0,  ($info[0] - $b[2]-260 ), $info[1]/3.4, $col, $font_file,mb_convert_encoding($textArr['con'],'html-entities','UTF-8'));

		//下面年月
		$b = imagettfbbox(20,0, $font_file,$textArr['con'] );
		//指定字体内容
		imagefttext($image, 20, 0,  ($info[0] - $b[2]-290 ), $info[1]/1.19, $col, $font_file,mb_convert_encoding($textArr['con'],'html-entities','UTF-8'));


		//下面 日期
		$b = imagettfbbox(20,0, $font_file,$textArr['riqi'] );
		//指定字体内容
		imagefttext($image, 20, 0,  ($info[0] - $b[2]-290 +110), $info[1]/1.19, $col, $font_file,mb_convert_encoding($textArr['riqi'],'html-entities','UTF-8'));


		//姓名
		$c = imagettfbbox(20,0, $font_file,$textArr['name'] );
		//指定字体内容
		imagefttext($image, 20, 0,  $info[0] - $c[2]-590, $info[1]/1.80, $col, $font_file,mb_convert_encoding($textArr['name'],'html-entities','UTF-8'));


		//出生年月
		$c = imagettfbbox(20,0, $font_file,$textArr['birthday'] );
		//指定字体内容
		imagefttext($image, 20, 0,  $info[0] - $c[2]-590, $info[1]/1.34, $col, $font_file,mb_convert_encoding($textArr['birthday'],'html-entities','UTF-8'));



		//编号
		$c = imagettfbbox(20,0, $font_file,$textArr['num'] );
		//指定字体内容
		imagefttext($image, 20, 0,  $info[0] - $c[2]-640, $info[1]/1.19, $col, $font_file,mb_convert_encoding($textArr['num'],'html-entities','UTF-8'));


		//性别
		$c = imagettfbbox(20,0, $font_file,$textArr['sex'] );
		//指定字体内容
		imagefttext($image, 20, 0,$info[0] - $c[2]-690, $info[1]/1.54, $col, $font_file,mb_convert_encoding($textArr['sex'],'html-entities','UTF-8'));




		//分数
		$c = imagettfbbox(24,0, $font_file,$textArr['content'] );
		//指定字体内容
		imagefttext($image, 24, 0,  ceil(($info[0] - $c[2]) / 2)+180, $info[1]/2.72, $col, $font_file,mb_convert_encoding($textArr['content'],'html-entities','UTF-8'));
		
		
		
		//指定输入类型
		header('Content-type:'.$info['mime']);
		//动态的输出图片到浏览器中
		$func = "image{$type}";
		$func($image,$src['src']);
		//销毁图片
		imagedestroy($image);
		
	}
	
	
	//生成圆二维码
	public function toround($imgs,$path='./'){
		//       $w = 100;  $h=100; // original size
		$sizeImg = $this->tosize($imgs['src'], 201);
		header('content-type:image/png');
		imagepng($sizeImg,$imgs['src']);
		//       $dest_path = $path.uniqid().'.png';
		$src = imagecreatefromstring(file_get_contents($imgs['src']));
		//取得源图片的宽度和高度
		list($w,$h)=getimagesize($imgs['src']);
		
		$newpic = imagecreatetruecolor($w,$h);
		imagealphablending($newpic,false);
		$transparent = imagecolorallocatealpha($newpic, 0, 0, 0, 127);
		
		imageantialias ( $transparent ,true );
		$r=$w/2;
		for($x=0;$x<$w;$x++)
			for($y=0;$y<$h;$y++){
				$c = imagecolorat($src,$x,$y);
				$_x = $x - $w/2;
				$_y = $y - $h/2;
				if((($_x*$_x) + ($_y*$_y)) < ($r*$r)){
					imagesetpixel($newpic,$x,$y,$c);
				}else{
					imagesetpixel($newpic,$x,$y,$transparent);
				}
		}
		
		imagesavealpha($newpic, true);
		
		//       header('content-type:image/png');
		//        imagepng($newpic);
		//        imagepng($newpic, $dest_path);
		imagedestroy($src);
		// unlink($url);
		return $newpic;
	}
	
	public function toroundb( $imgs,$path='./' ){
		$sizeImg = $this->tosize($imgs['src']);
		header('content-type:image/png');
		imagepng($sizeImg,$imgs['src']);
		
		$src = imagecreatefromstring(file_get_contents($imgs['src']));
		
		list($w,$h)=getimagesize($imgs['src']);
		
		$newpic = imagecreatetruecolor($w,$h);
		imagealphablending($newpic,false);
		$transparent = imagecolorallocatealpha($newpic, 0, 0, 0, 127);
		
		imageantialias ( $transparent ,true );
		$r=$w/1.487;
		for($x=0;$x<$w;$x++)
			for($y=0;$y<$h;$y++){
				$c = imagecolorat($src,$x,$y);
				$_x = $x - $w/2;
				$_y = $y - $h/2;
				if((($_x*$_x) + ($_y*$_y)) < ($r*$r)){
					imagesetpixel($newpic,$x,$y,$c);
				}else{
					imagesetpixel($newpic,$x,$y,$transparent);
				}
		}
		
		imagesavealpha($newpic, true);
		
		//       header('content-type:image/png');
		//        imagepng($newpic);
		//        imagepng($newpic, $dest_path);
		imagedestroy($src);
		// unlink($url);
		return $newpic;
	}
	
	private function getPointNews( $point ){
		static $flag = 1;
		for( $i = 0;$i<10;$i++){
			if(  $point < C( 'linePoint'.$flag ) ){
				break;
			}
			$flag++;
		}
		$data['lineInfo'] = C( 'lineInfo'.($flag-1) );
		$data['flag'] = $point >= C('jige')? 1:0 ;
		return $data;
	}
	
}

