<?php 
	require_once('functions.php'); 
	//require_once('check_session.php');

	if(empty($_SESSION['marketing_user_id'])){
		throw new Exception('<a href="/fr/marketing/login.php">Veuillez vous reconnecter</a>.');
	}


?>
	<br />
	<center>
		<select id="f_blacklist_select" name="f_blacklist_select" style="float:none;">
			<?php
			
				$res_get_motifs_query	= "SELECT
												id, 
												label 
											FROM
												marketing_base_email_motifs
											ORDER BY label";
				
				$res_get_motifs = $db->query($res_get_motifs_query, __FILE__, __LINE__);
				while($content_get_motifs	= $db->fetchAssoc($res_get_motifs)){
					echo('<option value="'.$content_get_motifs['id'].'">'.utf8_decode($content_get_motifs['label']).'</option>');
				}
			?>
		</select>
		
		<input type="button" id="base_emails_btn_blacklist" value="Confirmer" onclick="blacklist_this_address()" class="btn btn-primary" />
	</center>