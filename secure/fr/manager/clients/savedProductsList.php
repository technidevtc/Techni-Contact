<?php

/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 11 mars 2011

 Mises à jour :
 * AS IT IS REQUIRED THROUGH AJAX, THIS FILE MUST BE ENCODED IN UTF-8 *

 Fichier : /secure/manager/clients/savedProductsList.php
 Description : Affichage de la liste des produits sauvegardés d'un client

/=================================================================*/

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$handle = DBHandle::get_instance();

$clientID = empty ($clientID) && $_GET['customerID'] ? $_GET['customerID'] : '';
if ($clientID != '') {
  if (preg_match('/^\d+$/',$clientID)) {
    $clientInfos = new CustomerUser($handle,$clientID);
    if (!$clientInfos->exists) {
      $error = true;
      $errorstring .= "- Il n'existe pas de client ayant pour num&eacute;ro identifiant ".$clientID."<br />\n";
      exit;
    } else {
      $page = 'client';
    }
  } else {
    $error = true;
    $errorstring .= "- Le num&eacute;ro d'identifiant client est invalide<br />\n";
    exit;
  }
  
  
  
  $clientList = array();

  $twoDaysAgo = time()-3600*48;
 
  $savedProductsList = Doctrine_Query::create()
          ->select()
          ->from('ProductsSavedList ps INDEXBY ps.id')
          ->leftJoin('ps.clients c')
          ->leftJoin('ps.products_families pf')
          ->leftJoin('ps.products_fr pfr')
          //->where('ps.mail_sent = 0')
          ->andWhere('ps.client_id = ?', $clientID)
          ->fetchArray();
  
//pp($savedProductsList);
  // 10886860
  foreach($savedProductsList as $savedProduct){
    /*
     *  order 1216750141
     */
    //pp($savedProduct);
    $transformation = false;

     $order = Doctrine_Query::create()
       ->select('o.id, o.web_id, o.created, o.validated, o.total_ht, o.total_ttc, o.processing_status')
       ->from('Order o')
       ->leftJoin('o.lines ol')
       ->where('o.client_id = ?', $savedProduct['client_id'])
       ->andWhere('ol.pdt_id = ?', $savedProduct['product_id'])
       ->andWhere('o.created > ?', $twoDaysAgo)
       ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
  if(!empty($order))
    $transformation = 'commande';

     /*
      *  lead  10990215 217571
      */
     $contact = Doctrine_Query::create()
       ->select()
       ->from('Contacts c')
       ->where('c.email = ?', $savedProduct['clients']['login'])
       ->andWhere('c.create_time > ?', $twoDaysAgo)
       ->andWhere('c.idProduct = ?', $savedProduct['product_id'])
             ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
     //->getSqlQuery();//"info13@techni-contact.com" int(1349771708) string(8) "10886860"
     //var_dump($savedProduct['clients']['login'], $twoDaysAgo, $savedProduct['product_id'], $contact);// 
    // pp($contact);
   if(!empty($contact))
      $transformation = 'lead';

     /*
      *  devis pdf   8b3abdcc589599f8a5c92dba94fe9ffb  12553902
      */
      $estimate = Doctrine_Query::create()
              ->select()
              ->from('Paniers p')
              ->leftJoin('p.lines pl')
              ->where('p.idClient = ?', $savedProduct['client_id'])
              ->andWhere('p.create_time > ?', $twoDaysAgo)
              ->andWhere('pl.idProduct = ?', $savedProduct['product_id'])
              ->andWhere('estimate != 0')
              ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);

     if(!empty($estimate))
      $transformation = 'devis pdf';

      /*
      *  devis manager  4064704033  16124216
      */
     $estimate = Doctrine_Query::create()
             ->select()
             ->from('Estimate e')
             ->leftJoin('e.lines el')
             ->where('client_id = ?', $savedProduct['client_id'])
             ->andWhere('created > ?', $twoDaysAgo)
             ->andWhere('el.pdt_id = ?', $savedProduct['product_id'])
             ->fetchOne(array(), DOCTRINE::HYDRATE_ARRAY);
     if(!empty($estimate))
      $transformation = 'devis manager';
    //var_dump($transformation);
  
      $savedProductsList[$savedProduct['id']]['transformation'] = $transformation;

  }
//pp($savedProductsList);
}else
  exit;
?>
<style>
.pdt-preview { float: left; margin: 4px }
.pdt-preview { white-space: nowrap }
.pdt-preview.red { margin: 2px; border: 2px solid #b00000 }
.pdt-preview.fl , .pdt-preview.fr{ width: 420px; background: #f4f4f4 }
.pdt-preview .picture { float: left; width: 150px; height: 110px; text-align: center; border: 1px solid #cccccc; background: #ffffff }
.pdt-preview .infos { float: left; height: 118px; padding: 0 0 0 20px; font: normal 11px arial, helvetica, sans-serif; overflow: hidden }
.pdt-preview.fl .infos, .pdt-preview.fr .infos { float: right; width: 248px; padding: 0 }
.pdt-preview .infos strong { font-size: 12px; margin: 0; padding: 0 }
.pdt-preview .infos a { color: #36339a; text-decoration: underline }
.pdt-preview .infos a:hover { text-decoration: none }
</style>
<br />
<div class="bg">
  <div class="block">
    <div class="title">Produits sauvegardés</div>

    <?php $a = 0;
    foreach($savedProductsList as $savedProduct): 
      $cat3 = Doctrine_Query::create()->select()->from('Families f')->innerJoin('f.family_fr ffr')->where('id = ?', $savedProduct['products_families']['idFamily'])->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
      $adv = Doctrine_Query::create()->select()->from('Advertisers')->where('id = ?', $savedProduct['products_fr'][0]['idAdvertiser'])->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
      ?>
   
        <div class="pdt-preview <?php echo $a%2 == 0 ? 'fl': 'fr';?>">
          <div class="picture fl"><img class="vmaib" src="<?php echo is_file(PRODUCTS_IMAGE_INC."thumb_small/".$pdt["id"]."-1".".jpg") ? PRODUCTS_IMAGE_URL."thumb_small/".$pdt["id"]."-1".".jpg" : PRODUCTS_IMAGE_URL."no-pic-thumb_small.gif";?>"/><div class="vsma"></div></div>
          <div class="infos fr">
            <div class="vmaib">
              Sauvegardé le <?php echo date('d/m/Y à H:i:s', $savedProduct['create_time']); ?><br />
              <?php echo $savedProduct['transformation'] ? 'Transformé en '.$savedProduct['transformation'].'<br />' : ''; ?>
              <a class="_blank" href="<?php echo URL.'produits/'.$savedProduct['products_families']['idFamily'].'-'.$savedProduct['product_id'].'-'.$savedProduct['products_fr'][0]['ref_name'].'.html'; ?>" title="Voir la fiche en ligne"><img src="../ressources/icons/monitor_go.png" alt="" class="view-fo"/></a>
              <a class="_blank" href="<?php echo ADMIN_URL.'products/edit.php?id='.$savedProduct['product_id'];?>" title="Editer la fiche produit"><strong><?php echo $savedProduct['products_fr'][0]['name'];?></strong></a><br/>
              <span><?php echo $savedProduct['products_fr'][0]['fastdesc'];?></span><br/>
              Code fiche produit: <strong><?php echo $savedProduct['product_id']; ?></strong><br/>
              Famille 3 : <a class="_blank" href="<?php echo ADMIN_URL.'search.php?search_type=2&search='.$cat3['id'] ?>"><strong><?php echo $cat3['family_fr']['name']; ?></strong></a><br/>
              <?php echo $adv_cat_list[$adv['category']]['name']; ?> : <a class="_blank" href="<?php echo ADMIN_URL.'advertisers/edit.php?id='.$savedProduct['products_fr'][0]['idAdvertiser']; ?>"><strong><?php echo $adv['nom1'];?></strong></a><br/>
            </div><div class="vsma"></div>
          </div>
          <div class="zero"></div>
        </div>
    <?php echo $a%2 == 0 ? '': '<div class="zero"></div>'; $a++;?>
    <?php    endforeach; ?>
    <div class="zero"></div>
  </div>
 <?php  /*
  "<div class="pdt-preview fl"+(pdt.infos.adv_cat==<?php echo __ADV_CAT_BLOCKED__ ?>?" red":"")+"">"+
                      "<div class="picture"><img class="vmaib" src=""+pdt.pics[0].thumb_small+""/><div class="vsma"></div></div>"+
                      "<div class="infos">"+
                        "<div class="vmaib">"+
                          "<a class="_blank" href=""+pdt.urls.fo_url+"" title="Voir la fiche en ligne"><img src="../ressources/icons/monitor_go.png" alt="" class="view-fo"/></a>"+
                          "<a class="_blank" href=""+pdt.urls.bo_url+"" title="Editer la fiche produit"><strong>"+pdt.infos.name+"</strong></a><br/>"+
                          "<span>"+pdt.infos.fastdesc+"</span><br/>"+
                          "Code fiche produit: <strong>"+pdt.infos.id+"</strong><br/>"+
                          "Famille 3 : <a class="_blank" href=""+pdt.urls.cat3_bo_search_url+""><strong>"+pdt.infos.cat3_name+"</strong></a><br/>"+
                          "Famille 2 : <a  class="pdt_cat2_bo_search_url2" href="" name="cat2Id-"+pdt.infos.cat2_id+""><strong>"+pdt.infos.cat2_name+"</strong></a><br/>"+
                          pdt.infos.adv_cat_name+" : <a class="_blank" href=""+pdt.urls.adv_bo_url+""><strong>"+pdt.infos.adv_name+"</strong></a><br/>"+
                          "<a href="#pdt_sheet">Voir description produit</a><br/>"+
                          "<a href="#use_for_lead">Utiliser ce produit pour générer un lead</a>"+
                        "</div><div class="vsma"></div>"+
                      "</div>"+
                      "<div class="zero"></div>"+
                    "</div>"*/ ?>

</div>

