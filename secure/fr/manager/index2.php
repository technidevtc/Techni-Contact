<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Mises à jour :

       31 mai 2005 : = réécriture avec nouveau système des rangs

 Fichier : /secure/manager/index.php
 Description : Accueil administration de l'application Web

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$title  = 'Accueil';
$navBar = '<div align="center">MANAGER DE VOTRE SITE WEB TECHNI CONTACT</div>';

define('MD2I_ADMIN', true);

require(ADMIN . 'head.php');


if($result = & $handle->query('select count(id) from advertisers', __FILE__, __LINE__))
{
    $row           = & $handle->fetch($result);
    $nbAdvertisers = & $row[0];
}
else
{
    $nbAdvertisers = '-';
}


if($result = & $handle->query('select count(id) from products_fr where active = 1', __FILE__, __LINE__))
{
    $row        = & $handle->fetch($result);
    $nbProducts = & $row[0];
}
else
{
    $nbProducts = '-';
}
  

if($user->rank == HOOK_NETWORK || $user->rank == COMMADMIN)
{
    $logs = & last15($handle);
}

if($user->rank != CONTRIB)
{
    if($result = & $handle->query('select id from products_add where type = \'c\'', __FILE__, __LINE__))
    {

        if(($nb = $handle->numrows($result, __FILE__, __LINE__)) > 0)
        {
            $add = '<br /> - <a href="products/add_wait.php?' . $sid . '">' . $nb . ' en attente de validation de création</a>';
        }
    }
    


    if($result = & $handle->query('select id from products_add where type = \'m\'', __FILE__, __LINE__))
    {

        if(($nb = $handle->numrows($result, __FILE__, __LINE__)) > 0)
        {
            $mod = '<br /> - <a href="products/edit_wait.php?' . $sid . '">' . $nb . ' en attente de validation de modification</a>';
        }
    }


    $extranet = '';
    $from_extranet = false;
    if($result = & $handle->query('select id from products_add_adv where type = \'c\' and reject = 0', __FILE__, __LINE__))
    {

        if(($nb = $handle->numrows($result, __FILE__, __LINE__)) > 0)
        {
            $from_extranet = true;
            $extranet = '<br /> - Extranet : <a href="products/add_wait.php?' . $sid . '&from=adv">' . $nb . ' en attente de validation de création</a>';
        }
    }
    
    if($result = & $handle->query('select id from products_add_adv where type = \'m\' and reject = 0', __FILE__, __LINE__))
    {

        if(($nb = $handle->numrows($result, __FILE__, __LINE__)) > 0)
        {
            if(!$from_extranet)
            {
//                $extranet .= '<br>Extranet :';
                $from_extranet = true;
            }

            $extranet .= '<br /> - Extranet : <a href="products/edit_wait.php?' . $sid . '&from=adv">' . $nb . ' en attente de validation de modification</a>';
        }
    }


    if($result = & $handle->query('select id from sup_requests', __FILE__, __LINE__))
    {

        if(($nb = $handle->numrows($result, __FILE__, __LINE__)) > 0)
        {
            if(!$from_extranet)
            {
  //              $extranet .= '<br>Extranet :';
            }

            $extranet .= '<br /> - Extranet : <a href="products/sup_wait.php?' . $sid . '">' . $nb . ' en attente de validation de suppression</a>';
        }
    }
	
	$extranet_a = '';
    $from_extranet_a = false;
    if($result = & $handle->query('select id from advertisers_adv', __FILE__, __LINE__))
    {

        if(($nb = $handle->numrows($result, __FILE__, __LINE__)) > 0)
        {
            $from_extranet_a = true;
            $extranet_a = '<br /> - Extranet : <a href="advertisers/edit_wait.php?' . $sid . '&from=adv">' . $nb . ' en attente de validation de modification</a>';
        }
    }
}

if(!isset($add)){ $add = ''; }
if(!isset($mod)){ $mod = ''; }
if(!isset($del)){ $del = ''; }

?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="subtitle">
  <tr>
    <td><img src="images/fr.gif" alt="français" border="0"/></td>
    <td><a href="<?php echo URL ?>" target="_blank">Accès au site</a></td>
    <td><a href="<?php echo EXTRANET_URL . '/login2.html' ?>" target="_blank">Accès à l'extranet</a></td>
    <td><a href="http://www.xiti.com/fr/Login.aspx" target="_blank">XiTi</a></td>
    <td><a href="users/edit.php?<?php echo $sid ?>">Mon compte</a></td>
    <td><a href="logout.php?<?php echo $sid ?>">Déconnexion</a></td>
    <td><a href="../uk/manager"><img src="images/uk.gif" alt="english" border="0"/></a> <a href="../de/manager"><img src="images/de.gif" alt="deutsch" width="24" height="15" border="0"/></a> <a href="../es/manager"><img src="images/es.gif" alt="espaniol" width="24" height="15" border="0"/></a> <a href="../it/manager"><img src="images/it.gif" alt="italiano" width="24" height="15" border="0"/></a></td>
  </tr>
</table>
<br />
<br />
<?php

if($user->rank == HOOK_NETWORK)
{
    include(ADMIN . 'hook.php');
?>
<div class="titreRestricted">Zone d'action pour les techniciens</div>
<br />
<div class="bg">
<?php
    if(isset($_GET['action']))
    {
        switch($_GET['action'])
        {
			case RESET_SQL_LOG :
				if(resetLog(SQL_LOG_FILE)) { print('<div class="confirm">Fichier ' . SQL_LOG_FILE . ' remis à zéro.</div>'); }
				else { print('<div class="error">Erreur lors de la remise à zéro du fichier ' . SQL_LOG_FILE . '.</div>'); }
				break;
				
			case CAT_SQL_LOG :
				if(!catLog(SQL_LOG_FILE)) { print('<div class="error">Fichier Log ' . SQL_LOG_FILE . ' vide.</div>'); }
				break;
				
			case RESET_PHP_LOG :
				if(resetLog(PHP_LOG_FILE)) { print('<div class="confirm">Fichier ' . PHP_LOG_FILE . ' remis à zéro.</div>'); }
				else { print('<div class="error">Erreur lors de la remise à zéro du fichier ' . PHP_LOG_FILE . '.</div>'); }
				break;
				
			case CAT_PHP_LOG :
				if(!catLog(PHP_LOG_FILE)) { print('<div class="error">Fichier ' . PHP_LOG_FILE . ' vide.</div>'); }
				break;
			
			default :
				print('<div class="error">Action inconnue.</div>');
		}
        print('<br /><br />');
    } // fin isset action
?>
    <ul>
		<li><a href="index.php?action=<?php echo CAT_SQL_LOG . '&' . $sid ?>">Afficher le Log SQL</a> / <a href="index.php?action=<?php echo RESET_SQL_LOG . '&' . $sid ?>">Remettre à zéro le Log SQL</a></li>
		<li><a href="index.php?action=<?php echo CAT_PHP_LOG . '&' . $sid ?>">Afficher le Log PHP</a> / <a href="index.php?action=<?php echo RESET_PHP_LOG . '&' . $sid ?>">Remettre à zéro le Log PHP</a></li>
		<li><a href="hook.php?<?php echo $sid ?>&action=sql">Optimisation des bases SQL</a></li>
		<li><a href="hook.php?<?php echo $sid ?>&action=idr">Intégrité des relations</a></li>
	</ul>
</div>
<br />
<br />
<?php
}  // fin droits gestion configuration
?>
<div class="titreStandard">Gestion Commerciale </div>
<br />
<div class="bg"><br />
	<table border="0" align="center" cellpadding="2" cellspacing="0">
		<tr><td colspan="4" style="background-color: #666; font-weight: bold; color: white; border: solid 1px black"><div align="center">Business g&eacute;n&eacute;r&eacute; </div></td></tr>
		<tr>
			<td><a href="commandes/?<?php echo $sid ?>"><img src="ressources/commandes.gif" border="0" /></a></td>
			<td><a href="devis/?<?php echo $sid ?>"><img src="ressources/devis.gif" border="0" /></a></td>
			<td><a href="contacts/?<?php echo $sid ?>"><img src="ressources/contacts.gif" border="0" /></a></td>
			<td><a href="stats/?<?php echo $sid ?>"><img src="ressources/stats.gif" width="174" height="47" border="0" /></a></td>
		</tr>
	</table>
	<br />
	<table border="0" align="center" cellpadding="2" cellspacing="0">
		<tr><td colspan="2" style="background-color: #666; font-weight: bold; color: white; border: solid 1px black"><div align="center">Recherche</div></td></tr>
		<tr>
			<td><a href="clients/?<?php echo $sid ?>"><img src="ressources/clients.gif" border="0" /></a></td>
			<td><a href="products/find.php?<?php echo $sid ?>"><img src="ressources/produit.gif" border="0" /></a></td>
		</tr>
	</table>
</div>
<br />
<div class="bg"><br />
	<table border="0" align="center" cellpadding="5" cellspacing="3">
		<tr><td colspan="3" style="background-color: #666; font-weight: bold; color: white; border: solid 1px black"><div align="center">Gestion - Divers </div></td></tr>
		<tr>
			<td style="background-color: white; width: 175px; border: solid 1px black"><a href="products/ProductsFlagship.php?<?php echo $sid ?>">Gestion des produits phares</a></td>
			<td style="background-color: white; width: 175px; border: solid 1px black"><a href="tva/?<?php echo $sid ?>">Gestion des Taux de TVA</a></td>
			<td style="background-color: white; width: 175px; border: solid 1px black"><a href="config/?<?php echo $sid ?>">Options  par d&eacute;faut</a></td>
		</tr>
	</table>
	<br />
	<table border="0" align="center" cellpadding="5" cellspacing="3">
		<tr><td colspan="3" style="background-color: #666; font-weight: bold; color: white; border: solid 1px black"><div align="center">Demandes de catalogues </div></td></tr>
		<tr>
			<td style="background-color: white; width: 175px; border: solid 1px black"><a href="catalogues/?<?php echo $sid ?>">Derni&egrave;re &eacute;dition</a></td>
			<td style="background-color: white; width: 175px; border: solid 1px black"><a href="demandes/?<?php echo $sid ?>">Prochaine &eacute;dition</a></td>
			<td style="background-color: white; width: 175px; border: solid 1px black"><a href="demandes/s.php?<?php echo $sid ?>">Filtrage concurrence </a></td>
		</tr>
	</table>
	<br />
	<table border="0" align="center" cellpadding="5" cellspacing="3">
		<tr><td style="background-color: #666; width: 555px; font-weight: bold; color: white; border: solid 1px black"><div align="center">Demandes de contact </div></td></tr>
		<tr><td style="background-color: white; border: solid 1px black"><a href="export/contacts.php?<?php echo $sid ?>">Export des demandes de contact</a></td></tr>
	</table>
</div>
<br />
<div class="titreStandard">Gestion Editoriale </div>
<br />
<div class="bg">
	<br />
	<table border="0" align="center" cellpadding="5" cellspacing="3">
		<tr><td colspan="2" style="background-color: #666; font-weight: bold; color: white; border: solid 1px black"><div align="center">R&eacute;f&eacute;rentiel</div></td></tr>
		<tr>
			<td style="background-color: white; width: 270px; border: solid 1px black"><a href="advertisers/?<?php echo $sid ?>">Annonceurs et fournisseurs</a>
				<?php echo '(' . $nbAdvertisers . ') ' . $extranet_a ?>
			</td>
			<td style="background-color: white; width: 270px; border: solid 1px black"><a href="families/?<?php echo $sid ?>">Familles et sous-familles </a></td>
		</tr>
	</table>
	<br />
	<table border="0" align="center" cellpadding="5" cellspacing="3">
		<tr><td style="background-color: #666; font-weight: bold; color: white; border: solid 1px black"><div align="center">Fiches produits</div></td></tr>
		<tr>
			<td style="background-color: white; width: 555px; border: solid 1px black">
				<ul style="margin-top: 5px; margin-bottom: 5px">
					<li><a href="products/?<?php echo $sid ?>">Base de donn&eacute;es des produits</a>
					<?php echo '(' . $nbProducts . ') &nbsp; ' . $add .' &nbsp; '. $mod .' &nbsp; '. $del . $extranet ?>
					<br />
					<br />
					</li>
					<li><a href="products/find.php?<?php echo $sid ?>">Trouver un produit</a></li>
				</ul>
			</td>
		</tr>
	</table>
	<br />
	<table border="0" align="center" cellpadding="5" cellspacing="3">
		<tr><td style="background-color: #666; font-weight: bold; color: white; border: solid 1px black"><div align="center">Imports/Export</div></td></tr>
		<tr>
			<td style="background-color: white; width: 555px; border: solid 1px black">
				<ul style="margin-top: 5px; margin-bottom: 5px">
					<li><a href="import/imports.php?<?php echo $sid ?>">Module d'importation</a></li>
					<!--<li><a href="products/find.php?<?php echo $sid ?>">Module d'exportation</a></li>-->
				</ul>
			</td>
		</tr>
	</table>
  </div><br />
<div class="bg">
	<ul style="margin-top: 5px; margin-bottom: 5px">
		<li>(ancien module) <a href="stats/old/?<?php echo $sid ?>">Pages vues produits</a></li>
		<li>(ancien module) <a href="newsletter/?<?php echo $sid ?>">Lettre d'information</a></li>
	</ul>
</div>
<?php

if($user->rank == HOOK_NETWORK || $user->rank == COMMADMIN)
{
    // Administrateur
?>
    <br><br><div class="titreRestricted">Administration de l'application</div><br><div class="bg">
    <ul><li> <a href="users/?<?php echo $sid ?>">Base de données des utilisateurs de l\'application</a><br>
    <li> <a href="files/?<?php echo $sid ?>">Edition des fichiers divers</a><br>
    <li> <a href="files/cat.php?<?php echo $sid ?>">Couvertures / Exemplaires / Dates de sortie des catalogues</a><br>
    <li> <a href="files/bibli.php?<?php echo $sid ?>">Bibliothèque d\'images</a><br>
    <li> <a href="google.php?<?php echo $sid ?>">Référencement Google</a><br>
    <li> <a href="extranetinfo.php?<?php echo $sid ?>">Utilisation annonceurs / extranet</a><br>
    </ul>
    
	Les 15 dernières actions :<br><br><table border=1 cellspacing=1 cellpadding=1>
    <tr><td class="intitule"><font size="1"><center>Date</center></font></td><td class="intitule"><font size="1"><center>Session</center></font></td><td class="intitule"><font size="1"><center>Action</center></font></td></tr>
<?php
    for($i = 0; $i < count($logs); ++$i)
    {
            print('<tr><td class="intitule"><font size="1"><center>'.date('d/m/Y H:i:s', $logs[$i][0]).'</center></font></td><td class="intitule"><font size="1"><center>' . to_entities($logs[$i][1]) . '</center></font></td><td class="intitule"><font size="1"><center>' . to_entities(substr($logs[$i][2], 0, 100)) . ' ...</center></font></td></tr>');
    }

    print('</table>');
    
    $logsTable = array('logs' => '', 'elogs' => 'extranet');

    foreach($logsTable as $k => $v)
    {
        print('<br><form method="post" action="' . $k . '.php?' . session_name() . '=' . session_id() . '">Voir toutes les actions ' . $v . ' du <select name="d">');
        for($i = 1; $i <= 31; ++$i)
        {
            $sel = ($i == date('d')) ? 'selected' : '';
            print('<option value="'.$i.'" '.$sel.'>'.$i.'</option>');
        }

        print('</select> <select name="m">');
        for($i = 1; $i <= 12; ++$i)
        {
            $sel = ($i == date('m')) ? 'selected' : '';
            print('<option value="'.$i.'" '.$sel.'>'.$i.'</option>');
        }

        print('</select> <select name="y">');
        for($i = 2005; $i <= date('Y'); ++$i)
        {
            $sel = ($i == date('Y')) ? 'selected' : '';
            print('<option value="'.$i.'" '.$sel.'>'.$i.'</option>');
        }

        print('</select> <input type="button" value="Go" onClick="this.form.submit(); this.disabled = true"></form>');
    }

    print('</div><br><br>');

}


require(ADMIN . 'tail.php');

?>
