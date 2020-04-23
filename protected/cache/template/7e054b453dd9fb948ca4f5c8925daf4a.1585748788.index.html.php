<?php if(!class_exists("View", false)) exit("no direct access allowed");?><!DOCTYPE html>
<html>
<head>
<?php include $_view_obj->compile('backend/lib/meta.html'); ?>
<link rel="stylesheet" type="text/css" href="public/theme/backend/css/verydows.css" />
<link rel="stylesheet" type="text/css" href="public/theme/backend/css/login.css" />
<script type="text/javascript" src="public/script/jquery.js"></script>
<script type="text/javascript" src="public/theme/backend/js/verydows.js"></script>
<script type="text/javascript" src="public/script/md5.js"></script>
<script type="text/javascript">
function login(btn){
  $('#username').vdsFieldChecker({rules: {required:[true, '请输入登录名']}, tipsPos:'abs'});
  $('#password').vdsFieldChecker({rules: {required:[true, '请输入密码']}, tipsPos:'abs'});
  if($('#captcha').size() > 0){
    $('#captcha').vdsFieldChecker({rules: {required:[true, '请输入验证码']}, tipsPos:'abs'});
  }
  $('form').vdsFormChecker({
    beforeSubmit: function(){
      $(btn).addClass('disabled').text('正在登陆').prop('disabled', true);
      vdsSetCipher('password', 'Verydows');
    }
  });
}

function resetCaptcha(){
  var rand = Math.random();
  var src = "<?php echo url(array('c'=>'api/captcha', 'a'=>'image', 'v'=>'"+rand+"', ));?>";
  $('#captcha-img').attr('src', src);
}
</script>
</head>
<body>
<div class="wrap">
  <div class="banner fl">
    <h1><a href="/" target="_blank">商城管理系统</a></h1>
    <h2 class="mt10">商城管理系统</h2>
  </div>
  <?php if (empty($lockout)) : ?>
  <form method="post" action="<?php echo url(array('m'=>$MOD, 'c'=>'main', 'a'=>'login', ));?>">
    <input type="password" value="" class="hide" />
    <input type="hidden" name="<?php echo htmlspecialchars($_SESSION['LOGIN_TOKEN']['KEY'], ENT_QUOTES, "UTF-8"); ?>" value="<?php echo htmlspecialchars($_SESSION['LOGIN_TOKEN']['VAL'], ENT_QUOTES, "UTF-8"); ?>" />
    <div class="login">
      <h2 class="c666">系统管理登陆</h2>
      <dl class="username mt20">
        <dt><i class="icon"></i></dt>
        <dd><input name="username" id="username" type="text" placeholder="请输入登录名" /></dd>
      </dl>
      <dl class="pwd mt20">
        <dt><i class="icon"></i></dt>
        <dd><input name="password" id="password" type="password" placeholder="请输入密码" autocomplete="off" /></dd>
      </dl>
      <?php if ($captcha == 1) : ?>
      <div class="captcha module mt20 cut">
        <div class="module cut">
          <dl class="fl cut"><dd><input name="captcha" id="captcha" type="text" placeholder="请输入验证码" /></dd></dl>
          <div class="fr"><a onclick="resetCaptcha()">看不清换一张</a></div>
        </div>
        <div class="mt10"><img src="<?php echo url(array('c'=>'api/captcha', 'a'=>'image', ));?>" id="captcha-img" onclick="resetCaptcha()" /></div>
      </div>
      <?php endif; ?>
      <div class="login-btn cut">
        <div class="fl"><a class="btn unslt" onclick="login(this)">登 陆</a></div>
        <div class="fr"><label><input name="stay" type="checkbox" value="1" /><font class="unslt">一周内保持登陆</font></label></div>
      </div>
    </div>
  </form>
  <?php else : ?>
  <div class="login">
    <h2 class="c666">系统管理登陆</h2>
    <div class="lockout pad10">由于您多次输入错误的登录信息，系统将拒绝您的所有登录请求，请于<b><?php echo htmlspecialchars($lockout, ENT_QUOTES, "UTF-8"); ?>分钟</b>后刷新本页面重新尝试登录！</div>
  </div>
  <?php endif; ?>
</div>
</body>
</html>
