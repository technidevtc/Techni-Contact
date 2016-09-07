<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

header("Content-Type: text/plain; charset=utf-8");

require_once(ADMIN."logs.php");


$handle = DBHandle::get_instance();
$user = new BOUser();

if (!$user->login()) {
	$o["error"] = "Votre session a expirée, veuillez vous identifier à nouveau après avoir rafraichi votre page";
	print json_encode($o);
	exit();
}

$db = DBHandle::get_instance();
$o = array();
switch($_GET["action"]) {
	case "getorder": 
          $q = Doctrine_Query::create()
                ->select('id, name, home as rang')
                ->from('MiniStores')
                ->where('active = 1')
                ->andWhere('standalone = 0')
                ->andWhere('home != 0')
                ->orderBy('home ASC')
                ->fetchArray();
		//$msOrder= isset($_GET["msOrder"]) ? (int)$_GET["msOrder"] : "";
		if (empty($q[0])) { $o["error"] = "Mini-store Home empty"; break; }
		$o["msOrder"] = json_encode($q);
		break;
		

        case "reorder":
          $idList = $_GET['idList'];
          if (empty($idList)) { $o["error"] = "Data set empty"; break; }

          $listId = explode(',', $idList);
          $q = Doctrine_Query::create()
                  ->select('COUNT(id)')
                  ->from('MiniStores')
                  ->whereIn('id', $listId)
                  ->fetchArray();
           if(count($listId) != $q[0]['COUNT']){ $o["error"] = "Incorrect Id"; break; };
           foreach ($listId as $key=>$idMs){
             $q = Doctrine_Query::create()
                     ->update('MiniStores')
                     ->set('home', $key+1)
                     ->where('id = ?', $idMs)
                     ->execute();
           }
           $o["data"] = "Réordination effectuée avec succès";
        break;
		
	
	default:
		$o["error"] = "Action type missing";
		break;
}

mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);

?>