<?php
/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 04/07/2011 OD

 Fichier : /secure/fr/manager/ressources/ajax/AJAX_codesPostaux.php
 Description : requete ajax de code postal pour renseignement liste des communes

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

header("Content-Type: text/plain; charset=utf-8");

$o = array();

if(preg_match('/[0-9]{5}/', $_GET['code_postal'])){

  $listeCodesPostaux = CodesPostaux::get('code_postal = '.$_GET['code_postal']);

  $o['reponses'] = $listeCodesPostaux;
  
}


mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
  print json_encode($o);

?>
