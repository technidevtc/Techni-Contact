<?php
$this->content .= <<< EOF
<div>
	<div>
		<a title="page d'accueil" href="{$constant['URL']}">
			<img id="logo" alt="logo techni contact" src="{$constant['URL']}ressources/images/logo.jpg" nosend="1"/>
		</a>
	</div>
	<div class="intro">
		<p>Cher Utilisateur TECHNI-CONTACT,<br/>
		<br/>
		Voici les références des sociétés qui fabriquent des produits similaires et qui pourraient répondre à vos besoins.
		<br/>
		<br/>
		L'équipe TECHNI-CONTACT
		</p>
		{$data['LINKEDADVERTISER_LIST']}
	</div>
	<div class="miseazero"></div>
</div>
EOF;
?>