<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$title = $navBar = "Export CEGID";
require(ADMIN.'head.php');

if (!$userPerms->has("m-admin--sm-cegid-export","r")) {
?>
  <div class="bg">
    <div class="fatalerror">Vous n'avez pas les droits adéquats pour réaliser cette opération.</div>
  </div>
<?php
  require(ADMIN."tail.php");
  exit();
}

setlocale(LC_TIME, 'fr_FR');
?>
<link rel="stylesheet" type="text/css" href="<?php echo ADMIN_URL ?>ressources/css/item-list.css" />
<style type="text/css">
  .reporting > .filters { float: left; margin: 0 0 5px; padding: 0 5px 5px; line-height: 30px; text-align: right; border: 1px solid #cccccc }
  .reporting > .filters legend { padding: 0 5px; font-weight: bold }
  .reporting > .filters label { float: left; width: 80px; margin: 0 5px 0 0 }
  .reporting > .filters input { width: 110px }
  .reporting > .filters button { width: 50px }
  .hide-days .ui-datepicker-calendar { display: none }
  .reporting .section { position: relative; padding: 3px 5px; font-weight: bold; color: #fffbf3; text-transform: uppercase; text-shadow: 1px 1px 0 #4C3000; border: 1px solid #333333; background: url("<?php echo ADMIN_URL ?>css/themes/apple_pie/images/ui-bg_highlight-soft_50_dddddd_1x100.png") repeat-x scroll 50% 50% #333333 }
  .period-text { display: none; float: left; padding: 40px 0 0 30px; font-size: 15px }
  .period-text .links { display: inline-block; width: 320px }
  .period-text .links a { margin: 0 0 0 10px }
  .period-text button, .period-text span.msg { display: none; font-size: 11px }
</style>
<div class="reporting">
  <fieldset class="filters">
    <legend>Sélection d'un interval</legend>
    <label>Une journée :</label> <input id="period-day" type="text" value="<?php echo date('d/m/Y') ?>"> <button id="show-period-day" class="btn ui-state-default ui-corner-all">OK</button><br />
    <label>Un mois :</label> <input id="period-month" type="text" value="<?php echo ucfirst(strftime('%B %Y')) ?>"> <button id="show-period-month" class="btn ui-state-default ui-corner-all">OK</button><br />
    <label>Une période :</label> <input id="period-interval-start" type="text" value="<?php echo date('d/m/Y') ?>"> <input id="period-interval-end" type="text" value="<?php echo date('d/m/Y') ?>"> <button id="show-period-interval" class="btn ui-state-default ui-corner-all">OK</button>
  </fieldset>
  <div id="period-text" class="period-text">
    Extract des données CEGID du <b id="period-date-start-text"><?php echo date('d/m/Y') ?></b>
    au <b id="period-date-end-text"><?php echo date('d/m/Y') ?></b><br />
    - <span class="links">Voir données comptes tiers : <a id="extract-client-data-csv" href="">CSV</a> <a id="extract-client-data-tra" href="">TRA</a></span>
      <button id="extract-client-data-confirm" class="btn ui-state-default ui-corner-all">J'ai bien reçu l'extract des comptes tiers</button>
      <span id="extract-client-data-ok" class="msg">Accusé reception OK</span>
      <br />
    - <span class="links">Voir données factures et avoirs : <a id="extract-invoice-data-csv" href="">CSV</a> <a id="extract-invoice-data-tra" href="">TRA</a></span>
      <button id="extract-invoice-data-confirm" class="btn ui-state-default ui-corner-all">J'ai bien reçu l'extract des factures et avoirs</button>
      <span id="extract-invoice-data-ok" class="msg">Accusé reception OK</span>
      <br />
    <input id="extract-historic" type="checkbox" name="extract-historic" /> Extraite toutes les données
  </div>
  <div class="zero"></div>
<script type="text/javascript">
  var t1, t2;
  // date pickers
  $("#period-day").datepicker({
    beforeShow: function(input, inst){ inst.dpDiv.removeClass("hide-days"); }
  });
  var changingDate = false;
  $("#period-month").datepicker({
    changeMonth: true,
    changeYear: true,
    dateFormat: 'MM yy',
    showButtonPanel: true,
    beforeShow: function(input, inst){ inst.dpDiv.addClass("hide-days"); },
    onChangeMonthYear: function(year, month, inst) {
      inst.currentYear = year;
      inst.currentMonth = month-1;
      // avoid an infinite loop with setDate and a bug which reset the date with getDate
      $(this).val($.datepicker._formatDate(inst)); 
    }
  }).datepicker("refresh"); // force the init
  //console.log($("#period-month").datepicker({ dateFormat: 'MM yy' }).data("datepicker").dpDiv.find(".ui-datepicker-calendar"));
  var interval_dates = $("#period-interval-start, #period-interval-end").datepicker({
    defaultDate: "+1w",
    changeMonth: true,
    numberOfMonths: 1,
    beforeShow: function(input, inst){ inst.dpDiv.removeClass("hide-days"); },
    onSelect: function(selectedDate){
      var option = this.id == "period-interval-start" ? "minDate" : "maxDate",
          instance = $(this).data("datepicker"),
          date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
      interval_dates.not(this).datepicker("option", option, date);
    }
  });

  $("#extract-historic").on("change", function(){
    var historic = $(this).prop("checked") | 0;
    $("#extract-client-data-csv").attr("href", $("#extract-client-data-csv").attr("href").replace(/&historic=(\d+)/, function(s,$1){ return "&historic="+historic; }));
    $("#extract-client-data-tra").attr("href", $("#extract-client-data-tra").attr("href").replace(/&historic=(\d+)/, function(s,$1){ return "&historic="+historic; }));
    $("#extract-invoice-data-csv").attr("href", $("#extract-invoice-data-csv").attr("href").replace(/&historic=(\d+)/, function(s,$1){ return "&historic="+historic; }));
    $("#extract-invoice-data-tra").attr("href", $("#extract-invoice-data-tra").attr("href").replace(/&historic=(\d+)/, function(s,$1){ return "&historic="+historic; }));
  });
  
  // extract confirmation
  function confirmExtractReception(type, cb){
    $.ajax({
      type: "POST",
      url: HN.TC.ADMIN_URL+"export/cegid-confirm-extract-reception.php",
      data: { type: type, start: t1, end: t2 },
      dataType: "json",
      error: function(jqXHR, textStatus, errorThrown){
        var error;
        try { error = $.parseJSON(jqXHR.responseText)['error']; }
        catch (e) { error = textStatus+" : "+errorThrown }
        console.log(error);
      },
      success: function(data, textStatus, jqXHR){
        if (cb) cb();
      }
    });
  }
  $("#extract-client-data-csv, #extract-client-data-tra, #extract-invoice-data-csv, #extract-invoice-data-tra").on("click", function(){ $(this).parent().next("button").show().next("span").hide(); });
  $("#extract-client-data-confirm").on("click", function(){
    var $btn = $(this);
    confirmExtractReception("client", function(){
      $btn.hide().next("span").show();
    });
  });
  $("#extract-invoice-data-confirm").on("click", function(){
    var $btn = $(this);
    confirmExtractReception("invoice", function(){
      $btn.hide().next("span").show();
    });
  });
  
  // change text/links
  function onPeriodChange(){
    var historic = $("#extract-historic").prop("checked") | 0;
    $("#period-date-start-text").text(HN.TC.get_formated_date(t1));
    $("#period-date-end-text").text(HN.TC.get_formated_date(t2-1));
    $("#period-text").show();
    $("#extract-client-data-csv").attr("href", "cegid-extract.php?type=client&format=csv&start="+t1+"&end="+t2+"&historic="+historic).parent().nextUntil("br").hide();
    $("#extract-client-data-tra").attr("href", "cegid-extract.php?type=client&format=TRA&start="+t1+"&end="+t2+"&historic="+historic).parent().nextUntil("br").hide();
    $("#extract-invoice-data-csv").attr("href", "cegid-extract.php?type=invoice&format=csv&start="+t1+"&end="+t2+"&historic="+historic).parent().nextUntil("br").hide();
    $("#extract-invoice-data-tra").attr("href", "cegid-extract.php?type=invoice&format=TRA&start="+t1+"&end="+t2+"&historic="+historic).parent().nextUntil("br").hide();
  }
  
  // filtering buttons
  $("#show-period-day").on("click", function(){
    t1 = $("#period-day").datepicker("getDate").getTime()/1000;
    t2 = t1+86400;
    onPeriodChange();
  });
  $("#show-period-month").on("click", function(){
    var target = $("#period-month")[0],
        inst = $.datepicker._getInst(target),
        date = $.datepicker._getDate(inst) || $.datepicker._getDateDatepicker(target);
      date.setDate(1);
      t1 = date.getTime()/1000;
      t2 = HN.TC.mktime(0,0,0,date.getMonth()+2,1,date.getFullYear());
    onPeriodChange();
  });
  $("#show-period-interval").on("click", function(){
    t1 = $("#period-interval-start").datepicker("getDate").getTime()/1000;
    t2 = $("#period-interval-end").datepicker("getDate").getTime()/1000 + 86400;
    onPeriodChange();
  });
  
</script>
</div>
<?php require(ADMIN.'tail.php') ?>