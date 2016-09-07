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
		Vous venez de faire une {$data['Customer-RequestType']} pour le produit {$data['MainProduct-Name']}. Nous vous en remercions.<br/>
		<br/>
		Ce produit étant fourni par l'un de nos partenaires, votre demande lui a été directement transmise.<br/>
		<br/>
		Notre partenaire reprendra contact avec vous dans les plus brefs délais (délai moyen constaté : 24H/48H).<br/>
		<br/>
		Si vous souhaitez lui apporter un complément d'information concernant votre demande, vous trouverez ci-dessous ses coordonnées, ainsi qu'un rappel du produit que vous avez sollicité.<br/>
		<br/>
		Merci de votre confiance et à très bientôt sur www.techni-contact.com<br/>
		</p>
	</div>
	<div class="miseazero"></div>
	
	<h2 class="family3">{$data['MainProduct-Advertiser-Name']}</h2>
	<div id="hook">
		<div class="titreBloc">Coordonn&eacute;es</div>
		<div class="mail">
			<div class="haut"></div>
			<div class="ficheProduit">
				<div class="coord">
					{$data['MainProduct-Advertiser-Name']}<br/>
					{$data['MainProduct-Advertiser-Address']} {$data['MainProduct-Advertiser-Complement']}<br/>
					{$data['MainProduct-Advertiser-PC']} {$data['MainProduct-Advertiser-City']}<br/>
					{$data['MainProduct-Advertiser-Country']}<br/>
				</div>
			</div>
			<div class="miseAZero"></div>
			<div class="bas"></div>
		</div>
		<p><br/></p>
		<div class="titreBloc">Informations compl&eacute;mentaires </div>
		<div class="mail">
			<div class="haut"></div>
			<div class="ficheProduit">
				<div class="sousTitreBloc">Téléphone : <span class="coord2">{$data['MainProduct-Advertiser-Phone']}</span></div>
				<div class="sousTitreBloc">Fax : <span class="coord2">{$data['MainProduct-Advertiser-Fax']}</span></div>
				{$data['MainProduct-Advertiser-Contact']}
				{$data['MainProduct-Advertiser-Email']}
				{$data['MainProduct-Advertiser-URL']}
			</div>
			<div class="miseAZero"></div>
			<div class="bas"></div>
		</div>
		<p><br/></p>
		{$data['MainProduct-Precisions']}
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