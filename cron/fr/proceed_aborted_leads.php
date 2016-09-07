<?php
/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 05/10/2012

 Fichier : /cron/fr/proceed_aborted_leads.php
 Description : cron d'envoi de mails en cas d'abandon d'un lead

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

// deleting all aborted leads older than one day
$delete = Doctrine_Query::create()
        ->delete('ContactsAborted')
        ->where('create_time < ? AND recall_mail_sent = 1', (time()-60*60*24))
        ->orWhere('create_time < ?', (time()-60*60*7*24));
        $delete->execute();

// send mail for leads
$abortedLeadsList = Doctrine_Query::create()
        ->select('ca.*, pfr.name')
        ->from('ContactsAborted ca')
        ->leftJoin('ca.productFr pfr')
        ->where('timestamp < ?', time()-3600)
        ->andWhere('recall_mail_sent = 0')
        ->execute();

foreach($abortedLeadsList as $abortedLead){
 /* Nom expéditeur : Techni-Contact - Service client
Mail expéditeur : prob-devis@techni-contact.com
Nom retour :  Service client - Techni-Contact
Mail retour : prob-devis@techni-contact.com
Objet : Votre devis pour Nom du produit
    
    
    Les 2 liens pointent :
 Les 2 liens pointent vers le form de lead abandonné, rajouter le tracker suivant à la fin du 
          lien ?utm_source=email&utm_medium=email&utm_campaign=relance-lead-abandonne&campaignID=888888
  * 
  * 
*/
//prévoir chargement des données dans le formulaire + ajax searche 1-idprod-abc
  $mailContent = array(
      'email' => $abortedLead->email,
      'subject' => "Votre devis pour ".$abortedLead->productFr->name,
      'headers' => "From: Techni-Contact - Service client <prob-devis@techni-contact.com>\nReply-to: Techni-Contact - Service client <prob-devis@techni-contact.com>\n",
      'template' => "customer-aborted_lead-lead",
      'data' => array(
          ABORTED_LEAD_LINK => URL.'lead'.($abortedLead->advertiser->category == __ADV_CAT_SUPPLIER__ ? '-f':'-a').'.html?pdtID='.$abortedLead->idProduct.'&catID='.$abortedLead->idFamily.'&alID='.$abortedLead->web_id.'&utm_source=email&utm_medium=email&utm_campaign=relance-lead-abandonne&campaignID=888888',
          PRODUCT_NAME => $abortedLead->productFr->name,
      )
  );
  $mail = new Email($mailContent);
  if(!empty($abortedLead->email)){
    $mail->send();
    $abortedLead->recall_mail_sent = 1;
    $abortedLead->save();
  }
}
?>
