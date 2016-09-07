<?php
/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 05/10/2012

 Fichier : /cron/fr/proceed_aborted_leads.php
 Description : cron d'envoi de mails en cas de non transformation en lead/commande/devis d'un produit sauvegardé

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$transformation = false;
$clientList = array();

$twoDaysAgo = time()-3600*48;

$savedProductsList = Doctrine_Query::create()
        ->select()
        ->from('ProductsSavedList ps')
        ->leftJoin('ps.clients c')
        ->leftJoin('ps.products_families pf')
        ->leftJoin('ps.products_fr pfr')
        ->where('ps.create_time < '.$twoDaysAgo)
        ->andWhere('ps.mail_sent = 0')
        ->fetchArray();
// 10886860
foreach($savedProductsList as $savedProduct){
  /*
   *  order 1216750141
   */
 // pp($savedProduct);

   $order = Doctrine_Query::create()
     ->select('o.id, o.web_id, o.created, o.validated, o.total_ht, o.total_ttc, o.processing_status')
     ->from('Order o')
     ->leftJoin('o.lines ol')
     ->where('o.client_id = ?', $savedProduct['client_id'])
     ->andWhere('ol.pdt_id = ?', $savedProduct['product_id'])
     ->andWhere('o.created > ?', $twoDaysAgo)
     ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
if(!empty($order))
  $transformation = true;

   /*
    *  lead  10990215 217571
    */
   $contact = Doctrine_Query::create()
     ->select()
     ->from('Contacts c')
     ->where('c.email = ?', $savedProduct['clients']['login'])
     ->andWhere('c.create_time > ?', $twoDaysAgo)
     ->andWhere('c.idProduct = ?', $savedProduct['product_id'])
     //->andWhere('ca.category != ? OR ca.category != ?', array(__ADV_CAT_BLOCKED__,__ADV_CAT_LITIGATION__))
     //->andWhere('(c.invoice_status != ? AND c.invoice_status != ? AND c.invoice_status != ? AND c.invoice_status != ? AND c.invoice_status != ? AND c.invoice_status != ?)  OR (c.invoice_status = 0 AND c.processing_status = 2 AND processing_time != 0)', array(__LEAD_INVOICE_STATUS_NOT_CHARGED__, __LEAD_INVOICE_STATUS_REJECTED__, __LEAD_INVOICE_STATUS_REJECTED_WAIT__, __LEAD_INVOICE_STATUS_CHARGEABLE_REJECTED_WAIT__, __LEAD_INVOICE_STATUS_CREDITED__, __LEAD_INVOICE_STATUS_DISCHARGED__))
     ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
 if(!empty($contact))
    $transformation = true;

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
    $transformation = true;

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
    $transformation = true;

   $savedProduct['transformation'] = $transformation;
   
   $clientList[$savedProduct['client_id']][$savedProduct['product_id']] = $savedProduct;
   
}

foreach ($clientList as $client){
  $productList = '';
  foreach ($client as $savedProduct)
    if(!$savedProduct['transformation']){

      $productList .= '<a href="'. URL.'produits/'.$savedProduct['products_families']['idFamily'].'-'.$savedProduct['product_id'].'-'.$savedProduct['products_fr'][0]['ref_name'].'.html">'.$savedProduct['products_fr'][0]['name'].'</a><br />';
      $updateSavedProd = Doctrine_Query::create()
        ->update('ProductsSavedList')
        ->set('mail_sent', 1)
        ->where('id = ?', $savedProduct['id'])
        ->execute();
    }
    if(!empty($productList)){
      $mail_content = array(
         'email' => 'sauvegarde-produit@techni-contact.com',
         'subject' => "Client à relancer suite à sauvegarde produit",
         'headers' => "From: Techni-Contact – Sauvegarde produits <sauvegarde-produit@techni-contact.com>\nReply-to: Techni-Contact <sauvegarde-produit@techni-contact.com>\n",
         'template' => "tc-saved_product_not_transformed-cron",
         'data' => array(
             CUSTOMER_COMPANY_NAME => $savedProduct['clients']['societe'],
             CUSTOMER_FIRST_NAME => $savedProduct['clients']['prenom'],
             CUSTOMER_LAST_NAME => $savedProduct['clients']['nom'],
             CUSTOMER_EMAIL => $savedProduct['clients']['login'],
             CUSTOMER_PHONE => $savedProduct['clients']['tel1'],
             CUSTOMER_LINK => ADMIN_URL.'clients/?idClient='.$savedProduct['client_id'],
             PRODUCTS_LIST => $productList
         )
      );
      $mail = new Email($mail_content);
      $mail->send();
    }
  
}
?>
