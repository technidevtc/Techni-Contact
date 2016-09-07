<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$db = DBHandle::get_instance();

require_once("Spreadsheet/Excel/Writer.php");
$file = "export_familles_".date("d-m-Y_H-i-s").'.xls';
$workbook = new Spreadsheet_Excel_Writer();
$workbook->setVersion(8);
$worksheet =& $workbook->addWorksheet('Liste des familles');

// $worksheet->setInputEncoding('ISO-8859-7');
// $worksheet->setOutputEncoding('CP1251');;

    $format_top =& $workbook->addFormat();
    $format_top->setAlign('top');
    $format_top->setTextWrap(1);

	$format_center =& $workbook->addFormat();
	$format_center->setAlign('center');
	
	$worksheet->write(0, 0, 'Id');
	$worksheet->write(0, 1, 'Nom de la famille');
	$worksheet->write(0, 2, 'Statut');
	$worksheet->write(0, 3, 'Eligible');
	//$worksheet->setInputEncoding('utf-8');
	$worksheet->write(0, 4, 'Date de création');
	$worksheet->write(0, 5, 'Date de modification');
	
	
	$sql_export = "SELECT fr.id,fr.name ,create_timestamp ,blocked_activated ,update_timestamp,eligible
				   FROM families_fr fr , families_leads_blocked_for_tc_sales ff
				   WHERE fr.id = ff.idFamily
				   ";
	$req_export =  mysql_query($sql_export);
	$i =0;
	while($data_export = mysql_fetch_object($req_export)){
		
		$date_create = date('d/m/Y H:i', strtotime($data_export->create_timestamp));
		
		$worksheet->write($i, 0, utf8_decode($data_export->id));
		
		$worksheet->write($i, 1, $data_export->name);
			
			if($data_export->blocked_activated == 1) $statut = "Activé";
			else $statut = "Désactivé ";
			
			if($data_export->update_timestamp == "0000-00-00 00:00:00") $date_update = " - ";
			else $date_update = date("d/m/Y H:i:s", strtotime($data_export->update_timestamp));
			
			if($data_export->eligible == 1) $Eligible = "Oui";
			else $Eligible = "Non";
		

		$worksheet->setInputEncoding('utf-8');
		$worksheet->write($i, 2, utf8_encode($statut));
		$worksheet->write($i, 3, utf8_decode($Eligible));
		$worksheet->write($i, 4, utf8_decode($date_create));		
		$worksheet->write($i, 5, utf8_decode($date_update));		
	$i++;	
	unset($tableau);
	}	
	
	$workbook->send($file);
	$workbook->close();	
?>