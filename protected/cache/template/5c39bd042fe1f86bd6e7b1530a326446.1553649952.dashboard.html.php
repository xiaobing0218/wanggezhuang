<?php if(!class_exists("View", false)) exit("no direct access allowed");?><!DOCTYPE html>
<html>
<head>
<?php include $_view_obj->compile('backend/lib/meta.html'); ?>
<link rel="stylesheet" type="text/css" href="public/theme/backend/css/verydows.css" />
<link rel="stylesheet" type="text/css" href="public/theme/backend/css/main.css" />
<link rel="stylesheet" type="text/css" href="public/theme/backend/css/dashboard.css" />
<script type="text/javascript" src="public/script/jquery.js"></script>
<script type="text/javascript" src="public/theme/backend/js/verydows.js"></script>
<script type="text/javascript">
$(function(){
  $.getJSON("<?php echo url(array('m'=>$MOD, 'c'=>'main', 'a'=>'dashboard', 'step'=>'totals', ));?>", function(data){
    $('#totals-tbl').append(juicer($('#totals-tpl').html(), data));
  });
  $.getJSON("<?php echo url(array('m'=>$MOD, 'c'=>'main', 'a'=>'dashboard', 'step'=>'today', ));?>", function(data){
    $('#today-tbl').append(juicer($('#today-tpl').html(), data));
  });
  $.getJSON("<?php echo url(array('m'=>$MOD, 'c'=>'main', 'a'=>'dashboard', 'step'=>'pending', ));?>", function(data){
    $('#pending-tbl').append(juicer($('#pending-tpl').html(), data));
  });
  $.getJSON("<?php echo url(array('m'=>$MOD, 'c'=>'main', 'a'=>'dashboard', 'step'=>'sysinfo', ));?>", function(data){
    $('#sysinfo-tbl').append(juicer($('#sysinfo-tpl').html(), data)).find('.loading').remove();
  });
});
</script>
</head>
<body>
<div class="content">
  <div class="loc"><h2><i class="dashboard icon"></i>管理中心</h2></div>
  <div class="box">
    <div class="notice" id="notice"></div>
    <!-- 数据统计开始 -->
    <div class="module cut">
      <div class="divid">
        <div class="bw-row mr5 pad5">
          <h3 class="th ta-c">数据统计</h3>
          <div class="module mt5 cut" id="totals-tbl"></div>
        </div>
      </div>
      <div class="divid">
        <div class="bw-row mr5 pad5">
          <h3 class="th ta-c">今日新增</h3>
          <div class="module mt5 cut" id="today-tbl"></div>
        </div>
      </div>
      <div class="divid">
        <div class="bw-row mr5 pad5">
          <h3 class="th ta-c">待处理事项</h3>
          <div class="module mt5" id="pending-tbl"></div>
        </div>
      </div>
      <div class="divid">
        <div class="bw-row pad5">
          <h3 class="th ta-c">在线管理员</h3>
          <div class="actives module">
            <table class="stbl">
              <tr class="thd">
                <th width="50%">用户名 / 姓名</th>
                <th class="ta-r">登录时间</th>
              </tr>
              <?php $_foreach_v_counter = 0; $_foreach_v_total = count($admin_list);?><?php foreach( $admin_list as $v ) : ?><?php $_foreach_v_index = $_foreach_v_counter;$_foreach_v_iteration = $_foreach_v_counter + 1;$_foreach_v_first = ($_foreach_v_counter == 0);$_foreach_v_last = ($_foreach_v_counter == $_foreach_v_total - 1);$_foreach_v_counter++;?>
              <tr>
                <th><?php echo htmlspecialchars($v['username'], ENT_QUOTES, "UTF-8"); ?><?php if (!empty($v['name'])) : ?><font>[<?php echo htmlspecialchars($v['name'], ENT_QUOTES, "UTF-8"); ?>]</font><?php else : ?><font class="caaa">[未设置]</font><?php endif; ?></th>
                <td><?php echo date('Y-m-d H:i:s', $v['dateline']);?></td>
              </tr>
              <?php endforeach; ?>
            </table>
          </div>
        </div>
      </div>
    </div>
    <!-- 数据统计结束 -->
  </div>
</div>
<script type="text/template" id="totals-tpl">
  <table class="stbl">
    <tr><th width="100">订单总数</th><td><b>${order}</b>个</td></tr>
    <tr><th>总营收额</th><td><b>{@if revenue != null}${revenue}{@else}0.00{@/if}</b>元</td></tr>
    <tr><th>注册用户</th><td><b>${user}</b>个</td></tr>
    <tr><th>商品总数</th><td><b>${goods}</b>个</td></tr>
    <tr><th>广告总数</th><td><b>${adv}</b>条</td></tr>
    <tr><th>资讯总数</th><td><b>${article}</b>条</td></tr>
  </table>
</script>
<script type="text/template" id="today-tpl">
  <table class="stbl">
    <tr><th width="100">订单数量</th><td><b>${order}</b>个</td></tr>
    <tr><th>今日营收</th><td><b>{@if revenue != null}${revenue}{@else}0.00{@/if}</b>元</td></tr>
    <tr><th>新注册用户</th><td><b>${user}</b>个</td></tr>
    <tr><th>售后申请</th><td><b>${aftersales}</b>个</td></tr>
    <tr><th>咨询反馈</th><td><b>${feedback}</b>条</td></tr>
    <tr><th>今日浏览量</th><td>{@if pv == '-1'}<font class="mr5 c999">访问统计关闭</font>{@else if pv == null}<b>0</b>次{@else}<b>${pv}</b>次{@/if}</td></tr>
  </table>
</script>
<script type="text/template" id="pending-tpl">
  <table class="stbl">
    <tr><th width="100">待发货订单</th><td><b>${order}</b>个</td></tr>
    <tr><th>待处理售后</th><td><b>${aftersales}</b>个</td></tr>
    <tr><th>待审核评价</th><td><b>${review}</b>条</td></tr>
    <tr><th>待回复反馈</th><td><b>${feedback}</b>个</td></tr>
    <tr><th>到期广告</th><td><b>${adv}</b>条</td></tr>
    <tr><th>待确认订阅</th><td><b>${subscription}</b>个</td></tr>
  </table>
</script>
<script type="text/javascript" src="public/script/juicer.js"></script>
</body>
</html>