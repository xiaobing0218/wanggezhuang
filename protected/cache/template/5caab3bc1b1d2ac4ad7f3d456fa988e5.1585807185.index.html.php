<?php if(!class_exists("View", false)) exit("no direct access allowed");?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="hx_baseurl" content="<?php echo htmlspecialchars($common['baseurl'], ENT_QUOTES, "UTF-8"); ?>">
<meta name="keywords" content="<?php echo htmlspecialchars($GLOBALS['cfg']['home_keywords'], ENT_QUOTES, "UTF-8"); ?>" />
<meta name="description" content="<?php echo htmlspecialchars($GLOBALS['cfg']['home_description'], ENT_QUOTES, "UTF-8"); ?>" />
<title><?php echo htmlspecialchars($GLOBALS['cfg']['home_title'], ENT_QUOTES, "UTF-8"); ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo htmlspecialchars($common['theme'], ENT_QUOTES, "UTF-8"); ?>/css/general.css" />
<link rel="stylesheet" type="text/css" href="<?php echo htmlspecialchars($common['theme'], ENT_QUOTES, "UTF-8"); ?>/css/index.css" />
<link rel="stylesheet" type="text/css" href="<?php echo htmlspecialchars($common['theme'], ENT_QUOTES, "UTF-8"); ?>/plugins/swiper/css/swiper.min.css" />
<script type="text/javascript" src="<?php echo htmlspecialchars($common['baseurl'], ENT_QUOTES, "UTF-8"); ?>/public/script/jquery.js"></script>
<script type="text/javascript" src="<?php echo htmlspecialchars($common['theme'], ENT_QUOTES, "UTF-8"); ?>/js/general.js"></script>
<script type="text/javascript" src="<?php echo htmlspecialchars($common['theme'], ENT_QUOTES, "UTF-8"); ?>/js/carousel.js"></script>
<script type="text/javascript" src="<?php echo htmlspecialchars($common['theme'], ENT_QUOTES, "UTF-8"); ?>/plugins/swiper/js/swiper.min.js"></script>
</head>
<body>
<!-- 顶部开始 -->
<?php echo layout_topper(array('common'=>$common, ));?>
<!-- 头部开始 -->
<?php echo layout_header(array('common'=>$common, ));?>
<!-- 主体开始 -->
<div class="swiper-container index-banner" id="index-banner">
  <div class="swiper-wrapper">
    <?php if (!empty($ad)) : ?>
    <?php $_foreach_v_counter = 0; $_foreach_v_total = count($ad);?><?php foreach( $ad as $v ) : ?><?php $_foreach_v_index = $_foreach_v_counter;$_foreach_v_iteration = $_foreach_v_counter + 1;$_foreach_v_first = ($_foreach_v_counter == 0);$_foreach_v_last = ($_foreach_v_counter == $_foreach_v_total - 1);$_foreach_v_counter++;?>
    <div class="swiper-slide" style="background-image: url(<?php echo htmlspecialchars($v['src'], ENT_QUOTES, "UTF-8"); ?>)" onclick="jump_fun('<?php echo htmlspecialchars($v['link'], ENT_QUOTES, "UTF-8"); ?>')"></div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <div class="swiper-pagination"></div>
  <div class="index-banner-wrapper" onclick="clickHandle()">
    <div class="w1100 x-auto index-cate clear" onclick="clickHandle()">
      <!-- <div class="left-cate fl">
        <ul id="catebar">
          <?php $_foreach_v_counter = 0; $_foreach_v_total = count($catebar);?><?php foreach( $catebar as $v ) : ?><?php $_foreach_v_index = $_foreach_v_counter;$_foreach_v_iteration = $_foreach_v_counter + 1;$_foreach_v_first = ($_foreach_v_counter == 0);$_foreach_v_last = ($_foreach_v_counter == $_foreach_v_total - 1);$_foreach_v_counter++;?>
          <li class="cate-item">
            <a class="nowrap-text" href="<?php echo url(array('c'=>'category', 'a'=>'index', 'id'=>$v['cate_id'], ));?>">
              <span><?php echo htmlspecialchars($v['cate_name'], ENT_QUOTES, "UTF-8"); ?></span>
            </a>
            <?php if (!empty($v['children'])) : ?>
            <div class="right-cate">
              <?php $_foreach_vv_counter = 0; $_foreach_vv_total = count($v['children']);?><?php foreach( $v['children'] as $vv ) : ?><?php $_foreach_vv_index = $_foreach_vv_counter;$_foreach_vv_iteration = $_foreach_vv_counter + 1;$_foreach_vv_first = ($_foreach_vv_counter == 0);$_foreach_vv_last = ($_foreach_vv_counter == $_foreach_vv_total - 1);$_foreach_vv_counter++;?>
              <div class="right-two">
                <a href="<?php echo url(array('c'=>'category', 'a'=>'index', 'id'=>$vv['cate_id'], ));?>">
                  <span><?php echo htmlspecialchars($vv['cate_name'], ENT_QUOTES, "UTF-8"); ?></span>
                </a> >>>
                <?php if ((!empty($vv['children']))) : ?>
                <span class="right-three">
                <?php $_foreach_vvv_counter = 0; $_foreach_vvv_total = count($vv['children']);?><?php foreach( $vv['children'] as $vvv ) : ?><?php $_foreach_vvv_index = $_foreach_vvv_counter;$_foreach_vvv_iteration = $_foreach_vvv_counter + 1;$_foreach_vvv_first = ($_foreach_vvv_counter == 0);$_foreach_vvv_last = ($_foreach_vvv_counter == $_foreach_vvv_total - 1);$_foreach_vvv_counter++;?>
                <a href="<?php echo url(array('c'=>'category', 'a'=>'index', 'id'=>$vvv['cate_id'], ));?>">
                  <span><?php echo htmlspecialchars($vvv['cate_name'], ENT_QUOTES, "UTF-8"); ?></span>
                </a>
                <?php endforeach; ?>
                </span>
                <?php endif; ?>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </li>
          <?php endforeach; ?>
        </ul>
      </div> -->
    </div>
  </div>
</div>
<div class="container w1100">
  <!-- 新品上市开始 -->
  <div class="module mt30 cut">
    <div class="gli_t">
      <h2 class="fl">新品上市 <span class="tag">NEW</span> <span class="desc">商城新品 精心选配</span></h2>
    </div>
    <div class="gli w1110">
        <div class="u_banner">
          <a href="<?php echo htmlspecialchars($ce['0']['link'], ENT_QUOTES, "UTF-8"); ?>" target="_blank">
          <img class="adsense" src="<?php echo htmlspecialchars($ce['0']['src'], ENT_QUOTES, "UTF-8"); ?>" alt="">
          </a>
        </div>
        <?php if (!empty($newarrival)) : ?>
        <ul class="ul_box">
          <?php $_foreach_v_counter = 0; $_foreach_v_total = count($newarrival);?><?php foreach( $newarrival as $v ) : ?><?php $_foreach_v_index = $_foreach_v_counter;$_foreach_v_iteration = $_foreach_v_counter + 1;$_foreach_v_first = ($_foreach_v_counter == 0);$_foreach_v_last = ($_foreach_v_counter == $_foreach_v_total - 1);$_foreach_v_counter++;?>
          <li>
            <div class="im">
              <a href="<?php echo url(array('c'=>'goods', 'a'=>'index', 'id'=>$v['goods_id'], ));?>">
                <img alt="<?php echo htmlspecialchars($v['goods_name'], ENT_QUOTES, "UTF-8"); ?>" src="<?php echo htmlspecialchars($common['baseurl'], ENT_QUOTES, "UTF-8"); ?>/upload/goods/prime/<?php echo htmlspecialchars($v['merchant_id']['user_id'], ENT_QUOTES, "UTF-8"); ?>/<?php echo htmlspecialchars($v['goods_image'], ENT_QUOTES, "UTF-8"); ?>" />
              </a>
              <?php if ($v['merchant_id']['user_id']==12) : ?>
              <div class="ziy">自营</div>
              <?php endif; ?>
            </div>
            <h3><a href="<?php echo url(array('c'=>'goods', 'a'=>'index', 'id'=>$v['goods_id'], ));?>"><?php echo truncate($v['goods_name'], 40);?></a></h3>
            <h4 class="shop"><a href="<?php echo url(array('c'=>'goods', 'a'=>'shop', 'id'=>$v['merchant_id']['user_id'], ));?>"><?php echo htmlspecialchars($v['merchant_id']['abbreviation'], ENT_QUOTES, "UTF-8"); ?></a></h4>
            <p class="price"><i>¥</i> <?php echo htmlspecialchars($v['now_price'], ENT_QUOTES, "UTF-8"); ?></p>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
  </div>
  <!-- 首页横幅Banner广告位一(1110x90)开始 -->
  <div class="module mt20 cut">
  <?php if (!empty($adv11)) : ?>
  <?php $_foreach_v1_counter = 0; $_foreach_v1_total = count($adv11);?><?php foreach( $adv11 as $v1 ) : ?><?php $_foreach_v1_index = $_foreach_v1_counter;$_foreach_v1_iteration = $_foreach_v1_counter + 1;$_foreach_v1_first = ($_foreach_v1_counter == 0);$_foreach_v1_last = ($_foreach_v1_counter == $_foreach_v1_total - 1);$_foreach_v1_counter++;?>
  <img src="<?php echo htmlspecialchars($v1['src'], ENT_QUOTES, "UTF-8"); ?>" style="width:1100px;">
  <?php endforeach; ?>
  <?php endif; ?>
  </div>
  <!-- 首页横幅Banner广告位一(1110x90)结束 -->
  <!-- 新品上市结束 -->
  <!-- 推荐商品开始 -->
  <div class="cl"></div>
  <div class="module mt20 cut">
    <div class="gli_t">
      <h2>推荐商品</h2>
    </div>
    <div class="gli w1110">
      <div class="u_banner">
        <a href="<?php echo htmlspecialchars($ce['1']['link'], ENT_QUOTES, "UTF-8"); ?>" target="_blank">
        <img class="adsense" src="<?php echo htmlspecialchars($ce['1']['src'], ENT_QUOTES, "UTF-8"); ?>" alt="">
        </a>
      </div>
      <?php if (!empty($recommend)) : ?>
      <ul class="ul_box">
        <?php $_foreach_v_counter = 0; $_foreach_v_total = count($recommend);?><?php foreach( $recommend as $v ) : ?><?php $_foreach_v_index = $_foreach_v_counter;$_foreach_v_iteration = $_foreach_v_counter + 1;$_foreach_v_first = ($_foreach_v_counter == 0);$_foreach_v_last = ($_foreach_v_counter == $_foreach_v_total - 1);$_foreach_v_counter++;?>
        <li>
          <div class="im">
              <a href="<?php echo url(array('c'=>'goods', 'a'=>'index', 'id'=>$v['goods_id'], ));?>">
                  <img alt="<?php echo htmlspecialchars($v['goods_name'], ENT_QUOTES, "UTF-8"); ?>" src="<?php echo htmlspecialchars($common['baseurl'], ENT_QUOTES, "UTF-8"); ?>/upload/goods/prime/<?php echo htmlspecialchars($v['merchant_id']['user_id'], ENT_QUOTES, "UTF-8"); ?>/<?php echo htmlspecialchars($v['goods_image'], ENT_QUOTES, "UTF-8"); ?>" />
              </a>
            <?php if ($v['merchant_id']['user_id']==12) : ?>
            <div class="ziy">自营</div>
            <?php endif; ?>
          </div>
          <h3><a href="<?php echo url(array('c'=>'goods', 'a'=>'index', 'id'=>$v['goods_id'], ));?>"><?php echo truncate($v['goods_name'], 40);?></a></h3>
          <h4 class="shop"><a href="<?php echo url(array('c'=>'goods', 'a'=>'shop', 'id'=>$v['merchant_id']['user_id'], ));?>"><?php echo htmlspecialchars($v['merchant_id']['abbreviation'], ENT_QUOTES, "UTF-8"); ?></a></h4>
          <p class="price"><i>¥</i> <?php echo htmlspecialchars($v['now_price'], ENT_QUOTES, "UTF-8"); ?></p>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>
  </div>
  <!-- 推荐商品结束 -->
  <!-- 首页横幅Banner广告位二(1110x100)开始 -->
  <div class="module mt20 cut">
  <?php if (!empty($adv12)) : ?>
  <?php $_foreach_v2_counter = 0; $_foreach_v2_total = count($adv12);?><?php foreach( $adv12 as $v2 ) : ?><?php $_foreach_v2_index = $_foreach_v2_counter;$_foreach_v2_iteration = $_foreach_v2_counter + 1;$_foreach_v2_first = ($_foreach_v2_counter == 0);$_foreach_v2_last = ($_foreach_v2_counter == $_foreach_v2_total - 1);$_foreach_v2_counter++;?>
  <img src="<?php echo htmlspecialchars($v2['src'], ENT_QUOTES, "UTF-8"); ?>" style="width:1100px;">
  <?php endforeach; ?>
  <?php endif; ?>
  </div>
  <!-- 首页横幅Banner广告位二(1110x100)结束 -->
  <!-- 特价促销开始 -->
  <div class="cl"></div>
  <div class="module mt20 cut">
    <div class="gli_t">
      <h2>特价促销</h2>
    </div>
    <div class="gli w1110">
      <div class="u_banner">
        <a href="<?php echo htmlspecialchars($ce['2']['link'], ENT_QUOTES, "UTF-8"); ?>" target="_blank">
          <img class="adsense" src="<?php echo htmlspecialchars($ce['2']['src'], ENT_QUOTES, "UTF-8"); ?>" alt="">
        </a>
      </div>
      <?php if (!empty($bargain)) : ?>
      <ul class="ul_box">
        <?php $_foreach_v_counter = 0; $_foreach_v_total = count($bargain);?><?php foreach( $bargain as $v ) : ?><?php $_foreach_v_index = $_foreach_v_counter;$_foreach_v_iteration = $_foreach_v_counter + 1;$_foreach_v_first = ($_foreach_v_counter == 0);$_foreach_v_last = ($_foreach_v_counter == $_foreach_v_total - 1);$_foreach_v_counter++;?>
        <li>
          <div class="im">
              <a href="<?php echo url(array('c'=>'goods', 'a'=>'index', 'id'=>$v['goods_id'], ));?>">
                  <img alt="<?php echo htmlspecialchars($v['goods_name'], ENT_QUOTES, "UTF-8"); ?>" src="<?php echo htmlspecialchars($common['baseurl'], ENT_QUOTES, "UTF-8"); ?>/upload/goods/prime/<?php echo htmlspecialchars($v['merchant_id']['user_id'], ENT_QUOTES, "UTF-8"); ?>/<?php echo htmlspecialchars($v['goods_image'], ENT_QUOTES, "UTF-8"); ?>" />
              </a>
            <?php if ($v['merchant_id']['user_id']==12) : ?>
            <div class="ziy">自营</div>
            <?php endif; ?>
          </div>
          <h3><a href="<?php echo url(array('c'=>'goods', 'a'=>'index', 'id'=>$v['goods_id'], ));?>"><?php echo truncate($v['goods_name'], 40);?></a></h3>
            <h4 class="shop"><a href="<?php echo url(array('c'=>'goods', 'a'=>'shop', 'id'=>$v['merchant_id']['user_id'], ));?>"><?php echo htmlspecialchars($v['merchant_id']['abbreviation'], ENT_QUOTES, "UTF-8"); ?></a></h4>
          <p class="price"><i>¥</i> <?php echo htmlspecialchars($v['now_price'], ENT_QUOTES, "UTF-8"); ?></p>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>
  </div>
  <!-- 特价促销结束 -->
  <!-- 首页横幅Banner广告位三(1110x80)开始 -->
  <div class="module mt20 cut">
  <?php if (!empty($adv13)) : ?>
  <?php $_foreach_v3_counter = 0; $_foreach_v3_total = count($adv13);?><?php foreach( $adv13 as $v3 ) : ?><?php $_foreach_v3_index = $_foreach_v3_counter;$_foreach_v3_iteration = $_foreach_v3_counter + 1;$_foreach_v3_first = ($_foreach_v3_counter == 0);$_foreach_v3_last = ($_foreach_v3_counter == $_foreach_v3_total - 1);$_foreach_v3_counter++;?>
  <img src="<?php echo htmlspecialchars($v3['src'], ENT_QUOTES, "UTF-8"); ?>" style="width:1100px;">
  <?php endforeach; ?>
  <?php endif; ?>
  </div>
  <!-- 首页横幅Banner广告位三(1110x80)结束 -->

  <!-- 游玩开始 -->
  <div class="cl"></div>
  <div class="module mt20 cut">
    <div class="gli_t">
      <h2>游玩</h2>
    </div>
    <div class="gli w1110">
      <div class="u_banner">
        <a href="<?php echo htmlspecialchars($ce['3']['link'], ENT_QUOTES, "UTF-8"); ?>" target="_blank">
          <img class="adsense" src="<?php echo htmlspecialchars($ce['3']['src'], ENT_QUOTES, "UTF-8"); ?>" alt="">
        </a>
      </div>
      <?php if (!empty($youwan)) : ?>
      <ul class="ul_box">
        <?php $_foreach_v1_counter = 0; $_foreach_v1_total = count($youwan);?><?php foreach( $youwan as $v1 ) : ?><?php $_foreach_v1_index = $_foreach_v1_counter;$_foreach_v1_iteration = $_foreach_v1_counter + 1;$_foreach_v1_first = ($_foreach_v1_counter == 0);$_foreach_v1_last = ($_foreach_v1_counter == $_foreach_v1_total - 1);$_foreach_v1_counter++;?>
        <li>
          <div class="im">
              <a href="<?php echo url(array('c'=>'goods', 'a'=>'index', 'id'=>$v1['goods_id'], ));?>">
                  <img alt="<?php echo htmlspecialchars($v1['goods_name'], ENT_QUOTES, "UTF-8"); ?>" src="<?php echo htmlspecialchars($common['baseurl'], ENT_QUOTES, "UTF-8"); ?>/upload/goods/prime/<?php echo htmlspecialchars($v1['merchant_id']['user_id'], ENT_QUOTES, "UTF-8"); ?>/<?php echo htmlspecialchars($v1['goods_image'], ENT_QUOTES, "UTF-8"); ?>" />
              </a>
            <?php if ($v1['merchant_id']['user_id']==12) : ?>
            <div class="ziy">自营</div>
            <?php endif; ?>
          </div>
          <h3><a href="<?php echo url(array('c'=>'goods', 'a'=>'index', 'id'=>$v1['goods_id'], ));?>"><?php echo truncate($v1['goods_name'], 40);?></a></h3>
            <h4 class="shop"><a href="<?php echo url(array('c'=>'goods', 'a'=>'shop', 'id'=>$v1['merchant_id']['user_id'], ));?>"><?php echo htmlspecialchars($v1['merchant_id']['abbreviation'], ENT_QUOTES, "UTF-8"); ?></a></h4>
          <p class="price"><i>¥</i> <?php echo htmlspecialchars($v1['now_price'], ENT_QUOTES, "UTF-8"); ?></p>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>
  </div>
  <!-- 游玩结束 -->
  
  <!-- 首页横幅Banner广告位四(1110x80)开始 -->
  <div class="module mt20 cut">
  <?php if (!empty($adv14)) : ?>
  <?php $_foreach_v4_counter = 0; $_foreach_v4_total = count($adv14);?><?php foreach( $adv14 as $v4 ) : ?><?php $_foreach_v4_index = $_foreach_v4_counter;$_foreach_v4_iteration = $_foreach_v4_counter + 1;$_foreach_v4_first = ($_foreach_v4_counter == 0);$_foreach_v4_last = ($_foreach_v4_counter == $_foreach_v4_total - 1);$_foreach_v4_counter++;?>
  <img src="<?php echo htmlspecialchars($v4['src'], ENT_QUOTES, "UTF-8"); ?>" style="width:1100px;">
  <?php endforeach; ?>
  <?php endif; ?>
  </div>
  <!-- s(1110x80)结束 -->

</div>
<?php echo layout_helper();?>
<!-- 页脚开始 -->
<?php echo layout_footer();?>
<!-- 页脚结束 -->
<script type="text/javascript" src="<?php echo htmlspecialchars($common['baseurl'], ENT_QUOTES, "UTF-8"); ?>/public/script/juicer.js"></script>
<script>
  var mySwiper = new Swiper('#index-banner', {
    autoplay: {
      delay: 5000,
      stopOnLastSlide: false,
      disableOnInteraction: false,
    },
    loop: true,
    preventClicks : false,
    pagination: {
      el: '.swiper-pagination',
      clicked: true,
    }
  })
  function jump_fun(link) {
    window.location.href = link;
  }
  function clickHandle() {
    $('.swiper-slide')[mySwiper.realIndex].click();
    event.stopPropagation();
  }
</script>
</body>
</html>
