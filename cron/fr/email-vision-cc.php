<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
error_reporting(E_ALL & ~E_NOTICE);

$flog = fopen(LOGS."EmailVision_upload_historic.log", "a+");
tlog("SESSION BEGIN\n");

$login = "thg_iphone";
$pwd = "@@@@@md2ibatchapi";
$key = "CdX7CrVS5VawmFBVX8wq0bejKjUead67hEGuBNdSKbO9Lk-fnzyzwNpn_w";
$batchMemberWsdlUrl = "http://emvapi.emv3.com/apibatchmember/services/BatchMemberService?wsdl";

$historic = false;
$cols = array(
  'FIRSTNAME' => array('value' => 'client.prenom', 'format' => 'Text 64'),
  'LASTNAME' => array('value' => 'client.nom', 'format' => 'Text 64'),
  'EMAIL_ORIGINE' => array('value' => '', 'format' => 'Text 255'),
  'EMAIL' => array('value' => 'client.email', 'format' => 'Text 255'),
  'EMVCELLPHONE' => array('value' => '', 'format' => 'Number 38'),
  'EMAIL_FORMAT' => array('value' => '', 'format' => 'Number 1'),
  'TITLE' => array('value' => 'CUSTOM', 'format' => 'Text 24'),
  'DATEOFBIRTH' => array('value' => '', 'format' => 'Date'),
  'SEED' => array('value' => '', 'format' => 'Number 1'),
  'CLIENTURN' => array('value' => '', 'format' => 'Text 24'),
  'SOURCE' => array('value' => '', 'format' => 'Text 24'),
  'CUSTOMER_ID' => array('value' => 'client.id', 'format' => 'Number 10'),
  'SEGMENT' => array('value' => '', 'format' => 'Text 64'),
  'EMVADMIN1' => array('value' => '', 'format' => 'Text 255'),
  'EMVADMIN2' => array('value' => '', 'format' => 'Text 255'),
  'EMVADMIN3' => array('value' => '', 'format' => 'Text 255'),
  'EMVADMIN4' => array('value' => '', 'format' => 'Text 255'),
  'EMVADMIN5' => array('value' => '', 'format' => 'Date'),
  'SOCIETE' => array('value' => 'client.societe', 'format' => 'Text 255'),
  'ADRESSE' => array('value' => 'client.adresse', 'format' => 'Text 255'),
  'CADRESSE' => array('value' => 'client.complement', 'format' => 'Text 255'),
  'CP' => array('value' => 'client.cp', 'format' => 'Text 255'),
  'VILLE' => array('value' => 'client.ville', 'format' => 'Text 255'),
  'PAYS' => array('value' => 'client.pays', 'format' => 'Text 255'),
  'TEL' => array('value' => 'client.tel1', 'format' => 'Text 255'),
  'FAX' => array('value' => 'client.fax1', 'format' => 'Text 255'),
  'FONCTION' => array('value' => 'client.fonction', 'format' => 'Text 255'),
  'SIRET' => array('value' => 'client.num_siret', 'format' => 'Text 255'),
  'NAF' => array('value' => 'client.code_naf', 'format' => 'Text 255'),
  'SALARIES' => array('value' => 'client.nb_salarie', 'format' => 'Text 255'),
  'SECTEUR' => array('value' => 'client.secteur_activite', 'format' => 'Text 255'),
  'SECTEUR_QUALIFIE' => array('value' => 'client.secteur_qualifie', 'format' => 'Text 255'),
  'DATE_CREATION_CLIENT' => array('value' => 'client.timestamp', 'format' => 'Date'),
  'LAST_LEAD_ID' => array('value' => 'CUSTOM', 'format' => 'Text 255'),
  'LAST_ORDER_ID' => array('value' => 'CUSTOM', 'format' => 'Text 255'),
  'LAST_QUOTE_ID' => array('value' => 'CUSTOM', 'format' => 'Text 255'),
  'LAST_LEAD_DATE' => array('value' => 'CUSTOM', 'format' => 'Date'),
  'LAST_ORDER_DATE' => array('value' => 'CUSTOM', 'format' => 'Date'),
  'TYPE_DEMANDE' => array('value' => 'CUSTOM', 'format' => 'Text 255'),
  'LAST_QUOTE_DATE' => array('value' => 'CUSTOM', 'format' => 'Date'),
  'DATE_ENVOI_PDM' => array('value' => '', 'format' => 'Date'),
  'SENDINGID_PDM' => array('value' => '', 'format' => 'Text 30'),
  'MESSAGEID_PDM' => array('value' => '', 'format' => 'Text 30'),
  'CIVILITE_PDM' => array('value' => 'CUSTOM', 'format' => 'Text 30'),
  'NOM_PDM' => array('value' => 'client.nom_l', 'format' => 'Text 50'),
  'PRENOM_PDM' => array('value' => 'client.prenom_l', 'format' => 'Text 50'),
  'SOCIETE_PDM' => array('value' => 'client.societe_l', 'format' => 'Text 50'),
  'ADRESSE_PDM' => array('value' => 'client.adresse_l', 'format' => 'Text 255'),
  'COMPLEMENT_ADRESSE_PDM' => array('value' => 'client.complement_l', 'format' => 'Text 255'),
  'VILLE_PDM' => array('value' => 'client.ville_l', 'format' => 'Text 50'),
  'CP_PDM' => array('value' => 'client.cp_l', 'format' => 'Text 50'),
  'PAYS_PDM' => array('value' => 'client.pays_l', 'format' => 'Text 50'),
  'TEL1_PDM' => array('value' => 'client.tel2', 'format' => 'Text 50'),
  'FAX1_PDM' => array('value' => 'client.fax2', 'format' => 'Text 50'),
  'DATE_DERNIER_DEVIS_PDM' => array('value' => '', 'format' => 'Date'),
  'DATE_DERNIERE_COMMANDE_PDM' => array('value' => '', 'format' => 'Date'),
  'DATE_DERNIERE_ACTION_PDM' => array('value' => 'CUSTOM', 'format' => 'Date'),
  'ANCIENNETE_PDM' => array('value' => '', 'format' => 'Number 11'),
  'MONTANT_DERNIERE_COMMANDE_PDM' => array('value' => 'CUSTOM', 'format' => 'Number 10.2'),
  'MONTANT_DERNIER_DEVIS_PDM' => array('value' => 'CUSTOM', 'format' => 'Number 10.2'),
  'MONTANT_TOTAL_COMMANDE_PDM' => array('value' => 'CUSTOM', 'format' => 'Number 10.2'),
  'NOMBRE_DEVIS_PDM' => array('value' => 'CUSTOM', 'format' => 'Number 11'),
  'NOMBRE_COMMANDE_PDM' => array('value' => 'CUSTOM', 'format' => 'Number 11'),
  'UNIVERS_DERNIER_DEVIS_PDM' => array('value' => '', 'format' => 'Text 50'),
  'UNIVERS_DERNIERE_COMMANDE_PDM' => array('value' => '', 'format' => 'Text 50'),
  'IDFICHE_DERNIER_DEVIS_PDM' => array('value' => '', 'format' => 'Text 50'),
  'IDTC_DERNIERE_COMMANDE' => array('value' => '', 'format' => 'Text 50'),
  'NOM_PRODUIT_DERNIER_DEVIS_PDM' => array('value' => '', 'format' => 'Text 50'),
  'NOM_PRODUIT_DERNIERE_CMD_PDM' => array('value' => '', 'format' => 'Text 50'),
  'SYNCHRO_INSERT' => array('value' => '', 'format' => 'Date'),
  'SYNCHRO_UPDATE' => array('value' => '', 'format' => 'Date'),
  'SYNCHRO_UNJOIN' => array('value' => '', 'format' => 'Date'),
  'COMMERCIAL_DEVIS' => array('value' => 'CUSTOM', 'format' => 'Text 60'),
  'TYPE_PARTENAIRE' => array('value' => 'CUSTOM', 'format' => 'Text 60'),
  'CLE_DEVIS' => array('value' => 'CUSTOM', 'format' => 'Text 32')
);

if ($historic) { // when historic, do multiple requests using the client id as main index (more stable than the timestamp due to big client imports)
  tlog("HISTORIC MODE ON\n");
  $max_range = 0xffffffff;
  $range_s = 0;
  $range_e = round($max_range / 1000);
  $target_ccc = 10000; // target client collection count

} else { // else, do a 1st query to get all the client id's with a recent activity
  $range_e = mktime(0,0,0);
  $range_s = $range_e - 86400;
  $max_range = $range_e;
  $idc = array();
  tlog("GETTING LATEST UPDATED CLIENTS IDS\n");
  $idcc = Doctrine_Query::create()
    ->select('id')
    ->from('Clients')
    ->where('timestamp >= ? AND timestamp < ?', array($range_s, $range_e))
    ->andWhere('login <> \'\'')
    ->andWhere('login <> \'info@techni-contact.com\'')
    ->execute(array(), Doctrine_Core::HYDRATE_NONE);
  tlog("GETTING CLIENTS IDS WITH RECENT LEADS\n");
  $idcl = Doctrine_Query::create()
    ->select('c.id AS id')
    ->from('Contacts l')
    ->innerJoin('l.client c')
    ->where('l.timestamp >= ? AND l.timestamp < ?', array($range_s, $range_e))
    ->andWhere('l.email <> \'\'')
    ->andWhere('l.email <> \'info@techni-contact.com\'')
    ->execute(array(), Doctrine_Core::HYDRATE_NONE);
  tlog("GETTING CLIENTS IDS WITH RECENT ESTIMATES\n");
  $idce = Doctrine_Query::create()
    ->select('c.id')
    ->from('Estimate e')
    ->innerJoin('e.client c')
    ->where('e.created >= ? AND e.created < ?', array($range_s, $range_e))
    ->andWhere('c.login <> \'\'')
    ->andWhere('c.login <> \'info@techni-contact.com\'')
    ->execute(array(), Doctrine_Core::HYDRATE_NONE);
  tlog("GETTING CLIENTS IDS WITH RECENT ORDERS\n");
  $idco = Doctrine_Query::create()
    ->select('c.id')
    ->from('Order o')
    ->innerJoin('o.client c')
    ->where('o.created >= ? AND o.created < ?', array($range_s, $range_e))
    ->andWhere('c.login <> \'\'')
    ->andWhere('c.login <> \'info@techni-contact.com\'')
    ->execute(array(), Doctrine_Core::HYDRATE_NONE);
  $idch = array_merge($idcc,$idcl,$idce,$idco);
  foreach ($idch as $id)
    $idc[] = $id[0];
  $idc = array_unique($idc);
  
  if (empty($idc)) {
    tlog("NOTHING TO UPLOAD\n");
    tlog("SESSION END\n\n\n");
    exit();
  }
}

while ($range_e <= $max_range) {
  set_time_limit(60);
  $q = Doctrine_Query::create()
    ->select('c.id,
              c.timestamp,
              IF(c.titre, c.titre, 1) AS titre,
              c.nom,
              c.prenom,
              c.societe,
              c.adresse,
              c.complement,
              c.cp,
              c.ville,
              c.pays,
              c.tel1,
              c.fax1,
              c.fonction,
              c.num_siret,
              c.code_naf,
              c.nb_salarie,
              c.secteur_activite,
              c.secteur_qualifie,
              c.titre_l,
              c.nom_l,
              c.prenom_l,
              c.societe_l,
              c.adresse_l,
              c.complement_l,
              c.cp_l,
              c.ville_l,
              c.pays_l,
              c.tel2,
              c.fax2,
              c.email,
              l.id,
              l.timestamp,
              IF(0,l.id,la.category) AS l_a_cat,
              e.id,
              e.created,
              e.total_ht,
              IF(0,e.id,ecu.name) AS e_cu_name,
              o.id,
              o.created,
              o.total_ht,
              a.category AS a_cat')
    ->from('Clients c')
    ->leftJoin('c.leads l ON c.login = l.email AND l.parent = 0')
    ->leftJoin('l.advertiser la')
    ->leftJoin('c.estimates e')
    ->leftJoin('e.created_user ecu')
    ->leftJoin('c.orders o')
    ->leftJoin('c.advertiser a');
  if ($historic) {
    $q->where('c.id > ? AND c.id <= ?', array($range_s, $range_e))
      ->andWhere('c.login <> \'\'')
      ->andWhere('c.login <> \'info@techni-contact.com\'');
    tlog("GETTING CLIENTS COMPLETE INFOS FROM ".$range_s." TO ".$range_e."\n");
    //flog(array($range_s, $range_e));
  } else {
    $q->whereIn('c.id', $idc);
    tlog("GETTING CLIENTS COMPLETE INFOS\n");
    //flog($idc);
  }
  $q->orderBy('c.timestamp ASC, o.created DESC, e.created DESC');
  //flog($q->getSqlQuery());
  $cc = $q->fetchArray();
  
  if (count($cc) > 0) {
    tlog("OPENING PHP TMP\n");
    $os = fopen("php://temp", 'r+');
    tlog("WRITING COLUMN HEADERS\n");
    fputcsv($os, array_keys($cols), ';', '"');
    tlog("WRITING CSV TO PHP TMP\n");
    
    foreach ($cc as $c) {
      $values = array();
      $last_l = $c['leads'][0];
      $last_e = $c['estimates'][0];
      $last_o = $c['orders'][0];
      
      $last_action_type = null;
      if ($last_l['timestamp'] > $last_e['created']) {
        if ($last_l['timestamp'] > $last_o['created'])
          $last_action_type = 'l';
        elseif ($last_o['created'])
          $last_action_type = 'o';
      } else {
        if ($last_e['created'] > $last_o['created'])
          $last_action_type = 'e';
        elseif ($last_o['created'])
          $last_action_type = 'o';
      }
      
      foreach ($cols as $cn => $cs) {
        list($ftype, $fval) = explode(" ", $cs['format'], 2);
        $fval = $fval * 1; // cast to integer or float depending on the presence of a '.'
        $col_val_tree = preg_split('/(?<!\\\)\./',$cs['value']); // col value tree : ignore escaped dot (\.)
        $val = "";
        if (count($col_val_tree) > 1) {
          if ($col_val_tree[0] == 'client')
            array_shift($col_val_tree);
          $val = $c;
          foreach ($col_val_tree as $val_tree)
            $val = $val[$val_tree];
          $val = utf8_decode($val);
        } else if ($cs['value'] == 'CUSTOM') {
          switch ($cn) {
            case 'TITLE':
              $val = Clients::getTitleText($c['titre'] ? $c['titre'] : 1);
              break;
            case 'LAST_LEAD_ID':
              $val = $last_l ? $last_l['id'] : "";
              break;
            case 'LAST_ORDER_ID':
              $val = $last_o ? $last_o['id'] : "";
              break;
            case 'LAST_QUOTE_ID':
              $val = $last_e ? $last_e['id'] : "";
              break;
            case 'LAST_LEAD_DATE':
              $val = $last_l ? $last_l['timestamp'] : "";
              break;
            case 'LAST_ORDER_DATE':
              $val = $last_o ? $last_o['created'] : "";
              break;
            case 'TYPE_DEMANDE':
              switch ($last_action_type) {
                case 'l': $val = 'Lead '.($last_l['l_a_cat'] == __ADV_CAT_SUPPLIER__ ? 'fournisseur' : 'annonceur'); break;
                case 'e': $val = 'Devis Techni-Contact manager'; break;
                case 'o': $val = 'Commande'; break;
              }
              break;
            case 'LAST_QUOTE_DATE':
              $val = $last_e ? $last_e['created'] : "";
              break;
            case 'CIVILITE_PDM':
              $val = $c['titre_l'] != '' ? Clients::getTitleText($c['titre_l'] ? $c['titre_l'] : 1) : "";
              break;
            case 'DATE_DERNIERE_ACTION_PDM':
              switch ($last_action_type) {
                case 'l': $val = $last_l['timestamp']; break;
                case 'e': $val = $last_e['created']; break;
                case 'o': $val = $last_o['created']; break;
              }
              break;
            case 'MONTANT_DERNIERE_COMMANDE_PDM':
              $val = $last_o ? $last_o['total_ht'] : "";
              break;
            case 'MONTANT_DERNIER_DEVIS_PDM':
              $val = $last_e ? $last_e['total_ht'] : "";
              break;
            case 'MONTANT_TOTAL_COMMANDE_PDM':
              if (!empty($c['orders'])) {
                $val = 0;
                foreach ($c['orders'] as $order)
                  $val += $order['total_ht'];
              }
              break;
            case 'NOMBRE_DEVIS_PDM':
              $val = count($c['estimates']);
              break;
            case 'NOMBRE_COMMANDE_PDM':
              $val = count($c['orders']);
              break;
            case 'COMMERCIAL_DEVIS':
              $val = $last_e['e_cu_name'] ? $last_e['e_cu_name'] : "";
              break;
            case 'TYPE_PARTENAIRE':
              $val = $c['a_cat'] ? $adv_cat_list[(int)$c['a_cat']]['name'] : "";
              break;
            case 'CLE_DEVIS':
              $val = $last_e ? $last_e['id'] : "";
              break;
            default:
              $val = $cs['value'];
          }
        } else {
          $val = $cs['value'];
        }
        
        switch (strtolower($ftype)) {
          case 'text':
            if ($fval)
              $val = substr($val, 0, $fval);
            break;
          case 'number':
            $val = preg_replace('`\D`', '', $val);
            if (is_numeric($val) && $fval) {
              if (is_int($fval)) {
                $val = substr($val, 0, $fval);
              } elseif (is_float($fval)) {
                $int = (int)$fval;
                $dec = ($fval - $int)*10;
                $val = substr($val, 0, $int + $dec + 1);
                $val = sprintf('%.'.$dec.'f', $val);
              }
            }
            break;
          case 'date':
            if (is_numeric($val))
              $val = date('Y-m-d H:i:s', $val);
            break;
        }
        
        $values[] = $val;
      }
      fputcsv($os, $values, ';', '"');
    }
    
    tlog("WRITING PHP TMP CSV TO STRING\n");
    $csv = "";
    rewind($os);
    while (($line = fgets($os)) !== false)
      $csv .= $line;
    if (!feof($os))
      exit();
    fclose($os);
    
    /*tlog("WRITING CSV STRING TO FILE\n");
    $fh = fopen(CSV_PATH."emailvision-cc-test.csv", "w+");
    fwrite($fh, $csv);
    fclose($fh);*/
    
    try {
      tlog("CONNECTING TO EMAILVISION BATCH MEMBER SERVICE\n");
      $sc = new SoapClientI($batchMemberWsdlUrl);
      $r = $sc->openApiConnection(array(
        'login' => $login,
        'pwd' => $pwd,
        'key' => $key
      ));
      $token = $r->return;
      //pp($token);
      tlog("UPLOADING INFOS TO EMAILVISION\n");
      $r = $sc->uploadFileInsert(array(
        'token' => $token,
        'insertUpload' => array(
          'fileName' => 'member.csv',
          'fileEncoding' => 'UTF-8',
          'separator' => ';',
          'skipFirstLine' => false,
          'dateFormat' => 'yyyy-MM-dd HH:mm:ss',
          'autoMapping' => true,
          'dedup' => array(
            'criteria' => 'CUSTOMER_ID',
            'order' => 'last',
            'skipUnsubAndHBQ' => true
          )
        ),
        'file' => $csv
      ));
      
      //flog($sc->__getLastRequest());
      
      tlog("EMAILVISION UPLOAD SUCCESS !\n");
      //pp($r);
      //$sc->closeApiConnection(array('token' => $token));
    } catch(SoapFault $sf) {
      tlog("EMAILVISION UPLOAD FAILURE :\n".print_r($sf, true)."\n");
    }
    
  }
  
  if ($historic) {
    // adapt the new range
    $ccc = count($cc);
    if ($ccc < $target_ccc/10)
      $ccc = $target_ccc/10;
    $new_range_len = round(($range_e - $range_s) * $target_ccc / $ccc);
    $range_s = $range_e;
    $range_e = $range_s + $new_range_len;
    if ($range_e < $max_range && $range_e > $max_range - $new_range_len)
      $range_e = $max_range;
  } else {
    $range_e = $max_range + 1;
  }
  
  // free memory to improve speed a little
  $q->free();
  unset($q, $cc);
  
}

tlog("SESSION END\n\n\n");
