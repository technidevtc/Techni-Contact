<?php
/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD†pour Hook Network SARL - http://www.hook-network.com
 Date de crÈation : 31 mars 2011

 Fichier : /secure/manager/orders/AJAX_autocompleteSupplier.php
 Description : autocompletion de recherche fournisseurs

/=================================================================*/
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

define("MAX_AUTOCOMPLETION_RESULTS", 10);
define("AUTOCOMPLETION_CATEGORIES_RESULT_COUNT", 5);
define("AUTOCOMPLETION_PRODUCTS_TITLE_RESULT_COUNT", 10);

include LANG_LOCAL_INC . "includes-" . DB_LANGUAGE . "_local.php";
include LANG_LOCAL_INC . "www-" . DB_LANGUAGE . "_local.php";

$db = DBHandle::get_instance();

$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

if (!$user->login()) {
  $o["error"] = "Votre session a expirÈe, veuillez vous identifier ‡ nouveau aprËs avoir rafraichi votre page";
  print json_encode($o);
  exit();
}

require_once(ADMIN."logs.php");

if (!$user->login($login, $pass) || !$user->active) {
	$o["error"] = "Votre session a expir√©e, veuillez vous identifier √† nouveau apr√®s avoir rafraichi votre page";
	print json_encode($o);
	exit();
}

$o = array();

if(!empty ($_GET['search'])){

  $q = isset($_GET["search"]) ? trim($_GET["search"]) : '';
  $ql = strlen($q);

  $terms = preg_split("`\s|-`", Utils::noDiphthong(urldecode($q)));
  for ($i = 0; $i < count($terms); $i++) {
          if (Utils::is_plural($terms[$i])) {
                  $terms[$i] = "".Utils::get_singular($terms[$i])."* <<".$terms[$i]."*";
          }
          else
                  $terms[$i] = $terms[$i]."*";
          if ($i == 0) $terms[$i] = ">".$terms[$i];
  }
  $match_expr = mb_convert_encoding(implode(" ", $terms), "ISO-8859-1", "UTF-8");

  if ($ql >= 1) {

          $results = array("total" => array("count" => 0, "start_time" => microtime(true), "end_time" => 0));

          $results["suppliers"] = array("data" => array(), "count" => 0, "start_time" => microtime(true), "end_time" => 0);
          if ($results["total"]["count"] < MAX_AUTOCOMPLETION_RESULTS) {
            $query = "
                  SELECT id, nom1
                  FROM advertisers
                  WHERE MATCH (nom1) AGAINST ('" . $db->escape($match_expr) . "' IN BOOLEAN MODE) AND category = 1
                  ";
                  $ressource = $db->query($query);
                  $results["suppliers"]["end_time"] = microtime(true);
                  while ($result = $db->fetchAssoc($ressource)) {
                          $results["suppliers"]["data"][]= array(mb_convert_encoding($result["nom1"], "UTF-8","ISO-8859-1"), "");
                          $results["suppliers"]["count"]++;
                          $results["total"]["count"]++;
                          if ($results["total"]["count"] >= MAX_AUTOCOMPLETION_RESULTS) break;
                  }
          }

          $results["total"]["end_time"] = microtime(true);
          $out = $results["suppliers"]["data"];

          mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $out);
          print json_encode($out);
  }
  else {
          print json_encode(array());
  }

}else{
  
mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode(array('Recherche incorrecte'));
}



?>
