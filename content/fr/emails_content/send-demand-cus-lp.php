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
		Voici la fiche d'un produit similaire à celui que vous avez consulté sur le
		site <a href="http://www.techni-contact.com" target="_blank">Techni-Contact</a>, ainsi que les coordonnées de son fabricant.
		<br/>
		<br/>
		L'équipe TECHNI-CONTACT
		</p>
	</div>
	<div class="miseazero"></div>
	
	<h2 class="family3">{$data['LinkedProduct-Advertiser-Name']}</h2>
	<div id="hook">
		<div class="titreBloc">Coordonn&eacute;es</div>
		<div class="mail">
			<div class="haut"></div>
			<div class="ficheProduit">
				<div class="coord">
					{$data['LinkedProduct-Advertiser-Name']}<br/>
					{$data['LinkedProduct-Advertiser-Address']} {$data['LinkedProduct-Advertiser-Complement']}<br/>
					{$data['LinkedProduct-Advertiser-PC']} {$data['LinkedProduct-Advertiser-City']}<br/>
					{$data['LinkedProduct-Advertiser-Country']}<br/>
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
				<div class="sousTitreBloc">Téléphone : <span class="coord2">{$data['LinkedProduct-Advertiser-Phone']}</span></div>
				<div class="sousTitreBloc">Fax : <span class="coord2">{$data['LinkedProduct-Advertiser-Fax']}</span></div>
				{$data['LinkedProduct-Advertiser-Contact']}
				{$data['LinkedProduct-Advertiser-Email']}
				{$data['LinkedProduct-Advertiser-URL']}
			</div>
			<div class="miseAZero"></div>
			<div class="bas"></div>
		</div>
		<p><br/></p>
		{$data['LinkedProduct-Precisions']}
		<div class="titreBloc">Fiche du produit {$data['LinkedProduct-Name']}</div>
		<div class="mail">
			<div class="haut"></div>
			<div class="ficheProduit">
				{$data['LinkedProduct-Image']}
				<div class="sousTitreBloc">Description :</div>
				<div class="contenuBloc">
					{$data['LinkedProduct-Description']}
				</div>
				{$data['LinkedProduct-DetailedDescription']}
				<div class="sousTitreBloc">Prix / Références :</div>
				<div class="contenuBloc">
				{$data['LinkedProduct-PriceData']}
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