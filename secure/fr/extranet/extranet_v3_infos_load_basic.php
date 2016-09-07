<?php

	require_once('extranet_v3_functions.php'); 
	
	//Loading informations from the Database

	if(!empty($_SESSION['extranet_user_id'])){
	
		$req_get_infos	= "SELECT 
								a.id ,
								a.nom1,
								a.adresse1,
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
				echo('<div class="infos_element_left">');
					echo('Nom soci&eacute;t&eacute; :');
				echo('</div>');
				
				echo('<div class="infos_element_right">');
					echo($content_get_infos['nom1']);
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div class="row">');
				echo('<div class="infos_element_left">');
					echo('Adresse :');
				echo('</div>');
				
				echo('<div class="infos_element_right">');
					echo($content_get_infos['adresse1']);
				echo('</div>');
			echo('</div>');
			//End div .row
			
			//Start div .row
			echo('<div class="row">');
				echo('<div class="infos_element_left">');
					echo('Ville :');
				echo('</div>');
				
				echo('<div class="infos_element_right">');
					echo($content_get_infos['ville']);
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div class="row">');
				echo('<div class="infos_element_left">');
					echo('Code postal :');
				echo('</div>');
				
				echo('<div class="infos_element_right">');
					echo($content_get_infos['cp']);
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div class="row">');
				echo('<div class="infos_element_left">');
					echo('Pays :');
				echo('</div>');
				
				echo('<div class="infos_element_right">');
					echo($content_get_infos['pays']);
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div class="row">');
				echo('<div class="infos_element_left">');
					echo('T&eacute;l&eacute;phone :');
				echo('</div>');
				
				echo('<div class="infos_element_right">');
					echo($content_get_infos['tel1']);
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div class="row">');
				echo('<div class="infos_element_left">');
					echo('Fax :');
				echo('</div>');
				
				echo('<div class="infos_element_right">');
					echo($content_get_infos['fax1']);
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div class="row">');
				echo('<div class="infos_element_left">');
					echo('Site internet :');
				echo('</div>');
				
				echo('<div class="infos_element_right">');
					echo($content_get_infos['url']);
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div class="row">');
				echo('<div class="infos_element_left">');
					echo('Contact Principal :');
				echo('</div>');
				
				echo('<div class="infos_element_right">');
					echo($content_get_infos['contact']);
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div class="row">');
				echo('<div class="infos_element_left">');
					echo('Email contact principal :');
				echo('</div>');
				
				echo('<div class="infos_element_right">');
					echo($content_get_infos['email']);
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div class="row">');
				echo('<div class="infos_element_left">');
					echo('&nbsp;');
				echo('</div>');
				
				echo('<div class="infos_element_right" style="float: right; padding-right: 10%;">');
					echo('<input type="button" id="" class="btn btn-default" onclick="javascript:infos_basic_edit()" value="Modifier" />');
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