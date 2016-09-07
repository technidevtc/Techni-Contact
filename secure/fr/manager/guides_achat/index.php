<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$db = DBHandle::get_instance();

$title = $navBar = "Gestion des guides d'achat";
require(ADMIN."head.php");


//if ((!$userPerms->has($fntByName["m-comm--sm-pile-appel-personaliser"], "re")) || (!$userPerms->has($fntByName["m-comm--sm-pile-appels-complete"], "re")) ) {
if ((!$userChildScript->get_permissions()->has("m-mark--sm-gestion-achat","r")) ) {
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
<?php echo '<script type="text/javascript" src="../js/script.js"></script>'; ?>	


<div class="titreStandard">Compteurs + Filtres + Données du tableau</div>
<br/>
<div class="section">
  <div style="margin-bottom: 20px;">
    <div class="text">
      
  
  <div  style="float: left;">
	<br />
	
	<div class="blocka" style="float: right;">
		<div id="popup_window"><a href="creer_guides_achat.php">Créer un guide</a></div>		
	</div>

	<div class="blocka" style="float: right;">
		<div id="popup_window"><a href="gestion-familles.php">Gestion des familles 1</a></div>
	</div>
	
  </div>
      <div class="zero"></div>
    </div>
  </div>
<?php 
   if($_GET['add_guide'] == 'success'){
			echo '<div class="alert-box success">Guide créé avec succès</div>';
   }
   
    if($_GET['delete_guide'] == 'success'){
			echo '<div class="alert-box error">Suppression effectuée avec succès.</div>';
   }
   
?>	
  <div id="result_forms"></div>
  
</div>

<script src="script_guide.js"></script>
<script>
	$(document).ready(function() {
		show_vpc_table();
	});
</script>

<script type='text/javascript'>//<![CDATA[ 
$(window).load(function(){
jQuery(document).ready(function ($) {
	
    function clearPopup() {
		$('.popup.visible').addClass('transitioning').removeClass('visible');
        $('html').removeClass('overlay');
        setTimeout(function () {
            $('.popup').removeClass('transitioning');
        }, 200);
    }
	
	$('[data-popup-target]').click(function () {
        $('html').addClass('overlay');
        var activePopup = $(this).attr('data-popup-target');
        $(activePopup).addClass('visible');
    });
    $(document).keyup(function (e) {
        if (e.keyCode == 27 && $('html').hasClass('overlay')) {
            clearPopup();
        }
    });
    
    $('.popup-overlay').click(function () {
        clearPopup();
    });
	
	$('.popup-exit').click(function () {
        clearPopup();
    });
});
});//]]> 
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


