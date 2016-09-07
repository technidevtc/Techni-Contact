<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$flog = fopen(LOGS."Avis_Verifies_retrieval_historic.log", "a+");
tlog("SESSION BEGIN\n");

$db = $conn->getDbh();
$path = CSV_PATH."avis-verifies/";

$todayTime = mktime(12,0,0,date('m'),date('j'));
$today = date('Ymd', $todayTime);
$files = scandir($path);
for ($i = 0, $c = count($files); $i < $c; $i++) {
	list($fn, $ext) = explode('.', $files[$i]);
  $fnPart = explode('_', $fn);
  if ($fnPart[0] == 'avis' && $fnPart[2] == AVIS_VERIFIES_ID_WEBSITE && $fnPart[3] == $today) {
    switch ($fnPart[1]) {
      case 'site':
        break;
      case 'produit':
        if ($ext == 'csv') {
          tlog("PROCESSING CSV FILE ".$files[$i]." : \n");
          
          $pnc = new Doctrine_Collection('ProductsNotations');
          $pncLen = 0;
          $saveCount = 0;
          $stats = array('NEW' => 0, 'UPDATED' => 0, 'DELETED' => 0);
          $fh = fopen($path.$files[$i], "r");
          
          // reading col headers
          $cols = fgetcsv($fh, 0, "\t");
          
          // deleting all avis verifies the first day of the month, the file containing all the avis from the beginning in this particular case
          if (date('d', $todayTime) == "01") {
            $q = Doctrine_Query::create()
              ->delete('ProductsNotations')
              ->where('avis_verifies_order_id != ""')
              ->execute();
          }
          
          // reading each 'avis'
          while (($line = fgetcsv($fh, 0, "\t")) !== false) {
            $avis = array();
            foreach ($line as $colI => $col)
              $avis[strtolower($cols[$colI])] = $col;
            
            // update table procuts notations : ajouter 'id vente' + 'origine' + 'echanges'
            // creer objet doctrine products notations
            // check ou la table est utilisée actuellement
            // coder creation nouvelle notation
            if (!preg_match('`\d+`', $avis['ref. produit']))
              continue;
            
            switch (strtolower($avis['action'])) {
              case 'new':
                tlog("  ADDING NEW ENTRY FOR PRODUCT ".$avis['ref. produit']." FROM ORDER ".$avis['ref. vente']." ... ");
                // check order to get client id and make sure it has the corresponding product in it
                $o = Doctrine_Query::create()
                  ->select('o.id, c.id, ol.id, olr.id, olp.id')
                  ->from('Order o')
                  ->innerJoin('o.client c')
                  ->innerJoin('o.lines ol')
                  ->innerJoin('ol.pdt_ref olr')
                  ->innerJoin('olr.product olp')
                  ->where('o.id = ?', $avis['ref. vente'])
                  ->andWhere('olr.id = ?', $avis['ref. produit'])
                  ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
                
                if ($o !== false) {
                  $pn = new ProductsNotations();
                  $pn->id_product = $o['lines'][0]['pdt_ref']['product']['id'];
                  $pn->id_client = $o['client']['id'];
                  $pn->id_commande = $avis['ref. vente'];
                  $pn->token = "";
                  $date = DateTime::createFromFormat('Y-m-d\TH:i:sO', preg_replace('`\.\d+\s`', '+', $avis['horodate avis']));
                  $pn->timestamp = $date->format('U');
                  $pn->note = 2*$avis['note'];
                  $pn->comment = $avis['avis'];
                  $pn->origin = 'avis-verifies';
                  $pn->avis_verifies_order_id = $avis['id. vente'];
                  $pn->avis_verifies_product_id = $avis['id. produit'];
                  $pn->exchanges = serialize($avis['echanges']);
                  $pnc[] = $pn;
                  $pncLen++;
                  $stats['NEW']++;
                  tlog("SUCCESS ! READY TO BE INSERTED\n", false);
                } else {
                  tlog("FAILURE : ORDER NOT FOUND OR PRODUCT NOT PRESENT IN SPECIFIED ORDER\n", false);
                }
                break;
              
              case 'update':
                tlog("  UPDATING ENTRY FOR PRODUCT ".$avis['ref. produit']." FROM ORDER ".$avis['ref. vente']." ... ");
                $pn = Doctrine_Query::create()
                  ->select('pn.*')
                  ->from('ProductsNotations pn')
                  ->where('pn.avis_verifies_order_id = ?', $avis['id. vente'])
                  ->andWhere('pn.avis_verifies_product_id = ?', $avis['id. produit'])
                  ->fetchOne();
                
                if ($pn !== false) {
                  $pn->timestamp = $avis['horodate avis'];
                  $pn->note = 2*$avis['note'];
                  $pn->comment = $avis['avis'];
                  $pn->exchanges = serialize($avis['echanges']);
                  $pnc[] = $pn;
                  $pncLen++;
                  $stats['UPDATED']++;
                  tlog("SUCCESS ! READY TO BE UPDATED\n", false);
                } else {
                  tlog("FAILURE ! SPECIFIED ENTRY WAS NOT FOUND (AV order id = ".$avis['id. vente'].", AV product id = ".$avis['id. produit'].")\n", false);
                }
                break;
              
              case 'delete':
                tlog("  DELETING ENTRY FOR PRODUCT ".$avis['ref. produit']." FROM ORDER ".$avis['ref. vente']." ... ");
                $rows = Doctrine_Query::create()
                  ->delete('ProductsNotations')
                  ->where('avis_verifies_order_id = ?', $avis['id. vente'])
                  ->andWhere('avis_verifies_product_id = ?', $avis['id. produit'])
                  ->execute();
                if ($rows >= 1) {
                  $stats['DELETED']++;
                  tlog("SUCCESS (".$rows." DELETED) !\n", false);
                } else {
                  tlog("FAILURE ! SPECIFIED ENTRY WAS NOT FOUND (AV order id = ".$avis['id. vente'].", AV product id = ".$avis['id. produit'].")\n", false);
                }
                break;
            }
            if ($pncLen >= 100) {
              tlog("  NOW SAVING THE LAST ".$pncLen." NEW/UPDATED ENTRIES (#".$saveCount.") ... ");
              //pp($pnc->toArray());
              $pnc->save();
              tlog("SUCCESS !\n", false);
              $saveCount++;
              unset($pnc);
              $pncLen = 0;
              $pnc = new Doctrine_Collection('ProductsNotations');
            }
          }
          if ($pncLen > 0) {
            tlog("  NOW SAVING THE ".($saveCount>0?"REMAINING ":"").$pncLen." NEW/UPDATED ENTR".($pncLen>1?"IES":"Y")." ... ");
            //pp($pnc->toArray());
            $pnc->save();
            tlog("SUCCESS !\n", false);
          }
          tlog("ENTRIES STATISTICS FOR THIS FILE : ".$stats['NEW']." NEW / ".$stats['UPDATED']." UPDATED / ".$stats['DELETED']." DELETED\n");
          
        }
        break;
    }
  }
}

tlog("SESSION END\n\n\n");
fclose($flog);
