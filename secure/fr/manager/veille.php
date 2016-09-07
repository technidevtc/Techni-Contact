<?php

  require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

  define('MD2I_ADMIN', true);

  require(ADMIN . 'head.php');

?>

<?php include(dirname(__FILE__) . '/rss.php') ?>

<div class="content-box">
  <div class="two-column">
    <div class="column">
      <div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
        <div class="portlet-header ui-widget-header">Techni-contact</div>
        <div class="portlet-content">
          <p>
            <?php echo_rss("http://www.google.com/alerts/feeds/11169016124630468081/16160012459767503368") ?>
          </p>
        </div>
      </div>
    </div>
    <div class="column column-right">
      <div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
        <div class="portlet-header ui-widget-header">Welcome Office</div>
        <div class="portlet-content">
          <p>
            <?php echo_rss("http://www.google.com/alerts/feeds/11169016124630468081/14790096291978880564") ?>
          </p>
        </div>
      </div>
    </div>
  </div>
  <div class="clear"></div>
  <div class="two-column">
    <div class="column">
      <div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
        <div class="portlet-header ui-widget-header">Manutan</div>
        <div class="portlet-content">
          <p>
            <?php echo_rss("http://www.google.com/alerts/feeds/11169016124630468081/5555142603496290855") ?>
          </p>
        </div>
      </div>
    </div>
    <div class="column column-right">
      <div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
        <div class="portlet-header ui-widget-header">IpsoPresto</div>
        <div class="portlet-content">
          <p>
            <?php echo_rss("http://www.google.com/alerts/feeds/11169016124630468081/10396432438087199342") ?>
          </p>
        </div>
      </div>
    </div>
  </div>
  <div class="clear"></div>
  <div class="two-column">
    <div class="column">
      <div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
        <div class="portlet-header ui-widget-header">Hellopro</div>
        <div class="portlet-content">
          <p>
            <?php echo_rss("http://www.google.com/alerts/feeds/11169016124630468081/14226999715634201499") ?>
          </p>
        </div>
      </div>
    </div>
  </div>
  <div class="clear"></div>
</div>
<!-- End .content-box -->

<?php

  require(ADMIN . 'tail.php');

?>
