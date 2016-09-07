<?php
$this->content .= <<< EOF
	<div class="mail_body">
		Bonjour,<br />
		<br />
		Votre commande a bien été enregistrée.<br />
		<br />
		Merci d'avoir choisi techni-contact.com pour l'achat de votre matériel professionnel et technique.
		<br />
		Votre commande a été enregistrée sous le numéro <b>{$data['CommandID']}</b>.<br />
		Vous avez choisi le mode de règlement suivant : <b>{$data['PaymentMean']}</b><br />
		<br />
		Voici le récapitulatif de votre commande :<br />
{$data['CommandRecap']}
		<br />
		<br />
		<i>Rappel</i> : Votre commande ne sera traitée qu'après validation de votre paiement par notre banque.<br />
		<br />
		Cordialement.<br />
		<br />
		L'Equipe techni-contact.com<br />
		<br />
	</div>
EOF;
?>