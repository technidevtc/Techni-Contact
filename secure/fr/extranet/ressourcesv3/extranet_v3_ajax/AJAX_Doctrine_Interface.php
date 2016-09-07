<?php
session_name('extranet');
session_start();

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

require_once(ICLASS."ExtranetUser.php");

$db = DBHandle::get_instance();
$user = new ExtranetUser($db);

header("Content-Type: text/plain; charset=utf-8");
mb_convert_variables("UTF-8","ASCII,UTF-8,ISO-8859-1,CP1252",$_POST);
$o = array("data" => array());

try {
  if (empty($_SESSION['extranet_user_id']))
    throw new Exception("Votre session a expiré, veuillez vous identifier à nouveau après avoir rafraîchi votre page.");
  
  switch ($_POST["type"]) {
    
    case "Doctrine_query":
      if (!is_array($_POST["data"]) || !is_array($_POST["data"]["queryParts"]))
        throw new Exception("Query's data not valid.");
      $queriesObjects = array($_POST["data"]);
      $linkedLimit = 0;
    
    case "Doctrine_multiple_queries":
      if (!isset($queriesObjects)) {
        if (!is_array($_POST["data"]) || !is_array($_POST["data"]["queriesObjects"]))
          throw new Exception("Queries data not valid.");
        $queriesObjects = $_POST["data"]["queriesObjects"];
        $linkedLimit = filter_var($_POST["data"]["linkedLimit"], FILTER_VALIDATE_INT, array("min_range" => 0));
      }
      $resultsCumulatedCount = 0;
      
      foreach ($queriesObjects as $queryObject) {
        $queryParts = $queryObject["queryParts"];
        $hydrationMode = $queryObject["hydrationMode"];
        
        if (!is_array($queryParts))
          throw new Exception("query not valid");
        
        $methods = array_flip(array(
          "addFrom",
          "addGroupBy",
          "addHaving",
          "addOrderBy",
          "addSelect",
          "addWhere",
          "andWhere",
          "andWhereIn",
          "andWhereNotIn",
          "delete",
          "distinct",
          "from",
          "groupBy",
          "having",
          "innerJoin",
          "leftJoin",
          "limit",
          "offset",
          "orWhere",
          "orWhereIn",
          "orWhereNotIn",
          "orderBy",
          "select",
          "set",
          "update",
          "where",
          "whereIn"
        ));
        
        $q = Doctrine_Query::create();
        foreach ($queryParts as $part) {
          list($method, $params) = $part;
          if (isset($methods[$method])) {
            //print "method=".$method." pc=".count($params)." p1=".$params[0]." p2=".print_r($params[1], true)." p3=".$params[2]."\n";
            switch (count($params)) {
              case 1: $q->$method($params[0]); break;
              case 2: $q->$method($params[0], $params[1]); break;
              case 3: $q->$method($params[0], $params[1], $params[2]); break;
              case 4: $q->$method($params[0], $params[1], $params[2], $params[3]); break;
            }
          }
        }
        // if linked limit, we limit the current query to the remaining items limit to get
        if ($linkedLimit)
          $q->limit($linkedLimit - $resultsCumulatedCount);
        // rights check
        //print $q->getSqlQuery();
        /*switch ($q->getType) {
          case Doctrine_Query_Abstract::SELECT:
          case Doctrine_Query_Abstract::UPDATE:
          case Doctrine_Query_Abstract::DELETE:
        }*/
        if ($hydrationMode == "count") {
          $o["data"][] = $q->count();
        }
        else {
          switch ($hydrationMode) {
            case "fetchOne":
              $data = $q->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
              break;
            case "scalar":
              $data = $q->execute(array(), Doctrine_Core::HYDRATE_SCALAR);
              break;
            case "single_scalar":
              $data = $q->execute(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
              break;
            case "array":
            default:
              $data = $q->fetchArray();
          }
          $resultsCumulatedCount += count($data);
          $o["data"][] = $data;
          // linked limit is reached, we break the loop
          if ($linkedLimit && $resultsCumulatedCount >= $linkedLimit)
            break;
        }
      }
      // case of a single query
      if ($_POST["type"] == "Doctrine_query") {
        $o["data"] = $o["data"][0];
      }
      elseif ($linkedLimit) {
        // completing o["data"] with empty arrays if some queries were not executed because of the linked limit
        $queriesObjectsCount = count($queriesObjects);
        $dataCount = count($o["data"]);
        for ($k=$dataCount; $k<$queriesObjectsCount; $k++)
          $o["data"][$k] = array();
      }

      break;
    
    case "Doctrine_Object":
      
      // list of supported objects with their supported methods
      $objectsMethods = array(
        "Estimate" => array(
          "create" => 1,
          "update" => 1,
          "resend" => 1,
          "createOrder" => 1
        ),
        "Order" => array(
          "create" => 1,
          "update" => 1,
          "delete" => 1,
          "oking" => 1,
          "validate" => 1,
          "sendClientEmail" => 1
        ),
        "SupplierOrder" => array(
          "update" => 1,
          "send" => 1,
          "sendPartnerMail" => 1
        ),
        "Invoice" => array(
          "create" => 1,
          "update" => 1,
          "validate" => 1,
          "sendMail" => 1,
          "toCreditNote" => 1
        ),
        "InternalNotes" => array(
          "create" => 1,
          "update" => 1
        ),
        "Messenger" => array(
          "create" => 1
        )
      );
      
      if (!isset($objectsMethods[$_POST["object"]])) {
        throw new Exception("Object type missing or not supported.");
      }
      elseif (!isset($objectsMethods[$_POST["object"]][$_POST["method"]])) {
        throw new Exception("This object does not support this method.");
      }
        
      switch ($_POST["method"]) {
        
        case "update":
          if (!is_array($_POST["loadQueryParams"]))
            throw new Exception("Invalid loading parameters.");
          if (!is_array($_POST["data"]))
            throw new Exception("Object data missing.");
          $e = Doctrine_Query::create()->select("*")->from($_POST["object"])->where($_POST["loadQueryParams"][0], $_POST["loadQueryParams"][1])->fetchOne();
          $e->synchronizeWithArray($_POST["data"]);
          $e->save();
          $o["data"] = $e->toArray();
          break;
        
        case "create":
          if (!is_array($_POST["data"]))
            throw new Exception("Object data missing.");
          $e = new $_POST["object"]();
          $e->synchronizeWithArray($_POST["data"]);
          $e->save();
          $o["data"] = $e->toArray();
          break;
        
        case "delete":
          if (!is_array($_POST["loadQueryParams"]))
            throw new Exception("Invalid loading parameters.");
          $rows = Doctrine_Query::create()->delete($_POST["object"])->where($_POST["loadQueryParams"][0], $_POST["loadQueryParams"][1])->execute();
          $o["data"] = $rows;
          break;
          
        default:
          if (!is_array($_POST["loadQueryParams"]))
            throw new Exception("Invalid loading parameters.");
          $args = !isset($_POST["data"]) || !is_array($_POST["data"]) ? array() : $_POST["data"];
          $e = Doctrine_Query::create()->select("*")->from($_POST["object"])->where($_POST["loadQueryParams"][0], $_POST["loadQueryParams"][1])->fetchOne();
          $o["data"] = call_user_func_array(array($e, $_POST["method"]), $args);
          break;
      }
      
      break;
  }
  
  $o["success"] = 1;
  
  /*if (isset($_POST["queryParts"]))
    $queriesParts = array(array("queryParts" => $_POST["queryParts"], "hydrationMode" => $_POST["hydrationMode"]));
  else
    $queriesParts = isset($_POST["queriesParts"]) && is_array($_POST["queriesParts"]) ? $_POST["queriesParts"] : array();
  */
  
  // set a global limit for the results.
  // After the limit is reached, every remaining queries are ignored
  //$linkedLimit = filter_input(INPUT_POST, "linkedLimit", FILTER_VALIDATE_INT, array("min_range" => 0));
} catch (Exception $e) {
  $o["errorMsg"] = $e->getMessage();
}
print json_encode($o);

