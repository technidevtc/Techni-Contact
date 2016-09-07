<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

header("Content-Type: text/plain; charset=utf-8");
mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $_GET);

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
		$pdtID = isset($_GET["pdtID"]) ? (int)$_GET["pdtID"] : "";
		if (empty($pdtID)) { $o["error"] = "Product ID missing"; break; }
		$o["pdtID"] = $pdtID;
		$type = isset($_GET["type"]) ? $_GET["type"] : "";
		if ($type == "adv") {
			$dirINC = PRODUCTS_IMAGE_ADV_INC;
			$dirURL = PRODUCTS_IMAGE_ADV_URL;
		}
		else {
			$dirINC = PRODUCTS_IMAGE_INC;
			$dirURL = PRODUCTS_IMAGE_SECURE_URL;
		}
		
		$o["pics"] = array();
		$num = 1;
		while (is_file($dirINC."zoom/".$pdtID."-".$num.".jpg")) {
			$o["pics"][] = array(
				"num" => $num,
				"zoom" => $dirURL."zoom/".$pdtID."-".$num.".jpg",
				"card" => $dirURL."card/".$pdtID."-".$num.".jpg",
				"thumb_big" => $dirURL."thumb_big/".$pdtID."-".$num.".jpg",
				"thumb_small" => $dirURL."thumb_small/".$pdtID."-".$num.".jpg"
			);
			$num++;
		}
		break;
		
	case "delpics":
		$pdtID = isset($_GET["pdtID"]) ? (int)$_GET["pdtID"] : "";
		if (empty($pdtID)) { $o["error"] = "Product ID missing"; break; }
		$o["pdtID"] = $pdtID;
		$type = isset($_GET["type"]) ? $_GET["type"] : "";
		if ($type == "adv")
			$dirINC = PRODUCTS_IMAGE_ADV_INC;
		else
			$dirINC = PRODUCTS_IMAGE_INC;
		
		$num = isset($_GET["num"]) ? explode(",", $_GET["num"]) : array("1");
		foreach($num as $n) {
			deleteImageAndProceed($pdtID, $dirINC, $n);
		}
		$o["success"] = true;
		
		break;

        case "reorderpics":
          $pdtID = isset($_GET["pdtID"]) ? (int)$_GET["pdtID"] : "";
		if (empty($pdtID)) { $o["error"] = "Product ID missing"; break; }
		$o["pdtID"] = $pdtID;
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
                
                reorderImagesAndProceed($pdtID, $dirINC,$newOrder);

          break;
		
	case "getdocs":
		$pdtID = isset($_GET["pdtID"]) ? (int)$_GET["pdtID"] : "";
		if (empty($pdtID)) { $o["error"] = "Product ID missing"; break; }
		$o["pdtID"] = $pdtID;
		$type = isset($_GET["type"]) ? $_GET["type"] : "";
		if ($type == "adv") {
			$dirINC = PRODUCTS_FILES_ADV_INC;
			$dirURL = PRODUCTS_FILES_ADV_URL;
		}
		else {
			$dirINC = PRODUCTS_FILES_INC;
			$dirURL = PRODUCTS_FILES_SECURE_URL;
		}
		
		$o["docs"] = array();
		$res = $db->query("SELECT `docs` FROM products WHERE id = ".$pdtID, __FILE__, __LINE__);
		if ($db->numrows($res, __FILE__, __LINE__) == 0) { $o["error"] = "Bad Product ID"; break; }
		
		// Docs in the DB
		// array( 0 => array(TITLE_1, FILENAME_1), 1 => array(TITLE_2, FILENAME_2), ...)
		$docsDB = $db->fetch($res);
		$docsDB = mb_unserialize($docsDB[0]);
		if (!is_array($docsDB)) $docsDB = array();
		
		// If there is docs in the DB which point to no file, we indicate it
		foreach($docsDB as $k => &$doc) {
			if (!is_file($dirINC.$pdtID."-".($k+1).".pdf"))
				$doc["uploaded"] = 0;
			elseif ($doc["uploaded"] == 0) {
				$doc["uploaded"] = 1;
				$doc["filesize"] = filesize($dirINC.$pdtID."-".($k+1).".pdf");
			}
		}
		unset($doc);
		
		// If there is docs in the FS that aren't in the DB
		$docsFS = array();
		$num = 0;
		while (is_file($dirINC.$pdtID."-".($num+1).".pdf")) {
			if (!isset($docsDB[$num]))
				$docsDB[$num] = array(
					"title" => "Documentation ".($num+1),
					"filename" => "documentation-".($num+1),
					"num" => $num+1,
					"uploaded" => 1,
					"filesize" => filesize($dirINC.$pdtID."-".($num+1).".pdf"));
			$num++;
		}
		
		$o["docs"] = $docsDB;
		
		break;
		
	case "setdocs":
		$pdtID = isset($_GET["pdtID"]) ? (int)$_GET["pdtID"] : "";
		if (empty($pdtID)) { $o["error"] = "Product ID missing"; break; }
		$o["pdtID"] = $pdtID;
		$type = isset($_GET["type"]) ? $_GET["type"] : "";
		if ($type == "adv")
			$dirINC = PRODUCTS_FILES_ADV_INC;
		else
			$dirINC = PRODUCTS_FILES_INC;
		
		// Every doc file satisfying the mask
		$docs2delete = array();
		foreach(glob($dirINC.$pdtID."-*.pdf") as $doc2delete)
			$docs2delete[$doc2delete] = true;
		$docs2rename = array();
		
		$i = 0;
		$o["docs"] = array();
		while (isset($_GET["title".$i])) {
			if (!isset($_GET["filename".$i])
			|| !isset($_GET["num".$i])) { $o["error"] .= "Invalid data on line ".$i."\n"; break; }
			
			$title = $_GET["title".$i];
			if (empty($title)) { $o["error"] .= "Le champ 'Titre' de la ligne ".($i+1)." est vide\n"; break; }
			$filename = Utils::toDashAz09($_GET["filename".$i]);
			if (empty($filename)) { $o["error"] .= "Le champ 'Nom du fichier' de la ligne ".($i+1)." est vide\n"; break; }
			$num = (int)$_GET["num".$i];
			
			// Re-Indexing the DB->FS document index correspondence
			$uploaded = 0;
			$filename_src = $dirINC.$pdtID."-".$num.".pdf";
			$filename_dest = $dirINC.$pdtID."-".($i+1).".pdf";

			if (is_file($filename_src)) {
				$filesize = filesize($filename_src);
				rename($filename_src, $filename_dest.".dest");
				$uploaded = 2;
				$docs2rename[] = $filename_dest;
				// Do not delete, because it doesn't exist anymore
				$docs2delete[$filename_src] = false;
			}
			$num = $i+1;
			
			$o["docs"][$i] = array(
				"title" => $title,
				"filename" => $filename,
				"num" => $num,
				"uploaded" => $uploaded,
				"filesize" => $filesize);
			$i++;
		}
		
		if (empty($o["error"])) {
			// Deleting useless doc files
			foreach($docs2delete as $doc2delete => $delete)
				if ($delete) @unlink($doc2delete);
			// Renaming dest files
			foreach($docs2rename as $doc2rename)
				@rename($doc2rename.".dest", $doc2rename);
			
			if ($type == "adv")
				$db->query("update products_add set `docs` = '".$db->escape(serialize($o["docs"]))."', timestamp = ".time()." where id = ".$pdtID, __FILE__, __LINE__);
			else
				$db->query("update products set `docs` = '".$db->escape(serialize($o["docs"]))."', timestamp = ".time()." where id = ".$pdtID, __FILE__, __LINE__);
			if ($db->affected(__FILE__, __LINE__) != 1) $o["error"] .= "Mysql Fatal Error while updating product's data\n";
			else $o["success"] = true;
		}
		
		break;
	
	default:
		$o["error"] = "Action type missing";
		break;
}

mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);

?>