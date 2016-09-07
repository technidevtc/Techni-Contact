<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 23 mars 2011

 Fichier : /secure/fr/manager/smpo/AJAX_scripts-product.php
 Description : résultat ajax du listing des scripts produits

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

if (!$user->login()) {
  $o["error"] = "Votre session a expirÃ©, veuillez vous identifier Ã  nouveau aprÃ¨s avoir rafraichi votre page";
  print json_encode($o);
  exit();
}

$errorstring = '';

$userPerms = $user->get_permissions();

// usefull functionalities index ny name
$fntl_tmp = BOFunctionality::get("name, id");
$fntByName = array();
foreach($fntl_tmp as $fnt)
  $fntByName[$fnt["name"]] = $fnt["id"];

if (!$userPerms->has($fntByName["m-smpo--sm-script-product"], "re")){
  $errorstring .= 'Vous n\'avez pas les droits nécessaires';
}

require_once(ADMIN."logs.php");
require_once(ADMIN."logo.php");

$handle = DBHandle::get_instance();

$db = DBHandle::get_instance();
$o = array();

$bindTypeList = ScriptProduct::getRelationTypeList();

if(!empty ($_POST['idRelation']) && !empty ($_POST['typeRelation']) && $_POST['action'] == 'delete'){
  $script = ScriptProduct::get(array('id_relation = '.$_POST['idRelation'], 'type_relation = '.$_POST['typeRelation']));

  if(!empty ($script[0]['timestamp'])){
    if (!$userPerms->has($fntByName["m-smpo--sm-script-product"], "d")){
      $errorstring .= 'Vous n\'avez pas les droits nécessaires';
    }elseif(!ScriptProduct::delete($_POST['idRelation'], $_POST['typeRelation']))
      $errorstring = 'La suppression a échoué';
  }  else {
    $errorstring = 'Script introuvable';
  }
}



$idRelation = isset($_GET['idRelation'])     ? trim($_GET['idRelation']) : '';
$typeRelation = isset($_GET['typeRelation'])     ? trim($_GET['typeRelation']) : '';



if(!$errorstring){

  $args = '';
  if(!empty ( $idRelation))
     $args[] = 'id_relation = '.$idRelation;//, 'type_relation = '.$typeRelation);

  if(!empty ($typeRelation ))
    $args[] = 'type_relation = '.$typeRelation;

  $scriptList = ScriptProduct::get($args);
  
  if(!empty ($scriptList)){
    foreach ($scriptList as $cle => $script){

      $scriptList[$cle]['relation_name'] = $bindTypeList[$script['type_relation']];

      switch ($script['type_relation']){
        case 1: // famille 2
        case 2: // famille 3
          // getting name
          $family = FamiliesOld::getFamilyInfo($script['id_relation']);

          $scriptList[$cle]['family_name'] = !empty ($family['name']) ? $family['name'] : 'Famille inconnue';

          // getting number of products
          $products = array();
          $products[] = ProductsOld::getProductsNumbersFromFamily($script['id_relation']);

          if($script['type_relation'] == 1)
            $familyChildren = FamiliesOld::getChildren($script['id_relation']);


          if($familyChildren)
            foreach($familyChildren as $familyChild)
              $products[] = ProductsOld::getProductsNumbersFromFamily($familyChild['id']);

          $scriptList[$cle]['nb_products'] = array_sum($products);

          break;

        case 3: // partenaire
          // getting name
            $query1 = 'select nom1 from advertisers WHERE id = '.$db->escape($script['id_relation']);
            $res = $db->query($query1);
            $ret =  $db->fetchAssoc($res);
            $scriptList[$cle]['family_name'] = !empty ($ret['nom1']) ? $ret['nom1'] : 'Partenaire inconnu';

          // getting number of products
          $products = array();
          $products[] = ProductsOld::getProductsNumbersFromAdvertiser($script['id_relation']);

          $scriptList[$cle]['nb_products'] = array_sum($products);

          break;

      }
    }

    $o['reponses']  = $scriptList;
  
      // ordre de tri et pagination
    $page	= isset($_GET['page'])	? (int) trim($_GET['page']) : 1; if ($page < 1) $page = 1;
    $formerpage     = isset($_GET['formerpage']) ? (int) trim($_GET['formerpage']) : '';
    $sort     = isset($_GET['sort'])     ? trim($_GET['sort']) : '';
    $lastsort = isset($_GET['lastsort']) ? trim($_GET['lastsort']) : '';
    $sortway  = isset($_GET['sortway'])  ? trim($_GET['sortway']) : '';
    $NB = !empty($_GET['NB']) && is_numeric($_GET['NB']) ? (int) $_GET['NB'] : 30;
    define('NB', $NB);

    $argsQuery = array();

    if ($sort == $lastsort && $sort != '')
    {
            if ($formerpage == $page) $sortway = $sortway == 'asc' ? 'desc' : 'asc';
            else $sortway = ($sortway == 'asc' ? 'asc' : 'desc');
    }
    else $sortway = 'asc';

  $switchSortway = $sortway == 'asc' ? SORT_ASC : SORT_DESC;


    foreach($o['reponses'] as $key => $value){
      $timestamp[$key] = $value['timestamp'];
      $relation_name[$key] = $value['relation_name'];
      $family_name[$key] = $value['family_name'];
      $id_relation[$key] = $value['id_relation'];
      $nb_products[$key] = $value['nb_products'];
    }

    switch ($sort)
    {

            case 'date'   : array_multisort($timestamp, $switchSortway, $o['reponses']); break;
            case 'type'    : array_multisort($relation_name, $switchSortway, $o['reponses']); break;
            case 'name'    : array_multisort($family_name, $switchSortway, $o['reponses']); break;
            case 'id'    : array_multisort($id_relation, $switchSortway, $o['reponses']); break;
            case 'nb_prod'    : array_multisort($nb_products, $switchSortway, $o['reponses']); break;

            default : $sortway == ('asc' ? 'desc' : 'asc'); $sort = 'date';
    }

    $lastsort = $sort;
    $formerpage = $page;

    $o['reponses'] = array_chunk($o['reponses'], NB, false);

    $o['reponses'] = $o['reponses'][$page-1];
    // ordre de tri et pagination

  }else
      $o['reponses'] = 'vide';

}else
  $o['error'] = $errorstring;

$nbcmd = count($scriptList);

$lastpage = $nbcmd > NB ? ceil($nbcmd/NB) : 1;

$o['pagination'] = array('lastsort' => $lastsort , 'sort' =>  $sort, 'sortway' =>  $sortway, 'formerpage' => $formerpage, 'lastpage' => $lastpage , 'page' => $page, 'NB' => NB, 'nbcmd' => $nbcmd);

mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);

?>

