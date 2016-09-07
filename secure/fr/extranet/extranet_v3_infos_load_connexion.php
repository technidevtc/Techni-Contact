<?php

	require_once('extranet_v3_functions.php'); 
	
	//Loading informations from the Database

	if(!empty($_SESSION['extranet_user_id'])){
	
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
				echo('<div class="infos_element_left">');
					echo('Login :');
				echo('</div>');
				
				echo('<div class="infos_element_right">');
					echo($content_get_infos['login']);
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div class="row">');
				echo('<div class="infos_element_left">');
					echo('Mot de passe :');
				echo('</div>');
				
				echo('<div class="infos_element_right">');
					echo('<div id="user_connexion_container">');
						echo('*****');
						
						//Button to show the password
						/*
						echo('<input type="button" id="user_connexion_listner" onclick="javascript:infos_connexion_show_password()" class="btn btn-default" value="Afficher"   style="margin-left: 30px; position: absolute; margin-top: -8px;" />');
						*/
					echo('</div>');
				echo('</div>');
				
				//Button to show the password
				/*echo('<input type="hidden" id="user_connexion_hpassword" value="'.$content_get_infos['pass'].'" />');*/
				
			echo('</div>');
			//End div .row
			
				
			
			//Start div .row
			echo('<div class="row">');
				echo('<br />');
				echo('<div class="infos_element_left">');
					echo('&nbsp;');
				echo('</div>');
				
				echo('<div class="infos_element_right" style="float: right; padding-right: 10%;">');
					echo('<input type="button" id="" class="btn btn-default" onclick="javascript:infos_connexion_edit()" value="Modifier" />');
				echo('</div>');
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