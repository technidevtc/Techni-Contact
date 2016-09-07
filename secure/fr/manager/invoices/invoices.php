<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$title = $navBar = "Liste des factures et des avoirs";
require(DOCTRINE_MODEL_PATH.'Invoice.php');
require(ADMIN.'head.php');
?>
<?php if ($user->get_permissions()->has("m-comm--sm-invoices", "r")) : ?>
<link rel="stylesheet" type="text/css" href="<?php echo ADMIN_URL ?>ressources/css/item-list.css" />
<div>
  <button type="button" class="btn ui-state-default ui-corner-all fr" onclick="document.location.href='invoice-detail.php?id=new'">Créer une nouvelle facture</button>
  <div class="zero"></div>
  <div id="invoice-list"></div>
  <div class="zero"></div>
<script type="text/javascript">
  window.il = new HN.TC.ItemList({
    domHandle: "#invoice-list",
    columns: [
      { name: "tool", label: "Voir", type: "misc",
        onCellWrite: function(rowData, colName){ return "<a href=\"invoice-detail.php?id="+rowData.id+"\" class=\"icon application-double\"></a>"; },
        onCellEvent: { click: function(rowData, col, e){ e.stopPropagation(); e.preventDefault(); open($(this).find("a").attr("href"), "_blank"); } }
      },
      { name: "type", label: "Type", type: "const", filters: ["="], constStrings: HN.TC.Invoice.typeList },
      { name: "rid", label: "Numéro", type: "int", filters: ["=","between"], onCellWrite: function(rowData, colName){ return rowData[colName] || "non défini"; } },
      { name: "activity", label: "Activité", type: "const", filters: ["="], constStrings: HN.TC.Invoice.activityList },
      { name: "societe", label: "Société", type: "string", filters: ["=","like"] },
      { name: "order_id", label: "ID commande source", type: "int", filters: ["=","between"] },
      { name: "estimate_id", label: "ID devis source", type: "int", filters: ["=","between"] },
      { name: "created", label: "Date de création", type: "date", filters: ["=","between"] },
      { name: "due_date", label: "Echéance", type: "date", filters: ["=","between"], onCellWrite: function(rowData, colName){
          return HN.TC.Invoice.typeList[rowData.type].toLowerCase() == "avoir" ? "avoir" : (rowData[colName] != 0 ? HN.TC.get_formated_date(rowData[colName]) : " - ");
        }
      },
      { name: "status", label: "Etat", type: "const", filters: ["="], constStrings: HN.TC.Invoice.statusList },
      { name: "total_ht", label: "Total HT", type: "price", filters: ["=",">=","<=","between"] },
      { name: "total_ttc", label: "Total TTC", type: "price", filters: ["=",">=","<=","between"] }
    ],
    source: {
      fields: "i.id, i.rid, i.type, i.activity, i.societe, i.order_id, i.estimate_id, i.created, i.due_date, i.status, i.total_ht, i.total_ttc",
      tables: [
        ["from", "Invoice i"]
      ]
    },
    onRowEvent: {
      click: function(rowData, e){
        location.href = "invoice-detail.php?id="+rowData.id;
      }
    }
  });
  il.colsByName["created"].sort("DESC");
</script>
</div>
<?php else : ?>
<div class="bg" style="position: relative">
	<h2>Vous n'avez pas les droits adéquats pour réaliser cette opération.</h2>
</div>
<?php endif ?>
<?php require(ADMIN.'tail.php') ?>