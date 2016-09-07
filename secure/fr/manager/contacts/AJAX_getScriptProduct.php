<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 01 juillet 2011

 Fichier : /secure/fr/manager/contacts/AJAX_getScriptProduct.php
 Description : récupère le script d'info concernant un produit

/=================================================================*/

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

require_once(ADMIN."logs.php");

$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

if (!$user->login()) {
  $o["error"] = "Votre session a expir&eacute;, veuillez vous identifier &agrave; nouveau apr&egrave;s avoir rafra&icirc;chi votre page";
  print json_encode($o);
  exit();
}

// usefull functionalities index ny name
$fntl_tmp = BOFunctionality::get("name, id");
$fntByName = array();
foreach($fntl_tmp as $fnt)
  $fntByName[$fnt["name"]] = $fnt["id"];

$userPerms = $user->get_permissions();

if (!$userPerms->has($fntByName["m-smpo--sm-script-product"], "r")){
  $errorstring .= 'Vous n\'avez pas les droits nécessaires';
}

if(!empty ($_GET['idProduct'])){

  $products = ProductsOld::get('id = '.$_GET['idProduct'], 'inner join products_fr on id = products.id', 'where products_fr.deleted != 1');
  
  foreach ($products as $cle => &$product){
      $linkFamily = ProductsFamiliesOld::get('idProduct = '.$product['id']);
      $product['idFamily3'] = count($linkFamily) == 1 ? $linkFamily[0]['idFamily'] : '';
      if($product['idFamily3']){
        $parentFamily3 = FamiliesOld::getFamilyParent($product['idFamily3']);
        $product['idFamily2'] = !empty($parentFamily3) ? $parentFamily3 : '';
      }
    }

  if(!empty ($products[0]['idFamily3'])){
    $scripts = ScriptProduct::get(array('id_relation = '.$products[0]['idFamily2'], 'type_relation = 1'));
    if(!empty ($scripts)) $listeScripts[] = $scripts[0];
    $scripts = ScriptProduct::get(array('id_relation = '.$products[0]['idFamily3'], 'type_relation = 2'));
    if(!empty ($scripts)) $listeScripts[] = $scripts[0];
    $scripts = ScriptProduct::get(array('id_relation = '.$products[0]['idAdvertiser'], 'type_relation = 3'));
    if(!empty ($scripts)) $listeScripts[] = $scripts[0];
  }

  if(!empty ($listeScripts)){
    foreach ($listeScripts as $cle => $script)
      // Script partenaire prioritaire sur famille 3, Script famille 3 prioritaire sur famille 2
      switch($script['type_relation']){
        case 3 :
          if(!empty ($script))
            $retour = $script;
          break;
        case 2 :
          if(!empty ($script))
            $retour = $script;
          break;
        case 1 :
          if(!empty ($script))
            $retour = $script;
          break;
      }

    $reponses = !empty ($retour) ? $retour['content'] : 'vide';

  }else
    $reponses = 'vide';	

}else
  $reponses = 'vide';


$o['reponse'] = $reponses;
mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);


?>
