<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$flog = fopen(LOGS."Avis_Verifies_notification_historic.log", "a+");
tlog("SESSION BEGIN\n");

$curMonth = date('m');
$curDay = date('j');
$timeStart = mktime(0,0,0,$curMonth,$curDay-1);
//$timeStart = 1399300926-86400*40;
$timeEnd = mktime(0,0,0,$curMonth,$curDay)-1;

tlog("GETTING ORDERS FOR : ".date('Y-m-d', $timeStart)." ... ");
// only yesterday's validated orders by companies, filtering out those cancelled or with ASS opened
$oc = Doctrine_Query::create()
  ->select('o.id, o.email, o.nom, o.prenom, o.created, o.validated, ol.pdt_ref_id, ol.desc')
  ->from('Order o')
  ->leftJoin('o.lines ol')
  ->where('o.fonction NOT LIKE ?', '%particulier%')
  ->andWhere('o.validated >= ? AND o.validated <= ?', array($timeStart, $timeEnd))
  ->andWhere('o.sav_opened = 0 OR o.sav_closed > 0')
  ->andWhere('o.cancelled = 0 AND o.partly_cancelled = 0')
  ->orderBy('o.validated ASC')
  ->fetchArray();
tlog("SUCCESS ! ".count($oc)." ORDER(S) FOUND\n", false);

foreach ($oc as $o) {
  tlog("SENDING ORDER ".$o['id']." ... ");
  $q = array(
    'query' => 'pushCommandeSHA1',
    'refCommande' => $o['id'],
    'email' => $o['email'],
    'nom' => empty($o['nom']) ? '-' : $o['nom'],
    'prenom' => empty($o['prenom']) ? '-' : $o['prenom'],
    'dateCommande' => date('Y-m-d H:i:s', $o['validated']),
    'delaiSendAvis' => AVIS_VERIFIES_DELAI_AVANT_EMISSION_AVIS
  );

  $q['sign'] = SHA1(implode('', $q).AVIS_VERIFIES_SECURE_KEY);
  
  foreach ($o['lines'] as $line)
    $q['PRODUITS'][] = array('RefProduit' => $line['pdt_ref_id'], 'description' => $line['desc']);
  
  $encryptedNotification = http_build_query(array(
    'idWebsite' => AVIS_VERIFIES_ID_WEBSITE,
    'message' => AC_encode_base64(serialize($q))
  ));
  $postCommande = array(
    'http' => array(
      'method' => 'POST',
      'header' => 'Content-type: application/x-www-form-urlencoded',
      'content' => $encryptedNotification
    )
  );
  $contextCommande = stream_context_create($postCommande);
  $message = file_get_contents(AVIS_VERIFIES_URL.'?action=act_api_notification_sha1', false, $contextCommande);
  $message = unserialize(AC_decode_base64($message));
  switch ((int)$message['return']) {
    case 1: $messageResult = "SUCCESS !"; break;
    case 2: $messageResult = "FAILURE : BAD SIGNATURE"; break;
    case 3: $messageResult = "FAILURE : CLIENT ACCOUNT NOT RECOGNIZED"; break;
    case 4: $messageResult = "FAILURE : WRONG PARAMETERS"; break;
    default : $messageResult = "FAILURE : UNEXPECTED ERROR";
  }
  tlog($messageResult."\n", false);
}

tlog("SESSION END\n\n\n");
fclose($flog);
