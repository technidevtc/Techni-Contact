<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Fichier : /includes/managerV2/products.php
 Description : Fonction manipulation produits

/=================================================================*/

require_once(ADMIN.'generator.php');
require_once(ADMIN.'logo.php');
require_once(ADMIN.'actions.php');
require CONTROLLER.'manager/AttributeController.php';
require CONTROLLER.'manager/ProductAttributeController.php';
require CONTROLLER.'manager/ProductReferenceAttributeController.php';


function updateCat3Attributes($pdtId, $idTcList, $newCatIdList = array(), $oldCatIdList = array(), $newAttributes = array()) {

  // TODO if confirmed : if in the current main family a facet corresponding to an attribute is present, and some values are not included, add new values to this facet

  $attrCtrl = new AttributeController();
  $pdtAttrCtrl = new ProductAttributeController();
  $pdtRefAttrCtrl = new ProductReferenceAttributeController();

  // get current attribute list linked to this product
  $pdtAttrList = $pdtAttrCtrl->getList(['product_id' => $pdtId]);
  $lastPos = end($pdtAttrList)['position'];
  reset($pdtAttrList);
  if ($lastPos === false)
    $lastPos = 1;

  // -> delete all product_attribute <-> reference relations
  foreach ($idTcList as $idTc)
    $pdtRefAttrCtrl->deleteByReferenceId($idTc);

  // get all of the primary family's facets with their lines
  $facets = Doctrine_Query::create()
    ->select('fc.*, fcl.*')
    ->from('Facet fc')
    ->innerJoin('fc.lines fcl')
    ->where('fc.family_id = ?', $newCatIdList[0])
    ->fetchArray();

  foreach ($newAttributes as $i => $newAttr) {

    // remove useless spaces
    $newAttr['name'] = preg_replace('`\s+`', ' ', trim($newAttr['name']));

    // check if the product <-> attribute relation exists
    $pdtAttr = Utils::array_find(function($pa) use ($newAttr) {
      return strcasecmp($newAttr['name'], $pa['attribute']['name']) == 0;
    }, $pdtAttrList);

    // if attribute is not linked
    if ($pdtAttr === false) {
      // we search for an existing attribute with the same name (DB is case INSENSITIVE)
      $attr = $attrCtrl->getByName($newAttr['name']);

      // if the attribute does not exist, we create it
      if (!$attr)
        $attr = $attrCtrl->create(['name' => $newAttr['name']], true);

      // we add the product <-> attribute relation
      $pdtAttr = $pdtAttrCtrl->create([
        'product_id' => $pdtId,
        'attribute_id' => $attr['id'],
        'position' => ++$lastPos,
      ], true);
    }

    // get potential facet with corresponding attribute id
    $facet = Utils::array_find(function($facet) use ($pdtAttr) {
      return $facet['attribute_id'] == $pdtAttr['attribute_id'];
    }, $facets);

    // -> insert new product_attribute <-> reference relations
    foreach($newAttr['values'] as $i => $newAttrValue) {
      if ($newAttrValue !== '') {
        $pdtRefAttrCtrl->create([
          'product_reference_id' => $idTcList[$i],
          'product_attribute_id' => $pdtAttr['id'],
          'value' => $newAttrValue,
        ]);

        // if there's a corresponding facet, check if the value is contained in an interval, or present as a single value
        // if not, create it
        if ($facet !== false) {
          $refValue = Utils::toDashAz09($newAttrValue);
          $isNumeric = is_numeric($refValue);
          if ($isNumeric)
            $refValue = (float)$refValue;

          $foundFacetLine = Utils::array_find(function($facetLine) use ($refValue, $isNumeric) {
            return ($facetLine['type'] == FacetLine::TYPE_VALUE && $refValue == $facetLine['ref_value'])
                   || ($isNumeric && $facetLine['type'] == FacetLine::TYPE_INTERVAL && $refValue >= $facetLine['start'] && $refValue <= $facetLine['end']);

          }, $facet['lines']);

          if ($foundFacetLine === false) {
            $newFacetLine = new FacetLine();
            $newFacetLine->facet_id = $facet['id'];
            $newFacetLine->attribute_unit_id = $facet['attribute_unit_id'];
            $newFacetLine->type = FacetLine::TYPE_VALUE;
            $newFacetLine->value = $newAttrValue;
            $newFacetLine->active = 1;
            $newFacetLine->position = count($facet['lines']) + 1;

            $newFacetLine->save();

            // don't forget to add the new facet line to the already existing list to avoid duplicate
            $facet['lines'][] = $newFacetLine->toArray();
          }
        }
      }
    }
  }
}

/* Recherche
   i : réf handle connexion
   i : réf pattern recherché
   o : tableau résultats */
function & searchProduct(& $handle, $pattern)
{
    $ret = array();

    if($result = & $handle->query('select name from products_fr where '.$pattern.' and active = 1 group by name order by name',  __FILE__, __LINE__))
    {
        while($record = & $handle->fetch($result))
        {
            $ret[] = & $record[0];
        }

    }


    return $ret;

}


/* Retourner un tableau de produits
   i : référence handle connexion
   i : fin requete
   i : id utilisateur pour filtre optionnel
   o : référence tableau produits */
function displayGProducts($handle, $exp, $idUser = '')
{
  $ret = array();

    if($idUser == '')
    {
        $query = 'select p.id, pfr.name, pf.idFamily, pfr.ref_name, pfr.fastdesc from products_fr pfr, products p, products_families pf where pfr.deleted != 1 and p.id = pfr.id and pf.idProduct = p.id '.$exp;
    }
    else
    {
        $query = 'select p.id, pfr.name, pf.idFamily, pfr.ref_name, pfr.fastdesc from products_fr pfr, products p, products_families pf, advertisers a where a.idCommercial = \''.$handle->escape($idUser).'\' and a.id = p.idAdvertiser and pfr.deleted != 1 and p.id = pfr.id and p.id = pf.idProduct '.$exp;
    }

  if($result = $handle->query($query, __FILE__, __LINE__))
    {
        if($handle->numrows($result, __FILE__, __LINE__) == 0)
        {
            print('<center><b>Aucun résultat</b></center>');
        }
        else
        {
            while($record = $handle->fetch($result))
            {
              $ret[] = $record;
            }
        }
    }

    return $ret;
}



/* Créer un produit
   i : réf handle connexion
   i : réf nom produit
   i : réf desc rapide produit
   i : id annonceur
   i : réf liste familles
   i : réf liste alias
   i : réf liste mots clé
   i : réf description
   i : réf description détaillée
   i : type doc 1
   i : type doc 2
   i : type doc 3
   i : booléen CG
   i : booléen CI
   i : booléen CC
   i : réf liste produits liés
   i : réf référence fournisseur
   i : réf prix public
   i : réf prix fournisseur
   i : marge produit
   i : idTVA produit
   i : contrainteProduit
   i : nom utilisateur
   i : booléen ajoit final ou non
   i : ancien id optionnel (id article en ajout en attente de validation
   i : type ajout (optionnel)
   i : code références (optionnel)
   i : tableau d'infos sur le fournisseur éventuel
   o : true si ok, false si erreur */
function & addProduct(& $handle, & $name, & $fastdesc, $idAdvertiser, & $families, & $alias, & $keywords, & $desc, & $descd, $ean, $warranty, $title_tag, $meta_desc_tag, $shipping_fee, $video_code, $t1, $t2, $t3, $cg, $ci, $cc, & $products, $productsLinked, & $refSupplier, & $price, & $price2, $unite, $marge, $idTVA, $delai_livraison, $contrainteProduit, $asEstimate, $username, $save, $oldId = '', $type = '', $ref = '', & $supplierInfo)
{
  $ret = false;

  if($save) $idProduct = generateID(1, 16777215, 'id', 'products', $handle);
  else $idProduct = generateID(1, 16777215, 'id', 'products_add', $handle);
  $idTC = generateIDTC($handle);

  $cg = $cg ? 1 : 0;
  $ci = $ci ? 1 : 0;
  $cc = $cc ? 1 : 0;

  if($save)
  {
    $handle->query("insert into products_stats (`id`, `hits`, `orders`, `estimates`, `leads`, `first_hit_time`) values (".$idProduct.",0,0,0,0,0)", __FILE__, __LINE__);
    $handle->query("insert into products (id, idAdvertiser, idTC, timestamp, create_time, cg, ci, cc, refSupplier, price, price2, unite, marge, idTVA, contrainteProduit, as_estimate, ean, warranty, title_tag, meta_desc_tag, shipping_fee, video_code) " .
    "values('".$idProduct."', '".$handle->escape($idAdvertiser)."', '".$idTC."', '".time()."', '".time()."', '".$handle->escape($cg)."', '".$handle->escape($ci) .
    "', '".$handle->escape($cc)."', '".$handle->escape($refSupplier)."', '".$handle->escape($price)."', '".$handle->escape($price2)."', '".$handle->escape($unite) .
    "', '".$handle->escape($marge)."', '".$handle->escape($idTVA)."', '".$handle->escape($contrainteProduit)."', '".$handle->escape($asEstimate)."', '".$handle->escape($ean)."', '" .
    $handle->escape($warranty)."', '".$handle->escape($title_tag)."', '".$handle->escape($meta_desc_tag)."', '".$handle->escape($shipping_fee)."', '".$handle->escape($video_code)."')", __FILE__, __LINE__);

    $handle->query("insert into products_fr (id, idAdvertiser, name, fastdesc, ref_name, alias, keywords, descc, descd, delai_livraison, locked) " .
    "values('".$idProduct."', '".$handle->escape($idAdvertiser)."', '".$handle->escape($name)."', '".$handle->escape($fastdesc)."', '".$handle->escape(Utils::toDashAz09($name)).
    "', '".$handle->escape($keywords)."', '".$handle->escape($keywords)."', '".$handle->escape($desc)."', '".$handle->escape($descd)."', '".$handle->escape($delai_livraison)."', '0')", __FILE__, __LINE__);

    // Familles
    $familiesTab = explode(',', $families);
    $catList = array();
    for($i = 0; $i < count($familiesTab); ++$i)
    {
      if(preg_match('/^[0-9]+$/', $familiesTab[$i]))
      {
        $result = & $handle->query("select name from families_fr where id = '".$handle->escape($familiesTab[$i]). "'", __FILE__, __LINE__);
        if ($handle->numrows($result, __FILE__, __LINE__) == 1) {
          $handle->query("insert into products_families (idProduct, idFamily) values('".$idProduct."', '".$handle->escape($familiesTab[$i])."')");
          $catList[] = $familiesTab[$i];
        }
      }
    }

    // Produits liés lors d'un lead
    if(!empty($products))
    {
      $productsTab = explode(',', $products);
      for($i = 0; $i < count($productsTab); ++$i)
      {
        if(preg_match('/^[0-9]+$/', $productsTab[$i]))
        {
          $result = & $handle->query('select id from products where id = \''.$handle->escape($productsTab[$i]). '\'', __FILE__, __LINE__);
          if ($handle->numrows($result, __FILE__, __LINE__) == 1)
            $handle->query("insert into productslinks (idProduct, idProductLinked) values('".$idProduct."', '".$handle->escape($productsTab[$i])."')");
        }
      }
    }

    // Produits liés affichés sur fiche produit
    if (!empty($productsLinked)) {
      $productsLinkedIds = explode(',', $productsLinked);
      $linkPos = 1;
      foreach ($productsLinkedIds as $productLinkedId) {
        if (preg_match('/^[0-9]+$/', $productLinkedId)) {
          $linkId = generateID(1, 0xffffffff, 'id', 'products_linked', $handle);
          $handle->query("
            INSERT INTO products_linked (
              id,
              pdt_id,
              pdt_linked_id,
              position
            ) VALUES(
            '".$linkId."',
            '".$idProduct."',
            '".$productLinkedId."',
            '".$linkPos."'
            )"
          );
          $linkPos++;
        }
      }
    }

    // Références
    if($ref != '')
    {
      // Séparer nb colonnes du reste
      $nbCols_all_tab = explode('<=>', $ref);

      // Séparer liste colonnes et liste valeur chaque ligne
      $cols_lines_tab = explode('<_>', $nbCols_all_tab[1]);

      // Obtenir liste colonnes
      $elts = explode('<->', $cols_lines_tab[0]);
      $cols_content = array();

      for($i = 0; $i < $nbCols_all_tab[0]; ++$i)
        $cols_content[] = $elts[$i];

      $handle->query("insert into references_cols values('".$idProduct."', '".$handle->escape(serialize($cols_content))."')", __FILE__, __LINE__);

      $attributes = array();
      for ($i = 3; $i < $nbCols_all_tab[0] - 6; $i++) {
        $attributes[$i-3]["name"] = $elts[$i];
      }

      if (count($supplierInfo) >= 5 && $supplierInfo[0] != '')
      {
        $isSupplier     = true;
        $prixPublic     = $supplierInfo[1] == 1 ? true : false;
        $margeRemiseDft = $supplierInfos[2];
        //$idTVAdft       = $supplierInfos[4];
      }
      else $isSupplier = false;

      // list of final referrences ID's
      $idTcList = [];

      for($i = 1; $i < count($cols_lines_tab); ++$i)
      {
        $elts = explode('<->', $cols_lines_tab[$i]);
        $idTC = generateIDTC($handle);
        if($idTC)
        {
          $ref_content = array();
          if ($isSupplier)
          {
            $refSupplierRef = $elts[2];
            $uniteRef       = $elts[count($elts)-6];
            $idTVARef       = $elts[count($elts)-5];
            $price2Ref      = $elts[count($elts)-4];
            $margeRemiseRef = $elts[count($elts)-3];
            $priceRef       = $elts[count($elts)-2];
            $exoTax         = $elts[count($elts)-1];
            $price2RefOK = preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', $price2Ref);
            $priceRefOK  = preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', $priceRef);

            if (!preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', $margeRemiseRef)) $margeRemiseRef = $margeRemiseDft;
            if (!$prixPublic)
            {
              if ($price2RefOK) { if (!$priceRefOK) $priceRef = round($price2Ref * (100+$margeRemiseRef)/100, 2); }
              else { if ($priceRefOK) $price2Ref = round($priceRef * 100/(100+$margeRemiseRef), 2); }
            }
            else
            {
              if ($priceRefOK) { if (!$price2RefOK) $price2Ref = round($priceRef * (100-$margeRemiseRef)/100, 2); }
              else { if ($price2RefOK) $priceRef = round($price2Ref * 100/(100-$margeRemiseRef), 2); }
            }
            for ($j = 3; $j < count($elts) - 6; ++$j) {
              $ref_content[] = $elts[$j];
              $attributes[$j-3]["values"][] = $elts[$j];
            }
          }
          else
          {
            $refSupplierRef = $uniteRef = $idTVARef = $price2Ref = $margeRemiseRef = '';
            $priceRef = $elts[count($elts) - 1];
            for($j = 2; $j < count($elts) - 1; ++$j)
              $ref_content[] = $elts[$j];
          }
          // Insérer référence
          $handle->query("
            INSERT INTO `references_content`
              (`id`, `idProduct`, `sup_id`, `label`, `content`, `refSupplier`, `price`, `price2`, `unite`, `marge`, `idTVA`, `ecotax`, `classement`)
            VALUES (
              '".$idTC."',
              '".$idProduct."',
              '".$handle->escape($idAdvertiser)."',
              '".$handle->escape($elts[1])."',
              '".$handle->escape(serialize($ref_content))."',
              '".$handle->escape($refSupplierRef)."',
              '".$handle->escape($priceRef)."',
              '".$handle->escape($price2Ref)."',
              '".$handle->escape($uniteRef)."',
              '".$handle->escape($margeRemiseRef)."',
              '".$handle->escape($idTVARef)."',
              '".$handle->escape($ecoTax)."',
              ".$i."
            )", __FILE__, __LINE__);
        }
        $idTcList[] = $idTC;
      }
      // update attributes
      if ($isSupplier)
        updateCat3Attributes($id, $idTcList, $catList, null, $attributes);

      ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],  'Création de la fiche produit [ID : '.$idProduct.'] - '.$name.' - '.$fastdesc.' [ID Annonceur : '.$idAdvertiser.'] | CG : '.$cg.' -  CI : '.$ci.' -  CC : '.$cc.' | Nombre de références : '.(count($cols_lines_tab)-1).' | Contrainte quantité de produits : '.$contrainteProduit.' | Description : '.$desc.' - Détaillée : '.$descd.' | Code EAN : '.$ean.' | Garantie : '.$warranty.' | Balise Title : '.$title_tag.' | Balise Meta Desc : '.$meta_desc_tag.' | Frais de port : '.$shipping_fee.' | Alias : '.$alias.' - Mots clés : '.$keywords.' | Familles : '.$families.' | Produits liés : '.$products);

    }	// fin insert références
    else ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],  'Création de la fiche produit [ID : '.$idProduct.'] - '.$name.' - '.$fastdesc.' [ID Annonceur : '.$idAdvertiser.'] | CG : '.$cg.' -  CI : '.$ci.' -  CC : '.$cc.' | Référence Fournisseur : '.$refSupplier.' | Prix : '.$price.' - Prix fournisseur : '.$price2.' - Unité : '.$unite.' - Marge : '.$marge.' - idTVA : '.$idTVA.' - Contrainte quantité de produits : '.$contrainteProduit.' | Description : '.$desc.' - Détaillée : '.$descd.' | Code EAN : '.$ean.' | Garantie : '.$warranty.' | Balise Title : '.$title_tag.' | Balise Meta Desc : '.$meta_desc_tag.' | Frais de port : '.$shipping_fee.' | Alias : '.$alias.' - Mots clés : '.$keywords.' | Familles : '.$families.' | Produits liés : '.$products);

    notify($handle, 'Création de la fiche produit '.$name.' [ID : '.$idProduct.']', $username);
  }
  else
  {
    $handle->query("insert into products_add (id, idAdvertiser, idTC, name, fastdesc, families, alias, keywords, descc, descd, cg, ci, cc, products, refSupplier, price, price2, unite, " .
    "marge, idTVA, delai_livraison, contrainteProduit, as_estimate, ref, ean, warranty, title_tag, meta_desc_tag, shipping_fee, video_code) values('".$idProduct."', '".$handle->escape($idAdvertiser)."', '".$idTC."', '".$handle->escape($name) .
    "', '".$handle->escape($fastdesc)."', '".$handle->escape($families)."', '".$handle->escape($alias)."', '".$handle->escape($keywords)."', '".$handle->escape($desc) .
    "', '".$handle->escape($descd)."', '".$handle->escape($cg)."', '".$handle->escape($ci)."', '".$handle->escape($cc)."', '".$handle->escape($products)."', '" .$handle->escape($productsLinked)."', '" .
    $handle->escape($refSupplier)."', '".$handle->escape($price)."', '".$handle->escape($price2)."', '".$handle->escape($unite)."', '".$handle->escape($marge)."', '" .
    $handle->escape($idTVA)."', '".$handle->escape($delai_livraison)."', '".$handle->escape($contrainteProduit)."', '".$handle->escape($asEstimate)."', '".$handle->escape($ref)."', '".$handle->escape($ean) .
    "', '".$handle->escape($warranty)."', '".$handle->escape($title_tag)."', '".$handle->escape($meta_desc_tag)."', '".$handle->escape($shipping_fee)."', '".$handle->escape($video_code)."')", __FILE__, __LINE__);

    if($ref != '')
    {
      ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],  'Demande de création de la fiche produit '.$name.' - '.$fastdesc .
      ' [ID Annonceur : '.$idAdvertiser.'] | CG : '.$cg.' -  CI : '.$ci.' -  CC : '.$cc.' | Ce produit comporte des références | Contrainte quantité de produits : ' .$contrainteProduit.
      ' | Mise sous devis du produit : ' . $asEstimate.' | Description : '.$desc.' - Détaillée : '.$descd.' | Code EAN : '.$ean.' | Garantie : '.$warranty.
      ' | Balise Title : '.$title_tag.' | Balise Meta Desc : '.$meta_desc_tag.' | Frais de port : '.$shipping_fee .' | Alias : '.$alias.' - Mots clés : '.$keywords.
      ' | Familles : '.$families.' | Produits liés lead : '.$products.' | Produits liés fiche : '.$productsLinked.' | Vidéo : '.(empty($video_code)?"non":"oui"));
    }
    else
    {
      ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],  'Demande de création de la fiche produit '.$name.' - '.$fastdesc .
      ' [ID Annonceur : '.$idAdvertiser.'] | CG : '.$cg.' -  CI : '.$ci.' -  CC : '.$cc.' | Référence Fournisseur : '.$refSupplier.' | Prix : '.$price .
      ' | Prix fournisseur : '.$price2.' - Unité : '.$unite.' - Marge : '.$marge.' - idTVA : '.$idTVA.' - Contrainte quantité de produits : '.$contrainteProduit .' - Mise sous devis du produits : '.$asEstimate .
      ' | Description : '.$desc.' - Détaillée : '.$descd.' | Code EAN : '.$ean.' | Garantie : '.$warranty.' | Balise Title : '.$title_tag.' | Balise Meta Desc : '.$meta_desc_tag.
      ' | Frais de port : '.$shipping_fee .' | Alias : '.$alias.' - Mots clés : '.$keywords.' | Familles : '.$families.' | Produits liés lead : '.$products.' | Produits liés fiche: '.$productsLinked.' | Vidéo : '.(empty($video_code)?"non":"oui"));
    }
  }

  $ret = $idProduct;

  // Uniquement upload quand ajout classique (et non validation ajout user / annonceur)
  if($type == '') {
    uploadDoc('doc1', $idProduct.'-1', PRODUCTS_FILES_INC, $t1);
    uploadDoc('doc2', $idProduct.'-2', PRODUCTS_FILES_INC, $t2);
    uploadDoc('doc3', $idProduct.'-3', PRODUCTS_FILES_INC, $t3);
  }


  if(!empty($oldId)) {
    $rep_f = ($type == 'add_adv') ? PRODUCTS_FILES_ADV_INC : PRODUCTS_FILES_INC;

    // renommer les éventuels fichiers images et documents
    for($i = 1; $i <= 3; ++$i) {
      @rename($rep_f.$oldId.'-'.$i.'.doc', PRODUCTS_FILES_INC.$idProduct.'-'.$i.'.doc');
      @rename($rep_f.$oldId.'-'.$i.'.pdf', PRODUCTS_FILES_INC.$idProduct.'-'.$i.'.pdf');
    }

    // Deleting old images and moving the new ones
    $num = 1;
    while (is_file(PRODUCTS_IMAGE_ADV_INC."zoom/".$oldId."-".$num.".jpg")) {
      $oldFileName = $oldId."-".$num.".jpg";
      $newFileName = $idProduct."-".$num.".jpg";
      copy(PRODUCTS_IMAGE_ADV_INC."zoom/".$oldFileName, PRODUCTS_IMAGE_INC."zoom/".$newFileName);
      copy(PRODUCTS_IMAGE_ADV_INC."card/".$oldFileName, PRODUCTS_IMAGE_INC."card/".$newFileName);
      copy(PRODUCTS_IMAGE_ADV_INC."thumb_big/".$oldFileName, PRODUCTS_IMAGE_INC."thumb_big/".$newFileName);
      copy(PRODUCTS_IMAGE_ADV_INC."thumb_small/".$oldFileName, PRODUCTS_IMAGE_INC."thumb_small/".$newFileName);
      $num++;
    }
  }

  return $ret;
}


/* Uploader un document */
function uploadDoc($field, $name, $dir, $type) {
  if(is_uploaded_file($_FILES[$field]['tmp_name']))
    copy($_FILES[$field]['tmp_name'], $dir.$name.$type);
}

/* Afficher les produits en attente de validation */
function displayWait($handle, $type) {
  $ret = array();

  if($type != 'c' && $type != 'm') return $ret;

  if($result = & $handle->query('select id, name, fastdesc from products_add where type = \''.$handle->escape($type).'\' order by name', __FILE__, __LINE__))
    while($row = & $handle->fetch($result)) $ret[$row[0]] = array($row[1], $row[2]);

  return $ret;
}

/* Charger un produit */
function loadProduct($handle, $id, $action = '') {
  $ret = false;

  //                     0            1          2          3              4            5               6          7       8          9            10           11          12                13                14              15        16         17        18        19        20                  21                           22                23         24      25           26            27               28                    29                                30                            31
  if($action == 'add')
    $query = 'select p.name, p.idAdvertiser, a.nom1, p.fastdesc,       p.families,       p.alias, p.keywords, p.descc, p.descd,       p.cg,        p.ci,        p.cc,       p.products,       p.productsLinked, p.refSupplier,  p.price,  p.price2,  p.unite,  p.marge,  p.idTVA,  p.delai_livraison,  p.contrainteProduit, null AS ref_name, null AS idFamily,  p.ean,  p.warranty,  p.title_tag,  p.meta_desc_tag, p.shipping_fee,       p.video_code,                                  a.as_estimate AS a_as_estimate 						from products_add p, advertisers a where p.type = \'c\' and p.id = \''.$handle->escape($id).'\' and p.idAdvertiser = a.id';
  else if($action == 'add_adv')
    $query = 'select p.name, p.idAdvertiser, a.nom1, p.fastdesc,       p.families, null AS alias, p.keywords, p.descc, p.descd, null AS cg,  null AS ci,  null AS cc, null AS products,       p.productsLinked, p.refSupplier,  p.price,  p.price2,  p.unite,  p.marge,  p.idTVA,  p.delai_livraison,  p.contrainteProduit, null AS ref_name, null AS idFamily,  p.ean,  p.warranty,  p.title_tag,  p.meta_desc_tag, p.shipping_fee, null AS video_code,                                  a.as_estimate AS a_as_estimate 						from products_add_adv p, advertisers a where p.type = \'c\' and p.reject = 0 and p.id = \''.$handle->escape($id).'\' and p.idAdvertiser = a.id';
  else if($action == 'backup')
    $query = 'select p.name, p.idAdvertiser, a.nom1, p.fastdesc,       p.families,       p.alias, p.keywords, p.descc, p.descd,       p.cg,        p.ci,        p.cc,       p.products,       p.productsLinked, p.refSupplier,  p.price,  p.price2,  p.unite,  p.marge,  p.idTVA,  p.delai_livraison,  p.contrainteProduit, null AS ref_name, null AS idFamily,  p.ean,  p.warranty,  p.title_tag,  p.meta_desc_tag, p.shipping_fee,       p.video_code,                                  a.as_estimate AS a_as_estimate 						from products_add p, advertisers a where p.type = \'b\' and p.id = \''.$handle->escape($id).'\' and p.idAdvertiser = a.id';
  else if($action == 'edit')
    $query = 'select p.name, p.idAdvertiser, a.nom1, p.fastdesc,       p.families,       p.alias, p.keywords, p.descc, p.descd,       p.cg,        p.ci,        p.cc,       p.products,       p.productsLinked, p.refSupplier,  p.price,  p.price2,  p.unite,  p.marge,  p.idTVA,  p.delai_livraison,  p.contrainteProduit, null AS ref_name, null AS idFamily,  p.ean,  p.warranty,  p.title_tag,  p.meta_desc_tag, p.shipping_fee,       p.video_code,                                  a.as_estimate AS a_as_estimate 						from products_add p, advertisers a where p.type = \'m\' and p.id = \''.$handle->escape($id).'\' and p.idAdvertiser = a.id';
  else if($action == 'edit_adv')
	$query = 'select p.name, p.idAdvertiser, a.nom1, p.fastdesc,       p.families,      pf.alias, p.keywords, p.descc, p.descd,      pg.cg,       pg.ci,       pg.cc, null AS products, NULL AS productsLinked, p.refSupplier,  p.price,  p.price2,  p.unite,  p.marge,  p.idTVA,  p.delai_livraison,  p.contrainteProduit, null AS ref_name, null AS idFamily,  p.ean,  p.warranty,  p.title_tag,  p.meta_desc_tag, p.shipping_fee, p.video_code AS video_code,                                  a.as_estimate AS a_as_estimate,		pf.locked	 	from products_add_adv p, advertisers a, products_fr pf, products pg where p.type = \'m\' and p.id = \''.$handle->escape($id).'\' and p.idAdvertiser = a.id and p.id = pf.id and p.id = pg.id';
    //$query = 'select p.name, p.idAdvertiser, a.nom1, p.fastdesc,       p.families,      pf.alias, p.keywords, p.descc, p.descd,      pg.cg,       pg.ci,       pg.cc, null AS products, NULL AS productsLinked, p.refSupplier,  p.price,  p.price2,  p.unite,  p.marge,  p.idTVA,  p.delai_livraison,  p.contrainteProduit, null AS ref_name, null AS idFamily,  p.ean,  p.warranty,  p.title_tag,  p.meta_desc_tag, p.shipping_fee, null AS video_code,                                  a.as_estimate AS a_as_estimate,		pf.locked	 	from products_add_adv p, advertisers a, products_fr pf, products pg where p.type = \'m\' and p.id = \''.$handle->escape($id).'\' and p.idAdvertiser = a.id and p.id = pf.id and p.id = pg.id';
  else
    $query = 'select p.name, p.idAdvertiser, a.nom1, p.fastdesc, null AS families,       p.alias, p.keywords, p.descc, p.descd,      pg.cg,        pg.ci,      pg.cc, null AS products, NULL AS productsLinked, pg.refSupplier, pg.price, pg.price2, pg.unite, pg.marge, pg.idTVA,  p.delai_livraison, pg.contrainteProduit,       p.ref_name,      pf.idFamily, pg.ean, pg.warranty, pg.title_tag, pg.meta_desc_tag, pg.shipping_fee,      pg.video_code, pg.as_estimate AS p_as_estimate, a.as_estimate AS a_as_estimate,  	p.locked		from products_fr p, advertisers a, products pg, products_families pf where p.id = \''.$handle->escape($id).'\' and p.idAdvertiser = a.id and p.id = pg.id and pf.idProduct = p.id AND p.deleted != 1 limit 1';

  if($result = $handle->query($query, __FILE__, __LINE__)) {
    if($handle->numrows($result, __FILE__, __LINE__) == 1) {
      $ret = $handle->fetchArray($result);
      if($action != 'add' && $action != 'add_adv' && $action != 'backup' && $action != 'edit') {
        if($action != 'edit_adv' && ($result = & $handle->query('select idFamily from products_families where idProduct = \''.$handle->escape($id).'\' order by orderFamily asc', __FILE__, __LINE__))) {
          $ret[4] = $ret["families"] = "";
          while($row = & $handle->fetch($result)) {
            $ret[4] .= $row[0].",";
            $ret["families"] .= $row[0].",";
          }
        }

        // Générer la chaine des produits liés lead
        if($result = $handle->query('select idProductLinked from productslinks where idProduct = \''.$handle->escape($id).'\'', __FILE__, __LINE__)) {
          $ret[12] = $ret["products"] = "";
          while($row = & $handle->fetch($result)) {
            $ret[12] .= $row[0].",";
            $ret["products"] .= $row[0].",";
          }
        }

        // idem pour les produits liés fiche
        $result = $handle->query("SELECT pdt_linked_id FROM products_linked WHERE pdt_id = '".$handle->escape($id)."' ORDER BY position ASC", __FILE__, __LINE__);
        $ret[13] = array();
        while ($row = & $handle->fetch($result)) {
          $ret[13][] = $row[0];
        }
        $ret[13] = $ret['productsLinked'] = implode(',', $ret[13]);
      }
    }
  }
  return $ret;
}

/* supprimer un produit */
function delProduct($handle, $id, $name, $userId, $action = '', $motif = '') {

  if($action == 'add') {
    $handle->query("DELETE FROM products_add WHERE type = 'c' AND id = '".$handle->escape($id)."' limit 1");

    if($userId != '') {
      ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],  'Rejet de la fiche produit '.$name.' [ID : '.$id.']');
    }

    for($i=1; $i<=3; ++$i) {
      @unlink(PRODUCTS_FILES_INC.$id.'-'.$i.'.doc');
      @unlink(PRODUCTS_FILES_INC.$id.'-'.$i.'.pdf');
    }

    $num = 1;
    while (is_file(PRODUCTS_IMAGE_INC."zoom/".$id."-".$num.".jpg")) {
      $fileName = $id."-".$num.".jpg";
      unlink(PRODUCTS_IMAGE_INC."zoom/".$fileName);
      unlink(PRODUCTS_IMAGE_INC."card/".$fileName);
      unlink(PRODUCTS_IMAGE_INC."thumb_big/".$fileName);
      unlink(PRODUCTS_IMAGE_INC."thumb_small/".$fileName);
      $num++;
    }
  }
  elseif ($action == 'add_adv' || $action == 'edit_adv') {
    $letter = ($action == 'add_adv') ? 'c' : 'm';
    $type_t = ($action == 'add_adv') ? 'fiche' : 'modification de la fiche';

    if(!empty($motif)) {
      $handle->query("UPDATE products_add_adv SET reject = 1, timestamp = ".time()." WHERE type = '".$letter."' AND id = '".$handle->escape($id)."'", __FILE__, __LINE__);
      $handle->query("INSERT INTO rejects VALUES('".$handle->escape($id)."', '".$handle->escape($motif)."', '".time()."')", __FILE__, __LINE__);
    }
    else {
      $handle->query("DELETE FROM products_add_adv WHERE type = '".$letter."' AND id = '".$handle->escape($id)."' LIMIT 1", __FILE__, __LINE__);
    }
    //exit();

    if($userId != '') {
      ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],  'Rejet de la '.$type_t.' produit extranet '.$name.' [ID : '.$id.']');
    }

    for($i = 1; $i <= 3; ++$i) {
      @unlink(PRODUCTS_FILES_ADV_INC.$id.'-'.$i.'.doc');
      @unlink(PRODUCTS_FILES_ADV_INC.$id.'-'.$i.'.pdf');
    }

    // Deleting images
    $num = 1;
    while (is_file(PRODUCTS_IMAGE_ADV_INC."zoom/".$id."-".$num.".jpg")) {
      $fileName = $id."-".$num.".jpg";
      unlink(PRODUCTS_IMAGE_ADV_INC."zoom/".$fileName);
      unlink(PRODUCTS_IMAGE_ADV_INC."card/".$fileName);
      unlink(PRODUCTS_IMAGE_ADV_INC."thumb_big/".$fileName);
      unlink(PRODUCTS_IMAGE_ADV_INC."thumb_small/".$fileName);
      $num++;
    }
  }
  else if($action == 'edit') {
    $handle->query("DELETE FROM products_add WHERE type = 'm' AND id = '".$handle->escape($id)."' LIMIT 1");
    ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],  'Rejet de la modification sur la fiche produit '.$name.' [ID : '.$id.']');
  }
  else {
    $queries = array();
    if(hasDemands($handle, $id)) {
      // une ou plusieurs demandes, on désactive seulement
      //$queries[] = "UPDATE products_fr SET active = 0 WHERE id = '".$handle->escape($id)."' LIMIT 1";
      // pour une question d'homogénéité, on considère le produit supprimé meme s'il y a des demandes: OD 20/01/2012 - Création nouvel état de fiche - demande Tristan par tel 11h16
      $queries[] = "UPDATE products_fr SET deleted=1 WHERE id = '".$handle->escape($id)."'";
    }
    else {
      // Aucune demande on efface données + image + références
    //  $queries[] = "DELETE FROM products WHERE id = '".$handle->escape($id)."' LIMIT 1";
    //  $queries[] = "DELETE FROM products_fr WHERE id = '".$handle->escape($id)."' LIMIT 1";
    //  $queries[] = "DELETE FROM references_content WHERE idProduct = '".$handle->escape($id)."'";
    //  $queries[] = "DELETE FROM references_cols WHERE idProduct = '".$handle->escape($id)."'";
      $queries[] = "UPDATE products_fr SET deleted=1 WHERE id = '".$handle->escape($id)."'";

      // Deleting images
    //  $num = 1;
    //  while (is_file(PRODUCTS_IMAGE_INC."zoom/".$id."-".$num.".jpg")) {
    //    $fileName = $id."-".$num.".jpg";
    //    unlink(PRODUCTS_IMAGE_INC."zoom/".$fileName);
    //    unlink(PRODUCTS_IMAGE_INC."card/".$fileName);
    //    unlink(PRODUCTS_IMAGE_INC."thumb_big/".$fileName);
    //    unlink(PRODUCTS_IMAGE_INC."thumb_small/".$fileName);
    //    $num++;
    //  }
    }

    // récupération des données pour reporting log : OD 05/06/2011
    $res = $handle->query("SELECT idAdvertiser FROM products WHERE id = '".$handle->escape($id)."'", __FILE__, __LINE__);
    $adv = $handle->fetchAssoc($res);

    // A effacer dans tous les cas
  //  $queries[] = "DELETE FROM products_add WHERE id = '".$handle->escape($id)."' AND type != 'c'";
  //  $queries[] = "DELETE FROM products_families WHERE idProduct = '".$handle->escape($id)."'";
  //  $queries[] = "DELETE FROM productslinks WHERE idProduct = '".$handle->escape($id)."' OR idProductLinked = '".$handle->escape($id)."'";
  //  $queries[] = "DELETE FROM actions WHERE action LIKE '%de la fiche produit%' AND action LIKE '%[ID : ".$handle->escape($id)."]%'";
  //  $queries[] = "DELETE FROM stats_products WHERE id = '".$handle->escape($id)."'";
  //  $queries[] = "DELETE FROM sup_requests WHERE idProduct = '".$handle->escape($id)."'";
  //  $queries[] = "DELETE FROM products_add_adv WHERE id = '".$handle->escape($id)."'";
  //  $queries[] = "DELETE FROM rejects WHERE id = '".$handle->escape($id)."'";


    // redirections
    $res = $handle->query("SELECT idFamily FROM products_families WHERE idProduct = '".$handle->escape($id)."'", __FILE__, __LINE__);
    while (list($cat_id) = $handle->fetch($res)) {
      $old_product_name = $cat_id."-".$id."-".Utils::toDashAz09($name);
      $old_urls[] = $old_url = "produits/".$old_product_name.".html";
      $redirect_urls[$old_url] = "maintenance.php";
    }

    // now, we have to update every old redirection
    $urls_to_delete = array(); // old redirected urls equal to new ones we have to delete
    $res = $handle->query("SELECT `old_url`, `new_url` FROM `redirect_urls` WHERE `new_url` IN ('".implode("','",$old_urls)."')", __FILE__, __LINE__);
    while (list($old_old_url, $old_url) = $handle->fetch($res)) {
      $new_url = $redirect_urls[$old_url];
      $queries[] = "UPDATE `redirect_urls` SET `new_url` = '".$new_url."' WHERE `old_url` = '".$old_old_url."'";
    }


    // update attributes
  //  $res = $handle->query("SELECT idFamily FROM products_families WHERE idProduct = '".$id."' LIMIT 0, 1", __FILE__, __LINE__);
  //  list($oldCat) = $handle->fetch($res);
  //  updateCat3Attributes($id, null, $oldCat);

    foreach ($queries as $query)
      $handle->query($query, __FILE__, __LINE__);

  //  for($i = 1; $i <= 3; ++$i) {
  //    @unlink(PRODUCTS_FILES_INC.$id.'-'.$i.'.doc');
  //    @unlink(PRODUCTS_FILES_INC.$id.'-'.$i.'.pdf');
  //  }

    ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'], 'Suppression de la fiche produit '.$name.' [ID : '.$id.'] [ID Annonceur : '.$adv['idAdvertiser'].'] | old_product_name : '.$old_product_name);
    //?	En cas de reporting suppression : afficher la simple liste des ID fiches supprimées + ancien nom fiche + famille 3 + partenaire : OD 15/06/2011

  }

  return true;
}

/* Vérifier si la fiche a une version sauvegardée */
function isBackup($handle, $id) {
  $ret = false;

  $result = $handle->query("SELECT `id` FROM `products_add` WHERE id = '".$handle->escape($id)."' AND type = 'b'", __FILE__, __LINE__);
  if ($handle->numrows($result, __FILE__, __LINE__) == 1)
    $ret = true;

  return $ret;
}

/* MAJ un produit */
function updateProduct($handle, $id, $name, $fastdesc, $idAdvertiser, $families, $alias, $keywords, $desc, $descd, $ean, $warranty, $title_tag, $meta_desc_tag, $shipping_fee, $video_code, $t1, $t2, $t3, $cg, $ci, $cc, $products, $productsLinked, $refSupplier, $price, $price2, $unite, $marge, $idTVA, $delai_livraison, $contrainteProduit, $asEstimate, $username, $save, $type, $data, $ref = '', $supplierInfo, $locked='0') {
  $ret = false;
  if ($save) {
    // Enregistrer la version backup
    if (!isBackup($handle, $id)) {
      $handle->query("
      INSERT INTO products_add
        (id, idAdvertiser, name, fastdesc, type,
        families, alias, keywords, descc, descd,
        ean, warranty, title_tag, meta_desc_tag, shipping_fee,
        cg, ci, cc, products, refSupplier,
        price, price2, unite, marge, idTVA,
        delai_livraison, contrainteProduit, video_code, ref)
      VALUES
        ('".$handle->escape($id)."', '".$handle->escape($data["idAdvertiser"])."', '".$handle->escape($data["name"])  ."', '".$handle->escape($data["fastdesc"])."', 'b', '" .
        $handle->escape($data["families"])."', '".$handle->escape($data["keywords"])."', '".$handle->escape($data["keywords"])."', '".$handle->escape($data["descc"])."', '".$handle->escape($data["descd"])."', '" .
        $handle->escape($data["ean"])."', '".$handle->escape($data["warranty"])."', '".$handle->escape($data["title_tag"])."', '".$handle->escape($data["meta_desc_tag"])."', '".
        $handle->escape($data["shipping_fee"])."', '".$handle->escape($data["cg"])."', '".$handle->escape($data["ci"])."', '" .$handle->escape($data["cc"])."', '".$handle->escape($data["products"])."', '".
        $handle->escape($data["refSuppplier"])."', '".$handle->escape($data["price"])."', '".$handle->escape($data["price2"])."', '" .$handle->escape($data["unite"])."', '".$handle->escape($data["marge"])."', '".
        $handle->escape($data["idTVA"])."', '".$handle->escape($data["delai_livraison"])."', '".$handle->escape($data["contrainteProduit"])."', '" .$handle->escape($data["video_code"])."', '".$handle->escape($data["ref"])."')", __FILE__, __LINE__);
    }
    else {
		//alias = '".$handle->escape($data["alias"])."',
		// On change 07/04/2016 Mot clé doivent aller dans le champs Alias
      $handle->query("
      UPDATE products_add SET
        idAdvertiser = '".$handle->escape($data["idAdvertiser"])."',
        name = '".$handle->escape($data["name"])."',
        fastdesc = '".$handle->escape($data["fastdesc"])."',
        families = '".$handle->escape($data["families"])."',
        alias = '".$handle->escape($data["keywords"])."',
        keywords = '".$handle->escape($data["keywords"])."',
        descc = '".$handle->escape($data["descc"])."',
        descd = '".$handle->escape($data["descd"])."',
        ean = '".$handle->escape($data["ean"]). "',
        warranty = '".$handle->escape($data["warranty"])."',
        title_tag = '".$handle->escape($data["title_tag"])."',
        meta_desc_tag = '".$handle->escape($data["meta_desc_tag"])."',
        shipping_fee = '".$handle->escape($data["shipping_fee"])."',
        cg = '".$handle->escape($data["cg"])."',
        ci = '".$handle->escape($data["ci"])."',
        cc = '".$handle->escape($data["cc"])."',
        products = '".$handle->escape($data["products"])."',
        refSupplier = '".$handle->escape($data["refSupplier"])."',
        price = '".$handle->escape($data["price"])."',
        price2 = '".$handle->escape($data["price2"])."',
        unite = '".$handle->escape($data["unite"])."',
        marge = '".$handle->escape($data["marge"])."',
        idTVA = '".$handle->escape($data["idTVA"])."',
        delai_livraison = '".$handle->escape($data["delai_livraison"])."',
        contrainteProduit = '".$handle->escape($data["contrainteProduit"])."',
        video_code = '".$handle->escape($data["video_code"])."',
        ref = '".$handle->escape($data["ref"])."'
      WHERE type = 'b' AND id = '".$handle->escape($id)."' LIMIT 1", __FILE__, __LINE__);
    }

    $new_ref_name = Utils::toDashAz09($name);
	//$handle->escape($alias)
    $handle->query("
    UPDATE products_fr SET
      idAdvertiser = '".$handle->escape($idAdvertiser)."',
      name = '".$handle->escape($name)."',
      fastdesc = '".$handle->escape($fastdesc)."',
      ref_name = '".$handle->escape($new_ref_name)."',
      alias = '".$handle->escape($keywords)."',
      keywords = '".$handle->escape($keywords)."',
      descc = '".$handle->escape($desc)."',
      descd = '".$handle->escape($descd)."',
      delai_livraison = '".$handle->escape($delai_livraison)."',
	  locked	= '".$locked."'
    WHERE id = '".$handle->escape($id)."'", __FILE__, __LINE__);

    $handle->query("
    UPDATE products SET
      idAdvertiser = '".$handle->escape($idAdvertiser)."',
      timestamp = '".time()."',
      cg = '".$handle->escape($cg)."',
      ci = '".$handle->escape($ci)."',
      cc = '".$handle->escape($cc)."',
      refSupplier = '".$handle->escape($refSupplier)."',
      price = '".$handle->escape($price)."',
      price2 = '".$handle->escape($price2)."',
      unite = '".$handle->escape($unite)."',
      marge = '".$handle->escape($marge)."',
      idTVA = '".$handle->escape($idTVA)."',
      contrainteProduit = '".$handle->escape($contrainteProduit)."',
      as_estimate = '".$handle->escape($asEstimate)."',
      ean = '".$handle->escape($ean)."',
      warranty = '".$handle->escape($warranty)."',
      title_tag = '".$handle->escape($title_tag)."',
      meta_desc_tag = '".$handle->escape($meta_desc_tag)."',
      shipping_fee = '".$handle->escape($shipping_fee)."',
      video_code = '".$handle->escape($video_code)."'
    WHERE id = '".$handle->escape($id)."'", __FILE__, __LINE__);
  }
  else {
    // Prepa requête
    if (!isEdit($handle, $id)) {
      $query = "
        INSERT INTO products_add
          (id, idAdvertiser, name, fastdesc, type,
          families, alias, keywords, descc, descd,
          ean, warranty, title_tag, meta_desc_tag, shipping_fee,
          cg, ci, cc, products, productsLinked, refSupplier,
          price, price2, unite, marge, idTVA,
          delai_livraison, contrainteProduit, video_code, ref)
        VALUES
          ('".$handle->escape($id)."', '".$handle->escape($idAdvertiser)."', '".$handle->escape($name)."', '".$handle->escape($fastdesc)."', 'm',
          '".$handle->escape($families)."', '".$handle->escape($keywords)."', '".$handle->escape($keywords)."', '".$handle->escape($desc)."', '".$handle->escape($descd)."',
          '".$handle->escape($ean)."', '".$handle->escape($warranty)."', '".$handle->escape($title_tag)."', '".$handle->escape($meta_desc_tag)."', '".$handle->escape($shipping_fee)."',
          '".$handle->escape($cg)."', '".$handle->escape($ci)."', '".$handle->escape($cc)."', '".$handle->escape($products)."', '".$handle->escape($productsLinked)."', '".$handle->escape($refSupplier)."',
          '".$handle->escape($price)."', '".$handle->escape($price2)."', '".$handle->escape($unite)."', '".$handle->escape($marge)."', '".$handle->escape($idTVA)."',
          '".$handle->escape($delai_livraison)."', '".$handle->escape($contrainteProduit)."', '".$handle->escape($asEstimate)."', '".$handle->escape($video_code)."', '".$handle->escape($ref)."')";
    }
    else {
      //$handle->escape($alias)
	  $query = "
      UPDATE products_add SET
        idAdvertiser = '".$handle->escape($idAdvertiser)."',
        name = '".$handle->escape($name)."',
        fastdesc = '".$handle->escape($fastdesc)."',
        families = '".$handle->escape($families)."',
        alias = '".$handle->escape($keywords)."',
        keywords = '".$handle->escape($keywords)."',
        descc = '".$handle->escape($desc)."',
        descd = '".$handle->escape($descd)."',
        ean = '".$handle->escape($ean)."',
        warranty = '".$handle->escape($warranty)."',
        title_tag = '".$handle->escape($title_tag)."',
        meta_desc_tag = '".$handle->escape($meta_desc_tag)."',
        shipping_fee = '".$handle->escape($shipping_fee)."',
        cg = '".$handle->escape($cg)."',
        ci = '".$handle->escape($ci)."',
        cc = '".$handle->escape($cc)."',
        products = '".$handle->escape($products)."',
        productsLinked = '".$handle->escape($productsLinked)."',
        refSupplier = '".$handle->escape($refSupplier)."',
        price = '".$handle->escape($price)."',
        price2 = '".$handle->escape($price2)."',
        unite = '".$handle->escape($unite)."',
        marge = '".$handle->escape($marge)."',
        idTVA = '".$handle->escape($idTVA)."',
        delai_livraison = '".$handle->escape($delai_livraison)."',
        contrainteProduit = '".$handle->escape($contrainteProduit)."',
        as_estimate = '".$handle->escape($asEstimate)."',
        video_code = '".$handle->escape($video_code)."',
        ref = '".$handle->escape($ref)."'
      WHERE type = 'm' AND id = '".$handle->escape($id)."' limit 1";
    }
    $handle->query($query, __FILE__, __LINE__);
  }

  $ret = true;

  if ($save) {

    // redirection urls
    $old_urls = $new_urls = array(); // array of old and new urls
    $redirect_urls = array(); // arrays of new urls, indexed by old urls
    // cat lists are comma separated strings of id with an ending comma. ex: 768,687,113,
    $catListOld = explode(",",$data["families"]); // old product's category list
    $catListNew = explode(",",$families); // new product's category list
    //$catListOld = explode(",","768,113,687,498,335,79819,"); // old product's category list
    //$catListNew = explode(",","113,688,768,888,"); // new product's category list
    array_pop($catListOld); // get rid of the last ,
    array_pop($catListNew); // "

    $catCommonList = array_intersect($catListNew, $catListOld); // list of categories present before and after this update
    $catAddedList = array_diff($catListNew, $catListOld); // added categories
    $catRemovedList = array_diff($catListOld, $catListNew); // removed categories

    // if product's ref name changed, we redirect all the old urls to the new ones for every categories that are still present
    $old_ref_name = Utils::toDashAz09($data["name"]);
    if ($new_ref_name != $old_ref_name) {
      foreach ($catCommonList as $cat_id) {
        $old_urls[] = $old_url = "produits/".$cat_id."-".$id."-".$old_ref_name.".html";
        $new_urls[] = $new_url = "produits/".$cat_id."-".$id."-".$new_ref_name.".html";
        $redirect_urls[$old_url] = $new_url;
      }
    }

    // deleting every old redirection which would have pointed to urls equal to themselves
    if (!empty($catAddedList)) {
      foreach ($catAddedList as $added_cat_id)
        $added_urls[] = "produits/".$added_cat_id."-".$id."-".$new_ref_name.".html";
      $handle->query("DELETE FROM `redirect_urls` WHERE `old_url` IN ('".implode("','",$added_urls)."')", __FILE__, __LINE__);
    }

    // getting the id of the last new added category if there is any, or simply the id of the last common category
    if (!($last_cat_id = reset($catAddedList)))
      $last_cat_id = reset($catCommonList);

    // redirecting all old product's categories url to this last category
    foreach ($catRemovedList as $old_cat_id) {
      $old_urls[] = $old_url = "produits/".$old_cat_id."-".$id."-".$old_ref_name.".html";
      $new_urls[] = $new_url = "produits/".$last_cat_id."-".$id."-".$new_ref_name.".html";
      $redirect_urls[$old_url] = $new_url;
    }

    if (!empty($redirect_urls)) {
      $queries = array();

      // now, we have to update every old redirection refering to the 'new' old urls
      $urls_to_delete = array(); // old redirected urls equal to new ones we have to delete
      $res = $handle->query("SELECT `old_url`, `new_url` FROM `redirect_urls` WHERE `new_url` IN ('".implode("','",$old_urls)."')", __FILE__, __LINE__);
      while (list($old_old_url, $old_url) = $handle->fetch($res)) {
        $new_url = $redirect_urls[$old_url];
        if ($new_url == $old_old_url)
          $urls_to_delete[] = $new_url;
        else
          $queries[] = "UPDATE `redirect_urls` SET `new_url` = '".$new_url."' WHERE `old_url` = '".$old_old_url."'";
      }

      if (!empty($urls_to_delete))
        $queries[] = "DELETE FROM `redirect_urls` WHERE `old_url` IN ('".implode("','",$urls_to_delete)."')";

      // redirection urls to insert
      foreach ($redirect_urls as $old_url => $new_url)
        $values[] = "('".$old_url."', '".$new_url."', '".time()."')";
      $queries[] = "INSERT INTO `redirect_urls` (`old_url`, `new_url`, `timestamp`) VALUES ".implode(",",$values);

      foreach ($queries as $query)
        $handle->query($query, __FILE__, __LINE__);
    }

    /*print "queries"; pp($queries);
    print "redirect_urls"; pp($redirect_urls);
    print "common"; pp($catCommonList);
    print "added"; pp($catAddedList);
    print "removed"; pp($catRemovedList);*/
    // Familles
    $handle->query("DELETE FROM products_families WHERE idProduct = '".$handle->escape($id)."'", __FILE__, __LINE__);
    $familiesTab = explode(",", $families);
    for($i=0,$l=count($familiesTab); $i<$l; ++$i) {
      if(preg_match("/^[0-9]+$/", $familiesTab[$i])) {
        $result = $handle->query("SELECT name FROM families_fr WHERE id = '".$handle->escape($familiesTab[$i])."'", __FILE__, __LINE__);
        if ($handle->numrows($result, __FILE__, __LINE__) == 1) {
          $mainFamily = $i+1;
          $handle->query("INSERT INTO products_families (idProduct, idFamily, orderFamily) VALUES('".$handle->escape($id)."', '".$handle->escape($familiesTab[$i])."', '".$mainFamily."')");
        }
      }
    }

    // Produits liés lors d'un lead
    $handle->query("DELETE FROM productslinks WHERE idProduct = '".$handle->escape($id)."'", __FILE__, __LINE__);
    if(!empty($products)) {
      $productsTab = explode(",", $products);
      for($i=0,$l=count($productsTab); $i<$l; ++$i) {
        if(preg_match("/^[0-9]+$/", $productsTab[$i])) {
          $result = $handle->query("SELECT id FROM products WHERE id = '".$handle->escape($productsTab[$i])."'", __FILE__, __LINE__);
          if ($handle->numrows($result, __FILE__, __LINE__) == 1) {
            $handle->query("INSERT INTO productslinks (idProduct, idProductLinked) VALUES('".$handle->escape($id)."', '".$handle->escape($productsTab[$i])."')");
          }
        }
      }
    }

    // Produits liés affichés sur fiche produit
    $handle->query("DELETE FROM products_linked WHERE pdt_id = ".$id);
    if (!empty($productsLinked)) {
      $productsLinkedIds = explode(',', $productsLinked);
      $linkPos = 1;
      foreach ($productsLinkedIds as $productLinkedId) {
        if (preg_match('/^[0-9]+$/', $productLinkedId)) {
          $linkId = generateID(1, 0xffffffff, 'id', 'products_linked', $handle);
          $handle->query("
            INSERT INTO products_linked (
              id,
              pdt_id,
              pdt_linked_id,
              position
            ) VALUES(
            '".$linkId."',
            '".$id."',
            '".$productLinkedId."',
            '".$linkPos."'
            )"
          );
          $linkPos++;
        }
      }
    }

    // MAJ des références
    if ($ref != '') {

      // supplier infos
      if (count($supplierInfo) >= 5 && $supplierInfo[0] != "") {
        $isSupplier     = true;
        $prixPublic     = $supplierInfo[1] == 1 ? true : false;
        $margeRemiseDft = $supplierInfo[2];
        //$idTVAdft       = $supplierInfo[4];
      }
      else {
        $isSupplier = false;
      }


      $newAttributes = array();

      // new refs
      $nbCols_all_tab = explode("<=>", $ref);
      $cols_lines_tab = explode("<_>", $nbCols_all_tab[1]);
      $elts = explode("<->", $cols_lines_tab[0]);
      $cols_content = array();
      for($i=0; $i<$nbCols_all_tab[0]; $i++) {
        $cols_content[] = $elts[$i];
        if ($i >= 3 && $i < $nbCols_all_tab[0]-6)
          $newAttributes[$i-3]["name"] = $elts[$i];
      }

      //$handle->query("DELETE FROM `references_content` WHERE `idProduct` = '".$id."'", __FILE__, __LINE__);
      $handle->query("DELETE FROM `references_cols` WHERE `idProduct` = '".$handle->escape($id)."'", __FILE__, __LINE__);
      $handle->query("INSERT INTO `references_cols` VALUES('".$handle->escape($id)."', '".$handle->escape(serialize($cols_content))."')", __FILE__, __LINE__);


      $old_ref_ids = $new_ref_ids = array();
      // get old references ids
      $res = $handle->query("SELECT `id` FROM `references_content` WHERE `idProduct` = ".$id." AND `deleted` = 0", __FILE__, __LINE__);
      while ($ref_id = $handle->fetch($res))
        $old_ref_ids[] = $ref_id[0];

      // get the new ones, ignoring thoses without idTC (which will be further inserted
      for ($i=1,$l=count($cols_lines_tab); $i<$l; ++$i) {
        $elts = explode("<->", $cols_lines_tab[$i]);
        if (!empty($elts[0]))
          $new_ref_ids[] = $elts[0];
      }

      $ref_ids_2_upd = array_flip(array_intersect($new_ref_ids, $old_ref_ids)); // ref to update
      $ref_ids_2_del = array_diff($old_ref_ids, $new_ref_ids); // ref to "delete"

      // "delete" refs
      foreach ($ref_ids_2_del as $ref_id)
        $handle->query("UPDATE `references_content` SET `deleted` = 0, `classement` = 255 WHERE `id` = ".$ref_id, __FILE__, __LINE__);

      // list of final referrences ID's
      $idTcList = [];

      for ($i=1,$l=count($cols_lines_tab); $i<$l; ++$i) {
        $elts = explode("<->", $cols_lines_tab[$i]);
        $elts_count = count($elts);
        $idTC = $elts[0];
        $label = $elts[1];
        $ref_content = array();
        if ($isSupplier) {
          $refSupplierRef = $elts[2];
          $uniteRef       = $elts[$elts_count-6];
          $idTVARef       = $elts[$elts_count-5];
          $price2Ref      = $elts[$elts_count-4];
          $margeRemiseRef = $elts[$elts_count-3];
          $priceRef       = $elts[$elts_count-2];
          $ecoTax         = $elts[$elts_count-1];

          $price2RefOK = preg_match("/^[0-9]+((\.|,)[0-9]+){0,1}$/", $price2Ref);
          $priceRefOK  = preg_match("/^[0-9]+((\.|,)[0-9]+){0,1}$/", $priceRef);

          if (!preg_match("/^[0-9]+((\.|,)[0-9]+){0,1}$/", $margeRemiseRef)) $margeRemiseRef = $margeRemiseDft;

          if (!$prixPublic) {
            if ($price2RefOK) {
              if (!$priceRefOK)
                $priceRef = round($price2Ref * (100+$margeRemiseRef)/100, 2);
            }
            else {
              if ($priceRefOK)
                $price2Ref = round($priceRef * 100/(100+$margeRemiseRef), 2);
            }
          }
          else {
            if ($priceRefOK) {
              if (!$price2RefOK)
                $price2Ref = round($priceRef * (100-$margeRemiseRef)/100, 2);
            }
            else {
              if ($price2RefOK)
                $priceRef = round($price2Ref * 100/(100-$margeRemiseRef), 2);
            }
          }
          for ($j=3; $j<$elts_count-6; ++$j) {
            $ref_content[] = $elts[$j];
            $newAttributes[$j-3]["values"][] = $elts[$j];
          }
        }
        else {
          $refSupplierRef = $uniteRef = $idTVARef = $price2Ref = $margeRemiseRef = '';
          $priceRef = $elts[$elts_count-1];

          for ($j=2; $j<$elts_count-1; ++$j) {
            $ref_content[] = $elts[$j];
          }
        }

        if (!empty($idTC) && isset($ref_ids_2_upd[$idTC])) { // ref was present, update it
          $handle->query("
            UPDATE `references_content`
            SET
              `sup_id` = '".$idAdvertiser."',
              `label` = '".$handle->escape($label)."',
              `content` = '".$handle->escape(serialize($ref_content))."',
              `refSupplier` = '".$handle->escape($refSupplierRef)."',
              `price` = '".$handle->escape($priceRef)."',
              `price2` = '".$handle->escape($price2Ref)."',
              `unite` = '".$handle->escape($uniteRef)."',
              `marge` = '".$handle->escape($margeRemiseRef)."',
              `idTVA` = '".$handle->escape($idTVARef)."',
              `ecotax` = '".$handle->escape($ecoTax)."',
              `classement` = '".$i."'
            WHERE `id` = ".$idTC, __FILE__, __LINE__);
        } else { // new ref, insert it
          $idTC = generateIDTC($handle);
          $handle->query("
            INSERT INTO `references_content`
              (`id`, `idProduct`, `sup_id`, `label`, `content`, `refSupplier`, `price`, `price2`, `unite`, `marge`, `idTVA`, `ecotax`, `classement`)
              VALUES (
              '".$idTC."',
              '".$handle->escape($id)."',
              '".$handle->escape($idAdvertiser)."',
              '".$handle->escape($label)."',
              '".$handle->escape(serialize($ref_content))."',
              '".$handle->escape($refSupplierRef)."',
              '".$handle->escape($priceRef)."',
              '".$handle->escape($price2Ref)."',
              '".$handle->escape($uniteRef)."',
              '".$handle->escape($margeRemiseRef)."',
              '".$handle->escape($idTVARef)."',
              '".$handle->escape($ecoTax)."',
              '".$i."'
            )", __FILE__, __LINE__);
        }
        $idTcList[] = $idTC;
      }

      // update attributes
      if ($isSupplier)
        updateCat3Attributes($id, $idTcList, $catListNew, $catListOld, $newAttributes);

      ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],
        'Mise à jour de la fiche produit '.$name.' [ID : '.$id.'] - '.$fastdesc.' [ID Annonceur : '.$idAdvertiser.']'.
        ' | CG : '.$cg.' -  CI : '.$ci.' -  CC : '.$cc.
        ' | Nombre de références : '.(count($cols_lines_tab)-1).
        ' | Contrainte quantité de produits : '.$contrainteProduit.
        ' | Mise sous devis du produits : '.$asEstimate.
        ' | Description : '.$desc.' - Détaillée : '.$descd.
        ' | Code EAN : '.$ean.
        ' | Garantie : '.$warranty.
        ' | Balise Title : '.$title_tag.
        ' | Balise Meta Desc : '.$meta_desc_tag.
        ' | Frais de port : '.$shipping_fee.
        ' | Alias : '.$alias.' - Mots clés : '.$keywords.
        ' | Familles : '.$families.
        ' | Produits liés lead : '.$products.
        ' | Produits liés fiche : '.$productsLinked.
        ' | Vidéo : '.(empty($video_code)?"non":"oui"));

    }	// fin insert références
    else {
      $handle->query("DELETE FROM `references_cols` WHERE `idProduct` = '".$handle->escape($id)."'", __FILE__, __LINE__);
      $handle->query("UPDATE `references_content` SET `deleted` = 0 WHERE `idProduct` = '".$handle->escape($id)."'", __FILE__, __LINE__);
      ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],
        'Mise à jour de la fiche produit '.$name.' [ID : '.$id.'] - '.$fastdesc.' [ID Annonceur : '.$idAdvertiser.']'.
        ' | CG : '.$cg.' -  CI : '.$ci.' -  CC : '.$cc.
        ' | Référence Fournisseur : '.$refSupplier.
        ' | Prix : '.$price.' - Prix fournisseur : '.$price2.' - Unité : '.$unite.' - Marge : '.$marge.' - idTVA : '.$idTVA.' - Contrainte quantité de produits : '.$contrainteProduit.' - Mise sous devis du produits : '.$asEstimate.
        ' | Description : '.$desc.' - Détaillée : '.$descd.
        ' | Code EAN : '.$ean.
        ' | Garantie : '.$warranty.
        ' | Balise Title : '.$title_tag.
        ' | Balise Meta Desc : '.$meta_desc_tag.
        ' | Frais de port : '.$shipping_fee.
        ' | Alias : '.$alias.' - Mots clés : '.$keywords.
        ' | Familles : '.$families.
        ' | Produits liés lead : '.$products.
        ' | Produits liés fiche : '.$productsLinked.
        ' | Vidéo : '.(empty($video_code)?"non":"oui"));
    }

    notify($handle, 'Mise à jour de la fiche produit '.$name.' [ID : '.$id.']', $username);

    ////////////
    if($type == 'edit') {
      // Effacer version en attente
      $handle->query("DELETE FROM products_add WHERE id = '".$handle->escape($id)."' AND type = 'm' limit 1", __FILE__, __LINE__);
    }
    else if($type == 'edit_adv') {
      // Supprimer fichiers fiche existante + copier nouveaux
      for($i = 1; $i <= 3; ++$i) {
        @unlink(PRODUCTS_FILES_INC.$id.'-'.$i.'.doc');
        @unlink(PRODUCTS_FILES_INC.$id.'-'.$i.'.pdf');

        @rename(PRODUCTS_FILES_ADV_INC.$id.'-'.$i.'.doc', PRODUCTS_FILES_INC.$id.'-'.$i.'.doc');
        @rename(PRODUCTS_FILES_ADV_INC.$id.'-'.$i.'.pdf', PRODUCTS_FILES_INC.$id.'-'.$i.'.pdf');
      }

      // Deleting old images and moving the new ones
      $num = 1;
      while (is_file(PRODUCTS_IMAGE_INC."zoom/".$id."-".$num.".jpg")) {
        $fileName = $id."-".$num.".jpg";
        unlink(PRODUCTS_IMAGE_INC."zoom/".$fileName);
        unlink(PRODUCTS_IMAGE_INC."card/".$fileName);
        unlink(PRODUCTS_IMAGE_INC."thumb_big/".$fileName);
        unlink(PRODUCTS_IMAGE_INC."thumb_small/".$fileName);
        $num++;
      }
      $num = 1;
      while (is_file(PRODUCTS_IMAGE_ADV_INC."zoom/".$id."-".$num.".jpg")) {
        $fileName = $id."-".$num.".jpg";
        rename(PRODUCTS_IMAGE_ADV_INC."zoom/".$fileName, PRODUCTS_IMAGE_INC."zoom/".$fileName);
        rename(PRODUCTS_IMAGE_ADV_INC."card/".$fileName, PRODUCTS_IMAGE_INC."card/".$fileName);
        rename(PRODUCTS_IMAGE_ADV_INC."thumb_big/".$fileName, PRODUCTS_IMAGE_INC."thumb_big/".$fileName);
        rename(PRODUCTS_IMAGE_ADV_INC."thumb_small/".$fileName, PRODUCTS_IMAGE_INC."thumb_small/".$fileName);
        $num++;
      }

      // Effacer version en attente
      $handle->query("DELETE FROM products_add_adv WHERE id = '".$handle->escape($id)."' AND type = 'm' limit 1", __FILE__, __LINE__);
    }
  }  // fin save
  else {
    if($ref != '')
      ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],
        'Demande de mise à jour de la fiche produit '.$name.' [ID : '.$id.'] - '.$fastdesc.' [ID Annonceur : '.$idAdvertiser.']'.
        ' | CG : '.$cg.' -  CI : '.$ci.' -  CC : '.$cc.
        ' | Ce produit comporte des références'.
        ' | Contrainte quantité de produits : '.$contrainteProduit.
        ' | Mise sous devis du produit : '.$asEstimate.
        ' | Description : '.$desc.' - Détaillée : '.$descd.
        ' | Code EAN : '.$ean.
        ' | Garantie : '.$warranty.
        ' | Balise Title : '.$title_tag.
        ' | Balise Meta Desc : '.$meta_desc_tag.
        ' | Frais de port : '.$shipping_fee.
        ' | Alias : '.$alias.' - Mots clés : '.$keywords.
        ' | Familles : '.$families.
        ' | Produits liés lead : '.$products.
        ' | Produits liés fiche : '.$productsLinked.
        ' | Vidéo : '.(empty($video_code)?"non":"oui"));
    else
      ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],
        'Demande de mise à jour de la fiche produit '.$name.' [ID : '.$id.'] - '.$fastdesc.' [ID Annonceur : '.$idAdvertiser.']'.
        ' | CG : '.$cg.' -  CI : '.$ci.' -  CC : '.$cc.
        ' | Référence Fournisseur : '.$refSupplier.
        ' | Prix : '.$price.
          ' | Prix fournisseur : '.$price2.' - Unité : '.$unite.' - Marge : '.$marge.' - idTVA : '.$idTVA.' - Contrainte quantité de produits : '.$contrainteProduit.' - Mise sous devis du produit : '.$asEstimate.
        ' | Description : '.$desc.' - Détaillée : '.$descd.
        ' | Code EAN : '.$ean.
        ' | Garantie : '.$warranty.
        ' | Balise Title : '.$title_tag.
        ' | Balise Meta Desc : '.$meta_desc_tag.
        ' | Frais de port : '.$shipping_fee.
        ' | Alias : '.$alias.' - Mots clés : '.$keywords.
        ' | Familles : '.$families.
        ' | Produits liés : '.$products.
        ' | Produits liés fiche : '.$productsLinked.
        ' | Vidéo : '.(empty($video_code)?"non":"oui"));

    notify($handle, 'Demande de mise à jour de la fiche produit '.$name.' [ID : '.$id.']', $username);
  }

  return $ret;

}



/* Vérifier si produit déjà en attente de validation */
function isEdit($handle, $id) {
  $ret = false;

  $result = $handle->query("SELECT id FROM products_add WHERE id = '".$handle->escape($id)."' and type = 'm'", __FILE__, __LINE__);
  if ($handle->numrows($result, __FILE__, __LINE__) == 1)
    $ret = true;

  return $ret;
}



/* Vérifier si un produit a une ou pls demandes
   i : réf handle connexion
   i : id produit
   o : true si demande(s), false sinon */
function hasDemands(& $handle, $id)
{
    $ret = false;

    if(($result = & $handle->query('select id from contacts where idProduct = \''.$handle->escape($id).'\'', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) > 0)
    {
        $ret = true;
    }

    return $ret;

}


/* Afficher les produits extranet en attente de validation
   i : réf handle connexion
   i : type (c ou m)
   o : réf tableau produits */
function & displayWaitAdv(& $handle, $type)
{
    $ret = array();

    if($type != 'c' && $type != 'm'){
        return $ret;
    }

	//Start modification on 14/11/2014
	//To ignore the records starting with 10x"#"
	if($type=='c'){
		$product_query_condition	=  ' AND name not like \'##########%\' ';
	}

    if($result = & $handle->query('select p.id, p.name, p.fastdesc, a.nom1, p.timestamp from products_add_adv p, advertisers a where p.type = \''.$handle->escape($type).'\' '.$product_query_condition.' and p.reject = 0 and a.id = p.idAdvertiser order by p.timestamp desc, a.id, p.name', __FILE__, __LINE__))
    {
        while($row = & $handle->fetch($result))
        {
            $ret[$row[0]] = array($row[1], $row[2], $row[3], $row[4]);

        }
    }


    return $ret;

}

//Lock a product
function lock(& $handle, $id){
	$handle->query("UPDATE products_fr SET locked=1 WHERE id='".$handle->escape($id)."'", __FILE__, __LINE__);
}


//UnLock a product
function unlock(& $handle, $id){
	$handle->query("UPDATE products_fr SET locked=0 WHERE id='".$handle->escape($id)."'", __FILE__, __LINE__);
}
