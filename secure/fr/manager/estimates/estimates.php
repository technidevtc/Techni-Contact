<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$title = $navBar = "Liste des devis";
require(DOCTRINE_MODEL_PATH.'Estimate.php');
require(ADMIN.'head.php');
?>
<?php if ($user->get_permissions()->has("m-comm--sm-estimates", "r")) : ?>
<link rel="stylesheet" type="text/css" href="<?php echo ADMIN_URL ?>ressources/css/item-list.css" />
<div>
  <button type="button" class="btn ui-state-default ui-corner-all fr" onclick="document.location.href='estimate-detail.php?id=new'">Créer un nouveau devis</button>
  <button type="button" class="btn ui-state-default ui-corner-all fr" onclick="document.location.href='estimate-detail.php?id=new&type=ad_hoc'">Créer un nouveau devis Ad Hoc</button>
  <div class="zero"></div>
  <div id="estimate-list"></div>
  <div class="zero"></div>
<script type="text/javascript">
  window.el = new HN.TC.ItemList({
    domHandle: "#estimate-list",
    columns: [
      { name: "tool", label: "Voir", type: "misc",
        onCellWrite: function(rowData, colName){ return "<a href=\"estimate-detail.php?id="+rowData.id+"\" class=\"icon application-double\"></a>"; },
        onCellEvent: { click: function(rowData, col, e){ e.stopPropagation(); e.preventDefault(); open($(this).find("a").attr("href"), "_blank"); } }
      },
      { name: "id", label: "ID", type: "int", filters: ["=","like"] },
      { name: "source", label: "source", type: "const", filters: ["="], constStrings: HN.TC.Estimate.sourceList },
      { name: "societe", label: "Nom client", type: "string", filters: ["=","like"] },
      { name: "sup_name", label: "Fournisseur principal", type: "string", filters: ["=","like"] },
      { name: "created", label: "Date de création", type: "date", filters: ["=","between"] },
      { name: "created_user_name", label: "Suivi par", type: "string", filters: ["=","like"] },
      { name: "status", label: "Etat", type: "const", filters: ["="], constStrings: HN.TC.Estimate.statusList },
      { name: "total_ht", label: "Total HT", type: "price", filters: ["=",">=","<=","between"] },
      { name: "total_ttc", label: "Total TTC", type: "price", filters: ["=",">=","<=","between"] },
      { name: "waiting_info_status", label: "en attente d'infos", type: "const", filters: ["="], constStrings: HN.TC.Estimate.waitingInfoList },
      { name: "client_seen", label: "Vu par le client", type: "misc", filters: [
          { direct: true, text: "Vu", ctext: "est", getFilterParam: function(data){ return ["e.client_seen > ?", 0]; } },
          { direct: true, text: "Non Vu", ctext: "est", getFilterParam: function(data){ return ["e.client_seen = ?", 0]; } }
        ],
        onCellWrite: function(rowData, colName){
          if (rowData.client_seen|0)
            return "<span class=\"icon accept\" title=\"vu par le client\"></span>";
          return "<span class=\"icon cross\" title=\"non vu par le client\"></span>";
        }
      },
      { name: "no_reminder", label: "Relance D.", type: "const", filters: ["="], constStrings: HN.TC.Estimate.reminderList }
    ],
    source: {
      fields: "e.id,"+
              "e.source,"+
              "e.societe,"+
              "ms.nom1 AS sup_name,"+
              "e.created,"+
              "cu.login AS created_user_name,"+
              "e.status,"+
              "e.total_ht,"+
              "e.total_ttc,"+
              "e.waiting_info_status,"+
              "e.client_seen,"+
              "e.no_reminder",
      tables: [
        ["from", "Estimate e"],
        ["leftJoin", "e.main_supplier ms"],
        ["leftJoin", "e.created_user cu"]
      ]
    },
    onRowEvent: {
      click: function(rowData, e){
        location.href = "estimate-detail.php?id="+rowData.id;
      }
    }
  });
  el.colsByName["created"].sort("DESC");
</script>
</div>
<?php else : ?>
<div class="bg" style="position: relative">
	<h2>Vous n'avez pas les droits adéquats pour réaliser cette opération.</h2>
</div>
<?php endif ?>
<?php require(ADMIN.'tail.php') ?>