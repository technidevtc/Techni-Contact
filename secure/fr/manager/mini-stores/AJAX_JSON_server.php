<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ADMIN."logs.php");
$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

if (!$user->login()) {
  $o["error"] = "Votre session a expirée, veuillez vous identifier à nouveau après avoir rafraichi votre page";
  print json_encode($o);
  exit();
}

$db = DBHandle::get_instance();
$o = array();
//$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";
list($action, $object) = explode(" ",isset($_REQUEST["action"]) ? $_REQUEST["action"] : "");
$data = isset($_REQUEST["data"]) ? trim($_REQUEST["data"]) : "";
switch($object) {
  case "products":
    switch($action) {
      case "getwithoutfamily":
        if (!$user->get_permissions()->has("m-mark--sm-mini-stores","r")) {
          $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
          break;
        }
        if (!empty($data)) {
          $where = array();
          if (preg_match("/^\d+\s*(,\s*\d+\s*)*(,\s*\d+\s*)*$/", $data)) {
              $idList = explode(",",$data);
              $idCount = count($idList);
              if (!$idCount)
                $where[] = "";
              else {
                $pdt_idList = array();
                for ($idi=0; $idi<$idCount; $idi++) {
                  $pdt_id = (int)$idList[$idi];
                  if (!$pdt_id) continue;
                  $pdt_idList[] = $pdt_id;
                }
                if (!empty($pdt_idList))
                  $where[] = "p.id IN (".implode(",",$pdt_idList).")";
              }
          }
          else {
            $where[] = "NULL";
            //$wordList = preg_split("[\s,]+");
            // TODO
          }
          $pdt_idList_flipped = array_flip($pdt_idList);
          $res = $db->query("
            SELECT p.id, pfr.name, pfr.ref_name, ffr.id AS catID
            FROM products p
            INNER JOIN products_fr pfr ON p.id = pfr.id
            INNER JOIN products_families pf ON p.id = pf.idProduct
            INNER JOIN families_fr ffr ON pf.idFamily = ffr.id
            WHERE ".implode(" OR ",$where)."",__FILE__,__LINE__);
          while($row = $db->fetchAssoc($res)){
            $o["data"][] = $row;
            unset($pdt_idList_flipped[$row['id']]);
          }
          $pdt_idList_error = array_flip($pdt_idList_flipped);
          $o['idList_error'] = array_values($pdt_idList_error);
        }
        else
          $o["error"] = "no products specified to get";
        break;

        case "get":
        if (!$user->get_permissions()->has("m-mark--sm-mini-stores","r")) {
          $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
          break;
        }
        if (!empty($data)) {
          $where = array();
          if (preg_match("/^\d+\s*(,\s*\d+\s*)*(\|\s*\d+\s*(,\s*\d+\s*)*)*$/", $data)) {
            $idsList = explode("|",$data); // item id's
            foreach($idsList as $idList) {
              $idList = explode(",",$idList);
              $cat_id = (int)array_shift($idList);
              if (!$cat_id) continue;
              $idCount = count($idList);
              if (!$idCount)
                $where[] = "p.id = ".$cat_id;
              else {
                $pdt_idList = array();
                for ($idi=0; $idi<$idCount; $idi++) {
                  $pdt_id = (int)$idList[$idi];
                  if (!$pdt_id) continue;
                  $pdt_idList[] = $pdt_id;
                }
                if (!empty($pdt_idList))
                  $where[] = "(p.id IN (".implode(",",$pdt_idList).") AND ffr.id = ".$cat_id.")";
              }
            }
          }
          else {
            $where[] = "NULL";
            //$wordList = preg_split("[\s,]+");
            // TODO
          }
          $res = $db->query("
            SELECT p.id, pfr.name, pfr.ref_name, ffr.id AS catID
            FROM products p
            INNER JOIN products_fr pfr ON p.id = pfr.id
            INNER JOIN products_families pf ON p.id = pf.idProduct
            INNER JOIN families_fr ffr ON pf.idFamily = ffr.id
            WHERE ".implode(" OR ",$where)."",__FILE__,__LINE__);
          while($row = $db->fetchAssoc($res))
            $o["data"][] = $row;
        }
        else
          $o["error"] = "no products specified to get";
        break;

      default:
        $o["error"] = empty($action) ? "no action specified" : "action ".$action." not (yet) supported";
        break;
    }
    break;
  default:
    $o["error"] = empty($object) ? "no object specified" : "object ".$action." not (yet) supported";
    break;
}

mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);

?>