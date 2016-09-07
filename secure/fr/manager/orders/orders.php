<?php

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$title = $navBar = "Liste des commandes";
require(DOCTRINE_MODEL_PATH.'Order.php');
require(ADMIN.'head.php');
?>
<?php if ($user->get_permissions()->has("m-comm--sm-orders", "r")) : ?>
<link rel="stylesheet" type="text/css" href="<?php echo ADMIN_URL ?>ressources/css/item-list.css" />
<div>
  <button type="button" class="btn ui-state-default ui-corner-all fr" onclick="document.location.href='order-detail.php?id=new'">Créer une nouvelle commande</button>
  <div class="zero"></div>
  <div id="order-list"></div>
  <div class="zero"></div>
<script type="text/javascript">
  window.ol = new HN.TC.ItemList({
    domHandle: "#order-list",
    columns: [
      { name: "tool", label: "Voir", type: "misc",
        onCellWrite: function(rowData, colName){ return "<a href=\"order-detail.php?id="+rowData.id+"\" class=\"icon application-double\"></a>"; },
        onCellEvent: { click: function(rowData, col, e){ e.stopPropagation(); e.preventDefault(); open($(this).find("a").attr("href"), "_blank"); } }
      },
      { name: "type", label: "Type Commande", type: "const", filters: ["="], constStrings: HN.TC.Order.typeList },
      { name: "sender_name", label: "Traité par", type: "string", filters: ["=","like"] },
      { name: "in_charge_name", label: "Suivi par", type: "string", filters: ["=","like"] },
      { name: "campaign_id", label: "ID Campagne", type: "int", filters: ["=","between"], constStrings: HN.TC.Order.estimateSourceList },
      { name: "id", label: "réf.", type: "int", filters: ["=","between"] },
      { name: "client_id", label: "N° client", type: "int", filters: ["=","between"] },
      { name: "societe", label: "Société", type: "string", filters: ["=","like"] },
      { name: "created", label: "Date", type: "date", filters: ["=","between"] },
      { name: "payment_status", label: "Statut paiement", type: "const", filters: ["="], constStrings: HN.TC.Order.paymentStatusList },
      { name: "processing_status", label: "Statut traitement", type: "const", filters: ["="], constStrings: HN.TC.Order.globalProcessingStatusList },
      { name: "total_ttc", label: "Total TTC", type: "price", filters: ["=",">=","<=","between"] },
      { name: "validation_status", label: "Statut", type: "misc", filters: [
			{ direct: true, text: "validée", ctext: "est", getFilterParam: function(data){ return ["o.validated > ? AND o.cancelled = 0", 0]; } },
			{ direct: true, text: "annulée", ctext: "est", getFilterParam: function(data){ return ["o.cancelled > ?", 0]; } },
			{ direct: true, text: "OK compta", ctext: "est", getFilterParam: function(data){ return ["o.oked > ? AND o.validated = ? AND o.cancelled = 0", [0,0]]; } },
			{ direct: true, text: "A traiter", ctext: "est", getFilterParam: function(data){ return ["o.validated=0 AND o.oked=0 AND o.cancelled=0 AND o.partly_cancelled=0 AND sav_opened=0"]; } }
		],
        onCellWrite: function(rowData, colName){// && rowData.partly_cancelled|0 &&  rowData.sav_opened|0
			if (rowData.cancelled|0){
				return "<span class=\"icon delete\" title=\"commande annulée\"></span>";
			}else if (rowData.validated|0){
				return "<span class=\"icon accept\" title=\"commande validée\"></span>";
			}else if (rowData.oked|0){
				return "<span class=\"icon clock\" title=\"commande OK compta\"></span>";
			}else{
				//if (rowData.validated|0	&& rowData.oked|0 && rowData.cancelled|0){	
				return "<span class=\"icon waiting\" title=\"commande A traiter\"></span>";	
			}
          return "";
        }
      },
      { name: "waiting_info_status", label: "en attente d'infos", type: "const", filters: ["="], constStrings: HN.TC.Order.waitingInfoList },
      { name: "mailtime", label: "Ordre", type: "const", filters: ["="], constStrings: ['non', 'oui'] },
      { name: "forecasted_ship", label: "Expédition", type: "date_only", filters: ["=","between"] }
    ],
    source: {
      fields: "o.id,"+
              "o.type,"+
              "o.campaign_id,"+
              "o.client_id,"+
              "IFNULL(osos.name, '') as sender_name,"+
              "IFNULL(oicu.name, '') as in_charge_name,"+
              "o.societe,"+
              "o.created,"+
              "o.payment_status,"+
              "o.processing_status,"+
              "o.total_ttc,"+
              "o.validated,"+
              "o.cancelled,"+
              "o.oked,"+
              "o.waiting_info_status,"+
              "SUM(IF(oso.mail_time>0,1, 0)) as mailtime,"+
              "o.forecasted_ship",
      tables: [
        ["from", "Order o"],
        ["leftJoin", "o.in_charge_user oicu"],
        ["innerJoin", "o.supplier_orders oso"],
        ["leftJoin", "oso.sender osos"]
      ],
      filters: [
        ["groupBy", ["o.id"]]
      ]
    },
    onRowEvent: {
      click: function(rowData, e){
        location.href = "order-detail.php?id="+rowData.id;
      }
    }
  });
  ol.colsByName["created"].sort("DESC");
</script>
</div>
<?php else : ?>
<div class="bg" style="position: relative">
	<h2>Vous n'avez pas les droits adéquats pour réaliser cette opération.</h2>
</div>
<?php endif ?>
<?php require(ADMIN.'tail.php') ?>