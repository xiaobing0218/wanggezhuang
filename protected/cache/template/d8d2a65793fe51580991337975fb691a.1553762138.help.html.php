<?php if(!class_exists("View", false)) exit("no direct access allowed");?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="hx_baseurl" content="<?php echo htmlspecialchars($common['baseurl'], ENT_QUOTES, "UTF-8"); ?>">
<meta name="keywords" content="<?php echo htmlspecialchars($help['meta_keywords'], ENT_QUOTES, "UTF-8"); ?>" />
<meta name="description" content="<?php echo htmlspecialchars($help['meta_description'], ENT_QUOTES, "UTF-8"); ?>" />
<title><?php echo htmlspecialchars($help['title'], ENT_QUOTES, "UTF-8"); ?> - <?php echo htmlspecialchars($GLOBALS['cfg']['site_name'], ENT_QUOTES, "UTF-8"); ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo htmlspecialchars($common['theme'], ENT_QUOTES, "UTF-8"); ?>/css/general.css" />
<link rel="stylesheet" type="text/css" href="<?php echo htmlspecialchars($common['theme'], ENT_QUOTES, "UTF-8"); ?>/css/category.css" />
<link rel="stylesheet" type="text/css" href="<?php echo htmlspecialchars($common['theme'], ENT_QUOTES, "UTF-8"); ?>/css/article.css" />
<script type="text/javascript" src="<?php echo htmlspecialchars($common['baseurl'], ENT_QUOTES, "UTF-8"); ?>/public/script/jquery.js"></script>
<script type="text/javascript" src="<?php echo htmlspecialchars($common['theme'], ENT_QUOTES, "UTF-8"); ?>/js/general.js"></script>
</head>
<body style="background-color: #f8f8f8">
<!-- 顶部开始 -->
<?php echo layout_topper(array('common'=>$common, ));?>
<!-- 顶部结束 -->
<!-- 头部开始 -->
<?php echo layout_header(array('common'=>$common, ));?>
<!-- 头部结束 -->
<div class="loc w1100">
  <div><a href="<?php echo url(array('c'=>'main', 'a'=>'index', ));?>">首页</a><b>&gt;</b><font><?php echo htmlspecialchars($help['title'], ENT_QUOTES, "UTF-8"); ?></font></div>
</div>
<!-- 主体开始 -->
<div class="container w1100 mt10">
  <div class="module cut">
    <!-- 左侧开始 -->
    <div class="w210 fl cut">
      <div class="cli mb10">
        <?php if (!empty($cate_help_list)) : ?>
        <?php $_foreach_v_counter = 0; $_foreach_v_total = count($cate_help_list);?><?php foreach( $cate_help_list as $v ) : ?><?php $_foreach_v_index = $_foreach_v_counter;$_foreach_v_iteration = $_foreach_v_counter + 1;$_foreach_v_first = ($_foreach_v_counter == 0);$_foreach_v_last = ($_foreach_v_counter == $_foreach_v_total - 1);$_foreach_v_counter++;?>
        <dl>
          <dt><?php echo htmlspecialchars($v['cate_name'], ENT_QUOTES, "UTF-8"); ?></dt>
          <?php if (!empty($v['children'])) : ?>
          <?php $_foreach_vv_counter = 0; $_foreach_vv_total = count($v['children']);?><?php foreach( $v['children'] as $vv ) : ?><?php $_foreach_vv_index = $_foreach_vv_counter;$_foreach_vv_iteration = $_foreach_vv_counter + 1;$_foreach_vv_first = ($_foreach_vv_counter == 0);$_foreach_vv_last = ($_foreach_vv_counter == $_foreach_vv_total - 1);$_foreach_vv_counter++;?>
          <?php if ($vv['id'] == $_GET['id']) : ?>
          <dd class="cur"><?php echo htmlspecialchars($vv['title'], ENT_QUOTES, "UTF-8"); ?></dd>
          <?php else : ?>
          <dd><a title="<?php echo htmlspecialchars($vv['title'], ENT_QUOTES, "UTF-8"); ?>" href="<?php if (!empty($vv['link'])) : ?><?php echo htmlspecialchars($vv['link'], ENT_QUOTES, "UTF-8"); ?><?php else : ?><?php echo url(array('c'=>'help', 'a'=>'view', 'id'=>$vv['id'], ));?><?php endif; ?>"><?php echo htmlspecialchars($vv['title'], ENT_QUOTES, "UTF-8"); ?></a></dd>
          <?php endif; ?>
          <?php endforeach; ?>
          <?php endif; ?>
        </dl>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
    <!-- 左侧结束 -->
    <!-- 右侧开始 -->
    <div class="w880">
      <div class="article">
        <h1 class="aln-c c666"><?php echo htmlspecialchars($help['title'], ENT_QUOTES, "UTF-8"); ?></h1>
        <div class="content mt30 cut"><?php echo $help['content']; ?></div>
      </div>
    </div>
    <!-- 右侧结束 -->
  </div>
</div>
<!-- 主体结束 -->
<div class="cl"></div>
<!-- 页脚开始 -->
<?php echo layout_helper();?>
<?php echo layout_footer();?>
<!-- 页脚结束 -->
<script type="text/javascript" src="<?php echo htmlspecialchars($common['baseurl'], ENT_QUOTES, "UTF-8"); ?>/public/script/juicer.js"></script>
</body>
</html>
