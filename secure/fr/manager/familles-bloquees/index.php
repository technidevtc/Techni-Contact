<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$db = DBHandle::get_instance();



$title = $navBar = "Familles bloquées récupérables";
require(ADMIN."head.php");


//if ((!$userPerms->has($fntByName["m-comm--sm-pile-appel-personaliser"], "re")) || (!$userPerms->has($fntByName["m-comm--sm-pile-appels-complete"], "re")) ) {
if ((!$userChildScript->get_permissions()->has("m-comm--sm-pile-appel-personaliser","r")) && (!$userChildScript->get_permissions()->has("m-comm--sm-pile-appels-complete","r")) ) {
?>
<div class="bg">
  <div class="fatalerror">Vous n'avez pas les droits ad&eacute;quats pour r&eacute;aliser cette op&eacute;ration.</div>
</div>
<?php
}

else {
  /*$f = BOFunctionality::get("id","name='bi-kpi'");
  if (!empty($f)) {
    $ups = BOUserPermission::get("id_user","id_functionality=".$f[0]["id"]);
    foreach($ups as $up)
      $comIdList[] = $up["id_user"];
    if (!empty($comIdList)) {
      $comList = BOUser::get("id, name, login, email, phone","id in (".implode(",",$comIdList).")");
    }
  }*/
  
?>
<script src="../js/ManagerFunctions.js" type="text/javascript"></script>
<link rel="stylesheet"  type="text/css" href="style.css" />
<link rel="stylesheet"  type="text/css" href="leads.css" />
<link rel="stylesheet" href="box.css">
<link rel="stylesheet"  type="text/css" href="css/style_autoc.css" />
<?php echo '<script type="text/javascript" src="../js/script.js"></script>'; ?>	


<div class="titreStandard">Moteur de recherche + Données du tableau</div>
<br/>
<div class="section">
  <div style="margin-bottom: 20px;">
    <div class="text">
      <div id="search-engine" style="float:left">
	    <!--span class="image-button"></span>-->
		<div class="input_container">Nom de la famille : 
			<input type="text" value="" style="width: 195px;height: 20px;" onkeyup="autocomplet()" id="familles_id" />
			<ul id="familles_list_id"></ul>
		</div>
	  </div>
	  <div class="blocka" style="float: right;">
		<div id="popup_window"><a href="export_familles.php">Export des familles</a></div>		
	  </div>
      <div class="zero"></div>
    </div>
  </div>
	
  <div id="blog_famille"></div>
  <br />
  <div id="result_forms-table"></div>
  
</div>

<script src="script_families.js"></script>
<script>
	$(document).ready(function() {
		show_families_table();
	});
</script>



<style>
	.liste-ul-style{
		list-style: initial !important;
		text-align: left;
		line-height: 16px;
	}	
	.liste-ul-style > li{
		list-style: initial !important;
	}
</style>
<?php } ?>
<?php require(ADMIN."tail.php") ?>


