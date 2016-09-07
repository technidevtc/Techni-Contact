<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$db = DBHandle::get_instance();



$title = $navBar = "Pile d'appels commerciaux";
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
<?php echo '<script type="text/javascript" src="../js/script.js"></script>'; ?>	


<div class="titreStandard">Compteurs + Filtres + Données du tableau</div>
<br/>
<div class="section">
  <div id="filtering-options" class="block filtering" style="margin-bottom: 20px;">
    <div class="text">
      <div style="float: left;">
		<?php
		if ($userPerms->has($fntByName["m-comm--sm-pile-appel-personaliser"], "r")){
			echo '<input type="hidden" id="access_droit" value="personaliser" />';
			echo '<input type="hidden" id="id_users" value="'.$_SESSION["id"].'" />';
		}else{
			echo '<input type="hidden" id="access_droit" value="complete" />';
			echo '<input type="hidden" id="id_users" value="'.$_SESSION["id"].'" />';
		}
		?>	
		
		
	  <div id="performances_ajax"></div>
	
  </div>
  
  <div  style="float: right;">
	<div class="filter_search">
	<?php
	if ($userPerms->has($fntByName["m-comm--sm-pile-appel-personaliser"], "r")) { ?>
	<fieldset class="selector">
	<legend>Commercial  :</legend>
        <select id="comm-selector" name="fonction" class="edit" onchange="updateListe_filtre()">
            <?php
				$sql_user = "SELECT id,name FROM bo_users WHERE appels_commerciale='1' AND id='".$_SESSION["id"]."' ";
				$req_user =  mysql_query($sql_user);
				while($data_user = mysql_fetch_object($req_user)){
					echo '<option value="'.$data_user->id.'">'.$data_user->name.' </option>';
				}
			?>
        </select>
	</fieldset>
	<?php }else { ?>
	<fieldset class="selector">
	<legend>Commercial  :</legend>
        <select id="comm-selector" name="fonction" class="edit" onchange="updateListe_filtre()">
              <option value="all"> - </option>
			<?php
				$sql_user = "SELECT id,name FROM bo_users WHERE appels_commerciale='1'  ";
				$req_user =  mysql_query($sql_user);
				while($data_user = mysql_fetch_object($req_user)){
					if($data_user->id == $_GET['users']){
						echo '<option value="'.$data_user->id.'" selected="true">'.$data_user->name.' </option>';
					}else {
						echo '<option value="'.$data_user->id.'" >'.$data_user->name.' </option>';
					}
				}
			?>
        </select>
	</fieldset>
	<?php } ?>
	<fieldset class="selector">
	<legend>Typologie d’appel  :</legend>
        <select id="appel-selector" name="fonction" class="edit" onchange="updateListe_filtre()">
              <option value=""> - </option>
			  <?php
				if($_GET['appel'] == 'relance'){
					echo '<option value="relance" selected="true">Relance devis </option>							
						  <option value="livraison">Feedback livraison </option>							
                          <option value="rdv">RDV téléphonique </option>							
						  <option value="campagne">Campagne commerciale</option>							
                          <option value="requalif">Requalif lead</option>';
				}else if($_GET['appel'] == 'livraison'){
					echo '<option value="relance">Relance devis </option>							
						  <option value="livraison" selected="true">Feedback livraison </option>							
                          <option value="rdv">RDV téléphonique </option>							
						  <option value="campagne">Campagne commerciale</option>							
                          <option value="requalif">Requalif lead</option>';
				}else if($_GET['appel'] == 'rdv'){
					echo '<option value="relance">Relance devis </option>							
						  <option value="livraison">Feedback livraison </option>							
                          <option value="rdv" selected="true">RDV téléphonique </option>							
						  <option value="campagne">Campagne commerciale</option>							
                          <option value="requalif">Requalif lead</option>';
				}else if($_GET['appel'] == 'campagne'){
					echo '<option value="relance">Relance devis </option>							
						  <option value="livraison">Feedback livraison </option>							
                          <option value="rdv">RDV téléphonique </option>							
						  <option value="campagne" selected="true">Campagne commerciale</option>							
                          <option value="requalif">Requalif lead</option>';
				}else if($_GET['appel'] == 'requalif'){
					echo '<option value="relance">Relance devis </option>							
						  <option value="livraison">Feedback livraison </option>							
                          <option value="rdv">RDV téléphonique </option>							
						  <option value="campagne">Campagne commerciale</option>							
                          <option value="requalif" selected="true">Requalif lead</option>';
				}else if($_GET['appel'] == 'all'){
					echo '<option value="relance">Relance devis </option>							
						  <option value="livraison">Feedback livraison </option>							
                          <option value="rdv">RDV téléphonique </option>							
						  <option value="campagne">Campagne commerciale</option>							
                          <option value="requalif">Requalif lead</option>';
				}else{
					echo '<option value="relance">Relance devis </option>							
						  <option value="livraison">Feedback livraison </option>							
                          <option value="rdv">RDV téléphonique </option>							
						  <option value="campagne">Campagne commerciale</option>							
                          <option value="requalif">Requalif lead</option>';
				}
			  ?>
              							
        </select>
	</fieldset>
	
	<fieldset class="selector">
        <legend>Joignabilité  :</legend>
        <select id="joinabilite-selector" name="fonction" class="edit" onchange="updateListe_filtre()">
			<?php
				if($_GET['joinabilite'] == 'not_called'){
					echo '<option value="all">Tous les appels </option>							
						  <option value="not_called" selected="true">Non appelés </option>							
						  <option value="absence">Appels en absence </option>';						  
				}else if($_GET['joinabilite'] == 'absence'){
					echo '<option value="all">Tous les appels </option>							
						  <option value="not_called">Non appelés </option>							
						  <option value="absence" selected="true">Appels en absence </option>';
				}else if($_GET['joinabilite'] == 'all'){
					echo '<option value="all" selected="true">Tous les appels </option>							
						  <option value="not_called">Non appelés </option>							
						  <option value="absence">Appels en absence </option>';
				}else {
					echo '<option value="all">Tous les appels </option>							
						  <option value="not_called">Non appelés </option>							
						  <option value="absence">Appels en absence </option>';
				}
			?>
			  							
        </select>
    </fieldset>	
	</div>
	<br />
	<div class="blocka" style="float: right;">
		<div id="popup_window" style="margin-left: 30px;" data-popup-target_close="#example-popup" onclick="affiche_popup()">Créer une campagne</div>
	</div>	
  </div>
	  
      <div class="zero"></div>
    </div>
  </div>

   
  <div id="msg-tooltip" class="tooltip"></div>
   <div id="example-popup" class="popup">
    <div class="popup-body">	
	    <div class="popup-content">
            <h2 class="popup-title" style="margin-bottom: 20px;"> Charger une campagne d'appel </h2>
			<form action="" method="post" enctype="multipart/form-data">
			<div id="content_ajax" style="">
				<div>
				<div class="div_bottom">
					<div class="float_span">Nom de la campagne <span>*</span>:</div>
					<div class="des_over"><input type="text"  name="name_campagne" id="name_campagne" style="float: left;" /></div>
				</div>
				<div class="div_bottom">
					<div class="float_span">Choix du commercial <span>*</span>: </div>
					<div class="des_over">
					<select name="comm_id" id="comm_id" style="float: left;" >
						<?php
							$sql_comm = "SELECT  bu.id,bu.name ,bup.id_functionality
										 FROM bo_users bu,bo_users_permissions bup
										 WHERE bu.id = bup.id_user
										 AND bup.id_functionality ='6755' ";
							$req_comm = mysql_query($sql_comm);
							echo '<option value="">--</option>';
							while($data_comm = mysql_fetch_object($req_comm)){ ?>
								<option value="<?= $data_comm->id ?>"><?= $data_comm->name ?></option>
							<?php } ?>
					</select>
					</div>
				</div>
				<div class="div_bottom">
					<div class="float_span">ID campagne <span>*</span>: </div>
					<div class="des_over"><input type="text" name="id_campagne"  id="id_campagne" style="float: left;" /></div>
				</div>
				<div class="div_bottom">
					<div class="float_span">Fichier excel:  <span>*</span>:</div>
					<div class="des_over"><input type="file" required name="fichier_name" id="fichier_name" style="float: left;" /></div>
				</div>
				</div>
			</div>
			
			<div class="tow_button">
			   <div style="float: left;">
					<input type="submit" class="btn ui-state-default ui-corner-all" id="charger">Charger</button>
			   </div>
			   <div class="popup-exit"  style="overflow: hidden; width: 65px;"><button class="btn ui-state-default ui-corner-all">Fermer</button></div>
			</div>
			</form>
        </div>
    </div>
	</div>
	
	<?php

		if (isset($_POST['name_campagne']) && (isset($_POST['name_campagne']) )){
			
			function random($universal_key) {
				$string1 = "";
				$user_ramdom_key = "1234567890";
				srand((double)microtime()*time());
				for($i=0; $i<$universal_key; $i++) {
				$string1 .= $user_ramdom_key[rand()%strlen($user_ramdom_key)];
				}
				return $string1;
			}
	$key_1     		= random(10);
    $key_2   		= random(10);
	$target_path = "upload/";
    $validextensions = array("csv");
    $ext = explode('.', basename($_FILES['fichier_name']['name']));
    $file_extension = end($ext);                     
    $name_adress_picture    =    $key_1;
    $name_adress_picture    =    $name_adress_picture.$key_2;
    $name_adress_picture    =    $name_adress_picture.'.';
    $name_adress_picture    =    $name_adress_picture.$ext[count($ext) - 1];
	
	if(move_uploaded_file($_FILES['fichier_name']['tmp_name'], $target_path.$name_adress_picture)) {	
		$file = file($target_path.$name_adress_picture);
		foreach($file as $k){
				$csv[] = explode(';', $k);
		}
		$count_files  = count($csv);
		for ($x = 0; $x <= $count_files ; $x++ ) {
			$f_email	= $csv[$x][0];
			if(!empty($f_email)){
			$email_ff = trim($f_email);
			$sql_infos  = "SELECT id FROM clients WHERE email ='$email_ff'  ";
			$req_infos  = mysql_query($sql_infos);
			$data_infos = mysql_fetch_assoc($req_infos);
			
			if(!empty($data_infos['id'])){
			$name_campagne = mysql_real_escape_string($_POST['name_campagne']);
			$comm_id 	   = $_POST['comm_id'];
			$id_campagne   = $_POST['id_campagne'];
			$sql_insert = "INSERT INTO `call_spool_vpc` (
									`id`, 
									`order_id`, 
									`client_id`, 
									`rdv_id`, 
									`campaignID`, 
									`estimate_id`, 
									`timestamp_created`, 
									`timestamp_campaign`, 
									`campaign_name`, 
									`call_type`, 
									`assigned_operator`, 
									`call_operator`, 
									`timestamp_rdv`, 
									`timestamp_first_call`, 
									`timestamp_second_call`, 
									`timestamp_third_call`, 
									`calls_count`, 
									`call_result`) 
						  VALUES (NULL, '', 
										'".$data_infos['id']."', 
										'', 
										'$id_campagne', 
										'', 
										NOW(),
										NOW(), 
										'$name_campagne', 
										'3', 
										'$comm_id', 
										'', 
										'', 
										'0000-00-00 00:00:00', 
										'0000-00-00 00:00:00', 
										'0000-00-00 00:00:00', 
										'0', 
										'not_called')";
			//echo $sql_insert.'<br />';
			mysql_query($sql_insert);
			}
			}
		}
		unlink($target_path.$name_adress_picture);
	}
	}
	
	?>
	
  <div id="result_forms"></div>
 
  
</div>

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
<script src="script_vpc.js"></script>

<?php } ?>
<?php require(ADMIN."tail.php") ?>


