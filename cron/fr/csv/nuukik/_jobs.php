<?php

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$flog = fopen(LOGS."Nuukik_upload_historic.log", "a+");
tlog("SESSION BEGIN\n");

$db = $conn->getDbh();
$path = CSV_PATH."nuukik_";

// files to process
$files = array(
  'products',
  'articles',
  'categories',
  'attributes',
  'orders'
);

// processing each files
foreach ($files as $file) {
  // setting vars
  $csvFilename = $file.".csv";
  $csvPath = $path.$csvFilename;
  $zipFilename = $file.".zip";
  $zipPath = $path.$zipFilename;
  
  // Opening file for write
  tlog("CREATING FILE : ".$csvFilename."\n");
  if ($fh = fopen($csvPath, "w+")) {
    tlog("WRITING TO FILE : ".$csvFilename." ... ");
    include $file.".php";
    tlog("SUCCESS !\n", false);
    
    // closing file
    fclose($fh);
    
    // now zipping
    $zip = new ZipArchive;
    tlog("CREATING ARCHIVE : ".$zipFilename." ... ");
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
      $zip->addFile($csvPath, $csvFilename);
      if ($zip->close()) {
        tlog("SUCCESS !\n", false);
        tlog("DELETING CSV FILE : ".$csvFilename."\n");
        unlink($csvPath);
      }
    } else {
      tlog("ERROR !\n", false);
    }
  } else {
    tlog("ERROR WHILE CREATING FILE\n");
  }
}

if (!DEBUG) {
  // sending after all files in one connexion
  define("FTP_SERVER", 'ftp01.nuukik.com');
  define("FTP_USERNAME", 'technicontact');
  define("FTP_PASS", 'XdnZrs51pRLB2oXyMVFd');

  tlog("CONNECTING TO FTP : ".FTP_SERVER." ... ");
  if ($conn_id = ftp_connect(FTP_SERVER)) {
    tlog("SUCCESS !\n", false);
    
    tlog("LOGGING TO FTP WITH USERNAME : ".FTP_USERNAME." ... ");
    if (@ftp_login($conn_id, FTP_USERNAME, FTP_PASS)) {
      tlog("SUCCESS !\n", false);
      
      foreach ($files as $file) {
        $zipFilename = $file.".zip";
        $zipPath = $path.$zipFilename;

        tlog("UPLOADING FILE: ".$zipFilename." ... ");
        if (ftp_put($conn_id, $zipFilename, $zipPath, FTP_BINARY)) {
          tlog("SUCCESS !\n", false);
        } else {
          tlog("FAILED !\n", false);
        }
      }
      
    } else {
      tlog("FAILED !\n", false);
    }
    
    tlog("CLOSING FTP CONNEXION\n");
    ftp_close($conn_id);

  } else {
    tlog("FAILED !\n", false);
  }
}

tlog("SESSION END\n\n\n");
fclose($flog);
