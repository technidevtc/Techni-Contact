<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$title = $navBar = "Gestion des mini-boutiques";
require(DOCTRINE_MODEL_PATH.'MiniStores.php');
require(ADMIN.'head.php');
?>
<?php if ($user->get_permissions()->has("m-mark--sm-mini-stores", "r")) : ?>
<link rel="stylesheet" type="text/css" href="<?php echo ADMIN_URL ?>ressources/css/item-list.css" />
<div>
  <button type="button" class="btn ui-state-default ui-corner-all fr" onclick="document.location.href='mini-store.php?id=new'">Créer une nouvelle mini-boutique</button>
  <div class="zero"></div>
  <div id="mini-stores-list"></div>
  <div class="zero"></div>
<script type="text/javascript">
  window.msl = new HN.TC.ItemList({
    domHandle: "#mini-stores-list",
    columns: [
      { name: "tool", label: "Voir", type: "misc",
        onCellWrite: function(rowData, colName){ return "<a href=\"mini-store.php?id="+rowData.id+"\" class=\"icon application-double\"></a>"; },
        onCellEvent: { click: function(rowData, col, e){ e.stopPropagation(); e.preventDefault(); open($(this).find("a").attr("href"), "_blank"); } }
      },
      { name: "id", label: "ID", type: "float", filters: ["=",">=","<=","between"] },
      { name: "name", label: "Nom", type: "string", filters: ["=","like"] },
      { name: "fastdesc", label: "Description", type: "string", filters: ["=","like"] },
      { name: "type", label: "Type", type: "date", type: "string", filters: ["=","like"] ,onCellWrite: function(rowData, colName){return HN.TC.MiniStores.labelList[rowData.type];}},
      { name: "active", label: "Activée", type: "float", filters: ["=","between"] },
      { name: "standalone", label: "Ad hoc", type: "float", filters: ["=","between"] },
      { name: "espace_thematique", label: "Esp. Th.", type: "float", filters: ["=","between"] },
      { name: "create_time", label: "Date création", type: "date", filters: ["=","between"] },
      { name: "edit_time", label: "Dernière mod.", type: "date", filters: ["=","between"] }
    ],
    source: {
      fields: "ms.id, "+
        "ms.name ,"+
        "ms.fastdesc ,"+
        "ms.type ,"+
        "ms.active ,"+
        "ms.standalone ,"+
        "ms.espace_thematique ,"+
        "ms.create_time ,"+
        "ms.edit_time",
      tables: [
        ["from", "MiniStores ms"]
      ]
    },
    onRowEvent: {
      "click": function(rowData){
        location.href = "mini-store.php?id="+rowData.id;
      }
    }
  });
 msl.colsByName['create_time'].sort('DESC');
</script>
</div>
<?php else : ?>
<div class="bg" style="position: relative">
	<h2>Vous n'avez pas les droits adéquats pour réaliser cette opération.</h2>
</div>
<?php endif ?>
<?php require(ADMIN.'tail.php') ?>
