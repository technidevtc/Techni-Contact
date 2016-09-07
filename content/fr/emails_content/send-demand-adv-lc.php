<?php
$this->content .= <<< EOF
<div>
	<div>
		<a title="page d'accueil" href="{$constant['URL']}">
			<img id="logo" alt="Logo Techni Contact" src="{$constant['URL']}ressources/images/logo.jpg" nosend="1"/>
		</a>
	</div>
	<div class="intro">
		<p>Cher partenaire,<br/>
		<br/>
		Vous avez reçu une demande de devis.<br/>
		Cliquez sur le lien suivant pour accéder à votre extranet et en prendre connaissance.<br/>
		<br/>
		<a href="{$constant['EXTRANET_URL']}request_detail.html?uid={$data['ADVERTISER_WEBPASS']}&id={$data['GENERATEDCONTACTID']}">Accéder à la demande de contact sur l'extranet</a><br/>
		<br/>
		L'équipe TECHNI-CONTACT
		</p>
	</div>
</div>
EOF;
?>