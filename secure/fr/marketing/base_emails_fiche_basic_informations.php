<?php 
	require_once('functions.php'); 
	//require_once('check_session.php');

	if(empty($_SESSION['marketing_user_id'])){
		echo('<a href="/fr/marketing/login.php">Veuillez vous reconnecter</a>.');
		die;
	}

	$email_id					= mysql_escape_string($_POST['email_id']);
	if(empty($email_id)){
		echo('Vous avez des erreurs dans votre formulaire !');
		die;
	}
	
	//Get Email Basic Informations
	$res_get_email_query	= "SELECT
									m_base_emails.id, 
									m_base_emails.email,
									m_base_emails.etat, 
									m_base_emails.date_insert, 
									m_base_emails.date_last_edit, 
									m_base_emails.disable_source,

									m_b_e_motifs.label 
								
								FROM 
									marketing_base_emails AS m_base_emails
										LEFT JOIN marketing_base_email_motifs AS m_b_e_motifs ON m_base_emails.motif=m_b_e_motifs.id
								WHERE 
									m_base_emails.id=".$email_id."";	
							
	$res_get_email 			= $db->query($res_get_email_query, __FILE__, __LINE__);
	$content_get_email		= $db->fetchAssoc($res_get_email);
?>
	<div id="base_email_top_element_left">
		Adresse
		<?php
			if(strcmp($content_get_email['etat'],'ok')==0){
				echo(' <b>activ&eacute;e</b>');
			}else{
				echo(' <b>d&eacute;sactiv&eacute;e</b>');
				if(strcmp($content_get_email['disable_source'],'human')==0){
					echo(' manuellement');
				}else if(strcmp($content_get_email['disable_source'],'robot')==0){
					echo(' par Programme');
				}else{
					echo(' - ');
				}
				echo(' &agrave; cause de <b>'.$content_get_email['label'].'</b>');
				
			}
		?>
	</div>
	<div  id="base_email_top_element_right">
		<?php
			if(strcmp($content_get_email['etat'],'ok')==0){
				echo('<input type="button" class="btn btn-default" value="D&eacute;sabonner" onclick="ask_blacklist_this_address(\''.$content_get_email['id'].'\')" style="width:103px;" />');
			}else{
				echo('<input type="button" class="btn btn-default" value="R&eacute;activer" onclick="ask_autorize_this_address(\''.$content_get_email['id'].'\')" style="width:103px;" />');
			}
		?>
	</div>