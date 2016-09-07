<?php

	if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../../../config.php';
	}else{
		require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
	}

	$db = DBHandle::get_instance();
	$user = new BOUser();
	
	try {
	  if (!$user->login())
		throw new Exception("Votre session a expiré, veuillez vous identifier à nouveau après avoir rafraîchi votre page.");
		

		$nom_fournisseur	= mysql_real_escape_string(trim($_POST['nom_fournisseur']));
		
		if(!empty($nom_fournisseur)){

			$res_autocomplete_fournisseur = $db->query(" SELECT
															adv.nom1 as name
														FROM
															advertisers adv
														WHERE
															adv.nom1 like '%".$nom_fournisseur."%'	

														LIMIT 0,10", __FILE__, __LINE__);
			
			$result_count	= mysql_num_rows($res_autocomplete_fournisseur);
			if($result_count>0){
			
				echo('<ul>');
					$actual_element=0;
					while($content_autocomplete_fournisseur = $db->fetchAssoc($res_autocomplete_fournisseur)){
						$actual_element++;
						
						echo('<li onclick="mmf_autofill(\''.addslashes($content_autocomplete_fournisseur['name']).'\')"');
						if($result_count==$actual_element){
							echo(' class="lastone"');
						}
						echo('>');
						
							echo(ucfirst($content_autocomplete_fournisseur['name']));
							
						echo('</li>');
					}//end while
				echo('</ul>');		
			
			}//end if(mysql_num_rows($res_autocomplete_fournisseur)>0){

		}//end if(!empty($nom_fournisseur)){

	}catch(Exception $e){
		echo($e);
	}

?>