<?php
	$message_text	.= '<div style="font: normal 12px verdana,arial,sans-serif">';
		$message_text	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg" />';
		
		$message_text	.= '<br />';
		$message_text	.= '<br />';
		$message_text	.= 'Bonjour<br />';
		$message_text	.= '<br />';
		
		$message_text	.= 'Vous avez re&ccedil;u le '.$external_var_send_time.' une commande de notre part.<br />';
		$message_text	.= '<br />';
		
		$message_text	.= 'Sauf erreur de notre part, cette commande n\'a toujours pas &eacute;t&eacute; ';
		$message_text	.= 'consult&eacute;e par vos services ';
		//$message_text	.= 'ou n\'a pas encore fait l\'objet d\'un Accus&eacute; R&eacute;ception.<br />';
		$message_text	.= '.<br />';
		$message_text	.= '<br />';
	 
		$message_text	.= 'Nous vous serions reconnaissants d\'en prendre connaissance ou de nous faire parvenir votre AR<br />';
		$message_text	.= '<br />';
		
		$message_text	.= '<a href="'.$external_var_url_order.'" target="_blank">Consulter votre commande.</a><br />';
		$message_text	.= '<br />';

		$message_text	.= 'Cordialement<br />';
		$message_text	.= '<br />';

		$message_text	.= 'Le Service Achat Techni-Contact <br />';
		
		$message_text	.= '</div>';
?>