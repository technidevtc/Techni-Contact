<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ICLASS . "CUserSession.php");
require(ICLASS . "CCart.php");
require(ICLASS . "CStatisticsManager.php");

$handle = DBHandle::get_instance();
$session = new UserSession($handle);

function rawurldecodeEuro ($str) { return str_replace("%u20AC", "€", rawurldecode($str)); }

//header("Content-Type: text/plain; charset=utf-8");

$o = array();
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  // Getting action type. If none specified, we stop here
  $action = isset($_GET['action']) ? strtolower($_GET['action']) : "";
  if (isset($_GET['action'])) $action = strtolower($_GET['action']);
  else {
    $o["error"] = "No action specified";
    //mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
    print json_encode($o);
    exit();
  }

  // Getting Cart ID
  // By default, the current cart is used. If a different one is specified, we do some checks
  $cartID = isset($_GET["cartID"]) ? $_GET["cartID"] : $session->getID();
  if (!preg_match("/^[0-9a-v]{26,32}$/",$cartID)) {
    $o["error"] = "Bad Cart ID";
  } else {
    $cart = new Cart($handle, $cartID);
    if ($cartID != $session->getID()) {
      if (!$session->logged) {
      $o["error"] = "Session expired";
      }
      elseif (!$cart->existsInDB) {
        $o["error"] = "Cart does not exist in DataBase";
      }
      elseif ($cart->idClient != $session->userID) {
        $o["error"] = "The Cart does not belong to the customer";
      }
    }
  }
  if (isset($o["error"])) {
    //mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
    print json_encode($o);
    exit();
  }

  $ttcPrice = (isset($_GET['ttcPrice']) && $_GET['ttcPrice'] == 'true') ? true : false;
  // tâche ULTRA URGENT - Intervention Google Shopping - 19/02/2013
  // vu avec tristan par tel, on considère que tous les taux de tva de tous les produits sont au taux normal en vigueur
  define('IDTVA', 1);
  
  switch ($action) {
    case "add" :
      if (isset($_GET["pdtID"]) && preg_match("/^\d+$/",$_GET["pdtID"])
       && isset($_GET["idTC"]) && preg_match("/^\d+$/",$_GET["idTC"])) {
        $pdtID = $_GET["pdtID"];
        $idTC = $_GET["idTC"];
        $catID = isset($_GET["catID"]) ? (preg_match("/^\d+$/",$_GET["catID"]) ? $_GET["catID"] : "") : null;

        $db = DBHandle::get_instance();
        $res = $db->query("
          SELECT
            p.idAdvertiser,
            pfr.name,
            pfr.fastdesc,
            pfr.ref_name,
            pf.idFamily,
            a.category AS adv_cat,
            rcols.content AS ccols_headers,
            rc.label AS ref_label,
            rc.price+rc.ecotax AS ref_price,
            rc.content AS ccols_content
          FROM products p
          INNER JOIN products_fr pfr on p.id = pfr.id
          INNER JOIN products_families pf on p.id = pf.idProduct" . (!empty($catID) ? " and pf.idFamily = ".$catID."" : "") . "
          INNER JOIN advertisers a on p.idAdvertiser = a.id and a.category = " . __ADV_CAT_SUPPLIER__ . " and a.actif = 1
          INNER JOIN references_cols rcols ON p.id = rcols.idProduct
          INNER JOIN references_content rc on p.id = rc.idProduct and rc.id = " . $idTC . " and rc.price REGEXP '^[0-9]+((\.|,)[0-9]+){0,1}$'
          WHERE p.id = ".$pdtID."", __FILE__, __LINE__);

        if ($db->numrows($res) > 0) {
          $ref = $db->fetchAssoc($res);
          $ccols_headers = mb_unserialize($ref["ccols_headers"]);
          $ccols_headers = array_slice($ccols_headers, 3, -5); // get only custom cols headers
          $ccols_content = mb_unserialize($ref["ccols_content"]);
          
          for($k=0, $l=count($ccols_headers); $k<$l; $k++)
            $ref["customCols"][$ccols_headers[$k]] = $ccols_content[$k];
          
          if (!empty($ref["customCols"])) {
            $ref["cart_desc"] = $ref["ref_label"];
            foreach($ref["customCols"] as $ccol_header => $ccol_content)
              $ref["cart_desc"] .= " - ".$ccol_header.": ".$ccol_content;
          } else {
            $ref["cart_desc"] = $ref["name"] . (empty($ref["label"]) ? "" : " - " . $ref["label"]);
          }
          
          $qty = isset($_GET["qty"]) ? (preg_match("/^[0-9]{1,6}$/",$_GET["qty"]) ? (int)$_GET["qty"] : 1) : 1;
          $comment = isset($_GET["comment"]) ? substr(rawurldecodeEuro(trim($_GET['comment'])), 0, 1023) : null;
          if ($cart->AddProduct($pdtID, $idTC, $ref["idFamily"], $qty, $comment)) {
            $stats = new StatisticsManager($handle);
            $stats->AddProductToCart($pdtID, $idTC, $ref["idFamily"], $ref["idAdvertiser"], $qty);
            $cart->calculateCart();
            $tvaValues = Tva::calculatePriceFromId(IDTVA, $ref["ref_price"]);
            $o["data"] = array(
              "idTC" => $idTC,
              "name" => $ref["name"],
              "fastdesc" => $ref["fastdesc"],
              "label" => $ref["ref_label"],
              "price" => $ttcPrice? $tvaValues['priceTTC'] : $ref["ref_price"],
              "quantity" => $qty,
              "total_price" => ($ttcPrice? $tvaValues['priceTTC'] : $ref["ref_price"])*$qty,
              "cart_item_count" => $cart->itemCount,
              "cart_desc" => $ref["cart_desc"],
              "pic_url" => (is_file(WWW_PATH.'ressources/images/produits/thumb_small/'.$pdtID."-1.jpg") ? PRODUCTS_IMAGE_URL.'thumb_small/'.$pdtID."-1.jpg" : PRODUCTS_IMAGE_URL.'no-pic-thumb_small.gif')
            );
          } else {
            $o["warning"] = "No product Added";
          }
        } else {
          $o["error"] = "No valid product's Reference to add in Cart";
        }
      } else {
        $o["error"] = "Product ID or Reference IDTC not specified";
      }
      break;

    case "del" :
      if (isset($_GET["idTC"]) && preg_match("/^\d+$/",$_GET["idTC"])) {
        $idTC = $_GET["idTC"];
        $cart->delProduct($idTC);
        $cart->calculateCart();
        $o["data"] = 1;
      } else {
        $o["error"] = "Product or Reference IDTC not specified";
      }
      break;

    case "mod" :
      if (isset($_GET["idTC"]) && preg_match("/^\d+$/",$_GET["idTC"])) {
        $idTC = $_GET["idTC"];
        $modList = array();
        if (isset($_GET["qty"]) && preg_match("/^[0-9]{1,6}$/",$_GET["qty"])) {
          $qty = (int)$_GET["qty"];
          $modList["qty"] = $qty;
        }
        if (isset($_GET["comment"])) {
          $comment = substr(trim($_GET['comment']), 0, 1023);
          $modList["comment"] = $comment;
        }

        if (!empty($modList)) {
          $cart->updateProduct($idTC, $modList);
          $o["data"] = 1;
        } else {
          $o["warning"] = "No product modified";
        }
      } else {
        $o["error"] = "Product or Reference IDTC not specified";
      }
      break;
      
    case 'setadresses';
      $cart2 = Doctrine_Query::create()
        ->select('ct.*, ctl.*, c.id, ca.*, cda.*, cba.*')
        ->from('Paniers ct')
        ->innerJoin('ct.lines ctl')
        ->innerJoin('ct.client c')
        ->innerJoin('c.addresses ca')
        ->leftJoin('ct.delivery_address cda')
        ->leftJoin('ct.billing_address cba')
        ->where('ct.id = ?', $session->getID())
        ->andWhere('c.id = ?', $session->userID)
        ->orderBy('ca.type_adresse ASC, ca.num ASC')
        ->fetchOne();
      
      if ($cart2) {
        $cart2->delivery_address_id = $_GET['data'][0];
        $cart2->billing_address_id = $_GET['data'][1];
        if ($cart2->checkAndCorrectAddresses()) {
          $cart2->save();
          $o['data'] = 1;
        } else {
          $o['error'] = "Erreur innatendue";
        }
      } else {
        $o['error'] = "Erreur innatendue";
      }
      break;
      
    default :
      $o["error"] = "The action " . $action . " does not exist";
      break;
  }
  
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {

// Getting Cart ID
  // By default, the current cart is used. If a different one is specified, we do some checks
  $cartID = isset($_POST["cartID"]) ? $_POST["cartID"] : $session->getID();
  if (!preg_match("/^[0-9a-v]{26,32}$/",$cartID)) {
    $o["error"] = "Bad Cart ID";
  }
  else {
    $cart = new Cart($handle, $cartID);
    if ($cartID != $session->getID()) {
      if (!$session->logged) {
        $o["error"] = "Session expired";
      }
      elseif (!$cart->existsInDB) {
        $o["error"] = "Cart does not exist in DataBase";
      }
      elseif ($cart->idClient != $session->userID) {
        $o["error"] = "The Cart does not belong to the customer";
      }
    }
  }
  if (isset($o["error"])) {
    //mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
    print json_encode($o);
    exit();
  }

  if (isset($_POST['todo'])) {
    list($action,$value) = explode('_',$_POST['todo']);

    switch ($action) {
      case 'clearall' :
        $cart->clearProducts();
        $cart->calculateCart();
        break;

      case 'delpdt' :
        if (!empty($value)) {
          list($pdtID,$refID) = explode('-',$value);
          if (preg_match('/^\d+$/',$refID)) {
            $cart->delProduct($refID);
            $cart->calculateCart();

            // searching for references of the same product
            $pdtStillPresent = false;
            foreach($cart->items as $item) {
              if ($item["idProduct"] == $pdtID) {
                $pdtStillPresent = true;
                break;
              }
            }

            // Avail, only if there's no reference of this product anymore in the cart
            /*if (!$pdtStillPresent) {
              $api = new JsonRpcClient(AVAIL_JSONRPC_API_URL);
              try {
                $api->logRemovedFromCart(array(
                  "SessionID" => $_COOKIE["__avail_session__"],
                  "ProductID" => $pdtID
                ));
              } catch (Exception $e) {
                //echo $e->getMessage();
              }
            }*/
          }
        }
        break;

      case 'updqte' :
        if (isset($_POST['qty'])) {
          $data_ref_lines = explode('<_>',$_POST['qty']);
          foreach ($data_ref_lines as $data_ref_line) {
            list($refID,$quantity) = explode('-',$data_ref_line);
            if (preg_match('/^\d+$/',$refID) && preg_match('/^[1-9]?[0-9]*$/',$quantity)) {
              $cart->updateProductQuantity($refID,$quantity);
            }
          }
          $cart->calculateCart();

          foreach($cart->items as $prod)
            $o['data']['items'][] = array(
                'idTC' => $prod['idTC'],
                'sumHT' => $prod['sumHT']
                );

            $o['data']['totalsCart'] = array(
                'stotalHT' => $cart->stotalHT,
                'totalTVA' => $cart->totalTVA,
                'fdpHT' => $cart->fdpHT,
                'totalHT' => $cart->totalHT,
                'totalTTC' => $cart->totalTTC
            );
        }
        break;

      case "addInsurance" :
        $cart->calculateCart();
        $cart->addInsurance();
        $cart->calculateCart();
        break;

      case "delInsurance" :
        $cart->calculateCart();
        $cart->removeInsurance();
        $cart->calculateCart();
        break;

      default : break;
    }
    $o['data']['notValidPdtList'] = $cart->notValidPdtList;
    $o['data']['notValidAdvList'] = $cart->notValidAdvList;
  } else {
    $action = isset($_POST['action']) ? strtolower($_POST['action']) : "";
    switch ($action) {
      case "add" :
        if (isset($_GET["pdtID"]) && preg_match("/^\d+$/",$_GET["pdtID"])
            && isset($_GET["idTC"]) && preg_match("/^\d+$/",$_GET["idTC"])) {
          $pdtID = $_GET["pdtID"];
          $idTC = $_GET["idTC"];
          $catID = isset($_GET["catID"]) ? (preg_match("/^\d+$/",$_GET["catID"]) ? $_GET["catID"] : "") : null;
          
          $db = DBHandle::get_instance();
          $res = $db->query("
            SELECT
              p.idAdvertiser, pfr.name, pfr.fastdesc,
              pf.idFamily,
              a.category as adv_cat,
              rc.label as ref_label, rc.price+rc.ecotax as ref_price
            FROM products p
            INNER JOIN products_fr pfr on p.id = pfr.id
            INNER JOIN products_families pf on p.id = pf.idProduct" . (!empty($catID) ? " and pf.idFamily = ".$catID."" : "") . "
            INNER JOIN advertisers a on p.idAdvertiser = a.id and a.category = " . __ADV_CAT_SUPPLIER__ . " and a.actif = 1
            INNER JOIN references_content rc on p.id = rc.idProduct AND rc.id = " . $idTC . " AND rc.price REGEXP '^[0-9]+((\.|,)[0-9]+){0,1}$' AND rc.vpc = 1 AND rc.deleted = 0
            WHERE
              p.id = ".$pdtID, __FILE__, __LINE__);
      
          if ($db->numrows($res) > 0) {
            $ref = $db->fetchAssoc($res);
            $qty = isset($_GET["qty"]) ? (preg_match("/^[0-9]{1,6}$/",$_GET["qty"]) ? (int)$_GET["qty"] : 1) : 1;
            $comment = isset($_GET["comment"]) ? substr(rawurldecodeEuro(trim($_GET['comment'])), 0, 1023) : null;
            if ($cart->AddProduct($pdtID, $idTC, $ref["idFamily"], $qty, $comment)) {
              $stats = new StatisticsManager($handle);
              $stats->AddProductToCart($pdtID, $idTC, $ref["idFamily"], $ref["idAdvertiser"], $qty);
              $cart->calculateCart();
              $o["data"] = array(
                "idTC" => $idTC,
                "name" => $ref["name"],
                "fastdesc" => $ref["fastdesc"],
                "label" => $ref["ref_label"],
                "price" => $ref["ref_price"],
                "quantity" => $qty,
                "cart_item_count" => $cart->itemCount);
            }
            else {
              $o["warning"] = "No product Added";
            }
          }
          else {
            $o["error"] = "No valid product's Reference to add in Cart";
          }
        }
        else {
          $o["error"] = "Product ID or Reference IDTC not specified";
        }
        break;
        
      case "del" :
        if (isset($_GET["idTC"]) && preg_match("/^\d+$/",$_GET["idTC"])) {
          $idTC = $_GET["idTC"];
          $cart->delProduct($idTC);
          $cart->calculateCart();
          $o["data"] = 1;
        }
        else {
          $o["error"] = "Product or Reference IDTC not specified";
        }
        break;
        
      case "mod" :
        if (isset($_POST["idTC"]) && preg_match("/^\d+$/",$_POST["idTC"])) {
          $idTC = $_POST["idTC"];
          $modList = array();
          if (isset($_POST["qty"]) && preg_match("/^[0-9]{1,6}$/",$_POST["qty"])) {
            $qty = (int)$_POST["qty"];
            $modList["qty"] = $qty;
          }
          if (isset($_POST["comment"])) {
            $comment = substr(trim($_POST['comment']), 0, 1023);
            $modList["comment"] = $comment;
          }
          
          if (!empty($modList)) {
            $cart->updateProduct($idTC, $modList);
            $o["data"] = 1;
          }
          else {
            $o["warning"] = "No product modified";
          }
        }
        else {
          $o["error"] = "Product or Reference IDTC not specified";
        }
        break;
        
      default :
        $o["error"] = "The action " . $action . " does not exist";
        break;
    }
  }
  if (isset($_POST["promotion_code"])) {
    if ($_POST["promotion_code"] != "") {
      require_once ICLASS."CPromotion.php";
      if (Promotion::promotionCodeIsValid(time(),$_POST["promotion_code"]))
        $cart->promotionCode = $_POST["promotion_code"];
      else {
        $cart->promotionCode = "";
        $badPromotion = true;
      }
    }
    $cart->calculateCart();
  }

}
//mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);
print "\n\n";
