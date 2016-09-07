<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

header("Content-Type: text/plain; charset=utf-8");

require_once(ADMIN."logs.php");
require_once(ADMIN."logo.php");

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
	case "getpics": 
		$msID= isset($_GET["msID"]) ? (int)$_GET["msID"] : "";
		if (empty($msID)) { $o["error"] = "Mini-store ID missing"; break; }
		$o["msID"] = $msID;
		/*$type = isset($_GET["type"]) ? $_GET["type"] : "";
                if (empty($type)) { $o["error"] = "Image type missing"; break; }*/

		$o["pics"] = array();
		if (is_file(MSPP_HOME.$msID.".jpg"))
			$o["pics"][] = array('type'=>"home", 'url'=>URL_MSPP_HOME.$msID.".jpg");
		if (is_file(MSPP_VIGN.$msID.".jpg"))
			$o["pics"][] = array('type'=>"vignette", 'url'=>URL_MSPP_VIGN.$msID.".jpg");
                if (is_file(MSPP_ESPA.$msID.".jpg"))
			$o["pics"][] = array('type'=>"espace", 'url'=>URL_MSPP_ESPA.$msID.".jpg");
		break;
		
	case "delpics":
		$msID= isset($_GET["msID"]) ? (int)$_GET["msID"] : "";
		if (empty($msID)) { $o["error"] = "Mini-store ID missing"; break; }
		$o["msID"] = $msID;
		$type = isset($_GET["type"]) ? $_GET["type"] : "";
                if (empty($type)) { $o["error"] = "Image type missing"; break; }
		switch ($type){
                  case 'home':
                    $dirINC = MSPP_HOME;
                    break;
                  case 'vignette':
                    $dirINC = MSPP_VIGN;
                    break;
                  case 'espace':
                    $dirINC = MSPP_ESPA;
                    break;
                }
                @unlink($dirINC.$msID.'.jpg');
		$o["success"] = true;
		
		break;

        case "reorderpics":
          /*$msID= isset($_GET["msID"]) ? (int)$_GET["msID"] : "";
		if (empty($msID)) { $o["error"] = "Product ID missing"; break; }
		$o["msID"] = $msID;
		$type = isset($_GET["type"]) ? $_GET["type"] : "";
		if ($type == "adv")
			$dirINC = PRODUCTS_IMAGE_ADV_INC;
		else
			$dirINC = PRODUCTS_IMAGE_INC;

		$picsOrder = isset($_GET["picsOrder"]) ? explode(",", $_GET["picsOrder"]) : array();
                if(!is_array($picsOrder)){
                  $o["error"] = 'Structure d\'arguments incorrecte';
                  break;
                }
                foreach($picsOrder as $valuePic){
                    $nbrPic = str_replace('pic_nbr', '', $valuePic);
                    if(!is_numeric($nbrPic)){
                      $o["error"] = 'Structure d\'arguments incorrecte';
                      break;
                    }  else
                      $newOrder[] = $nbrPic;
                }
                
                reorderImagesAndProceed($msID, $dirINC,$newOrder);
*/
          break;
		
	
	default:
		$o["error"] = "Action type missing";
		break;
}

mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);

?>