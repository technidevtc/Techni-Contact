<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = $conn->getDbh();

$partnerId = filter_input(INPUT_GET, 'partnerid', FILTER_SANITIZE_NUMBER_INT);
$from = filter_input(INPUT_GET, 'from', FILTER_SANITIZE_NUMBER_INT);
$to = filter_input(INPUT_GET, 'to', FILTER_SANITIZE_NUMBER_INT);
$hash = filter_input(INPUT_GET, 'hash', FILTER_SANITIZE_URL);
//$timestamp = filter_input(INPUT_GET, 'timestamp', FILTER_SANITIZE_NUMBER_INT);

if (empty($partnerId) || empty($hash)) {
  echo json_encode(array("error" => "Malformed url"));
  exit();
}

switch ($partnerId) {
  case 35560: $authToken = 'xtg5WmLF4azjtpXrQC5YF2SNOigCHYy7'; break;
  default:
    echo json_encode(array("error" => "Unrecognized partnerid"));
    exit();
}
$requestSignature = URL.'api/'.basename(__FILE__).'?partnerid='.$partnerId.($from?'&from='.$from:'').($to?'&to='.$to:'');
$requestSignatureHash = base64_encode(hash_hmac("sha256", $requestSignature, $authToken, true));
$protectedRequestSignatureHash = str_replace(array('+', '/', '='), array('-', '_', ), $requestSignatureHash);

if ($protectedRequestSignatureHash === $hash) {
  if (!empty($from) && preg_match('/^(\d{4})(\d{2})(\d{2})$/', $from, $from)) {
    $tStart = mktime(0,0,0,$from[2],$from[3],$from[1]);
  } else {
    $tStart = 0;
  }
  $contractStart = mktime(0,0,0,1,27,2014);
  if ($tStart < $contractStart)
    $tStart = $contractStart;

  if (!empty($to) && preg_match('/^(\d{4})(\d{2})(\d{2})$/', $to, $to)) {
    $tEnd = mktime(0,0,0,$to[2],$to[3],$to[1]);
  } else {
    $tEnd = time();
  }

  $sth = $db->prepare("
    SELECT
      l.tel,
      l.email,
      l.nom,
      l.prenom,
      l.fonction,
      l.societe,
      l.adresse,
      l.cp,
      l.ville,
      l.pays,
      l.secteur,
      l.precisions
    FROM contacts l
    WHERE l.idAdvertiser = :id AND l.timestamp >= :tstart AND l.timestamp < :tend
  ");
  $sth->execute(array(':id' => $partnerId, ':tstart' => $tStart, ':tend' => $tEnd));
  $leadList = $sth->fetchAll(PDO::FETCH_ASSOC);
  
  echo json_encode($leadList);
  
} else {
  echo json_encode(array("error" => "Bad hash"));
}
