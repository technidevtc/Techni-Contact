<?php

/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de cr�ation : 13 f�vrier 2006

 Fichier : /secure/manager/families/FamiliesSearch.php
 Description : Fichier interface de recherche des familles AJAX

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ICLASS . "CUserSession.php");
require(ICLASS . "CCart.php");

$handle = DBHandle::get_instance();
$session = & new UserSession($handle);
$cart = & new Cart($handle, $session->getID());

if ($cart->itemCount == 0) {
	print COMMON_AJAX_COMMENTS_CARD_EMPTY . __ERROR_SEPARATOR__ . __MAIN_SEPARATOR__;
	exit();
}

if (!$session->logged){
	print COMMON_AJAX_COMMENTS_NOT_IDENTIFIED . __ERROR_SEPARATOR__ . __MAIN_SEPARATOR__;
	exit();
}

//include(LANG_LOCAL_INC . "includes-" . DB_LANGUAGE . "_local.php");
//include(LANG_LOCAL_INC . "command-" . DB_LANGUAGE . "_local.php");
include(LANG_LOCAL_INC . "common-" . DB_LANGUAGE . "_local.php");
//include(LANG_LOCAL_INC . "infos-" . DB_LANGUAGE . "_local.php");

header("Content-Type: text/html; charset=utf-8");

function rawurldecodeEuro ($str) { return str_replace("%u20AC", "�", rawurldecode($str)); }

/*
ProductsManagement.php?action=updatecomment&idTC=13513357&comment=Commentaire%20de%20test
*/
$es = $os = '';

if (isset($_GET['action'])) {
	switch ($_GET['action']) {
		//ProductsManagement.php?action=updatecomment&idTC=13513357&comment=Commentaire%20de%20test
		case "updatecomment" :
			$os .= "updatecomment" . __OUTPUT_SEPARATOR__;
			if (isset($_GET['idTC'])) {
				settype($_GET['idTC'], "integer");
				if (isset($_GET['comment'])) {
					$cart->updateProductComment($_GET['idTC'], rawurldecodeEuro($_GET['comment']));
					$os .=  "OK" . __OUTPUT_SEPARATOR__;
				}
				else $es .= COMMON_AJAX_COMMENTS_ERROR_EMPTY . __ERROR_SEPARATOR__;
			}
			else $es .= COMMON_AJAX_COMMENTS_ERROR_IDTC . __ERROR_SEPARATOR__;
			break;
		
		default : break;
	}
}

print $es . __MAIN_SEPARATOR__ . $os;

exit();

?>
