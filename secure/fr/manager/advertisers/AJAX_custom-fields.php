<?php

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

header("Content-Type: text/plain; charset=utf-8");
mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $_GET);

require_once(ADMIN."logs.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

if (!$user->login()) {
	$o["error"] = "Votre session a expirée, veuillez vous identifier à nouveau après avoir rafraichi votre page";
	print json_encode($o);
	exit();
}
$typeCheck = array("text", "select", "textarea");
$validationTypeCheck = array("none", "integer", "date", "email", "url");
$nameCheck = array("type", "nom", "prenom", "fonction", "societe", "salaries", "secteur", "naf", "siret", "adresse", "cadresse", "cp", "ville", "pays", "tel", "fax", "email", "url", "infos_sup", "precisions", "gen", "cread");

$db = DBHandle::get_instance();
$o = array("data" => array(), "error" => "");

$advID = (int)$_GET["advID"];
if (!$advID) {
	$o["error"] .= "Advertiser ID is missing";
}
else {
	switch($_GET["action"]) {
		case "get" :
			$res = $db->query("select customFields from advertisers where id = ".$advID, __FILE__, __LINE__);
			if ($db->numrows($res, __FILE__, __LINE__) != 1) $o["error"] .= "Mysql Fatal Error while loading advertiser's data\n";
			else {
				list($customFields) = $db->fetch($res);
				$o["data"] = mb_unserialize($customFields);
			}
			break;
			
		case "set":
			$i = 0;
			while (isset($_GET["name".$i])) {
				if (!isset($_GET["label".$i])
				|| !isset($_GET["type".$i])
				|| !isset($_GET["required".$i])
				|| !isset($_GET["valueList".$i])
				|| !isset($_GET["valueDefault".$i])
				|| !isset($_GET["validationType".$i])
				|| !isset($_GET["length".$i])) { $o["error"] .= "Invalid data on line ".$i."\n"; break; }
				
				$name = Utils::toDashAz09($_GET["name".$i]);
				if (empty($name)) { $o["error"] .= "Le champ 'nom' de la ligne ".($i+1)." est vide\n"; break; }
				if (in_array($name, $nameCheck)) { $o["error"] .= "Le champ 'nom' de la ligne ".($i+1)." existe déjà parmi les champs prédéfinis\n"; break; }
				$label = $_GET["label".$i];
				if (empty($label)) { $o["error"] .= "Le champ 'libellé' de la ligne ".($i+1)." est vide\n"; break; }
				$type = $_GET["type".$i];
				if (!in_array($type, $typeCheck)) { $o["error"] .= "Mauvais type de champ à la ligne ".($i+1)."\n"; break; }
				$required = $_GET["required".$i];
				if ($required != 0) $required = 1;
				$valueList = explode(",", $_GET["valueList".$i]);
				foreach($valueList as &$value) { $value = trim($value); }
				$valueDefault = $_GET["valueDefault".$i];
				if ($type == "select" && !empty($valueDefault) && !in_array($valueDefault, $valueList)) { $o["error"] .= "La valeur par défaut saisie à la ligne ".($i+1)." n'existe pas dans la liste de sélection précédente\n"; break; }
				$validationType = $_GET["validationType".$i];
				if (!in_array($validationType, $validationTypeCheck)) { $o["error"] .= "Mauvais type de validation à la ligne ".($i+1)."\n"; break; }
				$length = (int)$_GET["length".$i];
				
				$o["data"][$i] = array(
					"name" => $name,
					"label" => $label,
					"type" => $type,
					"required" => $required,
					"valueList" => implode(",", $valueList),
					"valueDefault" => $valueDefault,
					"validationType" => $validationType,
					"length" => $length);
				$i++;
			}
			//if (empty($o["data"])) $o["error"] .= "Aucune donnée valide n'a été trouvé\n";
			if (empty($o["error"])) {
				$db->query("update advertisers set customFields = '".$db->escape(serialize($o["data"]))."', timestamp = ".time()." where id = ".$advID, __FILE__, __LINE__);
				if ($db->affected(__FILE__, __LINE__) != 1) $o["error"] .= "Mysql Fatal Error while updating advertiser's data\n";
			}
			
			break;
			
		default:
			$o["error"] .= "Action type is missing";
			break;
	}
}
mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);

?>