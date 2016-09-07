<?php
	require_once('extranet_v3_functions.php'); 
	
	
	if(!empty($_SESSION['extranet_user_id'])){
	
		//Loading the user contacts
		$sql_get_contacts	= "SELECT 
									id, nom,
									prenom, email,
									date_creation
								FROM 
									extranet_contacts_users
								WHERE
									idadvertiser=".$_SESSION['extranet_user_id']." 
								ORDER BY date_creation DESC";
			
		$res_get_contacts	=  $db->query($sql_get_contacts, __FILE__, __LINE__);
		
		if(mysql_num_rows($res_get_contacts)!=0){
		
			//We have some results
			echo('<div id="infos_contacts_response_header">');
				echo('Vous pouvez transf&eacute;rer manuellement vos fiches contacts aux interlocuteurs ci dessous :');
			echo('</div>');
			
			while($content_get_contacts = $db->fetchAssoc($res_get_contacts)){
			
				echo('<div class="row">');
				
					echo('<div class="infos_contacts_fne">');
						echo($content_get_contacts['prenom'].' '.$content_get_contacts['nom'].' - '.$content_get_contacts['email']);
					
					echo('</div>');
					echo('<div class="infos_contacts_actions">');
					
						//Edit
						echo('<a href="javascript:void(0);" title="Modifier" onclick="javascript:infos_contacts_edit_listner(\''.$content_get_contacts['id'].'\')">');
							echo('<i class="fa fa-pencil"></i>');
						echo('</a>');
						echo('&nbsp;&nbsp;&nbsp;');
						
						//Delete
						echo('<a href="javascript:void(0);" title="Supprimer" onclick="javascript:infos_contacts_delete_listner(\''.$content_get_contacts['id'].'\')">');
							echo('<i class="fa fa-trash-o"></i>');
						echo('</a>');
						
					
					echo('</div>');
				
				echo('</div>');
			
			}//end while
		
		
		}else{
			//No contacts found
			echo('<div id="infos_contacts_response_header">');
				echo('Aucun interlocuteur param&eacute;tr&eacute; encore.');
			echo('</div>');
		}
	
	}else{
		echo('&nbsp;<strong><a href="login.html">Merci de vous reconnecter.</a></strong>');
	}//end if empty session
	
?>