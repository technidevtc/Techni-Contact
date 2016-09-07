<?php
/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 14/06/2012 OD

 Fichier : /secure/fr/manager/ressources/ajax/AJAXQuickAccountCreation.php
 Description : requete ajax de vérifications et de création rapide d'un compte client

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = DBHandle::get_instance();
$session = new UserSession($db);

// Arrays for customers and errors information
$error = array();

$infos['nom'] = isset($_POST['nom']) ? substr(trim($_POST['nom']), 0, 255) : '';
$infos['prenom'] = isset($_POST['prenom']) ? substr(trim($_POST['prenom']), 0, 255) : '';
$infos['societe'] = isset($_POST['societe']) ? substr(trim($_POST['societe']), 0, 255) : '';
$infos['email'] = isset($_POST['email']) ? substr(trim($_POST['email']), 0, 255) : '';
$infos['pass'] = isset($_POST['pass']) ? substr(trim($_POST['pass']), 0, 255) : '';
$infos['pass2'] = isset($_POST['pass2']) ? substr(trim($_POST['pass2']), 0, 255) : '';

// Always required fields
if (empty($infos['nom']))
  $error['nom'] = true;
else
  setCookie('nom', $infos['nom'], time() + 24 * 3600 * 365, '/', DOMAIN);

if (empty($infos['prenom']))
  $error['prenom'] = true;
else
  setCookie('prenom', $infos['prenom'], time() + 24 * 3600 * 365, '/', DOMAIN);

if (empty($infos['societe']))
  $error['societe'] = true;
else
  setCookie('societe', $infos['societe'], time() + 24 * 3600 * 365, '/', DOMAIN);
  
if (!preg_match('`^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$`', $infos['email']))
  $error['email'] = true;
else
  setCookie('email', $infos['email'], time() + 24 * 3600 * 365, '/', DOMAIN);

if (empty($infos['pass']))
  $error['pass'] = true;

if (empty($infos['pass2']) || $infos['pass'] != $infos['pass2'])
  $error['pass2'] = true;

if (empty($error) && !isset($_POST['onlyCheck'])) {

  if (!($user_id = CustomerUser::getCustomerIdFromLogin($infos['email'], $db))) {
    $user = new CustomerUser($db);
    // setting some required info to '-' and default country to "FRANCE"
    $accinfos = array(
      'coord_livraison' => 0,
      'login' => $infos['email'],
      'titre' => 1,
      'nom' => $infos['nom'],
      'prenom' => $infos['prenom'],
      'societe' => $infos['societe'],
      'tel1' => "-",
      'adresse' => "-",
      'cp' => "-",
      'ville' => "-",
      'pays' => "FRANCE",
      'actif' => 1,
      'email' => $infos['email'],
      'pass' => md5($infos['pass'])
    );
    $user->create();
    $user->setCoordFromArray($accinfos);
    $user->code = "9".substr(strtoupper(Utils::toASCII(utf8_encode($accinfos['societe']))),0,4).substr($user->id,0, 6);
    $user->save(time()-120); // on definit un timestamp antidaté de 120 secondes pour corriger le probleme 'Connexion Table clients / contact'
    // de la tache Hors Lot - Aout à Décembre 2010  - OD 10/12/2010

    $customerID = $user->canLogin($user->login, $infos['pass'], $db);
    
    // Log in
    if ($customerID) {
      $session->login($customerID);
      $session->userFirstName = $infos['nom'];
      $session->userName = $infos['prenom'];
      $session->userEmail = $infos['email'];
    }

    //require(ICLASS.'_ClassEmail.php');
    // sending mail
    $mailContent = array(
                            'email' => $user->email,
                            'subject' => "Création de votre compte gratuit",
                            'headers' => "From: Techni-Contact – Service clients <commandes@techni-contact.com>\nReply-To: Techni-Contact – Service clients <commandes@techni-contact.com>\n",
                            'template' => "customer-creation-compte",
                            'data' => array(
        'CUSTOMER_ID' => $user->id,
        'CUSTOMER_FIRSTNAME' => $user->prenom,
        'CUSTOMER_LASTNAME' => $user->nom,
        'CUSTOMER_EMAIL' => $user->email,
        'CUSTOMER_PASSWORD' => $infos['pass'],
        'SITE_MAIN_URL' => URL,
        'SITE_HELP_URL' => URL."aide.html",
        'SITE_ACCOUNT_URL_LOGIN' => COMPTE_URL."login.html"
      )
                        );
    $mail = new Email($mailContent);
    /*$mail->Build(
      "Création de votre compte",
      "",
      "creation-compte",
      "From: Service client Techni-Contact <web@techni-contact.com>\n",
      array(
        'CUSTOMER_ID' => $user->id,
        'CUSTOMER_FIRSTNAME' => $user->prenom,
        'CUSTOMER_LASTNAME' => $user->nom,
        'CUSTOMER_EMAIL' => $user->email,
        'CUSTOMER_PASSWORD' => $infos['pass'],
        'SITE_MAIN_URL' => URL,
        'SITE_HELP_URL' => URL."aide.html",
        'SITE_ACCOUNT_URL_LOGIN' => COMPTE_URL."login.html"
      ),
      'user'
    );*/
    $mail->send();
    //$mail->Save();
    
    echo 'createOk';
  }
  else { // account already exists
    echo 'alreadyExists';
  }

} else { // has error or only checking
  if (!empty($error)) {
    echo implode("|", array_keys($error));
  } else {
    echo 'checkOk';
  }
}
