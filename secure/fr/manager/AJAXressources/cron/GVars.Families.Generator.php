<?php

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = DBHandle::get_instance();

define("CATEGORY", "gvars/");
define("SUB_CATEGORY", "Families.");
define("AJAXDATA_FAMILIES_DATA", SECURE_PATH."manager/AJAXressources/".CATEGORY.SUB_CATEGORY."Data.js");
define("AJAXDATA_FAMILIES_INDEX_ID", SECURE_PATH."manager/AJAXressources/".CATEGORY.SUB_CATEGORY."iID.js");
define("AJAXDATA_FAMILIES_INDEX_NAME", SECURE_PATH."manager/AJAXressources/".CATEGORY.SUB_CATEGORY."iName.js");
define("AJAXDATA_FAMILIES_INDEX_REFNAME", SECURE_PATH."manager/AJAXressources/".CATEGORY.SUB_CATEGORY."iRefName.js");
define("AJAXDATA_FAMILIES_INDEX_PARENTID", SECURE_PATH."manager/AJAXressources/".CATEGORY.SUB_CATEGORY."iParentID.js");

if ($res = $db->query("show table status from `technico` like 'families'", __FILE__, __LINE__)) {
	$status = $db->fetchAssoc($res);

	//print "DB update Time = " . strtotime($status["Update_time"]) . "\n";
	//print FAMILIES_AJAX_SERVER_JS_CACHE . " last mod = " . filemtime(FAMILIES_AJAX_SERVER_JS_CACHE). "\n";

	if (!file_exists(AJAXDATA_FAMILIES_DATA) || filemtime(AJAXDATA_FAMILIES_DATA) < (strtotime($status["Update_time"])+600)) {
		$families = array(
			"Data" => array(),
			"iID" => array(),
			"iName" => array(),
			"iRefName" => array(),
			"iParentID" => array(),
		);
		
		$k = 0;
		// Root Family, necessary to get the main families
		$families["Data"][$k] = array(0,"","",0);
		$families["iID"][0] = $k;
		$k++;
		
		// ID = 0, Name = 1, Ref Name = 2, Parent ID = 3
		$res = $db->query("SELECT f.id, fr.name, fr.ref_name, f.idParent FROM families f, families_fr fr WHERE f.id = fr.id AND f.id <= 11 ORDER BY f.id", __FILE__, __LINE__);
		while ($family = $db->fetchAssoc($res)) {
			$families["Data"][$k] = array($family["id"],$family["name"],$family["ref_name"],$family["idParent"]);
			$families["iID"][$family["id"]] = $k;
			$families["iName"][$family["name"]] = $k;
			$families["iRefName"][$family["ref_name"]] = $k;
			$families["iParentID"][$family["idParent"]][] = $k;
			$k++;
		}
		
		$res = $db->query("SELECT f.id, fr.name, fr.ref_name, f.idParent FROM families f, families_fr fr WHERE f.id = fr.id AND f.id > 11 ORDER BY fr.name", __FILE__, __LINE__);
		while ($family = $db->fetchAssoc($res)) {
			$families["Data"][$k] = array($family["id"],$family["name"],$family["ref_name"],$family["idParent"]);
			$families["iID"][$family["id"]] = $k;
			$families["iName"][$family["name"]] = $k;
			$families["iRefName"][$family["ref_name"]] = $k;
			$families["iParentID"][$family["idParent"]][] = $k;
			$k++;
		}
		
		mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $families);
		
		$oData = json_encode($families["Data"]);
		$oiID = json_encode($families["iID"]);
		$oiName = json_encode($families["iName"]);
		$oiRefName = json_encode($families["iRefName"]);
		$oiParentID = json_encode($families["iParentID"]);
		
		$fh = fopen(AJAXDATA_FAMILIES_DATA, "w+");
		fwrite($fh, $oData);
		fclose($fh);
		
		$fh = fopen(AJAXDATA_FAMILIES_INDEX_ID, "w+");
		fwrite($fh, $oiID);
		fclose($fh);
		
		$fh = fopen(AJAXDATA_FAMILIES_INDEX_NAME, "w+");
		fwrite($fh, $oiName);
		fclose($fh);
		
		$fh = fopen(AJAXDATA_FAMILIES_INDEX_REFNAME, "w+");
		fwrite($fh, $oiRefName);
		fclose($fh);
		
		$fh = fopen(AJAXDATA_FAMILIES_INDEX_PARENTID, "w+");
		fwrite($fh, $oiParentID);
		fclose($fh);
	}
}

?>