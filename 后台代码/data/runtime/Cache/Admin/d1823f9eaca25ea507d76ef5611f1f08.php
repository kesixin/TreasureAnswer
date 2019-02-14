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
        概况统计
        <small></small>
      </h3>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box box2">
            <ul class="index_block_data">
              <li class="li1">
                <h2><?php echo ($staisData["totolUser"]); ?>个</h2>
                <p>总用户数</p>
              </li>
              <li class="li2">
                <h2><?php echo ($staisData["yesterdayUser"]); ?>个</h2>
                <p>昨天新增用户数</p>
              </li>
              <li class="li3">
                <h2><?php echo ($staisData["totalChallenge"]); ?>次</h2>
                <p>总参赛次数</p>
              </li>
              <li class="li4">
                <h2><?php echo ($staisData["totalPass"]); ?>次</h2>
                <p>总挑战成功次数</p>
              </li>
              <li class="li5">
                <h2><?php echo ($staisData["totalIncome"]); ?>元</h2>
                <p>复活卡总收入</p>
              </li>
            </ul>
          </div>

          <!-- 用户增长趋势表 start -->
          <div class="box">
            <div class="box-header">
              <h3 class="box-title box-title1">用户增长趋势表</h3>
            </div>
            <div class="box-body">
              <div class="contain">
                <div class="col-xs-12">
                  <div class="bjui-pageContent">
                    <div class="form-group d_t_dater">
                      <label class="col-sm-3 control-label"></label>
                      <div class="col-sm-12">
                        <div class="input-group">
                          <p style="display: inline-block;">时间段:　</p>
                          <button type="button" class="btn btn-default" style="border: 1px solid #eee; color: #666" id="daterange-btn1">
                            <span>
                              <i class="icon iconfont icon-calendar1"></i> 日期选择
                            </span>
                            <i class="icon iconfont icon-danxian-youjiantou-copy"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-xs-12">
                  <div id="sheet1" style="width: 100%;height:400px;"></div>
                </div>
              </div>
            </div>
          </div>
          <!-- 用户增长趋势表 end -->

          <!-- 参赛次数增长趋势表 start -->
          <div class="box">
            <div class="box-header">
              <h3 class="box-title box-title2">参赛次数增长趋势表</h3>
            </div>
            <div class="box-body">
              <div class="contain">
                <div class="col-xs-12">
                  <div class="bjui-pageContent">
                    <div class="form-group d_t_dater">
                      <label class="col-sm-3 control-label"></label>
                      <div class="col-sm-12">
                        <div class="input-group">
                          <p style="display: inline-block;">时间段:　</p>
                          <button type="button" class="btn btn-default" style="border: 1px solid #eee; color: #666" id="daterange-btn2">
                            <span>
                              <i class="icon iconfont icon-calendar1"></i> 日期选择
                            </span>
                            <i class="icon iconfont icon-danxian-youjiantou-copy"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-xs-12">
                  <div id="sheet2" style="width: 100%;height:400px;"></div>
                </div>
              </div>
            </div>
          </div>
          <!-- 参赛次数增长趋势表 end -->

          <!-- 复活卡总收入增长趋势表 start -->
          <div class="box">
            <div class="box-header">
              <h3 class="box-title box-title3">复活卡总收入增长趋势表</h3>
            </div>
            <div class="box-body">
              <div class="contain">
                <div class="col-xs-12">
                  <div class="bjui-pageContent">
                    <div class="form-group d_t_dater">
                      <label class="col-sm-3 control-label"></label>
                      <div class="col-sm-12">
                        <div class="input-group">
                          <p style="display: inline-block;">时间段:　</p>
                          <button type="button" class="btn btn-default" style="border: 1px solid #eee; color: #666" id="daterange-btn3">
                            <span>
                              <i class="icon iconfont icon-calendar1"></i> 日期选择
                            </span>
                            <i class="icon iconfont icon-danxian-youjiantou-copy"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-xs-12">
                  <div id="sheet3" style="width: 100%;height:400px;"></div>
                </div>
              </div>
            </div>
          </div>
          <!-- 复活卡总收入增长趋势表 end -->



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
<!-- page script -->
<!-- 日期双向选择器 -->
<script src="/public/adminlte/bower_components/picket_date/moment.js"></script>
<script src="/public/adminlte/bower_components/picket_date/daterangepicker.js"></script>

<!-- 图标 -->
<script src="/public/adminlte/dist/js/echarts.common.min.js"></script>

<script type="text/javascript">
window.onload=function(){
  sheet1();
  sheet2();
  sheet3();
};

// $(".index_block_data") function(){

// }
var sheet1_x_data = [];//['2018/3/20','2018/3/21','2018/3/22','2018/3/23','2018/3/24','2018/3/25','2018/3/26'];
var sheet1_y_data = [];//[964, 784, 312, 742, 999, 1022, 753];

var sheet2_x_data = [];//['2018/3/20','2018/3/21','2018/3/22','2018/3/23','2018/3/24','2018/3/25','2018/3/26'];
var sheet2_y_data = [];//[12000, 13208, 10188, 13498, 9088, 23098, 21120];

var sheet3_x_data = [];//['2018/3/20','2018/3/21','2018/3/22','2018/3/23','2018/3/24','2018/3/25','2018/3/26'];
var sheet3_y_data = [];//[9991, 10220, 9996, 10766, 8900, 12004, 10222];


  //图标
  function sheet1(){
    // 基于准备好的dom，初始化echarts实例
    var myChart = echarts.init(document.getElementById('sheet1'));

    // 指定图表的配置项和数据
    option = {
      // title: {
      //     text: '折线图堆叠'
      // },
      tooltip: {
          trigger: 'axis'
      },
      // legend: {
      //     data:['用户增长趋势表']
      // },
      grid: {
          left: '3%',
          right: '4%',
          bottom: '3%',
          containLabel: true
      },
      // toolbox: {
      //     feature: {
      //         saveAsImage: {}
      //     }
      // },
      xAxis: {
          type: 'category',
          boundaryGap: false,
          data: sheet1_x_data
      },
      yAxis: {
          type: 'value',
      },
      series: [
          {
              name:'用户增长趋势(人)',
              type:'line',
              stack: '总量',
              data: sheet1_y_data
          }
      ],
      color:['#7FA0FF']   //底盘颜色
    };
    // 使用刚指定的配置项和数据显示图表。
    myChart.setOption(option);

  };
  //图标
  function sheet2(){
    // 基于准备好的dom，初始化echarts实例
    var myChart = echarts.init(document.getElementById('sheet2'));
    

    // 指定图表的配置项和数据
    option = {
      // title: {
      //     text: '折线图堆叠'
      // },
      tooltip: {
          trigger: 'axis'
      },
      // legend: {
      //     data:['用户增长趋势表']
      // },
      grid: {
          left: '3%',
          right: '4%',
          bottom: '3%',
          containLabel: true
      },
      // toolbox: {
      //     feature: {
      //         saveAsImage: {}
      //     }
      // },
      xAxis: {
          type: 'category',
          boundaryGap: false,
          data: sheet2_x_data
      },
      yAxis: {
          type: 'value',
      },
      series: [
          {
              name:'参赛次数增长(人)',
              type:'line',
              stack: '总量',
              data: sheet2_y_data
          }
      ],
      color:['#C47FFF']   //底盘颜色
    };
    // 使用刚指定的配置项和数据显示图表。
    myChart.setOption(option);

  };

  function sheet3(){
    // 基于准备好的dom，初始化echarts实例
    var myChart = echarts.init(document.getElementById('sheet3'));

    // 指定图表的配置项和数据
    option = {
      // title: {
      //     text: '折线图堆叠'
      // },
      tooltip: {
          trigger: 'axis'
      },
      // legend: {
      //     data:['用户增长趋势表']
      // },
      grid: {
          left: '3%',
          right: '4%',
          bottom: '3%',
          containLabel: true
      },
      // toolbox: {
      //     feature: {
      //         saveAsImage: {}
      //     }
      // },
      xAxis: {
          type: 'category',
          boundaryGap: false,
          data: sheet3_x_data
      },
      yAxis: {
          type: 'value',
      },
      series: [
          {
              name:'复活卡总收入增长(元)',
              type:'line',
              stack: '总量',
              data: sheet3_y_data
          }
      ],
      color:['#FFA886'],
    };
    // 使用刚指定的配置项和数据显示图表。
    myChart.setOption(option);

  };
  // 时间选择器日期
    $('.ranges_1 ul').remove();
    // 时间选择器日期1
    $('#daterange-btn1').daterangepicker({
        ranges: {
            '全部': [moment(), moment().subtract(-1, 'days')],
            '今天': [moment(), moment()],
            '昨天': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            '前七天': [moment().subtract('days', 6), moment()],
            '前30天': [moment().subtract('days', 29), moment()],
            '前60天': [moment().subtract('days', 59), moment()]
        },
        startDate: moment(),
        endDate: moment()
      },
      function(start, end,label) {
          //label:通过它来知道用户选择的是什么，传给后台进行相应的展示
          if(label=='全部'){
              $('#daterange-btn1 span').html('全部');
          }
          else if(label=='今天'){
              $('#daterange-btn1 span').html(start.format('YYYY/MM/DD'));
          }else if(label=='昨天'){
              $('#daterange-btn1 span').html(start.format('YYYY/MM/DD'));
          }else if(label=='前七天'){
              $('#daterange-btn1 span').html(start.format('YYYY/MM/DD')+'-'+end.format('YYYY/MM/DD'));
          }else if(label=='前30天'){
              $('#daterange-btn1 span').html(start.format('YYYY/MM/DD')+'-'+end.format('YYYY/MM/DD'));
          }else if(label=='前60天'){
              $('#daterange-btn1 span').html(start.format('YYYY/MM/DD')+'-'+end.format('YYYY/MM/DD'));
          }
          /* var start1_time = start.format('YYYY/MM/DD');
          var end1_time = end.format('YYYY/MM/DD'); */
          //console.log(start,end1_time)
          /*转换成时间戳*/
          var datetimestamp = Date.parse(start).toString();  
          var startTime = datetimestamp.substr(0,datetimestamp.length-3);  
          datetimestamp = Date.parse(end).toString();
          var endTime = datetimestamp.substr(0,datetimestamp.length-3);  
			/*转换成时间戳 end*/
			$.ajax({
               //几个参数需要注意一下
                   type: "POST",//方法类型
                   dataType: "json",//预期服务器返回的数据类型
                   url: "<?php echo U('Statis/chatStatis');?>" ,//url
                   data: {startTime:startTime, endTime:endTime, type:0},
                   success: function (res) {
                      if (res.result) {
                    	  	sheet1_y_data = res.dataArr;
                          sheet1_x_data = res.timeArr;
                          sheet1();
                       }
                      //$.myToast(res.msg);
                   },
                   error : function() {
                       $.myToast("请求异常!");
                   }
         });
			
          /* sheet1_y_data = [1, 1, 1, 1, 1, 13, 5];
          sheet1_x_data = ['2018/3/20','2018/3/21','2018/3/22','2018/3/23','2018/3/24','2018/3/25','2018/3/26'];
          sheet1(); */
      }
    );

    // 时间选择器日期2
    $('#daterange-btn2').daterangepicker({
        ranges: {
            '全部': [moment(), moment().subtract(-1, 'days')],
            '今天': [moment(), moment()],
            '昨天': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            '前七天': [moment().subtract('days', 6), moment()],
            '前30天': [moment().subtract('days', 29), moment()],
            '前60天': [moment().subtract('days', 59), moment()]
        },
        startDate: moment(),
        endDate: moment()
      },
      function(start, end,label) {
          //label:通过它来知道用户选择的是什么，传给后台进行相应的展示
          if(label=='全部'){
              $('#daterange-btn1 span').html('全部');
          }
          else if(label=='今天'){
              $('#daterange-btn2 span').html(start.format('YYYY/MM/DD'));
          }else if(label=='昨天'){
              $('#daterange-btn2 span').html(start.format('YYYY/MM/DD'));
          }else if(label=='前七天'){
              $('#daterange-btn2 span').html(start.format('YYYY/MM/DD')+'-'+end.format('YYYY/MM/DD'));
          }else if(label=='前30天'){
              $('#daterange-btn2 span').html(start.format('YYYY/MM/DD')+'-'+end.format('YYYY/MM/DD'));
          }else if(label=='前60天'){
              $('#daterange-btn2 span').html(start.format('YYYY/MM/DD')+'-'+end.format('YYYY/MM/DD'));
          }
          /* var start2_time = start.format('YYYY/MM/DD');
          var end2_time = end.format('YYYY/MM/DD'); */
          
          /*转换成时间戳*/
          var datetimestamp = Date.parse(start).toString();  
          var startTime = datetimestamp.substr(0,datetimestamp.length-3);  
          datetimestamp = Date.parse(end).toString();
          var endTime = datetimestamp.substr(0,datetimestamp.length-3);  
			/*转换成时间戳 end*/
			$.ajax({
               //几个参数需要注意一下
                   type: "POST",//方法类型
                   dataType: "json",//预期服务器返回的数据类型
                   url: "<?php echo U('Statis/chatStatis');?>" ,//url
                   data: {startTime:startTime, endTime:endTime, type:1},
                   success: function (res) {
                      if (res.result) {
                    	  	sheet2_y_data = res.dataArr;
                          sheet2_x_data = res.timeArr;
                          sheet2();
                       }
                      //$.myToast(res.msg);
                   },
                   error : function() {
                       $.myToast("请求异常!");
                   }
         });
      }
    );

    // 时间选择器日期3
    $('#daterange-btn3').daterangepicker({
        ranges: {
            '全部': [moment(), moment().subtract(-1, 'days')],
            '今天': [moment(), moment()],
            '昨天': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            '前七天': [moment().subtract('days', 6), moment()],
            '前30天': [moment().subtract('days', 29), moment()],
            '前60天': [moment().subtract('days', 59), moment()]
        },
        startDate: moment(),
        endDate: moment()
      },
      function(start, end,label) {
          //label:通过它来知道用户选择的是什么，传给后台进行相应的展示
          if(label=='全部'){
              $('#daterange-btn3 span').html('全部');
          }
          else if(label=='今天'){
              $('#daterange-btn3 span').html(start.format('YYYY/MM/DD'));
          }else if(label=='昨天'){
              $('#daterange-btn3 span').html(start.format('YYYY/MM/DD'));
          }else if(label=='前七天'){
              $('#daterange-btn3 span').html(start.format('YYYY/MM/DD')+'-'+end.format('YYYY/MM/DD'));
          }else if(label=='前30天'){
              $('#daterange-btn3 span').html(start.format('YYYY/MM/DD')+'-'+end.format('YYYY/MM/DD'));
          }else if(label=='前60天'){
              $('#daterange-btn3 span').html(start.format('YYYY/MM/DD')+'-'+end.format('YYYY/MM/DD'));
          }
          /* var start3_time = start.format('YYYY/MM/DD');
          var end3_time = end.format('YYYY/MM/DD'); */
          
          /*转换成时间戳*/
          var datetimestamp = Date.parse(start).toString();  
          var startTime = datetimestamp.substr(0,datetimestamp.length-3);  
          datetimestamp = Date.parse(end).toString();
          var endTime = datetimestamp.substr(0,datetimestamp.length-3);  
			/*转换成时间戳 end*/
			$.ajax({
               //几个参数需要注意一下
                   type: "POST",//方法类型
                   dataType: "json",//预期服务器返回的数据类型
                   url: "<?php echo U('Statis/chatStatis');?>" ,//url
                   data: {startTime:startTime, endTime:endTime, type:2},
                   success: function (res) {
                      if (res.result) {
                    	  	sheet3_y_data = res.dataArr;
                          sheet3_x_data = res.timeArr;
                          sheet3();
                       }
                      //$.myToast(res.msg);
                   },
                   error : function() {
                       $.myToast("请求异常!");
                   }
         });
      }
    );
</script>



<!-- 时间选择器日期弹框 -->
<div class="daterangepicker dropdown-menu opensright"><div class="ranges"><ul><li>全部</li><li>今天</li><li>明天</li><li>未来七天</li><li>未来30天</li><li>未来60天</li></ul></div><div class="ranges ranges_1"><ul><li>全部</li><li>今天</li><li>明天</li><li>未来七天</li><li>未来30天</li><li>未来60天</li></ul><div class="range_inputs"><button class="applyBtn btn btn-sm btn-success" disabled="disabled" type="button">确定</button> <button class="cancelBtn btn btn-sm btn-default" type="button">取消</button></div></div><div class="calendar left"><div class="prev_year"><i class="icon iconfont icon-shuangxian-zuojiantou"></i></div><div class="calendar-table"></div></div><div class="calendar right"><div class="prev_month"><i class="icon iconfont icon-shuangxian-youjiantou"></i></div><div class="calendar-table"></div></div><div class="calendar calendar_1"><div class="daterangepicker_input"><input class="input-mini" type="text" name="daterangepicker_start" value=""><div class="calendar-time" style="display: none;"><div></div><i class="fa fa-clock-o glyphicon glyphicon-time"></i></div></div></div><div class="calendar calendar_2"><div class="daterangepicker_input"><input class="input-mini" type="text" name="daterangepicker_end" value=""><div class="calendar-time" style="display: none;"><div></div><i class="fa fa-clock-o glyphicon glyphicon-time"></i></div></div></div><div class="line_date"></div><div class="line_date_2"></div><div class="all">全部日期</div></div>
</body>
</html>