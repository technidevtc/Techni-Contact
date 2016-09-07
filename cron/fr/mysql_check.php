<?php
/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 02 novembre 2011

 Fichier : /cron/fr/mysql_check.php
 Description : cron de test de validité des tables de la base de données

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

//error_reporting(0);
//$handle = DBHandle::get_instance();
$handle = DBHandle::get_instance();
$mt = microtime(true);

$res = & $handle->query('SHOW TABLES FROM technico', __FILE__, __LINE__);
while($row = & $handle->fetchAssoc($res))
  $listTables[] = $row;

$send_mail = false;
$content = '';
  $content .= 'Check de la base technico '.PHP_EOL.PHP_EOL;
foreach($listTables as $table){

  if($table['Tables_in_technico'] != 'stats_hit' && $table['Tables_in_technico'] != 'logs' && $table['Tables_in_technico'] != 'exports_products' && $table['Tables_in_technico'] != 'emails_historic'){
    $mt2 = microtime(true);
    $res2 = & $handle->query('CHECK TABLE '.$table['Tables_in_technico'], __FILE__, __LINE__);
    
    // test locks
  //  $res3 = & $handle->query('SHOW OPEN TABLES', __FILE__, __LINE__);
  //  while($row3 = & $handle->fetchAssoc($res3)){
  //    if($row3["Table"] == $table['Tables_in_technico-test']){
  //    $toto[] = $row3;
  //    var_dump($row3);
  //    }
  //    if($row3["In_use"] != 0){
  //      var_dump($row3);
  //      exit();
  //    }
  //
  //  }


    $mt2_end = microtime(true);
    $row2 = & $handle->fetchAssoc($res2);
      $duree_table = ($mt2_end - $mt2);
      $content .=  'Check de la table \''.$table['Tables_in_technico'].'\' en '.$duree_table.' secondes.'.PHP_EOL;
      $content .=  '   => statut : '.$row2['Msg_text'].PHP_EOL;

        if($row2['Msg_text'] != 'OK'){
          $rpr = & $handle->query('REPAIR TABLE '.$table['Tables_in_technico-test'], __FILE__, __LINE__);
          $content .=  '      => la réparation de la table c\'est terminée avec le statut : '.$rpr['Msg_type'].PHP_EOL.'      => '.$rpr['Msg_text'].PHP_EOL;
          $send_mail = true;
        }
      $content .=  PHP_EOL;
  }
}
$mt_end = microtime(true);
$duree_globale = ($mt_end - $mt);

$content .=  'Le traitement global de la vérification des tables a duré '.$duree_globale.' secondes.'.PHP_EOL;

$to = 'olivier@hook-network.com';
$subject = "Check base de donnée du ".date('d-m-Y H:i', time());
$headers = '';
$headers = "Cc: Emmanuel<emmanuel@hook-network.com>\nCc: Frederic<frederic@hook-network.com>\nCc: Tristan<t.henryg@techni-contact.com>\r\n";

if($send_mail){
  mail($to, utf8_encode($subject), $content, $headers);
}


?>
