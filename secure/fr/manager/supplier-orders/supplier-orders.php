<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$title = $navBar = "Liste des ordres fournisseurs";
require(DOCTRINE_MODEL_PATH.'SupplierOrder.php');
require(ADMIN.'head.php');
?>
<?php if ($user->get_permissions()->has("m-comm--sm-partners-orders", "r")) : ?>
<link rel="stylesheet" type="text/css" href="<?php echo ADMIN_URL ?>ressources/css/item-list.css" />
<div>
  <div id="supplier-orders-list"></div>
  <div class="zero"></div>
<script type="text/javascript">
  window.sol = new HN.TC.ItemList({
    domHandle: "#supplier-orders-list",
    columns: [
      { name: "tool", label: "Voir", type: "misc",
        onCellWrite: function(rowData, colName){ return "<a href=\"supplier-order-detail.php?id="+rowData.id+"\" class=\"icon application-double\"></a>"; },
        onCellEvent: { click: function(rowData, col, e){ e.stopPropagation(); e.preventDefault(); open($(this).find("a").attr("href"), "_blank"); } }
      },
      { name: "societe", label: "Nom client", type: "string", filters: ["=","like"] },
      { name: "rid", label: "Ref ordre fournisseur", type: "misc", onCellWrite: function(rowData, colName){ return rowData.rid; } },
      { name: "supplier_name", label: "Fournisseur concerné", type: "string", filters: ["=","like"] },
      { name: "mail_time", label: "Date", type: "date", filters: ["=","between"] },
      { name: "sender_name", label: "Donneur d'ordre", type: "string", filters: ["=","like"] },
      { name: "status", label: "Etat", type: "misc",
        filters: (function(){
          var filters = [
            { direct: true, text: "Annulée", ctext: "est", getFilterParam: function(data){ return ["so.cancellation > ?", 0]; } },
            { direct: true, text: "Attente d'information supp.", ctext: "est", getFilterParam: function(data){ return ["so.waiting_infos > ?", 0]; } }
          ];
          for (var k in HN.TC.SupplierOrder.processingStatusList) (function(k){
            filters.push({ direct: true, text: HN.TC.SupplierOrder.processingStatusList[k], ctext: "est", getFilterParam: function(data){ return ["so.processing_status = ?", k]; } });
          }(k));
          return filters;
        }()),
        onCellWrite: function(rowData, colName){
          if (rowData.cancellation > 0)
            return "Annulée";
          if (rowData.waiting_infos > 0)
            return "Attente d'information supp.";
          else
            return HN.TC.SupplierOrder.processingStatusList[rowData.processing_status];
          return "";
        }
      },
      { name: "total_ht", label: "Total HT", type: "float", filters: ["=",">=","<=","between"] },
      { name: "total_ttc", label: "Total TTC", type: "float", filters: ["=",">=","<=","between"] }
    ],
    source: {
      fields: "so.id, "+
              "c.societe as societe, "+
              "so.order_id, "+
              "so.sup_id, "+
              "s.nom1 as supplier_name, "+
              "so.mail_time, "+
              "sos.login as sender_name, "+
              "so.cancellation, "+
              "so.waiting_infos, "+
              "so.processing_status, "+
              "so.total_ht, "+
              "so.total_ttc",
      tables: [
        ["from", "SupplierOrder so"],
        ["innerJoin", "so.order o"],
        ["innerJoin", "so.supplier s"],
        ["leftJoin", "so.sender sos"],
        ["leftJoin", "o.client c"]
      ],
      filters: [
        ["where", ["so.mail_time > ?", 0]]
      ]
    },
    onRowEvent: {
      "click": function(rowData){
        location.href = "supplier-order-detail.php?id="+rowData.id;
      }
    }
  });
  sol.colsByName["mail_time"].sort("DESC");
</script>
</div>
<?php else : ?>
<div class="bg" style="position: relative">
	<h2>Vous n'avez pas les droits adéquats pour réaliser cette opération.</h2>
</div>
<?php endif ?>
<?php require(ADMIN.'tail.php') ?>