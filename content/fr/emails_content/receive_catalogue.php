<?php
$this->content .= <<< EOF
	<div class="mail_body">
		<p>Bonjour,</p>
		<p>
			Une demande de catalogue a été effectuée le {$data['InscriptionDate']} par l'adresse {$data['InscriptionIP']} :</p>
			<table cellspacing="0" cellpadding="0">
				<tr><td>Nom : </td><td><b>{$data['LastName']}</b></td></tr>
				<tr><td>Prénom : </td><td><b>{$data['FirstName']}</b></td></tr>
				<tr><td>Fonction : </td><td><b>{$data['Job']}</b></td></tr>
				<tr><td>Téléphone : </td><td><b>{$data['Phone']}</b></td></tr>
				<tr><td>Fax : </td><td><b>{$data['Fax']}</b></td></tr>
				<tr><td>Email : </td><td><b>{$data['Email']}</b></td></tr>
				<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr><td>Société : </td><td><b>{$data['Company']}</b></td></tr>
				<tr><td>Nb de salariés : </td><td><b>{$data['WorkForce']}</b></td></tr>
				<tr><td>Site Internet : </td><td><b>{$data['URL']}</b></td></tr>
				<tr><td>Secteur d'activité : </td><td><b>{$data['ActivitySector']}</b></td></tr>
				<tr><td>Code NAF : </td><td><b>{$data['NafCode']}</b></td></tr>
				<tr><td>Siret : </td><td><b>{$data['SirenNumber']}</b></td></tr>
				<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr><td>Adresse : </td><td><b>{$data['Address']}</b></td></tr>
				<tr><td>Complément adresse : </td><td><b>{$data['Complement']}</b></td></tr>
				<tr><td>Code Postal : </td><td><b>{$data['PC']}</b></td></tr>
				<tr><td>Ville : </td><td><b>{$data['City']}</b></td></tr>
				<tr><td>Pays : </td><td><b>{$data['Country']}</b></td></tr>
				<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr><td>Description des produits : </td><td><b>{$data['Products']}</b></td></tr>
				<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr><td>Catalogues que l'utilisateur souhaite recevoir :</td><td><b>{$data['CatalogueList']}</b></td></tr>
				<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr><td>L'utilisateur souhaite :</td><td><b>{$data['WhichCatalogueEdition']}</b></td></tr>
			</table>
		</p>
	</div>
EOF;
?>