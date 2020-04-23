<?php if(!class_exists("View", false)) exit("no direct access allowed");?><div class="footer-wrapper">
  <div class="footer">
    <div class="guarantee cut">
      <dl>
        <dt>正</dt>
        <dd class="mt5">正品质量保障</dd>
      </dl>
      <dl>
        <dt>15</dt>
        <dd class="mt5">15天退货保障</dd>
      </dl>
      <dl>
        <dt>安</dt>
        <dd class="mt5">安全支付保障</dd>
      </dl>
      <dl>
        <dt>服</dt>
        <dd class="mt5">服务质量保障</dd>
      </dl>
    </div>
    <div class="cl"></div>
    <div class="links radius4">
      <?php if (!empty($nav)) : ?>
      <?php $_foreach_v_counter = 0; $_foreach_v_total = count($nav);?><?php foreach( $nav as $v ) : ?><?php $_foreach_v_index = $_foreach_v_counter;$_foreach_v_iteration = $_foreach_v_counter + 1;$_foreach_v_first = ($_foreach_v_counter == 0);$_foreach_v_last = ($_foreach_v_counter == $_foreach_v_total - 1);$_foreach_v_counter++;?><a href="<?php echo htmlspecialchars($v['link'], ENT_QUOTES, "UTF-8"); ?>"><?php echo htmlspecialchars($v['name'], ENT_QUOTES, "UTF-8"); ?></a><?php if ($_foreach_v_iteration != $_foreach_v_total) : ?>|<?php endif; ?><?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!--<{/*$footer nofilter*/}>-->
    <div style="font-size: 12px; text-align: center; margin-top: 18px;">
      <span style="color: rgb(215, 215, 215);"><?php echo $GLOBALS['cfg']['footer_info']; ?></span>
    </div>

  </div>

</div>

