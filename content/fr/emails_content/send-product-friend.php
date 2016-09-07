<?php
$this->content .= <<< EOF
<div>
	<div>
		<a title="Page d'accueil" href="{$constant['URL']}">
			<img id="logo" alt="Logo Techni Contact" src="{$constant['URL']}images/logo.jpg" nosend="1"/>
		</a>
	</div>
	<div class="intro">
		<p>
			Cher Utilisateur TECHNI-CONTACT,<br/>
			Votre ami ayant l'adresse email {$data['User-Mail']} nous a demandé de vous transmettre la fiche du produit ci-dessous.<br/>
			<br/>
			En espérant bientôt vous revoir sur notre site,<br/>
			<br/>
			L'équipe TECHNI-CONTACT
		</p>
	</div>
	<div class="miseAZero"></div>
	
	<div id="hook">
		<div class="mail">
			<div class="titreBloc">Fiche du produit {$data['Product-Name']}</div>
			<div class="haut"></div>
			<div class="ficheProduit"><a href="{$data['Product-URL']}?utm_source=Bloc-viral&utm_medium=email&utm_campaign=Bloc-viral">Cliquez ici pour visualiser le produit en ligne</a></div>
			<div class="miseAZero"></div>
			<div class="bas"></div>
		</div>
	</div>
	
	<div class="miseazero"></div>
	<img width="1" height="1" alt="" src="http://logi7.xiti.com/hit.xiti?s=157945&s2=10&p=bloc_viral"/>
</div>
EOF;
?>