<?php

	require_once('extranet_v3_functions.php'); 
	
	//Loading informations from the Database

	if(!empty($_SESSION['extranet_user_id'])){
	
	
		//Looking for the "Login"
		$req_get_infos	= "SELECT 
								eu.login,
								eu.pass
							FROM  
								extranetusers eu
							WHERE  
								eu.id=".$_SESSION['extranet_user_id']." ";
		
		$res_get_infos 			= $db->query($req_get_infos, __FILE__, __LINE__);
				
		$content_get_infos		= $db->fetchAssoc($res_get_infos);
		
		if(!empty($content_get_infos['login'])){
		
			//Start div .row
			echo('<div class="row">');
				echo('<br />');
				echo('<div class="infos_element_popup_left">');
					echo('<label for="infos_login_popup">Login</label>');
				echo('</div>');
				
				echo('<div class="infos_element_right" style="float: right; padding-right: 10%;">');
					echo('<input type="text" id="infos_login_popup" value="'.$content_get_infos['login'].'" />');
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div class="row">');
				echo('<br />');
				echo('<div class="infos_element_popup_left">');
					echo('<label for="infos_password_popup">Nouveau mot de passe</label>');
				echo('</div>');
				
				echo('<div class="infos_element_right" style="float: right; padding-right: 10%;">');
					echo('<input type="password" id="infos_password_popup" value="" />');
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div class="row">');
				echo('<br />');
				echo('<div class="infos_element_popup_left">');
					echo('<label for="infos_passwordc_popup">Confirmation mot de passe</label>');
				echo('</div>');
				
				echo('<div class="infos_element_right" style="float: right; padding-right: 10%;">');
					echo('<input type="password" id="infos_passwordc_popup" value="" />');
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div id="infos_connexion_edit_errors" class="row">');
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