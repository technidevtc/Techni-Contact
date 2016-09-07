<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$res_url = (!empty($_SERVER["HTTPS"]) ? SECURE_URL : URL)."ressources/";
?>
#cart-add-overlay-bg-black { position: absolute; top: expression((tmp = document.documentElement.scrollTop)+'px'); width: expression((tmp = document.documentElement.clientWidth)+'px'); height: expression((tmp = document.documentElement.clientHeight)+'px') }
#cart-add-overlay { position: absolute; top: expression((tmp = document.documentElement.scrollTop)+'px'); width: expression((tmp = document.documentElement.clientWidth)+'px'); height: expression((tmp = document.documentElement.clientHeight)+'px') }

.zero { display: inline }

.main-box .bt { overflow: hidden }
.main-box .bb { overflow: hidden }
.main-box .btl { background: none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=crop, src='<?php echo $res_url ?>images/box/main-box-borders-btl.png') }
.main-box .btr { background: none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=crop, src='<?php echo $res_url ?>images/box/main-box-borders-btr.png') }
.main-box .bbl { display: none; bottom: 2px; background: none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=crop, src='<?php echo $res_url ?>images/box/main-box-borders-bbl.png') }
.main-box .bbr { display: none; bottom: 2px; background: none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=crop, src='<?php echo $res_url ?>images/box/main-box-borders-bbr.png') }

.step-box .btl { background: none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=crop, src='<?php echo $res_url ?>images/box/step-box-borders-btl.png') }
.step-box .btr { background: none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=crop, src='<?php echo $res_url ?>images/box/step-box-borders-btr.png') }
.step-box .bbl { bottom: -9px; background: none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=crop, src='<?php echo $res_url ?>images/box/step-box-borders-bbl.png') }
.step-box .bbr { bottom: -9px; background: none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=crop, src='<?php echo $res_url ?>images/box/step-box-borders-bbr.png') }
.step-active-box .btl { background: none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=crop, src='<?php echo $res_url ?>images/box/step-active-box-borders-btl.png') }
.step-active-box .btr { background: none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=crop, src='<?php echo $res_url ?>images/box/step-active-box-borders-btr.png') }
.step-active-box .bbl { bottom: -9px; background: none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=crop, src='<?php echo $res_url ?>images/box/step-active-box-borders-bbl.png') }
.step-active-box .bbr { bottom: -9px; background: none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=crop, src='<?php echo $res_url ?>images/box/step-active-box-borders-bbr.png') }

.white-sr-box .btl { display: none }
.white-sr-box .btr { display: none }
.white-sr-box .bbl { display: none }
.white-sr-box .bbr { display: none }

.white-mr-box .btl { display: none }
.white-mr-box .btr { display: none }
.white-mr-box .bbl { display: none }
.white-mr-box .bbr { display: none }

.account-box .btl { display: none }
.account-box .btr { display: none }
.account-box .bbl { display: none }
.account-box .bbr { display: none }

.grey-lr-box .btl { display: none }
.grey-lr-box .btr { display: none }
.grey-lr-box .bbl { display: none }
.grey-lr-box .bbr { display: none }

.account-order-box .btl { display: none }
.account-order-box .btr { display: none }
.account-order-box .bbl { display: none }
.account-order-box .bbr { display: none }

.cat2-hover-box .btl { display: none }
.cat2-hover-box .btr { display: none }
.cat2-hover-box .bbl { display: none }
.cat2-hover-box .bbr { display: none }

#header-in .left-links { display: inline }
#header-in .right-links { display: inline }
#header-in .right-links .rl-bbl { bottom: -5px; left: -1px }
#header-in .right-links .rl-bbr { bottom: -5px; right: -2px }
#header-in .ad { display: inline }
#header-in .tag-line { display: inline }
#header-in .search div { display: inline }
#header-vborder { height: 2px; border: 1px solid #c0504d; background: #b10000; overflow: hidden }
#header-in #pictos { margin-right: 8px; }

/* common */
.blocks-left { display: inline }
.blocks-right { display: inline }

/* horizontal product block */
.pdt-hb { display: inline }

/* back link */
.pdt-sheet a.back-link { width: 55px }
.cart a.back-link { width: 115px }
.account a.back-link { top: 80px; right: 80px; width: 175px }
a.back-link .bl { background: none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=crop, src='<?php echo $res_url ?>images/back-link-bl.png') }
a.back-link .br { background: none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=crop, src='<?php echo $res_url ?>images/back-link-br.png') }

.cart-table td.quantity .add { overflow: hidden }
.cart-table td.quantity .sub { bottom: -1px; overflow: hidden }

.order-steps .steps .step { float: left; width: 0; height: 30px; white-space: nowrap }
.order-steps .steps .step-active { float: left; width: 0; height: 30px; white-space: nowrap }
