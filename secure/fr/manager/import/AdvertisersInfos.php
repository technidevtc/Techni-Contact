<?php

/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 13 février 2006

 Fichier : /secure/manager/families/FamiliesSearch.php
 Description : Fichier interface de recherche des familles AJAX

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN."logs.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

//header("Content-Type: text/plain; charset=iso-8859-1");
header("Content-Type: text/plain; charset=utf-8");

if(!$user->login())
{
	print "Votre session a expirée, veuillez réactualiser la page pour retourner à la page de login" . __MAIN_SEPARATOR__;
	exit();
}

$es = $os = '';
$id = isset($_GET['id']) ? trim($_GET['id']) : '';
if ($id != '')
{
	if ($result = $handle->query("select id, nom1, adresse1, adresse2, ville, cp, pays, tel1, tel2, fax1, fax2, email, parent from advertisers where id = " . $id, __FILE__, __LINE__, false))
	{
		$advInfos = & $handle->fetchAssoc($result);
		$os .= "<i>" . ($advInfos['parent'] == __ID_TECHNI_CONTACT__ || id == __ID_TECHNI_CONTACT__ ? "Fournisseur" : "Annonceur") . "</i> (" . $advInfos['id'] . ")<br />\n";
		$os .= "<br/>\n";
		$os .= "<b>" . $advInfos['nom1'] . "</b><br/>\n";
		$os .= $advInfos['adresse1'] . "<br/>\n";
		$os .= $advInfos['adresse2'] . "<br/>\n";
		$os .= $advInfos['cp'] . " " . $advInfos['ville'] . "<br/>\n";
		$os .= $advInfos['pays'] . "<br/>\n";
		$os .= "<br/>\n";
		$os .= "<b>tel 1:</b> " . $advInfos['tel1'] . "<br/>\n";
		$os .= "<b>tel 2:</b> " . $advInfos['tel2'] . "<br/>\n";
		$os .= "<b>fax 1:</b> " . $advInfos['fax1'] . "<br/>\n";
		$os .= "<b>fax 2:</b> " . $advInfos['fax2'] . "<br/>\n";
		$os .= "<b>email :</b> " . $advInfos['email'] . "<br/>\n";
		
		/*
		foreach($advInfos as $field => $value)
		{
			$os .= $field . __OUTPUTID_SEPARATOR__ . $value . __OUTPUT_SEPARATOR__;
		}
		*/
	}
	else $es .= "Erreur fatale lors de l'acquisition des informations de l'annonceur ayant pour ID " . $id;
}
else $es .= "Veuillez spécifier une ID d'annonceur ou de fournisseur à rechercher";

print $es . __MAIN_SEPARATOR__ . $os;

exit();

?>
