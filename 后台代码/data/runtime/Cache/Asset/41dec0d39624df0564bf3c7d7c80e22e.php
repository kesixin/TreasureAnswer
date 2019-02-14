<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<!-- Set render engine for 360 browser -->
	<meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- HTML5 shim for IE8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <![endif]-->

	<link href="/public/simpleboot/themes/<?php echo C('SP_ADMIN_STYLE');?>/theme.min.css" rel="stylesheet">
    <link href="/public/simpleboot/css/simplebootadmin.css" rel="stylesheet">
    <link href="/public/js/artDialog/skins/default.css" rel="stylesheet" />
    <link href="/public/simpleboot/font-awesome/4.4.0/css/font-awesome.min.css"  rel="stylesheet" type="text/css">
    <style>
		form .input-order{margin-bottom: 0px;padding:3px;width:40px;}
		.table-actions{margin-top: 5px; margin-bottom: 5px;padding:0px;}
		.table-list{margin-bottom: 0px;}
	</style>
	<!--[if IE 7]>
	<link rel="stylesheet" href="/public/simpleboot/font-awesome/4.4.0/css/font-awesome-ie7.min.css">
	<![endif]-->
	<script type="text/javascript">
	//全局变量
	var GV = {
	    ROOT: "/",
	    WEB_ROOT: "/",
	    JS_ROOT: "public/js/",
	    APP:'<?php echo (MODULE_NAME); ?>'/*当前应用名*/
	};
	</script>
    <script src="/public/js/jquery.js"></script>
    <script src="/public/js/layer/layer.js"></script>
    <script src="/public/js/wind.js"></script>
    <script src="/public/simpleboot/bootstrap/js/bootstrap.min.js"></script>
    <script>
    	$(function(){
    		$("[data-toggle='tooltip']").tooltip();
    	});
    </script>
<?php if(APP_DEBUG): ?><style>
		#think_page_trace_open{
			z-index:9999;
		}
	</style><?php endif; ?>
<style>
.file-upload-btn-wrapper {
	margin-bottom: 10px;
}

.file-upload-btn-wrapper .num {
	color: #999999;
	float: right;
	margin-top: 5px;
}

.file-upload-btn-wrapper .num em {
	color: #FF5500;
	font-style: normal;
}

.files-wrapper {
	border: 1px solid #CCCCCC;
}

.files-wrapper ul {
	height: 280px;
	overflow-y: scroll;
	padding-bottom: 10px;
	position: relative;
	margin: 0;
}

.files-wrapper li {
	display: inline;
	float: left;
	height: 100px;
	margin: 10px 0 0 10px;
	width: 100px;
	position: relative;
	border:1px solid #CCCCCC;
}

.files-wrapper li.selected {
	border: 1px solid #fe781e;
}
.files-wrapper li .upload-percent{
	width: 100%;
	height:100%;
	line-height: 100px;
	text-align: center;
	text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
}

.files-wrapper li .selected-icon-wrapper{
	position: absolute;
    right: 0;
    top: 0;
    width: 20px;
    height: 20px;
    font-size: 16px;
	text-align:center;
	line-height:20px;
	color:#fe781e;
	display: none;
}
.files-wrapper li.selected .selected-icon-wrapper{
	display: block;
}
.files-wrapper li img{
	width: 100%;
	height: 100%;
    vertical-align: top;
}
.nav-tabs>li>a {
    color: #95a5a6;
}
.nav-tabs>.active>a, .nav-tabs>.active>a:hover, .nav-tabs>.active>a:focus {
    color: #39f;
    background-color: transparent;
    border: none;
}
.btn_sort {
	background: #6c9;
}
</style>
<script>
	function get_selected_files(){
		var tab = $("#uploader-tabs li.active").data('tab');
		var files= [];
		if(tab=='upload-file'){
			var $files=$('#files-container li.uploaded.selected');
			if($files.length==0){
				alert('请上传文件！');
				return false;
			}
			$files.each(function(){
				var $this=$(this);
				var url = $this.data('url');
				var preview_url = $this.data('preview_url');
				var filepath = $this.data('filepath');
				var name = $this.data('name');
				var id = $this.data('id');
				files.push({url:url,preview_url:preview_url,filepath:filepath,name:name,id:id});
			});
		}
		if(tab=='network-file'){
			var url=$('#network-file-input').val();
			if(url==''){
				alert('请填写文件地址！');
				return false;
			}
			var id = "networkfile"+new Date().getTime();
			files.push({url:url,preview_url:url,filepath:url,id:id});
		}
		return files;
	}
</script>
</head>
<body>
	<div class="wrap" style="padding: 5px;">
		<ul class="nav nav-tabs" id="uploader-tabs">
			<li class="active" data-tab="upload-file"><a href="#upload-file-tab" data-toggle="tab">上传文件</a></li>
			<li data-tab="network-file"><a href="#network-file-tab" data-toggle="tab">网络文件</a></li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="upload-file-tab">
				<div class="file-upload-btn-wrapper">
					<!--选择按钮-->
					<div id="container" style="display: inline-block;">
					<a class="btn btn-primary btn_sort" id="select-files">
						选择文件
					</a>
					</div>
					<span class="num">
						<?php if(empty($multi)): ?>最多上传<em>1</em>个附件,<?php endif; ?>
						单文件最大<em><?php echo ($upload_max_filesize_mb); ?>MB</em>,
						<em style="cursor: help;" title="可上传格式：<?php echo ($extensions); ?>" data-toggle="tooltip">支持格式？</em>
					</span>
				</div>
				<div class="files-wrapper">
					<ul id="files-container">
					</ul>
				</div>
			</div>
			<div class="tab-pane" id="network-file-tab">
				请输入网络地址<br>
				<input type="text" name="info[filename]" style="width: 600px;" placeholder="http://" id="network-file-input">
			</div>
		</div>
	</div>
	<script>
		var multi=<?php echo ($multi); ?>;
		var mime_type = <?php echo ($mime_type); ?>;
		var extensions = mime_type.extensions.split(/,/);
		var mimeTypeRegExp=new RegExp('\\.(' + extensions.join('|') + ')$', 'i');

		Wind.use('plupload',function(){
			var uploader = new plupload.Uploader({
				runtimes : 'html5,flash,silverlight,html4',
				browse_button : 'select-files', // you can pass an id...
				container: document.getElementById('container'), // ... or DOM Element itself
				url : "<?php echo U('asset/asset/plupload');?>",
				flash_swf_url : '/public/js/plupload/Moxie.swf',
				silverlight_xap_url : '/public/js/plupload/Moxie.xap',
				filters : {
					max_file_size : '<?php echo ($upload_max_filesize_mb); ?>mb'/* ,
					mime_types: [<?php echo ($mime_type); ?>] */
				},
				multi_selection:<?php echo ($multi); ?>,
				multipart_params:{
					app:'<?php echo ($app); ?>'
				},
				init: {
					PostInit: function() {
					},

					FilesAdded: function(up, files) {
						plupload.each(files, function(file) {
							var $newfile=$('\
									<li class="selected">\
										<div class="selected-icon-wrapper"><i class="fa fa-check-circle"></i></div>\
										<div class="upload-percent">0%</div>\
									</li>');
							$newfile.attr('id',file.id);
							$('#files-container').append($newfile);
							$newfile.on('click',function(){
								var $this=$(this);
								$this.toggleClass('selected');
							});
						});
						uploader.start();
					},

					UploadProgress: function(up, file) {
						$('#'+file.id).find('.upload-percent').text(file.percent+"%");
					},

					FileUploaded: function(up, file, response) {
						var data = JSON.parse(response.response);
						if(data.status==1){
							if(!multi) {
								$('#select-files').css('visibility','hidden');
								$('#container').css('visibility','hidden');
							}
							var $file=$('#'+file.id);
							$file.addClass('uploaded')
							.data('id',file.id)
							.data('url',data.url)
							.data('preview_url',data.preview_url)
							.data('filepath',data.filepath)
							.data('name',data.name);

							if(data.url.match(/\.(jpeg|gif|jpg|png|bmp|pic)$/gi)){
								var $img=$('<img/>');
								$img.attr('src',data.url);
								$file.find('.upload-percent')
								.html($img);
							}else{
								$file.find('.upload-percent').attr('title',data.name).text(data.name);
							}
						}else{
							alert(data.message);
							$('#'+file.id).remove();
						}
					},

					Error: function(up, err) {
					}
				}
			});

			plupload.addFileFilter('mime_types', function(filters, file, cb) {
				if (!mimeTypeRegExp.test(file.name)) {
					this.trigger('Error', {
						code : plupload.FILE_EXTENSION_ERROR,
						message : plupload.translate('File extension error.'),
						file : file
					});
					alert("此文件类型不能上传!\n"+file.name);
					cb(false);
				} else {
					cb(true);
				}
			});


			uploader.init();

		});
	</script>
</body>
</html>