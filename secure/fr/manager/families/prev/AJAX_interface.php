<?php

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require ADMIN."statut.php";

$user = new BOUser();

//header("Content-Type: text/plain; charset=utf-8");

if (!$user->login()) {
  $o["error"] = "Votre session a expiré, veuillez vous identifier à nouveau après avoir rafraîchi votre page";
  mb_convert_variables("UTF-8","ASCII,UTF-8,ISO-8859-1,CP1252",$o);
  print json_encode($o);
  exit();
}

$db = DBHandle::get_instance();
$o = array("data" => array(),"error" => "");
$actions = $_POST["actions"];
if(!empty ($actions))
foreach($actions as $action) {
  switch ($action["action"]) {
    case "get_cat3_attributes":
      if (!$user->get_permissions()->has("m-prod--sm-categories","r")) {
        $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
        break;
      }
      if (!isset($action["cat3Id"]) || !is_numeric($action["cat3Id"])) {
        $o["error"] = "Id famille niveau 3 non spécifié ou invalide";
        break;
      }
      $cat3Id = (int)$action["cat3Id"];
      
      $cat3_attributes = array();
      $ac = new RefAttributeCollection(array($cat3Id, "order by order ASC"));

      foreach ($ac as $a) {
        $attr = $a->getData();
        $aic = new RefAttributeInterval();
        $aicVals = $aic->get('attributeId = '.$attr['id']);
        $attr['nbInterval'] = count($aicVals);
        $attr["values"] = array();
        foreach ($a->get_values("order by order ASC") as $av)
          $attr["values"][] = $av->getData();

        // virtual attributes
        if($a->virtual == 1){
          $avc = new RefAttributeVirtualCollection($cat3Id, 'attributeId = '.$attr['id'], "order by order ASC");

          $attr["values"] = array();
          $nbrProducts = 0;
          foreach ($avc as $av){

            $avDetail = $av->getData();
            $avpc = new RefAttributeVirtualProductsCollection($av->id);
            $nbrProduitByAttr = 0;
            foreach($avpc as $avp){
              if($avDetail['attributeId'] == $attr['id']){
                $nbrProducts++;
                $nbrProduitByAttr++;
              }
            }
            $avDetail['usedCount'] = $nbrProduitByAttr;

            if($avDetail['attributeId'] == $attr['id'])
              $attr["values"][] = $avDetail;
          }
          $attr['usedCount'] = $nbrProducts;
        }// virtual attributes

        $cat3_attributes[] = $attr;
      }
      $o["data"]["cat3_attributes"] = $cat3_attributes;
      
      break;
    
    case "set_cat3_selected_attributes":
      if (!$user->get_permissions()->has("m-prod--sm-categories","e")) {
        $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
        break;
      }
      if (!isset($action["cat3Id"]) || !is_numeric($action["cat3Id"])) {
        $o["error"] = "Id famille niveau 3 non spécifié ou invalide";
        break;
      }
      $cat3Id = (int)$action["cat3Id"];
      if (isset($action["saIds"]) && is_array($action["saIds"])) {
        $saIds = array();
        foreach ($action["saIds"] as $saId) {
          if (is_numeric($saId["saId"])) {
            if(is_array($saId["savIds"]))
            foreach ($saId["savIds"] as $savId) {
              if (is_numeric($savId))
                $saIds[$saId["saId"]][$savId] = true;
            }
          }
        }
      }
      if (!empty($action["saIds"]) && empty($saIds)) {
        $o["error"] = "Attributs non spécifiés ou invalides";
        break;
      }
      
      $aOrder = 0;
//      $ac = new RefAttributeCollection($cat3Id);
      $ac = new RefAttributeCollection(array($cat3Id, "order by order ASC"));
      foreach ($ac as $a)
        if($a->virtual == 0)
          $a->selected = $a->order = 0;
      foreach ($saIds as $saId => $savIds) {
        if ($a = $ac->get($saId)) {
          $a->selected = 1;
          $a->order = ++$aOrder;
          
          $avOrder = 0;
          $avs = $a->get_values();
          foreach ($avs as $av)
            $av->order = 0;
          foreach ($savIds as $savId => $v)
            if ($av = $avs->get($savId))
              $av->order = ++$avOrder;
        }
      }
      $ac->update();
      
      $o["data"] = "success";
      break;

    case 'toggle_activation':
      if (!$user->get_permissions()->has("m-prod--sm-categories","r")) {
        $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
        break;
      }
      if (!isset($action["idRefAttr"]) || !is_numeric($action["idRefAttr"])) {
        $o["error"] = "Id non spécifié ou invalide";
        break;
      }
      $a = new RefAttribute($action["idRefAttr"]);
      $new_active_value = $a->active == 0 ? 1 : 0;
      $a->active = $new_active_value;
      $a->save();
      $o["data"] = $new_active_value;
      break;

    case 'create_virtual_attr':
    case 'update_virtual_attr':
      if (!$user->get_permissions()->has("m-prod--sm-categories","r")) {
        $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
        break;
      }
      if (empty ($action["attr_name"])) {
        $o["error"] = "Nom de facette non spécifié ou invalide";
        break;
      }
      if (!isset($action["cat3Id"]) || !is_numeric($action["cat3Id"])) {
        $o["error"] = "Famille 3 non spécifiée ou invalide";
        break;
      }
      $idVirtAttr = !empty($action["cat3_attr_id"]) && is_numeric($action["cat3_attr_id"]) ? $action["cat3_attr_id"] : null;

      $a = new RefAttribute($idVirtAttr);
      $a->name = $action["attr_name"];
      $a->categoryId = $action["cat3Id"];
      $a->virtual = 1;
      $a->selected = 1;
      $a->save();
      if($a->existsInDB()){
        foreach($action['saIds'] as $saId){
          if(!empty($saId[0]) && !empty($saId[2])){
            $refVirtAttr = !empty($saId[3]) && is_numeric($saId[3]) ? $saId[3] : null;
            $virtual = new RefAttributeVirtual($refVirtAttr);
            $virtual->categoryId = $action["cat3Id"];
            $virtual->attributeId = $a->id;
            $virtual->value = $saId[0];
            $virtual->name = $saId[1];
            $virtual->position = $saId[2];
            $virtual->save();
          }
        }
      }
//      $o["data"] = $new_active_value;
      break;

    case 'get_virtual_attr_products_list':
      if (!$user->get_permissions()->has("m-prod--sm-categories","r")) {
        $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
        break;
      }
      if (!isset($action["cat3Id"]) || !is_numeric($action["cat3Id"])) {
        $o["error"] = "Famille 3 non spécifiée ou invalide";
        break;
      }
      if (!isset($action["cat3_attr_id"]) || !is_numeric($action["cat3_attr_id"])) {
        $o["error"] = "Facette non spécifié ou invalide";
        break;
      }
      
      $apl = new RefAttributeVirtualProductsCollection($action["cat3_attr_id"]);
      
      $list_products = array();
      foreach($apl as $ap)
        $list_products[] = $ap->id_product;

      $o["data"] = $list_products;
      break;

    case 'update_virtual_attr_products_list':
      if (!$user->get_permissions()->has("m-prod--sm-categories","r")) {
        $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
        break;
      }
      if (!isset($action["cat3Id"]) || !is_numeric($action["cat3Id"])) {
        $o["error"] = "Famille 3 non spécifiée ou invalide";
        break;
      }
      if (!isset($action["cat3_attr_id"]) || !is_numeric($action["cat3_attr_id"])) {
        $o["error"] = "Facette non spécifié ou invalide";
        break;
      }

      $products_list = preg_replace("/(\r\n|\r|\n)/", ',', $action["products_list"]);

      $products_list = explode(',', $products_list);

      $query = 'delete from ref_attributes_virtual_products where id_ref_attribute_virtual = '.$db->escape($action["cat3_attr_id"]) ;
      $res = $db->query($query);

      if(!empty ($action["products_list"])){
        foreach ($products_list as $product_id) {
          if(!empty ($product_id)){
            $virt_attr_prodt = new RefAttributeVirtualProducts();
            $virt_attr_prodt->id_ref_attribute_virtual = $action["cat3_attr_id"];
            $virt_attr_prodt->id_product = trim($product_id);
            $virt_attr_prodt->save();
          }
        }
      }

      break;

    case 'delete_virtual_attr_':
      if (!$user->get_permissions()->has("m-prod--sm-categories","r")) {
        $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
        break;
      }
      if (!isset($action["cat3Id"]) || !is_numeric($action["cat3Id"])) {
        $o["error"] = "Famille 3 non spécifiée ou invalide";
        break;
      }
      if (!isset($action["cat3_attr_id"]) || !is_numeric($action["cat3_attr_id"])) {
        $o["error"] = "Facette non spécifié ou invalide";
        break;
      }

      $query = 'delete from ref_attributes_virtual_products where id_ref_attribute_virtual = '.$db->escape($action["cat3_attr_id"]);
      $res = $db->query($query);

      $query = 'delete from ref_attributes_virtual where attributeId = '.$db->escape($action["cat3_attr_id"]).' and categoryId = '.$db->escape($action["cat3Id"]);
      $res = $db->query($query);

      $query = 'delete from ref_attributes where id = '.$db->escape($action["cat3_attr_id"]).' and categoryId = '.$db->escape($action["cat3Id"]);
      $res = $db->query($query);

      break;
    
    default:
      $o["error"] .= "Action type is missing";
      break;
  }
}
//mb_convert_variables("UTF-8","ASCII,UTF-8,ISO-8859-1,CP1252",$o);
print json_encode($o);
?>