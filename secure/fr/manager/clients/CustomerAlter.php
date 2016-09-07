<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 2 avril 2006

 Mises à jour :

 Fichier : /secure/manager/clients/CustomerAlter
 Description : Modification des données de client

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

//header("Content-Type: text/html; charset=iso-8859-15"); 

if(!$user->login())
{
  print "CustomerError" . __ERRORID_SEPARATOR__ . "Votre session a expirée, vous devez vous relogger." . __ERROR_SEPARATOR__ . __MAIN_SEPARATOR__;
  exit();
}
if (!$user->get_permissions()->has("m-comm--sm-customers","e")) {
  print "CustomerError" . __ERRORID_SEPARATOR__ . "Vous n'avez pas les droits adéquats pour réaliser cette opération." . __ERROR_SEPARATOR__ . __MAIN_SEPARATOR__;
  exit();
}

$sid = session_name() . '=' . session_id();

function GetTitle($val)
{
  switch ($val)
  {
    case 1  : return 'M.';
    case 2  : return 'Mme';
    case 3  : return 'Mlle';
    default : return 'M.';
  }
}

function GetTitleNum($val)
{
  switch ($val)
  {
    case 'M.'   : return 1;
    case 'Mme' : return 2;
    case 'Mlle'  : return 3;
    default : return 1;
  }
}

$customerID = isset($_GET['customerID']) ? $_GET['customerID'] : '';

$errorstring = $outputstring = '';

if (preg_match('/^\d+$/', $customerID))
{

  //header("content-type: text/css; charset=iso-8859-1");
  $alterInfos = isset($_GET['alterInfos']) ? $_GET['alterInfos'] : '';
  $alterLogin = isset($_GET['alterLogin']) ? $_GET['alterLogin'] : '';
  $alterCode = isset($_GET['alterCode']) ? $_GET['alterCode'] : '';
  $alterPassword = isset($_GET['alterPassword']) ? $_GET['alterPassword'] : '';
  $toggleActiveState = isset($_GET['toggleActiveState']) ? $_GET['toggleActiveState'] : '';
  $alterCreate_time = isset($_GET['alterCreate_time']) ? $_GET['alterCreate_time'] : '';
  
  $isValid = array();
  
  // Vérification de la validité des différentes informations
  if ($alterLogin != '')
  {
    if(preg_match('`^[[:alnum:]]([-_.]?[[:alnum:]])*(@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,15}))?$`', $alterLogin))
      $isValid['alterLogin'] = true;
    else
      $errorstring .= "InfosError" . __ERRORID_SEPARATOR__ . "Le login saisi est invalide" . __ERROR_SEPARATOR__;
  }
  
  if ($alterCode != '')
  {
    if(preg_match('`^[[:alnum:]]+$`', $alterCode))
      $isValid['alterCode'] = true;
    else
      $errorstring .= "InfosError" . __ERRORID_SEPARATOR__ . "Le code saisi est invalide" . __ERROR_SEPARATOR__;
  }
  
  if ($toggleActiveState != '')
  {
    $isValid['toggleActiveState'] = true;
  }
  
  if ($alterPassword != '')
  {
    $alterPasswordCheck = isset($_GET['alterPasswordCheck']) ? $_GET['alterPasswordCheck'] : '';
    
    if(!preg_match('/^[[:alnum:]]{8,12}$/', $alterPassword))
      $errorstring .= "PasswordError" . __ERRORID_SEPARATOR__ . "Le mot de passe saisie n'est pas valide. Vous devez saisir un mot de passe contenant entre 8 et 12 lettres ou chiffres" . __ERROR_SEPARATOR__;
    elseif ($alterPasswordCheck == '')
      $errorstring .= "PasswordError" . __ERRORID_SEPARATOR__ . "Vous n'avez pas saisi le mot de passe une deuxième fois" . __ERROR_SEPARATOR__;
    elseif ($alterPassword != $alterPasswordCheck)
      $errorstring .= "PasswordError" . __ERRORID_SEPARATOR__ . "Vous n'avez pas saisi 2 fois le même mot de passe" . __ERROR_SEPARATOR__;
    else $isValid['alterPassword'] = true;
  }
  
  if ($alterCreate_time != '')
  {
    if(!preg_match('/^[0-9]{0,9}$/', $alterCreate_time))
    $errorstring .= "Create_TimeError" . __ERRORID_SEPARATOR__ . "La date de création saisie est invalide" . __ERROR_SEPARATOR__;
  }
  
  // On vérifie que le client est valide
  if(!empty($alterInfos))
  {
    $tab_cpi = array(); $es_cpi = '';
    // Informations Personnelles du client
    $tab_cpi['titre']    = isset($_GET['titre']) ?    substr(trim(rawurldecode($_GET['titre'])), 0, 255) : '';
    $tab_cpi['nom']      = isset($_GET['nom']) ?      strtoupper(substr(trim(rawurldecode($_GET['nom'])), 0, 255)) : '';
    $tab_cpi['prenom']   = isset($_GET['prenom']) ?   ucfirst(substr(trim(rawurldecode($_GET['prenom'])), 0, 255)) : '';
    $tab_cpi['fonction'] = isset($_GET['fonction']) ? ucfirst(substr(trim(rawurldecode($_GET['fonction'])), 0, 255)) : '';
    $tab_cpi['service'] = isset($_GET['service']) ? ucfirst(substr(trim(rawurldecode($_GET['service'])), 0, 255)) : '';
    $tab_cpi['tel1']     = isset($_GET['tel1']) ?     trim(rawurldecode($_GET['tel1'])) : '';
    $tab_cpi['fax1']     = isset($_GET['fax1']) ?     trim(rawurldecode($_GET['fax1'])) : '';
    
    $tab_cpi['email']    = isset($_GET['email']) ?    trim(rawurldecode($_GET['email'])) : '';
    
    $tab_cpi['website_origin'] = isset($_GET['website_origin']) ? trim($_GET['website_origin']) : '';
    
    if ($tab_cpi['titre'] != '1' && $tab_cpi['titre'] != '2' && $tab_cpi['titre'] != '3')
      $es_cpi .= "- Le titre choisi n'existe pas<br />\n";
    
    if ($tab_cpi['nom'] == '')
      $es_cpi .= "- Vous n'avez pas saisi le nom<br />\n";
    
    if ($tab_cpi['prenom'] == '')
      $es_cpi .= "- Vous n'avez pas saisi le prénom<br />\n";
    
    
    if ($tab_cpi['tel1'] == '')
      $es_cpi .= "- Au moins un numéro de téléphone est nécessaire pour valider le compte client<br />\n";
    
    if ($tab_cpi['email'] != '' && !preg_match('`^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,15})$`', $tab_cpi['email']))
      $es_cpi .= "- Adresse email invalide<br />\n";
    
    /*if (!isset($website_origin_list[$tab_cpi['website_origin']]))
      $es_cpi .= "- Site d'origine invalide123<br />\n";
    */
    if ($es_cpi != '') $errorstring .= "CustomerInfosError" . __ERRORID_SEPARATOR__ . $es_cpi . "<br />\n" . __ERROR_SEPARATOR__;
    
    $tab_ci = array(); $es_ci = '';
    // Informations sur la Société

    $tab_ci['societe']          = isset($_GET['societe']) ?          ucfirst(substr(trim(rawurldecode($_GET['societe'])), 0, 255)) : '';
    $tab_ci['nb_salarie']       = isset($_GET['nb_salarie']) ?       substr(trim(rawurldecode($_GET['nb_salarie'])), 0, 255) : '';
    $tab_ci['secteur_activite'] = isset($_GET['secteur_activite']) ? substr(trim(rawurldecode($_GET['secteur_activite'])), 0, 255) : '';
    $tab_ci['secteur_qualifie'] = isset($_GET['secteur_qualifie']) ? substr(trim(rawurldecode($_GET['secteur_qualifie'])), 0, 255) : '';
    $tab_ci['code_naf']         = isset($_GET['code_naf']) ?         substr(trim(rawurldecode($_GET['code_naf'])), 0, 255) : '';
    $tab_ci['num_siret']        = isset($_GET['num_siret']) ?        substr(trim(rawurldecode($_GET['num_siret'])), 0, 255) : '';
    $tab_ci['tva_intra']        = isset($_GET['tva_intra']) ?        substr(trim(rawurldecode($_GET['tva_intra'])), 0, 255) : '';
    
    if ($tab_ci['societe'] == '') {
      $es_ci .= "- Vous n'avez pas saisi le nom de la société<br />\n";
    } else {
      // restraining activity sector and naf
      $terms = preg_replace('/ de /', ' ', $tab_ci["societe"]);
      $terms = explode(' ', $terms);
      $ActivitySector = Doctrine_Core::getTable('ActivitySectorSurqualification');
      $ActivitySector->batchUpdateIndex();
      $array_results = array();
      foreach ($terms as $term) {
        $term = trim(Utils::toDashAz09($term));

        if ($result = $ActivitySector->search($term)) {
          $q = Doctrine_Query::create()
            ->from('ActivitySector as')
            ->leftJoin('as.Surqualifications ass')
            ->where('ass.id = ?', $result[0]['id']);

          $array_results[$result[0]['id']] = $result[0]['id'];
          $sector = $q->fetchArray();
          $results[] = $result;
        }
      }

      if (count($array_results) == 1) {
        $tab_ci["secteur_activite"] = $sector[0]['sector'];
        $tab_ci["secteur_qualifie"] = $sector[0]['Surqualifications'][0]['qualification'];
        $tab_ci["code_naf"] = $sector[0]['Surqualifications'][0]['naf'];
      }
    }
    
    if ($es_ci != '') $errorstring .= "CompanyInfosError" . __ERRORID_SEPARATOR__ . $es_ci . "<br />\n" . __ERROR_SEPARATOR__;
    
    $tab_ba = array(); $es_ba = '';
    // Coordonnées (facturation)
    $tab_ba['adresse']    = isset($_GET['adresse']) ?    substr(trim(rawurldecode($_GET['adresse'])), 0, 255) : '';
    $tab_ba['complement'] = isset($_GET['complement']) ? substr(trim(rawurldecode($_GET['complement'])), 0, 255) : '';
    $tab_ba['ville']      = isset($_GET['ville']) ?      strtoupper(substr(trim(rawurldecode($_GET['ville'])), 0, 255)) : '';
    $tab_ba['cp']         = isset($_GET['cp']) ?         substr(trim(rawurldecode($_GET['cp'])), 0, 5) : '';
    $tab_ba['pays']       = isset($_GET['pays']) ?       strtoupper(substr(trim(rawurldecode($_GET['pays'])), 0, 255)) : '';
    
    if ($tab_ba['adresse'] == '')
      $es_ba .= "- Vous n'avez pas saisi l'adresse<br />\n";
    
    if ($tab_ba['ville'] == '')
      $es_ba .= "- Vous n'avez pas saisi la ville<br />\n";
    
    if ($tab_ba['cp'] == '' || !preg_match('/^[0-9]+$/', $tab_ba['cp']))
      $es_ba .= "- Le code postal saisi est invalide<br />\n";
    
    if ($tab_ba['pays'] == '')
      $es_ba .= "- Vous n'avez pas saisi le pays<br />\n";
    
    if ($es_ba != '') $errorstring .= "BillingAddressError" . __ERRORID_SEPARATOR__ . $es_ba . "<br />\n" . __ERROR_SEPARATOR__;
    
    $tab_sa = array(); $es_sa = '';
    if (isset($_GET['coord_livraison']))
    {
      if ($_GET['coord_livraison'] == '1')
      {
        $tab_sa['coord_livraison'] = 1;
        $tab_sa['titre_l']      = isset($_GET['titre_l']) ?      substr(trim(rawurldecode($_GET['titre_l'])), 0, 255) : '';
        $tab_sa['nom_l']        = isset($_GET['nom_l']) ?        strtoupper(substr(trim(rawurldecode($_GET['nom_l'])), 0, 255)) : '';
        $tab_sa['prenom_l']     = isset($_GET['prenom_l']) ?     ucfirst(substr(trim(rawurldecode($_GET['prenom_l'])), 0, 255)) : '';
        $tab_sa['societe_l']    = isset($_GET['societe_l']) ?    ucfirst(substr(trim(rawurldecode($_GET['societe_l'])), 0, 255)) : '';
        $tab_sa['adresse_l']    = isset($_GET['adresse_l']) ?    substr(trim(rawurldecode($_GET['adresse_l'])), 0, 255) : '';
        $tab_sa['complement_l'] = isset($_GET['complement_l']) ? substr(trim(rawurldecode($_GET['complement_l'])), 0, 255) : '';
        $tab_sa['ville_l']      = isset($_GET['ville_l']) ?      strtoupper(substr(trim(rawurldecode($_GET['ville_l'])), 0, 255)) : '';
        $tab_sa['cp_l']         = isset($_GET['cp_l']) ?         substr(trim(rawurldecode($_GET['cp_l'])), 0, 5) : '';
        $tab_sa['pays_l']       = isset($_GET['pays_l']) ?       strtoupper(substr(trim(rawurldecode($_GET['pays_l'])), 0, 255)) : '';
        $tab_sa['tel2']     	= isset($_GET['tel2']) ?     trim(rawurldecode($_GET['tel2'])) : '';

        if ($tab_sa['titre_l'] != '1' && $tab_sa['titre_l'] != '2' && $tab_sa['titre_l'] != '3')
          $es_sa .= "- Le titre choisi n'existe pas<br />\n";
        
        if (($tab_sa['prenom_l'] == '' || $tab_sa['nom_l'] == '') && $tab_sa['societe_l'] == '')
          $es_sa .= "- Vous n'avez pas saisi les nom et prénom, ou le nom de la société<br />\n";
        
        if ($tab_sa['adresse_l'] == '')
          $es_sa .= "- Vous n'avez pas saisi l'adresse<br />\n";
        
        if ($tab_sa['ville_l'] == '')
          $es_sa .= "- Vous n'avez pas saisi la ville<br />\n";
        
        if ($tab_sa['cp_l'] == '' || !preg_match('/^[0-9]+$/', $tab_sa['cp_l']))
          $es_sa .= "- Le code postal est invalide<br />\n";

                                if ($tab_sa['tel2'] != '')
                                  if(!preg_match('/^[0-9]{10}$/', $tab_sa['tel2']))
          $es_sa .= "- Le téléphone de livraison est invalide<br />\n";
        
        if ($tab_sa['pays_l'] == '')
          $es_sa .= "- Vous n'avez pas saisi le pays<br />\n";
        
        if ($es_sa != '') $errorstring .= "ShippingAddressError" . __ERRORID_SEPARATOR__ . $es_sa . "<br />\n" . __ERROR_SEPARATOR__;
      }
      else
      {
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
        $tab_sa['tel2'] = $tab_ba['tel1'];
      }
    }
    else
    {
      $es_sa = "Il n'a pas été spécifié si les coordonnées de livraison sont les mêmes que celles de facturation ou pas<br />\n";
      $errorstring .= "ShippingAddressError" . __ERRORID_SEPARATOR__ . $es_sa . "<br />\n" . __ERROR_SEPARATOR__;
    }
    
    if ($es_cpi == '' && $es_ci == '' && $es_ba == '' && $es_sa == '') $isValid['alterInfos'] = true;
  }
  
  // S'il y a quelque chose à faire
  if (!empty($isValid))
  {
    $es_v = '';
    $customer = & new Customer($handle, $customerID);
    
    if ($customer->statut == 0)
    {
      $es_v .= '- Le client ayant pour numéro identifiant ' . $customerID . " n'existe pas\n";
    }
    else
    {
      if (isset($isValid['toggleActiveState']))
      {
        if ($customer->actif == 0) $customer->actif = 1;
        else $customer->actif = 0;
        $outputstring .=  "ActiveState" . __OUTPUTID_SEPARATOR__ . $customer->actif . __OUTPUT_SEPARATOR__;
      }
      
      if (isset($isValid['alterLogin']))
      {
        $customer->login = $alterLogin;
        $outputstring .=  "LoginValue" . __OUTPUTID_SEPARATOR__ . $alterLogin . __OUTPUT_SEPARATOR__;
      }
      
      if (isset($isValid['alterCode']))
      {
        $customer->code = $alterCode;
        $outputstring .=  "CodeValue" . __OUTPUTID_SEPARATOR__ . $alterCode . __OUTPUT_SEPARATOR__;
      }
      
      if (isset($isValid['alterInfos']))
      {
        $outputstring .= "CustomerInfos" . __OUTPUTID_SEPARATOR__;
        //$tab_cpi['titre'] = GetTitle($tab_cpi['titre']);
        foreach ($tab_cpi as $prop => $value)
        {
          $customer->$prop = $value;
          if ($prop == 'website_origin') {
            $value = $website_origin_list[$value];
          }
          $outputstring .= $prop . __DATA_SEPARATOR__ . $value . __DATA_SEPARATOR__;
        }
        //$customer->titre = GetTitleNum($customer->titre);
        $outputstring .= __OUTPUT_SEPARATOR__;
        
        $outputstring .= "CompanyInfos" . __OUTPUTID_SEPARATOR__;
        foreach ($tab_ci as $prop => $value)
        {
          $customer->$prop = $value;
          $outputstring .= $prop . __DATA_SEPARATOR__ . $value . __DATA_SEPARATOR__;
        }
        $outputstring .= __OUTPUT_SEPARATOR__;
          
        $outputstring .= "BillingAddress" . __OUTPUTID_SEPARATOR__;
        foreach ($tab_ba as $prop => $value)
        {
          $customer->$prop = $value;
          $outputstring .= $prop . __DATA_SEPARATOR__ . $value . __DATA_SEPARATOR__;
        }
        $outputstring .= __OUTPUT_SEPARATOR__;
          
        $outputstring .= "ShippingAddress" . __OUTPUTID_SEPARATOR__;
        //$tab_sa['titre_l'] = GetTitle($tab_sa['titre_l']);
        foreach ($tab_sa as $prop => $value)
        {
          $customer->$prop = $value;
          $outputstring .= $prop . __DATA_SEPARATOR__ . $value . __DATA_SEPARATOR__;
        }
        //$customer->titre_l = GetTitleNum($customer->titre_l);
        $outputstring .= __OUTPUT_SEPARATOR__;
        
      }
      
	    
	    $sql_update = "UPDATE  `clients` SET  `fonction_service` =  '".$_GET['service']."' WHERE  `id` ='".$_GET['customerID']."' ";
		mysql_query($sql_update);
  
      if ($customer->save()) $outputstring .=  "Timestamp" . __OUTPUTID_SEPARATOR__ . 'le ' . date("d/m/Y à H:i.s", $customer->last_update) . __OUTPUT_SEPARATOR__;
      else $es_v .= "- Erreur fatale MySQL lors de la sauvegarde des changements clients\n";
    }
    if ($es_v != '') $errorstring .= "CustomerError" . __ERRORID_SEPARATOR__ . $es_v . "\n" . __ERROR_SEPARATOR__;
  }
  /*else
  {
    $errorstring .= "CustomerError" . __ERRORID_SEPARATOR__ . "Aucun changement n'a été effectuée sur ce client car aucune des données soumises n'était valide\n" . __ERROR_SEPARATOR__;
  }*/
}
else
{
  $errorstring .= "CustomerError" . __ERRORID_SEPARATOR__ . "Le numéro d'identifiant du client est invalide\n" . __ERROR_SEPARATOR__;
}
//mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $outputstring);
//mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $errorstring);
print $errorstring . __MAIN_SEPARATOR__ . $outputstring;

