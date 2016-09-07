<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 11 juin 2005

 Fichier : /secure/manager/files/preview.php
 Description : Prévisualisation édition fichiers divers

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN."logs.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

$files = array(
	"nous" => "Société (qui sommes nous)",
	"catalogues" => "Nos catalogues",
	"contact" => "Contactez nous",
	"aide" => "Aide",
	"premiere-visite" => "Première visite",
	"cgv" => "CGV",
	"infos-legales" => "Mentions légales",
	"recrutement" => "Recrutement");

if ($user->login() && isset($_GET['file']) && isset($files[$_GET["file"]])) {
	$filename = $_GET["file"];
	$file = WWW_PATH.$filename.".html";
	define("PREVIEW", true);
	include($file);
}

?>
