<?php
	if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../../../config.php';
	}else{
		require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
	}

	//$title = $navBar = "MAJ Fournisseurs";
	$db = DBHandle::get_instance();
	$user = new BOUser();
	
?>
<html>
	<head>
		<title>Mise &agrave; jour || Cr&eacute;ation familles 'Fr'</title>
	</head>
	<body>
		<div id="mcmf_container">
			<?php
			
				//If user is connected !
				try {
					if (!$user->login())
						throw new Exception("Votre session a expiré, veuillez vous identifier à nouveau après avoir rafraîchi votre page.");
			
					$id_operation		= mysql_real_escape_string(trim($_GET['id_operation']));
					
					if(!empty($id_operation)){
						//Get the new import from file by id_operation
						$res_get_new_import	= $db->query("SELECT
															fam_update.id,	fam_update.id_famille,
															fam_update.text_content
														FROM
															families_fr_update_import_from_file fam_update
														WHERE
															fam_update.id_operation=".$id_operation."", __FILE__, __LINE__);
						$total_lignes		= mysql_num_rows($res_get_new_import);
						if($total_lignes>0){	

							echo('D&eacute;but traitement <br />');
						
							$operation_total_insert	= 0;
							$operation_total_update	= 0;
							$total_ids_insert		= '';
							while($content_get_new_import = $db->fetchAssoc($res_get_new_import)){
							
								//Search one by one if it exist
								$res_get_one_familie	= $db->query("SELECT
																		fam_fr.id, fam_fr.text_content
																	FROM families_fr fam_fr
																	WHERE
																		fam_fr.id=".$content_get_new_import['id_famille']."", __FILE__, __LINE__);

								if(mysql_num_rows($res_get_one_familie)!=0){
									//This familie exist we have to update it
									$res_get_one_familie	= $db->query("UPDATE families_fr fam_fr
																			SET fam_fr.text_content='".addslashes($content_get_new_import['text_content'])."'
																			WHERE
																				fam_fr.id=".$content_get_new_import['id_famille']."", __FILE__, __LINE__);																	
																		
									$operation_total_update++;
								}else{
									//This familie doesn't exist we have to create it
									
									$total_ids_insert .= $content_get_new_import['id_famille'].';';
									$operation_total_insert++;
								}//end else if(mysql_num_rows($res_get_one_familie)!=0){
																
							}//end while fetching
							
							echo('Total lignes : <strong>'.$total_lignes.'</strong><br />');
							echo('Total ajout : <strong>'.$operation_total_insert.'</strong> '.$total_ids_insert.'<br />');
							echo('Total modification : <strong>'.$operation_total_update.'</strong><br />');
							
						}else{
							throw new Exception("Aucun traitement &agrave; faire pour cette op&eacute;ration !");
						}//end if(mysql_num_rows
					}else{
						throw new Exception("Id operation invalide !");
					}//end else if(!empty($id_operation))
			
				}catch(Exception $e){
					echo($e);
				}
			?>	
		</div>
	</body>
</html>