<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html >
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>挑战答题夺宝-后台管理中心</title>
<link href="/public/adminlte/dist/css/signlog.css" rel="stylesheet" type="text/css" />
</head>

<body class="list-web">
<!--[if lte IE 8]>
          <div class="text-ie8">
          <p class="browserupgrade font-size-18">你正在使用一个<strong>过时</strong>的浏览器。请<a href=" " target="_blank">升级您的浏览器</a>，以提高您的体验。</p >
          </div>
      <![endif]-->

 
<div class="l-s-bg">
    <div class="login_img"><img src="/public/adminlte/dist/img/logo.png"></div>
    <div class="login_title">挑战答题夺宝-后台管理中心</div>
    <div class="log-sign">
        <div class="warm_content">
            <p></p>
        </div>
        <div class="tagContent_overh">
            <div id="tagContent">
                <div class="tagContent" id="tagContent0">
                    <form id="loginForm">
                        <div class="log-list">
                            <ul>
                                <li>
                                    <i class="txtbox01"></i>
                                    <input type="text" name="username" placeholder="请输入用户名">
                                </li>
                                <li>
                                    <i class="txtbox02"></i>
                                    <input type="password" name ="password" placeholder="请输入密码">
                                </li>
                                <li>
                                    <input id="code_input" class="code-input" type="text" name="verify" placeholder="验证码">
                                    <div id="v_container" style="width: 200px;height: 46px; overflow: hidden">
                                        <?php echo sp_verifycode_img('length=4&font_size=20&width=248&height=42&use_noise=1&use_curve=0','style="cursor: pointer;" title="点击获取"');?>
                                    </div>
                                </li>
                                <!-- <li>
                                    <p class="rem_pwd"><span class="check" ></span>记住密码</p>
                                    <p class="tochange_pwd">忘记密码?</p>
                                </li> -->
                                <li>
                                    <a id="log-btn" class="btn" href="javascript:void(0);">登录</a>
                                </li>
                            </ul>
                        </div>
                    </form>
                </div>
                <div class="tagContent selectTag" id="tagContent1">
                    <form id="form2">
                        <div class="sign-list">
                            <ul>
                                <li>
                                    <i class="txtbox"></i>
                                    <input type="text" name="Phome" placeholder="请输入手机号">
                                </li>
                                <li>
                                    <i class="txtbox"></i>
                                    <input type="text" name="MessageCheck" placeholder="短信验证码">
                                    <button class="get-phone-code">获取验证码</button>
                                </li>
                                <li>
                                    <i class="txtbox"></i>
                                    <input type="password" name="Password" placeholder="输入新密码">
                                </li>
                                <li>
                                    <i class="txtbox"></i>
                                    <input type="password" name="PasswordCheck" placeholder="确认密码">
                                </li>
                                <li>
                                    <a id="sign-btn" class="btn" href="#">注册</a>
                                </li>
                            </ul>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="zhy_logo_ico">
       
        <p class="zhy_logo_ico_p">-本技术匡扶会提供</p>
    </div>
</div>


<script >
var posturl ="<?php echo U('public/dologin');?>";
// function rem_pwd(){
//     console.log("111")
//    $(".log-sign li .rem_pwd").hasClass('on_check')? $(this).removeClass("on_check"):$(this).addClass("on_check");
// }

// function tochange_pwd(){
//     document.getElementById("tagContent1").style.display = "block";
//     document.getElementById("tagContent0").style.display = "none";
// }
// $(function(){
//     $(".log-sign li .rem_pwd").onclick=function(e){
//     console.log("111")
//    $(this).hasClass('on_check')? $(this).removeClass("on_check"):$(this).addClass("on_check");
// };
// })
</script>
<script src="/public/js/jquery.js"></script>
<script type="text/javascript" src="/public/js/signlog.js"></script>
</body>
</html>