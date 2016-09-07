<?php
	require_once('extranet_v3_functions.php'); 
	
	$name					= mysql_escape_string($_POST['name']);
	$adresse				= mysql_escape_string($_POST['adresse']);
	$ville					= mysql_escape_string($_POST['ville']);
	$code_postale			= mysql_escape_string($_POST['code_postale']);
	$pays					= mysql_escape_string($_POST['pays']);
	$tel					= mysql_escape_string($_POST['tel']);
	$fax					= mysql_escape_string($_POST['fax']);
	$url					= mysql_escape_string($_POST['url']);
	$contact_name			= mysql_escape_string($_POST['contact_name']);
	$contact_mail			= mysql_escape_string($_POST['contact_mail']);
	
	
	if(!empty($_SESSION['extranet_user_id'])){

		if(!empty($name) && !empty($adresse) &&!empty($ville) && !empty($code_postale) && !empty($pays) && !empty($tel) && !empty($contact_name) && !empty($contact_mail)){

			
			$sql_update_advertiser	= "UPDATE
											advertisers
										SET
											nom1='".$name."',
											adresse1='".$adresse."',
											ville='".$ville."',
											cp='".$code_postale."',
											pays='".$pays."',
											tel1='".$tel."',
											fax1='".$fax."',
											url='".$url."',
											contact='".$contact_name."',
											email='".$contact_mail."'	
											
										WHERE
											id=".$_SESSION['extranet_user_id']."
										";
			
			$res_update_advertiser	=  $db->query($sql_update_advertiser, __FILE__, __LINE__);
		
		
			//Creation log of this action !
			
			$sql_insert_log_advertiser	= "INSERT INTO advertisers_extranet_infos_history
											(id, idadvertiser, 
											nom1, adresse1,
											ville, cp,
											pays, tel1,
											fax1, url,
											contact, econtact,
											date_modification)
											 
											VALUES(NULL, ".$_SESSION['extranet_user_id'].",
												'".$name."', '".$adresse."',
												'".$ville."', '".$code_postale."',
												'".$pays."', '".$tel."',
												'".$fax."', '".$url."',
												'".$contact_name."', '".$contact_mail."',
												NOW())
											";
										
			$res_insert_log_advertiser	=  $db->query($sql_insert_log_advertiser, __FILE__, __LINE__);					
			
			
		
			//That is OK
			echo('1');
		
		}else{
			echo('0');
		}//end else if !empty fields
	
	}else{
		echo('-1');
	}//End else if(!empty($_SESSION['extranet_user_id']))
?>