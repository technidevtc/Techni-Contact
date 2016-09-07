  <?php
  require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
  
  require_once('../../../../includes/fr/classV3/PHPExcel/Excel_r/oleread.inc');
  require_once('../../../../includes/fr/classV3/PHPExcel/Excel_r/reader.php');

  $db = DBHandle::get_instance();
  
  $file_full_path = "All-Unjoin-Blocked3.xls";
  
  $data = new Spreadsheet_Excel_Reader($file_full_path, false);
  $data->setOutputEncoding('CP1251');
  $data->read($file_full_path);
	 	
  $timestamp 	  = filemtime ($file_full_path );
  
  $count_files  = count($data->sheets[0]["cells"]);
  echo $count_files;
	for ($x = 1; $x <= $count_files; $x++ ) {
		$email = $data->sheets[0]["cells"][$x][2];
		if($email != 'EMAIL'){
			$sql_insert = "INSERT INTO `marketing_base_emails` (
							`id` ,
							`email` ,
							`motif` ,
							`disable_source` ,
							`etat` ,
							`date_insert` ,
							`date_last_edit` ,
							`id_user_add` ,
							`id_user_edit`
							)VALUES (NULL ,  '$email',  '0',  'robot',  'ko', NOW() ,  '0000-00-00 00:00:00',  '0',  '0')";
			mysql_query($sql_insert);
		}
	}
  
  
  
  
  ?>
  