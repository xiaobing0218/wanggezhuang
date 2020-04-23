<?php if(!class_exists("View", false)) exit("no direct access allowed");?><div class="" style="border-top: 1px solid #f2f2f2;margin-top: 24px;background-color: #fff">
  <div class="container w1100 helpper cut">
    <div class="helpli cut">
      <div class="service fl">
        <div class="title">关注我们</div>
        <div class="service-content">
          <!-- <div class="service-item feed fl" onclick="location.href='/feedback/apply.html'">
            <span>问题反馈</span>
          </div>
          <div class="service-item wx fl">
            <span>公众号</span>
            <div class="code">
              <img src="/public/image/qrcode.png" alt="">
            </div>
          </div>
          <div class="cl"></div> -->
          <img src="/public/image/qrcode.jpg" alt="傲江山公众号" style="width:155px;height:155px;">
        </div>
      </div>
      <?php if (!empty($helper_list)) : ?>
      <?php $_foreach_v_counter = 0; $_foreach_v_total = count($helper_list);?><?php foreach( $helper_list as $v ) : ?><?php $_foreach_v_index = $_foreach_v_counter;$_foreach_v_iteration = $_foreach_v_counter + 1;$_foreach_v_first = ($_foreach_v_counter == 0);$_foreach_v_last = ($_foreach_v_counter == $_foreach_v_total - 1);$_foreach_v_counter++;?>
      <dl>
        <dt><?php echo htmlspecialchars($v['cate_name'], ENT_QUOTES, "UTF-8"); ?></dt>
        <?php if (!empty($v['children'])) : ?>
        <?php $_foreach_vv_counter = 0; $_foreach_vv_total = count($v['children']);?><?php foreach( $v['children'] as $vv ) : ?><?php $_foreach_vv_index = $_foreach_vv_counter;$_foreach_vv_iteration = $_foreach_vv_counter + 1;$_foreach_vv_first = ($_foreach_vv_counter == 0);$_foreach_vv_last = ($_foreach_vv_counter == $_foreach_vv_total - 1);$_foreach_vv_counter++;?>
        <dd><a title="<?php echo htmlspecialchars($vv['title'], ENT_QUOTES, "UTF-8"); ?>" href="<?php if (!empty($vv['link'])) : ?><?php echo htmlspecialchars($vv['link'], ENT_QUOTES, "UTF-8"); ?><?php else : ?><?php echo url(array('c'=>'help', 'a'=>'view', 'id'=>$vv['id'], ));?><?php endif; ?>"><?php echo htmlspecialchars($vv['title'], ENT_QUOTES, "UTF-8"); ?></a></dd>
        <?php endforeach; ?>
        <?php endif; ?>
      </dl>
      <?php endforeach; ?>
      <?php endif; ?>
      <div class="contact fr">
        <div class="title">服务热线</div>
        <div class="tel">0532-88916157</div>
        <div class="desc">时间：9:00 - 18:00</div>
        <div class="desc" style="display: none">(仅收市话费)</div>
      </div>
    </div>
  </div>
</div>
