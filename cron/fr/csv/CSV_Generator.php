<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = DBHandle::get_instance();

$historic = false;
$upload = false;

$flog = fopen(CSV_PATH."Probance_upload_historic.log", "a+");
fwrite($flog, date("Y-m-d H:i:s")." SESSION BEGIN\n");

$path_parts = pathinfo(__FILE__);
$files = scandir($path_parts['dirname']);
$files_count = count($files);
for ($i = 0; $i < $files_count; $i++) {
	if ($files[$i] != $path_parts['basename'] && $files[$i] != "." && $files[$i] != "..") {
		include $files[$i];
	}
}

/* Probance specific */

if ($upload) {

	$files = scandir(CSV_PATH."upload/");
	$files_count = count($files);
	$ftp_server = "pdm-techni-contact.com";
	$ftp_user_name = "upload_technicontact";
	$ftp_user_pass = "vowfyerf/";
	/*$ftp_server = "data.probance.com";
	$ftp_user_name = "upload_technicontact";
	$ftp_user_pass = "IddEamowk:orHab7";*/
	/*$ftp_server = "preprod3.hook-network.com";
	$ftp_user_name = "preprod3";
	$ftp_user_pass = "HOOKOOH!33#";*/

	$global_fail = false;
	$conn_id = ftp_connect($ftp_server);
	if ($conn_id !== false) {
		$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
		if ($login_result !== false) {
			if (ftp_chdir($conn_id, "upload")) {
				for ($i = 0; $i < $files_count; $i++) {
					if ($files[$i] != "." && $files[$i] != "..") {
							fwrite($flog, date("Y-m-d H:i:s")." UPLOADING FILE : ".CSV_PATH."upload/".$files[$i]."\n");
						if (ftp_put($conn_id, $files[$i], CSV_PATH."upload/".$files[$i], FTP_BINARY)) {
							fwrite($flog, date("Y-m-d H:i:s")." MOVING FILE : ".CSV_PATH."upload/".$files[$i]." TO ".CSV_PATH.$files[$i]."\n");
							rename(CSV_PATH."upload/".$files[$i], CSV_PATH.$files[$i]);
						} else {
							fwrite($flog, date("Y-m-d H:i:s")." -> FAILED UPLOADING FILE : ".CSV_PATH."upload/".$files[$i]." renamed to ".CSV_PATH."upload/".$files[$i].".failed\n");
							rename(CSV_PATH."upload/".$files[$i], CSV_PATH."upload/".$files[$i].".failed");
						}
					}
				}
			}
			else {
				fwrite($flog, date("Y-m-d H:i:s")." -> FAILED : chdir to upload\n");
				$global_fail = true;
			}
		}
		else {
			fwrite($flog, date("Y-m-d H:i:s")." -> FAILED : login\n");
			$global_fail = true;
		}
		ftp_close($conn_id);
	}
	else {
		fwrite($flog, date("Y-m-d H:i:s")." -> FAILED : Connection\n");
		$global_fail = true;
	}

	if ($global_fail) {
		fwrite($flog, date("Y-m-d H:i:s")." -> FAILED : Global Upload\n");
		for ($i = 0; $i < $files_count; $i++) {
			if ($files[$i] != "." && $files[$i] != "..") {
					fwrite($flog, date("Y-m-d H:i:s")."    Renaming ".CSV_PATH."upload/".$files[$i]." to ".CSV_PATH."upload/".$files[$i].".failed\n");
					rename(CSV_PATH."upload/".$files[$i], CSV_PATH."upload/".$files[$i].".failed");
			}
		}
	}
}
else {
	fwrite($flog, date("Y-m-d H:i:s")." NO UPLOAD\n");
	$files = scandir(CSV_PATH."probance/");
	$files_count = count($files);
  $today = date("Ymd");
  for ($i=0; $i<$files_count; $i++) {
    if (is_file(CSV_PATH."probance/".$files[$i])) {
      list($date, $datatype) = explode("_",$files[$i],2);
      if ($date != $today) {
        fwrite($flog, date("Y-m-d H:i:s")." MOVING FILE : ".CSV_PATH."probance/".$files[$i]." TO ".CSV_PATH."probance_historic/".$files[$i]."\n");
        rename(CSV_PATH."probance/".$files[$i], CSV_PATH."probance_historic/".$files[$i]);
      }
    }
  }
}

fwrite($flog, date("Y-m-d H:i:s")." SESSION END\n\n");

fclose($flog);
