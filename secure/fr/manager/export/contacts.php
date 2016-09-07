<?php

/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 15 février 2007

 Mises à jour :

 Fichier : /secure/manager/stats/index.php
 Description : Index des statistiques
 
/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN . 'head.php');

?>
<div class="titreStandard">Export des clients des bases demande de contact et demande de catalogue</div>
<br>
<div class="bg">
<form action="contacts_export.php" method="post" style="width: 400px">
	<div>
		<fieldset>
			<legend>Interval de temps des clients à exporter</legend>
			<!--<input type="radio" name="mod_export" value="all" />Toute la base<br />-->
			<br />
			<input type="radio" name="mod_export" value="date" checked="checked" />
			Du <input type="text" name="DateBegin" value="<?php echo date("d/m/Y", mktime(0,0,0,date("m")-1, date("d"), date("Y"))) ?>" />
			au <input type="text" name="DateEnd" value="<?php echo date("d/m/Y") ?>" />
		</fieldset>
		<br />
		<input type="submit" class="bouton" value="Télécharger le xls" />
	</div>
</form>
</div>
<?php

require(ADMIN . 'tail.php');

?>
