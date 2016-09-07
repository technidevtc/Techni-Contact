<?php
$this->content .= <<< EOF
<div>
	<div>
		<a title="Page d'accueil" href="{$constant['URL']}">
			<img id="logo" alt="Logo Techni Contact" src="{$constant['URL']}images/logo.jpg" nosend="1"/>
		</a>
	</div>
	<div class="intro">
		<p>Cher Utilisateur TECHNI-CONTACT,<br/>
		<br/>
		Vous venez de faire une <strong>{$data['Customer-RequestType']}</strong> pour le produit <strong>{$data['MainProduct-Name']}</strong>. Nous vous en remercions.<br/>
		<br/>
		Votre demande a été transmise à notre service commercial qui reprendra contact avec vous sous 24/48H.<br/>
		<br/>
		Notre équipe reste à votre disposition de 8H à 18H pour tout complément d’information :<br/>
		<br/>
		William DEBAES<br/>
		01 55 60 29 26<br/>
		w.debaes@techni.contact.com<br/>
		<br/>
		Ornella PATTI<br/>
		01 55 60 29 21<br/>
		o.patti@techni.contact.com<br/>
		<br/>
		Supriya JOLY<br/>
		01 55 60 29 20<br/>
		info22@techni-contact.com<br/>
		<br/>
		Merci de votre confiance et à très bientôt sur www.techni-contact.com<br/>
		</p>
	</div>
	<div class="miseazero"></div>
	
	<div id="hook">
		<div class="titreBloc">Fiche du produit {$data['MainProduct-Name']}</div>
		<div class="mail">
			<div class="haut"></div>
			<div class="ficheProduit">
				{$data['MainProduct-Image']}
				<div class="sousTitreBloc">Description :</div>
				<div class="contenuBloc">
					{$data['MainProduct-Description']}
				</div>
				{$data['MainProduct-DetailedDescription']}
				<div class="sousTitreBloc">Prix / Références :</div>
				<div class="contenuBloc">
				{$data['MainProduct-PriceData']}
				</div>
			</div>
			<div class="miseAZero"></div>
			<div class="bas"></div>
		</div>
		<p></p>
	</div>
	
	<div class="miseazero"></div>
</div>
EOF;
?>