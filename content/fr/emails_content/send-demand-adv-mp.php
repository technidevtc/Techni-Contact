<?php
$this->content .= <<< EOF
<div>
	<div>
		<a title="Page d'accueil" href="{$constant['URL']}">
			<img id="logo" alt="Logo Techni Contact" src="{$constant['URL']}ressources/images/logo.jpg" nosend="1"/>
		</a>
	</div>
	<div class="intro">
		<p>Cher Annonceur,<br/>
		<br/>
		Un utilisateur de notre site <a href="{$constant['URL']}" target="_blank">Techni-Contact</a>
		vient de demander des informations sur votre produit {$data['MAINPRODUCT_NAME']}.
		<br/>
		<br/>
		L'équipe TECHNI-CONTACT
		</p>
	</div>
	<div class="miseAZero"></div>
	
	<h2 class="family3">{$data['CUSTOMER_COMPANY_NAME']}</h2>
	<div id="hook">
		<div class="titreBloc">Coordonn&eacute;es</div>
		<div class="mail">
			<div class="haut"></div>
			<div class="ficheProduit">
				<div class="coord">
					{$data['CUSTOMER_COMPANY_NAME']}<br/>
					{$data['CUSTOMER_COMPANY_ADDRESS']} {$data['CUSTOMER_COMPANY_COMPLEMENT']}<br/>
					{$data['CUSTOMER_COMPANY_PC']} {$data['CUSTOMER_COMPANY_CITY']}<br/>
					{$data['CUSTOMER_COMPANY_COUNTRY']}<br/>
				</div>
			</div>
			<div class="miseAZero"></div>
			<div class="bas"></div>
		</div>
		<p><br/></p>
		<div class="titreBloc">Informations compl&eacute;mentaires</div>
		<div class="mail">
			<div class="haut"></div>
			<div class="ficheProduit">
				<div class="sousTitreBloc">Contact : <span class="coord2">{$data['CUSTOMER_LASTNAME']} {$data['CUSTOMER_FIRSTNAME']}</span></div>
				<div class="sousTitreBloc">Fonction : <span class="coord2">{$data['CUSTOMER_JOB']}</span></div>
				<div class="sousTitreBloc">Téléphone : <span class="coord2">{$data['CUSTOMER_PHONE']}</span></div>
				<div class="sousTitreBloc">Fax : <span class="coord2">{$data['CUSTOMER_FAX']}</span></div>
				<div class="sousTitreBloc">E-mail : <span class="coord2">{$data['CUSTOMER_EMAIL']}</span></div>
				<br/>
				<div class="sousTitreBloc">Taille salariale : <span class="coord2">{$data['CUSTOMER_COMPANY_WORKFORCE']}</span></div>
				<div class="sousTitreBloc">Site : <span class="coord2">{$data['CUSTOMER_COMPANY_URL']}</span></div>
				<div class="sousTitreBloc">Secteur d'activit&eacute; : <span class="coord2">{$data['CUSTOMER_COMPANY_SECTOR']}</span></div>
				<div class="sousTitreBloc">Code Naf : <span class="coord2">{$data['CUSTOMER_COMPANY_NAF']}</span></div>
				<div class="sousTitreBloc">n° Siret : <span class="coord2">{$data['CUSTOMER_COMPANY_SIREN']}</span></div>
			</div>
			<div class="miseAZero"></div>
			<div class="bas"></div>
		</div>
		<p><br/></p>
		{$data['MAINPRODUCT_PRECISIONS']}
		
		<div class="titreBloc">Fiche du produit {$data['MAINPRODUCT_NAME']}</div>
		<div class="mail">
			<div class="haut"></div>
			<div class="ficheProduit">
				<img src="{$data['MAINPRODUCT_IMAGE']}" alt=""/>
				<div class="sousTitreBloc">Description :</div>
				<div class="contenuBloc">
					{$data['MAINPRODUCT_DESCRIPTION']}
				</div>
				{$data['MAINPRODUCT_DETAILEDDESCRIPTION']}
				<div class="sousTitreBloc">Prix / Références :</div>
				<div class="contenuBloc">
					{$data['MAINPRODUCT_PRICEDATA']}
				</div>
			</div>
			<div class="miseAZero"></div>
			<div class="bas"></div>
		</div>
	</div>
	<div class="miseazero"></div>
</div>
EOF;
?>