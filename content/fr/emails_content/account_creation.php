<?php
$this->content .= <<< EOF
	<div class="mail_body">
		<p>Bonjour,</p>
		<p>
			Vous vous êtes récemment inscrit avec succès sur notre site.<br />
			Votre numéro client est : <b>{$data['CustomerID']}</b><br />
			Pour avoir accès à votre compte, ainsi qu’à d’autres ressources,
			connectez-vous sur le site à l’adresse suivante : <a href="{$constant['COMPTE_URL']}/login.html">{$constant['COMPTE_URL']}login.html</a><br />
			login : <i><b>{$data['CustomerLogin']}</b></i><br />
			Mot de passe : <i><b>{$data['CustomerPassword']}</b></i><br />
		</p>
		<p>Veillez à bien noter le mot de passe, car il ne pourra plus vous être communiqué en cas d'oubli.</p>
		
		<p>Nous vous remercions de votre enregistrement.</p>
		
		<p>Meilleures salutations,</p>
		
		<p>L’équipe techni-contact.com</p>
	</div>
EOF;
?>