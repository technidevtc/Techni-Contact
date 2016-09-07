<?php

	require_once('extranet_v3_functions.php'); 
	
	//Loading informations from the Database

	if(!empty($_SESSION['extranet_user_id'])){
	
		$req_get_infos	= "SELECT 
								a.id ,
								a.nom1,
								a.adresse1,
								a.ville,
								a.ville,
								a.cp,
								a.pays,
								a.tel1,
								a.fax1,
								a.url,
								a.contact,
								a.email
							FROM  
								advertisers a
							WHERE  
								a.id=".$_SESSION['extranet_user_id']." ";
		
		$res_get_infos 			= $db->query($req_get_infos, __FILE__, __LINE__);
				
		$content_get_infos		= $db->fetchAssoc($res_get_infos);
		
		if(!empty($content_get_infos['nom1'])){
			
			//Start div .row
			echo('<div class="row">');
				echo('<div class="infos_element_popup_left">');
					echo('<label for="infos_basic_name">Nom soci&eacute;t&eacute; :</label>');
					echo(' <span class="srequired">*</span>');
				echo('</div>');
				
				echo('<div class="infos_element_right">');
					echo('<input type="text" id="infos_basic_name" name="infos_basic_name" value="'.$content_get_infos['nom1'].'" />');
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div class="row">');
				echo('<div class="infos_element_popup_left">');
					echo('<label for="infos_basic_adresse">Adresse :</label>');
					echo(' <span class="srequired">*</span>');
				echo('</div>');
				
				echo('<div class="infos_element_right">');
					echo('<input type="text" id="infos_basic_adresse" name="infos_basic_adresse" value="'.$content_get_infos['adresse1'].'" />');
				echo('</div>');
			echo('</div>');
			//End div .row
			
			//Start div .row
			echo('<div class="row">');
				echo('<div class="infos_element_popup_left">');
					echo('<label for="infos_basic_ville">Ville :</label>');
					echo(' <span class="srequired">*</span>');
				echo('</div>');
				
				echo('<div class="infos_element_right">');
					echo('<input type="text" id="infos_basic_ville" name="infos_basic_ville" value="'.$content_get_infos['ville'].'" />');
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div class="row">');
				echo('<div class="infos_element_popup_left">');
					echo('<label for="infos_basic_cp">Code postal :</label>');
					echo(' <span class="srequired">*</span>');
				echo('</div>');
				
				echo('<div class="infos_element_right">');
					echo('<input type="text" id="infos_basic_cp" name="infos_basic_cp" value="'.$content_get_infos['cp'].'" />');
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div class="row">');
				echo('<div class="infos_element_popup_left">');
					echo('<label for="infos_basic_pays">Pays :</label>');
					echo(' <span class="srequired">*</span>');
				echo('</div>');
				
				echo('<div class="infos_element_right">');
					echo('<input type="text" id="infos_basic_pays" name="infos_basic_pays" value="'.$content_get_infos['pays'].'" />');
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div class="row">');
				echo('<div class="infos_element_popup_left">');
					echo('<label for="infos_basic_tel">T&eacute;l&eacute;phone :</label>');
					echo(' <span class="srequired">*</span>');
				echo('</div>');
				
				echo('<div class="infos_element_right">');
					echo('<input type="text" id="infos_basic_tel" name="infos_basic_tel" value="'.$content_get_infos['tel1'].'" />');
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div class="row">');
				echo('<div class="infos_element_popup_left">');
					echo('<label for="infos_basic_fax">Fax :</label>');
				echo('</div>');
				
				echo('<div class="infos_element_right">');
					echo('<input type="text" id="infos_basic_fax" name="infos_basic_fax" value="'.$content_get_infos['fax1'].'" />');
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div class="row">');
				echo('<div class="infos_element_popup_left">');
					echo('<label for="infos_basic_url">Site internet :</label>');
				echo('</div>');
				
				echo('<div class="infos_element_right">');
					echo('<input type="text" id="infos_basic_url" name="infos_basic_fax" value="'.$content_get_infos['url'].'" />');
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div class="row">');
				echo('<div class="infos_element_popup_left">');
					echo('<label for="infos_basic_contact">Contact Principal :</label>');
					echo(' <span class="srequired">*</span>');
				echo('</div>');
				
				echo('<div class="infos_element_right">');
					echo('<input type="text" id="infos_basic_contact" name="infos_basic_contact" value="'.$content_get_infos['contact'].'" />');
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div class="row">');
				echo('<div class="infos_element_popup_left">');
					echo('<label for="infos_basic_econtact">Email contact principal :</label>');
					echo(' <span class="srequired">*</span>');
				echo('</div>');
				
				echo('<div class="infos_element_right">');
					echo('<input type="text" id="infos_basic_econtact" name="infos_basic_econtact" value="'.$content_get_infos['email'].'" />');
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div id="infos_basic_edit_errors" class="row">');
			echo('</div>');
			//End div .row
		
		}else{
			echo('Erreur chargement informations !');
		}
	
	
	}else{
		//header('Location: /login.html');
		echo('<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;<strong><a href="login.html">Merci de vous reconnecter.</a></strong>');
	}//end elseif session//end if session
?>