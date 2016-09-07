<?php
/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 28 février 2011

 Fichier : /cron/fr/partners-orders-warnings.php
 Description : cron d'envoi de mails de relance des ordres fournisseurs

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(EXTRANET  . 'logs.php');

$now = time();
$twoDaysAgo = $now-172800; // prod version
//$twoDaysAgo = $now-960; // test version
$quarterEnds = $twoDaysAgo+900;

$orders = new OrderCollectionOld();

$ordersInWarning = $orders->getInWarning();

foreach ($ordersInWarning as $order){
  if($order['timestampIMS'] != 0){
  $extranetUser = new AdvertiserOld($order['idAdvertiser']);

    switch ( $order['statut_traitement']){
      case 0:
      case 1:
      case 2://fiche commande non visitée via le mail de notification ou via la liste des commandes
          if($order['timestampIMS'] >= $twoDaysAgo && $order['timestampIMS'] <= $quarterEnds){
            // mail
            $arrayEmail = array();
            $advertiser = new AdvertiserOld($order['idAdvertiser']);
            $arrayEmail = array(
              "email" => $advertiser->email,
              "subject" => "Relance concernant la commande ".$order['idAdvertiser']."-".$order['idCommande'],
              "headers" => "From: Service Achat Techni-Contact <achat@techni-contact.com>\nReply-To: Service Achat Techni-Contact <achat@techni-contact.com>\r\n",
              "template" => "advertiser-bo_orders-read_or_arc_warnings",
              "data" => array(
                "EXP_DATE" => date('d/m/Y à H:i:s', $order['timestampIMS']),
                'CMD_LINK' => EXTRANET_URL.'commande.html?idCommande='.$order['idAdvertiser']."-".$order['idCommande'].'&uid='.$extranetUser->webpass,
              )
            );
            
            $mail = new Email($arrayEmail);
            $mail->send();
          }
        break;

//      case 3://commande lue et validée dont l'ARC n'a pas encore été lié
//        if($order['timestampSeen'] >= $twoDaysAgo && $order['timestampSeen'] <= $quarterEnds){
//            // mail
//            $arrayEmail = array();
//            $advertiser = new AdvertiserOld($order['idAdvertiser']);
//            $arrayEmail = array(
//              "email" => $advertiser->email,
//              "subject" => "Relance concernant la commande ".$order['idAdvertiser']."-".$order['idCommande'],
//              "headers" => "From: Service Achat Techni-Contact <achat@techni-contact.com>\nReply-To: Service Achat Techni-Contact <achat@techni-contact.com>\r\n",
//              "template" => "advertiser-bo_orders-read_or_arc_warnings",
//              "data" => array(
//                "EXP_DATE" => date('d/m/Y à H:i:s', $order['timestampIMS']),
//                'CMD_LINK' => EXTRANET_URL.'commande.html?idCommande='.$order['idAdvertiser']."-".$order['idCommande'].'&uid='.$extranetUser->webpass,
//              )
//            );
//
//            $mail = new Email($arrayEmail);
//            $mail->send();
//            echo 'mail send';
//          }
//        break;

    }
  }
}

