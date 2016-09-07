<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

if($_GET){
  $faxNumber = filter_input(INPUT_GET, 'faxNumber', FILTER_SANITIZE_NUMBER_INT);
  $itemId = filter_input(INPUT_GET, 'itemId', FILTER_SANITIZE_NUMBER_INT);
  $context = filter_input(INPUT_GET, 'context', FILTER_SANITIZE_STRING);
  
  if(!empty($faxNumber) && !empty($itemId) && !empty($context)){

    switch($context){
      case 'estimate' :
        
        $estimate = Doctrine_core::getTable('Estimate')->find($itemId);
        if(empty($estimate))
          $error = 'Devis introuvable';
        else{
          $web_id = $estimate->web_id;
          require WWW_PATH.'pdf/estimate.php';
          
          $faxDestinataire = $faxNumber;
          $mdp = '5TeQywpf';
          $faxSender = '0183623612';
          /*if(TEST){
            $faxDestinataire = '0955065445';//33.(0)9.55.06.54.45 // fax de test chez HN
            $faxSender = '0183623612';
          };*/
          if(!preg_match('/^\d{10}$/', $faxDestinataire))
            $error = 'Le numéro de fax '.$faxDestinataire.' est incorrect';
          else{
            //$faxDestinataire = '0155600591'; // fax de test chez TC
            $desti = $faxDestinataire.'@ecofax.fr';
            //$desti = 'olivier@hook-network.com';
            $from = 'fax@techni-contact.com';
            $body = 'password :'.$mdp;
            $arrayMail = array(
              "email" => $desti,
              "subject" => $faxSender,
              "headers" => "From: ".$from."\r\n",// \rContent-Type: multipart/mixed;
              "template" => "fax-bo-send_fax",
              "data" => array(
                "PASSWORD" => $body
              )
            );

            $mail = new Email($arrayMail);
            if(is_file(PDF_ESTIMATE."Devis commercial ".$itemId.'.pdf')){
              $mail->addAttachment(PDF_ESTIMATE."Devis commercial ".$itemId.'.pdf');
              $envoiMail = $mail->send();
              $error = ($envoiMail === true) ? '' : 'Erreur à l\'envoi du fax'; 
            }else
              $error = 'Fichier Devis commercial '.$itemId.'.pdf introuvable';
            
          }
        }
        break;
      default :
        $error = 'Contexte incorrect';
        break;
    }
  }else 
    $error = 'Information absente';
  
  if(!empty($error))
    echo json_encode(array('error' => $error));
  else
    echo json_encode(array('Fax envoxé avec succès'));
}
else {
  echo json_encode(array());
}
?>
