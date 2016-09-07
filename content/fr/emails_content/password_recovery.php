<?php
$this->content .= <<< EOF
	<div class="mail_body">
		<p>
			Cher(ère) client(e),<br/>
			<br/>
			Nous avons bien reçu votre requête de demande de récupération de vos identifiants.<br/>
			Vous trouverez ci-dessous les informations que nous possédons à ce sujet.<br/>
			Si vous n'avez pas soumis cette requête, veuillez ignorer cet e-mail.<br/>
			<br/>
			Votre login : {$data['Login']}<br/>
			Votre mot de passe : {$data['Password']}<br/>
			E-mail enregistré lors de votre inscription : {$data['Email']}<br/>
			<br/>
			Nous vous rappelons que vous pouvez modifier votre mot de passe à tout moment à partir de la rubrique "mon compte" sur notre site.<br/>
			<br/>
			Merci de votre confiance et à bientôt !<br/>
			<br/>
			Cordialement,<br/>
			<br/>
			L’équipe techni-contact.com<br/>
			<br/>
		</p>
	</div>
EOF;
?>