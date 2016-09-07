<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$user = new BOUser();
if (!$user->login()) {
	header('Location: '.ADMIN_URL.'login.html');
	exit();
}

$inputs = filter_input_array(INPUT_GET, array(
  'type' => array('filter' => FILTER_VALIDATE_REGEXP,
                  'options' => array('regexp' => '`client|invoice`')
                 ),
  'format' => array('filter' => FILTER_VALIDATE_REGEXP,
                    'options' => array('regexp' => '`csv|TRA`')
                   ),
  'start' => FILTER_VALIDATE_INT,
  'end' => FILTER_VALIDATE_INT,
  'historic' => FILTER_VALIDATE_INT
));
foreach ($inputs as $k => $v)
  if (!isset($v))
    exit();
  else
    $$k = $v;

$os = fopen("php://temp", 'r+');

if ($type == "client") {

  $fileName = "comptes-tiers-du-".date('d-m-Y',$start)."-au-".date('d-m-Y',$end-1).".".$format;
  
  $cols = array(
    'FIXE' => array('value' => '***', 'length' => 3),
    'IDENTIFIANT' => array('value' => 'CAE', 'length' => 3),
    'CODE' => array('value' => 'client.code', 'length' => 17),
    'LIBELLE' => array('value' => 'SPECIFIC', 'length' => 35),
    'NATURE' => array('value' => 'CLI', 'length' => 3),
    'LETTRABLE' => array('value' => 'X', 'length' => 1),
    'COLLECTIF' => array('value' => 'SPECIFIC', 'length' => 204),
    'ADRESSE 1' => array('value' => 'client.adresse', 'length' => 35),
    'ADRESSE 2' => array('value' => 'client.complement', 'length' => 35),
    'ADRESSE 3' => array('value' => '', 'length' => 35),
    'CODE POSTAL' => array('value' => 'client.cp', 'length' => 9),
    'VILLE' => array('value' => 'client.ville', 'length' => 35),
    'DOMICILIATION' => array('value' => '-', 'length' => 24),
    'ETABLISSEMENT' => array('value' => '-', 'length' => 5),
    'GUICHET' => array('value' => '-', 'length' => 5),
    'COMPTE' => array('value' => '-', 'length' => 11),
    'CLE' => array('value' => '-', 'length' => 2),
    'PAYS' => array('value' => 'client.pays', 'length' => 3),
    'LIBELLE ABREGE' => array('value' => 'client.id', 'length' => 17),
    'LANGUE' => array('value' => 'FRA', 'length' => 3),
    'MULTIDEVISE' => array('value' => '', 'length' => 1),
    'DEVISE DU TIERS' => array('value' => '', 'length' => 3),
    'TELEPHONE' => array('value' => 'client.tel1', 'length' => 25),
    'FAX' => array('value' => 'client.fax1', 'length' => 25),
    'REGIME TVA' => array('value' => 'SPECIFIC', 'length' => 3),
    'MODE REGLEMENT' => array('value' => 'SPECIFIC', 'length' => 3),
    'COMMENTAIRE' => array('value' => '', 'length' => 35),
    'NIF' => array('value' => '', 'length' => 17),
    'SIRET' => array('value' => 'client.num_siret', 'length' => 17),
    'APE' => array('value' => 'client.code_naf', 'length' => 5),
    'PRENOM' => array('value' => 'client.prenom', 'length' => 35),
    'CONTACT : SERVICE' => array('value' => '', 'length' => 35),
    'CONTACT : FONCTION' => array('value' => 'client.fonction', 'length' => 35),
    'CONTACT : TELEPHONE' => array('value' => 'client.tel1', 'length' => 25),
    'CONTACT : FAX' => array('value' => 'client.fax1', 'length' => 25),
    'CONTACT : TELEX' => array('value' => '', 'length' => 40),
    'CONTACT : RVA' => array('value' => '', 'length' => 35),
    'CONTACT : CIVILITE' => array('value' => 'client.titre_text', 'length' => 3), // titre
    'CONTACT : PRINCIPAL' => array('value' => '', 'length' => 3),
    'FORME JURIDIQUE' => array('value' => '', 'length' => 1),
    'RIB PRINCIPAL' => array('value' => 'X', 'length' => 1),
    'TVAENCAISSEMENT' => array('value' => 'TD', 'length' => 3),
    'PAYEUR' => array('value' => '', 'length' => 10),
    'Code BIC' => array('value' => '', 'length' => 10),
    '001' => array('value' => '001', 'length' => 3, 'tra_only' => true)
  );
  if ($format == "csv")
    fputcsv($os, array_keys($cols), ';', '"');
  elseif ($format == "TRA")
    fwrite($os, "!\n");
  
  $offset = 0;
  $limit = 1000;
  do {
    set_time_limit(60);
    $q = Doctrine_Query::create()
      ->select('c.id,
                c.titre,
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
                c.num_siret,
                c.fonction,
                c.website_origin,
                c.code,
                i.activity,
                i.payment_mean,
                i.fonction')
      ->from('Clients c')
      ->innerJoin('c.invoices i')
      ->where('i.issued >= ? AND i.issued < ?', array($start, $end))
      ->orderBy('i.issued DESC')
      ->offset($offset)
      ->limit($limit);
    if (!$historic)
      $q->andWhere('c.cegid_exported = ?', 0);
    $cc = $q->fetchArray();
    
    foreach ($cc as $c) {
      $values = array();
      foreach ($cols as $cn => $cs) {
        if ($format == "csv" && $cs["tra_only"])
          continue;
        
        $col_val_tree = preg_split('/(?<!\\\)\./',$cs['value']); // col value tree : ignore escaped dot (\.)
        $val = "";
        if (count($col_val_tree) > 1) {
          if ($col_val_tree[0] == 'client')
            array_shift($col_val_tree);
          $val = $c;
          foreach ($col_val_tree as $val_tree)
            $val = $val[$val_tree];
          $val = utf8_decode($val);
        } else if ($cs['value'] == 'SPECIFIC') {
          $ac = $c['invoices'][0]['activity'];
          $pm = $c['invoices'][0]['payment_mean'];
          switch ($cn) {
            case 'LIBELLE':
              if (preg_match('/\bparticulier\b/i', $c['invoices'][0]['fonction']))
                $val = $c['nom'];
              else
                $val = $c['societe'];
              break;
            case 'COLLECTIF':
              if ($ac == Invoice::ACTIVITY_ANNONCEUR || $ac == Invoice::ACTIVITY_ANNONCEUR_EXPORT)
                $val = 4111000;
              elseif ($c['website_origin'] == WEBSITE_ORIGIN_MOBANEO)
                $val = 4112000;
              else
                $val = 4110000;
              break;
            case 'REGIME TVA':
              if ($ac == Invoice::ACTIVITY_VPC_INTRA_COMPTANT || $ac == Invoice::ACTIVITY_VPC_INTRA_DIFFERE)
                $val = 'INT';
              elseif ($ac == Invoice::ACTIVITY_VPC_EXPORT_COMPTANT || $ac == Invoice::ACTIVITY_VPC_EXPORT_DIFFERE || $ac == Invoice::ACTIVITY_ANNONCEUR_EXPORT)
                $val = 'EXP';
              else
                $val = 'FRA';
              break;
            case 'MODE REGLEMENT':
              switch ($pm) {
                case Invoice::PAYMENT_MEAN_BC_TBD:
                case Invoice::PAYMENT_MEAN_BC_CB:
                case Invoice::PAYMENT_MEAN_BC_VISA:
                case Invoice::PAYMENT_MEAN_BC_MASTERCARD:
                case Invoice::PAYMENT_MEAN_BC_AMEX:       $val = 'CBL'; break;
                case Invoice::PAYMENT_MEAN_PAYPAL:        $val = 'PAY'; break;
                case Invoice::PAYMENT_MEAN_CHEQUE:        $val = 'CHQ'; break;
                case Invoice::PAYMENT_MEAN_VIREMENT:      $val = 'VIR'; break;
                case Invoice::PAYMENT_MEAN_DIFFERE:       break; // never happen
                case Invoice::PAYMENT_MEAN_CB:            break; // "
                case Invoice::PAYMENT_MEAN_MANDAT:        $val = 'MDT'; break;
                case Invoice::PAYMENT_BANKER_ORDER:       $val = 'PRL'; break;
                case Invoice::PAYMENT_BILL_OF_EXCHANGE:   $val = 'LCR'; break;
              }
              break;
            default:
              $val = $cs['value'];
          }
        } else {
          $val = $cs['value'];
        }
        if ($format == "TRA")
          $val = substr(str_pad($val, $cs["length"]), 0 , $cs["length"]);
        $values[] = $val;
      }
      if ($format == "csv")
        fputcsv($os, $values, ';', '"');
      elseif ($format == "TRA")
        fwrite($os, implode("", $values)."\n");
    }
    $offset += $limit;
  } while (!empty($cc));
  
} elseif ($type == "invoice") {

  $fileName = "fact-av-du-".date('d-m-Y',$start)."-au-".date('d-m-Y',$end-1).".".$format;
  
  $cols = array(
    'JOURNAL' => array('value' => 'VTE', 'length' => 3),
    'DATECOMPTABLE' => array('value' => 'invoice.issued', 'length' => 8),
    'TYPE PIECE' => array('value' => 'SPECIFIC', 'length' => 2),
    'GENERAL' => array('value' => 'SPECIFIC', 'length' => 17),
    'TYPE CPTE' => array('value' => 'SPECIFIC', 'length' => 1),
    'AUXILIAIRE OU SECTION' => array('value' => 'SPECIFIC', 'length' => 17),
    'REFINTERNE' => array('value' => 'invoice.rid', 'length' => 35),
    'LIBELLE' => array('value' => 'SPECIFIC', 'length' => 35),
    'MODEPAIE' => array('value' => 'SPECIFIC', 'length' => 3),
    'ECHEANCE' => array('value' => 'invoice.due_date', 'length' => 8),
    'SENS' => array('value' => 'SPECIFIC', 'length' => 1),
    'MONTANT1' => array('value' => 'SPECIFIC', 'length' => 20),
    'N' => array('value' => 'N', 'length' => 1, 'tra_only' => true),
    'NUMEROPIECE' => array('value' => 'invoice.rid', 'length' => 8),
    'EUR 1' => array('value' => 'EUR1,00000', 'length' => 13, 'tra_only' => true),
    'E--' => array('value' => 'E--', 'length' => 43, 'tra_only' => true),
    '140 1' => array('value' => '140', 'length' => 7, 'tra_only' => true),
    'REFEXTERNE' => array('value' => 'invoice.order_id', 'length' => 35),
    'DATEREFEXTERNE' => array('value' => 'invoice.order_date', 'length' => 8),
    'DATECREATION' => array('value' => 'SPECIFIC', 'length' => 8),
    '140 2' => array('value' => '140', 'length' => 112, 'tra_only' => true),
    'TVAENCAISSEMENT' => array('value' => '-', 'length' => 1),
    'REGIMETVA' => array('value' => 'SPECIFIC', 'length' => 3),
    'TVA' => array('value' => 'SPECIFIC', 'length' => 3)
  );
  if ($format == "csv")
    fputcsv($os, array_keys($cols), ';', '"');
  elseif ($format == "TRA")
    fwrite($os, "!\n");

  $offset = 0;
  $limit = 1000;
  do {
    set_time_limit(60);
    $q = Doctrine_Query::create()
      ->select('i.id,
                i.rid,
                i.nom,
                i.fonction,
                i.societe,
                i.activity,
                IF(i.order_id, i.order_id, "") AS order_id,
                i.type,
                i.website_origin,
                FROM_UNIXTIME(i.issued, "%d%m%Y") AS issued,
                FROM_UNIXTIME(i.due_date, "%d%m%Y") AS due_date,
                i.payment_mean,
                i.code,
                i.total_ht,
                i.total_ttc,
                IFNULL(FROM_UNIXTIME(o.validated, "%d%m%Y"),"") AS order_date,
                il.tva_code')
      ->from('Invoice i')
      ->innerJoin('i.lines il')
      ->leftJoin('i.order o')
      ->where('i.issued >= ? AND i.issued < ?', array($start, $end))
      ->orderBy('i.rid ASC')
      ->offset($offset)
      ->limit($limit);
    if (!$historic)
      $q->andWhere('i.cegid_exported = ?', 0);
    $ic = $q->fetchArray();

    // each invoice = 2 or 3 lines (no vat if export or intra)
    // VAT
    // product line (in fact total HT of invoice)
    // client line with total ttc and client code
    foreach ($ic as $i) {
      $line_types = array('cli','pdt','tva');
      
      // init usefull vars
      $scheme = 'FRA';
      switch ($i['activity']) {
        case Invoice::ACTIVITY_VPC_INTRA_COMPTANT:
        case Invoice::ACTIVITY_VPC_INTRA_DIFFERE:
        case Invoice::ACTIVITY_ANNONCEUR_INTRA:
        case Invoice::ACTIVITY_CATALOG_INTRA:
          $scheme = 'INT';
          array_pop($line_types);
          break;
        case Invoice::ACTIVITY_VPC_EXPORT_COMPTANT:
        case Invoice::ACTIVITY_VPC_EXPORT_DIFFERE:
        case Invoice::ACTIVITY_ANNONCEUR_EXPORT:
          $scheme = 'EXP';
          array_pop($line_types);
      }
      
      $ccv = array(); // Common Column Values
      foreach ($line_types as $line_type) {
        $values = array();
        foreach ($cols as $cn => $cs) {
          if ($format == "csv" && $cs["tra_only"])
            continue;
          
          $val = '';
          
          // init the Common Column Values for all invoice lines
          if (!isset($ccv[$cn])) {
            $vtree = preg_split('/(?<!\\\)\./',$cs['value']); // value tree : ignore escaped dot (\.)
            if (count($vtree) > 1) {
              if ($vtree[0] == 'invoice')
                array_shift($vtree);
              $ct = $i;
              foreach ($vtree as $v)
                $ct = $ct[$v];
              $ccv[$cn] = utf8_decode($ct);
            } else {
              $ccv[$cn] = false;
            }
          }
          
          if ($ccv[$cn] !== false) { // there is a common value
            $val = $ccv[$cn];
          } else if ($cs['value'] == 'SPECIFIC') { // specific value
            switch ($cn) {
              case 'TYPE PIECE': // common to all invoice lines
                if ($i['type'] == Invoice::TYPE_INVOICE)
                  $val = $ccv[$cn] = 'FC';
                else
                  $val = $ccv[$cn] = 'AC';
                break;
              case 'GENERAL':
                // client code changes depending on the website origin
                if ($i['website_origin'] == WEBSITE_ORIGIN_MOBANEO)
                  $cliVal = 4112000;
                else // TC
                  $cliVal = 4110000;
                
                $tvaIsCode3 = $i['lines'][0]['tva_code'] == 3;
                $tvaAccount = $scheme == 'FRA' ? ($tvaIsCode3 ? 4457120 : 4457100) : 4457100;
                
                switch ($i['activity']) {
                  case Invoice::ACTIVITY_VPC_COMPTANT:
                  case Invoice::ACTIVITY_VPC_DIFFERE:
                    switch ($line_type) {
                      case 'tva': $val = $tvaAccount; break;
                      case 'pdt': $val = $tvaIsCode3 ? 7071000 : 7071100; break;
                      case 'cli': $val = $cliVal; break;
                    }
                    break;
                  case Invoice::ACTIVITY_VPC_EXPORT_COMPTANT:
                  case Invoice::ACTIVITY_VPC_EXPORT_DIFFERE:
                    switch ($line_type) {
                      case 'pdt': $val = 7079900; break;
                      case 'cli': $val = $cliVal; break;
                    }
                    break;
                  case Invoice::ACTIVITY_VPC_INTRA_COMPTANT:
                  case Invoice::ACTIVITY_VPC_INTRA_DIFFERE:
                    switch ($line_type) {
                      case 'pdt': $val = 7079800; break;
                      case 'cli': $val = $cliVal; break;
                    }
                    break;
                  case Invoice::ACTIVITY_ANNONCEUR:
                    switch ($line_type) {
                      case 'tva': $val = $tvaAccount; break;
                      case 'pdt': $val = $tvaIsCode3 ? 7082000 : 7082010; break;
                      case 'cli': $val = 4111000; break;
                    }
                    break;
                  case Invoice::ACTIVITY_ANNONCEUR_INTRA:
                    switch ($line_type) {
                      case 'pdt': $val = 7082900; break;
                      case 'cli': $val = 4111000; break;
                    }
                    break;
                  case Invoice::ACTIVITY_ANNONCEUR_EXPORT:
                    switch ($line_type) {
                      case 'pdt': $val = 7082500; break;
                      case 'cli': $val = 4111000; break;
                    }
                    break;
                    break;
                  case Invoice::ACTIVITY_CATALOG:
                    switch ($line_type) {
                      case 'tva': $val = $tvaAccount; break;
                      case 'pdt': $val = 7070000; break;
                      case 'cli': $val = $cliVal; break;
                    }
                    break;
                  case Invoice::ACTIVITY_CATALOG_INTRA:
                    switch ($line_type) {
                      case 'pdt': $val = 7079810; break;
                      case 'cli': $val = $cliVal; break;
                    }
                    break;
                  case Invoice::ACTIVITY_LOCATION_FICHIERS:
                    switch ($line_type) {
                      case 'tva': $val = $tvaAccount; break;
                      case 'pdt': $val = 7082100; break;
                      case 'cli': $val = $cliVal; break;
                    }
                    break;
                  case Invoice::ACTIVITY_LOCATION_BANNIERE_PUB:
                    switch ($line_type) {
                      case 'tva': $val = $tvaAccount; break;
                      case 'pdt': $val = 7082200; break;
                      case 'cli': $val = $cliVal; break;
                    }
                    break;
                }
                break;
              case 'TYPE CPTE':
                if ($line_type == 'cli')
                  $val = 'X';
                break;
              case 'AUXILIAIRE OU SECTION':
                if ($line_type == 'cli')
                  $val = $i['code'];
                break;
              case 'LIBELLE':
                if (preg_match('/\bparticulier\b/i', $i['fonction']))
                  $val = $i['nom'];
                else
                  $val = $i['societe'];
                break;
              case 'MODEPAIE':
                switch ($i['payment_mean']) {
                  case Invoice::PAYMENT_MEAN_BC_TBD:
                  case Invoice::PAYMENT_MEAN_BC_CB:
                  case Invoice::PAYMENT_MEAN_BC_VISA:
                  case Invoice::PAYMENT_MEAN_BC_MASTERCARD:
                  case Invoice::PAYMENT_MEAN_BC_AMEX:       $val = $ccv[$cn] = 'CBL'; break;
                  case Invoice::PAYMENT_MEAN_PAYPAL:        $val = $ccv[$cn] = 'PAY'; break;
                  case Invoice::PAYMENT_MEAN_CHEQUE:        $val = $ccv[$cn] = 'CHQ'; break;
                  case Invoice::PAYMENT_MEAN_VIREMENT:      $val = $ccv[$cn] = 'VIR'; break;
                  case Invoice::PAYMENT_MEAN_DIFFERE:       break; // never happen
                  case Invoice::PAYMENT_MEAN_CB:            break; // "
                  case Invoice::PAYMENT_MEAN_MANDAT:        $val = 'MDT'; break;
                  case Invoice::PAYMENT_BANKER_ORDER:       $val = $ccv[$cn] = 'PRL'; break;
                  case Invoice::PAYMENT_BILL_OF_EXCHANGE:   $val = $ccv[$cn] = 'LCR'; break;
                }
                break;
              case 'SENS':
                if ($i['type'] == Invoice::TYPE_INVOICE)
                  $val = $line_type == 'cli' ? 'D' : 'C';
                else
                  $val = $line_type == 'cli' ? 'C' : 'D';
                break;
              case 'MONTANT1':
                switch ($line_type) {
                  case 'tva': $val = $i['total_tva']; break;
                  case 'pdt': $val = $i['total_ht']; break;
                  case 'cli': $val = $i['total_ttc']; break;
                }
                break;
              case 'DATECREATION':
                $val = $ccv[$cn] = date('dmY');
                break;
              case 'REGIMETVA':
                $val = $ccv[$cn] = $scheme;
                break;
              case 'TVA':
                if ($scheme == 'FRA') {
                  switch ($i['lines'][0]['tva_code']) {
                    case 1: $val = $ccv[$cn] = 'TN'; break;
                    case 2: $val = $ccv[$cn] = 'TR'; break;
                    case 3:
                    case 4:
                  }
                } else {
                  $val = $ccv[$cn] = '';
                }
                break;
              default: // never happens
                $val = $cs['value'];
            }
          } else { // default value
            $val = $cs['value'];
          }
          if ($format == "TRA")
            $val = substr(str_pad($val, $cs["length"]), 0 , $cs["length"]);
          $values[] = $val;
        }
        if ($format == "csv")
          fputcsv($os, $values, ';', '"');
        elseif ($format == "TRA")
          fwrite($os, implode("", $values)."\n");
      }
    }
    $offset += $limit;
  } while (!empty($ic));
  
}

$csv = "";
rewind($os);
while (($line = fgets($os)) !== false)
  $csv .= $line;
if (!feof($os))
  exit();
fclose($os);

//pp($fileName);
//pp($csv);

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='.$fileName);
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: '.strlen($csv));
ob_clean();
flush();
print $csv;
