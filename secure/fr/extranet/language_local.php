<?php
/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 18 septembre 2007

 Fichier : /secure/extranet/language_local.php
 Description : Fichier de choix de la langue

/=================================================================*/

$language_list = array(
	'de' => 'deutsch',
	'es' => 'espaniol',
	'fr' => 'francais',
	'it' => 'italiano',
	'uk' => 'english'
);

$language_local = isset($_COOKIE['language_local']) ? $_COOKIE['language_local'] : 'fr';

if (!isset($language_list[$language_local])){
	$language_local = 'uk';
	setCookie('language_local', $language_local, time() + 24 * 3600 * 365, '/', 'techni-contact.com');
}

include($language_local.'_local.php');

?>
