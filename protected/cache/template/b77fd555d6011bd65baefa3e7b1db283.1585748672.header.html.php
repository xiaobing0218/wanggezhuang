<?php if(!class_exists("View", false)) exit("no direct access allowed");?><div class="header">
  <div class="w1100">
    <div class="module cut">
      <div class="logo fl"><a href="<?php echo url(array('c'=>'main', 'a'=>'index', ));?>"><img alt="<?php echo htmlspecialchars($GLOBALS['cfg']['site_name'], ENT_QUOTES, "UTF-8"); ?>" width="200" src="/public/theme/frontend/default/images/logo.png" border="0" /></a></div>
      <div class="fr header-right">
        <!--导航栏-->
        <div class="nav fl">
          <div class="cross cut">
            <ul>
              <li><a href="/" class="<?php if ($_SERVER['REQUEST_URI']=='/') : ?>active<?php else : ?>oncetas<?php endif; ?>" >首页</a></li>
              <?php $_foreach_v_counter = 0; $_foreach_v_total = count($nav);?><?php foreach( $nav as $k => $v ) : ?><?php $_foreach_v_index = $_foreach_v_counter;$_foreach_v_iteration = $_foreach_v_counter + 1;$_foreach_v_first = ($_foreach_v_counter == 0);$_foreach_v_last = ($_foreach_v_counter == $_foreach_v_total - 1);$_foreach_v_counter++;?>
              <li><a class="<?php if ($_SERVER['REQUEST_URI']==$v['link']) : ?>active<?php else : ?>oncetas<?php endif; ?>" href="<?php echo htmlspecialchars($v['link'], ENT_QUOTES, "UTF-8"); ?>" <?php echo $v['target']; ?> >  <?php echo htmlspecialchars($v['name'], ENT_QUOTES, "UTF-8"); ?></a></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
        <!-- 头部搜索开始 -->
        <div class="top-search fl">
          <form method="get" action="<?php echo url(array('c'=>'search', 'a'=>'index', ));?>">
            <?php if ($GLOBALS['cfg']['rewrite_enable'] == 0) : ?><input type="hidden" name="c" value="search" /><input type="hidden" name="a" value="index" /><?php endif; ?>
            <div class="sf cut"><input class="fl" name="kw" type="text" value="" placeholder="输入商品关键词" /><button class="fr submit" type="submit"><i class="icon"></i></button></div>
          </form>
          <!--<?php if (!empty($header['hot_searches'])) : ?>
          <div class="hw mt8">热门搜索：<?php $_foreach_v_counter = 0; $_foreach_v_total = count($hot_searches);?><?php foreach( $hot_searches as $v ) : ?><?php $_foreach_v_index = $_foreach_v_counter;$_foreach_v_iteration = $_foreach_v_counter + 1;$_foreach_v_first = ($_foreach_v_counter == 0);$_foreach_v_last = ($_foreach_v_counter == $_foreach_v_total - 1);$_foreach_v_counter++;?><a href="<?php echo url(array('c'=>'goods', 'a'=>'search', 'kw'=>$v, ));?>"><?php echo htmlspecialchars($v, ENT_QUOTES, "UTF-8"); ?></a><?php endforeach; ?></div>
          <?php endif; ?>-->
        </div>
        <!-- 头部搜索结束 -->
        <!-- 头部购物车开始 -->
        <div class="top-cart fr">
          <div class="radius4">
            <a href="<?php echo url(array('c'=>'cart', 'a'=>'index', ));?>" id="cartbar">
              <i class="icon"></i><b>0</b><font>我的购物车</font>
            </a>
          </div>
        </div>
        <!-- 头部购物车结束 -->
      </div>
    </div>
  </div>
</div>
