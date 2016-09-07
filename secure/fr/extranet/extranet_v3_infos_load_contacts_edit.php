<?php
	require_once('extranet_v3_functions.php'); 
	
	$id					= mysql_escape_string($_POST['id']);
	
	if(!empty($_SESSION['extranet_user_id'])){
	
		if(!empty($id)){
		
			//Loading the user contact
			$sql_get_contacts	= "SELECT 
										id, nom,
										prenom, email,
										date_creation
									FROM 
										extranet_contacts_users
									WHERE
										idadvertiser=".$_SESSION['extranet_user_id']."
									AND
										id=".$id."";
				
			$res_get_contacts	=  $db->query($sql_get_contacts, __FILE__, __LINE__);
			
			if(mysql_num_rows($res_get_contacts)!=0){
			
				$content_get_contacts = $db->fetchAssoc($res_get_contacts);
				
				//Start div .row
				echo('<div class="row">');
					echo('<br />');
					echo('<div class="infos_element_left" style="width: 80px;">');
						echo('<label for="infos_prenom_popup">Pr&eacute;nom</label>');
						echo(' <span class="srequired">*</span>');
					echo('</div>');
					
					echo('<div class="infos_element_right" style="float: right;">');
						echo('<input type="text" id="infos_prenom_popup" value="'.$content_get_contacts['prenom'].'" />');
					echo('</div>');
				echo('</div>');
				//End div .row
				
				
				//Start div .row
				echo('<div class="row">');
					echo('<br />');
					echo('<div class="infos_element_left" style="width: 80px;">');
						echo('<label for="infos_nom_popup">Nom</label>');
						echo(' <span class="srequired">*</span>');
					echo('</div>');
					
					echo('<div class="infos_element_right" style="float: right;">');
						echo('<input type="text" id="infos_nom_popup" value="'.$content_get_contacts['nom'].'" />');
					echo('</div>');
				echo('</div>');
				//End div .row
				
				
				//Start div .row
				echo('<div class="row">');
					echo('<br />');
					echo('<div class="infos_element_left" style="width: 80px;">');
						echo('<label for="infos_contact_email_popup">Email</label>');
						echo(' <span class="srequired">*</span>');
					echo('</div>');
					
					echo('<div class="infos_element_right" style="float: right;">');
						echo('<input type="text" id="infos_contact_email_popup" value="'.$content_get_contacts['email'].'" />');
					echo('</div>');
				echo('</div>');
				//End div .row
				
				
				//Start div .row
				echo('<div id="infos_contacts_errors" class="row">');
				echo('</div>');
				//End div .row
			
			}else{
				//Contact not found !
				echo('0');
			}
			
		}else{
			echo('0');
		}//end else if !empty fields
	
	}else{
		echo('-1');
	}//End else if(!empty($_SESSION['extranet_user_id']))
?>