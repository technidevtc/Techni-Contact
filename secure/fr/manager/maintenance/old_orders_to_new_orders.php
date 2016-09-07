<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

try {

$db = DBHandle::get_instance();

$tva_rates = Doctrine_Query::create()->select('*')->from('Tva')->fetchArray();
$tva_by_rate = array();
foreach ($tva_rates as $tva_rate)
  $tva_by_rate[$tva_rate['taux']] = $tva_rate;

$itemFields = array(
  'idProduct' => 0,
  'idTC' => 0,
  'name' => "",
  'fastdesc' => "",
  'label' => "",
  'price' => 0,
  'price2' => 0,
  'unite' => 1,
  'idFamily' => 0,
  'quantity' => 0,
  'tauxTVA' => 0,
  'promotion' => 0,
  'discount' => 0,
  'idAdvertiser' => 0,
  'refSupplier' => "",
  'comment' => "",
  'customCols' => array()
);

$db->set_charset('latin1');
$res = $db->query('
  SELECT o.*, c.tel1, c.tel1 AS tel2, c.fax1, c.email
  FROM commandes o
  LEFT JOIN clients c ON c.id = o.idClient', __FILE__, __LINE__);
while ($oo = $db->fetchAssoc($res)) {
  set_time_limit(20);
  
  $oo['fdpTVA'] = round($oo['fdp'] * $oo['fdp_tva'] / 100, 2);
  $oo['fdpTTC'] = $oo['fdp'] + $oo['fdpTVA'];
  $oo['fdpHT'] = $oo['fdp'];
  // We add the fdp for the commands before 8/10/2008 04:00:00
  if ($oo['create_time'] < 1223438400) {
    $oo['totalHT'] += $oo['fdp'];
    $oo['totalTTC'] += $oo['fdpTTC'];
  }
  
  $oo['coord'] = unserialize($oo['coord']);
  
  echo $oo['id']."<br/>\n";
  $no = new Order();
  $no->id = $oo['id'];
  //$no->web_id
  $no->titre = $oo['coord']['titre'];
  $no->nom = $oo['coord']['nom'];
  $no->prenom = $oo['coord']['prenom'];
  //$no->fonction
  $no->societe = $oo['coord']['societe'];
  //$no->salaries
  //$no->secteur
  //$no->qualification
  //$no->naf
  //$no->siret
  $no->adresse = $oo['coord']['adresse'];
  $no->cadresse = $oo['coord']['complement'];
  $no->cp = $oo['coord']['cp'];
  $no->ville = $oo['coord']['ville'];
  $no->pays = $oo['coord']['pays'];
  $no->tel = empty($oo['coord']['tel1']) ? $oo['tel1'] : $oo['coord']['tel1'];
  $no->fax = $oo['fax1'];
  $no->email = $oo['email'];
  //$no->url
  $no->delivery_infos = $oo['coord']['infos_sup'];
  $no->titre2 = $oo['coord']['titre_l'];
  $no->nom2 = $oo['coord']['nom_l'];
  $no->prenom2 = $oo['coord']['prenom_l'];
  $no->societe2 = $oo['coord']['societe_l'];
  $no->adresse2 = $oo['coord']['adresse_l'];
  $no->cadresse2 = $oo['coord']['complement_l'];
  $no->cp2 = $oo['coord']['cp_l'];
  $no->ville2 = $oo['coord']['ville_l'];
  $no->pays2 = $oo['coord']['pays_l'];
  $no->tel2 = empty($oo['coord']['tel2']) ? $oo['tel2'] : $oo['coord']['tel2'];
  //$no->fax2
  $no->activity = Order::ACTIVITY_VPC_COMPTANT;
  $no->client_id = $oo['idClient'];
  $no->type = Order::TYPE_INTERNET;
  //$no->estimate_id
  //$no->lead_id
  //$no->main_sup_id
  $no->campaign_id = $oo['campaignID'];
  $no->created = $oo['create_time'];
  $no->updated = $oo['timestamp'];
  $no->oked = $oo['create_time'];
  $no->validated = $oo['create_time'];
  $no->partly_cancelled = 0;
  $no->cancelled = $oo['cancel_reason'] != "" ? $oo['statut_timestamp'] : 0;
  $no->processed = $oo['statut_traitement'] >= Order::GLOBAL_PROCESSING_STATUS_PROCESSING ? $oo['statut_timestamp'] : 0;
  //$no->sav_opened
  //$no->sav_closed
  $no->shipped = $oo['dispatch_time'];
  //$no->created_user_id
  //$no->updated_user_id
  //$no->in_charge_user_id
  //$no->oked_user_id
  //$no->validated_user_id
  //$no->partly_cancelled_user_id
  //$no->cancelled_user_id
  $no->forecast_shipping_text = $oo['planned_delivery_date'];
  $no->partly_cancelled_text = $oo['partially_cancelled_reason'];
  $no->cancelled_text = $oo['cancel_reason'];
  $no->sav_opened_text = $oo['open_sav'];
  $no->sav_closed_text = $oo['close_sav'];
  //$no->partly_shipped_text
  $no->shipped_text = $oo['dispatch_comment'];
  $no->total_a_ht = $oo['totalPrice2HT'];
  $no->total_ht = $oo['totalHT'];
  $no->total_ttc = $oo['totalTTC'];
  $no->fdp_ht = $oo['fdpHT'];
  $no->fdp_ttc = $oo['fdpTTC'];
  $no->insurance = $oo['insurance'];
  $no->promotion_code = $oo['promotionCode'];
  $no->transaction_id = $oo['transaction_id'];
  $no->payment_mode = Order::PAYMENT_MODE_AT_ORDER;
  $no->payment_mean = $oo['type_paiement'];
  $no->payment_status = $oo['statut_paiement'];
  $no->processing_status = $oo['statut_traitement'];
  $no->waiting_info_status = $oo['attente_info'];
  //$no->comment
  $data = unserialize($oo['produits']);
  
  // Searching the idTC key position
  foreach($data[0] as $pos => $key) {
    if ($key == "idTC") {
      $idTCpos = $pos;
      break;
    }
  }
  
  if (!isset($idTCpos)) {
    echo "Invalid item table for order ".$oo['id']."<br/>\n";
  } else {
    $oo['items'] = array();
    
    $headersIndexes = array_flip($data[0]);
    for ($i=1, $size=count($data); $i<$size; $i++) {
      foreach ($itemFields as $fieldName => $dftValue) {
        $oo['items'][$data[$i][$idTCpos]][$fieldName] = isset($data[$i][$headersIndexes[$fieldName]]) ? $data[$i][$headersIndexes[$fieldName]] : "";
      }
    }
    unset($idTCpos);
  }
  
  foreach ($oo['items'] as $item) {
    $nol = new OrderLine();
    //$nol->id
    $nol->pdt_id = $item['idProduct'];
    $nol->pdt_ref_id = $item['idTC'];
    $nol->sup_id = $item['idAdvertiser'];
    $nol->sup_ref = $item['refSupplier'];
    if (is_string($item['customCols']))
      $item['customCols'] = mb_unserialize($item['customCols']);
    if (!empty($item['customCols'])) {
      $itemDesc = $item['label'];
      foreach($item['customCols'] as $labelCol => $ccol_content)
        $itemDesc .= " - ".$labelCol.": ".$ccol_content;
    } else {
      $itemDesc = $item['name'] . (empty($item['fastdesc']) ? "" : " - " . $item['fastdesc']) . (empty($item['label']) ? "" : " - " . $item['label']);
    }
    $nol->desc = $itemDesc;
    $nol->pau_ht = $item['price2'];
    $nol->pu_ht = $item['price'];
    $nol->quantity = $item['quantity'];
    $nol->promotion = $item['price'] > 0 ? round($item['promotion'] / $item['price'] * 100, 6) : 0;
    $nol->discount = $item['price'] > 0 ? round($item['discount'] / $item['price'] * 100, 6) : 0;
    
    // taken from CCommand.php
    $item['priceHT'] = $item['price'] - ($item['promotion'] + $item['discount']);
    $item['priceTVA'] = round($item['priceHT'] * $item['tauxTVA'] / 100, 6);
    $item['priceTTC'] = $item['priceHT'] + $item['priceTVA'];
    $item['sumHT'] = $item['quantity'] * $item['priceHT'];
    $item['sumTVA'] = $item['quantity'] * $item['priceTVA']; // no round needed : != $item['sumHT'] * $item['tauxTVA'] because with great quantities the result is not the same
    $item['sumTTC'] = $item['sumHT'] + $item['sumTVA'];
    
    $nol->total_ht = $item['sumHT'];
    $nol->total_ttc = $item['sumTTC'];
    $nol->tva_code = $tva_by_rate[$item['tauxTVA']]['id'];
    //$nol->delivery_time
    $nol->comment = $item['comment'];
    //$nol->sup_comment
    $no->lines[] = $nol;
  }
  
  // determine main sup id - taken from Order.php
  $so_infos = array();
  foreach ($no->lines as $line) {
    if (!empty($line->sup_id)) {
      if (!isset($so_infos[$line->sup_id]))
        $so_infos[$line->sup_id] = array('total_ht' => 0);
      $so_infos[$line->sup_id]['total_ht'] += $line->total_ht;
    }
  }
  $main_sup_id = 0;
  $max_total_ht = 0;
  foreach ($so_infos as $sup_id => $infos)
    if ($infos['total_ht'] > $max_total_ht)
      $main_sup_id = $sup_id;
  $no->main_sup_id = $main_sup_id;
  
  $no->save();
  $no->free(true);
  
}

} catch (Exception $e) {
  echo $e->getMessage();
}
