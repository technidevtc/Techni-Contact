<?php

	if(strcmp($_SESSION['extranet_user_category'],'5')==0){
	
		echo('<div id="advertiser_category5">');
			echo('ATTENTION, suite &agrave; non-paiement de facture(s), votre compte est pass&eacute; en mode restreint. Afin de r&eacute;gulariser votre compte, veuillez nous contacter au plus vite : <a href="mailto:comptabilite@techni-contact.com">comptabilite@techni-contact.com</a> ou <a href="tel:01.55.60.29.29">01.55.60.29.29</a>');
		echo('</div>');
		
	}else if(strcmp($_SESSION['extranet_user_category'],'3')==0 || strcmp($_SESSION['extranet_user_category'],'4')==0 ){
	
		echo('<div id="advertiser_category3_4">');
			echo('Vous souhaitez avoir acc&egrave;s &agrave; l\'identit&eacute; compl&egrave;te de nos prospects ? Contactez le <a href="tel:01.72.08.01.28">01.72.08.01.28</a> ou utilisez le formulaire de contact ');
		echo('</div>');
	
	}//end if

?>