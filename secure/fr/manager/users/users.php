<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = DBHandle::get_instance();

$title = $navBar = "Gestion des utilisateurs";
require(ADMIN."head.php");

if (isset($_POST["del"]) && preg_match("/^[1-9]?[0-9]*$/", $_POST["del"])) {
	BOUser::delete($_POST["del"]);
}

$ul = BOUser::get();
$uc = count($ul);
$ui = 0;

?>
<link href="HN.css" rel="stylesheet" type="text/css"/>
<style type="text/css">
#MSTable table { min-width: 970px }
#MSTable table .column-edit { width: 5%; text-align: center }
#MSTable table .column-edit .check { float: left }
#MSTable table .column-edit .edit { float: left; padding: 0 2px; width: 16px; height: 16px; background: url(b_edit.png) 2px 0px no-repeat }
#MSTable table .column-edit .del { float: left; padding: 0 2px; width: 16px; height: 16px; background: url(b_drop.png) 2px 0px no-repeat }
#MSTable table .column-0 { width: 10%; text-align: center }
#MSTable table .column-1 { width: 17%; text-align: center }
#MSTable table .column-2 { width: 17%; text-align: center }
#MSTable table .column-3 { width: 5%; text-align: center }
#MSTable table .column-4 { width: 28%; text-align: center }
#MSTable table .column-5 { width: 5%; text-align: center }
#MSTable table .column-6 { width: 13%; text-align: center }
</style>
<script type="text/javascript" src="AJAXclasses.js"></script>
<script type="text/javascript" src="AJAXmodules.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_URL ?>ressources/js/ManagerFunctions.js"></script>
<script type="text/javascript">
var mstable;
$(function(){
	if (!window.HN) HN = window.HN = {};
	if (!HN.TC) HN.TC = {};
	if (!HN.TC.BO) HN.TC.BO = {};
	
	$("#btn-create-user").click(function(){ document.location.href = "user.php?id=new"; });
	
	mstable = new HN.Mods.JSTable();
	mstable.setID("MSTable");
	mstable.setClass("CommonTable");
	mstable.setHeaders(["ID", "Nom", "Login", "Rang", "Email", "Actif ?", "Date créa.", function(val, rowh) { rowh.className = val; } ]);
	/*mstable.setHeaders([
		{ "textvalue" : "Réf", "sort" : true, "filter" : false, "type" : "int", "func" : null },
		{ "textvalue" : "Annonceur", "sort" : true, "filter" : false, "type" : "int", "func" : null },
		{ "textvalue" : "Date cré.", "sort" : true, "filter" : false, "type" : "int", "func" : null },
		{ "textvalue" : "Date mod.", "sort" : true, "filter" : false, "type" : "int", "func" : null },
		{ "textvalue" : "Fichier", "sort" : true, "filter" : false, "type" : "int", "func" : null },
		{ "textvalue" : "", "sort" : false, "filter" : false, "type" : "int", "func" : function(val, rowh) { rowh.className = val; } }
	]);
	*/
	mstable.setInitialData([
<?php foreach ($ul as $u) { ?>
		[<?php echo $u["id"] ?>, "<?php echo str_replace('"', '\"', $u["name"]) ?>", "<?php echo str_replace('"', '\"', $u["login"]) ?>", <?php echo $u["rank"] ?>, "<?php echo $u["email"]  ?>", "<?php echo ($u["active"]?"oui":"non") ?>", "<?php echo date("Y-m-d H:i:s",$u["create_time"]) ?>"]<?php echo ($ui++<($uc-1)?",":"") ?>
<?php } ?>
	]);
	mstable.setColumnCount(7);
	mstable.setMultiPage(true);
	mstable.setRowCount(100);
	mstable.setCurrentPage(1);
	mstable.setRowFct( {
		"onmouseover" : function() { this.style.backgroundColor = "#CCCCCC"; },
		"onmouseout" : function() { this.style.backgroundColor = ""; }
	} );
	mstable.setEditTools( {
		"edit" : {"element" : "div", "attributes" : { "onclick" : function() { document.location.href = "user.php?id=" + this.parentNode.parentNode.cc[0].textvalue; } } },
		"del" : {"element" : "div", "attributes" : { "onclick" : function() { document.mssForm.del.value = this.parentNode.parentNode.cc[0].textvalue; document.mssForm.submit(); } } }
	} );
	mstable.Refresh();

});
</script>

<div class="titreStandard">Utilisateurs du manager Techni-Contact</div>
<form name="mssForm" method="post" action="users.php">
<div class="bg">
	<input type="hidden" name="del"/>
	<input id="btn-create-user" type="button" class="bouton" value="Créer un nouvel utilisateur"/><br/>
  <br/>
	<div id="PageSwitcher1"></div>
	<div class="zero"></div>
	<div id="MSTable"></div>
	<div id="PageSwitcher2"></div>
	<div class="zero"></div>
</div>
</form>

<?php
require(ADMIN."tail.php");
?>
