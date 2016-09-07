<?php

/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 13 février 2006

 Fichier : /secure/manager/families/FamiliesSearch.php
 Description : Fichier interface de recherche des familles AJAX

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN."logs.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

if (version_compare(PHP_VERSION,'5','>=')) require_once('domxml-php4-to-php5.php');

if(!$user->login())
{
	header('Location: ' . ADMIN_URL . 'login.html');
	exit();
}

if(empty ($_GET['typeImport']) || !($_GET['typeImport'] & __IMPORT_TYPE__)) {
        header('Location: ' . ADMIN_URL . 'login.html');
	exit();
}

switch ( $_GET['typeImport'] ){
  case __IMPT_PDT__ :
    $refPage = 'imports.php';
    break;

  case  __IMPT_UPDT_SUPPLIER__ :
    $refPage = 'suppliers.php';
    break;

  default :
    header('Location: ' . ADMIN_URL . 'login.html');
    break;

}

$typeImport = $_GET['typeImport'];

if (!$user->get_permissions()->has("m-prod--sm-import","r")) {
  header("Location: ".$refPage."?error=permissions");
  exit();
}

if (!is_uploaded_file($_FILES['import_file']['tmp_name']))
{
	header("Location: ".$refPage."?error=file");
	exit();
}


$result = & $handle->query("select a.id, a.delai_livraison as delivery_time, a.prixPublic as PublicPrice, a.margeRemise as margin, a.arrondi as round, a.parent, tva.taux as VAT from advertisers a, tva where a.nom1 = '" . $handle->escape($_POST['import_advertiser']) . "' and a.idTVA = tva.id", __FILE__, __LINE__, false);
if ($handle->numrows($result, __FILE__, __LINE__) != 1)
{
	header('Location: '.$refPage.'?error=advertiser');
	exit();
}
$adv_infos = $handle->fetchAssoc($result);
$isSupplier = $adv_infos['parent'] == __ID_TECHNI_CONTACT__  || $adv_infos['id'] == __ID_TECHNI_CONTACT__ ? true : false;
$PublicPrice = $adv_infos['PublicPrice'] == 1 ? true : false;

require('_ClassImportUpdateSupplier.php');
require('_ClassImportProduct.php');
require('_ClassImport.php');

if (preg_match('/.xls$/', $_FILES['import_file']['name']))
{
/*	$idAdvertiser = 47830;
	$idAdvertiser = 55533;*/

	require_once 'Excel/reader.php';

	// ExcelFile($filename, $encoding);
	$data = new Spreadsheet_Excel_Reader();
	$data->setOutputEncoding('CP1252');
	$data->read($_FILES['import_file']['tmp_name']);

	error_reporting(E_ALL ^ E_NOTICE);

	$hi = array(); // Header Index
	for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++)
	{
		$header = mb_strtolower(Utils::toDashAz09(utf8_encode($data->sheets[0]['cells'][1][$j])));
    switch($header)
		{
			case "identifiant" :
			case "identifiant-produit" :
			case "numero-produit" :
			case "id-produit" : $hi['id'] = $j; break;

			case "identifiant-tc" :
			case "idtc" :
			case "identifiant-unique" : $hi['idTC'] = $j; break;

			case "nom-produit" : $hi['name'] = $j; break;

			case "description-rapide" :
			case "desc-rapide" : $hi['fastdesc'] = $j; break;

			case "famille" :
			case "categorie" : $hi['family_name'] = $j; break;

			case "description-produit" :
			case "desc-detaille" : $hi['descc'] = $j; break;

			case "description-technique" :
			case "desc-technique" : $hi['descd'] = $j; break;

			case "desc-html" :
			case "html" :
			case "description-html" : $hi['html'] = $j; break;

			case "delai-livraison" :
			case "delai-de-livraison" : $hi['delivery_time'] = $j; break;

			case "url-image" :
			case "image" : $hi['url_image'] = $j; break;

			case "url-docs" :
			case "docs" : $hi['url_docs'] = $j; break;

			case "alias" : $hi['alias'] = $j; break;

			case "mot-cle" :
			case "mot-cles" :
			case "mots-cle" :
			case "mots-cles" : $hi['keywords'] = $j; break;

			case "nombre-de-reference" :
			case "nombre-de-references" :
			case "nombre-references" :
			case "nombre-reference" : $hi['ref_count'] = $j; break;

			case "reference-fournisseur" :
			case "references-fournisseur" :
			case "ref-fournisseur" : $hi['ref_supplier'] = $j; break;

			case "libelle" : $hi['label'] = $j; break;

			case "prix" : $hi['price'] = $j; break;

			case "prix-barre" : $hi['price_deleted'] = $j; break;

			case "unite" : $hi['unit'] = $j; break;

			case "taux-tva" :
			case "tva" : $hi['VAT'] = $j; break;

			case "ordre" : $hi['order'] = $j; break;

			default : $hi['mixed_data_entitle'][$header] = $j; break;

			//case "famille" : $hi['family_name'] = $j; break;
		}
	}
	/*
Import type __IMPT_PDT__ :
Seules 5 colonnes sont obligatoires dans le fichier xls pour que l'import puisse être lu :
"Nom produit"
"Description rapide"
"Famille"
"Description produit"
"Description technique"

Import type __IMPT_UPDT_SUPPLIER__ :
"Prix"
"Référence fournisseur"

Les autres colonnes reconnues automatiquement sont :
"Identifiant unique" qui est l'identifiant TC du produit pour mise à jour
"Délai livraison"
"image"
"alias"
"mots clés"
"nombre référence"
"référence fournisseur"
"libellé"
"prix"
"unité"
"taux tva"
"ordre"

	*/

        $colError = false;

        switch ( $typeImport ){

          case __IMPT_PDT__ :
            if (!isset($hi['name']) ||
		!isset($hi['fastdesc']) ||
		!isset($hi['family_name']) ||
		!isset($hi['descc']) ||
		!isset($hi['descd']))
                {
                  $colError = true;
                }
            break;

            case __IMPT_UPDT_SUPPLIER__:
              if (!isset($hi['price']) ||
		!isset($hi['ref_supplier']))
                {
                  $colError = true;
                }
            break;


        }

  if ($colError) {
    header('Location: '.$refPage.'?error=colstitle');
    exit();
	}

	$imp = & new Import($handle, null);
	$imp->idAdvertiser = $adv_infos['id'];
	$imp->type = $typeImport;
	$imp->GenerateID();
        
	$is_row_read = array();
//	print_r($data->sheets[0]['cells']);
        switch ( $typeImport ){

          case __IMPT_PDT__ :
              for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++)
              {
                      if (isset($is_row_read[$i])) continue;
                      $is_row_read[$i] = true;

                      $name = trim(utf8_encode($data->sheets[0]['cells'][$i][$hi['name']]));
                      if (empty($name)) continue;

                      $ip = new ImportProduct($handle);
                      if (isset($hi['id'])) $ip->id_final = (int)$data->sheets[0]['cells'][$i][$hi['id']];
                      $ip->GenerateProductID();

                      $ip->id_import =			$imp->id;
                      $ip->name =					$name;
                      $ip->ref_name =				Utils::toDashAz09($ip->name);
                      $ip->fastdesc =				trim(utf8_encode($data->sheets[0]['cells'][$i][$hi['fastdesc']]));
                      $ip->family_name =			trim(utf8_encode($data->sheets[0]['cells'][$i][$hi['family_name']]));

                      $ip->descc =				utf8_encode($data->sheets[0]['cells'][$i][$hi['descc']]);
                      $ip->descd =				utf8_encode($data->sheets[0]['cells'][$i][$hi['descd']]);
                      $html =						isset($hi['html']) ? utf8_encode($data->sheets[0]['cells'][$i][$hi['html']]) : "";
                      if (empty($html))
                      {
                              $ip->descc = str_replace(array("\r\n", "\n", "\r"), "<br/>", trim($ip->descc));
                              $ip->descd = str_replace(array("\r\n", "\n", "\r"), "<br/>", trim($ip->descd));
                      }

                      $ip->delivery_time =		isset($hi['delivery_time']) ? trim(utf8_encode($data->sheets[0]['cells'][$i][$hi['delivery_time']])) : "";
                      if (empty($ip->delivery_time)) $ip->delivery_time = $adv_infos['delivery_time'];

                      $ip->url_image =			isset($hi['url_image']) ? trim(utf8_encode($data->sheets[0]['cells'][$i][$hi['url_image']])) : "";
                      $url_docs =					isset($hi['url_docs']) ? utf8_encode($data->sheets[0]['cells'][$i][$hi['url_docs']]) : "";
                      $url_docs = explode("\n", $url_docs);
                      foreach ($url_docs as $key => $doc) $url_docs[$key] = trim($doc);
                      $ip->url_docs = serialize($url_docs);

                      $ip->alias =				isset($hi['alias']) ? utf8_encode($data->sheets[0]['cells'][$i][$hi['alias']]) : "";
                      $ip->keywords =				isset($hi['keywords']) ? utf8_encode($data->sheets[0]['cells'][$i][$hi['keywords']]) : "";

                      $ip->ref_count =			isset($hi['ref_count']) ? (int)trim(utf8_encode($data->sheets[0]['cells'][$i][$hi['ref_count']])) : 0;
                      //print trim($data->sheets[0]['cells'][$i][$hi['ref_count']]) . "__";

                      $ip->mixed_data_entitle = array();

                      if ($ip->ref_count > 0)
                      {
                              $rf = 0; // Reference found
                              $row = $i;
                              $row_2_read_again = array(); // row to read again after getting the complete list of mixed columns
                              while ($rf < $ip->ref_count && $row <= $data->sheets[0]['numRows'])
                              {
                                      if (trim(utf8_encode($data->sheets[0]['cells'][$row][$hi['name']])) == $ip->name)
                                      {
                                              $is_row_read[$row] = true;		// this row has been parsed
                                              $row_2_read_again[$rf] = $row;	// this row will be read again
                                              $ip->references[$rf] = array();
                                              if ($isSupplier)
                                              {
                                                      /*print "\n<br/> ref_supplier = " . $data->sheets[0]['cells'][$row][$hi['ref_supplier']] .
                                                      "  |  type = " . $data->sheets[0]['cellsInfo'][$row][$hi['ref_supplier']]['type'] .
                                                      "  |  raw = " . $data->sheets[0]['cellsInfo'][$row][$hi['ref_supplier']]['raw'];*/
                                                      $ip->references[$rf]['id_final'] =		isset($hi['idTC']) ? utf8_encode($data->sheets[0]['cells'][$row][$hi['idTC']]) : "";
                                                      $ip->references[$rf]['ref_supplier'] =	isset($hi['ref_supplier']) ? utf8_encode($data->sheets[0]['cells'][$row][$hi['ref_supplier']]) : "";
                                                      $ip->references[$rf]['label'] =			isset($hi['label']) ? utf8_encode($data->sheets[0]['cells'][$row][$hi['label']]) : "";
                                                      $ip->references[$rf]['unit'] =			isset($hi['unit']) ? $data->sheets[0]['cells'][$row][$hi['unit']] : 1;
                                                      $ip->references[$rf]['VAT'] =			isset($hi['VAT']) ? $data->sheets[0]['cells'][$row][$hi['VAT']] : $adv_infos['VAT'];
                                                      $ip->references[$rf]['marge'] =			(float)($adv_infos['margin']);

                                                      $price = isset($hi['price']) ? (float)($data->sheets[0]['cells'][$row][$hi['price']]) : 0.0;
                                                      $ip->references[$rf]['price'] = $PublicPrice ? $price : $price / (1 - $ip->references[$rf]['marge'] / 100);
                                                      $ip->references[$rf]['price2'] = $PublicPrice ? $price * (1 - $ip->references[$rf]['marge'] / 100) : $price;

                                                      $ip->references[$rf]['price_deleted'] =	isset($hi['price_deleted']) ? $data->sheets[0]['cells'][$row][$hi['price_deleted']] : 0.0;
                                              }
                                              else
                                              {
                                                      $ip->references[$rf]['id_final'] =		isset($hi['idTC']) ? $data->sheets[0]['cells'][$row][$hi['idTC']] : "";
                                                      $ip->references[$rf]['label'] =			isset($hi['label']) ? utf8_encode($data->sheets[0]['cells'][$row][$hi['label']]) : "";
                                                      $ip->references[$rf]['price'] =			isset($hi['price']) ? (float)($data->sheets[0]['cells'][$row][$hi['price']]) : 0.0;
                                              }
                                              $ip->references[$rf]['order'] =				isset($hi['order']) ? $data->sheets[0]['cells'][$row][$hi['order']] : 0;

                                              // We read all the rows to get all the mixed columns to take into account for this specific product
                                              $col = 0;
                                              foreach($hi['mixed_data_entitle'] as $entitle => $colNum)
                                                      if (!empty($data->sheets[0]['cells'][$row][$colNum]) && !isset($ip->mixed_data_entitle[$entitle]))
                                                              $ip->mixed_data_entitle[$entitle] = $col++;

                                              $rf++;
                                      }
                                      $row++;
                              }

                              if ($rf > 0)
                              {
                                      $ip->price = "ref";
                                      $ip->ref_count = $rf;
                                      if ($isSupplier) $ip->online_sell = 1;

                                      $ip->mixed_data_entitle = array_flip($ip->mixed_data_entitle);	// {"weight" => 0, "Height" => 1 } --> {0 => "weight", 1 => "Height" }

                                      // We parse again all the rows to read again, to get all the mixed data from the mixed columns specific to this product
                                      foreach($row_2_read_again as $rf => $rowNum)
                                      {
                                              $ip->references[$rf]['mixed_data'] = array();
                                              foreach($ip->mixed_data_entitle as $entitle)
                                                      $ip->references[$rf]['mixed_data'][] = utf8_encode($data->sheets[0]['cells'][$rowNum][$hi['mixed_data_entitle'][$entitle]]);

                                              $ip->references[$rf]['mixed_data'] = serialize($ip->references[$rf]['mixed_data']);
                                      }
                              }
                      }
                      else
                      {
                              $ip->price = isset($hi['price']) ? $data->sheets[0]['cells'][$i][$hi['price']] : __DEFAULT_PRICE__;
                              if (empty($ip->price)) $ip->price = __DEFAULT_PRICE__;
                      }

                      $ip->mixed_data_entitle = serialize($ip->mixed_data_entitle);
                      $ip->UpdateStatus();
                      $ip->Save();
                  $imp->UpdateStatus();
                      //print print_r($ip, true) . "<br/><br/>\n\n";
              }
        break;

        case __IMPT_UPDT_SUPPLIER__ :
            for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++)
            {
                    if (isset($is_row_read[$i])) continue;
                    $is_row_read[$i] = true;
                    $ref_supplier = trim(utf8_encode($data->sheets[0]['cells'][$i][$hi['ref_supplier']]));
                    if (empty($ref_supplier)) continue;

                    $ip = new ImportUpdateSupplier($handle);

//                    if (isset($hi['id'])) $ip->id_final = (int)$data->sheets[0]['cells'][$i][$hi['id']];
                    $ip->GenerateProductID();
//                    var_dump($imp->idAdvertiser);
                    $ip->referenceExists($imp->idAdvertiser, $ref_supplier);

                    if($ip->referenceExist)
                            ++$imp->nbp_valid;
                    else
                            ++$imp->nbp_notvalid;
                    
                    $ip->id_import =			$imp->id;
           
                    $ip-> price  =			trim($data->sheets[0]['cells'][$i][$hi['price']]);
//                    $ip->former_price =			$imp->id;
//var_dump( 'p', $PublicPrice, 'p');
                    $ip->setFormerPrice($PublicPrice);
//var_dump($ip->nb_idTC);
                    
//                    $ip->status =			$imp->id;
                    
//                    $ip->name =					$name;
//                    $ip->ref_name =				Utils::toDashAz09($ip->name);
//                    $ip->fastdesc =				trim($data->sheets[0]['cells'][$i][$hi['fastdesc']]);
//                    $ip->family_name =			trim($data->sheets[0]['cells'][$i][$hi['family_name']]);

//                    $ip->descc =				$data->sheets[0]['cells'][$i][$hi['descc']];
//                    $ip->descd =				$data->sheets[0]['cells'][$i][$hi['descd']];
//                    $html =						isset($hi['html']) ? $data->sheets[0]['cells'][$i][$hi['html']] : "";
//                    if (empty($html))
//                    {
//                            $ip->descc = str_replace(array("\r\n", "\n", "\r"), "<br/>", trim($ip->descc));
//                            $ip->descd = str_replace(array("\r\n", "\n", "\r"), "<br/>", trim($ip->descd));
//                    }

//                    $ip->delivery_time =		isset($hi['delivery_time']) ? trim($data->sheets[0]['cells'][$i][$hi['delivery_time']]) : "";
//                    if (empty($ip->delivery_time)) $ip->delivery_time = $adv_infos['delivery_time'];
//
//                    $ip->url_image =			isset($hi['url_image']) ? trim($data->sheets[0]['cells'][$i][$hi['url_image']]) : "";
//                    $url_docs =					isset($hi['url_docs']) ? $data->sheets[0]['cells'][$i][$hi['url_docs']] : "";
//                    $url_docs = explode("\n", $url_docs);
//                    foreach ($url_docs as $key => $doc) $url_docs[$key] = trim($doc);
//                    $ip->url_docs = serialize($url_docs);
//
//                    $ip->alias =				isset($hi['alias']) ? $data->sheets[0]['cells'][$i][$hi['alias']] : "";
//                    $ip->keywords =				isset($hi['keywords']) ? $data->sheets[0]['cells'][$i][$hi['keywords']] : "";
//
//                    $ip->ref_count =			isset($hi['ref_count']) ? (int)trim($data->sheets[0]['cells'][$i][$hi['ref_count']]) : 0;
//                    //print trim($data->sheets[0]['cells'][$i][$hi['ref_count']]) . "__";
//
//                    $ip->mixed_data_entitle = array();

//                    if ($ip->ref_count > 0)
//                    {
//                            $rf = 0; // Reference found
//                            $row = $i;
//                            $row_2_read_again = array(); // row to read again after getting the complete list of mixed columns
//                            while ($rf < $ip->ref_count && $row <= $data->sheets[0]['numRows'])
//                            {
//                                    if (trim($data->sheets[0]['cells'][$row][$hi['$ref_supplier']]) == $ip->ref_supplier)
//                                    {
//                                            $is_row_read[$row] = true;		// this row has been parsed
//                                            $row_2_read_again[$rf] = $row;	// this row will be read again
//                                            $ip->references[$rf] = array();
//                                            if ($isSupplier)
//                                            {
//                                                    $ip->references[$rf]['id_final'] =		isset($hi['idTC']) ? $data->sheets[0]['cells'][$row][$hi['idTC']] : "";
//                                                    $ip->references[$rf]['reference'] =	isset($hi['ref_supplier']) ? $data->sheets[0]['cells'][$row][$hi['ref_supplier']] : "";
//
//                                                    $price = isset($hi['price']) ? (float)($data->sheets[0]['cells'][$row][$hi['price']]) : 0.0;
//                                                    $ip->references[$rf]['price'] = $PublicPrice ? $price : $price / (1 - $ip->references[$rf]['marge'] / 100);
//                                                    var_dump('supplier');
//                                            }
//                                            else
//                                            {
//                                              var_dump('not supplier');
//                                                    $ip->references[$rf]['id_final'] =		isset($hi['idTC']) ? $data->sheets[0]['cells'][$row][$hi['idTC']] : "";
////                                                    $ip->references[$rf]['label'] =			isset($hi['label']) ? $data->sheets[0]['cells'][$row][$hi['label']] : "";
//                                                    $ip->references[$rf]['price'] =			isset($hi['price']) ? (float)($data->sheets[0]['cells'][$row][$hi['price']]) : 0.0;
//                                                    $ip->references[$rf]['former_price'] =			isset($hi['former_price']) ? (float)($data->sheets[0]['cells'][$row][$hi['former_price']]) : 0.0;
//                                                    $ip->references[$rf]['nb_idTC'] =		isset($hi['nb_idTC']) ? $data->sheets[0]['cells'][$row][$hi['nb_idTC']] : "";
//                                                    $ip->references[$rf]['status'] =		isset($hi['status']) ? $data->sheets[0]['cells'][$row][$hi['status']] : "";
//                                            }
//
////                                            $ip->references[$rf]['order'] =				isset($hi['order']) ? $data->sheets[0]['cells'][$row][$hi['order']] : 0;
//
//                                            // We read all the rows to get all the mixed columns to take into account for this specific product
////                                            $col = 0;
////                                            foreach($hi['mixed_data_entitle'] as $entitle => $colNum)
////                                                    if (!empty($data->sheets[0]['cells'][$row][$colNum]) && !isset($ip->mixed_data_entitle[$entitle]))
////                                                            $ip->mixed_data_entitle[$entitle] = $col++;
//
//                                            $rf++;
//                                    }
//                                    $row++;
//                            }
//
//                            if ($rf > 0)
//                            {
//                                    $ip->price = "ref";
//                                    $ip->ref_count = $rf;
////                                    if ($isSupplier) $ip->online_sell = 1;
////
////                                    $ip->mixed_data_entitle = array_flip($ip->mixed_data_entitle);	// {"weight" => 0, "Height" => 1 } --> {0 => "weight", 1 => "Height" }
//
//                                    // We parse again all the rows to read again, to get all the mixed data from the mixed columns specific to this product
////                                    foreach($row_2_read_again as $rf => $rowNum)
////                                    {
////                                            $ip->references[$rf]['mixed_data'] = array();
////                                            foreach($ip->mixed_data_entitle as $entitle)
////                                                    $ip->references[$rf]['mixed_data'][] = $data->sheets[0]['cells'][$rowNum][$hi['mixed_data_entitle'][$entitle]];
////
////                                            $ip->references[$rf]['mixed_data'] = serialize($ip->references[$rf]['mixed_data']);
////                                    }
//                            }
//                    }
//                    else
//                    {
//                            $ip->price = isset($hi['price']) ? $data->sheets[0]['cells'][$i][$hi['price']] : __DEFAULT_PRICE__;
//                            if (empty($ip->price)) $ip->price = __DEFAULT_PRICE__;
//                    }

//                    $ip->mixed_data_entitle = serialize($ip->mixed_data_entitle);
                    $ip->UpdateStatus();
//                    var_dump($ip->status);
                    $ip->Save();
//                    echo '<br/>';
                $imp->UpdateStatusSupplier();
                    //print print_r($ip, true) . "<br/><br/>\n\n";
            }
            break;

        }
        
	$imp->Save();
//exit;
	header('Location: '.$refPage.'?id=' . $imp->id);
	exit();

}
elseif (preg_match('/.xml$/', $_FILES['import_file']['name']))
{
}
else
{
	header('Location: '.$refPage.'?error=fileext');
	exit();
}


?>
