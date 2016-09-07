<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$title = $navBar = "Reporting Commandes";
require(DOCTRINE_MODEL_PATH.'Estimate.php');
require(DOCTRINE_MODEL_PATH.'Order.php');
require(ADMIN.'head.php');

if (!$userPerms->has("m-reporting--sm-orders","r")) {
?>
  <div class="bg">
    <div class="fatalerror">Vous n'avez pas les droits adéquats pour réaliser cette opération.</div>
  </div>
<?php
  require(ADMIN."tail.php");
  exit();
}
?>
<link rel="stylesheet" type="text/css" href="<?php echo ADMIN_URL ?>ressources/css/item-list.css" />
<style type="text/css">
  .reporting > .filters { float: left; margin: 0 0 5px; padding: 0 5px 5px; line-height: 30px; text-align: right; border: 1px solid #cccccc }
  .reporting > .filters legend { padding: 0 5px; font-weight: bold }
  .reporting > .filters input { width: 150px }
  .reporting > .filters button { width: 150px }
  .reporting .section { position: relative; padding: 3px 5px; font-weight: bold; color: #fffbf3; text-transform: uppercase; text-shadow: 1px 1px 0 #4C3000; border: 1px solid #333333; background: url("<?php echo ADMIN_URL ?>css/themes/apple_pie/images/ui-bg_highlight-soft_50_dddddd_1x100.png") repeat-x scroll 50% 50% #333333 }
  .period-text { float: left; padding: 30px 0 0 30px; font-size: 15px }
</style>
<div class="reporting">
  <button id="get-orders-extract" type="button" class="btn ui-state-default ui-corner-all fr">Télécharger l'extract de la période étudiée</button>
  <fieldset class="filters">
    <legend>Sélection d'un interval</legend>
    <input id="period-day" type="text" value="<?php echo date('d/m/Y') ?>"> <button id="show-period-day" class="btn ui-state-default ui-corner-all">Afficher la journée</button><br/>
    <input id="period-interval-start" type="text" value="<?php echo date('d/m/Y') ?>"> <input id="period-interval-end" type="text" value="<?php echo date('d/m/Y') ?>"> <button id="show-period-interval" class="btn ui-state-default ui-corner-all">Afficher la période</button>
  </fieldset>
  <div class="period-text">
    <i>Période affichée :</i><br/>
    du <b id="period-date-start-text"><?php echo date('d/m/Y') ?></b> à 00:00:00<br/>
    au <b id="period-date-end-text"><?php echo date('d/m/Y') ?></b> à 23:59:59</div>
  <div class="zero"></div>
  <div class="section">Reporting par source</div>
  <div id="order-source-reporting-list"></div>
  <div class="zero"></div>
  <div class="section">Reporting par commercial</div>
  <div id="order-commercial-reporting-list"></div>
  <div class="zero"></div>
<script type="text/javascript">
  // filtering vars
  var t1 = HN.TC.mktime(0,0,0),
      t2 = t1+86400;
  // date pickers
  $("#period-day").datepicker();
  var interval_dates = $("#period-interval-start, #period-interval-end").datepicker({
    defaultDate: "+1w",
    changeMonth: true,
    numberOfMonths: 1,
    onSelect: function(selectedDate){
      var option = this.id == "period-interval-start" ? "minDate" : "maxDate",
          instance = $(this).data("datepicker"),
          date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
      interval_dates.not(this).datepicker("option", option, date);
    }
  });
  
  // filtering buttons
  $("#show-period-day").on("click", function(){
    t1 = $("#period-day").datepicker("getDate").getTime()/1000;
    t2 = t1+86400;
    $("#period-date-start-text").text($("#period-day").val());
    $("#period-date-end-text").text($("#period-day").val());
    osrl.setSourceFilters([
      ["where", ["o.validated >= ? AND o.validated < ? AND o.cancelled = ?", [t1,t2,0]]],
      ["groupBy", ["o.type"]]
    ], true);
    ocrl.setSource({
      fields: getOrderComReportingFields(t1,t2),
      tables: [
        ["from", "BoUsers bou"],
        ["leftJoin", "bou.orders_in_charge ocl WITH (ocl.created >= "+t1+" AND ocl.created < "+t2+" AND ocl.estimate_id <> 0 AND ocl.validated <> 0 AND ocl.cancelled = 0)"]
      ]
    });
    ocrl.updateView();
  });
  $("#show-period-interval").on("click", function(){
    t1 = $("#period-interval-start").datepicker("getDate").getTime()/1000;
    t2 = $("#period-interval-end").datepicker("getDate").getTime()/1000 + 86400;
    $("#period-date-start-text").text($("#period-interval-start").val());
    $("#period-date-end-text").text($("#period-interval-end").val());
    osrl.setSourceFilters([
      ["where", ["o.validated >= ? AND o.validated < ? AND o.cancelled = ?", [t1,t2,0]]],
      ["groupBy", ["o.type"]]
    ], true);
    ocrl.setSource({
      fields: getOrderComReportingFields(t1,t2),
      tables: [
        ["from", "BoUsers bou"],
        ["leftJoin", "bou.orders_in_charge ocl WITH (ocl.created >= "+t1+" AND ocl.created < "+t2+" AND ocl.estimate_id <> 0 AND ocl.validated <> 0 AND ocl.cancelled = 0)"]
      ]
    });
    ocrl.updateView();
  });
  
  // extract button
  $("#get-orders-extract").on("click", function(){
    document.location.href = "extract-orders.php?start="+t1+"&end="+t2;
  });
  
  // table objects
  window.osrl = new HN.TC.ItemList({ // Order Source Reporting List
    domHandle: "#order-source-reporting-list",
    columns: [
      { name: "type", label: "Type Commande", type: "const", filters: ["="], constStrings: HN.TC.Order.typeList },
      { name: "count", label: "Nombre de commande", type: "int" },
      { name: "sum_ht", label: "Somme totale € HT", type: "price" },
      { name: "sum_ttc", label: "Somme totale € TTC", type: "price" },
      { name: "avg_ht", label: "Moy. par panier € HT", type: "price" }
    ],
    source: {
      fields: "o.type,"+
              "COUNT(o.id) AS count,"+
              "SUM(o.total_ht) AS sum_ht,"+
              "SUM(o.total_ttc) AS sum_ttc,"+
              "AVG(o.total_ht) AS avg_ht",
      tables: [
        ["from", "Order o"]
      ],
      filters: [
        ["where", ["o.validated >= ? AND o.validated < ? AND o.cancelled = ?", [t1,t2,0]]],
        ["groupBy", ["o.type"]]
      ]
    }
  });
  osrl.colsByName["sum_ht"].sort("DESC");
  
  // needed because the source fields changes for each new interval
  function getOrderComReportingFields(t1,t2){
    return "ocl.id,"+ // useless, but let us avoid this bug : http://www.doctrine-project.org/jira/browse/DC-927
           "bou.id,"+
           "bou.name AS created_user_name,"+
           "("+
             "SELECT COUNT(l.id) "+
             "FROM Contacts l "+
             "INNER JOIN l.advertiser a "+
             "WHERE l.id_user_commercial = bou.id AND l.timestamp >= "+t1+" AND l.timestamp < "+t2+" AND a.category = "+HN.TC.__ADV_CAT_SUPPLIER__+
           ") AS lead_count,"+
           "("+
             "SELECT COUNT(ecl.id) "+
             "FROM Estimate ecl "+
             "WHERE ecl.created_user_id = bou.id AND ecl.created >= "+t1+" AND ecl.created < "+t2+" AND ecl.status >= "+HN.TC.Estimate.STATUS_SENT+
           ") AS estimate_count,"+
           "count(ocl.id) AS order_count,"+
           "SUM(ocl.total_ht) AS sum_ht,"+
           "SUM(ocl.total_ttc) AS sum_ttc,"+
           "AVG(ocl.total_ht) AS avg_ht"
  }
  
  window.ocrl = new HN.TC.ItemList({ // Order Commercial Reporting List
    domHandle: "#order-commercial-reporting-list",
    columns: [
      { name: "created_user_name", label: "Nom Commercial", type: "string", onCellWrite: function(rowData, colName){ return rowData[colName] || "<i>Commande Internet</i>" } },
      { name: "lead_count", label: "Nb demandes reçues", type: "int" },
      { name: "estimate_count", label: "Nb devis envoyés", type: "int" },
      { name: "order_count", label: "Nb commandes", type: "int" },
      { name: "sum_ht", label: "Somme totale € HT", type: "price" },
      { name: "sum_ttc", label: "Somme totale € TTC", type: "price" },
      { name: "avg_ht", label: "Moy. par panier € HT", type: "price" }
    ],
    source: {
      fields: getOrderComReportingFields(t1,t2),
      tables: [
        ["from", "BoUsers bou"],
        ["leftJoin", "bou.orders_in_charge ocl WITH (ocl.created >= "+t1+" AND ocl.created < "+t2+" AND ocl.estimate_id <> 0 AND ocl.validated <> 0 AND ocl.cancelled = 0)"],
      ],
      filters: [
        ["groupBy", ["bou.id"]],
        ["having", ["lead_count > ? OR estimate_count > ? OR order_count > ?", [0,0,0]]]
      ]
    }
  });
  ocrl.colsByName["sum_ht"].sort("DESC");
  
  /*window.ocrl = new HN.TC.ItemList({ // Order Commercial Reporting List
    domHandle: "#order-commercial-reporting-list",
    columns: [
      { name: "created_user_name", label: "Nom Commercial", type: "string", onCellWrite: function(rowData, colName){ return rowData[colName] || "<i>Commande Internet</i>" } },
      { name: "count", label: "Nombre de commande", type: "int" },
      { name: "sum_ht", label: "Somme totale € HT", type: "price" },
      { name: "sum_ttc", label: "Somme totale € TTC", type: "price" },
      { name: "avg_ht", label: "Moy. par panier € HT", type: "price" }
    ],
    source: {
      fields: "o.id,"+ // useless, but let us avoid this bug : http://www.doctrine-project.org/jira/browse/DC-927
              "cu.name AS created_user_name,"+
              "COUNT(o.id) AS count,"+
              "SUM(o.total_ht) AS sum_ht,"+
              "SUM(o.total_ttc) AS sum_ttc,"+
              "AVG(o.total_ht) AS avg_ht",
      tables: [
        ["from", "Order o"],
        ["leftJoin", "o.created_user cu"]
      ],
      filters: [
        ["where", ["o.processing_status >= ? AND o.processing_status < ?", [HN.TC.Order.GLOBAL_PROCESSING_STATUS_PROCESSING, HN.TC.Order.GLOBAL_PROCESSING_STATUS_PARTLY_CANCELED]]],
        ["andWhere", ["o.validated >= ? AND o.validated < ?", [t1, t2]]],
        ["groupBy", ["cu.id"]]
      ]
    }
  });
  ocrl.colsByName["sum_ht"].sort("DESC");*/
</script>
</div>
<?php require(ADMIN.'tail.php') ?>