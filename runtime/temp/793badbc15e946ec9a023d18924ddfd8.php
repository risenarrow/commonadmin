<?php /*a:2:{s:70:"K:\CODE\php\site\commonadmin\application\admin\view\publics\login.html";i:1654241555;s:70:"K:\CODE\php\site\commonadmin\application\admin\view\public\layout.html";i:1654587960;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>commonAdmin后台</title>
    <link rel="stylesheet" href="/static/js/layui/css/layui.css">
    <link rel="stylesheet" href="/static/admin/css/style.css">
    <script src="/static/js/jquery/jquery.min.js"></script>
    <script src="/static/js/layui/layui.js"></script>
    <script src="https://static.xiakucao.top/js/utils.js" type="text/javascript"></script>
    
<style>
    html,body,.layui-layout-admin{height: 100%;}
  body{background: url("/static/admin/img/loginbg.jpg");background-repeat:no-repeat;background-size: cover }
</style>


</head>
<body>
<div class="layui-layout layui-layout-admin">
    



    
    
<div class="login-box-html">
<div class="layui-box login-box">
  <div class="login-logo">
    <a href="javascript:;"><b>commonadmin后台</b></a>
  </div>
  <!-- /.login-logo -->
  <div class="login-box-body">
    <p class="login-box-msg">Sign in to start your session</p>

    <form action="" class="layui-form" method="post" onsubmit="return false;">
      <div class="layui-form-item form-group has-feedback">
          <label class="layui-form-label">用户名</label>
          <div class="layui-input-block">
            <input type="text" class="layui-input" lay-verify="required" id="username" placeholder="用户名">
          </div>
      </div>
      <div class="layui-form-item form-group has-feedback">
          <label class="layui-form-label">密码</label>
          <div class="layui-input-block">
            <input type="password" class="layui-input" lay-verify="required" id="password" placeholder="密码">
          </div>
      </div>
        <div class="layui-form-item form-group has-feedback">
            <label class="layui-form-label">验证码</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" lay-verify="required" id="verifycode" placeholder="验证码">
                <img src="<?php echo captcha_src(); ?>" id="verifyimg" class="verifyimg" onclick='this.src = "<?php echo captcha_src(); ?>?time="+(new Date().getTime())' alt="captcha" />
            </div>
        </div>
        <div class="layui-form-item" pane="" style="text-align: initial">

            <div class="layui-input-block">
                <input type="checkbox" name="remember" lay-skin="primary" id="remember" title="记住我" checked="">
                <button  lay-submit lay-filter="test" id="loginbtn" class="layui-btn layui-btn-lg">登录</button>
            </div>

        </div>
    </form>

    <!-- /.social-auth-links -->


  </div>
  <!-- /.login-box-body -->
</div>
<!-- /.login-box -->
</div>




    
</div>


<script>
  var loginurl = '<?php echo admin_url("admin/publics/login"); ?>';
  var adminindex = '<?php echo admin_url("admin/index/index"); ?>';
  var imgurl = '<?php echo captcha_src(); ?>';
  layui.use('form',function(){
      var form = layui.form;
      form.on('submit(test)', function(){
          var param = new Object();
          param.username= $("#username").val();
          param.password = $("#password").val();
          param.remember = $("#remember").val();
          param.verifycode = $("#verifycode").val();
          y_utils.request_url({
              url:loginurl,
              param:param,
              success:function(data){
                  if(data.code == 1){
                      y_utils.successMsg(data.msg,function(){
                          location.href=adminindex;
                      });
                  }else{
                      y_utils.errorMsg(data.msg);$("#verifyimg").attr('src',imgurl+'?time='+(new Date()).getTime());
                  }
              }
          })
      });
  });


</script>


</body>
</html>