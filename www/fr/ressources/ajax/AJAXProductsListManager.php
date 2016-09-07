<?php
require substr(dirname(__FILE__), 0, strpos(dirname(__FILE__), "/", stripos(dirname(__FILE__), "technico")+1) + 1) . "config.php";
require(ICLASS . "CUserSession.php");

$db = $handle = DBHandle::get_instance();
$session = new UserSession($handle);

$o = array();
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  // Getting action type. If none specified, we stop here
  $action = isset($_GET['action']) ? strtolower($_GET['action']) : "";
  if (isset($_GET['action'])) $action = strtolower($_GET['action']);
  else {
          $o["error"] = "No action specified";
          mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
          print json_encode($o);
          exit();
  }
  if($action != 'emptylist'){
    // Getting product ID
    $productID = isset($_GET["productID"]) ? $_GET["productID"] : '';

    $productID = filter_var($productID, FILTER_SANITIZE_NUMBER_INT); 
    
    if (!preg_match("/^[0-9]{1,8}$/",$productID)) {
            $o["error"] = "Bad Product ID";
    }
    else {

      $product = Doctrine_Query::create()
              ->select()
              ->from('Products p')
              ->innerJoin('p.product_fr pfr')
              ->where('p.id = ?', $productID)
              ->fetchOne(array());

    
      if(!$product)
        $o["error"] = "Product does not exist in DataBase";

      if($product['product_fr']['active']!=1)
        $o["error"] = "Product is not active";

      if($product['product_fr']['deleted']==1)
        $o["error"] = "Product removed";
    }
    if (isset($o["error"])) {
        //mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
        print json_encode($o);
        exit();
    }
  }
    //$_COOKIE['saved_products_list'] = '';
    $productsSavedList = new ProductsSavedList();
    
    switch ($action) {
        case "add" :
          $productsSavedList->saveProduct($product['id']);
          $o['data']['status'] = 'saved';
          $o['data']['logged'] = $session->logged ? 1 : 0;
          break;

        case 'remove' :
          $productsSavedList->removeProduct($product['id']);
          $o['data']['status'] = 'removed';
          break;

        case 'emptylist':
          $productsSavedList->emptyList();
          $o['data']['status'] = 'emptied';
          break;

        default:
          break;
    }

}

//  mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
  print json_encode($o);
  print "\n\n";
  exit();


?>
