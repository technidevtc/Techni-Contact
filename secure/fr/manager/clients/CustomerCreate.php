<?php
/*================================================================/
 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com
 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 2 avril 2006
 Mises à jour :
 Fichier : /secure/manager/tva/index.php
 Description : Accueil gestion des taux de TVA
/=================================================================*/

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

require_once(ADMIN."logs.php");
require_once(ICLASS . '_ClassCustomer.php');

$handle = DBHandle::get_instance();
$user = new BOUser();

if (!$user->login()) {
	print "CustomerCE" . __ERRORID_SEPARATOR__ . "Votre session a expirée, vous devez vous relogger." . __ERROR_SEPARATOR__ . __MAIN_SEPARATOR__;
	exit();
}
if (!$user->get_permissions()->has("m-comm--sm-customers","e")) {
  print "CustomerCE" . __ERRORID_SEPARATOR__ . "Vous n'avez pas les droits adéquats pour réaliser cette opération." . __ERROR_SEPARATOR__ . __MAIN_SEPARATOR__;
  exit();
}

$sid = session_name() . '=' . session_id();

$errorstring = $outputstring = '';

$tab_cpi = array(); $es_cpi = '';
$isValid = array();
$isValid['Login'] = false;
$tab_cpi['email']    = isset($_POST['email']) ?    trim($_POST['email']) : '';

// Vérification de la validité des différentes informations
// nom des id : type d'informations + C(reate) + E(rror)
$es_ai = '';

if (preg_match('/^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,15})$/', $tab_cpi['email'])) {
	$result = $handle->query("select id from clients where login = '" . $handle->escape($tab_cpi['email']) . "'", __FILE__, __LINE__);
	if ($handle->numrows($result, __FILE__, __LINE__) != 0)
    $es_ai .= "- Cette adresse email existe déjà<br />\n";
	else
    $isValid['Login'] = true;
} else {
	$es_ai .= "- Adresse email invalide<br />\n";
}

$Login = $tab_cpi['email'];

if ($es_ai != '')
  $errorstring .= "AccountInfosCE" . __ERRORID_SEPARATOR__ . $es_ai . "<br />\n" . __ERROR_SEPARATOR__;

// Informations Personnelles du client
$tab_cpi = array(); $es_cpi = '';
$tab_cpi['titre']    = isset($_POST['titre']) ?    substr(trim($_POST['titre']), 0, 255) : '';
$tab_cpi['nom']      = isset($_POST['nom']) ?      strtoupper(substr(trim($_POST['nom']), 0, 255)) : '';
$tab_cpi['prenom']   = isset($_POST['prenom']) ?   ucfirst(substr(trim($_POST['prenom']), 0, 255)) : '';
$tab_cpi['fonction'] = isset($_POST['fonction']) ? ucfirst(substr(trim($_POST['fonction']), 0, 255)) : '';
$tab_cpi['service']  = isset($_POST['service']) ? ucfirst(substr(trim($_POST['service']), 0, 255)) : '';
$tab_cpi['tel1']     = isset($_POST['tel1']) ?     trim($_POST['tel1']) : '';
$tab_cpi['fax1']     = isset($_POST['fax1']) ?     trim($_POST['fax1']) : '';
$tab_cpi['email']    = isset($_POST['email']) ?    trim($_POST['email']) : '';
$tab_cpi['website_origin'] = isset($_POST['website_origin']) ? trim($_POST['website_origin']) : '';



$hidden = !!$_POST['hidden']; // hidden account ?

if ($tab_cpi['tel1'] == '')
	$es_cpi .= "- Au moins un numéro de téléphone est nécessaire pour valider le compte client<br />\n";

if ($tab_cpi['titre'] != '1' && $tab_cpi['titre'] != '2' && $tab_cpi['titre'] != '3')
	$es_cpi .= "- Le titre choisi n'existe pas<br />\n";

if ($tab_cpi['nom'] == '')
	$es_cpi .= "- Vous n'avez pas saisi le nom<br />\n";

if ($tab_cpi['prenom'] == '')
	$es_cpi .= "- Vous n'avez pas saisi le prénom<br />\n";

if ($tab_cpi['email'] != '' && !preg_match('`^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,15})$`', $tab_cpi['email']))
	$es_cpi .= "- Adresse email invalide<br />\n";

if (!isset($website_origin_list[$tab_cpi['website_origin']]))
  $es_cpi .= "- Site d'origine invalide<br />\n";

if ($es_cpi != '') $errorstring .= "CustomerInfosCE" . __ERRORID_SEPARATOR__ . $es_cpi . "<br />\n" . __ERROR_SEPARATOR__;

// Informations sur la Société
$tab_ci = array(); $es_ci = '';
$tab_ci['societe']          = isset($_POST['societe']) ?          ucfirst(substr(trim($_POST['societe']), 0, 255)) : '';
$tab_ci['nb_salarie']       = isset($_POST['nb_salarie']) ?       substr(trim($_POST['nb_salarie']), 0, 255) : '';
$tab_ci['secteur_activite'] = isset($_POST['secteur_activite']) ? substr(trim($_POST['secteur_activite']), 0, 255) : '';
$tab_ci['secteur_qualifie'] = isset($_POST['secteur_qualifie']) ? substr(trim($_POST['secteur_qualifie']), 0, 255) : '';
$tab_ci['code_naf']         = isset($_POST['code_naf']) ?         substr(trim($_POST['code_naf']), 0, 255) : '';
$tab_ci['num_siret']        = isset($_POST['num_siret']) ?        substr(trim($_POST['num_siret']), 0, 255) : '';
$tab_ci['tva_intra']        = isset($_POST['tva_intra']) ?        substr(trim($_POST['tva_intra']), 0, 255) : '';

if ($tab_ci['societe'] == '') {
  $es_ci .= "- Vous n'avez pas saisi le nom de la société <br />\n";
} else {
  // restraining activity sector and naf
  $terms = preg_replace('/ de /', ' ', $tab_ci["societe"]);
  $terms = explode(' ', $terms);
  $ActivitySector = Doctrine_Core::getTable('ActivitySectorSurqualification');
  $ActivitySector->batchUpdateIndex();
  $array_results = array();
  
  foreach ($terms as $term){
    $term = Utils::toDashAz09($term);
    if($result = $ActivitySector->search($term)){
      $q = Doctrine_Query::create()
        ->from('ActivitySector as')
        ->leftJoin('as.Surqualifications ass')
        ->where('ass.id = ?', $result[0]['id']);

      $array_results[$result[0]['id']] = $result[0]['id'];
      $sector = $q->fetchArray();
      $results[] = $result;
    }
  }

  if (count($array_results) == 1){
    $tab_ci["secteur_activite"] = $sector[0]['sector'];
    $tab_ci["secteur_qualifie"] = $sector[0]['Surqualifications'][0]['qualification'];
    $tab_ci["code_naf"] = $sector[0]['Surqualifications'][0]['naf'];
  }
}

if ($es_ci != '')
  $errorstring .= "CompanyInfosCE" . __ERRORID_SEPARATOR__ . $es_ci . "<br />\n" . __ERROR_SEPARATOR__;

// Coordonnées (facturation)
$tab_ba = array(); $es_ba = '';
$tab_ba['adresse']    = isset($_POST['adresse']) ?    substr(trim($_POST['adresse']), 0, 255) : '';
$tab_ba['complement'] = isset($_POST['complement']) ? substr(trim($_POST['complement']), 0, 255) : '';
$tab_ba['ville']      = isset($_POST['ville']) ?      strtoupper(substr(trim($_POST['ville']), 0, 255)) : '';
$tab_ba['cp']         = isset($_POST['cp']) ?         substr(trim($_POST['cp']), 0, 5) : '';
$tab_ba['pays']       = isset($_POST['pays']) ?       strtoupper(substr(trim($_POST['pays']), 0, 255)) : '';

if ($tab_ba['adresse'] == '')
	$es_ba .= "- Vous n'avez pas saisi l'adresse<br />\n";

if ($tab_ba['ville'] == '')
	$es_ba .= "- Vous n'avez pas saisi la ville<br />\n";

if ($tab_ba['cp'] == '' || !preg_match('/^[0-9]+$/', $tab_ba['cp']))
	$es_ba .= "- Le code postal saisi est invalide<br />\n";

if ($tab_ba['pays'] == '')
	$es_ba .= "- Vous n'avez pas saisi le pays<br />\n";

if ($es_ba != '') $errorstring .= "BillingAddressCE" . __ERRORID_SEPARATOR__ . $es_ba . "<br />\n" . __ERROR_SEPARATOR__;

// Coordonnées (livraison)
$tab_sa = array(); $es_sa = '';
if (isset($_POST['coord_livraison'])) {
	if ($_POST['coord_livraison'] == '1') {
		$tab_sa['coord_livraison'] = 1;
		$tab_sa['titre_l']      = isset($_POST['titre_l']) ?      substr(trim($_POST['titre_l']), 0, 255) : '';
		$tab_sa['nom_l']        = isset($_POST['nom_l']) ?        strtoupper(substr(trim($_POST['nom_l']), 0, 255)) : '';
		$tab_sa['prenom_l']     = isset($_POST['prenom_l']) ?     ucfirst(substr(trim($_POST['prenom_l']), 0, 255)) : '';
		$tab_sa['societe_l']    = isset($_POST['societe_l']) ?    ucfirst(substr(trim($_POST['societe_l']), 0, 255)) : '';
		$tab_sa['adresse_l']    = isset($_POST['adresse_l']) ?    substr(trim($_POST['adresse_l']), 0, 255) : '';
		$tab_sa['complement_l'] = isset($_POST['complement_l']) ? substr(trim($_POST['complement_l']), 0, 255) : '';
		$tab_sa['ville_l']      = isset($_POST['ville_l']) ?      strtoupper(substr(trim($_POST['ville_l']), 0, 255)) : '';
		$tab_sa['cp_l']         = isset($_POST['cp_l']) ?         substr(trim($_POST['cp_l']), 0, 5) : '';
		$tab_sa['pays_l']       = isset($_POST['pays_l']) ?       strtoupper(substr(trim($_POST['pays_l']), 0, 255)) : '';
                $tab_sa['tel2']     = isset($_POST['tel2']) ?     trim($_POST['tel2']) : '';

		if ($tab_sa['titre_l'] != '1' && $tab_sa['titre_l'] != '2' && $tab_sa['titre_l'] != '3')
			$es_sa .= "- Le titre choisi n'existe pas<br />\n";

		if ($tab_sa['prenom_l'] == '' || $tab_sa['nom_l'] == '' || $tab_sa['societe_l'] == '')
			$es_sa .= "- Vous n'avez pas saisi les nom et prénom, ou le nom de la société<br />\n";

		if ($tab_sa['adresse_l'] == '')
			$es_sa .= "- Vous n'avez pas saisi l'adresse<br />\n";

		if ($tab_sa['ville_l'] == '')
			$es_sa .= "- Vous n'avez pas saisi la ville<br />\n";

		if ($tab_sa['cp_l'] == '' || !preg_match('/^[0-9]+$/', $tab_sa['cp_l']))
			$es_sa .= "- Le code postal est invalide<br />\n";

                if ($tab_sa['tel2'] != '')
                  if(!preg_match('/^[0-9]{10}$/', $tab_sa['tel2']))
                        $es_sa .= "- Le téléphone de livraison saisi est invalide<br />\n";

		if ($tab_sa['pays_l'] == '')
			$es_sa .= "- Vous n'avez pas saisi le pays<br />\n";

		if ($es_sa != '') $errorstring .= "ShippingAddressCE" . __ERRORID_SEPARATOR__ . $es_sa . "<br />\n" . __ERROR_SEPARATOR__;
	
  } else {
		$tab_sa['coord_livraison'] = 0;
		$tab_sa['titre_l'] = $tab_cpi['titre'];
		$tab_sa['nom_l'] = $tab_cpi['nom'];
		$tab_sa['prenom_l'] = $tab_cpi['prenom'];
		$tab_sa['societe_l'] = $tab_ci['societe'];
		$tab_sa['adresse_l'] = $tab_ba['adresse'];
		$tab_sa['complement_l'] = $tab_ba['complement'];
		$tab_sa['ville_l'] = $tab_ba['ville'];
		$tab_sa['cp_l'] = $tab_ba['cp'];
		$tab_sa['pays_l'] = $tab_ba['pays'];
        $tab_sa['tel2'] = $tab_ba['tel2'];
	}
} else {
	$es_sa = "Il n'a pas été spécifié si les coordonnées de livraison sont les mêmes que celles de facturation ou pas<br />\n";
	$errorstring .= "ShippingAddressCE" . __ERRORID_SEPARATOR__ . $es_sa . "<br />\n" . __ERROR_SEPARATOR__;
}

if ($es_cpi == '' && $es_ci == '' && $es_ba == '' && $es_sa == '')
  $isValid['Infos'] = true;


// S'il y a quelque chose à faire
if (isset($isValid['Login']) && $isValid['Login'] == true && isset($isValid['Infos'])) {

	$es_v = '';
  
  // automatically create an account if email does not already exists in the db
  if (!(CustomerUser::getCustomerIdFromLogin($Login, $handle))) {
    $customer = new CustomerUser($handle);

    foreach ($tab_cpi as $prop => $value) $infos[$prop] = $value;
    foreach ($tab_ci as $prop => $value) $infos[$prop] = $value;
    foreach ($tab_ba as $prop => $value) $infos[$prop] = $value;
    foreach ($tab_sa as $prop => $value) $infos[$prop] = $value;

    $pass = $customer->generatePassword();

    $accinfos = array(
      "coord_livraison" => $infos["coord_livraison"],
      "login" => $Login,
      "pass" => md5($pass),
      "titre" => $infos["titre"],
      "nom" => $infos["nom"],
      "prenom" => $infos["prenom"],
      "fonction" => $infos["fonction"],
      "societe" => $infos["societe"],
      "nb_salarie" => $infos["nb_salarie"],
      "secteur_activite" => $infos["secteur_activite"],
      "secteur_qualifie" => $infos["secteur_qualifie"],
      "code_naf" => $infos["code_naf"],
      "num_siret" => $infos["num_siret"],
      "tva_intra" => $infos["tva_intra"],
      "adresse" => $infos["adresse"],
      "complement" => $infos["complement"],
      "ville" => $infos["ville"],
      "cp" => $infos["cp"],
      "pays" => $infos["pays"],
      "tel1" => $infos["tel1"],
      "tel2" => $infos["tel2"],
      "fax1" => $infos["fax1"],
      "actif" => 1,
      "titre_l" => $infos["titre_l"],
      "nom_l" => $infos["nom_l"],
      "prenom_l" => $infos["prenom_l"],
      "societe_l" => $infos["societe_l"],
      "adresse_l" => $infos["adresse_l"],
      "complement_l" => $infos["complement_l"],
      "ville_l" => $infos["ville_l"],
      "cp_l" => $infos["cp_l"],
      "pays_l" => $infos["pays_l"],
      "email" => $Login,
      "website_origin" => $infos["website_origin"] 
	  );
//    var_dump($accinfos);exit;
    $customer->create();
    $customer->setCoordFromArray($accinfos);
    
    // always hidden if website origin is not TC
    $hidden = $hidden || $infos['website_origin'] != 'TC';
    
    if ($hidden) {
      $customer->origin = 'A'; // 'A' was historically an automatically created account from an old lead, we use its behavior
      $customer->actif = 0; // disabled by default
      $customer->pass = ""; // no need for a pass
    }
    
    $customer->code = "9".substr(strtoupper(Utils::toASCII($customer->societe)),0,4).substr($customer->id,0, 6);
    $customer->save(time()-120);  // on definit un timestamp antidaté de 120 secondes pour corriger le probleme 'Connexion Table clients / contact'
                                  // de la tache Hors Lot - Aout à Décembre 2010  - OD 10/12/2010
    if($customer->exists)
      $outputstring .= $customer->id;
    elseif($errorstring)
      $es_v .= $errorstring. "\n";

    // sending mail
    if (!$hidden) {
      $arrayMail = array(
          "email" => $customer->email,
          "subject" => "Création de votre compte",
          "headers" => "From: Service client Techni-Contact <web@techni-contact.com>\n",
          "template" => "user-bo_clients-create_account",
          "data" => array(
            "CUSTOMER_ID" => $customer->id,
            "CUSTOMER_FIRSTNAME" => $customer->prenom,
            "CUSTOMER_LASTNAME" => $customer->nom,
            "CUSTOMER_EMAIL" => $customer->email,
            "CUSTOMER_PASSWORD" => $pass,
            "SITE_MAIN_URL" => URL,
            "SITE_HELP_URL" => URL."aide.html",
            "SITE_ACCOUNT_URL_LOGIN" => COMPTE_URL."login.html"
          )
        );
      $mail = new Email($arrayMail);
      $mail->Send();
    }
  }
  
  $sql_max  = "SELECT id FROM clients WHERE login='".$Login."'";
  $req_max  = mysql_query($sql_max);
  $data_max = mysql_fetch_object($req_max);

  $sql_update = "UPDATE  `clients` SET  `fonction_service` =  '".$tab_cpi['service']."' WHERE  `id` ='".$data_max->id."' ";
  mysql_query($sql_update);

  if ($es_v != '')
    $errorstring .= "CustomerCE" . __ERRORID_SEPARATOR__ . $es_v . "\n" . __ERROR_SEPARATOR__;
}

print $errorstring . __MAIN_SEPARATOR__ . $outputstring;

