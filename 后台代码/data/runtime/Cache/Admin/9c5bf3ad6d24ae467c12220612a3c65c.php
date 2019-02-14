<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>智慧云夺题答宝-后台管理系统</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="/public/adminlte/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="/public/adminlte/bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="/public/adminlte/bower_components/Ionicons/css/ionicons.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="/public/adminlte/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
  <link rel="stylesheet" href="/public/adminlte/bower_components/Buttons-1.5.1/css/buttons.bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="/public/adminlte/dist/css/AdminLTE.css">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="/public/adminlte/dist/css/skins/_all-skins.css">
<!--   <link rel="stylesheet" href="/public/adminlte/dist/css/skins/me_all-skins.css"> -->
  <!-- 双向日历选择器 -->
  <link rel="stylesheet" type="text/css" href="/public/adminlte/bower_components/picket_date/iconfont.css">
  <link rel="stylesheet" type="text/css" href="/public/adminlte/bower_components/picket_date/daterangepicker.css">
  <!-- myAlert -->
  <link rel="stylesheet" href="/public/adminlte/bower_components/myAlert/myAlert.min.css">

  <link rel="stylesheet" href="/public/adminlte/dist/css/common.css">

  <style>
  .dataTablelayout {float: right; margin-left: 30px}
  </style>

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

  <!-- Google Font -->
  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

<link href="/public/js/artDialog/skins/default.css" rel="stylesheet" />

<script type="text/javascript">
	//全局变量
	var GV = {
	    ROOT: "/",
	    WEB_ROOT: "/",
	    JS_ROOT: "public/js/",
	    APP:'<?php echo (MODULE_NAME); ?>'/*当前应用名*/
	};
	</script>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <header class="main-header">
    <!-- Logo -->
    <!-- Sidebar user panel -->
      <div class="user-panel logo">
        <div class="pull-left image">
          <img src="/public/adminlte/dist/img/logo.png" alt="User Image">  <!-- class="img-circle" -->
        </div>
        <div class="pull-left info">
          <p>夺宝答题后台</p>
        </div>
      </div>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <!-- <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a> -->

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <!-- User Account: style can be found in dropdown.less -->
          <li class="user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <span class="hidden-xs">Admin</span>
              <img src="/public/adminlte/dist/img/user2-160x160.jpg" class="user-image" alt="User Image">
            </a>
          </li>
          <li class="dropdown user user-menu">
            <a href="#" class="sidebar-toggle sdropdown-toggle" data-toggle="dropdown">
            </a>
            <ul class="dropdown-menu">
              <div style="line-height: 0; padding-right: 8px; overflow: hidden; text-align: right;"><img src="/public/adminlte/dist/img/triangle.png"></div>
              <!-- <li class="first-child"><a><i class="fa fa-cog"></i>设置</a></li> -->
              <li class="first-child"><a href="<?php echo U('JBsetting/Changepwd');?>"><i class="fa fa-lock"></i>修改密码</a></li>
              <li><a href="<?php echo U('Public/logout');?>"><i class="fa fa-power-off"></i>退出登录</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </nav>
  </header>
  <!-- Left side column. contains the logo and sidebar -->
    <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <ul class="sidebar-menu" data-widget="tree">
        <!-- <li class="header">主导航</li> -->
        <!-- 多个菜单 -->
        <li class="treeview <?php if((CONTROLLER_NAME) == "Statis"): ?>menu-open<?php endif; ?>">
          <a href="#">
            <i class="fa fa-pie-chart"></i> <span>统计分析</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu " <?php if((CONTROLLER_NAME) == "Statis"): ?>style="display: block;"<?php endif; ?>>
            <li><a class="<?php if((CONTROLLER_NAME) == "Statis"): if((ACTION_NAME) == "index"): ?>active<?php endif; endif; ?>" href="<?php echo U('Statis/index');?>">概况统计</a></li>
            <li><a class="<?php if((CONTROLLER_NAME) == "Statis"): if((ACTION_NAME) == "statsReply"): ?>active<?php endif; endif; ?>" href="<?php echo U('Statis/statsReply');?>">答对率统计</a></li>
          </ul>
        </li>
         <li class="<?php if((CONTROLLER_NAME) == "Gsetting"): if((ACTION_NAME) == "Gsetting"): ?>on<?php endif; endif; ?>">
          <a  href="<?php echo U('Gsetting/Gsetting');?>">
            <i class="fa fa-gamepad"></i> <span>游戏设置</span>
          </a>
        </li>
        <!-- /单个菜单 -->
        <li class="treeview <?php if((CONTROLLER_NAME) == "TK"): ?>menu-open<?php endif; ?>">
          <a href="#">
            <i class="fa fa-list-alt"></i> <span>题库管理</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu " <?php if((CONTROLLER_NAME) == "TK"): ?>style="display: block;"<?php endif; ?>>
            <li><a class="<?php if((CONTROLLER_NAME) == "TK"): if((ACTION_NAME) == "TKdisplay"): ?>active<?php endif; endif; ?>" href="<?php echo U('TK/TKdisplay');?>">题库展示</a></li>
            <li><a class="<?php if((CONTROLLER_NAME) == "TK"): if((ACTION_NAME) == "typeIndex"): ?>active<?php endif; endif; ?>" href="<?php echo U('TK/typeIndex');?>">题库设置</a></li>
            <li><a class="<?php if((CONTROLLER_NAME) == "TK"): if((ACTION_NAME) == "TKinto"): ?>active<?php endif; endif; ?>" href="<?php echo U('TK/TKinto');?>">题库导入</a></li>
          </ul>
        </li>
        <li class="<?php if((CONTROLLER_NAME) == "UserAdmin"): if((ACTION_NAME) == "userAdmin"): ?>on<?php endif; endif; ?>">
          <a  href="<?php echo U('userAdmin/userAdmin');?>">
            <i class="fa fa-user-circle"></i> <span>用户管理</span>
          </a>
        </li>
        <li class="treeview <?php if((CONTROLLER_NAME) == "Psetting"): ?>menu-open<?php endif; ?>">
          <a href="#">
            <i class="fa fa-address-book-o"></i> <span>中奖管理</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu" <?php if((CONTROLLER_NAME) == "Psetting"): ?>style="display: block;"<?php endif; ?>>
            <li><a class="<?php if((CONTROLLER_NAME) == "Psetting"): if((ACTION_NAME) == "Psetting"): ?>active<?php endif; endif; ?>" href="<?php echo U('Psetting/Psetting');?>">奖品设置</a></li>
            <li><a class="<?php if((CONTROLLER_NAME) == "Psetting"): if((ACTION_NAME) == "Pdetail"): ?>active<?php endif; endif; ?>" href="<?php echo U('Psetting/Pdetail');?>">中奖明细</a></li>
            <li><a class="<?php if((CONTROLLER_NAME) == "Psetting"): if((ACTION_NAME) == "Plogic"): ?>active<?php endif; endif; ?>" href="<?php echo U('Psetting/Plogic');?>">物流管理</a></li>
          </ul>
        </li>

        <li class="<?php if((CONTROLLER_NAME) == "Paydetail"): if((ACTION_NAME) == "Paydetail"): ?>on<?php endif; endif; ?>">
          <a  href="<?php echo U('Paydetail/Paydetail');?>">
            <i class="fa fa-credit-card"></i> <span>支付明细</span>
          </a>
        </li>

        <li class="<?php if((CONTROLLER_NAME) == "TGsetting"): if((ACTION_NAME) == "TGsetting"): ?>on<?php endif; endif; ?>">
          <a  href="<?php echo U('TGsetting/TGsetting');?>">
            <i class="fa fa-bullhorn"></i> <span>推广设置</span>
          </a>
        </li>

        <li class="<?php if((CONTROLLER_NAME) == "Appsetting"): if((ACTION_NAME) == "APPsetting"): ?>on<?php endif; endif; ?>">
          <a  href="<?php echo U('Appsetting/APPsetting');?>">
            <i class="fa fa-link"></i> <span>小程序设置</span>
          </a>
        </li>

        <li class="treeview <?php if((CONTROLLER_NAME) == "JBsetting"): ?>menu-open<?php endif; ?>">
          <a href="#">
            <i class="fa fa-cog"></i> <span>基本设置</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu" <?php if((CONTROLLER_NAME) == "JBsetting"): ?>style="display: block;"<?php endif; ?>>
            <li><a class="<?php if((CONTROLLER_NAME) == "JBsetting"): if((ACTION_NAME) == "Paysetting"): ?>active<?php endif; endif; ?>" href="<?php echo U('JBsetting/Paysetting');?>">支付设置</a></li>
            <li><a class="<?php if((CONTROLLER_NAME) == "JBsetting"): if((ACTION_NAME) == "Custsetting"): ?>active<?php endif; endif; ?>" href="<?php echo U('JBsetting/Custsetting');?>">客服自动回复设置</a></li>
            <li><a class="<?php if((CONTROLLER_NAME) == "JBsetting"): if((ACTION_NAME) == "Changepwd"): ?>active<?php endif; endif; ?>" href="<?php echo U('JBsetting/Changepwd');?>">修改密码</a></li>
          </ul>
        </li>
      </ul>
      <div class="zhy_logo"><img src="/public/adminlte/dist/img/zhy_login_ico.png"></div>
    </section>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h3>
        推广设置
        <small></small>
      </h3>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
              <!-- 题库1 start -->
              <div class="box-body">
                <!-- <button style="display: none;" type="button" class="btn btn-default" style="border: 1px solid #eee; color: #666" id="daterange-btn1">
                  <span>
                    <i class="icon iconfont icon-calendar1"></i> 日期选择
                  </span>
                  <i class="icon iconfont icon-danxian-youjiantou-copy"></i>
                </button> -->
                <table id="example1" class="table table-bordered table-striped">
                  <thead class="thead">
                    <tr>
                      <th>序号</th>
                      <th>小程序名称</th>
                      <th>小程序头像</th>
                      <th>描述1</th>
                      <th>描述2</th>
                      <th>操作</th>
                    </tr>
                  </thead>
                  <tbody>

                  </tbody>
                </table>
              </div>
              <!-- 题库1 end -->
          </div>

        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  <footer class="main-footer">
  <div class="pull-right hidden-xs">
    <b>Version</b> 2.4.0
  </div>
  <strong>Copyright &copy; 2014-2016 <a href="https://adminlte.io">Almsaeed Studio</a>.</strong> All rights
  reserved.
</footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Create the tabs -->
    <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
      <li><a href="#control-sidebar-home-tab" data-toggle="tab"><i class="fa fa-home"></i></a></li>
      <li><a href="#control-sidebar-settings-tab" data-toggle="tab"><i class="fa fa-gears"></i></a></li>
    </ul>
    <!-- Tab panes -->
    <div class="tab-content">
      <!-- Home tab content -->
      <div class="tab-pane" id="control-sidebar-home-tab">
        <h3 class="control-sidebar-heading">Recent Activity</h3>
        <ul class="control-sidebar-menu">
          <li>
            <a href="javascript:void(0)">
              <i class="menu-icon fa fa-birthday-cake bg-red"></i>

              <div class="menu-info">
                <h4 class="control-sidebar-subheading">Langdon's Birthday</h4>

                <p>Will be 23 on April 24th</p>
              </div>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)">
              <i class="menu-icon fa fa-user bg-yellow"></i>

              <div class="menu-info">
                <h4 class="control-sidebar-subheading">Frodo Updated His Profile</h4>

                <p>New phone +1(800)555-1234</p>
              </div>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)">
              <i class="menu-icon fa fa-envelope-o bg-light-blue"></i>

              <div class="menu-info">
                <h4 class="control-sidebar-subheading">Nora Joined Mailing List</h4>

                <p>nora@example.com</p>
              </div>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)">
              <i class="menu-icon fa fa-file-code-o bg-green"></i>

              <div class="menu-info">
                <h4 class="control-sidebar-subheading">Cron Job 254 Executed</h4>

                <p>Execution time 5 seconds</p>
              </div>
            </a>
          </li>
        </ul>
        <!-- /.control-sidebar-menu -->

        <h3 class="control-sidebar-heading">Tasks Progress</h3>
        <ul class="control-sidebar-menu">
          <li>
            <a href="javascript:void(0)">
              <h4 class="control-sidebar-subheading">
                Custom Template Design
                <span class="label label-danger pull-right">70%</span>
              </h4>

              <div class="progress progress-xxs">
                <div class="progress-bar progress-bar-danger" style="width: 70%"></div>
              </div>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)">
              <h4 class="control-sidebar-subheading">
                Update Resume
                <span class="label label-success pull-right">95%</span>
              </h4>

              <div class="progress progress-xxs">
                <div class="progress-bar progress-bar-success" style="width: 95%"></div>
              </div>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)">
              <h4 class="control-sidebar-subheading">
                Laravel Integration
                <span class="label label-warning pull-right">50%</span>
              </h4>

              <div class="progress progress-xxs">
                <div class="progress-bar progress-bar-warning" style="width: 50%"></div>
              </div>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)">
              <h4 class="control-sidebar-subheading">
                Back End Framework
                <span class="label label-primary pull-right">68%</span>
              </h4>

              <div class="progress progress-xxs">
                <div class="progress-bar progress-bar-primary" style="width: 68%"></div>
              </div>
            </a>
          </li>
        </ul>
        <!-- /.control-sidebar-menu -->

      </div>
      <!-- /.tab-pane -->
      <!-- Stats tab content -->
      <div class="tab-pane" id="control-sidebar-stats-tab">Stats Tab Content</div>
      <!-- /.tab-pane -->
      <!-- Settings tab content -->
      <div class="tab-pane" id="control-sidebar-settings-tab">
        <form method="post">
          <h3 class="control-sidebar-heading">General Settings</h3>

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Report panel usage
              <input type="checkbox" class="pull-right" checked>
            </label>

            <p>
              Some information about this general settings option
            </p>
          </div>
          <!-- /.form-group -->

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Allow mail redirect
              <input type="checkbox" class="pull-right" checked>
            </label>

            <p>
              Other sets of options are available
            </p>
          </div>
          <!-- /.form-group -->

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Expose author name in posts
              <input type="checkbox" class="pull-right" checked>
            </label>

            <p>
              Allow the user to show his name in blog posts
            </p>
          </div>
          <!-- /.form-group -->

          <h3 class="control-sidebar-heading">Chat Settings</h3>

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Show me as online
              <input type="checkbox" class="pull-right" checked>
            </label>
          </div>
          <!-- /.form-group -->

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Turn off notifications
              <input type="checkbox" class="pull-right">
            </label>
          </div>
          <!-- /.form-group -->

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Delete chat history
              <a href="javascript:void(0)" class="text-red pull-right"><i class="fa fa-trash-o"></i></a>
            </label>
          </div>
          <!-- /.form-group -->
        </form>
      </div>
      <!-- /.tab-pane -->
    </div>
  </aside>
  <!-- /.control-sidebar -->
  <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
  <!-- <div class="control-sidebar-bg"></div> -->
</div>
<!-- ./wrapper -->

<!-- jQuery 3 -->
<script src="/public/adminlte/bower_components/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="/public/adminlte/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- DataTables -->
<script src="/public/adminlte/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="/public/adminlte/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<script src="/public/adminlte/bower_components/Buttons-1.5.1/js/dataTables.buttons.min.js"></script>
<script src="/public/adminlte/bower_components/Buttons-1.5.1/js/buttons.bootstrap.min.js"></script>

<!-- SlimScroll -->
<script src="/public/adminlte/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="/public/adminlte/bower_components/fastclick/lib/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="/public/adminlte/dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="/public/adminlte/dist/js/demo.js"></script>

<!-- 日期双向选择器 -->
<script src="/public/adminlte/bower_components/picket_date/moment.js"></script>
<script src="/public/adminlte/bower_components/picket_date/daterangepicker.js"></script>
<!-- page script -->

<!-- myAlert -->
<script src="/public/adminlte/bower_components/myAlert/myAlert.min.js"></script>

<script type="text/javascript" src="/public/js/common.js"></script>

<script src="/public/js/wind.js"></script>

<script type="text/javascript">

  var gobalDt;
  var global_row;
  var list = [
    {
      id:"1",
      name:"比赛",
      app_img:"http://pic.cr173.com/up/2017-1/2017120835278034.png",
      des1:"赛一赛",
      des2:"我也来玩玩",
    },
    {
      id:"2",
      name:"我爱学习",
      app_img:"http://5b0988e595225.cdn.sohucs.com/a_auto,c_cut,x_0,y_0,w_800,h_800/images/20171213/64183fe3eb4741c9a0ce91a8e3df6d2c.jpeg",
      des1:"教育学习",
      des2:"我也来玩玩",
    },
    {
      id:"3",
      name:"拼多多",
      app_img:"http://src.onlinedown.net/images/xcs/10/2017-08-13_598ff9c0420e9.png",
      des1:"网购",
      des2:"我也来玩玩",
    }
  ]

  // 上传显示图片
  $("#fileupload").click(function() {
  console.log(111)
  var objUrl = $file[0].files[0];
  if(objUrl){
    console.log(objUrl)
  }
  })
  $("#fileupload").change(function() {
    console.log(111)
    var $file = $(this);
    var objUrl = $file[0].files[0];
    //获得一个http格式的url路径:mozilla(firefox)||webkit or chrome
    var windowURL = window.URL || window.webkitURL;
    //createObjectURL创建一个指向该参数对象(图片)的URL
    var dataURL;
    dataURL = windowURL.createObjectURL(objUrl);
    console.log(objUrl)
    if(dataURL){
      $("#imageview").attr("src",dataURL);
      $("#fa_pic_ico").hide();  //隐藏图片块
    }
  });


  //删除一条记录
  function removeType(tid,this_row){
    global_row = gobalDt.row($(this_row).parent().parent());
    var row_data = global_row.data();
    $.myConfirm({
      title:'提示',
      message:'确定删除ID为'+tid+'的记录吗',
      callback:function(){
        global_row.remove().draw();

        $.ajax({
          url: 'ajaxDelTG',
          type: 'POST',
          dataType: 'json',
          data:{'id':tid},
          success:function(data){
              if( data.code == 1 ){
                $.myToast("删除成功!");
              }
              $.myToast( data.msg );
          }
        });


          
      }
    });
  }

  $(function () {
    gobalDt =  $('#example1').DataTable({
    		"aLengthMenu":[3,15,20],
        "bServerSide" : true,//服务器处理分页，默认是false，需要服务器处理，必须true
        "sAjaxDataProp" : "data",//是服务器分页的标志，必须有
        "sAjaxSource" : "<?php echo U('TGsetting/appList');?>",//通过ajax实现分页的url路径。
        "columns": [
          { data: "id"},
          { data: "app_name"},
          { data: "logo",render:function(data, type , row){
          return "<img style='width:50px' src='"+data+"' />";
          }},
          { data: "app_desc"},
          { data: "app_desc1"},
          { data: null, "render": function(data, type,row) {
          var id = '"' + row.id + '"';
          var html = '';//"<a href='javascript:void(0);'  class='delete btn btn-default btn-xs'  ><i class='fa fa-times'></i> 查看</a>"
          html += '<button type="button" class="table_btn" data-toggle="modal" data-target="#addModal" data-whatever='+id+'>编辑</button>'
          html += '<button type="button" class="table_btn" onclick="removeType('+row.id+',this);">删除</button>'
          return html;
          }},
        ],
        dom: '<"dataTablelayout"l><"dataTablelayout"B>t<"dataTablelayout"i>',
        buttons: {
            buttons: [
                {
                    text: '添加',
                    className: 'add_btn',
                    action: function ( dt ) {
                        $('#addModal').modal('show');
                       // console.log( 'My custom button!' );
                    }
                }
            ]
        },
        "columnDefs":[
          //{ className: "dt-center", targets: [ 0,1,2,3 ] }

        ],
        "search": {
            "search": ""
        },
        "aoColumnDefs": [
          {"orderable":false,"aTargets":[1,2]}// 制定列不参与排序
        ],
        "language": {
          "decimal":        "",
          "emptyTable":     "暂无记录", //No data available in table
          "info":           "", //Showing _START_ to _END_ of _TOTAL_ entries
          "infoEmpty":      "", //Showing 0 to 0 of 0 entries
          "infoFiltered":   "", //(filtered from _MAX_ total entries)
          "infoPostFix":    "",
          "thousands":      ",",
          "lengthMenu":     "", //Show _MENU_ entries
          "loadingRecords": "加载中...",
          "processing":     "处理中...",
          "search":         "搜索:",
          "zeroRecords":    "未找到相关记录",
          "paginate": {
              "first":      "第一页",
              "last":       "最后一页",
              "next":       "下一页",
              "previous":   "上一页"
          },
          "aria": {
              "sortAscending":  ": activate to sort column ascending",
              "sortDescending": ": activate to sort column descending"
          },
        },

          // "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]]
    });
  /*   $('#example2').DataTable({
      'paging'      : true,
      'lengthChange': false,
      'searching'   : false,
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false
    }) */
    // console.log(list)
    $('#addModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget) // Button that triggered the modal
        var recipient = button.data('whatever')
        var modal = $(this)

        var modal_title = '添加';
        var modal_footer = '添加';
        //console.log(recipient);
        modal.find('.modal-body input[name="id"]').val('');
        modal.find('.modal-body input[name="app_name"]').val('');
        $("#thumb-preview").attr('src', '/admin/themes/simplebootx/Public/assets/images/default-thumbnail.png');

        modal.find('.modal-body input[name="app_desc"]').val('');
        modal.find('.modal-body input[name="app_desc1"]').val('');
        if(recipient){
          global_row = gobalDt.row(button.parent().parent());
          var row_data = global_row.data();
          modal_title = '修改';
          modal_footer = '保存';
          modal.find('.modal-body input[name="id"]').val(recipient)
          modal.find('.modal-body input[name="app_name"]').val(row_data.app_name);
          modal.find('.modal-body input[name="app_id"]').val(row_data.app_id);
          
          $("#thumb-preview").attr('src', row_data.logo);
          console.log()
          modal.find('.modal-body input[name="app_desc"]').val(row_data.app_desc);
          modal.find('.modal-body input[name="app_desc1"]').val(row_data.app_desc1);
          //异步获取数据填入form
          // $.ajax({
          //   //几个参数需要注意一下
          //     type: "POST",//方法类型
          //     dataType: "json",//预期服务器返回的数据类型
          //     url: "" ,//url  <?php echo U('Question/getTypeInfo');?>
          //     data: {tid:recipient},
          //     success: function (res) {
          //         console.log(res);//打印服务端返回的数据(调试用)
          //         if (res.result) {
          //             modal.find('.modal-body input[name="quest_type_name"]').val(res.data.quest_type_name);
          //             modal.find('.modal-body textarea[name="remark"]').val(res.data.remark);
          //             modal.find('.modal-body input[name="typeId"]').val(res.data.id);
          //         }
          //     },
          //     error : function() {
          //         $.myToast("请求异常!");
          //     }
          // });
        }
        modal.find('.modal-title').text(modal_title)
        modal.find('#submitButton').text(modal_footer)

      })

      //弹出框提示提交按钮
      $('#submitButton').click(function(){
        
       $.ajax({
           //几个参数需要注意一下
           type: "POST",//方法类型
           dataType: "json",//预期服务器返回的数据类型
           url:  '<?php echo U('TGsetting/addOrEditInfo');?>',
           data: $('#dialogForm').serialize(),
           success: function (res) {
               if (res.result) {
                   $('#addModal').modal('hide');
                   gobalDt.ajax.reload();
               }
               $.myToast(res.msg);
           },
           error : function() {
               $.myToast("请求异常!");
           }
         });
      });

      // 时间选择器日期
    $('.ranges_1 ul').remove();
    // 时间选择器日期1
    $('#daterange-btn1').daterangepicker({
        ranges: {
            '全部': [moment(), moment().subtract(-1, 'days')],
            '今天': [moment(), moment()],
            '明天': [moment().subtract(-1, 'days'), moment().subtract(-1, 'days')],
            '未来七天': [moment(),moment().subtract(-6, 'days')],
            '未来30天': [moment(),moment().subtract(-29, 'days')],
            '未来60天': [moment(),moment().subtract(-59, 'days'), ]
        },
        startDate: moment(),
        endDate: moment()
      },
      function(start, end,label) {
          //label:通过它来知道用户选择的是什么，传给后台进行相应的展示
          if(label=='全部'){
              $('#daterange-btn1 span').html('全部');
          }else if(label=='今天'){
              $('#daterange-btn1 span').html(start.format('YYYY/MM/DD'));
          }else if(label=='明天'){
              $('#daterange-btn1 span').html(start.format('YYYY/MM/DD'));
          }else if(label=='未来七天'){
              $('#daterange-btn1 span').html(start.format('YYYY/MM/DD')+'-'+end.format('YYYY/MM/DD'));
          }else if(label=='未来30天'){
              $('#daterange-btn1 span').html(start.format('YYYY/MM/DD')+'-'+end.format('YYYY/MM/DD'));
          }else if(label=='未来60天'){
              $('#daterange-btn1 span').html(start.format('YYYY/MM/DD')+'-'+end.format('YYYY/MM/DD'));
          }

      }
    );

  })
</script>

<!-- 弹出框 -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="exampleModalLabel">New message</h4>
      </div>
      <div class="modal-body">
        <form id="dialogForm">
          <input type="hidden" name="id" value="" />
          <div class="form-group">
            <label for="" class="control-label">小程序名称:</label>
            <input type="text" class="form-control" name="app_name" >
          </div>
          <div class="form-group">
            <label for="" class="control-label">小程序appid:</label>
            <input type="text" class="form-control" name="app_id" >
          </div>
          <div class="form-group">
            <label for="" class="control-label">小程序头像:</label>
            <div style="/*text-align: center;*/margin-left: 180px;">
									<input type="hidden" name="logo" id="thumb" value="">
									<a href="javascript:upload_one_image('图片上传','#thumb');">
									
									<img src="/admin/themes/simplebootx/Public/assets/images/default-thumbnail.png" id="thumb-preview" width="135" style="cursor: hand; height: 113px;">									</a>
									
									<input type="button" class="btn btn-small" onclick="$('#thumb-preview').attr('src','/admin/themes/simplebootx/Public/assets/images/default-thumbnail.png');$('#thumb').val('');return false;" value="取消图片">
								</div>
          </div>
          <div class="form-group">
            <label for="" class="control-label">描述1:</label>
            <input type="text" class="form-control" name="app_desc" >
          </div>
          <div class="form-group">
            <label for="" class="control-label">描述2:</label>
            <input type="text" class="form-control" name="app_desc1" >
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button type="button" id="submitButton" class="btn btn-primary">提交</button>
      </div>
    </div>
  </div>
</div>
<!-- 弹出框 end -->


</body>
</html>