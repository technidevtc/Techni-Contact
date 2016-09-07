<?php

/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 03 février 201

 Fichier : /secure/manager/orders/AJAX_cancelOrder.php
 Description : Fichier interface de confirmation d'annulation de l'ordre

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN."logs.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

$o = array();

if(!$user->login())
{
	$o['error'] = "Votre session a expirée, veuillez réactualiser la page pour retourner à la page de login";
}
else
{

  if (!$user->get_permissions()->has("m-comm--sm-partners-orders","e")) {
    print "ProductsError".__ERRORID_SEPARATOR__."Vous n'avez pas les droits adéquats pour réaliser cette opération".__ERROR_SEPARATOR__.__MAIN_SEPARATOR__;
    exit();
  }


  if(isset($_POST['idOrdre']) && isset($_POST['idAdvertiser']) && is_numeric($_POST['idOrdre']) && is_numeric($_POST['idAdvertiser']) && $_POST['idOrdre'] > 0 && $_POST['idAdvertiser'] > 0){

    $idOrdre = $_POST['idAdvertiser'].'-'.$_POST['idOrdre'];

      if (!preg_match('/^[1-9]{1}[0-9]{0,8}\-[1-9]{1}[0-9]{0,8}$/', $idOrdre) || !isset($_POST['action']) || $_POST['action'] != 'remove')
      {
        $o['error'] = "Requête incorrecte";
          exit();
      }

    $ordre = new OrderOld($handle, $idOrdre);

	if (!$ordre->exists) {
		$o['error'] = "Ordre inexistant";
                exit();
	}  else {
           if($ordre->cancelOrder($_POST['motif'])){
            $o['annulation'] = "Annulation OK";

            $advertiser = new AdvertiserOld($ordre->idAdvertiser);

            // mail
            $arrayEmail = array(
              "email" => $advertiser->email,
              "subject" => "Annulation de la commande ".$idOrdre,
              "headers" => "From: Service Achat Techni-Contact <achat@techni-contact.com>\nReply-To: Service Achat Techni-Contact <achat@techni-contact.com>\r\n",
              "template" => "advertiser-bo_orders-order_cancelled",
              "data" => array(
                'LINK' => EXTRANET_URL.'commande.html?idCommande='.$idOrdre,
                'CMD_NUMBER' => $idOrdre,
                'CANCEL_MOTIVE' => $_POST['motif']
              )
            );

            $mail = new Email($arrayEmail);
            $mail->send();
            
            /**
             * record cancellation as message in messenger : http://www.hook-network.com/storm/tasks/2011/09/28/petites-taches-4 -> OD 11/10/2011
             */
            $context = __MSGR_CTXT_SUPPLIER_TC_ORDER__;
            $contenu = utf8_encode(' Ordre fournisseur annulé le '.date('d/m/Y', time()).' à '.date('H:i', time()).' par '.$user->name.'
              Raison : ').$_POST['motif'];
            $messagerie = new MessengerOld($handle, $user, $context);
            if(!$messagerie->sendMessageToManagerUser($contenu, $advertiser->id, $ordre->idCommande))
              $o["error"] = "Erreur d'enregistrement de l'annulation en tant qu'entrée de messagerie";

           }else
            $o['error'] = "Erreur d'annulation, un motif doit être indiqué";

        }

  }
 
}

    mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
        print json_encode($o);
?>