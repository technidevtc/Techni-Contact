<?php
$this->content .= <<< EOF
<div>
	<div>
		<a title="Page d'accueil" href="{$constant['URL']}">
			<img id="logo" alt="Logo Techni Contact" src="{$constant['URL']}ressources/images/logo.jpg" nosend="1"/>
		</a>
	</div>
	<div class="intro">
		<p>Cher Annonceur,<br />
		<br />
		Un utilisateur de notre site <a href="{$constant['URL']}" target="_blank">Techni-Contact</a>
		vient de demander des informations sur votre produit {$data['LINKEDPRODUCT_NAME']}.
		<br />
		Cliquez sur le lien suivant pour accéder à votre extranet et en prendre connaissance.
		<br />
		<br />
		<a href="{$constant['EXTRANET_URL']}request_detail.html?uid={$data['LINKEDPRODUCT_ADVERTISER_WEBPASS']}&id={$data['LINKEDPRODUCT_GENERATEDCONTACTID']}">Accéder à la demande de contact sur l'extranet</a>
		<br />
		<br />
		L'équipe TECHNI-CONTACT
		</p>
	</div>
</div>
EOF;
?>