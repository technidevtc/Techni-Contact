<?php

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require substr(dirname(__FILE__), 0, strpos(dirname(__FILE__), "/", stripos(dirname(__FILE__), "technico")+1) + 1) . "config.php";
}

$db = DBHandle::get_instance();

$title = $navBar = "Demandes de contact";
require(ADMIN."head.php");

?>
<link href="HN.css" rel="stylesheet" type="text/css"/>
<style type="text/css">
#LeadsTable table { min-width: 970px; }
#LeadsTable table .column-edit { width: 5%; text-align: center; }
#LeadsTable table .column-edit .check { float: left; }
#LeadsTable table .column-edit .edit { float: left; padding: 0 2px; width: 16px; height: 16px; background: url(b_edit.png) 2px 0px no-repeat; }
#LeadsTable table .column-edit .del { float: left; padding: 0 2px; width: 16px; height: 16px; background: url(b_drop.png) 2px 0px no-repeat; }
#LeadsTable table .column-0 { width: 8%; text-align: center; }
#LeadsTable table .column-1 { width: 20%; text-align: center; }
#LeadsTable table .column-2 { width: 30%; text-align: center; }
#LeadsTable table .column-3 { width: 7%; text-align: center; }
#LeadsTable table .column-4 { width: 8%; text-align: center; }
#LeadsTable table .column-5 { width: 11%; text-align: center; }
#LeadsTable table .column-6 { width: 11%; text-align: center; }
</style>
<script type="text/javascript" src="AJAXclasses.js"></script>
<script type="text/javascript" src="AJAXmodules.js"></script>
<script type="text/javascript" src="<?=ADMIN_URL?>ressources/js/ManagerFunctions.js"></script>
<script type="text/javascript">
var leadsTable, ps, ps2;
$(function(){
	if (!window.HN) HN = window.HN = {};
	if (!HN.TC) HN.TC = {};
	if (!HN.TC.BO) HN.TC.BO = {};
	if (!HN.TC.BO.MS) HN.TC.BO.MS = {}; // Item Selected by Category
	
	$("#btn-create-mini-store").click(function(){ document.location.href = "mini-store.php?id=new"; });
	
	leadsTable = new HN.Mods.JSTable();
	leadsTable.setID("LeadsTable");
	leadsTable.setClass("CommonTable");
	leadsTable.setHeaders(["ID", "Nom", "Description simple", "Type", "Activée ?", "Date création", "Dernière mod.", function(val, rowh) { rowh.className = val; } ]);
	leadsTable.setInitialData([
<?php foreach ($msl as $ms) { ?>
		["<?php echo $ms['id']; ?>", "<?php echo str_replace('"', '\"', $ms['name']); ?>", "<?php echo str_replace('"', '\"', $ms['fastdesc']); ?>", "<?php echo ($ms['type']=="cat"?"familles":"produits"); ?>", "<?php echo ($ms['active']?"oui":"non"); ?>", "<?php echo $ms['create_time']; ?>", "<?php echo $ms['edit_time']; ?>"]<?php echo ($msi++<($msc-1)?",":""); ?>
<?php } ?>
	]);
	leadsTable.setColumnCount(7);
	leadsTable.setMultiPage(true);
	leadsTable.setRowCount(30);
	leadsTable.setCurrentPage(1);
	leadsTable.setRowFct( {
		"onmouseover" : function() { this.style.backgroundColor = "#CCCCCC"; },
		"onmouseout" : function() { this.style.backgroundColor = ""; }
	} );
	leadsTable.setEditTools( {
		"edit" : {"element" : "div", "attributes" : { "onclick" : function() { document.location.href = "mini-store.php?id=" + this.parentNode.parentNode.cc[0].textvalue; } } },
		"del" : {"element" : "div", "attributes" : { "onclick" : function() { document.mssForm.del.value = this.parentNode.parentNode.cc[0].textvalue; document.mssForm.submit(); } } }
	} );
	leadsTable.Refresh();
	//leadsTable.PurgeAll();

	ps = new HN.Mods.PageSwitcher();
	ps.setID("PageSwitcher1");
	ps.setCurrentPage(1);
	ps.setLastPage(leadsTable.getLastPage());
	ps.setTriggerFct( function(page) { leadsTable.setCurrentPage(page); ps2.setCurrentPage(page); leadsTable.Refresh(); ps2.Refresh(); } );
	ps.Refresh();

	ps2 = new HN.Mods.PageSwitcher();
	ps2.setID("PageSwitcher2");
	ps2.setCurrentPage(1);
	ps2.setLastPage(leadsTable.getLastPage());
	ps2.setTriggerFct( function(page) { leadsTable.setCurrentPage(page); ps.setCurrentPage(page); leadsTable.Refresh(); ps.Refresh(); } );
	ps2.Refresh();
});
</script>

<div class="titreStandard">Demandes de contact</div>
<form name="mssForm" method="post" action="mini-stores.php">
<div class="bg">
	<div id="PageSwitcher1"></div>
	<div class="zero"></div>
	<div id="LeadsTable"></div>
	<div id="PageSwitcher2"></div>
	<div class="zero"></div>
</div>
</form>

<?php
require(ADMIN."tail.php");
?>
