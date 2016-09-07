<?php

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require ADMIN."statut.php";

$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

if (!$user->login()) {
  $o["error"] = "Votre session a expiré, veuillez vous identifier à nouveau après avoir rafraîchi votre page";
  mb_convert_variables("UTF-8","ASCII,UTF-8,ISO-8859-1,CP1252",$o);
  print json_encode($o);
  exit();
}

$db = DBHandle::get_instance();
$o = array("data" => array(),"error" => "");
$actions = $_POST["actions"];
foreach($actions as $action) {
  switch ($action["action"]) {
//    case "get_cat3_attributes":
//      if (!$user->get_permissions()->has("m-prod--sm-categories","r")) {
//        $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
//        break;
//      }
//      if (!isset($action["cat3Id"]) || !is_numeric($action["cat3Id"])) {
//        $o["error"] = "Id famille niveau 3 non spécifié ou invalide";
//        break;
//      }
//      $cat3Id = (int)$action["cat3Id"];
//
//      $cat3_attributes = array();
//      $ac = new RefAttributeCollection($cat3Id, "order by order ASC");
//      foreach ($ac as $a) {
//        $attr = $a->getData();
//        $attr["values"] = array();
//        foreach ($a->get_values("order by order ASC") as $av)
//          $attr["values"][] = $av->getData();
//        $cat3_attributes[] = $attr;
//      }
//      $o["data"]["cat3_attributes"] = $cat3_attributes;
//
//      break;
    
    case "set_cat3_values_groups":
      if (!$user->get_permissions()->has("m-prod--sm-categories","e")) {
        $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
        break;
      }
      if (!isset($action["cat3Id"]) || !is_numeric($action["cat3Id"])) {
        $o["error"] = "Id famille niveau 3 non spécifié ou invalide";
        break;
      }
      if (isset($action["cat3_attr"])){
        $cat3_attr = substr($action["cat3_attr"], 10);
        if(!is_numeric($cat3_attr)) {
          $o["error"] = "Attribut non spécifié ou invalide";
          break;
        }
      }else{
        $o["error"] = "Attribut non spécifié ou invalide";
        break;
      }

      $cat3Id = (int)$action["cat3Id"];
      if (isset($action["saIds"]) && is_array($action["saIds"])) {
        $saIds = array();
        foreach ($action["saIds"] as $saId) {
          if (is_numeric($saId[0]) && is_numeric($saId[1]) && is_numeric($saId[3])) {
//            foreach ($saId["savIds"] as $savId) {
//              if (is_numeric($savId))
                $saIds[] = $saId;
//            }
          }elseif($saId[0] == '' && $saId[1] == '' && $saId[2] == '' && $saId[3] == '' && is_numeric($saId[4]))
            RefAttributeInterval::delete ($saId[4]);
        }
      }

      if (!empty($action["saIds"]) && empty($saIds)) {
        $o["error"] = "Attributs non spécifiés ou invalides";
        break;
      }
      
      $aOrder = 0;
      $ac = new RefAttributeIntervalCollection(array($cat3Id, 'attributeId = '.$cat3_attr));

      foreach ($saIds as $saId => $savIds) {
        if ($a = new RefAttributeInterval($savIds[4])) {
          $save = false;
          if($savIds[0] != $a->start_from){
            $a->start_from = $savIds[0];
            $save = true;
          }
          if($savIds[1] != $a->goes_to){
            $a->goes_to = $savIds[1];
            $save = true;
          }
          if($savIds[2] != $a->name){
            $a->name = $savIds[2];
            $save = true;
          }
          if($savIds[3] != $a->position){
            $a->position = $savIds[3];
            $save = true;
          }
          if($cat3_attr != $a->attributeId){
            $a->attributeId = $cat3_attr;
            $save = true;
          }
          if($cat3Id != $a->categoryId){
            $a->categoryId = $cat3Id;
            $save = true;
          }


          if($save)$a->save();

        }
      }
      
      $o["data"] = "success";
      break;
    
    default:
      $o["error"] .= "Action type is missing";
      break;
  }
}
mb_convert_variables("UTF-8","ASCII,UTF-8,ISO-8859-1,CP1252",$o);
print json_encode($o);
?>