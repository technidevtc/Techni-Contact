<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN . 'products.php');
require(ADMIN . 'users.php');
require(ADMIN . 'families.php');
require(ADMIN . 'advertisers.php');
require(ADMIN . 'tva.php');

$title = $navBar = 'Base de données des produits';
require(ADMIN . 'head.php');



$advertisers = & displayAdvertisers($handle, 'order by a.nom1');
$families    = & displayFamilies($handle);
$suppliers   = & GetSuppliersInfos($handle, 'order by a.nom1');
$listeTVAs   = displayTVAs($handle, ' order by taux desc');
mb_convert_variables("UTF-8","ASCII,UTF-8,ISO-8859-1,CP1252",$listeTVAs);
$idTVAdftDft = getConfig($handle, 'idTVAdft');

///////////////////////////////////////////////////////////////////////////

$error = false;
$errorstring = '';

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
  if (!$userPerms->has("m-prod--sm-products", "e")) {
    $errorstring = "Vous n'avez pas les droits de modification produits.";
  }
  else {
    $nom = isset($_POST['nom']) ? substr(trim($_POST['nom']), 0, 255) : '';
    $nom = preg_replace('/ +/', ' ', $nom);

    if($nom == '')
    {
        $error = true;
        $errorstring .= '- Vous n\'avez pas saisi le nom du produit<br/>';
    }
    else if($nom[0] < '0' && $nom[0] > '9' && $nom[0] < 'A' && $nom[0] > 'Z')
    {
        $error = true;
        $errorstring .= '- Le nom du produit doit débuter par une lettre / chiffre<br/>';
    }

    $fastdesc = isset($_POST['fastdesc']) ? substr(trim($_POST['fastdesc']), 0, 255) : '';

    if($fastdesc == '')
    {
        $error = true;
        $errorstring .= '- Vous n\'avez pas saisi la description rapide du produit<br/>';
    }

    $advertiser = isset($_POST['advertiser']) ? $_POST['advertiser'] : 0;

    if($advertiser == 0)
    {
        $error = true;
        $errorstring .= '- Vous n\'avez pas sélectionné l\'annonceur<br/>';
    }

    $isSupplier = $suppliers[$advertiser][0] != '' ? true : false;

	if ($suppliers[$advertiser][1] != '')
	{
		if ($suppliers[$advertiser][1] == 1) $prixPublic = 1;
		elseif ($suppliers[$advertiser][1] == 0) $prixPublic = 0;
		else $prixPublic = -1;
	}
	else
		$prixPublic = -1;


	$margeRemiseDft = $suppliers[$advertiser][2];
	$idTVAdft = $suppliers[$advertiser][4];

	if ($isSupplier)
	{
		$errorSupplier = false;

		if ($prixPublic == -1)
		{
	        $errorSupplier = true;
	        $errorstring .=	'- Erreur fatale dans la détermination du type de prix (prix fournisseur ou prix public) du fournisseur.<br/>';
		}

		if (!preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', $margeRemiseDft))
		{
	        $errorSupplier = true;
	        $errorstring .=	'- Erreur fatale dans la détermination du taux de marge ou de remise par défaut du fournisseur.<br/>';
		}

		if (!existTVA($handle, $idTVAdft))
		{
	        $errorSupplier = true;
	        $errorstring .=	'- Erreur fatale dans la détermination du taux de tva par défaut du fournisseur.<br/>';
		}

		if ($errorSupplier)
		{
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

  if($familiesHidden == '')
  {
      $error = true;
      $errorstring .= '- Vous devez lier le produit au minimum à une sous-famille<br/>';
  }

  if($familiesShown == '')
  {
      $familiesShown = '&nbsp; Actuellement aucune sous-famille associée.';
  }


  $alias    = isset($_POST['alias'])    ? substr(trim($_POST['alias']), 0, 255)    : '';
  $keywords = isset($_POST['keywords']) ? substr(trim($_POST['keywords']), 0, 255) : '';


  $desc = isset($_POST['desc']) ? trim($_POST['desc']) : '';

  if($desc == '')
  {
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
      if (($result = & $handle->query('select p.name, a.nom1 from products_fr p, advertisers a where p.id = \'' . $handle->escape($productsTab[$i]). '\' and p.idAdvertiser = a.id', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1) {
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
	if (($typeprice == 0 || $typeprice == 4) && $isSupplier)
	{
		if($contrainteProduit != '' && !preg_match('/^[0-9]+$/', $contrainteProduit))
		{
			$error = true;
			$errorstring .= '- La contrainte de quantité de produit saisie est incorrecte<br/>';
		}
	}

    if($typeprice == 0)
    {

		if(!preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', $price)) // vérif validité du prix public
		{
		   $error = true;
		   $errorstring .= '- Le format du prix public est incorrect<br/>';
		}

		if ($isSupplier) // fournisseur
		{

			if(trim($refSupplier) == '')
			{
				$error = true;
				$errorstring .= '- La référence fournisseur n\'a pas été saisie<br/>';
			}

			if(!preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', $price2)) // vérif validité du prix fournisseur
			{
				$error = true;
				$errorstring .= '- Le format du prix fournisseur est incorrect<br/>';
			}

			if(!preg_match('/^[1-9]{1}[0-9]*$/', $unite)) // vérif validité unité
			{
				$error = true;
				$errorstring .= '- Le format de l\'unité est incorrect<br/>';
			}

			if ($prixPublic == 0) // si type de prix fournisseur
			{
				if(!preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', $marge)) // vérif validité de la marge
				{
					$error = true;
					$errorstring .= '- Le format du pourcentage de marge est incorrect<br/>';
				}
				$margeRemise = $marge;
			}
			elseif ($prixPublic == 1) // si type de prix public
			{
				if(!preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', $remise) || $remise > 100) // vérif validité de la remise
				{
					$error = true;
					$errorstring .= '- Le format du pourcentage de remise est incorrect<br/>';
				}
				$margeRemise = $remise;
			}
			else
			{
				$error = true;
				$errorstring .= '- Impossible de déterminer si c\'est une marge ou remise qui doit être appliquée, car le type de prix par défaut du fournisseur est invalide<br/>';
			}

			if (!existTVA($handle, $idTVA))
			{
				$error = true;
				$errorstring .= '- Le taux de TVA n\'existe pas<br/>';
			}

		}
		else
		{
			$margeRemise = 0;
		}
    }
	else if($typeprice == 4)
	{
		// Créer le code_ref à partir des données import
		if(is_uploaded_file($_FILES['csv_ref']['tmp_name']))
		{
			if(substr($_FILES['csv_ref']['name'], -3) == 'csv')
			{

				$import_tab = file($_FILES['csv_ref']['tmp_name']);

				if(is_array($import_tab) && count($import_tab) > 0)
				{

					// Protéger ; dans les noms de colonnes ( = entre guillemets) après isolement des vrais caractères guillements
					$import_tab[0] = str_replace('""', '[guillemet]', $import_tab[0]);
					$import_tab[0] = preg_replace('/"([^"]*);([^"]*)"/', '$1[pvirgule]$2', $import_tab[0]);
					$import_tab[0] = str_replace('"', '', $import_tab[0]);

					$import_cols = explode(';', trim($import_tab[0]));
					$code_ref = (count($import_cols) + 1) . '<=>';

					$code_ref .= 'Référence TC';
					for($i = 0; $i < count($import_cols); ++$i)
					{
						$code_ref .= '<->' . str_replace(array('<=>', '<_>', '<->', '[guillemet]', '[pvirgule]'), array('', '', '', '"', ';'), $import_cols[$i]);
					}

					for($i = 1; $i < count($import_tab); ++$i)
					{
						$code_ref .= '<_>';

						$import_tab[$i] = str_replace('""', '[guillemet]', $import_tab[$i]);
						$import_tab[$i] = preg_replace('/"([^"]*);([^"]*)"/', '$1[pvirgule]$2', $import_tab[$i]);
						$import_tab[$i] = str_replace('"', '', $import_tab[$i]);

						$import_line = explode(';', trim($import_tab[$i]));

						//$code_ref .= ''; --> champ vide pour générer un nouveau identifiant TC
						for($j = 0; $j < count($import_line); ++$j)
						{
							$code_ref .= '<->' . str_replace(array('<=>', '<_>', '<->', '[guillemet]', '[pvirgule]'), array('', '', '', '"', ';'), $import_line[$j]);
						}

					}

				}
				else
				{
					$errorstring .= '- Fichier d\'import vide<br/>';
          $code_ref = '9<=>Référence TC<->Libellé<->Référence Fournisseur<->Unité<->Taux TVA<->Prix Fournisseur<->Marge<->Prix Public<->Éco Taxe';
				}

			}
			else
			{
				$errorstring .= '- Format du fichier d\'import incorrect<br/>';
        $code_ref = '9<=>Référence TC<->Libellé<->Référence Fournisseur<->Unité<->Taux TVA<->Prix Fournisseur<->Marge<->Prix Public<->Éco Taxe';
			}

			unlink($_FILES['csv_ref']['tmp_name']);

		}

		if ($isSupplier) // tableau de références fournisseur
		{
			$code_ref_tab = explode('<=>', $code_ref);
			$colscount = $code_ref_tab[0];

			// Au moins 1 colonne sup
			if(count($code_ref_tab) != 2 || !preg_match('/^[0-9]+$/', $colscount) || $colscount < 9)
			{
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

				if($colscount > 8)
				{
          $code_ref_tab_cols[$colscount - 6] = 'Unité';
          $code_ref_tab_cols[$colscount - 5] = 'Taux TVA';
          $code_ref_tab_cols[$colscount - 4] = 'Prix Fournisseur';
          $code_ref_tab_cols[$colscount - 3] = 'Marge';
          $code_ref_tab_cols[$colscount - 2] = 'Prix Public';
          $code_ref_tab_cols[$colscount - 1] = 'Éco Taxe';
				}
				else
				{
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
			if($colscount > 9)
			{
			    for($i = 3; $i < $colscount-6; ++$i)
				{
				    if(trim($code_ref_tab_cols[$i]) == '')
					{
						$error = true;
						$errorstring .= '- Libellé colonne ' . ($i + 1) . ' du tableau de références non saisi<br/>';
					}
				}
			}

			// Vérifier si au moins 1 ligne
			if(count($code_ref_tab_next) == 1)
			{
			    $error = true;
				$errorstring .= '- Le tableau de références doit comporter au moins 1 ligne<br/>';
			}

			$some_lines = false;

			// Vérifier nombre de colonnes dans chaque ligne (annuler celles qui correspondent pas)
			$label_lines = 0;

			$code_ref_lines = array();
			for($i = 1; $i < count($code_ref_tab_next); ++$i)
			{
			    $line_data = & explode('<->', $code_ref_tab_next[$i]);

				$line_colscount = count($line_data);

				if($line_colscount != $colscount)
				{
				    $error = true;
					$errorstring .= ' - Ligne ' . $i . ' du fichier / tableau ignorée car son format est incorrect (les lignes suivantes sont automatiquement décallées, vous pouvez rajouter la ligne &agrave; la main)<br/>';
				}
				else
				{
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
				    if($code_ref_line['id'] != '' && !preg_match('/^[0-9]+$/', $code_ref_line['id']))
				    {
				        $error = true;
					    $errorstring .= '- La ligne ' . $label_lines . ' du tableau de références ne possède pas de numéro de référence Techni-Contact valide. Veuillez prévenir votre webmaster si cette erreur survient à nouveau<br/>';
				    }

				    // Vérif si libellé
				    if(trim($code_ref_line['label']) == '')
				    {
				        $error = true;
					    $errorstring .= '- La ligne ' . $label_lines . ' du tableau de références ne possède pas de libellé<br/>';
				    }

					// Vérif si référence fournisseur
					if(trim($code_ref_line['refSupplier']) == '')
				    {
				        $error = true;
					    $errorstring .= '- La ligne ' . $label_lines . ' du tableau de références ne possède pas de référence fournisseur<br/>';
				    }

					// Test de validité des unité, taux de TVA, prix fournisseur, marge/remise, prix public et écotaxe s'ils sont saisis

					if(!preg_match('/^[1-9]{1}[0-9]*$/',  trim($code_ref_line['unite']))) // vérif validité unité
					{
						$error = true;
						$errorstring .= '- L\'unité de la référence de la ligne ' . $label_lines . ' du tableau de références est incorrect<br/>';
					}

					if (!existTVA($handle, trim($code_ref_line['idTVA'])))
					{
						$error = true;
						$errorstring .= '- Le taux de TVA de la référence de la ligne ' . $label_lines . ' du tableau de références n\'existe pas, laissez le champ vide si vous souhaitez avoir le taux de TVA par défaut du fournisseur<br/>';
					}

					if (trim($code_ref_line['price2']) != '' && !preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', trim($code_ref_line['price2'])))
					{
						$error = true;
						$errorstring .= '- Le prix fournisseur de la référence de la ligne ' . $label_lines . ' du tableau de références est incorrect, laissez le champ vide si vous souhaitez conserver celui par défaut<br/>';
					}

					if (trim($code_ref_line['marge']) != '' && !preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', trim($code_ref_line['marge'])))
					{
						$error = true;
						$errorstring .= '- La marge ou remise de la référence de la ligne ' . $label_lines . ' du tableau de références est incorrecte, laissez le champ vide si vous ne souhaitez utiliser celle par défaut<br/>';
					}

					if (trim($code_ref_line['price']) != '' && !preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', trim($code_ref_line['price'])))
					{
						$error = true;
						$errorstring .= '- Le prix public de la référence de la ligne ' . $label_lines . ' du tableau de références est incorrect, laissez le champ vide si vous souhaitez conserver celui par défaut<br/>';
					}

          if (trim($code_ref_line['ecotax']) != '' && !preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', trim($code_ref_line['ecotax'])))
          {
            $error = true;
            $errorstring .= '- L\'éco taxe de la référence de la ligne ' . $label_lines . ' du tableau de références est incorrect, laissez le champ vide s\'il n\'y a pas d\'éco taxe pour cette référence<br/>';
          }

					if (trim($code_ref_line['price2']) == '' && trim($code_ref_line['price']) == '')
					{
						$error = true;
						$errorstring .= '- Les prix fournisseur et public de la référence de la ligne ' . $label_lines . ' du tableau de références n\'ont pas été saisis, il n\'est donc pas possible de valider la ligne via calcul en fonction du taux de marge ou remise par défaut du fournisseur<br/>';
					}

					$code_ref_lines[] = $code_ref_line;
				}
			}

			// Vérifier si au moins 1 ligne
			if(!$some_lines)
			{
			    $error = true;
				$errorstring .= '- Le tableau de références doit comporter au moins 1 ligne valide<br/>';
			}

		}
		else // tableau de références annonceur
		{
			$code_ref_tab = explode('<=>', $code_ref);
			$colscount = $code_ref_tab[0];

			// Au moins 1 colonne sup
			if(count($code_ref_tab) != 2 || !preg_match('/^[0-9]+$/', $colscount) || $colscount < 3)
			{
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
				$code_ref_tab_cols[$colscount - 1] != 'Prix')
			{
				$error = true;

				$errorstring .= ' - Nombre de colonnes erroné / modification libellé ou prix interdite<br/>';
			}

			// Nom des autres colonnes
			if($colscount > 3)
			{
				for($i = 2; $i < $colscount - 1; ++$i)
				{
					if(trim($code_ref_tab_cols[$i]) == '')
					{
						$error = true;
						$errorstring .= '- Libellé colonne ' . ($i + 1) . ' du tableau de références non saisi<br/>';
					}
				}
			}


			// Vérifier si au moins 1 ligne
			if(count($code_ref_tab_next) == 1)
			{
				$error = true;
				$errorstring .= '- Le tableau de références doit comporter au moins 1 ligne<br/>';
			}


			$some_lines = false;

			// Vérifier nombre de colonnes dans chaque ligne (annuler celles qui correspondent pas)
			$label_lines = 0;

			for($i = 1; $i < count($code_ref_tab_next); ++$i)
			{
			    $line_data = explode('<->', $code_ref_tab_next[$i]);

				$line_colscount = count($line_data);

				if($line_colscount != $colscount)
				{
					$error = true;
					$errorstring .= ' - Ligne ' . $i . ' du fichier ignorée car son format est incorrect (les lignes suivantes sont automatiquement décallées, vous pouvez rajouter la ligne &agrave; la main)<br/>';
				}
				else
				{

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
				    if($code_ref_line['id'] != '' && !preg_match('/^[0-9]+$/', $code_ref_line['id']))
				    {
				        $error = true;
					    $errorstring .= '- La ligne ' . $label_lines . ' du tableau de références ne possède pas de numéro de référence Techni-Contact valide. Veuillez prévenir votre webmaster si cette erreur survient à nouveau<br/>';
				    }

					// Vérif si libellé
					if(trim($code_ref_line['label']) == '')
					{
						$error = true;
						$errorstring .= '- La ligne ' . $label_lines . ' du tableau de références ne possède pas de libellé<br/>';
					}

					// Vérifier si au moins 1 donnée en + de l'identifiant TC et du libellée est présente
					$line_ok = false;

					$line_content = explode('<->', $code_ref_line['content']);
					for($j = 0; $j < count($line_content); $j++)
					{
						if(trim($line_content[$j]) != '')
						{
							$line_ok = true;
							break;
						}
				    }
					if(!$line_ok)
					{
						$error = true;
						$errorstring .= '- La ligne ' . $label_lines . ' du tableau de références ne possède aucune donnée caractérisant le libellé et le prix<br/>';
					}

					if(trim($code_ref_line['price']) != '' && !preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', trim($code_ref_line['price'])))
					{
						$error = true;
						$errorstring .= '- Le prix de la référence de la ligne ' . $label_lines . ' du tableau de références est incorrect, laissez le champ vide si vous ne souhaitez pas préciser de prix<br/>';
					}

					$code_ref_lines[] = $code_ref_line;
				}
			}
			// Vérifier si au moins 1 ligne
			if(!$some_lines)
			{
				$error = true;
				$errorstring .= '- Le tableau de références doit comporter au moins 1 ligne<br/>';
			}

			// s'il y a des erreurs, on adapte le nombre de colonne à un format fournisseur pour la suite du script
			if ($error)
			{
				$code_ref_tab_cols2[0] = 'Référence TC';
				$code_ref_tab_cols2[1] = 'Libellé';
				$code_ref_tab_cols2[2] = 'Référence Fournisseur';

				$colscount += 5;

				if($colscount > 9)
				{
					for ($i = 2; $i < $colscount-7; $i++)
						$code_ref_tab_cols2[$i+1] = $code_ref_tab_cols[$i];

					$code_ref_tab_cols2[$colscount - 6] = 'Unité';
					$code_ref_tab_cols2[$colscount - 5] = 'Taux TVA';
					$code_ref_tab_cols2[$colscount - 4] = 'Prix Fournisseur';
					$code_ref_tab_cols2[$colscount - 3] = 'Marge';
					$code_ref_tab_cols2[$colscount - 2] = 'Prix Public';
          $code_ref_tab_cols2[$colscount - 1] = 'Éco Taxe';
				}
				else
				{
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



    if(!$error)
    {
      switch($typeprice)
      {
        case 1 : $price = 'sur demande';    $refSupplier = $price2 = ''; $margeRemise = '-1'; $idTVA = '0'; $delai_livraison = $contrainteProduit = ''; break;
        case 2 : $price = 'sur devis';      $refSupplier = $price2 = ''; $margeRemise = '-1'; $idTVA = '0'; $delai_livraison = $contrainteProduit = ''; break;
        case 3 : $price = 'nous contacter'; $refSupplier = $price2 = ''; $margeRemise = '-1'; $idTVA = '0'; $delai_livraison = $contrainteProduit = ''; break;
        case 4 : $price = 'ref';            $refSupplier = $price2 = ''; $margeRemise = '-1'; $idTVA = '0'; break;
        default : $typeprice = 0;
      }

        $save = ($user->rank != CONTRIB) ? 1 : 0;
        //$save = 0;
        $ok = addProduct($handle, $nom, $fastdesc, $advertiser, $familiesHidden, $alias, $keywords, $desc, $descd, $ean, $warranty, $title_tag, $meta_desc_tag, $shipping_fee, $video_code, $type1, $type2, $type3, $gen, $ind, $col, $productsHidden, $productsLinked, $refSupplier, $price, $price2, $unite, $margeRemise, $idTVA, $delai_livraison, $contrainteProduit, $asEstimate, $user->login, $save, '', '', $code_ref, $suppliers[$advertiser]);


    }
  }
}
else {
	$nom = $fastdesc = $alias = $keywords = $familiesHidden = $productsHidden = $productsLinked = $desc = $descd = $ean = $warranty = $title_tag = $meta_desc_tag = $shipping_fee = $video_code = "";
	$refSupplier = $price = $price2 = $unite = $marge = $remise = $idTVA = $delai_livraison = $contrainteProduit = "";
	$advertiser = $familiesList = 0;
	$typeprice = 1;
	$gen = $col = $ind = false;

	$familiesShown = '&nbsp; Actuellement aucune sous-famille associée.';
	$productsShown = ' Actuellement aucun produit lié.';
}



///////////////////////////////////////////////////////////////////////////


$filter = (isset($_GET['filter']) && $_GET['filter'] == '1') ? 1 : 0;
$liste  = array(10, 25, 50, 75);
$lmois  = array(3, 4, 5, 6, 7, 8, 9, 10, 11, 12);

if(isset($_GET['nb']) && in_array($_GET['nb'], $liste))
{
    $nb   = $_GET['nb'];
    $type = 1;
}
else if(isset($_GET['lettre']) && preg_match('/^[0a-z]$/', $_GET['lettre']))
{
    $lettre = $_GET['lettre'];
    $type   = 0;
}
else if(isset($_GET['month']) && in_array($_GET['month'], $lmois))
{
    $month = $_GET['month'];
    $type   = 2;
}
else
{
    $type = 1;
    $nb   = 10;
}

?><div class="titreStandard">Liste des produits</div><br/>
<div class="bg"><div align="center"><a href="index.php?nb=10&<?php print(session_name() . '=' . session_id() . '&filter=' . $filter) ?>">Récents</a> - <a href="index.php?lettre=0&<?php print(session_name() . '=' . session_id() . '&filter=' . $filter) ?>">0-9</a>
<?php


for($i = ord('a'); $i <= ord('z'); ++$i)
{
    print(' - <a href="index.php?lettre='.chr($i).'&' . session_name() . '=' . session_id() . '&filter=' . $filter . '">'.strtoupper(chr($i)).'</a> ');
}

print('</div><br/><br/>');

if($user->rank == COMM)
{
    if($type == 1)
    {
        $_url = 'nb=' . $nb;
    }

    else if($type == 2)
    {
        $_url = 'month=' . $month;
    }

    else
    {
        $_url = 'lettre=' . $lettre;
    }

    print('<a href="index.php?' . $_url . '&' . session_name() . '=' . session_id() . '&filter=');

    if($filter)
    {
        print('0">Afficher tous les produits');
    }
    else
    {
        print('1">Afficher uniquement les produits de vos annonceurs');
    }

    print('</a><br/><br/>');
}

?>
<br/><br/>Afficher les <select onChange="goTo('index.php?nb=' + this.options[this.selectedIndex].value + '&<?php print(session_name() . '=' . session_id() . '&filter=' . $filter) ?>')">
<?php

foreach($liste as $k => $v)
{

    $sel = ($nb == $v) ? ' selected' : '';
    print('<option value="' . $v . '"' . $sel . '>' . $v . '</option>');

}

?></select> derniers produits ajoutés ou mis à jour. <form method="get" action="index.php">Afficher les produits non mis à jour depuis  <select name="month">
<?php

foreach($lmois as $k => $v)
{

    $sel = ($month == $v) ? ' selected' : '';
    print('<option value="' . $v . '"' . $sel . '>' . $v . '</option>');

}

?></select> mois. <input type="hidden" name="filter" value="<?php print($filter) ?>"><input type="hidden" name="<?php print(session_name()) ?>" value="<?php print(session_id()) ?>"><input type="button" value="Go" onClick="this.form.submit(); this.disabled=true"></form><br/><br/><?php

if($type == 0)
{
    print('<b>Liste des produits dont le nom commence par ');
    if($lettre == '0')
    {
        $pattern = 'REGEXP(\'^[0-9]\')';
        print('un chiffre :</b><br/><br/>');
    }
    else
    {
        $pattern = 'like \'' . $lettre . '%\'';
        print('la lettre ' . strtoupper($lettre) . ' :</b><br/><br/>');
    }

    if($user->rank == COMM && $filter == 1)
    {
        $p = & displayGProducts($handle, 'and pfr.name '.$pattern.' group by pfr.id order by pfr.name', $user->id);
    }
    else
    {
        $p = & displayGProducts($handle, 'and pfr.name '.$pattern.' group by pfr.id order by pfr.name');
    }

}
else if($type == 2)
{
    print('<b>Liste des produits non mis à jour depuis ' . $month . ' mois : </b><br/><br/>');

    $line = time() - $month * 30 * 24 * 3600;

    if($user->rank == COMM && $filter == 1)
    {
        $p = & displayGProducts($handle, 'and p.timestamp <  ' . $line . ' group by p.id order by p.timestamp', $user->id);
    }
    else
    {
        $p = & displayGProducts($handle, 'and p.timestamp <  ' . $line . ' group by p.id order by p.timestamp');
    }

}
else
{
    print('<b>Liste des '.$nb.' derniers produits ajoutés ou mis à jour : </b><br/><br/>');

    if($user->rank == COMM && $filter == 1)
    {
        $p = & displayGProducts($handle, 'group by p.id order by p.timestamp desc limit ' . $nb, $user->id);
    }
    else
    {
        $p = & displayGProducts($handle, 'group by p.id order by p.timestamp desc limit ' . $nb);
    }
}

if(count($p) > 0)
{
    print('<ul>');

    foreach($p as $k => $v)
    {
         $product = & loadProduct($handle, $v[0]);
         $partner = & loadAdvertiser($handle, $product[1]);
         $pdt["pic_url"] = is_file(PRODUCTS_IMAGE_INC."thumb_big/".$v[0]."-1".".jpg") ? PRODUCTS_IMAGE_SECURE_URL."thumb_big/".$v[0]."-1".".jpg" : PRODUCTS_IMAGE_SECURE_URL."no-pic-thumb_big.gif";

         $extra = $product[3] . '<br />';
         $extra .= '<a href="' . ADMIN_URL . 'advertisers/edit.php?id=' . $product[1] . '&' . session_name() . '=' . session_id() . '">' . to_entities($partner[2]) . '</a>';

         print('<li style="clear: both; margin-top: 5px;"> <a href="' . URL . 'produits/' . $v[2] . '-' . $v[0] . '-' . $v[3] . '.html" target="_blank"><img style="margin-top: -5px; height: 40px; width: 55px; margin-right: 5px; float: left; overflow: hidden;" src="' . $pdt["pic_url"] . '" border="0"></a> <a href="edit.php?id=' . $v[0] . '&' . session_name() . '=' . session_id() . '">' . to_entities($v[1]) . '</a> <br />' . $extra . '<br />' );
    }


    print('</ul>');
}



?></div>
<br/><br/><div class="titreStandard">Ajouter un nouveau produit</div><br/>
<div class="bg"><?php

$next = true;

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if(!$error)
    {
        if($ok)
        {
            if($user->rank != CONTRIB)
            {
                $out = 'Produit créé avec succès.';
            }
            else
            {
                $out = 'Produit enregistré avec succès. Il sera en ligne dès qu\'il sera validé par un commercial.';
            }
        }
        else
        {
            $out = 'Erreur lors de la création du produit.<br/>'.$errorstring;
        }

        print('<div class="confirm">' . $out . '</div><br/><br/>');

        $next = false;

    }
    else
    {
        print('<font color="red">Une ou plusieurs erreurs sont survenues lors de la validation : <br/>' . $errorstring  . '</font><br/><br/>');
        $next = true;
    }


}

if($next) {
?>
<script type="text/javascript">
<!--

// Variables produits
var productFamilies   = [];
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
var p_as_estimate = 0;
var a_as_estimate = 0;

var idTVAdftDft       = <?php echo idTVAdftDft ?>;

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

        testAndWrite('productsLinked', productsShown + '<input type="hidden" name="productsHidden" value="'+productsHidden+'">');
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

    testAndWrite('productsLinked', productsShown + '<input type="hidden" name="productsHidden" value="'+productsHidden+'">');


}


function refreshPrice(val) {
	if (isSupplier) { // cas d'un fournisseur
		if(val == 0) { // saisi prix
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
        '</table>' +
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
        '</table>' +
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
        // '<a class="btn ui-state-default ui-corner-all" href="#" onclick="$(\'#cat3_attributes_dialog\').dialog(\'open\'); return false;">Voir tous les attributs de la famille</a>';
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
        // '<a class="btn ui-state-default ui-corner-all" href="#" onclick="$(\'#cat3_attributes_dialog\').dialog(\'open\'); return false;">Voir tous les attributs de la famille</a>';
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
		'<select name="typeprice" onChange="refreshPrice(this.value)">' +
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

<form name="addProduct" method="post" action="index.php?<?php print(session_name() . '=' . session_id()) ?>" class="formulaire" enctype="multipart/form-data">
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
 <tr><td class="intitule">Liste des alias :</td><td class="intitule"><input type="text" class="champstexte" name="alias" size="40" maxlength="255" value="<?php print(to_entities($alias)) ?>"> - Séparez chaque alias par le caractère | (AltGR + 6)</td></tr>
 <tr><td class="intitule">Liste des mots-clés :</td><td class="intitule"><input type="text" class="champstexte" name="keywords" size="40" maxlength="255" value="<?php print(to_entities($keywords)) ?>"> - Séparez chaque mot-clé par le caractère | (AltGR + 6)</td></tr>
 <tr><td class="intitule" colspan="2"><i>Note : Les mots clés sont ceux utilisés par le moteur de recherche interne</i></td></tr>
</table>
<br/>
<table>
 <tr><td class="intitule">Code EAN :</td><td class="intitule"><input type="text" class="champstexte" name="ean" size="40" maxlength="255" value="<?php print(to_entities($ean)) ?>"></td></tr>
 <tr><td class="intitule">Garantie :</td><td class="intitule"><input type="text" class="champstexte" name="warranty" size="40" maxlength="255" value="<?php print(to_entities($warranty)) ?>"></td></tr>
</table>

<br/>
<table>
 <tr><td class="intitule"><i>SEO</i> title :</td><td class="intitule"><input type="text" class="champstexte" name="title_tag" size="40" maxlength="255" value="<?php print(to_entities($title_tag)) ?>"></td></tr>
 <tr><td class="intitule"><i>SEO</i> meta desc :</td><td class="intitule"><input type="text" class="champstexte" name="meta_desc_tag" size="40" maxlength="255" value="<?php print(to_entities($meta_desc_tag)) ?>"></td></tr>
</table>

<br/><br/>Description du produit : *
<textarea name="desc"></textarea>
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
<textarea name="descd"></textarea>
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
    pFCKeditor.Create();*/

//-->
</script>
<br/><br/>

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
Sélectionnez les produits que vous souhaitez éventuellement lier avec ce produit. L'annonceur de ce produit sera alors
<br/>prévenu par email à chaque demande de contact auprès des annonceurs des produits qui lui sont liés :
<br/><br/>
<select name="padv" onChange="displayAdvertiserProducts(this.value)">
 <option value="">Sélectionnez un annonceur</option>
 <option value=""></option>
<?php
foreach($advertisers as $k => $v)
{
    print(' <option value="' . $k . '">' . to_entities($v) . '</option>' . "\n");
}
?>
</select>
<br/><br/>
<div id="padvDSP"><br/></div>
<br/>
<div id="productsLinked"> Actuellement aucun produit lié.</div>
<br/>
<hr width="50%" align="center">
<script> testAndWrite('productsLinked', productsShown + '<input type="hidden" name="productsHidden" value="'+productsHidden+'">'); </script>
<br/><br/><br/>
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

<div id="modPrix" class="intitule"></div>

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

<div id="cat3_attributes_dialog" title="Attributs de la famille"></div>

<script type="text/javascript">
// declare the dialog
$("#cat3_attributes_dialog").dialog({ width: 400, autoOpen: false });

// if an attribute is selected, add a new column only if the header doesn't already exist using the same rules than the addRefColumn function
// if an attribute value is selected, just add it in the last active cell
$("table.cat3-alt tbody div.icon-add").live("click", function(){
  var val = $(this).parent().find("span").text();
  if ($(this).data("type") == "attr") {
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
  else {
    if (last_active_td_x >= fixedColsLeft && last_active_td_x < refCols.length-fixedColsRight && last_active_td_y > 0) {
      var $input = $("#references table tr:eq("+last_active_td_y+") > td:eq("+last_active_td_x+") > input");
      $input.val(val);
      saveCellContent($input.get(0),last_active_td_x,last_active_td_y-1);
    }
  }
});

// tree level functions
var tri, trs;
var createTreeLevel = function(){
  tri = 0;
  trs = $("#cat3_attributes_dialog tbody tr").get();
  $(trs).filter("[class='attr']:odd").addClass("odd");

  while (tri < trs.length) {
    if ($(trs[tri]).hasClass("scat1"))
      createTreeLevelRecursive(1);
    else
      tri++;
  }
}

var createTreeLevelRecursive = function(dn) {
  // Adding | and + pics
  var $td = $(trs[tri]).find("td:first");
  for (var i=1; i<dn; i++)
    $td.append("<div class=\"more\"></div>");
  var div_folder = document.createElement("div");
  div_folder.className = "add";
  $td.append(div_folder);

  tri++;
  var trs_cat = [];
  var trs_start = tri;
  while(tri < trs.length) {
    if ($(trs[tri]).hasClass("selem"+dn)) {
      for (var i=1; i<=dn; i++)
        $(trs[tri]).find("td:first").append("<div class=\"more\"></div>");
      trs_cat.push(trs[tri]);
      tri++;
    }
    else if ($(trs[tri]).hasClass("scat"+(dn+1))) {
      trs_cat.push(trs[tri]);
      createTreeLevelRecursive(dn+1);
    }
    else {
      break;
    }
  }
  var trs_over = trs.slice(trs_start, tri);
  $(trs_cat).filter(":odd").addClass("odd");

  $(div_folder).click(function(){
    if ($(div_folder).hasClass("add")) {
      $(trs_cat).show();
      $(trs_cat).find("td:first div.sub").click().click();
    }
    else
      $(trs_over).hide();

    $(div_folder).toggleClass("add").toggleClass("sub");
    return false;
  });
};
</script>

<br/>
<center><input type="button" class="bouton" value="Valider" name="ok" onClick="if(typePrice == 4 && this.form.csv_ref.value == ''){ createRefCode();  } this.form.submit(); this.disabled = true"> &nbsp; <input type="reset" name="nok" value="Annuler" class="bouton"></center>
</form>
<br/>
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

    productFamilies = familiesHidden.substring(0, familiesHidden.length-1).split(',');
    var params = reduceObjectToString({
  		"familyId": productFamilies[0],
  		"_": Date.now()
  	}, "&", "=");
    $("#product-attributes").attr("src", "attributes/index.php?"+params);
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

</script>
<?php

}  // fin next


print('</div>');

require(ADMIN . 'tail.php');

?>
