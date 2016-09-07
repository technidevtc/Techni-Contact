<?php
/* ================================================================/

  Techni-Contact V3 - MD2I SAS
  http://www.techni-contact.com

  Auteur : Hook Network SARL - http://www.hook-network.com
  Date de création : 15 juillet 2011

  Fichier : /secure/manager/clients/AJAX_password-resend.php
  Description : Procédure ajax de redéfinition du mot de passe d'un client

  /================================================================= */

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

if(isset($_POST["idClient"]) && isset($_POST['action']) && $_POST['action'] == 'rst_pwd'){

  require(ICLASS . 'CCustomerUser.php');

  $handle = DBHandle::get_instance();

  $customer = new CustomerUser($handle, $_POST["idClient"]);
  if(!$customer->exists){
      $o["error"] = "Client introuvable";
      print json_encode($o);
      exit();
  }

  $email = $customer->email;
  $errorstring = "";
  $mail_sent = false;

  if (!empty($email)) {
          if (strlen($email) >= 6 && preg_match('`^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$`', $email)) {

                  $pass = $customer->generatePassword();

                  $mail_data = array(
                          "email" => $email,
                          "subject" => "Identifiants d'accès à votre compte",
                          "headers" => "From: Techni-Contact  - Service client <sav@techni-contact.com>\nReply-To: sav@techni-contact.com\r\n",
                          "template" => "customer-fo_bo-recuperation_password",
                          "data" => array(
                            "SITE_MAIN_URL" => URL,
                            "SITE_ACCOUNT_URL_INFOS" => COMPTE_URL."infos.html",
                            "CUSTOMER_EMAIL" => $email,
                            "CUSTOMER_NAME" => $customer->prenom.' '.$customer->nom,
                            "CUSTOMER_LOGIN" => $customer->login,
                            "CUSTOMER_PASSWORD" => $pass)
                          );

                  $mail = new Email($mail_data);

                  if ($mail->send())
                          $mail_sent = true;
                  else
                          $o['error'] = "Une erreur interne est survenue lors de la procédure.";

                  $customer->save();
                  $o['reponse'] = 'La modification du mot de passe a été effectuée';
                  
                  if($mail_sent){
                    $o['reponse'] .= '<br />et le client a été prévenu par mail ';
                  }
                  mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
                  print json_encode($o);

          }
          else {
            $o["error"] = "Client introuvable";
            print json_encode($o);
            exit();
          }
  }else {
      $o["error"] = "Email client absent";
      print json_encode($o);
      exit();
    }
}

?>