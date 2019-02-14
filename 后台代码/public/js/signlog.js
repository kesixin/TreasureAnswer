//弹出的扫码窗口
$(function(){
    // 屏幕宽度
    var wH = window.innerHeight > 0 ? window.innerHeight : document.documentElement.clientHeight;
    document.body.style.height = wH + 'px';
    $("#log-btn").click(function(e){
        var jsonData = $("#loginForm").serializeArray();

        // 提示动画方法
        function anima(){
            $(".warm_content").show();
            $('.warm_content').css({
                "transition": "all 500ms ease",
                "opacity": "1",
                "height": "50px",
            });
            setTimeout(function(){
                $('.warm_content').css({
                    "transition": "all 300ms ease",
                    "opacity": "0",
                    "height": "0",
                });
            },1200)
        }

        if(jsonData[0].value ==''){
            anima();
            $(".warm_content p").text("账户不能为空!");
            return false;
        }
        if(jsonData[1].value ==''){
            anima();
            $(".warm_content p").text("密码不能为空!");
            return false;
        }
        if(jsonData[2].value ==''){
            anima();
            $(".warm_content p").text("验证码不能为空!");
            return false;
        }

        else{
            $.ajax({
              //几个参数需要注意一下
                  type: "POST",//方法类型
                  dataType: "json",//预期服务器返回的数据类型
                  url: posturl ,//url
                  data: $('#loginForm').serialize(),

                  success: function (res) {
                    anima();
                    $(".warm_content p").text(res.info);
                    if(res.status ==0){
                        $('#v_container img').click();
                        return false;
                    }
                    else{
                        $(".warm_content p").css("background-color","#63D68B");
                        window.location.href=res.url
                    }
                  },
                  error : function() {
                      $.myToast("请求异常!");
                  }
            });
        }
    })

    $("body").keydown(function(event) {
	    	 if (event.keyCode == "13") {//keyCode=13是回车键
	    		 event.returnValue=false;
	    		 event.cancel = true;
	    		 $("#log-btn").click();
	    	 }
    	 }
    )

})


// 记住密码
// $(".log-sign li .rem_pwd span").click(function(e){
//    $(this).hasClass('on_check')? $(this).removeClass("on_check"):$(this).addClass("on_check");
// });

// 忘记密码
// $(".log-sign li .tochange_pwd").click(function(e){
//     document.getElementById("tagContent1").style.display = "block";
//     document.getElementById("tagContent0").style.display = "none";
// });
