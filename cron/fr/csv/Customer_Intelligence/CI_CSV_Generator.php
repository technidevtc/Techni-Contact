<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = DBHandle::get_instance();

$historic = true;
$upload = true;

$flog = fopen(CSV_PATH."Customer_Intelligence/CI_upload_historic.log", "a+");
fwrite($flog, date("Y-m-d H:i:s")." SESSION BEGIN\n");

$path_parts = pathinfo(__FILE__);
$files = scandir($path_parts['dirname']);
$files_count = count($files);
for ($i = 0; $i < $files_count; $i++) {
	if ($files[$i] != $path_parts['basename'] && $files[$i] != "." && $files[$i] != "..") {
          include $files[$i];
	}
}


fwrite($flog, date("Y-m-d H:i:s")." SESSION END\n\n");

fclose($flog);

