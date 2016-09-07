<?php


/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Mises à jour :

       24 juin 2005 : + accès commerciaux à tous les éléments

 Fichier : /secure/manager/products/edit.php
 Description : Edition d'un produit
/=================================================================*/

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

require(ADMIN . 'products.php');
require(ADMIN . 'users.php');
require(ADMIN . 'families.php');
require(ADMIN . 'advertisers.php');
require(ADMIN . 'tva.php');

$title = 'Base de données des produits';

$type_a = isset($_GET['type']) ? $_GET['type'] : '';

switch ($type_a) {
  case "add":
    $navBar = '<a href="add_wait.php?SESSION" class="navig">Base de données des produits en attente de validation de création</a> &raquo; Valider un produit';
    break;
  case "add_adv":
    $navBar = '<a href="add_wait.php?SESSION&from=adv" class="navig">Base de données des produits extranet en attente de validation de création</a> &raquo; Valider un produit';
    break;
  case "edit_adv":
    $navBar = '<a href="edit_wait.php?SESSION&from=adv" class="navig">Base de données des produits extranet en attente de validation de modification</a> &raquo; Valider un produit';
    break;
  default:
    $navBar = '<a href="index.php?SESSION" class="navig">Base de données des produits</a> &raquo; Editer un produit';
}

require(ADMIN.'head.php');

$mts["TOTAL TIME"]["start"] = microtime(true);
$mts["LOADING PRODUCT"]["start"] = microtime(true);

if (!isset($_GET['id']) || !preg_match('/^[0-9]+$/', $_GET['id']) || ($type_a != '' && $type_a != 'add' && $type_a != 'edit' && $type_a != 'backup' && $type_a != 'add_adv' && $type_a != 'edit_adv') || !($data = & loadProduct($handle, $_GET['id'], $type_a))) { ?>
<div class="bg">
  <div class="fatalerror">Identifiant produit incorrect.</div>
</div>
<?php } elseif($type_a != '' && $user->rank == CONTRIB) { ?>
  <div class="bg">
    <div class="fatalerror">Vous n'avez pas les droits adéquats pour réaliser cette opération.</div>
  </div>
<?php } else {

$mts["LOADING PRODUCT"]["end"] = microtime(true);
$mts["LOADING ADV/SUP/CAT/TVA"]["start"] = microtime(true);

$advertisers = & displayAdvertisers($handle, 'order by a.nom1');
$suppliers   = & GetSuppliersInfos($handle, 'order by a.nom1');
$families    = & displayFamilies($handle);
$listeTVAs   = displayTVAs($handle, ' order by taux desc');
mb_convert_variables("UTF-8","ASCII,UTF-8,ISO-8859-1,CP1252",$listeTVAs);
$idTVAdftDft = getConfig($handle, 'idTVAdft');

$mts["LOADING ADV/SUP/CAT/TVA"]["end"] = microtime(true);

///////////////////////////////////////////////////////////////////////////

$error = false;
$errorstring = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (!$userPerms->has("m-prod--sm-products", "e")) {
    $errorstring = "Vous n'avez pas les droits de modification produits.";
  }
  else {
    $mts["POST PROCESSING"]["start"] = microtime(true);
    $nom = isset($_POST['nom']) ? substr(trim($_POST['nom']), 0, 255) : '';
    $nom = preg_replace('/ +/', ' ', $nom);

    if($nom == '') {
      $error = true;
      $errorstring .= '- Vous n\'avez pas saisi le nom du produit<br/>';
    }
    elseif($nom[0] < '0' && $nom[0] > '9' && $nom[0] < 'A' && $nom[0] > 'Z') {
      $error = true;
      $errorstring .= '- Le nom du produit doit débuter par une lettre / chiffre<br/>';
    }

    $fastdesc = isset($_POST['fastdesc']) ? substr(trim($_POST['fastdesc']), 0, 255) : '';

    if($fastdesc == '') {
      $error = true;
      $errorstring .= '- Vous n\'avez pas saisi la description rapide du produit<br/>';
    }

    $advertiser = isset($_POST['advertiser']) ? $_POST['advertiser'] : 0;

    if($advertiser == 0) {
      $error = true;
      $errorstring .= '- Vous n\'avez pas sélectionné l\'annonceur<br/>';
    }

    $isSupplier = $suppliers[$advertiser][0] != '' ? true : false;

    if ($suppliers[$advertiser][1] != '') {
      if ($suppliers[$advertiser][1] == 1) $prixPublic = 1;
      elseif ($suppliers[$advertiser][1] == 0) $prixPublic = 0;
      else $prixPublic = -1;
    }
    else {
      $prixPublic = -1;
    }

    $margeRemiseDft = $suppliers[$advertiser][2];
    $idTVAdft       = $suppliers[$advertiser][4];

    if ($isSupplier) {
      $errorSupplier = false;

      if ($prixPublic == -1) {
        $errorSupplier = true;
        $errorstring .=	'- Erreur fatale dans la détermination du type de prix (prix fournisseur ou prix public) du fournisseur.<br/>';
      }

      if (!preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', $margeRemiseDft)) {
        $errorSupplier = true;
        $errorstring .=	'- Erreur fatale dans la détermination du taux de marge ou de remise par défaut du fournisseur.<br/>';
      }

      if (!existTVA($handle, $idTVAdft)) {
        $errorSupplier = true;
        $errorstring .=	'- Erreur fatale dans la détermination du taux de tva par défaut du fournisseur.<br/>';
      }

      if ($errorSupplier) {
        $error = true;
        $errorstring .= '--> Veuillez éditer le fournisseur ' . $suppliers[$advertiser][0] . ' dans la base de données des annonceurs et fournisseurs pour corriger les problèmes suscités qui lui sont liés.<br/>';
      }
    }

    $familiesHidden = isset($_POST['familiesHidden']) ? $_POST['familiesHidden'] : '';

    $familiesTab    = explode(',', $familiesHidden);
    $familiesHidden = $familiesShown  = '';

    $cur_cat_id = $cur_cat_name = null;
    for($i = 0; $i < count($familiesTab); ++$i) {
      if(preg_match('/^[0-9]+$/', $familiesTab[$i])) {
        if(($result = & $handle->query('select name from families_fr where id = \'' . $handle->escape($familiesTab[$i]). '\'', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1) {
          $record = & $handle->fetch($result);
          $familiesHidden .= $familiesTab[$i] . ',';
          if($familiesShown != '') {
            $familiesShown .= ' - ';
          }
          // code redondant avec linkFamily.php (en cas de modif ...)
          $familiesShown .= '<a href="javascript:removeFamily(\\\'' . $familiesTab[$i] . '\\\', \\\'' . to_entities(str_replace(array('"', "'", '&', '(', ')'), array(' ', ' ', '', '', ''), Utils::toASCII($record[0]))) . '\\\')">' . to_entities(str_replace(array('"', "'", '&', '(', ')'), array(' ', ' ', '', '', ''), Utils::toASCII($record[0]))) . '</a>';
          if (!isset($cur_cat_id)) {
            $cur_cat_id = $familiesTab[$i];
            $cur_cat_name = $record[0];
          }
        }
      }
    }

    if($familiesHidden == '') {
      $error = true;
      $errorstring .= '- Vous devez lier le produit au minimum à une sous-famille<br/>';
    }

    if($familiesShown == '') {
      $familiesShown = '&nbsp; Actuellement aucune sous-famille associée.';
    }

    $alias    = isset($_POST['alias'])    ? substr(trim($_POST['alias']), 0, 255)    : '';
    $keywords = isset($_POST['keywords']) ? substr(trim($_POST['keywords']), 0, 255) : '';

    $desc = isset($_POST['desc']) ? trim($_POST['desc']) : '';

    if($desc == '') {
      $error = true;
      $errorstring .= '- Vous n\'avez pas saisi la description du produit<br/>';
    }

    $descd = isset($_POST['descd']) ? trim($_POST['descd']) : '';
    $ean = isset($_POST['ean']) ? trim($_POST['ean']) : '';
    $warranty = isset($_POST['warranty']) ? trim($_POST['warranty']) : '';

    $title_tag = isset($_POST['title_tag']) ? trim($_POST['title_tag']) : '';
    $meta_desc_tag = isset($_POST['meta_desc_tag']) ? trim($_POST['meta_desc_tag']) : '';

    $shipping_fee = isset($_POST['shipping_fee']) ? trim($_POST['shipping_fee']) : '';
    $video_code = isset($_POST['video_code']) ? trim($_POST['video_code']) : '';

    $type1 = isset($_POST['type1']) && $_POST['type1'] == '.doc' ? '.doc' : '.pdf';
    $type2 = isset($_POST['type2']) && $_POST['type2'] == '.doc' ? '.doc' : '.pdf';
    $type3 = isset($_POST['type3']) && $_POST['type3'] == '.doc' ? '.doc' : '.pdf';

    $gen = isset($_POST['gen']) ? true : false;
    $col = isset($_POST['col']) ? true : false;
    $ind = isset($_POST['ind']) ? true : false;

    // produits liés lead
    $productsHidden = isset($_POST['productsHidden']) ? $_POST['productsHidden'] : '';
    $productsTab    = explode(',', $productsHidden);
    $productsHidden = $productsShown  = '';
    for ($i=0; $i<count($productsTab); ++$i) {
      if (preg_match('/^[0-9]+$/', $productsTab[$i])) {
        if (($result = & $handle->query('select p.name, a.nom1 from products_fr p, advertisers a where p.active = 1 and p.id = \'' . $handle->escape($productsTab[$i]). '\' and p.idAdvertiser = a.id', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1) {
          $record = & $handle->fetch($result);
          $record[0] .= ' #' . $record[1] . '#';
          $productsHidden .= $productsTab[$i] . ',';
          if($productsShown != '') {
            $productsShown .= ' - ';
          }
          // code redondant avec linkProduct.php (en cas de modif ...)
          $productsShown .= '<a href="javascript:removeProduct(\\\'' . $productsTab[$i] . '\\\', \\\'' . to_entities(str_replace(array('"', "'", '&', '(', ')'), array(' ', ' ', '', '', ''), Utils::toASCII($record[0]))) . '\\\')">' . to_entities(str_replace(array('"', "'", '&', '(', ')'), array(' ', ' ', '', '', ''), Utils::toASCII($record[0]))) . '</a>';
        }
      }
    }
    if ($productsShown == '') {
      $productsShown = ' Actuellement aucun produit lié.';
    }

    // produits liés fiche
    $productsLinked = isset($_POST['productsLinked']) ? $_POST['productsLinked'] : '';

    // Type prix
    $typeprice = isset($_POST['typeprice']) ? $_POST['typeprice'] : 0;

    $code_ref = isset($_POST['code_ref']) ? $_POST['code_ref'] : '';

    // Champs en cas de saisie d'un produit sans référence
    $price  = isset($_POST['price'])  ? substr(trim($_POST['price']), 0, 9)  : '';
    $price2 = isset($_POST['price2']) ? substr(trim($_POST['price2']), 0, 9) : '';
    $unite  = isset($_POST['unite'])  ? substr(trim($_POST['unite']), 0, 6)  : '';
    $marge  = isset($_POST['marge'])  ? substr(trim($_POST['marge']), 0, 6)  : '';
    $remise = isset($_POST['remise']) ? substr(trim($_POST['remise']), 0, 6) : '';
    $idTVA  = isset($_POST['idTVA'])  ? substr(trim($_POST['idTVA']), 0, 2)  : '';

    // Champs communs aux produits avec ou sans référence
    $delai_livraison   = isset($_POST['delai_livraison'])   ? substr(trim($_POST['delai_livraison']), 0, 255) : '';
    $contrainteProduit = isset($_POST['contrainteProduit']) ? substr(trim($_POST['contrainteProduit']), 0, 9) : '';
    $asEstimate = (isset($_POST['asEstimate']) && $_POST['asEstimate'] == 'on') ? 1 : 0;
    $refSupplier       = isset($_POST['refSupplier'])       ? substr(trim($_POST['refSupplier']), 0, 9)       : '';

    // Vérif des champs communs aux produits avec ou sans référence
    if (($typeprice == 0 || $typeprice == 4) && $isSupplier) {
      if($contrainteProduit != '' && !preg_match('/^[0-9]+$/', $contrainteProduit)) {
        $error = true;
        $errorstring .= '- La contrainte de quantité de produit saisie est incorrecte<br/>';
      }
    }

    if($typeprice == 0) { // si prix simple saisi

      if(!preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', $price)) { // vérif validité du prix public
         $error = true;
         $errorstring .= '- Le format du prix public est incorrect<br/>';
      }

      if ($isSupplier) {

        if(trim($refSupplier) == '') {
          $error = true;
          $errorstring .= '- La référence fournisseur n\'a pas été saisie<br/>';
        }

        if(!preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', $price2)) { // vérif validité du prix fournisseur
          $error = true;
          $errorstring .= '- Le format du prix fournisseur est incorrect<br/>';
        }

        if(!preg_match('/^[1-9]{1}[0-9]*$/', $unite)) { // vérif validité unité
          $error = true;
          $errorstring .= '- Le format de l\'unité est incorrect<br/>';
        }

        if ($prixPublic == 0) { // si type de prix fournisseur
          if(!preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', $marge)) { // vérif validité de la marge
            $error = true;
            $errorstring .= '- Le format du pourcentage de marge est incorrect<br/>';
          }
          $margeRemise = $marge;
        }
        elseif ($prixPublic == 1) { // si type de prix public
          if(!preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', $remise) || $remise > 100) { // vérif validité de la remise
            $error = true;
            $errorstring .= '- Le format du pourcentage de remise est incorrect<br/>';
          }
          $margeRemise = $remise;
        }
        else {
          $error = true;
          $errorstring .= '- Impossible de déterminer si c\'est une marge ou remise qui doit être appliquée, car le type de prix par défaut du fournisseur est invalide<br/>';
        }

        if (!existTVA($handle, $idTVA)) {
          $error = true;
          $errorstring .= '- Le taux de TVA n\'existe pas<br/>';
        }

      }
      else {
        $margeRemise = 0;
      }
    }
    elseif($typeprice == 4) { // si tableau de référence
      // Créer le code_ref à partir des données import
      if(is_uploaded_file($_FILES['csv_ref']['tmp_name'])) {
        if(substr($_FILES['csv_ref']['name'], -3) == 'csv') {

          $import_tab = file($_FILES['csv_ref']['tmp_name']);

          if(is_array($import_tab) && count($import_tab) > 0) {

            // Protéger ; dans les noms de colonnes ( = entre guillemets) après isolement des vrais caractères guillements
            $import_tab[0] = str_replace('""', '[guillemet]', $import_tab[0]);
            $import_tab[0] = preg_replace('/"([^"]*);([^"]*)"/', '$1[pvirgule]$2', $import_tab[0]);
            $import_tab[0] = str_replace('"', '', $import_tab[0]);

            $import_cols = explode(';', trim($import_tab[0]));
            $code_ref = (count($import_cols) + 1) . '<=>';

            $code_ref .= 'Référence TC';
            for($i = 0; $i < count($import_cols); ++$i) {
              $code_ref .= '<->' . str_replace(array('<=>', '<_>', '<->', '[guillemet]', '[pvirgule]'), array('', '', '', '"', ';'), $import_cols[$i]);
            }

            for($i = 1; $i < count($import_tab); ++$i) {
              $code_ref .= '<_>';

              $import_tab[$i] = str_replace('""', '[guillemet]', $import_tab[$i]);
              $import_tab[$i] = preg_replace('/"([^"]*);([^"]*)"/', '$1[pvirgule]$2', $import_tab[$i]);
              $import_tab[$i] = str_replace('"', '', $import_tab[$i]);

              $import_line = explode(';', trim($import_tab[$i]));

              //$code_ref .= ''; --> champ vide pour générer un nouveau identifiant TC
              for($j = 0; $j < count($import_line); ++$j) {
                $code_ref .= '<->' . str_replace(array('<=>', '<_>', '<->', '[guillemet]', '[pvirgule]'), array('', '', '', '"', ';'), $import_line[$j]);
              }
            }
          }
          else {
            $errorstring .= '- Fichier d\'import vide<br/>';
            $code_ref = '9<=>Référence TC<->Libellé<->Référence Fournisseur<->Unité<->Taux TVA<->Prix Fournisseur<->Marge<->Prix Public<->Éco Taxe';
          }
        }
        else {
          $errorstring .= '- Format du fichier d\'import incorrect<br/>';
          $code_ref = '9<=>Référence TC<->Libellé<->Référence Fournisseur<->Unité<->Taux TVA<->Prix Fournisseur<->Marge<->Prix Public<->Éco Taxe';
        }

        unlink($_FILES['csv_ref']['tmp_name']);

      }

      if ($isSupplier) { // tableau de références fournisseur
        $code_ref_tab = explode('<=>', $code_ref);
        $colscount = $code_ref_tab[0];

        // Doit avoir au moins les 8 colonnes par défaut
        if(count($code_ref_tab) != 2 || !preg_match('/^[0-9]+$/', $colscount) || $colscount < 9) {
          $error = true;
          $errorstring .= '- Le tableau de références doit contenir au moins 1 propriété en plus de l\'identifiant Techni-Contact, du libellé, de la référence fournisseur, de l\'unité, du taux de TVA, du prix fournisseur, de la marge ou remise, du prix public et de l\'éco taxe<br/>';
        }

        // Vérifier libellés des colonnes saisis
        $code_ref_tab_next = explode('<_>', $code_ref_tab[1]);
        $code_ref_tab_cols = explode('<->', $code_ref_tab_next[0]);

        // Nombre de colonnes correspond + libellé première et dernière
        if (count($code_ref_tab_cols) != $colscount ||
            $code_ref_tab_cols[0] != 'Référence TC' ||
            $code_ref_tab_cols[1] != 'Libellé' ||
            $code_ref_tab_cols[2] != 'Référence Fournisseur' ||
            $code_ref_tab_cols[$colscount - 6] != 'Unité' ||
            $code_ref_tab_cols[$colscount - 5] != 'Taux TVA' ||
            $code_ref_tab_cols[$colscount - 4] != 'Prix Fournisseur' ||
            ($code_ref_tab_cols[$colscount - 3] != 'Marge' && $code_ref_tab_cols[$colscount - 3] != 'Remise') ||
            $code_ref_tab_cols[$colscount - 2] != 'Prix Public' ||
            $code_ref_tab_cols[$colscount - 1] != 'Éco Taxe') {

          $error = true;

          $code_ref_tab_cols[0] = 'Référence TC';
          $code_ref_tab_cols[1] = 'Libellé';
          $code_ref_tab_cols[2] = 'Référence Fournisseur';

          if($colscount > 8) {
            $code_ref_tab_cols[$colscount - 6] = 'Unité';
            $code_ref_tab_cols[$colscount - 5] = 'Taux TVA';
            $code_ref_tab_cols[$colscount - 4] = 'Prix Fournisseur';
            $code_ref_tab_cols[$colscount - 3] = 'Marge';
            $code_ref_tab_cols[$colscount - 2] = 'Prix Public';
            $code_ref_tab_cols[$colscount - 1] = 'Éco Taxe';
          }
          else {
            $code_ref_tab_cols[3] = 'Unité';
            $code_ref_tab_cols[4] = 'Taux TVA';
            $code_ref_tab_cols[5] = 'Prix Fournisseur';
            $code_ref_tab_cols[6] = 'Marge';
            $code_ref_tab_cols[7] = 'Prix Public';
            $code_ref_tab_cols[8] = 'Éco Taxe';
          }

          $errorstring .= ' - Nombre de colonnes erroné / modification Référence Fournisseur, Libellé, Unité, Taux TVA, Prix Fournisseur, Marge/Remise, Prix Public ou Éco Taxe interdite<br/>';
        }

        // Nom des autres colonnes
        if($colscount > 9) {
            for($i = 3; $i < $colscount-6; ++$i) {
              if(trim($code_ref_tab_cols[$i]) == '') {
              $error = true;
              $errorstring .= '- Libellé colonne ' . ($i + 1) . ' du tableau de références non saisi<br/>';
            }
          }
        }

        // Vérifier si au moins 1 ligne
        if(count($code_ref_tab_next) == 1) {
            $error = true;
          $errorstring .= '- Le tableau de références doit comporter au moins 1 ligne<br/>';
        }

        $some_lines = false;

        // Vérifier nombre de colonnes dans chaque ligne (annuler celles qui correspondent pas)
        $label_lines = 0;

        $code_ref_lines = array();
        for($i = 1; $i < count($code_ref_tab_next); ++$i) {
          $line_data = & explode('<->', $code_ref_tab_next[$i]);

          $line_colscount = count($line_data);

          if($line_colscount != $colscount) {
            $error = true;
            $errorstring .= ' - Ligne ' . $i . ' du fichier / tableau ignorée car son format est incorrect (les lignes suivantes sont automatiquement décallées, vous pouvez rajouter la ligne &agrave; la main)<br/>';
          }
          else {
            $code_ref_line = array();
            $code_ref_line['id']          = & $line_data[0];
            $code_ref_line['label']       = & $line_data[1];
            $code_ref_line['refSupplier'] = & $line_data[2];

            $code_ref_line['unite']       = & $line_data[$line_colscount-6];
            $code_ref_line['idTVA']       = & $line_data[$line_colscount-5];
            $code_ref_line['price2']      = & $line_data[$line_colscount-4];
            $code_ref_line['marge']       = & $line_data[$line_colscount-3];
            $code_ref_line['price']       = & $line_data[$line_colscount-2];
            $code_ref_line['ecotax']      = & $line_data[$line_colscount-1];

            $code_ref_line['content'] = array();
            for ($j=3; $j<$line_colscount-6; $j++)
              $code_ref_line['content'][] = $line_data[$j];
            $code_ref_line['content'] = implode("<->",$code_ref_line['content']);

            $label_lines++;
            $some_lines = true;

            // Vérif si quand la Référence TC est présente, elle est valide
            if($code_ref_line['id'] != '' && !preg_match('/^[0-9]+$/', $code_ref_line['id'])) {
              $error = true;
              $errorstring .= '- La ligne ' . $label_lines . ' du tableau de références ne possède pas de numéro de référence Techni-Contact valide. Veuillez prévenir votre webmaster si cette erreur survient à nouveau<br/>';
            }

            // Vérif si libellé
            if(trim($code_ref_line['label']) == '') {
              $error = true;
              $errorstring .= '- La ligne ' . $label_lines . ' du tableau de références ne possède pas de libellé<br/>';
            }

            // Vérif si référence fournisseur
            if(trim($code_ref_line['refSupplier']) == '') {
              $error = true;
              $errorstring .= '- La ligne ' . $label_lines . ' du tableau de références ne possède pas de référence fournisseur<br/>';
            }

            // Test de validité des unité, taux de TVA, prix fournisseur, marge/remise, prix public et écotaxe s'ils sont saisis

            if(!preg_match('/^[1-9]{1}[0-9]*$/',  trim($code_ref_line['unite']))) { // vérif validité unité
              $error = true;
              $errorstring .= '- L\'unité de la référence de la ligne ' . $label_lines . ' du tableau de références est incorrect<br/>';
            }

            if (!existTVA($handle, trim($code_ref_line['idTVA']))) {
              $error = true;
              $errorstring .= '- Le taux de TVA de la référence de la ligne ' . $label_lines . ' du tableau de références n\'existe pas, laissez le champ vide si vous souhaitez avoir le taux de TVA par défaut du fournisseur<br/>';
            }

            if (trim($code_ref_line['price2']) != '' && !preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', trim($code_ref_line['price2']))) {
              $error = true;
              $errorstring .= '- Le prix fournisseur de la référence de la ligne ' . $label_lines . ' du tableau de références est incorrect, laissez le champ vide si vous souhaitez conserver celui par défaut<br/>';
            }

            if (trim($code_ref_line['marge']) != '' && !preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', trim($code_ref_line['marge']))) {
              $error = true;
              $errorstring .= '- La marge ou remise de la référence de la ligne ' . $label_lines . ' du tableau de références est incorrecte, laissez le champ vide si vous ne souhaitez utiliser celle par défaut<br/>';
            }

            if (trim($code_ref_line['price']) != '' && !preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', trim($code_ref_line['price']))) {
              $error = true;
              $errorstring .= '- Le prix public de la référence de la ligne ' . $label_lines . ' du tableau de références est incorrect, laissez le champ vide si vous souhaitez conserver celui par défaut<br/>';
            }

            if (trim($code_ref_line['ecotax']) != '' && !preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', trim($code_ref_line['ecotax']))) {
              $error = true;
              $errorstring .= '- L\'éco taxe de la référence de la ligne ' . $label_lines . ' du tableau de références est incorrect, laissez le champ vide s\'il n\'y a pas d\'éco taxe pour cette référence<br/>';
            }

            if (trim($code_ref_line['price2']) == '' && trim($code_ref_line['price']) == '') {
              $error = true;
              $errorstring .= '- Les prix fournisseur et public de la référence de la ligne ' . $label_lines . ' du tableau de références n\'ont pas été saisis, il n\'est donc pas possible de valider la ligne via calcul en fonction du taux de marge ou remise par défaut du fournisseur<br/>';
            }

            $code_ref_lines[] = $code_ref_line;
          }
        }

        // Vérifier si au moins 1 ligne
        if(!$some_lines) {
          $error = true;
          $errorstring .= '- Le tableau de références doit comporter au moins 1 ligne valide<br/>';
        }

      }
      else { // tableau de références annonceur
        $code_ref_tab = explode('<=>', $code_ref);
        $colscount = $code_ref_tab[0];

        // Au moins 1 colonne sup
        if(count($code_ref_tab) != 2 || !preg_match('/^[0-9]+$/', $colscount) || $colscount < 3) {
          $error = true;
          $errorstring .= '- Le tableau de références doit contenir au moins 1 propriété en plus de l\'identifiant Techni-Contact, du libellé et du prix<br/>';
        }

        // Vérifier libellés des colonnes saisis
        $code_ref_tab_next = explode('<_>', $code_ref_tab[1]);
        $code_ref_tab_cols = explode('<->', $code_ref_tab_next[0]);


        // Nombre de colonnes correspond + libellé première et dernière
        if(count($code_ref_tab_cols) != $colscount ||
          $code_ref_tab_cols[0] != 'Référence TC' ||
          $code_ref_tab_cols[1] != 'Libellé' ||
          $code_ref_tab_cols[$colscount - 1] != 'Prix') {
          $error = true;

          $errorstring .= ' - Nombre de colonnes erroné / modification libellé ou prix interdite<br/>';
        }

        // Nom des autres colonnes
        if($colscount > 3) {
          for($i = 2; $i < $colscount - 1; ++$i) {
            if(trim($code_ref_tab_cols[$i]) == '') {
              $error = true;
              $errorstring .= '- Libellé colonne ' . ($i + 1) . ' du tableau de références non saisi<br/>';
            }
          }
        }

        // Vérifier si au moins 1 ligne
        if(count($code_ref_tab_next) == 1) {
          $error = true;
          $errorstring .= '- Le tableau de références doit comporter au moins 1 ligne<br/>';
        }

        $some_lines = false;

        // Vérifier nombre de colonnes dans chaque ligne (annuler celles qui correspondent pas)
        $label_lines = 0;

        $code_ref_lines = array();
        for($i = 1; $i < count($code_ref_tab_next); ++$i) {
          $line_data = explode('<->', $code_ref_tab_next[$i]);

          $line_colscount = count($line_data);

          if($line_colscount != $colscount) {
            $error = true;
            $errorstring .= ' - Ligne ' . $i . ' du fichier ignorée car son format est incorrect (les lignes suivantes sont automatiquement décallées, vous pouvez rajouter la ligne &agrave; la main)<br/>';
          }
          else {

            $code_ref_line = array();
            $code_ref_line['id']          = $line_data[0];
            $code_ref_line['label']       = $line_data[1];
            $code_ref_line['price']       = $line_data[$line_colscount-1];

            $code_ref_line['content']     = $line_data[2];
            for($j = 3; $j < $line_colscount - 1; ++$j)
              $code_ref_line['content'] .= '<->' . $line_data[$j];

            // colonnes pour fournisseur mise à 0
            $code_ref_line['refSupplier'] = '';
            $code_ref_line['unite']       = '';
            $code_ref_line['idTVA']       = '';
            $code_ref_line['price2']      = '';
            $code_ref_line['marge']       = '';
            $code_ref_line['ecotax']      = '';

            ++$label_lines;
            $some_lines = true;

          // Vérif si quand la Référence TC est présente, elle est valide
            if($code_ref_line['id'] != '' && !preg_match('/^[0-9]+$/', $code_ref_line['id'])) {
              $error = true;
              $errorstring .= '- La ligne ' . $label_lines . ' du tableau de références ne possède pas de numéro de référence Techni-Contact valide. Veuillez prévenir votre webmaster si cette erreur survient à nouveau<br/>';
            }

            // Vérif si libellé
            if(trim($code_ref_line['label']) == '') {
              $error = true;
              $errorstring .= '- La ligne ' . $label_lines . ' du tableau de références ne possède pas de libellé<br/>';
            }

            // Vérifier si au moins 1 donnée en + de l'identifiant TC et du libellée est présente
            $line_ok = false;

            $line_content = explode('<->', $code_ref_line['content']);
            for($j = 0; $j < count($line_content); $j++) {
              if(trim($line_content[$j]) != '') {
                $line_ok = true;
                break;
              }
            }
            if(!$line_ok) {
              $error = true;
              $errorstring .= '- La ligne ' . $label_lines . ' du tableau de références ne possède aucune donnée caractérisant le libellé et le prix<br/>';
            }
            if(trim($code_ref_line['price']) != '' && !preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', trim($code_ref_line['price']))) {
              $error = true;
              $errorstring .= '- Le prix de la référence de la ligne ' . $label_lines . ' du tableau de références est incorrect, laissez le champ vide si vous ne souhaitez pas préciser de prix<br/>';
            }

            $code_ref_lines[] = $code_ref_line;
          }
        }
        // Vérifier si au moins 1 ligne
        if(!$some_lines) {
          $error = true;
          $errorstring .= '- Le tableau de références doit comporter au moins 1 ligne<br/>';
        }

        // s'il y a des erreurs, on adapte le nombre de colonne à un format fournisseur pour la suite du script
        if ($error) {
          $code_ref_tab_cols2[0] = 'Référence TC';
          $code_ref_tab_cols2[1] = 'Libellé';
          $code_ref_tab_cols2[2] = 'Référence Fournisseur';

          $colscount += 5;

          if($colscount > 9) {
            for ($i = 2; $i < $colscount-7; $i++)
              $code_ref_tab_cols2[$i+1] = $code_ref_tab_cols[$i];

            $code_ref_tab_cols2[$colscount - 6] = 'Unité';
            $code_ref_tab_cols2[$colscount - 5] = 'Taux TVA';
            $code_ref_tab_cols2[$colscount - 4] = 'Prix Fournisseur';
            $code_ref_tab_cols2[$colscount - 3] = 'Marge';
            $code_ref_tab_cols2[$colscount - 2] = 'Prix Public';
            $code_ref_tab_cols2[$colscount - 1] = 'Éco Taxe';
          }
          else {
            $code_ref_tab_cols2[2] = 'Unité';
            $code_ref_tab_cols2[3] = 'Taux TVA';
            $code_ref_tab_cols2[4] = 'Prix Fournisseur';
            $code_ref_tab_cols2[5] = 'Marge';
            $code_ref_tab_cols2[6] = 'Prix Public';
            $code_ref_tab_cols2[7] = 'Éco Taxe';
          }

          $code_ref_tab_cols = & $code_ref_tab_cols2;
        }
      }
    } // fin typePrice == 4



    if(!$error) {
      switch($typeprice) {
        case 1 : $price = 'sur demande';    $refSupplier = $price2 = ''; $margeRemise = '-1'; $idTVA = '0'; $delai_livraison = $contrainteProduit = ''; break;
        case 2 : $price = 'sur devis';      $refSupplier = $price2 = ''; $margeRemise = '-1'; $idTVA = '0'; $delai_livraison = $contrainteProduit = ''; break;
        case 3 : $price = 'nous contacter'; $refSupplier = $price2 = ''; $margeRemise = '-1'; $idTVA = '0'; $delai_livraison = $contrainteProduit = ''; break;
        case 4 : $price = 'ref';            $refSupplier = $price2 = ''; $margeRemise = '-1'; $idTVA = '0'; break;
        default : $typeprice = 0;
      }


      // Recuper ancienne version références pr le backup
      $refOldString = '';

      if($data[15] == 'ref') {
        if($type_a == '' || $type_a == 'edit') {
          if($result = & $handle->query('select content from references_cols where idProduct = \'' . $handle->escape($_GET['id']) . '\'', __FILE__, __LINE__)) {
            $data_old_ref = & $handle->fetch($result);
            $data_old_ref = mb_unserialize($data_old_ref[0]);

            $refOldString = count($data_old_ref) . '<=>';

            for($i = 0; $i < count($data_old_ref); ++$i) {
              if($i > 0) $refOldString .= '<->';
              $refOldString .= $data_old_ref[$i];
            }
            $refOldString .= '<_>';
          }

          if($result = & $handle->query("
            SELECT id, label, content, refSupplier, price, price2, unite, marge, idTVA, ecotax
            FROM references_content
            WHERE idProduct = '" . $handle->escape($_GET['id']) . "' AND deleted = 0 AND vpc = 1
            ORDER BY classement", __FILE__, __LINE__)) {
            $i = 0;
            while($data_old_ref = & $handle->fetchArray($result, 'assoc')) {
              if($i > 0) $refOldString .= '<_>';

              $refOldString .= $data_old_ref['id'] . '<->' . $data_old_ref['label'] . '<->';
              if($isSupplier) $refOldString .= $data_old_ref['refSupplier'] . '<->';

              $un_old_data = mb_unserialize($data_old_ref['content']);
              for($j = 0; $j < count($un_old_data); ++$j) {
                $refOldString .= $un_old_data[$j] . '<->';
              }

              if($isSupplier) $refOldString .= $data_old_ref['unite'] . '<->' . $data_old_ref['idTVA'] . '<->' . $data_old_ref['price2'] . '<->' . $data_old_ref['marge'] . '<->' . $data_old_ref['price']. '<->' . $data_old_ref['ecotax'];
              else $refOldString .= $data_old_ref['price'];

              ++$i;
            }
          }
        }
        elseif($type_a == 'backup') {
          if($result = & $handle->query('select ref from products_add where id = \'' . $handle->escape($_GET['id']) . '\' and type=\'b\'', __FILE__, __LINE__)) {
              $data_old_ref = & $handle->fetch($result);
            $refOldString = $data_old_ref[0];
          }
        }
      }

      $data[30] = $data["ref"] = & $refOldString;

      // Fin recup références pr backup

	  //Recuperation champ bloquage
	  if(strcmp($_POST['input_product_locked'],'on')=='0'){
		$product_blocked	= '1';
	  }else{
		$product_blocked	= '0';
	  }

      if($type_a == 'add' || $type_a == 'add_adv') {
        $ok = addProduct($handle, $nom, $fastdesc, $advertiser, $familiesHidden, $alias, $keywords, $desc, $descd, $ean, $warranty, $title_tag, $meta_desc_tag, $shipping_fee, $video_code, $type1, $type2, $type3, $gen, $ind, $col, $productsHidden, $productsLinked, $refSupplier, $price, $price2, $unite, $margeRemise, $idTVA, $delai_livraison, $contrainteProduit, $asEstimate, $user->login, true, $_GET['id'], $type_a, $code_ref, $suppliers[$advertiser]);
        // pas d'id user = pas de notif car suppression implicite
        delProduct($handle, $_GET['id'], '', '', $type_a);
      }
      elseif($type_a == 'edit' || $type_a == 'edit_adv') {
        $ok = updateProduct($handle, $_GET['id'], $nom, $fastdesc, $advertiser, $familiesHidden, $alias, $keywords, $desc, $descd, $ean, $warranty, $title_tag, $meta_desc_tag, $shipping_fee, $video_code, $type1, $type2, $type3, $gen, $ind, $col, $productsHidden, $productsLinked, $refSupplier, $price, $price2, $unite, $margeRemise, $idTVA, $delai_livraison, $contrainteProduit, $asEstimate, $user->login, true, $type_a, $data, $code_ref, $suppliers[$advertiser], $product_blocked);
      }
      elseif($type_a == 'backup') {
        $ok = updateProduct($handle, $_GET['id'], $nom, $fastdesc, $advertiser, $familiesHidden, $alias, $keywords, $desc, $descd, $ean, $warranty, $title_tag, $meta_desc_tag, $shipping_fee, $video_code, $type1, $type2, $type3, $gen, $ind, $col, $productsHidden, $productsLinked, $refSupplier, $price, $price2, $unite, $margeRemise, $idTVA, $delai_livraison, $contrainteProduit, $asEstimate, $user->login, true, $type_a, $data, $code_ref, $suppliers[$advertiser], $product_blocked);
      }
      else {
        $save = ($user->rank != CONTRIB) ? 1 : 0;
        //$save = 0;
        $ok = updateProduct($handle, $_GET['id'], $nom, $fastdesc, $advertiser, $familiesHidden, $alias, $keywords, $desc, $descd, $ean, $warranty, $title_tag, $meta_desc_tag, $shipping_fee, $video_code, $type1, $type2, $type3, $gen, $ind, $col, $productsHidden, $productsLinked, $refSupplier, $price, $price2, $unite, $margeRemise, $idTVA, $delai_livraison, $contrainteProduit, $asEstimate, $user->login, $save, $type_a, $data, $code_ref, $suppliers[$advertiser], $product_blocked);
		
		//Supprimer la ligne a la position pos en BDD
		  if(isset($_POST['id_ref_tc_delete'])){
				$id_ref_tc_delete  =  $_POST['id_ref_tc_delete'];
				$id_explode 	   =  explode('|',$id_ref_tc_delete);
				foreach($id_explode as $value_explode){
					if(!empty($value_explode)){
						$sql_delete = "DELETE FROM references_content WHERE id='".$value_explode."' ";
						mysql_query($sql_delete);
					}
				}
			}
      }
    } // fin !error

    $mts["POST PROCESSING"]["end"] = microtime(true);
  }
}
else { // $_SERVER['REQUEST_METHOD'] != 'POST'
  $mts["NORMAL PROCESSING"]["start"] = microtime(true);
  $nom = $data[0];
  $fastdesc = $data[3];
  $alias = $data[5];
  $keywords = $data[6];
  $familiesHidden = $data[4];
  $familiesTab    = explode(',', $familiesHidden);
  $familiesHidden = $familiesShown  = '';

  $cur_cat_id = $cur_cat_name = null;
  for($i = 0; $i < count($familiesTab); ++$i) {
    if(preg_match('/^[0-9]+$/', $familiesTab[$i])) {
      if(($result = & $handle->query('select name from families_fr where id = \'' . $handle->escape($familiesTab[$i]). '\'', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1) {
        $record = & $handle->fetch($result);

        $familiesHidden .= $familiesTab[$i] . ',';

        if($familiesShown != '') {
          $familiesShown .= ' - ';
        }
        // code redondant avec linkFamily.php (en cas de modif ...)
        $familiesShown .= '<a href="javascript:removeFamily(\\\'' . $familiesTab[$i] . '\\\', \\\'' . to_entities(str_replace(array('"', "'", '&', '(', ')'), array(' ', ' ', '', '', ''), Utils::toASCII($record[0]))) . '\\\')">' . to_entities(str_replace(array('"', "'", '&', '(', ')'), array(' ', ' ', '', '', ''), Utils::toASCII($record[0]))) . '</a>';
        if (!isset($cur_cat_id)) {
          $cur_cat_id = $familiesTab[$i];
          $cur_cat_name = $record[0];
        }
      }
    }
  }

  //////////////////////////////////////////

  $productsHidden = $data[12];
  $productsLinked = $data[13];

  $productsTab    = explode(',', $productsHidden);
  $productsHidden = $productsShown  = '';

  for ($i=0; $i<count($productsTab); ++$i) {
    if (preg_match('/^[0-9]+$/', $productsTab[$i])) {
      if (($result = & $handle->query('select p.name, a.nom1 from products_fr p, advertisers a where p.active = 1 and p.id = \'' . $handle->escape($productsTab[$i]). '\' and p.idAdvertiser = a.id', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1) {
        $record = & $handle->fetch($result);
        $record[0] .= ' #' . $record[1] . '#';
        $productsHidden .= $productsTab[$i] . ',';

        if ($productsShown != '') {
          $productsShown .= ' - ';
        }
        // code redondant avec linkProduct.php (en cas de modif ...)
        $productsShown .= '<a href="javascript:removeProduct(\\\'' . $productsTab[$i] . '\\\', \\\'' . to_entities(str_replace(array('"', "'", '&', '(', ')'), array(' ', ' ', '', '', ''), Utils::toASCII($record[0]))) . '\\\')">' . to_entities(str_replace(array('"', "'", '&', '(', ')'), array(' ', ' ', '', '', ''), Utils::toASCII($record[0]))) . '</a>';
      }
    }
  }
  if ($productsShown == '') {
    $productsShown = ' Actuellement aucun produit lié.';
  }

  $advertiser = $data[1];

  if ($isSupplier = isset($suppliers[$advertiser])) {
    $prixPublic     = $suppliers[$advertiser][1] == 1;
    $margeRemiseDft = $suppliers[$advertiser][2];
    $arrondi        = $suppliers[$advertiser][3];
    $idTVAdft       = $suppliers[$advertiser][4];
  }

  $desc = $data[7];
  $descd = $data[8];
  $ean = $data[24];
  $warranty = $data[25];

  $title_tag = $data[26];
  $meta_desc_tag = $data[27];

  $shipping_fee = $data[28];
  $video_code = $data[29];

  $price = $data[15];

  $delai_livraison = $data[20];
  $contrainteProduit = $data[21];
  $PAsEstimate = $data[30];
  $AAsEstimate = $data[31];

  switch(strtolower($price)) {
    case 'sur demande'    : $typeprice = 1; $refSupplier = $price = $price2 = $unite = $marge = $remise = $idTVA = $delai_livraison = $contrainteProduit = ""; break;
    case 'sur devis'      : $typeprice = 2; $refSupplier = $price = $price2 = $unite = $marge = $remise = $idTVA = $delai_livraison = $contrainteProduit = ""; break;
    case 'nous contacter' : $typeprice = 3; $refSupplier = $price = $price2 = $unite = $marge = $remise = $idTVA = $delai_livraison = $contrainteProduit = ""; break;
    case 'ref'            : $typeprice = 4; $refSupplier = $price = $price2 = $unite = $marge = $remise = $idTVA = ''; break;
    default               : $typeprice = 0; $refSupplier = $data[14]; $price2 = $data[16]; $unite = $data[17]; $marge = $remise = $data[18]; $idTVA = $data[19];
  }


  // Load références
  if($typeprice == 4) {

    $code_ref_tab_cols  = array();
    $code_ref_tab_next  = array();
    $code_ref_lines = array();

    if($type_a == '') {

      if($result = & $handle->query('select content from references_cols where idProduct = \'' . $handle->escape($_GET['id']) . '\'', __FILE__, __LINE__)) {
        $data_ref = & $handle->fetch($result);
        $code_ref_tab_cols = mb_unserialize($data_ref[0]);
      }
      if($result = $handle->query("
        SELECT id, label, content, refSupplier, price, price2, unite, marge, idTVA, ecotax
        FROM references_content
        WHERE idProduct = '" . $handle->escape($_GET['id']) . "' AND deleted = 0 AND vpc = 1
        ORDER BY classement", __FILE__, __LINE__)) {
        while($data_ref = $handle->fetchArray($result)) {
          $data_content = mb_unserialize($data_ref['content']);

          $data_ref['content'] = array();
          for ($i=0; $i<count($data_content); $i++)
            $data_ref['content'][] = $data_content[$i];
          $data_ref['content'] = implode("<->",$data_ref['content']);
          $code_ref_lines[] = $data_ref;
        }
      }
    }
    elseif($type_a == 'add' || $type_a == 'edit' || $type_a == 'backup') {
      $letter_load_ref = ($type_a == 'add') ? 'c' : (($type_a == 'backup') ? 'b' : 'm');

      if($result = & $handle->query('select ref from products_add where id = \'' . $handle->escape($_GET['id']) . '\' and type = \'' . $letter_load_ref . '\'', __FILE__, __LINE__)) {
        $data_ref = & $handle->fetch($result);
        $code_ref_tab = explode('<=>', $data_ref[0]);

        $code_ref_tab_next = explode('<_>', $code_ref_tab[1]);
        $code_ref_tab_cols = explode('<->', $code_ref_tab_next[0]);


        // si c'est un fournisseur et le libellé de l'avant dernière colonne est correct, on estime que ref est correctement serialize
        if ($isSupplier && $code_ref_tab_cols[count($code_ref_tab_cols)-2] == 'Prix Public') {
          for ($i = 1; $i < count($code_ref_tab_next); $i++) {
            $line_data = explode('<->', $code_ref_tab_next[$i]);

            $code_ref_line = array();
            $code_ref_line['id']          = & $line_data[0];
            $code_ref_line['label']       = & $line_data[1];
            $code_ref_line['refSupplier'] = & $line_data[2];

            $code_ref_line['unite']       = & $line_data[count($line_data)-6];
            $code_ref_line['idTVA']       = & $line_data[count($line_data)-5];
            $code_ref_line['price2']      = & $line_data[count($line_data)-4];
            $code_ref_line['marge']       = & $line_data[count($line_data)-3];
            $code_ref_line['price']       = & $line_data[count($line_data)-2];
            $code_ref_line['ecotax']      = & $line_data[count($line_data)-1];

            $code_ref_line['content'] = array();
            for ($j=3; $j<$line_colscount-6; $j++)
              $code_ref_line['content'][] = $line_data[$j];
            $code_ref_line['content'] = implode("<->",$code_ref_line['content']);

            $code_ref_lines[] = & $code_ref_line;
          }
        }
        else {
          for ($i = 1; $i < count($code_ref_tab_next); $i++) {
            $line_data = explode('<->', $code_ref_tab_next[$i]);

            // si la première colonne n'a pas le libellé de référence TC, on estime qu'il faut rajouter une colonne avec
            if ($code_ref_tab_cols[0] != 'Référence TC')
              $line_data = explode('<->', '<->' . $code_ref_tab_next[$i]);
            else
              $line_data = explode('<->', $code_ref_tab_next[$i]);

            $code_ref_line = array();
            $code_ref_line['id']          = $line_data[0];
            $code_ref_line['label']       = $line_data[1];
            $code_ref_line['price']       = $line_data[count($line_data)-1];

            $code_ref_line['content']     = $line_data[2];
            for($j = 3; $j < count($line_data) - 1; ++$j)
              $code_ref_line['content'] .= '<->' . $line_data[$j];

            $code_ref_line['refSupplier'] = '';
            $code_ref_line['unite']       = '';
            $code_ref_line['idTVA']       = '';
            $code_ref_line['price2']      = '';
            $code_ref_line['marge']       = '';
            $code_ref_line['ecotax']      = '';

            $code_ref_lines[] = & $code_ref_line;
          }
        }
      }
    }
    elseif ($type_a == 'add_adv' || $type_a == 'edit_adv') {
      $letter_load_ref = $type_a == 'add_adv' ? 'c' : 'm';

      $result = & $handle->query("select ref from products_add_adv where id = '" . $handle->escape($_GET['id']) . "' and type = '" . $letter_load_ref . "'", __FILE__, __LINE__);
      $data_ref = & $handle->fetch($result);
      //print '$data_ref ' . print_r($data_ref, true) . "\n";
      $code_ref_tab = explode('<=>', $data_ref[0]);

      $code_ref_tab_rows = explode('<_>', $code_ref_tab[1]);
      $code_ref_tab_cols = explode('<->', $code_ref_tab_rows[0]);

      if ($isSupplier) {
        // Le tableau en entrée a pour colonne : Référence TC, Libellé, Référence, usercols[1..n], Unité, Taux TVA, Prix
        $code_ref_tab_cols2[0] = 'Référence TC';
        $code_ref_tab_cols2[1] = 'Libellé';
        $code_ref_tab_cols2[2] = 'Référence Fournisseur';

        $colscount = count($code_ref_tab_cols);
        for ($i = 3; $i < $colscount-3; $i++)
          $code_ref_tab_cols2[$i] = $code_ref_tab_cols[$i];

        $code_ref_tab_cols2[$colscount - 3] = 'Unité';
        $code_ref_tab_cols2[$colscount - 2] = 'Taux TVA';
        $code_ref_tab_cols2[$colscount - 1] = 'Prix Fournisseur';
        $code_ref_tab_cols2[$colscount + 0] = 'Marge';
        $code_ref_tab_cols2[$colscount + 1] = 'Prix Public';
        $code_ref_tab_cols2[$colscount + 2] = 'Éco Taxe';

        $code_ref_tab_cols = & $code_ref_tab_cols2;

        for ($i = 1; $i < count($code_ref_tab_rows); $i++) {
          $line_data = explode('<->', $code_ref_tab_rows[$i]);

          $code_ref_line = array();
          $code_ref_line['id']          = $line_data[0];
          $code_ref_line['label']       = $line_data[1];
          $code_ref_line['refSupplier'] = $line_data[2];

          $code_ref_line['unite']       = $line_data[count($line_data)-3];
          $code_ref_line['idTVA']       = $line_data[count($line_data)-2];
          $code_ref_line['marge']       = $margeRemiseDft;
          $code_ref_line['ecotax']      = 0;

          if ($prixPublic) {
            $code_ref_line['price']  = $line_data[count($line_data)-1];
            $code_ref_line['price2'] = round($code_ref_line['price'] * (100-$margeRemiseDft) / 100, 2);
          }
          else {
            $code_ref_line['price2']  = $line_data[count($line_data)-1];
            $code_ref_line['price'] = round($code_ref_line['price2'] * (100+$margeRemiseDft) / 100, 2);
          }

          $code_ref_line['content'] = array();
          for ($j=3; $j<$line_colscount-5; $j++)
            $code_ref_line['content'][] = $line_data[$j];
          $code_ref_line['content'] = implode("<->",$code_ref_line['content']);

          $code_ref_lines[] = $code_ref_line;

        }
      }
      else {
        // Le tableau en entrée a pour colonne : Référence TC, Libellé, usercols[1..n], Prix
        /*$code_ref_tab_cols2[0] = 'Référence TC';
        $code_ref_tab_cols2[1] = 'Libellé';
        $code_ref_tab_cols2[2] = 'Référence Fournisseur';

        $colscount = count($code_ref_tab_cols);
        for ($i = 2; $i < $colscount-1; $i++)
          $code_ref_tab_cols2[$i+1] = $code_ref_tab_cols[$i];

        $code_ref_tab_cols2[$colscount + 0] = 'Unité';
        $code_ref_tab_cols2[$colscount + 1] = 'Taux TVA';
        $code_ref_tab_cols2[$colscount + 2] = 'Prix Fournisseur';
        $code_ref_tab_cols2[$colscount + 3] = 'Marge';
        $code_ref_tab_cols2[$colscount + 4] = 'Prix Public';

        $code_ref_tab_cols = & $code_ref_tab_cols2;*/

        for ($i = 1; $i < count($code_ref_tab_rows); $i++) {
          $line_data = explode('<->', $code_ref_tab_rows[$i]);

          $colscount = count($line_data);

          $code_ref_line = array();
          $code_ref_line['id']          = $line_data[0];
          $code_ref_line['label']       = $line_data[1];
          $code_ref_line['refSupplier'] = '';

          $code_ref_line['unite'] = 0;
          $code_ref_line['idTVA'] = 0;
          $code_ref_line['marge'] = 0;
          $code_ref_line['ecotax'] = 0;

          $code_ref_line['price']  = $line_data[$colscount-1];
          $code_ref_line['price2'] = '';

          for($j = 2; $j < $colscount - 1; ++$j) {
            if ($j > 2) $code_ref_line['content'] .= '<->';
            $code_ref_line['content'] .= $line_data[$j];
          }
          $code_ref_lines[] = $code_ref_line;
        }
      }
    }

    // si ce n'est pas un fournisseur, ou que l'avant dernière colonne n'est pas celle d'un fournisseur, on estime qu'on a des données d'un annonceur
    if (!$isSupplier || $code_ref_tab_cols[count($code_ref_tab_cols)-2] != 'Prix Public') {
      $code_ref_tab_cols2 = array();

      $i = 1; $j = 3;
      // si la première colonne n'a pas le libellé de référence TC, on estime qu'il faut rajouter une colonne avec
      if ($code_ref_tab_cols[0] == 'Référence TC') $i++;

      while ($i < count($code_ref_tab_cols)-1) {
        $code_ref_tab_cols2[$j] = $code_ref_tab_cols[$i];
        $i++; $j++;
      }
      $j -= 3; // nombre de colonne dans content

      $code_ref_tab_cols2[0] = 'Référence TC';
      $code_ref_tab_cols2[1] = 'Libellé';
      $code_ref_tab_cols2[2] = 'Référence Fournisseur';

      $code_ref_tab_cols2[3 + $j] = 'Unité';
      $code_ref_tab_cols2[4 + $j] = 'Taux TVA';
      $code_ref_tab_cols2[5 + $j] = 'Prix Fournisseur';
      $code_ref_tab_cols2[6 + $j] = 'Marge';
      $code_ref_tab_cols2[7 + $j] = 'Prix Public';
      $code_ref_tab_cols2[8 + $j] = 'Éco Taxe';

      $code_ref_tab_cols = & $code_ref_tab_cols2;
    }
  }


    $gen = $data[9]  ? true : false;
    $ind = $data[10] ? true : false;
    $col = $data[11] ? true : false;

  $mts["NORMAL PROCESSING"]["end"] = microtime(true);
}

$mts["TOTAL TIME"]["end"] = microtime(true);
///////////////////////////////////////////////////////////////////////////

$filter = (isset($_GET['filter']) && $_GET['filter'] == '1') ? 1 : 0;
$liste  = array(10, 25, 50, 75);
$lmois  = array(3, 4, 5, 6, 7, 8, 9, 10, 11, 12);

if(isset($_GET['nb']) && in_array($_GET['nb'], $liste)) {
  $nb   = $_GET['nb'];
  $type = 1;
}
elseif(isset($_GET['lettre']) && preg_match('/^[0a-z]$/', $_GET['lettre'])) {
  $lettre = $_GET['lettre'];
  $type   = 0;
}
elseif(isset($_GET['month']) && in_array($_GET['month'], $lmois)) {
  $month = $_GET['month'];
  $type   = 2;
}
else {
  $type = 1;
  $nb   = 10;
}

$mts["PRODUCT LISTING"]["start"] = microtime(true);
?>
<div class="titreStandard">Liste des produits</div><br/>
<div class="bg">
  <div align="center">
    <a href="index.php?nb=10&<?php print(session_name() . '=' . session_id() . '&filter=' . $filter) ?>">Récents</a>
  - <a href="index.php?lettre=0&<?php print(session_name() . '=' . session_id() . '&filter=' . $filter) ?>">0-9</a>
   <?php for ($i=ord('a'); $i<=ord('z'); $i++) { ?>
  - <a href="index.php?lettre=<?php echo chr($i)."&filter=".$filter ?>"><?php echo strtoupper(chr($i)) ?></a>
   <?php } ?>
</div>
<br/>
<br/>
<?php
$mts["PRODUCT LISTING"]["end"] = microtime(true);

if($user->rank == COMM) {
  if($type == 1) {
    $_url = 'nb=' . $nb;
  }
  elseif($type == 2) {
    $_url = 'month=' . $month;
  }
  else {
    $_url = 'lettre=' . $lettre;
  }

  print '<a href="index.php?'.$_url.'&filter=';

  if($filter) {
    print '0">Afficher tous les produits';
  }
  else {
    print '1">Afficher uniquement les produits de vos annonceurs';
  }

  print '</a><br/><br/>';
}

$mts["PRODUCT LATEST"]["start"] = microtime(true);

?>
<br/>
<br/>
Afficher les
<select onChange="goTo('index.php?nb=' + this.options[this.selectedIndex].value + '&filter=<?php echo $filter ?>')">
<?php foreach($liste as $k => $v) { ?>
  <option value="<?php echo $v ?>"<?php echo ($nb==$v?" selected":"") ?>><?php echo $v ?></option>
<?php } ?>
</select> derniers produits ajoutés ou mis à jour.
<form method="get" action="index.php">
  Afficher les produits non mis à jour depuis
  <select name="month">
   <?php foreach($lmois as $k => $v) { ?>
    <option value="<?php echo $v ?>"<?php echo ($month==$v?" selected":"") ?>><?php echo $v ?></option>
   <?php } ?>
  </select> mois.
  <input type="hidden" name="filter" value="<?php echo $filter ?>">
  <input type="button" value="Go" onClick="this.form.submit(); this.disabled=true">
</form>
<br/>
<br/>
<?php

$mts["PRODUCT LATEST"]["end"] = microtime(true);
$mts["PRODUCT UPDATED"]["start"] = microtime(true);

if($type == 0) {
  print('<b>Liste des produits dont le nom commence par ');
  if($lettre == '0') {
    $pattern = 'REGEXP(\'^[0-9]\')';
    print('un chiffre :</b><br/><br/>');
  }
  else {
    $pattern = 'like \'' . $lettre . '%\'';
    print('la lettre ' . strtoupper($lettre) . ' :</b><br/><br/>');
  }

  if($user->rank == COMM && $filter == 1) {
    $p = & displayGProducts($handle, 'and pfr.name '.$pattern.' group by pfr.id order by pfr.name', $user->id);
  }
  else {
    $p = & displayGProducts($handle, 'and pfr.name '.$pattern.' group by pfr.id order by pfr.name');
  }
}
elseif($type == 2) {
  print('<b>Liste des produits non mis à jour depuis ' . $month . ' mois : </b><br/><br/>');
  $line = time() - $month * 30 * 24 * 3600;
  if($user->rank == COMM && $filter == 1) {
    $p = & displayGProducts($handle, 'and p.timestamp <  ' . $line . ' group by p.id order by p.timestamp', $user->id);
  }
  else  {
    $p = & displayGProducts($handle, 'and p.timestamp <  ' . $line . ' group by p.id order by p.timestamp');
  }
}
else {
  print('<b>Liste des '.$nb.' derniers produits ajoutés ou mis à jour : </b><br/><br/>');

  if($user->rank == COMM && $filter == 1) {
    $p = & displayGProducts($handle, 'group by p.id order by p.timestamp desc limit ' . $nb, $user->id);
  }
  else {
    $p = & displayGProducts($handle, 'group by p.id order by p.timestamp desc limit ' . $nb);
  }
}

if(count($p) > 0) {
  print('<ul>');
  foreach($p as $k => $v) {
    $product = & loadProduct($handle, $v[0]);
    $partner = & loadAdvertiser($handle, $product[1]);
    $pdt["pic_url"] = is_file(PRODUCTS_IMAGE_INC."thumb_big/".$v[0]."-1".".jpg") ? PRODUCTS_IMAGE_SECURE_URL."thumb_big/".$v[0]."-1".".jpg" : PRODUCTS_IMAGE_SECURE_URL."no-pic-thumb_big.gif";

    $extra = $product[3] . '<br />';
    $extra .= '<a href="' . ADMIN_URL . 'advertisers/edit.php?id=' . $product[1] . '&' . session_name() . '=' . session_id() . '">' . to_entities($partner[2]) . '</a>';

    print('<li style="clear: both; margin-top: 5px;"> <a href="' . URL . 'produits/' . $v[2] . '-' . $v[0] . '-' . $v[3] . '.html" target="_blank"><img style="margin-top: -5px; height: 40px; width: 55px; margin-right: 5px; float: left; overflow: hidden;" src="' . $pdt["pic_url"] . '" border="0"></a> <a href="edit.php?id=' . $v[0] . '&' . session_name() . '=' . session_id() . '">' . to_entities($v[1]) . '</a> <br />' . $extra . '<br />' );
  }
  print('</ul>');
}

$mts["PRODUCT UPDATED"]["end"] = microtime(true);

?>
</div>
<br/>
<br/>
<div class="titreStandard">
<?php

$mts["MISC"]["start"] = microtime(true);

$newname = $ok ? $nom : $data[0];
$newname = to_entities($newname);

if($type_a == 'add') {
  print('Validation de la fiche produit ' . $newname);
}
elseif($type_a == 'add_adv') {
  print('Validation de la fiche produit extranet ' . $newname);
}
elseif($type_a == 'backup') {
  print('Restauration de la fiche produit ' . $newname);
}
elseif($type_a == 'edit') {
  print('Validation de la modif. de la fiche produit ' . $newname);
}
elseif($type_a == 'edit_adv') {
  print('Validation de la modif. de la fiche produit extranet ' . $newname);
}
else {
  print('Edition de la fiche produit ' . $newname);
}

if($_SERVER['REQUEST_METHOD'] == 'GET' || $error) {
  if($type_a == '') {
    print(' | <a href="' . URL . 'produits/' . $data[23] . '-' . $_GET['id'] . '-' . $data[22] . '.html" target="_blank">Visualiser la fiche produit</a>');
  }
  else {
    print(' | <a href="pp_product.php?' . session_name() . '=' . session_id() . '&id=' . $_GET['id'] . '&type=' . $type_a . '" target="_blank">Visualiser la fiche produit</a>');
  }

  if($user->rank != CONTRIB) {
    if($type_a != 'backup') {
      if($type_a == 'add' || $type_a == 'add_adv') {
        print " | <a href=\"del.php?id=".$_GET['id']."&type=".$type_a."\" onclick=\"return confirm('Etes-vous sûr de vouloir rejeter cette fiche ?')\">Rejeter la fiche</a>";
      }
      elseif($type_a == 'edit' || $type_a == 'edit_adv') {
        print " | <a href=\"del.php?id=".$_GET['id']."&type=".$type_a."\" onclick=\"return confirm('Etes-vous sûr de vouloir rejeter la modification de cette fiche ?')\">Rejeter la modification</a>";
      }
      else {
        print " | <a href=\"copy.php?id=".$_GET['id']."&type=".$type_a."\" onclick=\"return confirm('Etes-vous sûr de vouloir copier cette fiche ?')\">Copier la fiche</a>";
        print " | <a href=\"del.php?id=".$_GET['id']."&type=".$type_a."\" onclick=\"return confirm('Etes-vous sûr de vouloir effacer cette fiche ?')\">Supprimer la fiche</a>";
      }
    }

    // Affichage classique on propose la bk si y en a un
    if(isBackup($handle, $_GET['id']) && $type_a == '') {
      print(' | <a href="edit.php?type=backup&id=' . $_GET['id'] . '&' . session_name() . '=' . session_id() . '">Afficher la sauvegarde</a>');
    }
    elseif($type_a == 'backup') {
      print(' | <a href="edit.php?id=' . $_GET['id'] . '&' . session_name() . '=' . session_id() . '">Afficher la version courante</a>');
    }

    if($type_a != 'add' && $type_a != 'add_adv') {
      print(' | <a href="../actions.php?type=produit&id=' . $_GET['id'] . '&' . session_name() . '=' . session_id() . '&type_a=' . $type_a . '">Historique des actions</a>');
    }
  }
}

?>
</div>
<br/>
<div class="bg">
<?php

$next = true;

if($_SERVER['REQUEST_METHOD'] == 'POST') {
  if(!$error) {
    if($ok) {
      if($type_a == 'add') {
        $out = 'Produit validé avec succès.<br/><br/>| <a href="add_wait.php?' . session_name() . '=' . session_id() . '">Retour à la liste des produits à valider</a> |';
      }
      elseif($type_a == 'add_adv') {
        $out = 'Produit extranet validé avec succès.<br/><br/>| <a href="add_wait.php?' . session_name() . '=' . session_id() . '&from=adv">Retour à la liste des produits extranet à valider</a> |';
      }
      elseif($type_a == 'backup') {
        $out = 'Produit restauré avec succès.';
      }
      elseif($type_a == 'edit') {
        $out = 'Modification validée avec succès.<br/><br/>| <a href="edit_wait.php?' . session_name() . '=' . session_id() . '">Retour à la liste des modifications à valider</a> |';
      }
      elseif($type_a == 'edit_adv') {
        $out = 'Modification extranet validée avec succès.<br/><br/>| <a href="edit_wait.php?' . session_name() . '=' . session_id() . '&from=adv">Retour à la liste des modifications extranet à valider</a> |';
      }
      else {
        if($user->rank != CONTRIB) {
          $out = 'Produit modifié avec succès.<br/><br/>| <a href="../advertisers/edit.php?' . session_name() . '=' . session_id() . '&id=' . $advertiser . '">Liste des produits de l\'annonceur</a> |';
        }
        else {
          $out = 'Produit modifié avec succès. La modification sera en ligne dès qu\'elle sera validée par un commercial.<br/><br/>| <a href="../advertisers/edit.php?' . session_name() . '=' . session_id() . '&id=' . $advertiser . '">Liste des produits de l\'annonceur</a> |';
        }
      }
    }
    else {
      $out = 'Erreur lors de la modification / validation du produit.<br/>'.$errorstring;
    }
    print('<div class="confirm">' . $out . '</div><br/><br/>');
    $next = false;
  }
  else {
    print('<font color="red">Une ou plusieurs erreurs sont survenues lors de la validation : <br/>' . $errorstring  . '</font><br/><br/>');
    $next = true;
  }
}

$mts["MISC"]["end"] = microtime(true);

if ($next) {
?>
<script type="text/javascript">
<!--

// Variables produits
var productId         = <?php echo $_GET['id'] ?>;
var productFamilies   = <?php echo json_encode(explode(',', substr($familiesHidden, 0, -1))) ?>;
var typePrice         = <?php echo $typeprice ?>;
var refSupplier       = '<?php echo $refSupplier ?>';
var price             = isNaN(parseFloat('<?php echo $price ?>'))              ? '' : parseFloat('<?php echo $price ?>');
var price2            = isNaN(parseFloat('<?php echo $price2 ?>'))             ? '' : parseFloat('<?php echo $price2 ?>');
var unite             = isNaN(  parseInt('<?php echo $unite ?>'))              ? '' :   parseInt('<?php echo $unite ?>');
var marge             = isNaN(parseFloat('<?php echo $marge ?>'))              ? '' : parseFloat('<?php echo $marge ?>');
var remise            = isNaN(parseFloat('<?php echo $marge ?>'))              ? '' : parseFloat('<?php echo $marge ?>');
var idTVA             = isNaN(  parseInt('<?php echo $idTVA ?>'))              ? 0  :   parseInt('<?php echo $idTVA ?>');
var delai_livraison   = '<?php echo addslashes($delai_livraison) ?>';
var shipping_fee   = '<?php echo addslashes($shipping_fee) ?>';
var contrainteProduit = isNaN(  parseInt('<?php echo $contrainteProduit ?> ')) ? '' :   parseInt('<?php echo $contrainteProduit ?> ');
if (contrainteProduit == 0) contrainteProduit = '';
var p_as_estimate = <?php echo $PAsEstimate?$PAsEstimate:0 ?>;
var a_as_estimate = <?php echo $AAsEstimate?$AAsEstimate:0 ?>;

var idTVAdftDft = <?php echo $idTVAdftDft ?>;

var listeTVAs = <?php echo json_encode($listeTVAs) ?>;

function displayOptionsTVA(selected, displayType)
{
	var stringOptionsTVAs = '';

	for (var indexTVA = 0; indexTVA < listeTVAs.length; indexTVA++)
	{
		stringOptionsTVAs += '<option value="' + listeTVAs[indexTVA][0] + '"' + ( (listeTVAs[indexTVA][0] == selected) ? ' selected' : '' ) + '>';
		switch(displayType)
		{
			case 'all' : stringOptionsTVAs += listeTVAs[indexTVA][1] + ' ' + listeTVAs[indexTVA][2]; break;
			case 'short' : stringOptionsTVAs += listeTVAs[indexTVA][1].toLowerCase().replace(/taux\ /,'') + ' ' + listeTVAs[indexTVA][2]; break;
			case 'rate only' : stringOptionsTVAs += listeTVAs[indexTVA][2]; break;

			default : stringOptionsTVAs += listeTVAs[indexTVA][2]; break;
		}
		stringOptionsTVAs += '%</option>';
	}

	return stringOptionsTVAs;
}

function displayTVA(idTVA, displayType)
{
	var indexTVA = 0;
	var stringTVA = '';

	while (indexTVA < listeTVAs.length && listeTVAs[indexTVA][0] != idTVA) indexTVA++;
	if (listeTVAs[indexTVA][0] == idTVA)
	{
		switch(displayType)
		{
			case 'all' : stringTVA += listeTVAs[indexTVA][1] + ' à '; break;
			case 'short' : stringTVA += listeTVAs[indexTVA][1].toLowerCase().replace(/taux\ /,'') + ' à '; break;
			case 'rate only' : break;

			default : break;
		}
		stringTVA += listeTVAs[indexTVA][2] + '%';

		return stringTVA;
	}
	else
		return 'Invalide';
}

function getValidTVA(idTVA, idTVAdft)
{
	if (isNaN(idTVA = parseInt(idTVA))) // si taux de TVA non valide, on retourn le taux par défaut du fournisseur
		return idTVAdft;
	else // si taux de TVA valide, on vérifie qu'il existe bien
	{
		var indexTVA = 0;
		var TVAfound = false;
		for (indexTVA = 0 ; indexTVA < listeTVAs.length ; indexTVA++)
		{
			if (listeTVAs[indexTVA][0] == idTVA)
			{
				TVAfound = true;
				break;
			}
		}
		if (TVAfound) // s'il existe on le retourne
			return idTVA;
		else // sinon on retourne celui par défaut
			return idTVAdft;
	}

}


/*
suppliersData, tableau contenant des informations utiles à la saisie des produits pour tous les fournisseurs
suppliersData[i][0] = nom1
suppliersData[i][1] = prixPublic
suppliersData[i][2] = margeRemise
suppliersData[i][3] = arrondi
suppliersData[i][4] = idTVA
 */

var suppliersData = new Array();

var i, j;

<?php
$mts["JS ADV LIST"]["start"] = microtime(true);

foreach($suppliers as $k => $v)
{
	print('suppliersData[' . $k . '] = new Array(');
    for ($i = 0; $i < 5; ++$i)
	{
		if ($i > 0) print(', ');
		print('"' . to_entities($v[$i]) . '"');
	}
	print(");\n");
}
$mts["JS ADV LIST"]["end"] = microtime(true);

?>

// Variables fournisseurs par défaut
var advCurSelected = advPrevSelected = advPrevShowRef = <?php echo $advertiser ?>;
var isSupplier = typeof(suppliersData[advCurSelected]) != 'undefined' ? true : false;
var prixPublic, margeRemiseDft, arrondi, idTVAdft;

function check_field(value, type_value)
{
	if (value != '')
	{
		var tempvalue;
		switch (type_value)
		{
			case 'int' : tempvalue = parseInt(value); break;
			case 'float' : tempvalue = parseFloat(value); break;
			default : tempvalue = parseFloat(value); break;
		}

		if (isNaN(tempvalue)) return '0';
		else return tempvalue;
	}
	else return '';
}


function namesearch()
{

    var handle = document.addProduct;
    if(handle.nom.value.length < 3)
    {
        alert('Vous devez saisir au moins 3 caractères du nom du produit avant de lancer la recherche.');
        handle.nom.focus();
    }
    else
    {
        window.open('search.php?<?php print(session_name() . '=' . session_id()) ?>&name=' + escape(handle.nom.value) , 'Recherche', 'menubar=no,scrollbars=yes,top=100,left=400,height=500,width=700');
    }
}

var familiesHidden = '<?php print($familiesHidden) ?>';
var familiesShown  = '<?php print($familiesShown) ?>';

AJAXHandleSE = {
	type : "GET",
	url: "AJAX_searchFamilies.php",
	dataType: "json",
	error: function (XMLHttpRequest, textStatus, errorThrown) {
		$("#PerfReqLabel-LinkedFamilies").text(textStatus);
                $('div[class=legend]').text('0 familles correspondent à votre titre produit');
	},
	success: function (data, textStatus) {
          var html = '';
          var nbFamilies = 0;
          if(data.error){
            $('.familySE-window-bg').append('<div id="SearchEngineResponse">'+data.error+'</div>');
            $('div[class=legend]').text('0 familles correspondent à votre titre produit');
          }else if(data.firstLevel){
            $.each(data.firstLevel, function(key1, level1){
              html += '<h2 class="familyFirstLevel">'+level1.cat_name+'</h2><ul class="familySecondLevel">';
              $.each(level1.children, function(key2, level2){
                nbFamilies += level2.children.length;
                $.each(level2.children, function(key3, level3){
                  html += '<li>'+level2.cat_name+' > <span class="familyThirdLevel" onClick="addFamily('+level3.id+')">'+level3.cat_name+' ('+level3.nbr_active_products+')</span></li>';
                });
              });
              html += '</ul>'
            });
            $('div[class=legend]').text(nbFamilies+' familles correspondent à votre titre produit');
            $('.familySE-window-bg').append('<div id="SearchEngineResponse">'+html+'</div>');
          }
        }
}

function familiesSearchEngine(){
  var ChampNom = $('input[name=nom]')[0];

  var Legend = $('div[class=legend]');
  if(ChampNom.value.length < 3){
      Legend.text('Vous devez saisir au moins 3 caractères du nom du produit avant de lancer la recherche.');
      ChampNom.focus();
  }
  else{
        AJAXHandleSE.data = "search="+ChampNom.value+"&search_type=2";
        $.ajax(AJAXHandleSE);

  }
}

function addFamily(id){

	if (id != 0)
	{
		var data = familiesHidden.split(",");
		familiesHidden = "";
		var id = parseInt(id);
		if (PMP.fse.mod == "add")
		{
			for (i = 0; i < data.length-1; i++) {
				if (parseInt(data[i]) != id) familiesHidden += data[i] + ",";
			}
			familiesHidden += id + ",";
		}

        PMP.Family.fillTable();
        }
}

function familyLink(val)
{
    if(val == '')
    {
        alert('Merci de sélectionner une sous-famille avant de la lier.');
        document.addProduct.familiesList.focus();
        return;
    }

    if(val == 0)
    {
        alert('Vous ne pouvez lier à un produit que des sous-familles de niveau 2.');
        document.addProduct.familiesList.focus();
        return;
    }

    if(familiesHidden.indexOf(val + ',', 0) != -1)
    {
        alert('Cette sous-famille est déjà présente dans la liste des sous-familles liées.');
        document.addProduct.familiesList.focus();
        return;
    }


    document.addProduct.ok.disabled  = true;
    document.addProduct.nok.disabled = true;

    document.addProduct.familiesLinker.disabled = true;
    document.addProduct.familiesLinker.value = 'Liaison en cours';

    /////////////

    var uniq = new Date();
        uniq = uniq.getTime();

    var query = '<?php print(session_name() . '=' . session_id()) ?>&time='+ uniq +'&id=' + escape(val);

    var data  = getContent('linkFamily.php', query);

    if(data == -1)
    {
        alert('Une erreur est survenue : impossible de récupérer les données de la sous-famille.');
    }
    else
    {
        var tab = data.split('<separator>');

        if(familiesHidden == '')
        {
            familiesShown = '<a href="javascript:removeFamily(\''+tab[0]+'\', \''+tab[1]+'\')">' + tab[1] + '</a>';
        }
        else
        {
            familiesShown += ' - <a href="javascript:removeFamily(\''+tab[0]+'\', \''+tab[1]+'\')">' + tab[1] + '</a>';
        }

        familiesHidden += tab[0] + ',';

        testAndWrite('families', familiesShown + '<input type="hidden" name="familiesHidden" value="'+familiesHidden+'">');
    }

    document.addProduct.ok.disabled  = false;
    document.addProduct.nok.disabled = false;

    document.addProduct.familiesLinker.disabled = false;
    document.addProduct.familiesLinker.value = "Lier";

    document.addProduct.familiesList.focus();

}


function removeFamily(id, name)
{

    var sentence = '<a href="javascript:removeFamily\\(\\\''+id+'\\\', \\\''+name+'\\\'\\)">'+name+'</a>';

    if(familiesHidden == id + ',')
    {
        exp = new RegExp(sentence, '');
    }
    else if(familiesHidden.indexOf(id + ',') == 0)
    {
        exp = new RegExp(sentence + ' - ', '');
    }
    else
    {
        exp = new RegExp(' - ' + sentence, '');
    }

    familiesShown = familiesShown.replace(exp, '');

    if(familiesShown == '')
    {
        familiesShown = ' Actuellement aucune sous-famille associée.';
    }

    var exp = new RegExp(id + ',', '');
    familiesHidden = familiesHidden.replace(exp, '');

    testAndWrite('families', familiesShown + '<input type="hidden" name="familiesHidden" value="'+familiesHidden+'">');


}

var current = '';

function displayAdvertiserProducts(val)
{
    if(val == '')
    {
        alert('Merci de sélectionner un annonceur avant de procéder à l\'affichage de ses produits');
        document.addProduct.padv.focus();
        return;
    }

    var uniq = new Date();
        uniq = uniq.getTime();

    var query = '<?php print(session_name() . '=' . session_id()) ?>&time='+ uniq +'&id=' + escape(val);

    var data  = getContent('getProducts.php', query);

    if(data == -1)
    {
        alert('Une erreur est survenue : impossible de récupérer les données de la sous-famille.');
    }
    else
    {
        var main_tab = data.split('<main_separator>');

        if(main_tab.length < 2)
        {
            testAndWrite('padvDSP', 'Actuellement aucun produit en ligne.');
            return;
        }

        var out = '<select name="products" onChange="productLink(this.value)"><option value="">Sélectionner le produit de ' + main_tab[0] + ' à lier </option><option value=""></option>';
        current = main_tab[0];

        for(i = 1; i < main_tab.length; ++i)
        {
            tab = main_tab[i].split('<separator>');
            out += '<option value="' + tab[0] + '">' + tab[1] + '</option>';

        }

        out += '</select>';

        testAndWrite('padvDSP', out);
    }
}


var productsHidden = '<?php print($productsHidden) ?>';
var productsShown  = '<?php print($productsShown) ?>';

function productLink(val)
{
    if(val == '')
    {
        alert('Merci de sélectionner un produit avant de le lier.');
        document.addProduct.products.focus();
        return;
    }

    if(productsHidden.indexOf(val + ',', 0) != -1)
    {
        alert('Ce produit est déjà lié au produit en cours de création.');
        document.addProduct.products.focus();
        return;
    }


    document.addProduct.ok.disabled  = true;
    document.addProduct.nok.disabled = true;

    /////////////

    var uniq = new Date();
        uniq = uniq.getTime();

    var query = '<?php print(session_name() . '=' . session_id()) ?>&time='+ uniq +'&id=' + escape(val);

    var data  = getContent('linkProduct.php', query);

    if(data == -1)
    {
        alert('Une erreur est survenue : impossible de récupérer les données du produit.');
    }
    else
    {
        var tab = data.split('<separator>');
        tab[1] += ' #' + current + '#';

        if(productsHidden == '')
        {
            productsShown = '<a href="javascript:removeProduct(\''+tab[0]+'\', \''+tab[1]+'\')">' + tab[1] + '</a>';
        }
        else
        {
            productsShown += ' - <a href="javascript:removeProduct(\''+tab[0]+'\', \''+tab[1]+'\')">' + tab[1] + '</a>';
        }

        productsHidden += tab[0] + ',';

        testAndWrite('productsLinkedLead', productsShown + '<input type="hidden" name="productsHidden" value="'+productsHidden+'">');
    }

    document.addProduct.ok.disabled  = false;
    document.addProduct.nok.disabled = false;

    document.addProduct.products.focus();

}


function removeProduct(id, name)
{

    var sentence = '<a href="javascript:removeProduct\\(\\\''+id+'\\\', \\\''+name+'\\\'\\)">'+name+'</a>';

    if(productsHidden == id + ',')
    {
        exp = new RegExp(sentence, '');
    }
    else if(productsHidden.indexOf(id + ',') == 0)
    {
        exp = new RegExp(sentence + ' - ', '');
    }
    else
    {
        exp = new RegExp(' - ' + sentence, '');
    }

    productsShown = productsShown.replace(exp, '');

    if(productsShown == '')
    {
        productsShown = ' Actuellement aucun produit lié.';
    }

    var exp = new RegExp(id + ',', '');
    productsHidden = productsHidden.replace(exp, '');

    testAndWrite('productsLinkedLead', productsShown + '<input type="hidden" name="productsHidden" value="'+productsHidden+'">');


}



function upload()
{
    handle = document.addProduct;
    handle.target = '_blank';
    handle.action = 'upload.php?id=<?php print($_GET['id'] . '&' . session_name() . '=' . session_id() . '&type=' . $type_a) ?>';

    handle.submit();

    handle.action = 'edit.php?<?php print(session_name() . '=' . session_id() . '&type=' . $type_a . '&id=' . $_GET['id']) ?>';
    handle.target = '_self';

    window.location = 'edit.php?<?php print(session_name() . '=' . session_id() . '&type=' . $type_a . '&id=' . $_GET['id']) ?>';


}


function refreshPrice(val) {
	if (isSupplier) { // cas d'un fournisseur
		if (val == 0) { // saisi prix
			if (!prixPublic) { // le fournisseur est réglé sur un type de prix Fournisseur
				document.getElementById('modPrixF').innerHTML =
				'<table>' +
				' <tr><td class="intitule">Référence fournisseur : <input type="text" name="refSupplier" size="15" maxlength="255" class="champstexte" value="' + refSupplier + '"></td></tr>' +
				' <tr><td class="intitule">Prix fournisseur : <input type="text" name="price2" size="10" maxlength="9" class="champstexte" onChange="updatePrices(this)" value="' + price2 + '">€</td></tr>' +
				' <tr><td class="intitule">Unité : <input type="text" name="unite" size="8" maxlength="8" class="champstexte" onChange="this.value = unite = check_field(this.value, \'int\')" value="' + unite + '"></td></tr>' +
				' <tr><td class="intitule">Marge : <input type="text" name="marge" size="8" maxlength="8" class="champstexte" onChange="updatePrices(this)" value="' + marge + '">%</td>' +
				'  <td class="intitule">Marge par défaut du fournisseur : <b><span>' + margeRemiseDft + '</span>%</b></td></tr>' +
				' <tr><td class="intitule">Prix public : <input type="text" name="price" size="10" maxlength="9" class="champstexte" onChange="updatePrices(this)" value="' + price + '">€</td>' +
				'  <td class="intitule">Prix avec marge par défaut : <b><span></span>€</b></td></tr>' +
				' <tr><td class="intitule">Taux de TVA : <select name="idTVA">' + displayOptionsTVA(idTVA, 'short') + '</select></td>' +
				'  <td class="intitule">Taux de TVA par défaut du fournisseur : <b>' + displayTVA(idTVAdft, 'short') + '</b></td></tr>' +
				' <tr><td class="intitule">Délai de livraison du produit : <input type="text" name="delai_livraison" size="30" maxlength="255" class="champstexte" value="' + delai_livraison + '"></td></tr>' +
<?php //				' <tr><td class="intitule">Frais de port du produit : <input type="text" name="shipping_fee" size="30" maxlength="255" class="champstexte" value="' + shipping_fee + '"></td></tr>' +?>
				' <tr><td class="intitule">Contrainte sur le nombre de produits : <input type="text" name="contrainteProduit" size="10" maxlength="9" class="champstexte" onChange="this.value = contrainteProduit = check_field(this.value, \'int\')" value="' + contrainteProduit + '"></td></tr>' +
				'Mise sous devis par défaut : <input type="checkbox" '+(p_as_estimate ? 'checked="checked" ' : '')+'name="asEstimate" />'+
          (a_as_estimate ? ' Mise sous devis affectée à l\'annonceur<br /><br />' : '<br /><br />')+
        '</table>'+
				getProductAttributesIframe();
			}
			else { // le fournisseur est réglé sur un type de prix Public
				document.getElementById('modPrixF').innerHTML =
				'<table>' +
				' <tr><td class="intitule">Référence fournisseur : <input type="text" name="refSupplier" size="15" maxlength="255" class="champstexte" value="' + refSupplier + '"></td></tr>' +
				' <tr><td class="intitule">Prix public : <input type="text" name="price" size="10" maxlength="9" class="champstexte" onChange="updatePrices(this)" value="' + price + '">€</td></tr>' +
				' <tr><td class="intitule">Unité : <input type="text" name="unite" size="8" maxlength="8" class="champstexte" onChange="this.value = unite = check_field(this.value, \'int\')" value="' + unite + '"></td></tr>' +
				' <tr><td class="intitule">Remise : <input type="text" name="remise" size="6" maxlength="5" class="champstexte" onChange="updatePrices(this)" value="' + remise + '">% ' +
					     'soit une marge de <b><span></span>%</b></td>' +
				'  <td class="intitule">Remise par défaut du fournisseur : <b><span>' + margeRemiseDft + '</span>%</b> ' +
					  'soit une marge de <b><span>' + (Math.round((100/((100-margeRemiseDft)/100) - 100)*100) / 100) + '</span>%</b></td></tr>' +
				' <tr><td class="intitule">Prix fournisseur : <input type="text" name="price2" size="10" maxlength="9" class="champstexte" onChange="updatePrices(this)" value="' + price2 + '">€</td>' +
				'  <td class="intitule">Prix fournisseur avec remise par défaut : <b><span></span>€</b></td></tr>' +
				' <tr><td class="intitule">Taux de TVA : <select name="idTVA">' + displayOptionsTVA(idTVA, 'short') + '</select></td>' +
				'  <td class="intitule">Taux de TVA par défaut du fournisseur : <b>' +  displayTVA(idTVAdft, 'short') + '</b></td></tr>' +
				' <tr><td class="intitule">Délai de livraison du produit : <input type="text" name="delai_livraison" size="30" maxlength="255" class="champstexte" value="' + delai_livraison + '"></td></tr>' +
<?php //				' <tr><td class="intitule">Frais de port du produit : <input type="text" name="shipping_fee" size="30" maxlength="255" class="champstexte" value="' + shipping_fee + '"></td></tr>' +?>
				' <tr><td class="intitule">Contrainte sur le nombre de produits : <input type="text" name="contrainteProduit" size="10" maxlength="9" class="champstexte" onChange="this.value = contrainteProduit = check_field(this.value, \'int\')" value="' + contrainteProduit + '"></td></tr>' +
				'Mise sous devis par défaut : <input type="checkbox" '+(p_as_estimate ? 'checked="checked" ' : '')+'name="asEstimate" />'+
          (a_as_estimate ? ' Mise sous devis affectée à l\'annonceur<br /><br />' : '<br /><br />')+
				'</table>'+
				getProductAttributesIframe();
			}
			updatePrices(document.getElementById('modPrixF').getElementsByTagName('input')[0]);

			hideRef();
		}
		else if(val == 4) { // saisi références
			if (!prixPublic) { // le fournisseur est réglé sur un type de prix Fournisseur
				document.getElementById('modPrixF').innerHTML =
				'<a href="#">Guide pour la saisie / importation des références</a><br/><br/>' +
				'Fichier à importer : <input type="file" name="csv_ref"><br/>' +
				'Ou bien remplissez le tableau ci-dessous.<br/><br/>' +
				'Marge par défaut du fournisseur : <b><span>' + margeRemiseDft + '</span>%</b><br/><br/>' +
				'Taux de TVA par défaut du fournisseur : <b>' + displayTVA(idTVAdft, 'all') + '</b><br/><br/>' +
				'Délai de livraison du produit : <input type="text" name="delai_livraison" size="30" maxlength="255" class="champstexte" value="' + delai_livraison + '"><br/><br/>' +
<?php //				'Frais de port du produit : <input type="text" name="shipping_fee" size="30" maxlength="255" class="champstexte" value="' + shipping_fee + '"><br/><br/>' +?>
				'Contrainte sur le nombre de produits : <input type="text" name="contrainteProduit" size="10" maxlength="9" class="champstexte" onChange="this.value = contrainteProduit = check_field(this.value, \'int\')" value="' + contrainteProduit + '"><br/><br/>' +
				'Mise sous devis par défaut : <input type="checkbox" '+(p_as_estimate ? 'checked="checked" ' : '')+'name="asEstimate" />'+
                                (a_as_estimate ? ' Mise sous devis affectée à l\'annonceur<br /><br />' : '<br /><br />')+
                                'Type de prix par défaut du fournisseur : <b>Prix Fournisseur</b> <i>le prix public est calculé en fonction du prix fournisseur et de la marge</i><br/>' +
				'<div id="menu" style="z-index: 500; position: absolute; width: 150px; border: 1px solid #9D9DA1; background-color: #ffffff; font-size: 12px; cursor: default; visibility: hidden; padding: 3"></div><br/><br/>' +
        '<div class="zero"></div>' +
				getProductAttributesIframe() +
				'<div id="cat3_salc"></div>'+
				'<div id="references" onClick="hideContextMenu(); return false" style="overflow: auto"></div>';
			}
			else { // le fournisseur est réglé sur un type de prix Public
				document.getElementById('modPrixF').innerHTML =
				'<a href="#">Guide pour la saisie / importation des références</a><br/><br/>' +
				'Fichier à importer : <input type="file" name="csv_ref"><br/>' +
				'Ou bien remplissez le tableau ci-dessous.<br/><br/>' +
				'Remise par défaut du fournisseur : <b><span>' + margeRemiseDft + '</span>%</b> ' +
					'soit une marge de <b><span>' + (Math.round((100/((100-margeRemiseDft)/100) - 100)*100) / 100) + '</span>%</b><br/><br/>' +
				'Taux de TVA par défaut du fournisseur : <b>' +  displayTVA(idTVAdft, 'all') + '</b><br/><br/>' +
				'Délai de livraison du produit : <input type="text" name="delai_livraison" size="30" maxlength="255" class="champstexte" value="' + delai_livraison + '"><br/><br/>' +
<?php //				'Frais de port du produit : <input type="text" name="shipping_fee" size="30" maxlength="255" class="champstexte" value="' + shipping_fee + '"><br/><br/>' +?>
				'Contrainte sur le nombre de produits : <input type="text" name="contrainteProduit" size="10" maxlength="9" class="champstexte" onChange="this.value = contrainteProduit = check_field(this.value, \'int\')" value="' + contrainteProduit + '"><br/><br/>' +
				'Mise sous devis par défaut : <input type="checkbox" '+(p_as_estimate ? 'checked="checked" ' : '')+'name="asEstimate" />'+
                                (a_as_estimate ? ' Mise sous devis affectée à l\'annonceur<br /><br />' : '<br /><br />')+
                                'Type de prix par défaut du fournisseur : <b>Prix Public</b> <i>le prix fournisseur est calculé en fonction du prix public et de la remise</i><br/>' +
				'<div id="menu" style="z-index: 500; position: absolute; width: 150px; border: 1px solid #9D9DA1; background-color: #ffffff; font-size: 12px; cursor: default; visibility: hidden; padding: 3"></div><br/><br/>' +
        '<div class="zero"></div>' +
				getProductAttributesIframe() +
        '<div id="cat3_salc"></div>'+
				'<div id="references" onClick="hideContextMenu(); return false" style="overflow: auto"></div>';
			}
      // if ($("#cat3_sal").length)
      //   $("#cat3_salc").append($("#cat3_sal").show());
			displayRef();
		}
		else {
			document.getElementById('modPrixF').innerHTML = getProductAttributesIframe();
		}
	}
	else { // cas d'un annonceur
		if (val == 0) { // saisi prix
			document.getElementById('modPrixA').innerHTML =
			'Prix : <input type="text" name="price" size="10" maxlength="9" class="champstexte" onChange="this.value=(parseFloat(this.value))" value="' + price + '"><br/>' +
			getProductAttributesIframe();
			hideRef();
		}
		else if(val == 4) { // saisi références
			document.getElementById('modPrixA').innerHTML =
			'<a href="#">Guide pour la saisie / importation des références</a><br/><br/>' +
			'Fichier à importer : <input type="file" name="csv_ref"><br/>' +
			'Ou bien remplissez le tableau ci-dessous.<br/>' +
			'<div id="menu" style="z-index: 500; position: absolute; width: 150px; border: 1px solid #9D9DA1; background-color: #ffffff; font-size: 12px; cursor: default; visibility: hidden; padding: 3"></div><br/><br/>' +
			getProductAttributesIframe() +
			'<center><div id="references" onClick="hideContextMenu(); return false" style="overflow: auto; width: 800px; height: 250px"></div></center>';
			displayRef();
		}
		else {
			document.getElementById('modPrixA').innerHTML = getProductAttributesIframe();
			hideRef();
		}
	}
	typePrice = val;
}

function reduceObjectToString(obj, sep1, sep2) {
	return Object.keys(obj).reduce(function(prev, current){
		prev.push(current + sep2 + obj[current]);
		return prev;
	}, []).join(sep1);
}

function getProductAttributesIframe() {
	var styles = reduceObjectToString({
		"width": "100%",
		"min-height": "100px"
	}, ";", ":");
	var params = reduceObjectToString({
		"productId": productId,
		"familyId": productFamilies[0],
		"_": Date.now()
	}, "&", "=");

	return '<iframe id="product-attributes" style="'+styles+'" src="attributes/index.php?'+params+'"></iframe>';
}

window.resizeProductAttributesIframe = function() {
	var iframe = $("#product-attributes")[0];
	iframe.style.height = iframe.contentWindow.document.body.clientHeight + "px";
}

setInterval(window.resizeProductAttributesIframe, 100);

function changeTypePrix(id)
{
	advCurSelected = id;
	isSupplier = typeof(suppliersData[id]) != 'undefined' ? true : false;
	if (isSupplier)
	{
		prixPublic = (suppliersData[id][1] == '1') ? true : false;
		margeRemiseDft = parseFloat(suppliersData[id][2]);
		arrondi = suppliersData[id][3];
		idTVAdft = getValidTVA(suppliersData[id][4], idTVAdftDft);
		idTVA = getValidTVA(idTVA, idTVAdft);
		document.getElementById('modPrix').innerHTML =
		' Type prix : ' +
		'<select name="typeprice" id="typeprice" onChange="refreshPrice(this.value)">' +
		'<option value="2">Sur devis</option>' +
		'<option value="4">Références</option>' +
		'</select>' +
		'<div id="modPrixF"></div>';
		refreshPrice(typePrice);
	}
	else
	{
		prixPublic = false;
		margeRemiseDft = 0;
		arrondi = 0;
		idTVAdft = 0;
 		document.getElementById('modPrix').innerHTML =
		' Type prix : '+
		'<select name="typeprice" onChange="refreshPrice(this.value)">' +
		'<option value="2">Sur devis</option>' +
		'<option value="0">Autre</option>' +
		'</select>' +
		'<div id="modPrixA"></div>';
		refreshPrice(typePrice);
	}
	document.getElementsByName('typeprice')[0].value = typePrice;

	advPrevSelected = id;
}


function updatePrices(field)
{

	var parentInputs = document.getElementById('modPrixF').getElementsByTagName('input');
	var parentSpans  = document.getElementById('modPrixF').getElementsByTagName('span');


	var nothingToDo = false;

	if (!prixPublic)
	{
		price2 = parseFloat(parentInputs[1].value);
		marge  = parseFloat(parentInputs[3].value);
		price  = parseFloat(parentInputs[4].value);

		if (!isNaN(price2) || !isNaN(marge) || !isNaN(price))
		{
			switch (field.name)
			{

				case 'price2':
					if (!isNaN(price2))
					{
						if (isNaN(marge)) marge = margeRemiseDft;
						price = price2 * (100+marge)/100;
					}
					else
					{
						if (!isNaN(price))
						{
							if (isNaN(marge)) marge = margeRemiseDft;
							price2 = price * 100/(100+marge);
						}
						else nothingToDo = true;
					}
					break;

				case 'marge' :
					if (isNaN(marge)) marge = margeRemiseDft;

					if (!isNaN(price2)) price = price2 * (100+marge)/100;
					else if (!isNaN(price)) price2 = price * 100/(100+marge);
					else nothingToDo = true;
					break;

				case 'price':
					if (!isNaN(price))
					{
						if (!isNaN(price2))
							marge = (price/price2 - 1)*100;
						else
						{
							if (isNaN(marge)) marge = margeRemiseDft;
							price2 = price * 100/(100+marge);
						}
					}
					else
					{
						if (!isNaN(price2))
						{
							if (isNaN(marge)) marge = margeRemiseDft;
								price = price2 * (100+marge)/100;
						}
						else nothingToDo = true;
					}
					break;

			}
			if (!nothingToDo)
			{
				price2 = Math.round(price2*100)/100;
				marge = Math.round(marge*100000)/100000;
				price = Math.round(price*100)/100;

				parentInputs[1].value = price2;
				parentInputs[3].value = Math.round(marge*100000)/100000;
				parentInputs[4].value = Math.round(price*100)/100;

				parentSpans[1].innerHTML = Math.round(price2 * (100+margeRemiseDft)) / 100;
			}
		}
		else
		{
			price2 = marge = price = '';
		}

	}
	else
	{
		price = parseFloat(parentInputs[1].value);
		remise = parseFloat(parentInputs[3].value);
		price2 = parseFloat(parentInputs[4].value);

		if (!isNaN(price) || !isNaN(remise) || !isNaN(price2))
		{
			switch (field.name)
			{

				case 'price':
					if (!isNaN(price))
					{
						if (isNaN(remise)) remise = margeRemiseDft;
						price2 = price * (100-remise)/100;
					}
					else
					{
						if (!isNaN(price2))
						{
							if (isNaN(remise)) remise = margeRemiseDft;
							price = price2 * 100/(100-remise);
						}
						else nothingToDo = true;
					}
					break;

				case 'remise' :
					if (isNaN(remise)) remise = margeRemiseDft;

					if (!isNaN(price)) price2 = price * (100-remise)/100;
					else if (!isNaN(price2)) price = price2 * 100/(100-remise);
					else nothingToDo = true;
					break;

				case 'price2':
					if (!isNaN(price2))
					{
						if (!isNaN(price))
							remise = (1 - price2/price)*100;
						else
						{
							if (isNaN(remise)) remise = margeRemiseDft;
							price = price2 * 100/(100-remise);
						}
					}
					else
					{
						if (!isNaN(price))
						{
							if (isNaN(remise)) remise = margeRemiseDft;
								price2 = price * (100-remise)/100;
						}
						else nothingToDo = true;
					}
					break;

			}
			if (!nothingToDo)
			{
				price = Math.round(price*100)/100;
				remise = Math.round(remise*100000)/100000;
				price2 = Math.round(price2*100)/100;

				parentInputs[1].value = price;
				parentInputs[3].value = remise;
				parentInputs[4].value = price2;

				parentSpans[0].innerHTML = Math.round((100/((100-remise)/100) - 100)*100) / 100;
				parentSpans[3].innerHTML = Math.round(price * (100-margeRemiseDft)) / 100;
			}
		}
		else
		{
			price = remise = price2 = '';
		}
	}
}

// Elements références

<?php
$mts["JS REF LIST"]["start"] = microtime(true);

// nous avons toujours en entrée un tableau formatté pour fournisseur

function replaceTokens(& $value)
{
	return str_replace(array("\\", "'", chr(10), chr(13)), array("\\\\", "\'", '', ''), $value);
}

if($typeprice == 4)
{
	print('var refCols = new Array(');

	for($i = 0; $i < count($code_ref_tab_cols); ++$i)
	{
	    if ($i > 0) print(',');
		print('\'' . replaceTokens($code_ref_tab_cols[$i]) . '\'');
	}

	print(");\n");

    print('var refRows = new Array(' . count($code_ref_lines) . ');' . "\n");

  for($i = 0; $i < count($code_ref_lines); ++$i)
	{
		$code_ref_line = & $code_ref_lines[$i];

    print('refRows[' . $i . '] = new Array(');

		print('\'' . replaceTokens($code_ref_line['id']) . '\',');
		print('\'' . replaceTokens($code_ref_line['label']) . '\',');
		print('\'' . replaceTokens($code_ref_line['refSupplier']) . '\',');

		if (count($code_ref_tab_cols) > 8) {
      $line_content = explode('<->', $code_ref_line['content']);
      for ($j = 0; $j < count($line_content); $j++)
        print('\'' . replaceTokens($line_content[$j]) . '\',');
    }

		print('\'' . replaceTokens($code_ref_line['unite']) . '\',');
		print('\'' . replaceTokens($code_ref_line['idTVA']) . '\',');
		print('\'' . replaceTokens($code_ref_line['price2']) . '\',');
		print('\'' . replaceTokens($code_ref_line['marge']) . '\',');
		print('\'' . replaceTokens($code_ref_line['price']) . '\',');
    print('\'' . replaceTokens($code_ref_line['ecotax']) . '\'');

		print(");\n");
	}

?>

// Vérif supplémentaire pour avoir un tableau consistant (ce qui devrait toujours être le cas sauf données erronées dans la BDD)

var maxRowLength = 0;

for (i = 0; i < refRows.length; i++ )
	if (refRows[i].length > maxRowLength) maxRowLength = refRows[i].length;

if (refCols.length < 9) refCols.length = 9;
if (maxRowLength < refCols.length) maxRowLength = refCols.length;
else if (refCols.length < maxRowLength) refCols.length = maxRowLength;

for (i = 0; i < refRows.length; i++ )
{
	var oldRowLength = 0;
	if (refRows[i].length < maxRowLength)
	{
		oldRowLength = refRows[i].length;
		refRows[i].length = maxRowLength;

		for (j = oldRowLength; j < maxRowLength; j++)
			refRows[i][j] = '';
	}
}

if (refCols[0] != 'Référence TC')
  refCols[0] = 'Référence TC';
if (refCols[1] != 'Libellé')
  refCols[1] = 'Libellé';
if (refCols[2] != 'Référence Fournisseur')
  refCols[2] = 'Référence Fournisseur';
if (refCols[refCols.length-6] != 'Unité')
  refCols[refCols.length-6] = 'Unité';
if (refCols[refCols.length-5] != 'Taux TVA')
  refCols[refCols.length-5] = 'Taux TVA';
if (refCols[refCols.length-4] != 'Prix Fournisseur')
  refCols[refCols.length-4] = 'Prix Fournisseur';
if (refCols[refCols.length-3] != 'Marge' && refCols[refCols.length-3] != 'Remise')
  refCols[refCols.length-3] = 'Marge';
if (refCols[refCols.length-2] != 'Prix Public')
  refCols[refCols.length-2] = 'Prix Public';
if (refCols[refCols.length-1] != 'Éco Taxe')
  refCols[refCols.length-1] = 'Éco Taxe';

<?php
}
else
{
?>

var refCols  = new Array('Référence TC', 'Libellé', 'Référence Fournisseur', 'Unité', 'Taux TVA', 'Prix Fournisseur', 'Marge', 'Prix Public', 'Éco Taxe');
var refRows  = new Array();

<?php
}
$mts["JS REF LIST"]["end"] = microtime(true);

?>

//-->
</script>
<script type="text/javascript" src="../ref/global.js"></script>
<script type="text/javascript" src="../ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="../ckfinder/ckfinder.js"></script>

<link href="HN.css" rel="stylesheet" type="text/css"/>
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/HN.Mods.DialogBox.blue.css"/>
<script type="text/javascript" src="<?php echo ADMIN_URL ?>ressources/js/AJAXclasses.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_URL ?>ressources/js/AJAXmodules.js"></script>
<script type="text/javascript" src="Classes.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_URL ?>ressources/js/ManagerFunctions.js"></script>
<style type="text/css">
/* Family Selection Window */
#FamilySelectionWindowShad { z-index: 3; position: absolute; top: 613px; left: 25px; width: 788px; height: 404px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#FamilySelectionWindow { z-index: 4; position: absolute; top: 608px; left: 20px; border: 2px solid #999999; visibility: hidden; }

/* Family Search Engine Window */
#FamilySearchEngineWindowShad { z-index: 3; position: absolute; top: 613px; left: 25px; width: 788px; height: 404px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#FamilySearchEngineWindow { z-index: 4; position: absolute; top: 608px; left: 20px; border: 2px solid #999999; visibility: hidden; }

/* Advertiser Selection Window */
#PMP-ChooseAdvertiser { z-index: 3; width: 530px; position: absolute; top: 650px; left: 15px; }
.window-silver { padding: 5px; font: normal 11px Tahoma, Arial, Helvetica, sans-serif; }
.window-silver a { color: #000000; font-weight: normal; }
.window-silver a:hover { font-weight: normal; }

.tab_menu { height: 24px; padding: 0 5px 0 5px; position: relative; top: 1px; }

.tab_menu .tab { float: left; width: 118px; text-align: center; cursor: default; }

.tab_menu .tab_lb_i, .tab_menu .tab_lb_a, .tab_menu .tab_lb_s, .tab_menu .tab_rb_i, .tab_menu .tab_rb_a, .tab_menu .tab_rb_s { float: left; width: 4px; height: 23px; }
.tab_menu .tab_lb_i { background : url(tab-left-border.gif) repeat-x; }
.tab_menu .tab_lb_a { background : url(tab-active-left-border.gif) repeat-x; }
.tab_menu .tab_rb_i { background : url(tab-right-border.gif) repeat-x; }
.tab_menu .tab_rb_a { background : url(tab-active-right-border.gif) repeat-x; }
.tab_menu .tab_lb_s { height: 24px; background : url(tab-active-left-border.gif) repeat-x;}
.tab_menu .tab_rb_s { height: 24px; background : url(tab-active-right-border.gif) repeat-x; }

.tab_menu .tab_bg_i, .tab_menu .tab_bg_a, .tab_menu .tab_bg_s { height: 17px; float: left; width: 90px; text-align: left; color: #000000; padding: 6px 10px 0px 10px; white-space: nowrap; }
.tab_menu .tab_bg_i { background: url(tab-bg.gif) repeat-x; }
.tab_menu .tab_bg_a { background: url(tab-active-bg.gif) repeat-x; }
.tab_menu .tab_bg_s { height: 18px; background: url(tab-active-bg.gif) repeat-x; font-weight: bold; }

.menu-below { border: 1px solid #808080; height: 2px; font-size: 0; border-bottom: none; background-color: #D8D4CD; }
.main { border: 1px solid #808080; background-color: #DEDCD6; }

.search_menu { width: 516px; cursor: default; padding: 3px 6px; border-bottom: 1px solid #808080; display: block; float: left}
.search_menu span { border: 1px solid #DEDCD6; padding: 2px 5px; outline: none; }
.search_menu span.over { border-color: #FFFFFF #808080 #808080 #FFFFFF; }
.search_menu span.down { border-color: #808080 #FFFFFF #FFFFFF #808080; }
.search_menu span.selected { border-color: #808080 #FFFFFF #FFFFFF #808080; }

.body { padding: 2px 4px; background-color: #DEDCD6; border-top: 1px solid #FFFFFF; clear: left}
.body .colg { float: left; width: 258px; margin-right: 5px; }
.body .colc { float: left; width: 257px; }
.body .col-title { cursor: default; font-weight: bold; margin: 2px; }
.body .colg .list { width: 252px; height: 298px; background-color: #FFFFFF; border: 2px inset #808080; margin: 0; padding: 1px; list-style-type: none; overflow: auto; }
.body .colg .list li { cursor: default; white-space: nowrap; }
.body .colg .list li.over { background-color: #316AC5; color: #FFFFFF; }
.body .colg .list li.selected { background-color: #0C266C; color: #FFFFFF; }

.body .colc .infos { position: relative; height: 290px; background-color: #FFFFFF; border: 2px inset #808080; padding: 5px; }

.body .colc .select_label { padding: 0 5px 0 20px; }
.body .colc input.button1 { top: 250px; left: 5px; width: 243px; }
.body .colc input.button2 { top: 275px; left: 5px; width: 243px; }

/* Common */
.app-form-edit-button { cursor: pointer; margin: 0 0 -3px 5px }
.app-table-add-icon { cursor: pointer; }
.field-fixed { height: 15px; padding: 0 0 0 3px; border: 1px solid #cccccc; background: #ffffff; font-size: 12px }
.fl { float: left }
.fr { float: right }
.w-100 { width: 100px }
.w-150 { width: 150px }
.w-200 { width: 200px }
.w-250 { width: 250px }
.w-300 { width: 300px }
.w-350 { width: 350px }
.w-400 { width: 400px }
.w-450 { width: 450px }
.w-500 { width: 500px }

#link-family-button { float: right; margin: 0 46px 0 0 }
#linked-families { background-color: #ffffff; border-collapse: collapse; border: 1px solid #000000; font: 10px verdana, helvetica, sans-serif; }
#linked-families th { height: 17px; background-color: #f0f0f0 }
#linked-families td { height: 17px; border-style: solid; border-width: 1px 0px; border-color: #000000 }
#linked-families .family-id { width: 102px; text-align: center }
#linked-families .family-name { width: 300px; text-align: left }
#linked-families .edit { width: 24px; text-align: center }
#linked-families .edit img { cursor: pointer }
#linked-families .del { width: 24px; text-align: center }
#linked-families .del img { cursor: pointer }
#PerfReqLabel-LinkedFamilies { float: right; color: #b00000 }

#references { width: 100% !important; }
#references table { width: 100%; border-collapse: collapse }
#references table td { border: 1px solid #000000; }
#references table td.isCat3SA { background: #80FF90!important }
#references table td.isCat3SA input { background: #80FF90!important }
#references table td.isCat3SA:hover { background: #316ac5!important }
#references table td.isCat3SA:hover input { background: #316ac5!important }
input.ref-col { width: 90%; }
#references center { text-align: left; }
#references .intitule { background-color: #E9EFF8;}
</style>

<div class="window-silver" id="PMP-ChooseAdvertiser" style="display: none;">
	<div id="PMP-main_menu" class="tab_menu"></div>
	<div class="menu-below"></div>
	<div class="main">
		<div id="PMP-Suppliers">
			<div id="PMP-search_menuS" class="search_menu"></div>
			<div class="body">
				<div class="colg">
					<div class="col-title" onmousedown="grab(document.getElementById('PMP-ChooseAdvertiser'))">Liste des fournisseurs</div>
					<ul class="list" id="PMP-listS">
					</ul>
				</div>
				<div class="colc">
					<div class="col-title" onmousedown="grab(document.getElementById('PMP-ChooseAdvertiser'))">Informations</div>
					<div class="infos">
						<div id="PMP-infosS"></div>
						<input type="button" class="button button1" value="Valider" onclick="PMP.SetAdvertiserName(PMP.ElementListS);"/>
						<input type="button" class="button button2" value="Annuler" onclick="PMP.HideAdvertiserSearchWindow();"/>
					</div>
				</div>
				<div class="zero"></div>
			</div>
		</div>
		<div id="PMP-Advertisers">
			<div id="PMP-search_menuA" class="search_menu"></div>
			<div class="body">
				<div class="colg">
					<div class="col-title" onmousedown="grab(document.getElementById('PMP-ChooseAdvertiser'))">Liste des annonceurs</div>
					<ul class="list" id="PMP-listA">
					</ul>
				</div>
				<div class="colc">
					<div class="col-title" onmousedown="grab(document.getElementById('PMP-ChooseAdvertiser'))">Informations</div>
					<div class="infos">
						<div id="PMP-infosA"></div>
						<input type="button" class="button button1" value="Valider" onclick="PMP.SetAdvertiserName(PMP.ElementListA);"/>
						<input type="button" class="button button2" value="Annuler" onclick="PMP.HideAdvertiserSearchWindow();"/>
					</div>
				</div>
				<div class="zero"></div>
			</div>
		</div>
	</div>
</div>

<div id="FamilySelectionWindow"></div>
<div id="FamilySearchEngineWindow"></div>

<div id="PMP-PerfReqLabel" class="PerfReqLabel"><br/></div>

<form name="addProduct" method="post" action="edit.php?<?php print(session_name() . '=' . session_id() . '&type=' . $type_a . '&id=' . $_GET['id']) ?>" class="formulaire" enctype="multipart/form-data">
<input type="hidden" id="id_ref_tc_delete" name="id_ref_tc_delete"  value="" />
<table>
	<tr><td class="intitule">Nom du produit (mot clé google) :</td><td>
		<input class="champstexte" type="text" size="25" maxlength="255" name="nom" onBlur="this.value = trim(this.value); maj = this.value.charAt(0).toUpperCase(); if(this.value.length > 1) this.value = maj + this.value.substring(1, this.value.length); else this.value = maj" value="<?php print(to_entities($nom)) ?>"> * &nbsp; &nbsp; &nbsp; <input type="button" class="bouton" value="Rechercher" onClick="namesearch()">
                <input type="button" class="bouton" value="Rechercher une famille" onclick="PMP.fsw2.Show(); PMP.fse.Build(); PMP.fse.mod='add'; familiesSearchEngine()">
        </td></tr>
	<tr><td class="intitule">Description rapide du produit :</td><td>
		<input class="champstexte" type="text" size="49" maxlength="255" name="fastdesc" onBlur="this.value = trim(this.value); maj = this.value.charAt(0).toUpperCase(); if(this.value.length > 1) this.value = maj + this.value.substring(1, this.value.length); else this.value = maj" value="<?php print(to_entities($fastdesc)) ?>"> *
	</td></tr>
	<tr><td class="intitule">Annonceur ou fournisseur :</td><td>
		<input type="hidden" id="advertiser" name="advertiser" value="<?php echo $advertiser ?>">
		<div id="advertiser-name" class="field-fixed fl w-450">
			<?php if ($advertiser == 0) { ?> Sélectionnez l'annonceur ou le fournisseur du produit
			<?php } else { ?> <?php echo to_entities($advertisers[$advertiser]) ?> (<i><?php echo $isSupplier ? "Fournisseur" : "Annonceur" ?></i> <b><?php echo $advertiser ?></b>)
			<?php } ?>
		</div>&nbsp;*
		<img class="app-form-edit-button" src="<?php echo ADMIN_URL ?>ressources/icons/application_form_edit.png" onclick="PMP.ShowAdvertiserSearchWindow()"/>
	</td></tr>
	<tr><td class="intitule">Sous-familles associées :</td><td>
		<input type="hidden" id="familiesHidden" name="familiesHidden" value="<?php echo $familiesHidden ?>">
		<img id="link-family-button" class="app-table-add-icon" src="<?php echo ADMIN_URL ?>ressources/icons/table_add.png" onclick="PMP.fsw.Show(); PMP.fb.Build(); PMP.fb.mod='add';"/>
		<table id="linked-families" cellspacing="0" cellpadding="0">
			<thead>
			<tr>
				<th class="family-id">ID(s)</th>
				<th class="family-name"><div id="PerfReqLabel-LinkedFamilies"></div>Famille(s)</th>
        <th class="up"></th>
        <th class="down"></th>
				<th class="edit"></th>
				<th class="del"></th>
			</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</td></tr>
</table>

<table>
<?php

		if(($_GET['type'] == 'edit_adv') || ($_GET['type'] == 'add_adv')){
				$alias_send = $keywords;
		}else $alias_send = $alias;
   
?>
 <tr><td class="intitule">Liste des alias :</td><td class="intitule"><input type="text" class="champstexte" name="alias" size="40" maxlength="255" value="<?php print(to_entities($alias_send)) ?>"> - Séparez chaque alias par le caractère | (AltGR + 6)</td></tr>
 <tr><td class="intitule">Liste des mots-clés :</td><td class="intitule"><input type="text" class="champstexte" name="keywords" size="40" maxlength="255" value="<?php print(to_entities($keywords)) ?>"> - Séparez chaque mot-clé par le caractère | (AltGR + 6)</td></tr>
 <tr><td class="intitule" colspan="2"><i>Note : Les mots clés sont ceux utilisés par le moteur de recherche interne</i></td></tr>
</table>
<br/>
<table>
 <tr><td class="intitule">Code EAN :</td><td class="intitule"><input type="text" class="champstexte" name="ean" size="40" maxlength="255" value="<?php print(to_entities($ean)) ?>"></td></tr>
 <tr><td class="intitule">Garantie :</td><td class="intitule"><input type="text" class="champstexte" name="warranty" size="40" maxlength="255" value="<?php print(to_entities($warranty)) ?>"></td></tr>
</table>

<br/>
<?php
	$sql_tag_seo  = "SELECT title_tag,meta_desc_tag FROM products WHERE id='".$handle->escape($_GET['id'])."'";
	$req_tag_seo  =  mysql_query($sql_tag_seo);
	$data_tag_seo =  mysql_fetch_object($req_tag_seo);
?>
<table>
 <tr><td class="intitule"><i>SEO</i> title :</td><td class="intitule"><input type="text" class="champstexte" name="title_tag" size="40" maxlength="255" value="<?php print(to_entities($data_tag_seo->title_tag)) ?>"></td></tr>
 <tr><td class="intitule"><i>SEO</i> meta desc :</td><td class="intitule"><input type="text" class="champstexte" name="meta_desc_tag" size="40" maxlength="255" value="<?php print(to_entities($data_tag_seo->meta_desc_tag)) ?>"></td></tr>
</table>

<br/><br/>Description du produit : *
<textarea name="desc"><?php print(str_replace(array("</script>"), array("</scr\" + \"ipt>"), $desc)) ?></textarea>
<script type="text/javascript">
<!--
CKEDITOR.config.enterMode = CKEDITOR.ENTER_BR;
var editor = CKEDITOR.replace('desc');
CKFinder.setupCKEditor( editor, '../ckfinder/' );
/*

var sBasePath = '<?php echo ADMIN_URL ?>editor/';
var oFCKeditor = new FCKeditor('desc');
    oFCKeditor.BasePath	= sBasePath;
    oFCKeditor.Height = 250;
    oFCKeditor.Width  = 800;
    oFCKeditor.Config['CustomConfigurationsPath'] = '<?php echo ADMIN_URL ?>files/myconfig.js';
    oFCKeditor.Value = "<?php print(str_replace(array('"', "\r\n", "\r", "\n", "</script>"), array('\"', "\\n", "\\n", "\\n", "</scr\" + \"ipt>"), $desc)) ?>";
    oFCKeditor.Create();*/

//-->
</script>
<br/><br/>Description détaillée du produit :
<textarea name="descd"><?php print(str_replace(array( "</script>"), array( "</scr\" + \"ipt>"), $descd)) ?></textarea>
<script type="text/javascript">
<!--
CKEDITOR.replace('descd');
var editor2 = CKEDITOR.replace('descd');
CKFinder.setupCKEditor( editor2, '../ckfinder/' );
/*

var pFCKeditor = new FCKeditor('descd');
    pFCKeditor.BasePath	= sBasePath;
    pFCKeditor.Height = 250;
    pFCKeditor.Width  = 800;
    pFCKeditor.Config['CustomConfigurationsPath'] = '<?php echo ADMIN_URL ?>files/myconfig.js';
    pFCKeditor.Value = "<?php print(str_replace(array('"', "\r\n", "\r", "\n", "</script>"), array('\"', "\\n", "\\n", "\\n", "</scr\" + \"ipt>"), $descd)) ?>";
    pFCKeditor.Create();
*/

//-->
</script>
<br/><br/>

<style type="text/css">
.grey-block { clear:both; border: 1px solid #e4e4e4; background: #fcfcfc url(../ressources/images/block-bg-grey-150.gif) repeat-x }
a.btn-red { display: block; padding: 5px; font: bold 12px arial, sans-serif; color: #ffffff; text-align: center; background: #9d0503 url(../ressources/images/dot-bg.png) repeat-x }
a.btn-red:hover { text-decoration: underline }

#PPSDB { z-index: 4; position: absolute; left: 75px; top: 5px; width: 500px; border: 2px solid #205683; visibility: hidden }
#pic-selection { position: relative; }
#box-pic-add { position: absolute;  }
#box-pic { margin: 0; padding: 0; border: 1px solid #cccccc }
#box-pic ul li{ display: inline ; clear: none !important; display: block; float: left}
#box-pic ul li div img{cursor: move}
#box-pic .pic-block { margin: 10px; cursor: pointer }
#box-pic .pic-del, #box-pic ul li{ width: 166px }
#box-pic .btn-pic-del { padding: 2px; font-size: 9px }
#btn-add-pic { width: 160px }
#btn-reorder{display: none; margin-left: 10px}
</style>
<script type="text/javascript">
if (!HN.TC) HN.TC = {};
if (!HN.TC.BO) HN.TC.BO = {};
$(function(){
	$("#btn-add-pic").click(function(){
		HN.TC.BO.PPSDB.Show();
		return false;
	});
	HN.TC.BO.PPSDB = new HN.Mods.DialogBox("PPSDB");
	HN.TC.BO.PPSDB.setTitleText("Choisir une image produit (JPEG)");
	HN.TC.BO.PPSDB.setMovable(true);
	HN.TC.BO.PPSDB.showCancelButton(true);
	HN.TC.BO.PPSDB.showValidButton(true);
	HN.TC.BO.PPSDB.setValidFct(function() {
		HN.TC.BO.refreshProductImages(<?php echo $_GET["id"] ?>);
		HN.TC.BO.PPSDB.Hide();
	});
	//HN.TC.BO.PPSDB.setShadow(true);
	HN.TC.BO.PPSDB.Build();

	//PPB = new HN.Mods.PreviewPictureBox("PPB");

	var deleteProductImage = function (pdtID) {
		var s = "";
		if (arguments.length < 2) s += "1";
		else for (var i = 1; i < arguments.length; i++) s += (i>1?",":"") + arguments[i];
		$.ajax({
			async: true,
			cache: false,
			data: "action=delpics<?php echo ($_GET["type"]=="add_adv" || $_GET["type"]=="edit_adv") ? "&type=adv" : "" ?>&pdtID="+pdtID+"&num="+s,
			dataType: "json",
			error: function (XMLHttpRequest, textStatus, errorThrown) { alert("Fatal error while loading Products Data"); },
			success: function (data, textStatus) {
				if (data.pdtID)
					HN.TC.BO.refreshProductImages(data.pdtID);
			},
			type: "GET",
			url: "AJAX_pdt-edition.php"
		});
	};

	HN.TC.BO.refreshProductImages = function (pdtID) {
		$.ajax({
			async: true,
			cache: false,
			data: "action=getpics<?php echo ($_GET["type"]=="add_adv" || $_GET["type"]=="edit_adv") ? "&type=adv" : "" ?>&pdtID="+pdtID,
			dataType: "json",
			error: function (XMLHttpRequest, textStatus, errorThrown) { alert("Fatal error while loading Products Data"); },
			success: function (data, textStatus) {
				var ul = document.createElement("ul");
				for (var i = 0; i < data.pics.length; i++) {
					var li = document.createElement("li");
                                        $(li).attr('id','pic_nbr'+(i+1));
					var div = document.createElement("div");
					div.className = "pic-block";
					div.srcZoom = data.pics[i].zoom+"?t="+(new Date).getTime();;
					li.appendChild(div);
						var img = document.createElement("img");
						img.src = data.pics[i].thumb_big+"?t="+(new Date).getTime();
						img.alt = "";
						//img.onclick = function(){ alert(this.parentNode.srcZoom); /*PPB.show(data.pics[i].zoom); */};
						div.appendChild(img);
						var a = document.createElement("a");
						a.setAttribute("href", "#"+data.pdtID+"-"+data.pics[i].num);
						a.className = "btn-red btn-pic-del";
						a.innerHTML = "supprimer";
						a.onclick = function(){
							var parts = this.getAttribute("href").split("-");
							var pdtID = parts[0].substr(1, this.href.length);
							var num = parts[1];
							deleteProductImage(pdtID, num);
							return false;
						};
                                                ul.appendChild(li);
						div.appendChild(a);


				}
				$("#box-pic").empty().get(0).appendChild(ul);
                                var divClear = document.createElement("div");
                                      divClear.className = "zero";
                                      $("#box-pic").get(0).appendChild(divClear);
                                $("#box-pic ul").attr('id', 'sortable');
                                $( "#sortable" ).sortable({
                                        revert: true,
                                        update: function(){
                                          $('#btn-reorder').show();
                                        }
                                });
			},
			type: "GET",
			url: "AJAX_pdt-edition.php"
		});
	};
	HN.TC.BO.refreshProductImages(<?php echo $_GET["id"] ?>);
});
</script>
<div id="pic-selection">
	<div id="PPSDB">
		<iframe src="<?php echo ADMIN_URL ?>products/product-pic.php?id=<?php echo $_GET["id"] ?><?php echo ($_GET["type"]=="add_adv" || $_GET["type"]=="edit_adv") ? "&type=adv" : "" ?>" width="100%" frameborder="0" height="80"></iframe>
	</div>
	<div class="intitule">Images produits : seules les 3 premières seront affichées en Front office</div>
	<div id="box-pic" class="grey-block"></div>
	<a href="#" class="btn-red fl" id="btn-add-pic">Ajouter une image produit</a>
        <a href="javascript:recordNewPicOrder(<?php echo $_GET["id"] ?>)" class="btn-red fl" id="btn-reorder">Enregistrer l'ordre des images</a>
        <div class="zero"</div>
</div>
<br/>
<br/>

<style type="text/css">
.section { width: 940px; margin: 10px 0; padding: 10px; border: 1px solid #cccccc; background: #f0f8fe }
.section .title { margin: 0 0 10px 0; padding: 2px; font: bold 14px arial, sans-serif; border: 1px solid #000000; background: #ffffff }

.doc-selection-table { width: 100%; font: normal 10px verdana, helvetica, sans-serif; border: 1px solid #000000; border-collapse: collapse; background-color: #ffffff }
.doc-selection-table tr.selected { background: #ffd9a7 }
.doc-selection-table th { height: 17px; padding: 1px; background: #f0f0f0 }
.doc-selection-table td { height: 17px; padding: 1px; text-align: center; border: 1px solid #000000; border-width: 1px 0; text-align: left }
.doc-selection-table .doc-title { width: 315px }
.doc-selection-table .doc-filename { width: 250px }
.doc-selection-table .btn-doc-num { width: 160px }
.doc-selection-table .doc-filesize { width: 60px; text-align: right }
.doc-selection-table .doc-uploaded { float: right; width: 35px; height: 18px; margin: 3px 5px 0 5px; background: #FF0000 }
.doc-selection-table .uploaded { background: #FFA000 }
.doc-selection-table .confirmed { background: #00F800 }
.doc-selection-table .actions { width: 80px }
.doc-selection-table .actions .icon { display: inline; float: left; width: 16px; height: 16px; margin: 0 4px; background-repeat: no-repeat; cursor: pointer }
.doc-selection-table .actions .icon-up { background: url(arrow_up.png) }
.doc-selection-table .actions .icon-down { background: url(arrow_down.png) }
.doc-selection-table .actions .icon-edit { margin: 0 5px 0 10px; background: url(table_edit.png) }
.doc-selection-table .actions .icon-del { background: url(table_delete.png) }

.grey-block { clear:both; border: 1px solid #e4e4e4; background: #fcfcfc url(block-bg-grey-150.gif) repeat-x }
a.btn-red { display: block; padding: 5px; font: bold 12px arial, sans-serif; color: #ffffff; text-align: center; background: #9d0503 url(dot-bg.png) repeat-x }
a.btn-red:hover { text-decoration: underline }
#btn-add-doc { float: left; width: 170px; height: 20px; margin: 5px 0; background: url(btn-add-doc-up.png) no-repeat; cursor: pointer }
#btn-add-doc.down { background: url(btn-add-doc-down.png) no-repeat }
#btn-save-doc { float: right; width: 200px; margin: 5px 0 0 }
#doc-selection { position: relative; }
#PDSDB { z-index: 4; position: absolute; left: 25px; top: 50px; width: 500px; border: 2px solid #205683; visibility: hidden }
</style>

<script type="text/javascript" src="products.js"></script>
<script type="text/javascript">$(function(){ HN.TC.BO.Pdt.Init(<?php echo $_GET["id"] ?>, "<?php echo $_GET["type"] ?>"); });</script>
<div id="doc-selection" class="section">
	<div id="PDSDB">
		<iframe name="doc-selection-iframe" src="<?php echo ADMIN_URL ?>products/product-doc.php?id=<?php echo $_GET["id"] ?><?php echo ($_GET["type"]=="add_adv" || $_GET["type"]=="edit_adv") ? "&type=adv" : "" ?>" width="100%" frameborder="0" height="80"></iframe>
	</div>
	<div class="title">Documentation (PDF)</div>
	<table class="doc-selection-table" cellspacing="0" cellpadding="0">
	<thead>
		<tr>
			<th>Titre</th>
			<th>Nom du fichier</th>
			<th>Upload</th>
			<th>Taille (char)</th>
			<th class="actions"></th>
		</tr>
	</thead>
	<tbody>
	</tbody>
	</table>
	<div id="btn-add-doc"></div>
	<a href="#" class="btn-red" id="btn-save-doc">Sauvegarder les changements</a>
	<div class="zero"></div>
</div>

<br/>
<table>
 <tr><td class="intitule"><br/><br/>Outils de eCRM - Catalogues associés aux demandes :</td><td>&nbsp;</td></tr>
 <tr><td class="intitule"><input type="checkbox" name="gen" <?php if($gen) print('checked') ?>> Catalogue général</td><td class="intitule"><input type="checkbox" name="ind" <?php if($ind) print('checked') ?>> Catalogue industrie</td></tr>
 <tr><td class="intitule"><input type="checkbox" name="col" <?php if($col) print('checked') ?>> Catalogue collectivités</td><td>&nbsp;</td></tr>
</table>

<br/>
<br/>
Code vidéo :<br/>
<textarea name="video_code" rows="4" style="width: 800px"><?php echo $video_code ?></textarea>
<br/>
<br/>
<br/>
Sélectionnez les produits que vous souhaitez éventuellement lier avec ce produit lors d'une demande de contact.<br/>
L'annonceur de ce produit sera alors prévenu par email à chaque demande de contact auprès des annonceurs des produits qui lui sont liés :
<br/>
<select name="padv" onChange="displayAdvertiserProducts(this.value)">
  <option value="">Sélectionnez un annonceur</option>
  <option value=""></option>
 <?php foreach ($advertisers as $k => $v) : ?>
  <option value="<?php echo $k ?>"><?php echo to_entities($v) ?></option>
 <?php endforeach ?>
</select>
<br/>
<div id="padvDSP"><br/></div>
<br/>
<div id="productsLinkedLead"> Actuellement aucun produit lié lors d'un lead</div>
<br/>
<hr width="50%" align="center">
<script> testAndWrite('productsLinkedLead', productsShown + '<input type="hidden" name="productsHidden" value="'+productsHidden+'">'); </script>

<!-- linked products -->
<ul id="linked-products-pdt-preview" class="pdt-previews layer">
</ul>
<div id="linked-products-pdt-detail" class="pdt-detail layer">
  <div class="picture"><img id="linked-products-pdt-detail-pic" class="vmaib" src="<?php echo SECURE_URL ?>ressources/images/produits/no-pic-thumb_big.gif" /><div class="vsma"></div></div>
  <div class="infos">
    <div class="vmaib">
      <a id="linked-products-pdt-detail-p-fo-url" class="_blank" href="" title="Voir la fiche en ligne"><img src="../ressources/icons/monitor_go.png" alt="" class="view-fo" /></a>
      <a id="linked-products-pdt-detail-p-bo-url" class="_blank" href="" title="Editer la fiche produit"><strong id="linked-products-pdt-detail-name"></strong></a><br />
      <span id="linked-products-pdt-detail-p-fastdesc"></span><br />
      Code fiche produit: <strong id="linked-products-pdt-detail-p-id"></strong><br />
      Famille : <a id="linked-products-pdt-detail-f-bo-pdt-list-url" class="_blank" href=""><strong id="linked-products-pdt-detail-f-name"></strong></a><br />
      <span>Fournisseur</span> : <a id="linked-products-pdt-detail-a-bo-url" class="_blank" href=""><strong id="linked-products-pdt-detail-a-name"></strong></a><br />
      <a id="linked-products-see-pdt-sheet" href="#pdt_sheet">Voir description produit</a>
    </div><div class="vsma"></div>
  </div>
  <div class="zero"></div>
  <div id="linked-products-pdt-detail-references" class="refs">
  <table>
    <thead>
      <tr>
        <th>Réf. TC</th>
        <th>Réf. Four.</th>
        <th>Libellé</th>
        <th>P.A.U. € HT</th>
        <th>P.U. € HT</th>
      </tr>
    </thead>
    <tbody>
    </tbody>
  </table>
  </div>
</div>
<fieldset id="linked-products">
  <legend class="title">Produits liés apparaissant sur la fiche produit</legend>
  <button id="linked-products-add-line" type="button" class="btn ui-state-default ui-corner-all fr">Ajouter un produit</button>
  <table class="list">
    <thead>
      <tr>
        <th class="image">Image</th>
        <th class="id">Id</th>
        <th class="desc">Description</th>
        <th class="cat-name">Famille 3</th>
        <th class="partner-name">Fournisseur</th>
        <th class="action">&nbsp;</th>
      </tr>
    </thead>
    <tbody>
    </tbody>
  </table>
</fieldset>
<div class="zero"></div>
<input type="hidden" name="productsLinked" value="<?php echo $productsLinked ?>">

<!-- Category Selected Attributes -->
<style type="text/css">
#cat3_sal { display: none }
.cat3-sal { margin: 0 0 5px; overflow: hidden; overflow-x: auto } /* selected attribute list */
.cat3-sal .title { font-weight: bold; color: #336600 }
.cat3-sal table { font: normal 10px verdana, helvetica, sans-serif; border: 1px solid #000000; border-collapse: collapse; background: #ffffff }
.cat3-sal td { border: 1px solid #000000 }
.cat3-sal ul { min-width: 150px }
.cat3-sal li { padding: 2px; text-indent: 10px }
.cat3-sal li:first-child { font-weight: bold; text-indent: 0; background: #f0f0f0 }
.cat3-sal .icon { display: inline-block; width: 16px; height: 16px; margin: 0 4px; background-repeat: no-repeat; vertical-align: middle; cursor: pointer }
.cat3-sal .icon-add { background: url(table_add.png) }
</style>

<div id="cat3_sal" class="cat3-sal"></div>

<?php 
	$sql_check_idve_foun  = "SELECT aa.id
							 FROM   products pp, advertisers aa
							 WHERE  pp.idAdvertiser = aa.id
							 AND    aa.category='1' 
							 AND    pp.id = '".$_GET['id']."' ";
	$req_check_idve_foun  =  mysql_query($sql_check_idve_foun);
	$date_check_idve_foun =  mysql_fetch_object($req_check_idve_foun);
	
	if(!empty($date_check_idve_foun->id)){
		echo '<div id="change_value_fourn">
					<input type="button" value="Rétablir le tableau prix si bug d\'affichage" class="bouton" />
			  </div><br />';
	}
?>

<div id="modPrix" class="intitule"></div>


<!-- Category Attributes dialog -->
<style type="text/css">
.item-list-table { width: 100%; font: normal 10px verdana, helvetica, sans-serif; border: 1px solid #000000; border-collapse: collapse; background-color: #ffffff }
.item-list-table td { height: 17px; padding: 1px; text-align: left; border: 1px solid #000000; border-width: 1px 0; vertical-align: middle }
.item-list-table .tree { width: 20px; text-align: center }
.item-list-table .icon { display: inline-block; width: 16px; height: 16px; margin: 0 4px; background-repeat: no-repeat; vertical-align: middle; cursor: pointer }
.item-list-table .icon-add { background: url(table_add.png) }
.item-list-table tr.scat1 { background: #ffffff }
.item-list-table tr.selem1 { display: none; background: #f8f8f8 }
.item-list-table tr.selem1 .name-value { text-indent: 20px }
.item-list-table tr.selem1.odd { background: #f8f8f8 }
.item-list-table div.add { float: left; width: 15px; height: 15px; margin: 0 3px 0 0; background: url(../images/add-sub-more.gif) no-repeat 0 0 }
.item-list-table div.sub { float: left; width: 15px; height: 15px; margin: 0 3px 0 0; background: url(../images/add-sub-more.gif) no-repeat -15px 0 }
.item-list-table div.more { float: left; width: 15px; height: 15px; margin: 0 3px 0 0; background: url(../images/add-sub-more.gif) no-repeat -30px 0 }
</style>

<script type="text/javascript">
var facetNames = []; // cat 3 Selected Attribute Names

changeTypePrix(<?php print($advertiser) ?>);

// watching over the last active td
var last_active_td_x = last_active_td_y = -1;
$("#modPrix").on("click", "#references table", function(e){
  last_active_td_x = $(e.target).closest("td").index();
  last_active_td_y = $(e.target).closest("tr").index();
}).on("blur", "#references table td.intitule input", function(){
	colorReferenceCols();
});

function colorReferenceCols() {
	var $body = $("#references table tbody");
	$body.find("td").removeClass("isCat3SA");
	$body.find("tr:first td.intitule input.ref-col").each(function(k, elem){
		var $elem = $(elem);
		if (facetNames.find(function(facetName){ return facetName == $elem.val(); }))
			$body.find("tr td:nth-child("+($elem.closest("td").index()+1)+")").addClass("isCat3SA");
	});
}

function addAttributeToReferences(val) {
	var x = last_active_td_x < fixedColsLeft ? fixedColsLeft : (last_active_td_x >= refCols.length-fixedColsRight ? refCols.length-fixedColsRight : last_active_td_x+1);
	var y = last_active_td_y;
	var found = false;
	$("#references table tr:eq(0) > td > input").each(function(){
		if ($(this).val() == val) {
			found = true;
			return false;
		}
	});
	if (!found) {
		addRefColumn(1, x, 1);
		$("#references table tr:eq(0) > td:eq("+x+") > input").val(val).blur();
	}
}
</script>

<br />
<?php

	if($userPerms->has("m-prod--sm-products", "l")){
		echo('<br /><br />');
		echo('<input type="checkbox" name="input_product_locked" id="input_product_locked" ');
		if(strcmp($data['locked'],'1')=='0'){
			echo('checked="true"');
		}else{
			//echo('checked="false"');
		}
		echo(' /> <label for="input_product_locked">Bloquer la fiche</label>');
		echo('<br />');
	?>

	<?php
	}//end if($userPerms->has("m-prod--sm-products", "l"))

?>

	<center>
		<?php
			if(strcmp($data['locked'],'1')=='0'){
				echo('<strong>Important ! Il est formellement interdit de modifier le contenu de cette fiche</strong>');
				echo('<br /><br />');

				echo('<div id="edit_fiche_locked">');
					echo('Important ! Il est formellement interdit de modifier le contenu de cette fiche');
				echo('</div>');
				if($userPerms->has("m-prod--sm-products", "l")){
					echo('<input type="button" class="bouton" value="Valider" name="ok" onClick=""> &nbsp; <input type="reset" name="nok" value="Annuler" class="bouton">');
				}
			}else{
				echo('<input type="button" class="bouton" value="Valider" name="ok" onClick=""> &nbsp; <input type="reset" name="nok" value="Annuler" class="bouton">');

			}
		?>
	 </center>


<script type="text/javascript">
  $("input[name='ok']").click(function(){
    if (typePrice == 4 && this.form.csv_ref.value == '')
      createRefCode();
    $("input[name='productsLinked']").val(rpm.getPdtIdsString());
    this.form.submit();
    this.disabled = true;
  });
</script>
</form>
<br />
<div class="commentaire">Note : * signifie que le champ est obligatoire.</div>
<script type="text/javascript">
var __SID__ = '<?php echo $sid ?>';
var __ADMIN_URL__ = '<?php echo ADMIN_URL ?>';
var __MAIN_SEPARATOR__ = '<?php echo __MAIN_SEPARATOR__ ?>';
var __ERROR_SEPARATOR__ = '<?php echo __ERROR_SEPARATOR__ ?>';
var __ERRORID_SEPARATOR__ = '<?php echo __ERRORID_SEPARATOR__ ?>';
var __OUTPUT_SEPARATOR__ = '<?php echo __OUTPUT_SEPARATOR__ ?>';
var __OUTPUTID_SEPARATOR__ = '<?php echo __OUTPUTID_SEPARATOR__ ?>';
var __DATA_SEPARATOR__ = '<?php echo __DATA_SEPARATOR__ ?>';
var __IP_NOT_VALID__ = '<?php echo __IP_NOT_VALID__ ?>';
var __IP_VALID__ = '<?php echo __IP_VALID__ ?>';
var __IP_FINALIZED__ = '<?php echo __IP_FINALIZED__ ?>';

// FAMILIES //
<?php
$mts["JS CAT LIST"]["start"] = microtime(true);

$families = array();
$families[0]['name'] = '';
$families[0]['ref_name'] = '';
$families[0]['idParent'] = 0;

$result = & $handle->query("select f.id, fr.name, fr.ref_name, f.idParent from families f, families_fr fr where f.id = fr.id", __FILE__, __LINE__);
while ($family = & $handle->fetchAssoc($result))
{
	$families[$family['id']]['name'] = $family['name'];
	$families[$family['id']]['ref_name'] = $family['ref_name'];
	$families[$family['id']]['idParent'] = $family['idParent'];
	if (!isset($families[$family['idParent']]['nbchildren']))
		$families[$family['idParent']]['nbchildren'] = 1;
	else
		$families[$family['idParent']]['nbchildren']++;
	$families[$family['idParent']]['children'][$families[$family['idParent']]['nbchildren']-1] = $family['id'];
}

?>
// TODO intégrer le get des familles en ajax dans l'objet FamiliesBrowser
var families = [];
var familiesIndexByName = [];
var familiesIndexByRefName = [];
var name = 0; var ref_name = 1; var idParent = 2; var nbchildren = 3; var children = 4;

function fam_sort_ref_name(a, b)
{
	if (families[a][ref_name] > families[b][ref_name]) return 1;
	if (families[a][ref_name] < families[b][ref_name]) return -1;
	return 0;
}

<?php
foreach ($families as $id => $fam)
{
	print 'families[' . $id . '] = ["' . str_replace('"', '\"', $fam['name']) . '", "' . $fam['ref_name'] . '", ' . $fam['idParent'] . ', ';
	if (isset($fam['nbchildren']))
	{
		print $fam['nbchildren'] . ', [' . $fam['children'][0];
		for ($i = 1; $i < $fam['nbchildren']; $i++)
			print ", " . $fam['children'][$i];
		print "]";
	}
	else
	{
		print "0, []";
	}
	print  ']; ';
	//print 'familiesIndexById[' . $id . '] = ' . $id . '; ';
	print 'familiesIndexByName["' . str_replace('"', '\"', $fam['name']) . '"] = ' . $id . '; ';
	print 'familiesIndexByRefName["' . $fam['ref_name'] . '"] = ' . $id . ';';
	print "\n";
}
$mts["JS CAT LIST"]["end"] = microtime(true);
?>

// Product's main properties (namespace)
var PMP = {};

/* Family selection window */
PMP.fb = new HN.FamiliesBrowser();
PMP.fb.setID("FamilySelectionWindow");
PMP.fb.Build();
PMP.fb.mod = "add";

PMP.fse = new HN.FamiliesSearchEngine();
PMP.fse.setID("FamilySearchEngineWindow");
PMP.fse.Build();
PMP.fse.mod = "add";


PMP.fsw2 = new HN.Window();
PMP.fsw2.setID("FamilySearchEngineWindow");
PMP.fsw2.setTitleText("Choisir une famille");
PMP.fsw2.setMovable(true);
PMP.fsw2.showCancelButton(true);
PMP.fsw2.showValidButton(false);
//PMP.fsw2.setValidFct(function() {
//});
PMP.fsw2.setShadow(true);
PMP.fsw2.Build();

PMP.fsw = new HN.Window();
PMP.fsw.setID("FamilySelectionWindow");
PMP.fsw.setTitleText("Choisir une famille");
PMP.fsw.setMovable(true);
PMP.fsw.showCancelButton(true);
PMP.fsw.showValidButton(true);
PMP.fsw.setValidFct(function() {
	var family = PMP.fb.getCurFam();
	if (family.id != 0)
	{
		/*var as = "action=" + PMP.fb.mod;
		as += "&productID=<?php echo $_GET['id'] ?>";
		as += "&familyID=" + family.id;
		if (PMP.fb.mod == "update") as += "&oldfamilyID=" + PMP.fb.oldfamilyid;
		PMP.Family.AJAXHandle.data = as;
		$.ajax(PMP.Family.AJAXHandle);*/

		var data = familiesHidden.split(",");
		familiesHidden = "";
		var id = parseInt(family.id);
		if (PMP.fb.mod == "add")
		{
			for (i = 0; i < data.length-1; i++) {
				if (parseInt(data[i]) != id) familiesHidden += data[i] + ",";
			}
			familiesHidden += id + ",";
		}
		else if (PMP.fb.mod == "update")
		{
			for (i = 0; i < data.length-1; i++)
				if (parseInt(data[i]) == PMP.fb.oldfamilyid) familiesHidden += id + ",";
				else familiesHidden += data[i] + ",";
		}
		PMP.Family.fillTable();
		PMP.fsw.Hide();
	}
});
PMP.fsw.setShadow(true);
PMP.fsw.Build();

/* Adv choose window */
PMP.ShowAdvertiserSearchWindow = function() { document.getElementById('PMP-ChooseAdvertiser').style.display = 'block'; }
PMP.HideAdvertiserSearchWindow = function() { document.getElementById('PMP-ChooseAdvertiser').style.display = 'none'; }
PMP.SetAdvertiserName = function(ElementList) {
	if (ElementList.SelectedObject)
	{
		var advtype = " (<i>";
		switch (ElementList)
		{
			case PMP.ElementListA : advtype += "Annonceur"; break;
			case PMP.ElementListS : advtype += "Fournisseur"; break;
			default : advtype = "Annonceur"; break;
		}
		advtype += "</i> <b>" + ElementList.SelectedObject.ElementID + "</b>)";
		$("#advertiser").val(ElementList.SelectedObject.ElementID);
		$("#advertiser-name").html(ElementList.SelectedObject.firstChild.nodeValue + advtype);
		changeTypePrix(parseInt(ElementList.SelectedObject.ElementID));
	}
	PMP.HideAdvertiserSearchWindow();
}

PMP.MenuTabs = new TabList(
	"PMP-main_menu",
	function (tc, layerID) {
		for (var t in tc)
		{
			if (t == layerID) document.getElementById(layerID).style.display = "block";
			else document.getElementById(t).style.display = "none";
		}
	},
	{ "PMP-Suppliers" : "Fournisseur", "PMP-Advertisers" : "Annonceurs" }
);
PMP.MenuTabs.Draw();
PMP.MenuTabs.tc["PMP-Suppliers"].onclick();

PMP.SearchMenuA = new SearchMenu("PMP-search_menuA",
	{"0-9" : function () {
			PMP.ElementListAHandle.QueryA('AdvertisersSearch.php?' + __SID__ + '&AdvertisersSearchText=' + escape('[_0-9]'));
			document.getElementById('PMP-infosA').innerHTML = "Choisissez un annonceur";
		},
	"[A-Z]" : function (letter) {
			PMP.ElementListAHandle.QueryA('AdvertisersSearch.php?' + __SID__ + '&AdvertisersSearchText=' + escape(letter));
			document.getElementById('PMP-infosA').innerHTML = "Choisissez un annonceur";
		}
	}, "span");
PMP.ElementListA = new ElementList("PMP-listA", "li", function(id) { PMP.InfosAHandle.QueryA('AdvertisersInfos.php?' + __SID__ + '&id=' + id); });
PMP.ElementListAHandle = new AJAXHandle(function(xhr) { PMP.ElementListProcessResponse(PMP.ElementListA, xhr); }, "PMP-PerfReqLabel");
PMP.InfosAHandle = new AJAXHandle(function(xhr) { PMP.InfosProcessResponse("PMP-infosA", xhr); }, "PMP-PerfReqLabel");
PMP.SearchMenuA.Draw();

PMP.SearchMenuS = new SearchMenu("PMP-search_menuS",
	{"0-9" : function () {
			PMP.ElementListSHandle.QueryA('SuppliersSearch.php?' + __SID__ + '&SuppliersSearchText=' + escape('[_0-9]'));
			document.getElementById('PMP-infosS').innerHTML = "Choisissez un fournisseur";
		},
	"[A-Z]" : function (letter) {
			PMP.ElementListSHandle.QueryA('SuppliersSearch.php?' + __SID__ + '&SuppliersSearchText=' + escape(letter));
			document.getElementById('PMP-infosS').innerHTML = "Choisissez un fournisseur";
		}
	}, "span");
PMP.ElementListS = new ElementList("PMP-listS", "li", function(id) { PMP.InfosSHandle.QueryA('AdvertisersInfos.php?' + __SID__ + '&id=' + id); });
PMP.ElementListSHandle = new AJAXHandle(function(xhr) { PMP.ElementListProcessResponse(PMP.ElementListS, xhr); }, "PMP-PerfReqLabel");
PMP.InfosSHandle = new AJAXHandle(function(xhr) { PMP.InfosProcessResponse("PMP-infosS", xhr); }, "PMP-PerfReqLabel");
PMP.SearchMenuS.Draw();

PMP.ElementListProcessResponse = function(el, xhr)
{
	el.Clean();
	el.Clear();

	var mainsplit = xhr.responseText.split(__MAIN_SEPARATOR__);
	if (mainsplit[0] == '') // Pas d'erreur
	{
		var outputs = mainsplit[1].split(__OUTPUT_SEPARATOR__);
		for (var i = 0; i < outputs.length-1; i++)
		{
			var outputID = outputs[i].split(__OUTPUTID_SEPARATOR__);
			if (outputID.length == 2) el.Add(outputID[0],outputID[1]);
		}
		el.Draw();
	}
	else document.getElementById(el.id).innerHTML = mainsplit[0];
}

PMP.InfosProcessResponse = function(id, xhr)
{
	var mainsplit = xhr.responseText.split(__MAIN_SEPARATOR__);
	if (mainsplit[0] == '') document.getElementById(id).innerHTML = mainsplit[1];
	else document.getElementById("PMP-PerfReqLabel").innerHTML = mainsplit[0];
}

// Family namespace
PMP.Family = {};
/*
PMP.Family.AJAXHandle = {
	type : "GET",
	url: "AJAX-JSON-LinkedFamilies.php",
	dataType: "json",
	error: function (XMLHttpRequest, textStatus, errorThrown) {
		$("#PerfReqLabel-LinkedFamilies").text(textStatus);
	},
	success: function (data, textStatus) {
		if (data.error) $("#PerfReqLabel-LinkedFamilies").text(data.error);
		else
		{
			var tbody = $("#linked-families tbody");
			tbody.empty();
			familiesHidden = "";
			for (i = 0; i < data.length; i++)
			{
				tbody.append(
					"<tr id=\"linked-family-"+data[i].id+"\">" +
					"	<td class=\"family-id\">"+data[i].id+"</td>" +
					"	<td class=\"family-name\">"+data[i].name+"</td>" +
					"	<td class=\"edit\">" + "<img src=\"<?php echo ADMIN_URL ?>ressources/icons/table_edit.png\" alt=\"editer\" title=\"editer\" onclick=\"PMP.Family.UpdateLinkedFamily("+data[i].id+")\"/></td>" +
					"	<td class=\"del\">" + "<img src=\"<?php echo ADMIN_URL ?>ressources/icons/table_delete.png\" alt=\"supprimer\" title=\"supprimer\" onclick=\"PMP.Family.DeleteLinkedFamily("+data[i].id+")\"/></td>" +
					"</tr>");
					familiesHidden += data[i].id + ",";
			}
			$("#PerfReqLabel-LinkedFamilies").text("");
			$("#families input").val(familiesHidden);
		}
	}
};

PMP.Family.GetLinkedFamilies = function() {
	PMP.Family.AJAXHandle.data = "action=get&productID=<?php echo $_GET['id'] ?>";
	$.ajax(PMP.Family.AJAXHandle);
}
*/
PMP.Family.UpdateLinkedFamily = function(id) {
	PMP.fb.mod = "update";
	PMP.fb.oldfamilyid = id;
	PMP.fb.SelectFamByID(id);
	PMP.fsw.Show();
}
PMP.Family.DeleteLinkedFamily = function(id) {
	if (document.getElementById("linked-family-"+id))
	{
		var data = familiesHidden.split(",");
		familiesHidden = "";
		id = parseInt(id);
		for (i = 0; i < data.length-1; i++)
			if (parseInt(data[i]) != id) familiesHidden += data[i] + ",";

		PMP.Family.fillTable();
		//PMP.Family.AJAXHandle.data = "action=delete&productID=<?php echo $_GET['id'] ?>&familyID="+id;
		//$.ajax(PMP.Family.AJAXHandle);
	}
}

PMP.Family.fillTable = function() {
	var data = familiesHidden.split(",");
	var $tbody = $("#linked-families tbody");
	$tbody.empty();
  if (data.length) {
    for (var i=0; i<data.length-1; i++) {
      data[i] = parseInt(data[i]);
      $tbody.append(
        "<tr id=\"linked-family-"+data[i]+"\">" +
        "	<td class=\"family-id\">"+data[i]+"</td>" +
        "	<td class=\"family-name\">"+families[data[i]][name]+"</td>" +
        "	<td class=\"action up\">" + "<img src=\"<?php echo ADMIN_URL ?>ressources/icons/arrow_up.png\" alt=\"monter\" title=\"monter\" onclick=\"PMP.Family.FamilyUp("+data[i]+")\"/></td>" +
        "	<td class=\"action down\">" + "<img src=\"<?php echo ADMIN_URL ?>ressources/icons/arrow_down.png\" alt=\"descendre\" title=\"descendre\" onclick=\"PMP.Family.FamilyDown("+data[i]+")\"/></td>" +
        "	<td class=\"edit\">" + "<img src=\"<?php echo ADMIN_URL ?>ressources/icons/table_edit.png\" alt=\"editer\" title=\"editer\" onclick=\"PMP.Family.UpdateLinkedFamily("+data[i]+")\"/></td>" +
        "	<td class=\"del\">" + "<img src=\"<?php echo ADMIN_URL ?>ressources/icons/table_delete.png\" alt=\"supprimer\" title=\"supprimer\" onclick=\"PMP.Family.DeleteLinkedFamily("+data[i]+")\"/></td>" +
        "</tr>");
    }
    $("#PerfReqLabel-LinkedFamilies").text("");
    $("#familiesHidden").val(familiesHidden);
  }
}

//PMP.Family.GetLinkedFamilies();
PMP.Family.fillTable();

PMP.Family.FamilyUp = function(id){

    var $tr = $('table#linked-families tr#linked-family-'+id);
    $tr.prevAll().first().before($tr.parent().find("tr[id^='linked-family-"+id+"']"));
    reorderFamiliesHidden();
}

PMP.Family.FamilyDown = function(id){

    var $tr = $('table#linked-families tr#linked-family-'+id);
    $tr.nextAll().first().after($tr.parent().find("tr[id^='linked-family-"+id+"']"));
    reorderFamiliesHidden();
}

function reorderFamiliesHidden(){
  var famHid = '';
  $('table#linked-families tbody tr').each(function(){
    famHid += $(this).find('td.family-id').text()+',';
  });
  $('#familiesHidden').val(famHid);
}

function recordNewPicOrder(pdtID){
  var newPicOrder = $( "#sortable" ).sortable('toArray');
  $.ajax({
    async: true,
    cache: false,
    data: "action=reorderpics<?php echo ($_GET["type"]=="add_adv" || $_GET["type"]=="edit_adv") ? "&type=adv" : "" ?>&pdtID="+pdtID+"&picsOrder="+newPicOrder,
    dataType: "json",
    error: function (XMLHttpRequest, textStatus, errorThrown) { alert("Fatal error while loading Products Data"); },
    success: function (data, textStatus) {
            if (data.pdtID){
               HN.TC.BO.refreshProductImages(data.pdtID);
               $('#btn-reorder').hide();
            }
    },
    type: "GET",
    url: "AJAX_pdt-edition.php"
  });
}

$( "#change_value_fourn" ).click(function() {
  var typeprice = $("#typeprice").val();
  if(typeprice == 4){
	  alert('Le tableau prix est déjà présent. Il n\'est pas nécessaire de le rétablir ');
  }else{
  if(confirm('Souhaitez rétablir le tableau prix ? ')){
	  $.ajax({
			url: 'AJAX-change_type.php?id_pdt='+<?= $_GET['id']?>,
			type: 'GET',
			success:function(data){
				window.location.reload();
			}
		});
  }
  }
});
</script>
<?php

}  // fin next

?>

</div>

<style type="text/css">
.comments { font: 11px Verdana, Arial, Helvetica, sans-serif; margin: 0, padding: 0; width: 100%; }
.comments th{ font: 11px Verdana, Arial, Helvetica, sans-serif; border-bottom: 2px solid #a00100; }
.comments td { border-style: solid; border-color: #E9EFF8; border-width: 4px 0px 2px 0px; padding: 1px 5px; }
.comments .col-1 { width: 95px; }
.comments .col-2 { width: 60px; }
.comments .col-3 { width: 70px; }
.comments .col-4 { width: auto; }
.comments .c_odd { background-color: #F0F0F0; }
.comments .c_even { background-color: #FBFBFB; }
.comments .time { color: #B00000; font: bold 10px Arial, Helvetica, sans-serif; text-align: center; }
.comments .contact { color: #0000B0; font: bold 10px Arial, Helvetica, sans-serif; text-align: center; }
.comments .tools { text-align: center; }
.comments .tools .edit { width: 20px; height: 16px; background: url(b_edit.png) no-repeat 2px 0px; float: left; }
.comments .tools .delete { width: 20px; height: 16px; background: url(b_drop.png) no-repeat 2px 0px; float: left; }
.comments .tools .show-0 { width: 20px; height: 16px; background: url(Light_16x16_red.gif) no-repeat 2px 0px; float: right; }
.comments .tools .show-1 { width: 20px; height: 16px; background: url(Light_16x16_green.gif) no-repeat 2px 0px; float: right; }
.comments .col-3 textarea { height: auto; width: auto; border: 0; background: transparent; font: 11px Verdana, Arial, Helvetica, sans-serif; }

.comments .col-1, .comments .col-2, .comments .col-3 { min-height: 0; }
#EditCommentWindowShad { z-index: 3; position: absolute; top: 80px; left: 125px; width: 804px; height: 404px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#EditCommentWindow, #EditCommentWindowAvis { z-index: 4; position: absolute; top: 75px; left: 120px; width: 800px; height: 400px; border: 2px solid #999999; background-color: #E9EFF8; visibility: hidden;  font: 11px Verdana, Arial, Helvetica, sans-serif; }
#AddCommentWindowShad { z-index: 5; position: absolute; top: 60px; left: 75px; width: 804px; height: 404px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#AddCommentWindow, #AddCommentWindowAvis { z-index: 6; position: absolute; top: 55px; left: 70px; width: 800px; height: 400px; border: 2px solid #999999; background-color: #E9EFF8; visibility: hidden;  font: 11px Verdana, Arial, Helvetica, sans-serif; }
.CW_datetime_label, .CW_Avis_datetime_label { position: absolute; top: 32px; left: 10px; width: 265px; border-bottom: 1px solid #B00000; }
.CW_datetime, .CW_Avis_datetime { position: absolute; top: 30px; left: 290px; width: 198px; border: 1px solid #000000; }
.CW_show_label { position: absolute; top: 55px; left: 404px; width: 55px; border-bottom: 1px solid #B00000; }
.CW_show_note { position: absolute; top: 55px; left: 504px; width: 55px; border-bottom: 1px solid #B00000; }
.CW_show_note_value { position: absolute; top: 55px; left: 544px; width: 55px; border-bottom: 1px solid #B00000; }
.CW_show, .CW_Avis_show { position: absolute; top: 53px; left: 474px; width: 13px; height: 13px; }
.CW_text_label, .CW_text_Avis_label { position: absolute; top: 55px; left: 10px; width: 155px; border-bottom: 1px solid #B00000; }
.CW_text, .CW_Avis_comment { position: absolute; top: 75px; left: 10px; width: 778px; height: 313px; border: 1px solid #000000; }
.CW_error, .CW_Avis_error { position: absolute; top: 22px; right: 10px; width: 290px; height: 54px; overflow: auto; color: #B00000; font-size: 10px; }

</style>
<?php
$notations = ProductNotation::get('id_product = '.$_GET["id"]);
if(!empty($notations)){ // user notation and commentaries

?>
<br />
<div class="titreStandard" id="avis">Gestion des avis client</div>
<div class="bg" style="position: relative">
<!--	<div id="AddCommentWindowAvis">
		<input id="ACW_Avis_id" type="hidden" value=""/>
		<span class="CW_Avis_datetime_label">Date du commentaire (JJ/MM/AAAA HH:mm) :</span><input id="ACW_Avis_datetime" class="CW_Avis_datetime" type="edit" value=""/>
		<span class="CW_Avis_show_label">Afficher :</span><input id="ACW_Avis_show" class="CW_Avis_show" type="checkbox"/>
		<span class="CW_Avis_text_label">Contenu du commentaire :</span>
		<textarea id="ACW_Avis_text" class="CW_Avis_text"></textarea>
		<div id="AddCommentWindowAvisError" class="CW_Avis_error"><br/></div>
	</div>-->
	<div id="EditCommentWindowAvis">
		<input id="ECW_Avis_id" type="hidden" value=""/>
		<span class="CW_datetime_label">Date du commentaire (JJ/MM/AAAA HH:mm) :</span><input id="ECW_Avis_datetime" class="CW_datetime" type="edit" value=""/>
		<span class="CW_show_label">Afficher :</span><input id="ECW_Avis_show" class="CW_show" type="checkbox"/>
                <span class="CW_show_note">Note :</span><span class="CW_show_note_value"></span>
		<span class="CW_text_Avis_label">Contenu du commentaire :</span>
		<textarea id="ECW_Avis_comment" class="CW_Avis_comment"></textarea>
		<div id="AlterAvisWindowError" class="CW_Avis_error"><br/></div>
	</div>
	<div id="PerfReqLabelAvis" class="PerfReqLabel"><br/></div>
	<div id="AvisProcessError" class="InfosError"><br/></div>
<script type="text/javascript">
var ProductsAvisAJAXHandle = new AJAXHandle(ProductAvisResponse, "PerfReqLabelAvis");

function GetAvis(id)
{
	var id = parseInt(id);
	if (document.getElementById("notation_comment_"+id))
		ProductsAvisAJAXHandle.QueryA("AvisManagement.php?action=get&id="+id);
}
function DeleteAvis(id)
{
	var id = parseInt(id);
	if (document.getElementById("notation_comment_"+id))
	{
		if (confirm("Voulez-vous vraiment supprimer ce commentaire ?"))
			ProductsAvisAJAXHandle.QueryA("AvisManagement.php?action=delete&id="+id);
	}
}
function ToggleShowAvis(id)
{
	var id = parseInt(id);
	if (document.getElementById("notation_comment_"+id))
		ProductsAvisAJAXHandle.QueryA("AvisManagement.php?action=toggleshow&id="+id);
}
function AlterAvis() {
	var as = "action=alter";
	as += "&id=" + parseInt(document.getElementById("ECW_Avis_id").value);
	as += "&datetime=" + escape(document.getElementById("ECW_Avis_datetime").value);
	as += "&comment=" + escape(document.getElementById("ECW_Avis_comment").value);
	as += "&show=" + (document.getElementById("ECW_Avis_show").checked ? "1" : "0");

	ProductsAvisAJAXHandle.QueryA("AvisManagement.php?" + as);
}
function ProductAvisResponse(xhr) {

	var mainsplit = xhr.responseText.split(__MAIN_SEPARATOR__);
	var outputs = mainsplit[1].split(__OUTPUT_SEPARATOR__);

	if (mainsplit[0] == '') // Pas d'erreur
	{
		switch (outputs[0])
		{
			case "get" :
				var id, datetime, comment, note, show;
				for (var i = 1; i < outputs.length-1; i++)
				{
					var outputID = outputs[i].split(__OUTPUTID_SEPARATOR__);
					if (outputID.length != 2) break;
					switch (outputID[0])
					{
						case "id" :  id = parseInt(outputID[1]); break;
						case "datetime" : datetime = outputID[1]; break;
						case "note" : note = outputID[1]; break;
                                                case "comment" : comment = outputID[1]; break;
						case "show" : show = parseInt(outputID[1]); if (show == 0) show = 1; break;
						default: break;
					}
				}
				var commentrow;
				if (commentrow = document.getElementById("notation_comment_"+id))
				{
					document.getElementById("ECW_Avis_id").value = id;
					document.getElementById("ECW_Avis_datetime").value = datetime;
					document.getElementById("ECW_Avis_comment").value = comment;
                                        $(".CW_show_note_value").html(note);
					document.getElementById("ECW_Avis_show").checked = (show == 1);
					document.getElementById("EditCommentWindowAvis").style.top = (commentrow.offsetTop - 20) + "px";
					document.getElementById("EditCommentWindowShad").style.top = (commentrow.offsetTop - 15) + "px";
					ecw_avis.Show();
				}
				document.getElementById("AvisProcessError").innerHTML = "<br/>";
				break;

			case "alter" :
				var id, datetime, comment, show;
				for (var i = 1; i < outputs.length-1; i++)
				{
					var outputID = outputs[i].split(__OUTPUTID_SEPARATOR__);
					if (outputID.length != 2) break;
					switch (outputID[0])
					{
						case "id" :  id = parseInt(outputID[1]); break;
						case "datetime" : datetime = outputID[1]; break;
						case "comment" : comment = outputID[1].replace(/(\r\n|\n|\r)/g, "<br/>"); break;
						case "show" : show = outputID[1]; break;
						default: break;
					}
				}
				if (document.getElementById("notation_comment_"+id))
				{
					document.getElementById("notation_comment_"+id+"_time").innerHTML = datetime;
					document.getElementById("notation_comment_"+id+"_comment").innerHTML = comment;
					document.getElementById("notation_comment_"+id+"_show").className = "show-"+show;
					ecw_avis.Hide();
				}

				document.getElementById("AlterAvisWindowError").innerHTML = "<br/>";
				break;

			case "delete" :
				var id, comment;
				outputID = outputs[1].split(__OUTPUTID_SEPARATOR__);
				if (outputID.length != 2) break;
				if (outputID[0] == "id") id = parseInt(outputID[1]);
				if (comment = document.getElementById("notation_comment_"+id))
					comment.parentNode.removeChild(comment);

				document.getElementById("AvisProcessError").innerHTML = "<br/>";
				break;

			case "toggleshow" :
				var id, show;
				for (var i = 1; i < outputs.length-1; i++)
				{
					var outputID = outputs[i].split(__OUTPUTID_SEPARATOR__);
					if (outputID.length != 2) break;
					switch (outputID[0])
					{
						case "id" :  id = parseInt(outputID[1]); break;
						case "show" : show = outputID[1]; break;
						default: break;
					}
				}
				if (document.getElementById("notation_comment_"+id))
					document.getElementById("notation_comment_"+id+"_show").className = "show-"+show;

				document.getElementById("AvisProcessError").innerHTML = "<br/>";
				break;

			default : break;
		}
	}
	else
	{
		var errors = mainsplit[0].replace(new RegExp(__ERROR_SEPARATOR__, "g"), "<br/>");
		for (var i = 0; i < errors.length; i++)
		switch (outputs[0])
		{
			case "alter" : document.getElementById("AlterAvisWindowError").innerHTML = errors; break;
			default : document.getElementById("AvisProcessError").innerHTML = errors; break;
		}
	}
}

ecw_avis = new HN.Window();
ecw_avis.setID("EditCommentWindowAvis");
ecw_avis.setTitleText("Edition du commentaire");
ecw_avis.setMovable(true);
ecw_avis.showValidButton(true);
ecw_avis.showCancelButton(true);
ecw_avis.setValidFct( function() { AlterAvis(); } );
ecw_avis.setShadow(true);
ecw_avis.Build();

</script>

	<table cellspacing="0" cellpadding="0" border="0" class="comments" id="CommentsList">
	<thead>
		<tr>
			<th class="col-1">Date</th>
			<th class="col-2">Id  client</th>
			<th class="col-3">Outils</th>
			<th class="col-4" style="width:90px">Note attribuée</th>
                        <th class="col-5">Commentaire</th>
		</tr>
	</thead>
	<tbody>
<?php
		$nbc = 0;
		foreach($notations as $key => $notation){
			$volume[$key]  = $notation['timestamp'];
		}
		array_multisort($volume, SORT_DESC, $notations);
		foreach ($notations as $notation)
		{
			$style = ($nbc%2 == 0 ? 'c_even' : 'c_odd');
                        $notation_active = $notation['inactive'] ? 0 : 1;
?>
		<tr id="notation_comment_<?php echo $notation['id'] ?>" class="<?php echo $style ?>">
			<td id="notation_comment_<?php echo $notation['id'] ?>_time" class="col-1 time" ><?php echo date('d/m/Y H:i', $notation['timestamp']) ?></td>
			<td id="notation_comment_<?php echo $notation['id'] ?>_contact" class="col-2 contact" ><?php echo empty($notation['id_client']) ? "Id incorrect" : $notation['id_client'] ?></td>
			<td class="col-3 tools">
				<div title="editer" class="edit" onclick="GetAvis(<?php echo $notation['id'] ?>)"></div>
				<div title="supprimer" class="delete" onclick="DeleteAvis(<?php echo $notation['id'] ?>)"></div>
				<div id="notation_comment_<?php echo $notation['id'] ?>_show" title="Afficher/Cacher" class="show-<?php echo $notation_active ?>" onclick="ToggleShowAvis(<?php echo $notation['id'] ?>)"></div>
			</td>
			<td id="notation_comment_<?php echo $notation['id'] ?>_mark" class="col-4"><?php echo str_replace(array("\r\n", "\n", "\r",), "<br/>", $notation['note']) ?></td>
                        <td id="notation_comment_<?php echo $notation['id'] ?>_comment" class="col-5"><?php echo str_replace(array("\r\n", "\n", "\r",), "<br/>", to_entities($notation['comment'])) ?></td>
		</tr>
<?php
		$nbc++;
		}
?>

	</tbody>
	</table>
</div>
<?php
}// end user notation and commentaries

	if ($result = & $handle->query("select id, contactID, timestamp, text, `show` from products_comments where productID = " . $_GET['id'] . " order by timestamp desc", __FILE__, __LINE__, false))
	{
?>
<br/>
<div class="titreStandard">Edition des demandes utilisateurs</div>
<br/>
<div class="bg" style="position: relative">
	<div id="AddCommentWindow">
		<input id="ACW_id" type="hidden" value=""/>
		<span class="CW_datetime_label">Date du commentaire (JJ/MM/AAAA HH:mm) :</span><input id="ACW_datetime" class="CW_datetime" type="edit" value=""/>
		<span class="CW_show_label">Afficher :</span><input id="ACW_show" class="CW_show" type="checkbox"/>
		<span class="CW_text_label">Contenu du commentaire :</span>
		<textarea id="ACW_text" class="CW_text"></textarea>
		<div id="AddCommentWindowError" class="CW_error"><br/></div>
	</div>
	<div id="EditCommentWindow">
		<input id="ECW_id" type="hidden" value=""/>
		<span class="CW_datetime_label">Date du commentaire (JJ/MM/AAAA HH:mm) :</span><input id="ECW_datetime" class="CW_datetime" type="edit" value=""/>
		<span class="CW_show_label">Afficher :</span><input id="ECW_show" class="CW_show" type="checkbox"/>
		<span class="CW_text_label">Contenu du commentaire :</span>
		<textarea id="ECW_text" class="CW_text"></textarea>
		<div id="AlterCommentWindowError" class="CW_error"><br/></div>
	</div>
	<input type="button" value="Créer un commentaire" onclick="ShowAddCommentWindow();"/>
	<div id="PerfReqLabelComments" class="PerfReqLabel"><br/></div>
	<div id="CommentsProcessError" class="InfosError"><br/></div>
<script type="text/javascript">
var ProductsAJAXHandle = new AJAXHandle(ProductResponse, "PerfReqLabelComments");

function ShowAddCommentWindow()
{
	document.getElementById("ACW_datetime").value = "";
	document.getElementById("ACW_text").value = "";
	document.getElementById("ACW_show").checked = true;
	acw.Show();
}
function GetComment(id)
{
	var id = parseInt(id);
	if (document.getElementById("comment_"+id))
		ProductsAJAXHandle.QueryA("CommentsManagment.php?action=get&id="+id);
}
function AddComment()
{
	var as = "action=add";
	as += "&productID=<?php echo $_GET['id'] ?>";
	as += "&datetime=" + escape(document.getElementById("ACW_datetime").value);
	as += "&text=" + escape(document.getElementById("ACW_text").value);
	as += "&show=" + (document.getElementById("ACW_show").checked ? "1" : "0");

	ProductsAJAXHandle.QueryA("CommentsManagment.php?"+as);
}
function DeleteComment(id)
{
	var id = parseInt(id);
	if (document.getElementById("comment_"+id))
	{
		if (confirm("Voulez-vous vraiment supprimer ce commentaire ?"))
			ProductsAJAXHandle.QueryA("CommentsManagment.php?action=delete&id="+id);
	}
}
function ToggleShow(id)
{
	var id = parseInt(id);
	if (document.getElementById("comment_"+id))
		ProductsAJAXHandle.QueryA("CommentsManagment.php?action=toggleshow&id="+id);
}
function AlterComment() {
	var as = "action=alter";
	as += "&id=" + parseInt(document.getElementById("ECW_id").value);
	as += "&datetime=" + escape(document.getElementById("ECW_datetime").value);
	as += "&text=" + escape(document.getElementById("ECW_text").value);
	as += "&show=" + (document.getElementById("ECW_show").checked ? "1" : "0");

	ProductsAJAXHandle.QueryA("CommentsManagment.php?" + as);
}
function ProductResponse(xhr) {

	var mainsplit = xhr.responseText.split(__MAIN_SEPARATOR__);
	var outputs = mainsplit[1].split(__OUTPUT_SEPARATOR__);

	if (mainsplit[0] == '') // Pas d'erreur
	{
		switch (outputs[0])
		{
			case "get" :
				var id, datetime, text, show;
				for (var i = 1; i < outputs.length-1; i++)
				{
					var outputID = outputs[i].split(__OUTPUTID_SEPARATOR__);
					if (outputID.length != 2) break;
					switch (outputID[0])
					{
						case "id" :  id = parseInt(outputID[1]); break;
						case "datetime" : datetime = outputID[1]; break;
						case "text" : text = outputID[1]; break;
						case "show" : show = parseInt(outputID[1]); if (show != 0) show = 1; break;
						default: break;
					}
				}
				var commentrow;
				if (commentrow = document.getElementById("comment_"+id))
				{
					document.getElementById("ECW_id").value = id;
					document.getElementById("ECW_datetime").value = datetime;
					document.getElementById("ECW_text").value = text;
					document.getElementById("ECW_show").checked = (show == 1);
					document.getElementById("EditCommentWindow").style.top = (commentrow.offsetTop - 20) + "px";
					document.getElementById("EditCommentWindowShad").style.top = (commentrow.offsetTop - 15) + "px";
					ecw.Show();
				}
				document.getElementById("CommentsProcessError").innerHTML = "<br/>";
				break;

			case "add" :
				var id, productID, contactID, datetime, text, show;
				for (var i = 1; i < outputs.length-1; i++)
				{
					var outputID = outputs[i].split(__OUTPUTID_SEPARATOR__);
					if (outputID.length != 2) break;
					switch (outputID[0])
					{
						case "id" :  id = parseInt(outputID[1]); break;
						case "productID" : productID = parseInt(outputID[1]); break;
						case "contactID" : contactID = parseInt(outputID[1]); break;
						case "datetime" : datetime = outputID[1]; break;
						case "text" : text = outputID[1].replace(/(\r\n|\n|\r)/g, "<br/>"); break;
						case "show" : show = parseInt(outputID[1]); if (show != 0) show = 1; break;
						default: break;
					}
				}

				var tbody;
				if (tbody = document.getElementById("CommentsList").getElementsByTagName("tbody")[0])
				{
					var tr = document.createElement("tr");
					tr.id = "comment_" + id;
					tr.className = tbody.getElementsByTagName('tr').length%2 == 0 ? 'c_even' : 'c_odd';

					var td = document.createElement("td");
					td.id = 'comment_'+id+'_time';
					td.className = 'col-1 time';
					td.appendChild(document.createTextNode(datetime));
					tr.appendChild(td);

					td = document.createElement("td");
					td.id = 'comment_'+id+'_contact';
					td.className = 'col-2 contact';
					td.appendChild(document.createTextNode(contactID == 0 ? 'TC' : contactID));
					tr.appendChild(td);

					td = document.createElement("td");
					td.className = 'col-3 tools';
					td.innerHTML = '' +
						'<div title="editer" class="edit" onclick="GetComment('+id+')"></div>' +
						'<div title="supprimer" class="delete" onclick="DeleteComment('+id+')"></div>' +
						'<div id="comment_'+id+'_show" title="Afficher/Cacher" class="show-'+show+'" onclick="ToggleShow('+id+')"></div>';
					tr.appendChild(td);

					td = document.createElement("td");
					td.id = 'comment_'+id+'_text';
					td.className = 'col-4';
					td.innerHTML = text;
					tr.appendChild(td);

					tbody.insertBefore(tr, tbody.firstChild);
					acw.Hide();
				}
				document.getElementById("AddCommentWindowError").innerHTML = "<br/>";
				break;

			case "alter" :
				var id, datetime, text, show;
				for (var i = 1; i < outputs.length-1; i++)
				{
					var outputID = outputs[i].split(__OUTPUTID_SEPARATOR__);
					if (outputID.length != 2) break;
					switch (outputID[0])
					{
						case "id" :  id = parseInt(outputID[1]); break;
						case "datetime" : datetime = outputID[1]; break;
						case "text" : text = outputID[1].replace(/(\r\n|\n|\r)/g, "<br/>"); break;
						case "show" : show = outputID[1]; break;
						default: break;
					}
				}
				if (document.getElementById("comment_"+id))
				{
					document.getElementById("comment_"+id+"_time").innerHTML = datetime;
					document.getElementById("comment_"+id+"_text").innerHTML = text;
					document.getElementById("comment_"+id+"_show").className = "show-"+show;
					ecw.Hide();
				}

				document.getElementById("AlterCommentWindowError").innerHTML = "<br/>";
				break;

			case "delete" :
				var id, comment;
				outputID = outputs[1].split(__OUTPUTID_SEPARATOR__);
				if (outputID.length != 2) break;
				if (outputID[0] == "id") id = parseInt(outputID[1]);
				if (comment = document.getElementById("comment_"+id))
					comment.parentNode.removeChild(comment);

				document.getElementById("CommentsProcessError").innerHTML = "<br/>";
				break;

			case "toggleshow" :
				var id, show;
				for (var i = 1; i < outputs.length-1; i++)
				{
					var outputID = outputs[i].split(__OUTPUTID_SEPARATOR__);
					if (outputID.length != 2) break;
					switch (outputID[0])
					{
						case "id" :  id = parseInt(outputID[1]); break;
						case "show" : show = outputID[1]; break;
						default: break;
					}
				}
				if (document.getElementById("comment_"+id))
					document.getElementById("comment_"+id+"_show").className = "show-"+show;

				document.getElementById("CommentsProcessError").innerHTML = "<br/>";
				break;

			default : break;
		}
	}
	else
	{
		var errors = mainsplit[0].replace(new RegExp(__ERROR_SEPARATOR__, "g"), "<br/>");
		for (var i = 0; i < errors.length; i++)
		switch (outputs[0])
		{
			case "add" : document.getElementById("AddCommentWindowError").innerHTML = errors; break;
			case "alter" : document.getElementById("AlterCommentWindowError").innerHTML = errors; break;
			default : document.getElementById("CommentsProcessError").innerHTML = errors; break;
		}
	}
}

ecw = new HN.Window();
ecw.setID("EditCommentWindow");
ecw.setTitleText("Edition du commentaire");
ecw.setMovable(true);
ecw.showValidButton(true);
ecw.showCancelButton(true);
ecw.setValidFct( function() { AlterComment(); } );
ecw.setShadow(true);
ecw.Build();

acw = new HN.Window();
acw.setID("AddCommentWindow");
acw.setTitleText("Ajout d'un commentaire");
acw.setMovable(true);
acw.showValidButton(true);
acw.showCancelButton(true);
acw.setValidFct( function() { AddComment(); } );
acw.setShadow(true);
acw.Build();

</script>
	<table cellspacing="0" cellpadding="0" border="0" class="comments" id="CommentsList">
	<thead>
		<tr>
			<th class="col-1">Date</th>
			<th class="col-2">Contact</th>
			<th class="col-3">Outils</th>
			<th class="col-4">Demandes des utilisateurs</th>
		</tr>
	</thead>
	<tbody>
<?php
		$nbc = 0;
		while ($comment = & $handle->fetchAssoc($result))
		{
			$style = ($nbc%2 == 0 ? 'c_even' : 'c_odd');
?>
		<tr id="comment_<?php echo $comment['id'] ?>" class="<?php echo $style ?>">
			<td id="comment_<?php echo $comment['id'] ?>_time" class="col-1 time" ><?php echo date('d/m/Y H:i', $comment['timestamp']) ?></td>
			<td id="comment_<?php echo $comment['id'] ?>_contact" class="col-2 contact" ><?php echo empty($comment['contactID']) ? "TC" : $comment['contactID'] ?></td>
			<td class="col-3 tools">
				<div title="editer" class="edit" onclick="GetComment(<?php echo $comment['id'] ?>)"></div>
				<div title="supprimer" class="delete" onclick="DeleteComment(<?php echo $comment['id'] ?>)"></div>
				<div id="comment_<?php echo $comment['id'] ?>_show" title="Afficher/Cacher" class="show-<?php echo $comment['show'] ?>" onclick="ToggleShow(<?php echo $comment['id'] ?>)"></div>
			</td>
			<td id="comment_<?php echo $comment['id'] ?>_text" class="col-4"><?php echo str_replace(array("\r\n", "\n", "\r",), "<br/>", $comment['text']) ?></td>
		</tr>
<?
		$nbc++;
		}
?>

	</tbody>
	</table>
</div>
<?php
	} // end comments section
} // fin accès ok

if (DEBUG) {
  $mts["TOTAL TIME"]["end"] = microtime(true);
  foreach($mts as $mtn => $mt) print $mtn . " = <b>" . ($mt["end"]-$mt["start"])*1000 . "ms</b><br/>\n";
}

require(ADMIN . 'tail.php');

?>
