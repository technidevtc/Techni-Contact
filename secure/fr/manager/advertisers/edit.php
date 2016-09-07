<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Mises à jour :

	11 juin 2005 : + accès commerciaux à tous les éléments
	28 mars 2006 : + options fournisseurs
	   

 Fichier : /secure/manager/advertisers/edit.php
 Description : Edition fiche annonceur

/=================================================================*/

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

require(ADMIN . 'advertisers.php');
require(ADMIN . 'users.php');
require(ADMIN . 'tva.php');
$title  = 'Base de données des annonceurs et fournisseurs';
$navBar = '<a href="index.php?SESSION" class="navig">Base de données des annonceurs et fournisseurs</a> &raquo; Editer un annonceur ou fournisseur';
require(ADMIN . 'head.php');

$from_extranet = isset($_GET['type']) ? true : false;

///////////////////////////////////////////////////////////////////////////

if (!isset($_GET['id']) 
|| !preg_match('/^[0-9]+$/', $_GET['id'])
|| !($data = & loadAdvertiser($handle, $_GET['id'], $from_extranet))
|| !($extranet = & getExtranetData($handle, $_GET['id']))) { 
?>
	<div class="bg">
		<div class="fatalerror">Identifiant annonceur ou fournisseur incorrect.</div>
	</div>
<?php
}
else {
	$error = false;
	$errorstring = '';

	$listeTVAs = & displayTVAs($handle, ' order by taux desc'); // chargement des différents taux de TVA ici pour n'avoir à le faire qu'une seule fois
	$idTVAdft = getConfig($handle, 'idTVAdft');
        
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!$userPerms->has("m-prod--sm-partners","e")) {
      $errorstring = "Vous n'avez pas les droits de modification partenaires.";
    }
    else {
      $nom1 = isset($_POST['nom1']) ? substr(trim($_POST['nom1']), 0, 255) : '';

      if($nom1 == '') {
        $error = true;
        $errorstring .= '- Vous n\'avez pas saisi le nom de l\'annonceur ou fournisseur<br>';
      }
      // $data[2] = nom1
      if($nom1 != $data[2] && !isAUnique($handle, 'nom1', $nom1)) {
        $error = true;
        $errorstring .= '- Un annonceur ou fournisseur porte déjà ce nom<br>';
      }

      $nom2 = isset($_POST['nom2']) ? substr(trim($_POST['nom2']), 0, 255) : '';

      
      
      if ($user->rank == HOOK_NETWORK || $user->rank == COMMADMIN || $user->rank == COMM) {
        $category = isset($_POST['category']) ? $_POST['category'] : __ADV_CAT_ADVERTISER__;
        if (!isset($adv_cat_list[$category])) $category = __ADV_CAT_ADVERTISER__;
        
        /* Hors Lot - Aout à Décembre 2010 > Tâches > Création nouvel état partenaire  
         * suite au changement d'état, on envoie un mail - OD 15/12/2010 */

        if( $data[31] != __ADV_CAT_LITIGATION__ && $_POST['category'] == __ADV_CAT_LITIGATION__ ){ // si on affecte un état litige de paiement, on prévient le partenaire par mail
          $data[53] = time(); //timestamp_litigation
          // envoi d'un mail au partenaire
          $arrayEmail = array(
            "email" => '',
            "subject" => "Litige de paiement : blocage provisoire de votre compte Techni-Contact",
            "headers" => "From: Service comptabilité Techni-Contact <comptabilite@techni-contact.com>\nReply-To: Service comptabilité Techni-Contact <comptabilite@techni-contact.com>\r\n",
            "template" => "partner-bo_partners-bop_litigation",
            "data" => array(
              "MAIL_LINK" => '<a href="mailto:comptabilite@techni-contact.com">comptabilite@techni-contact.com</a>'
            )
          );
          $mail = new Email($arrayEmail);
          $mail->email = $data[17];
          $mail->send();

          // envoi d'un mail au au service comptable
          $mailCompta = new Email($arrayEmail);
          $mailCompta->email = 'comptabilite@techni-contact.com';
          $mailCompta->send();

        }elseif($data[31] == __ADV_CAT_LITIGATION__ && $_POST['category'] != __ADV_CAT_LITIGATION__){ // de meme si on sort un partenaire d'un état litige de paiement
          // envoi d'un mail au partenaire
          $arrayEmail = array(
            "email" => '',
            "subject" => "Fin du litige de paiement : déblocage de votre compte Techni-Contact",
            "headers" => "From: Service comptabilité Techni-Contact <comptabilite@techni-contact.com>\nReply-To: Service comptabilité Techni-Contact <comptabilite@techni-contact.com>\r\n",
            "template" => "partner-bo_partners-bop_litigationEnding",
            "data" => array( // TODO : vérifier s'il est pertinent que ce tableau ne puisse pas être vide
              "TOTO" => ''
            )
          );
          $mail = new Email($arrayEmail);
          $mail->email = $data[17];
          if($_POST['category'] == __ADV_CAT_ADVERTISER__ || $_POST['category'] == __ADV_CAT_ADVERTISER_NOT_CHARGED__)
            $mail->send();

          // envoi d'un mail au au service comptable
          $mailCompta = new Email($arrayEmail);
          $mailCompta->email = 'comptabilite@techni-contact.com';
          $mailCompta->send();

        }
      }
      else {
        $category = $data[31];
      }

      $adresse1 = isset($_POST['adresse1']) ? substr(trim($_POST['adresse1']), 0, 255) : '';

      if($adresse1 == '') {
        $error = true;
        $errorstring .= '- Vous n\'avez pas saisi l\'adresse<br>';
      }

      $adresse2 = isset($_POST['adresse2']) ? substr(trim($_POST['adresse2']), 0, 255) : '';

      $ville = isset($_POST['ville']) ? substr(trim($_POST['ville']), 0, 255) : '';

      if($ville == '') {
        $error = true;
        $errorstring .= '- Vous n\'avez pas saisi le nom de la ville<br>';
      }

      $cp = isset($_POST['cp']) ? substr(trim($_POST['cp']), 0, 255) : '';

      if($cp == '') {
        $error = true;
        $errorstring .= '- Vous n\'avez pas saisi le code postal<br>';
      }

      $pays = isset($_POST['pays']) ? substr(trim($_POST['pays']), 0, 255) : '';

      if($pays == '') {
        $error = true;
        $errorstring .= '- Vous n\'avez pas saisi le nom du pays<br>';
      }

      $warranty = isset($_POST['warranty']) ? substr(trim($_POST['warranty']), 0, 255) : '';
      $catalog_code = isset($_POST['catalog_code']) ? trim($_POST['catalog_code']) : "";

      /* help message */
      $help_show = isset($_POST['help_show']) ? ($_POST['help_show'] != '' ? '1' : '0') : '0';
      $help_msg = isset($_POST['help_msg']) ? trim($_POST['help_msg']) : '';


      $contact = isset($_POST['contact']) ? substr(trim($_POST['contact']), 0, 255) : '';

      $email = isset($_POST['email']) ? trim($_POST['email']) : '';

	  /*
      if($email != '' && !preg_match('`^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$`', $email)) {
        $error = true;
        $errorstring .= '- Adresse email invalide<br>';
      }
	  */
	  
	  if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$error = true;
		$errorstring .= '- Adresse email invalide<br/>';
	  }
	  
      // data[10] = email
      /*if($email != '' && $email != $data[17] && !isAUnique($handle, 'email', $email))
      {
      $error = true;
      $errorstring .= '- Cette adresse email est déjà utilisée<br>';
      }*/

      $url = isset($_POST['url']) ? trim($_POST['url']) : '';
      if($url == 'http://') $url = '';

      if($url != '' && strpos($url, '/', 8) === false) {
        $url .= '/';
      }

      if($url != '' && !preg_match('/^(http|https|ftp):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\//i', $url)) {
        $error = true;
        $errorstring .= '- Adresse du site web invalide<br>';
      }

      $tel1 = isset($_POST['tel1']) ? trim($_POST['tel1']) : '';

      if($tel1 == '') {
        $error = true;
        $errorstring .= '- Vous n\'avez pas saisi le numéro de téléphone <br>';
      }

      $tel2 = isset($_POST['tel2']) ? trim($_POST['tel2']) : '';

      $fax1 = isset($_POST['fax1']) ? trim($_POST['fax1']) : '';

      if($fax1 == '') {
        $error = true;
        $errorstring .= '- Vous n\'avez pas saisi le numéro de fax <br>';
      }

      $fax2 = isset($_POST['fax2']) ? trim($_POST['fax2']) : '';

      if ($userPerms->has("m-prod--sm-partners-manage-profil","e")) {
        $commercial = isset($_POST["commercial"]) ? trim($_POST["commercial"]) : -1;
        $auser = new BOUser($commercial);
        if (!$auser->existsInDB() || !$auser->get_permissions()->has("m-prod--sm-partners-appear-as-profil","r")) {
          $error = true;
          $errorstring .= '- Le commercial associé est incorrect <br>';
        }
      }
      else {
        $commercial = $data[1];
      }

      if (true) {
        $client_id = isset($_POST['client_id']) ? trim($_POST['client_id']) : '';
      }
      else {
        $client_id = $data[52];
      }
      
      $from_web = isset($_POST['from_web']) ? ($_POST['from_web'] != '' ? '1' : '0') : '0';
      $cc_foreign = isset($_POST['cc_foreign']) ? ($_POST['cc_foreign'] != '' ? '1' : '0') : '0';
      $cc_intern = isset($_POST['cc_intern']) ? ($_POST['cc_intern'] != '' ? '1' : '0') : '0';
      $cc_noPrivate = isset($_POST['cc_noPrivate']) ? ($_POST['cc_noPrivate'] != '' ? '1' : '0') : '0';
      $show_infos_online = isset($_POST['show_infos_online']) ? ($_POST['show_infos_online'] != '' ? '1' : '0') : '0';
      
      // Options fournisseurs
      if ($category == __ADV_CAT_SUPPLIER__) {
        $delai_livraison = isset($_POST['delai_livraison']) ? substr(trim($_POST['delai_livraison']), 0, 255) : '';
        $delai_expedition = isset($_POST['delai_expedition']) ? substr(trim($_POST['delai_expedition']), 0, 255) : '';
        $shipping_fee = isset($_POST['shipping_fee']) ? substr(trim($_POST['shipping_fee']), 0, 255) : '';
        $prixPublic = isset($_POST['prixPublic']) ? $_POST['prixPublic'] : '0';
        $margeRemise = isset($_POST['margeRemise']) ? substr(trim($_POST['margeRemise']), 0, 255) : '';
        $peuChangerTaux = isset($_POST['peuChangerTaux']) ? ($_POST['peuChangerTaux'] != '' ? '1' : '0') : '0';
        $arrondi = isset($_POST['arrondi']) ? ($_POST['arrondi'] != '' ? '1' : '') : '';
        $idTVA = isset($_POST['idTVA']) ? $_POST['idTVA'] : $idTVAdft;
        $contraintePrix = isset($_POST['contraintePrix']) ? substr(trim($_POST['contraintePrix']), 0, 255) : '0';
        $asEstimate = isset($_POST['asEstimate']) && $_POST['asEstimate'] == 'on' ? 1 : 0;

        if ($delai_livraison == '') {
          $error = true;
          $errorstring .= '- Vous n\'avez pas saisi de délai de livraison <br>';
        }
		
		if(!empty($delai_expedition)){
		  if(!is_numeric($delai_expedition)){
			$error = true;
			$errorstring .= '- Délai d\'expédition : Ce champs ne peux contenir que des valeurs numériques <br/>';		  
		  }
		}
        /*if ($shipping_fee == '') {
          $error = true;
          $errorstring .= '- Vous n\'avez pas saisi de frais de port <br>';
        }*/
        
        if($prixPublic != '1') {
          $prixPublic = '0';
        }

        if ($margeRemise == '') {
          $error = true;
          $errorstring .= '- Vous n\'avez pas saisi le taux de ' . ($prixPublic == '1' ? 'remise' : 'marge') . ' <br>';
        }
        elseif(!preg_match('/^[0-9]*((\.|\,)[0-9]{0,5})?$/',$margeRemise)) {
          $error = true;
          $errorstring .= '- Le taux de '. ($prixPublic == '1' ? 'remise' : 'marge') . ' saisi est invalide <br>';
        }

        $idTVAexist = false;
        foreach ($listeTVAs as $v) {
          if ($idTVA == $v[0]) {
            $idTVAexist = true;
            break;
          }
        }
        if (!$idTVAexist) {
          $error = true;
          $errorstring .= '- Le taux de TVA choisi n\'existe pas<br>';
        }

        if (!preg_match('/^[0-9]*((\.|\,)[0-9]{0,2})?$/',$contraintePrix)) {
          $error = true;
          $errorstring .= '- La contrainte de prix saisie est invalide <br>';
        }
      }
      else {
        $delai_livraison = $shipping_fee = $prixPublic = $margeRemise = $peuChangerTaux = $arrondi = $contraintePrix = '';
        $idTVA = $idTVAdft;
      }

  // Fin options fournisseurs

      $prenomcontact = isset($_POST['prenomcontact']) ? $_POST['prenomcontact'] : '';
      $nomcontact    = isset($_POST['nomcontact'])    ? $_POST['nomcontact']    : '';
      $emailcontact  = isset($_POST['emailcontact'])  ? $_POST['emailcontact']  : '';
      if($emailcontact != '' && !preg_match('`^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$`', $emailcontact))
          {
              $error = true;
              $errorstring .= "- Adresse email du contact 1 invalide<br />";
          }
          $critere  = isset($_POST['critere']) ? $_POST['critere'] :  1;
      
      $contacts = array();
      for ($i = 1; $i < 10; $i ++)
      {
        $contacts[$i]['prenom'] = isset($_POST['prenomcontact'.$i]) ? $_POST['prenomcontact'.$i] : '';
        $contacts[$i]['nom'] = isset($_POST['nomcontact'.$i]) ? $_POST['nomcontact'.$i] : '';
        $contacts[$i]['email'] = isset($_POST['emailcontact'.$i]) ? $_POST['emailcontact'.$i] : '';
        if($contacts[$i]['email'] != '' && !preg_match('`^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$`', $contacts[$i]['email']))
        {
          $error = true;
          $errorstring .= "- Adresse email du contact " . ($i+1) . " invalide<br />";
        }
        $contacts[$i]['critere'] = isset($_POST['critere'.$i]) ? $_POST['critere'.$i] : 1;
      }
      
      // Not Required Default Fields
      $notRequiredFields = array();
      if (!isset($_POST["nr_fonction"])) $notRequiredFields[] = "fonction";
      if (!isset($_POST["nr_societe"])) $notRequiredFields[] = "societe";
      if (!isset($_POST["nr_adresse"])) $notRequiredFields[] = "adresse";
      if (!isset($_POST["nr_complement"])) $notRequiredFields[] = "complement";
      if (!isset($_POST["nr_cp"])) $notRequiredFields[] = "cp";
      if (!isset($_POST["nr_ville"])) $notRequiredFields[] = "ville";
      if (!isset($_POST["nr_pays"])) $notRequiredFields[] = "pays";
      if (!isset($_POST["nr_nb_salarie"])) $notRequiredFields[] = "nb_salarie";
      if (!isset($_POST["nr_secteur_activite"])) $notRequiredFields[] = "secteur_activite";
      if (!isset($_POST["nr_code_naf"])) $notRequiredFields[] = "code_naf";
      if (!isset($_POST["nr_num_siret"])) $notRequiredFields[] = "num_siret";
      
      // Invoicing Customization
      $ic_reject = isset($_POST["ic_reject"]) ? 1 : 0;
      $ic_active = isset($_POST["ic_active"]) ? 1 : 0;
      $ic_fields = isset($_POST["ic_fields"]) ? json_decode($_POST["ic_fields"]) : array();
      foreach($ic_fields as &$ic_field)
        if (is_string($ic_field))
          $ic_field = rawurldecode($ic_field);
      unset($ic_field);
      $ic_extranet = isset($_POST["ic_extranet"]) ? 1 : 0;
      $noLeads2in = isset($_POST["noLeads2in"]) ? 1 : 0;
      $noLeads2out = isset($_POST["noLeads2out"]) ? 1 : 0;
      $noAlert2out = isset($_POST["noAlert2out"]) ? 1 : 0;
      $auto_reject_threshold = isset($_POST["auto_reject_threshold"]) ? (int)$_POST["auto_reject_threshold"] : 0;
          
      
      $listeHidden = isset($_POST['listeLinked']) ? $_POST['listeLinked'] : '';
      // $direct_debit = $_POST['direct_debit'] == 'on' ? 1 : 0;
	  
	  
		$sql_update = "UPDATE `advertisers` SET `contacts_not_read_notification` = '".$noAlert2out."' 
					   WHERE `id` ='".$_GET['id']."' ";
		mysql_query($sql_update);
		
		
		$sql_update_payment = " UPDATE `advertisers` SET `direct_debit` = '".$_POST['payment_mean']."' 
								WHERE `id` ='".$_GET['id']."' ";
		mysql_query($sql_update_payment);
		
	  
          /////////////

          $listeTab = explode(',', $listeHidden);
          $listeHidden = $listeShown  = '';   // listes regénérées en cas de prob dans le form
      
          for($i = 0; $i < count($listeTab); ++$i)
          {
              if(preg_match('/^[0-9]+$/', $listeTab[$i]))
              {
                   if(($result = & $handle->query('select nom1 from advertisers where id = \'' . $handle->escape($listeTab[$i]). '\'', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
                   {
                       $record = & $handle->fetch($result);
                   
                       $listeHidden .= $listeTab[$i] . ',';
                   
                       if($listeShown != '')
                       {
                           $listeShown .= ' - ';
                       }
                                // code redondant avec link.php (en cas de modif ...)
                       $listeShown .= '<a href="javascript:remove(\\\'' . $listeTab[$i] . '\\\', \\\'' . to_entities(str_replace(array('"', "'", '&', '(', ')'), array(' ', ' ', '', '', ''), Utils::toASCII($record[0]))) . '\\\')">' . to_entities(str_replace(array('"', "'", '&', '(', ')'), array(' ', ' ', '', '', ''), Utils::toASCII($record[0]))) . '</a>';
                   }
              }
          }
      
          if($listeShown == '')
          {
              $listeShown = 'Actuellement aucun annonceur ni fournisseur n\\\'est lié à celui en cours de modification.';
          }

        
          ////////////


          if(isset($_POST['typecout']) && $_POST['typecout'] != 0)
          {
              if($_POST['typecout'] == 1)
              {
                  // variable
                  $ctype  = 1;
              
                  $datef = $_POST['anneef'] . '-' . $_POST['moisf'] . '-' . $_POST['jourf'];
                  if(!checkdate($_POST['moisf'], $_POST['jourf'], $_POST['anneef']) || $_POST['anneef'] < date('Y') || $_POST['anneef'] > (date('Y') + 1))
                  {
                      $error = true;
                      $errorstring .= '- Date de début de facturation invalide<br>';

                  }
              

                  $coutcontact = isset($_POST['coutcontact']) ? str_replace(',', '.', $_POST['coutcontact']) : 0.0;
                  if(!preg_match('/^[0-9]+(\.[0-9]+){0,1}$/', $coutcontact))
                  {
                      $error = true;
                      $errorstring .= '- Le format du coût du contact est invalide<br>';

                  }

                  $datea = '0000-00-00';
              }
              else
              {
                  // fixe
                  $ctype  = 2;
                  $datef = '0000-00-00';
                  $coutcontact = 0;

                  $datea = $_POST['anneea'] . '-' . $_POST['moisa'] . '-' . $_POST['joura'];
                  if(!checkdate($_POST['moisa'], $_POST['joura'], $_POST['anneea']) || $_POST['anneea'] < date('Y') || $_POST['anneea'] > (date('Y') + 1))
                  {
                      $error = true;
                      $errorstring .= '- Date de fin d\'abonnement invalide<br>';

                  }

              }
          }
          else
          {
              $ctype       = 0;
              $datef       = $datea = '0000-00-00';
              $coutcontact = 0;
          }
          

          $login = isset($_POST['login']) ? trim(substr($_POST['login'], 0, 255)) : '';

          if($login == '')
          {
              $error = true;
              $errorstring .= '- Vous n\'avez pas saisi le login extranet de l\'annonceur ou fournisseur<br>';
          }
          else if(strlen($login) < 3)
          {
              $error = true;
              $errorstring .= '- Le login extranet doit faire au minimum 3 caractères<br>';
          }
          
          if($login != $extranet['login'] && !isAUnique($handle, 'login', $login, 'extranetusers'))
          {
              $error = true;
              $errorstring .= '- Un annonceur ou fournisseur utilise déjà ce login<br>';
          }


          $pass = isset($_POST['pass']) ? substr($_POST['pass'], 0, 255) : '';
          
          if($pass != '')
          {
              if(strlen($pass) < 6)
              {
                  $error = true;
                  $errorstring .= '- Le nouveau mot de passe extranet doit faire au minimum 6 caractères<br>';
              }
              else
              {
                  $cpass = isset($_POST['cpass']) ? substr($_POST['cpass'], 0, 255) : '';
                  
                  if($cpass != $pass)
                  {
                      $error = true;
                      $errorstring .= '- Le nouveau mot de passe et sa confirmation doivent être identiques<br>';
                  }
              }
          }
          
          
      $active = isset($_POST['active']) ? true : false;


      if(!$error)
      {
        if($user->rank == CONTRIB)
        {    // Pas de maj de la liste des liés + option spécifiques aux comm
          $upall = false;
        }
        else
        {
          $upall = true;
        }
        //$data[31] = $parent
        $timestamp_litigation = !empty($data[53]) ? $data[53] : '';
        
        $ok = updateAdvertiser($handle, $_GET['id'], $commercial, $nom1, $nom2, $adresse1, $adresse2, $ville, $cp, $pays, $delai_livraison,$delai_expedition, $shipping_fee, $warranty, $catalog_code, $prixPublic, $margeRemise, $peuChangerTaux, $arrondi, $idTVA, $contraintePrix, $asEstimate, $contact, $email, $url, $tel1, $tel2, $fax1, $fax2, $client_id, $prenomcontact, $nomcontact, $emailcontact, $critere, $ctype, $datef, $coutcontact, $datea, $listeHidden, $active, $user->login, $data[2], $upall, $login, $pass, $category, $data[31], $contacts, $from_web, $cc_foreign, $cc_intern, $show_infos_online, $help_show, $help_msg, implode(",", $notRequiredFields), $cc_noPrivate, $ic_reject, $ic_active, serialize($ic_fields), $ic_extranet, $noLeads2in, $noLeads2out, $auto_reject_threshold, $timestamp_litigation);
        
		
		$email_order = isset($_POST['email_order']) ? ($_POST['email_order'] != '' ? '1' : '0') : '0';
		$sql_update  = "UPDATE `advertisers` SET direct_link_printable_orders='".$email_order."' WHERE id='".$_GET['id']."'  ";
		mysql_query($sql_update);
		

		
        if($ok && $from_extranet)
        {
          $handle->query('delete from advertisers_adv where id = \'' . $handle->escape($_GET['id']) . '\'');
          $OK_ADV_EXT = true;
        }
      }
    }
	}
	else // if($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		$ctype = $data[27];
		$anneef = substr($data[28], 0, 4); if($anneef == '0000') $anneef = date('Y');
		$moisf  = substr($data[28], 5, 2); if($moisf  == '00')   $moisf  = date('m');
		$jourf  = substr($data[28], 8, 2); if($jourf  == '00')   $jourf  = date('d');
		$coutcontact = $data[29];
		$anneea = substr($data[30], 0, 4); if($anneea == '0000') $anneea = date('Y') + 1;
		$moisa  = substr($data[30], 5, 2); if($moisa  == '00')   $moisa  = date('m');
		$joura  = substr($data[30], 8, 2); if($joura  == '00')   $joura  = date('d');
		
		$commercial = $data[1];
		$critere = $data[26];
		
		$nom1 = & $data[2];
		$nom2 = & $data[3];
		$adresse1 = & $data[4];
		$adresse2 = & $data[5];
		$ville = & $data[6];
		$cp = $data[7];
		$pays = & $data[8];
		
		// Options fournisseurs
		$delai_livraison = & $data[9];
		$shipping_fee = $data[39];
		$prixPublic = $data[10];
		$margeRemise = $data[11];
		$peuChangerTaux = $data[12];
		$arrondi = $data[13];
		$idTVA = $data[14];
		$contraintePrix = $data[15];
    $asEstimate = $data[51];
		$contact = & $data[16];
		$email = & $data[17];
		$url = & $data[18];
		$tel1 = & $data[19];
		$tel2 = & $data[20];
		$fax1 = & $data[21];
		$fax2 = & $data[22];
    $client_id = & $data[52];
		$prenomcontact = & $data[23];
		$nomcontact = & $data[24];
		$emailcontact = & $data[25];
		
		$category = $data[31];
		$contacts = mb_unserialize($data[32]);
		$from_web = $data[33];
		$cc_foreign = $data[34];
		$cc_intern = $data[35];
		$cc_noPrivate = $data[43];
		$show_infos_online = $data[36];
		$warranty = $data[40];
		$catalog_code = $data[41];
		$notRequiredFields = explode(",", $data[42]);
		
		$ic_reject = $data[44];
		$ic_active = $data[45];
		$ic_fields = empty($data[46]) ? array() : mb_unserialize($data[46]);
		$ic_extranet = $data[47];
		$noLeads2in = $data[48];
		$noLeads2out = $data[49];
		$auto_reject_threshold = $data[50];
    
		$help_show = $data[37];
		$help_msg = empty($data[38]) ? "Besoin d’aide ? Contactez le 00 00 00 00 00" : $data[38];
		
                $timestamp_litigation = !empty($data[53]) ? $data[53] : '';
                $direct_debit = $data[54];

                $listeHidden = & $data[55];
    
		$listeShown  = '';
		
		$active = $data[0];
		
		$listeTab = explode(',', $listeHidden);
		for($i = 0; $i < count($listeTab); ++$i)
		{
			if(($result = & $handle->query('select nom1 from advertisers where id = \'' . $handle->escape($listeTab[$i]). '\'', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
			{
				$record = & $handle->fetch($result);
				if($listeShown != '')
				{
					$listeShown .= ' - ';
				}
				// code redondant avec link.php (en cas de modif ...)
				$listeShown .= '<a href="javascript:remove(\\\'' . $listeTab[$i] . '\\\', \\\'' . to_entities(str_replace(array('"', "'", '&', '(', ')'), array(' ', ' ', '', '', ''), Utils::toASCII($record[0]))) . '\\\')">' . to_entities(str_replace(array('"', "'", '&', '(', ')'), array(' ', ' ', '', '', ''), Utils::toASCII($record[0]))) . '</a>';
			}
		}
		
		if($listeShown == '')
		{
			$listeShown = 'Actuellement aucun annonceur ni fournisseur n\\\'est lié à celui en cours de modification.';
		}
		
		$login = $extranet['login'];
		
		//$parent = getParentAdvertiser($handle, $_GET['id']);
		
	}

	$initpass = $extranet['pass'];

  // getting commercial user profils
  $comm_profils_ids = BOUserPermission::get("id_user","id_functionality = ".$fntByName["m-prod--sm-partners-appear-as-profil"]);
  foreach($comm_profils_ids as &$v) $v = $v["id_user"]; unset($v);
  $comm_profils = array();
  if (!empty($comm_profils_ids))
    $comm_profils = BOUser::get("id, name","id in (".implode(",",$comm_profils_ids).")");
  
///////////////////////////////////////////////////////////////////////////

	$filter = (isset($_GET['filter']) && $_GET['filter'] == '1') ? 1 : 0;
	$liste  = array(10, 25, 50, 75);

	if(isset($_GET['nb']) && in_array($_GET['nb'], $liste))
	{
		$nb   = $_GET['nb'];
		$type = 1;
	}
	else if(isset($_GET['lettre']) && preg_match('/^[0a-z]+$/', urldecode($_GET['lettre'])))
	{
		$lettre = $_GET['lettre'];
		$type   = 0;
	}
	else
	{
		$type = 1;
		$nb   = 10;
	}

?>
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/HN.Mods.DialogBox.blue.css"/>
<script type="text/javascript" src="../products/Classes.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_URL ?>ressources/js/ManagerFunctions.js"></script>
<style type="text/css">
#toggleSelectFamilies {cursor: pointer;}
#toggleSelectFamilies:hover {text-decoration: underline;}

/* Family confimation layer */
#FamilyChangeWrap {  position: absolute; top: 608px; left: 20px;  display: none;}
.FamilyChangeLayer{ width: 788px; height: 404px;}
#FamilyChangeConfirmShad { z-index: 3; position: absolute; top: 5px; left: 5px; background-color: #000000; display: none;  filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#FamilyChangeConfirm { z-index: 4; position: absolute; top: 0px; left: 0px; display: none; border: 2px solid #999999; }
.contentConfirmFamilyChange{margin: 10px}

/* Family Selection Window */
#FamilySelectionWindowShad { z-index: 3; position: absolute; top: 613px; left: 25px; width: 788px; height: 404px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#FamilySelectionWindow { z-index: 4; position: absolute; top: 608px; left: 20px; border: 2px solid #999999; visibility: hidden; }

.window-silver { padding: 5px; font: normal 11px Tahoma, Arial, Helvetica, sans-serif; }
.window-silver a { color: #000000; font-weight: normal; }
.window-silver a:hover { font-weight: normal; }

.tab_menu { height: 24px; padding: 0 5px 0 5px; position: relative; top: 1px; }

.tab_menu .tab { float: left; width: 118px; text-align: center; cursor: default; }

.tab_menu .tab_lb_i, .tab_menu .tab_lb_a, .tab_menu .tab_lb_s, .tab_menu .tab_rb_i, .tab_menu .tab_rb_a, .tab_menu .tab_rb_s { float: left; width: 4px; height: 23px; }
.tab_menu .tab_lb_i { background : url(tab-left-border.gif) repeat-x; }
.tab_menu .tab_lb_a { background : url(tab-active-left-border.gif) repeat-x; }
.tab_menu .tab_rb_i { background : url(tab-right-border.gif) repeat-x; }
.tab_menu .tab_rb_a { background : url(tab-active-right-border.gif) repeat-x; }
.tab_menu .tab_lb_s { height: 24px; background : url(tab-active-left-border.gif) repeat-x;}
.tab_menu .tab_rb_s { height: 24px; background : url(tab-active-right-border.gif) repeat-x; }

.tab_menu .tab_bg_i, .tab_menu .tab_bg_a, .tab_menu .tab_bg_s { height: 17px; float: left; width: 90px; text-align: left; color: #000000; padding: 6px 10px 0px 10px; white-space: nowrap; }
.tab_menu .tab_bg_i { background: url(tab-bg.gif) repeat-x; }
.tab_menu .tab_bg_a { background: url(tab-active-bg.gif) repeat-x; }
.tab_menu .tab_bg_s { height: 18px; background: url(tab-active-bg.gif) repeat-x; font-weight: bold; }

.menu-below { border: 1px solid #808080; height: 2px; font-size: 0; border-bottom: none; background-color: #D8D4CD; }
.main { border: 1px solid #808080; background-color: #DEDCD6; }

.search_menu { width: 516px; cursor: default; padding: 3px 6px; border-bottom: 1px solid #808080; display: block; float: left}
.search_menu span { border: 1px solid #DEDCD6; padding: 2px 5px; outline: none; }
.search_menu span.over { border-color: #FFFFFF #808080 #808080 #FFFFFF; }
.search_menu span.down { border-color: #808080 #FFFFFF #FFFFFF #808080; }
.search_menu span.selected { border-color: #808080 #FFFFFF #FFFFFF #808080; }

.body { padding: 2px 4px; background-color: #DEDCD6; border-top: 1px solid #FFFFFF; clear: left}
.body .colg { float: left; width: 258px; margin-right: 5px; }
.body .colc { float: left; width: 257px; }
.body .col-title { cursor: default; font-weight: bold; margin: 2px; }
.body .colg .list { width: 252px; height: 298px; background-color: #FFFFFF; border: 2px inset #808080; margin: 0; padding: 1px; list-style-type: none; overflow: auto; }
.body .colg .list li { cursor: default; white-space: nowrap; }
.body .colg .list li.over { background-color: #316AC5; color: #FFFFFF; }
.body .colg .list li.selected { background-color: #0C266C; color: #FFFFFF; }

.body .colc .infos { position: relative; height: 290px; background-color: #FFFFFF; border: 2px inset #808080; padding: 5px; }

.body .colc .select_label { padding: 0 5px 0 20px; }
.body .colc input.button1 { top: 250px; left: 5px; width: 243px; }
.body .colc input.button2 { top: 275px; left: 5px; width: 243px; }

/* Common */
.app-form-edit-button { cursor: pointer; margin: 0 0 -3px 5px }
.app-table-add-icon { cursor: pointer; }
.field-fixed { height: 15px; padding: 0 0 0 3px; border: 1px solid #cccccc; background: #ffffff; font-size: 12px }
.fl { float: left }
.fr { float: right }
.w-100 { width: 100px }
.w-150 { width: 150px }
.w-200 { width: 200px }
.w-250 { width: 250px }
.w-300 { width: 300px }
.w-350 { width: 350px }
.w-400 { width: 400px }
.w-450 { width: 450px }
.w-500 { width: 500px }

/* HN.FamiliesBrowser */
.family-window { width: 784px; height: 400px; background-color: #FCFCFF; }
.family-window-bg {  }
.family-window-bg .menu { width: 780px; height: 16px; text-align: center; background: #A00100; font: 13px Arial, Helvetica, sans-serif; color: white; padding: 6px 2px; margin: 4px 0; }
.family-window-bg .menu a { font-weight: normal; background: #cd2d2c; color: white; text-decoration: none; padding: 2px 3px; }
.family-window-bg .menu a:hover { background: #FFFFFF; color: #A00100; }
.family-window-bg .menu a.current { background: #A00100; color: #FFFFFF; outline: none; }
.family-window-bg .menu a.current:hover { text-decoration: underline; }

.family-window-bg .cols { width: 785px; height: 325px; background-color: #FFFFFF; overflow: scroll; }
.family-window-bg .colg { float: left; width: 190px; text-align: left; padding-bottom: 2px; background-color: #FFFFFF; }
.family-window-bg .colg .titre { text-align: center; display: block; background: #637382; font: bold 12px Arial, Helvetica, sans-serif; letter-spacing: 1px; text-transform: uppercase; color: white; padding: 5px 13px; }
.family-window-bg .colg .sf { padding-top: 3px; }
.family-window-bg .colg .sf a { display: block; color: #3d4b58; text-decoration: none; font: 11px Arial, Helvetica, sans-serif; letter-spacing: 1px; font-weight: bold; padding: 3px 0 5px 20px; background: url(../ressources/flecheUn.gif) no-repeat left bottom; }
.family-window-bg .colg .sf a:hover { text-decoration: underline; }
.family-window-bg .colg .sf a.currentFolded { background-color: #FFDD82; }
.family-window-bg .colg .sf a.currentUnfolded { background: #FFDD82 url(../ressources/flecheDeux.gif) no-repeat left bottom; }
.family-window-bg .colg .sf a.notCurrentUnfolded { background: url(../ressources/flecheDeux.gif) no-repeat left bottom; }

.family-window-bg .colc { float: left; width: 570px; text-align: left; background-color: #FFFFFF; padding: 0 4px; }
.family-window-bg .colc .ssf { background: url(../ressources/flecheTrois.gif) no-repeat left bottom; padding-bottom: 3px; }
.family-window-bg .colc .ssf a { float: left; width: 264px; display: block; color: #3D4B58; text-decoration: none; font: 11px Arial, Helvetica, sans-serif; letter-spacing: 1px; padding: 0 0 0 10px; margin: 2px 0 1px 8px; border-left: solid 2px #889c48; background: #f6f6f6; }
.family-window-bg .colc .ssf a.current { background-color: #C6D6D8; padding: 0 0 0 10px; }
.family-window-bg .colc .ssf a:hover { background-color: #C6D6D8; text-decoration: none; }
.family-window-bg .colc .ssf a.current:hover { background-color: #889C48; color: #FFFFFF; }
.family-window-bg .colc h1 { font: bold 12px Arial, Helvetica, sans-serif; letter-spacing: 1px; color: #272727; text-decoration: none; padding: 8px 2px; border-bottom: solid 3px #c6d6d8; margin: 0; }

#references { width: 100% !important; }
#references table { width: 100%; border-collapse: collapse }
#references table td { border: 1px solid #000000; }
#references table td.isCat3SA { background: #80FF90!important }
#references table td.isCat3SA input { background: #80FF90!important }
#references table td.isCat3SA:hover { background: #316ac5!important }
#references table td.isCat3SA:hover input { background: #316ac5!important }
input.ref-col { width: 90%; }
#references center { text-align: left; }
#references .intitule { background-color: #E9EFF8;}
</style>

<div id="FamilySelectionWindow"></div>
<div id="FamilyChangeWrap" class="FamilyChangeLayer">
  <div id="FamilyChangeConfirm" class="FamilyChangeLayer family-window">
    <div class="window_title_bar">
      <div>
        <div class="move_img"></div>
        <div class="titletext">Confirmation</div>
        <div style="clear: both;"></div>
      </div>
    </div>
    <div class="family-window-bg">
      <div class="cols"></div>
    </div>
  </div>
  <div id="FamilyChangeConfirmShad" class="FamilyChangeLayer"></div>
</div>


<script type="text/javascript">
var __SID__ = '<?php echo $sid ?>';
var __ADMIN_URL__ = '<?php echo ADMIN_URL ?>';
var __MAIN_SEPARATOR__ = '<?php echo __MAIN_SEPARATOR__ ?>';
var __ERROR_SEPARATOR__ = '<?php echo __ERROR_SEPARATOR__ ?>';
var __ERRORID_SEPARATOR__ = '<?php echo __ERRORID_SEPARATOR__ ?>';
var __OUTPUT_SEPARATOR__ = '<?php echo __OUTPUT_SEPARATOR__ ?>';
var __OUTPUTID_SEPARATOR__ = '<?php echo __OUTPUTID_SEPARATOR__ ?>';
var __DATA_SEPARATOR__ = '<?php echo __DATA_SEPARATOR__ ?>';
var __IP_NOT_VALID__ = '<?php echo __IP_NOT_VALID__ ?>';
var __IP_VALID__ = '<?php echo __IP_VALID__ ?>';
var __IP_FINALIZED__ = '<?php echo __IP_FINALIZED__ ?>';

// FAMILIES //
<?php
$mts["JS CAT LIST"]["start"] = microtime(true);

$families = array();
$families[0]['name'] = '';
$families[0]['ref_name'] = '';
$families[0]['idParent'] = 0;

$result = & $handle->query("select f.id, fr.name, fr.ref_name, f.idParent from families f, families_fr fr where f.id = fr.id", __FILE__, __LINE__);
while ($family = & $handle->fetchAssoc($result))
{
	$families[$family['id']]['name'] = $family['name'];
	$families[$family['id']]['ref_name'] = $family['ref_name'];
	$families[$family['id']]['idParent'] = $family['idParent'];
	if (!isset($families[$family['idParent']]['nbchildren']))
		$families[$family['idParent']]['nbchildren'] = 1;
	else
		$families[$family['idParent']]['nbchildren']++;
	$families[$family['idParent']]['children'][$families[$family['idParent']]['nbchildren']-1] = $family['id'];
}

?>
// TODO intégrer le get des familles en ajax dans l'objet FamiliesBrowser
var families = [];
var familiesIndexByName = [];
var familiesIndexByRefName = [];
var name = 0; var ref_name = 1; var idParent = 2; var nbchildren = 3; var children = 4;

function fam_sort_ref_name(a, b)
{
	if (families[a][ref_name] > families[b][ref_name]) return 1;
	if (families[a][ref_name] < families[b][ref_name]) return -1;
	return 0;
}

<?php
foreach ($families as $id => $fam)
{
	print 'families[' . $id . '] = ["' . str_replace('"', '\"', $fam['name']) . '", "' . $fam['ref_name'] . '", ' . $fam['idParent'] . ', ';
	if (isset($fam['nbchildren']))
	{
		print $fam['nbchildren'] . ', [' . $fam['children'][0];
		for ($i = 1; $i < $fam['nbchildren']; $i++)
			print ", " . $fam['children'][$i];
		print "]";
	}
	else
	{
		print "0, []";
	}
	print  ']; ';
	//print 'familiesIndexById[' . $id . '] = ' . $id . '; ';
	print 'familiesIndexByName["' . str_replace('"', '\"', $fam['name']) . '"] = ' . $id . '; ';
	print 'familiesIndexByRefName["' . $fam['ref_name'] . '"] = ' . $id . ';';
	print "\n";
}
$mts["JS CAT LIST"]["end"] = microtime(true);
?>

// Product's main properties (namespace)
var PMP = {};
var selectedFamily = '';
/* Family selection window */
PMP.fb = new HN.FamiliesBrowser();
PMP.fb.setID("FamilySelectionWindow");
PMP.fb.Build();
PMP.fb.mod = "add";


PMP.fsw = new HN.Window();
PMP.fsw.setID("FamilySelectionWindow");
PMP.fsw.setTitleText("Choisir une famille");
PMP.fsw.setMovable(true);
PMP.fsw.showCancelButton(true);
PMP.fsw.showValidButton(true);
PMP.fsw.setValidFct(function() {
	var family = PMP.fb.getCurFam();
	if (family.id != 0)
	{
            selectedFamily = family.id;

            $("#selectedFamily").val(selectedFamily);
            PMP.fsw.Hide();
            $("#FamilyChangeWrap").css('top', ($("#FamilySelectionWindow").offset().top - 100));
            var FCC = '';
            if($('#selectedFamily').val() != ''){
              var prodList = new Array();
              var confirmFamilyButton = '';
              var liste = ''; //<ul>';
              $('input[name=select]:checked').each(function(index){
                prodList[index] = $(this).parent().parent().find("td.title a").text();
              });
              if(prodList.length != 0){
                var i=0;
//                for(i=0; i<prodList.length; i++){
//                  liste += '<li><b>'+prodList[i]+'</b></li>';
//                };
                liste += '<b>'+prodList.length+'</b>';
//                liste += '</ul>';
                confirmFamilyButton = '<input type="button" value="Confirmer" id="familyChangeConfirmButton" />';
              }else{
                liste += 'Aucun produit n\'est sélectionné</ul>';
              }
              FCC = '<span>Récapitulatif de l\'opération :</span><br /><br />Produits sélectionnés : '+liste+'<br /><br />Famille de destination : <b>'+family.name+'</b><br /><br />'+confirmFamilyButton+'<input type="button" value="Annuler" onClick="javascript:hideFamilyChangeWindow()" />';
            }else
              FCC = '<span>Vous n\'avez sélectionné aucun produit</span><input type="button" value="Annuler" onClick="javascript:hideFamilyChangeWindow()" />';
            $('#FamilyChangeConfirm').find('.cols').css({'height': '380px'});
            $('#FamilyChangeConfirm').find('.cols').html('<div class="contentConfirmFamilyChange">'+FCC+'</div>');
            $("#FamilyChangeWrap").show();
            $("#FamilyChangeConfirm").show();
            $("#FamilyChangeConfirmShad").show();

            $('#familyChangeConfirmButton').bind(
              'click', function(){
              $pdtList = $("form[name='pdtList']");
              var selectList = [];
              $("table.php-list input[name='select']:checked").each(function(){
                      selectList.push($(this).parent().parent().find("input[name='pdtID']").val());
              });
              $pdtList.find("input[name='selectList']").val(selectList.join("|"));
              $pdtList.submit();
            });
	}
});

PMP.fsw.setShadow(true);
PMP.fsw.Build();

</script>





<link type="text/css" rel="stylesheet" href="advertisers.css"/>
<div class="titreStandard">Liste des annonceurs et fournisseurs</div>
<br />
<div class="bg">
	<div align="center">
		<a href="index.php?nb=10&<?php echo $sid . '&filter=' . $filter ?>">Récents</a>
		- <a href="index.php?lettre=0&<?php echo $sid . '&filter=' . $filter ?>">0-9</a>
<?php
    for($i = ord('a'); $i <= ord('z'); ++$i)
        print '		- <a href="index.php?lettre=' . chr($i) . '&' . $sid . '&filter=' . $filter . '">' . strtoupper(chr($i)) . "</a>\n";
?>
	</div>
	<br />
	<br />
<?php
    if($user->rank == COMM)
    {
		if($type == 1) $_url = 'nb=' . $nb;
		else $_url = 'lettre=' . $lettre;
		print '<a href="index.php?' . $_url . '&' . $sid . '&filter=' . ($filter ? '0">Afficher tous les annonceurs et fournisseurs' : '1">Afficher uniquement vos annonceurs et fournisseurs') . "</a>\n" .
			"	<br />\n" . 
			"	<br />\n";
    }
	
?>
Entrer le nom d'un fournisseur à rechercher :
<script type="text/javascript">
function SearchAdvertiser()
{
	document.location.href = 'index.php?lettre=' + escape(document.getElementById('searchValue').value) + '&<?php echo $sid ?>&filter=<?php echo $filter ?>';
}
</script>
<input id="searchValue" type="text" class="champstexte" size="25" />
<input type="button" class="bouton" value="Rechercher" onclick="SearchAdvertiser()" />

<br><br>Afficher les <select onChange="goTo('index.php?nb=' + this.options[this.selectedIndex].value + '&<?php print(session_name() . '=' . session_id() . '&filter=' . $filter) ?>')">
<?php

    foreach($liste as $k => $v)
    {

        $sel = ($nb == $v) ? ' selected' : '';
        print('<option value="' . $v . '"' . $sel . '>' . $v . '</option>');

    }

?></select> derniers annonceurs et fournisseurs ajoutés ou mis à jour. <br><br><?php

    if($type == 0)
    {
        print('<b>Liste des annonceurs et fournisseurs dont le nom commence par ');
        if($lettre == '0')
        {
            $pattern = 'REGEXP(\'^[0-9]\')';
            print('un chiffre :</b><br><br>');
        }
        else
        {
            $pattern = 'like \'' . $lettre . '%\'';
            print('la lettre ' . strtoupper($lettre) . ' :</b><br><br>');
        }

        if($user->rank == COMM && $filter == 1)
        {
            $a = & displayAdvertisers($handle, 'and a.nom1 ' . $pattern . ' order by a.nom1', $user->id);
        }
        else
        {
            $a = & displayAdvertisers($handle, 'where a.nom1 ' . $pattern . ' order by a.nom1');
        }

    }
    else
    {
        print('<b>Liste des '.$nb.' derniers annonceurs et fournisseurs ajoutés ou mis à jour : </b><br><br>');
   
        if($user->rank == COMM && $filter == 1)
        {
            $a = & displayAdvertisers($handle, 'order by a.timestamp desc limit ' . $nb, $user->id);
        }
        else
        {
            $a = & displayAdvertisers($handle, 'order by a.timestamp desc limit ' . $nb);
        }

    }


    if(count($a) > 0)
    {
        print('<ul>');

        foreach($a as $k => $v)
        {
             print('<li><a href="edit.php?id=' . $k . '&' . session_name() . '=' . session_id() . '">' . to_entities($v) . '</a>');
        }

        print('</ul>');
    }


?>
</div><br><br>



	
<div class="titreStandard"><?php

if($from_extranet)
{
	print('Demande de mise à jour des coordonnées de l\'annonceur ou du fournisseur ');
	
	if($user->rank != CONTRIB && ($user->rank != COMM || $user->id == $data[1]) && !isset($OK_ADV_EXT))
	{
		$options =  ' - <a href="del.php?type=edit_adv&id='.$_GET['id'].'&' . session_name() . '=' . session_id() . '" onClick="return confirm(\'Etes-vous sûr de vouloir refuser cette demande de mise à jour ?\')">Rejeter la demande</a> - ';
	}
	else
	{
		$options = ' ';
	}
	
}
else
{
	print('Edition de l\'annonceur ou du fournisseur ');

	if($user->rank != CONTRIB)
	{
		if($user->rank != COMM || $user->id == $data[1])
		{
			$options =  ' - <a href="del.php?id='.$_GET['id'].'&' . session_name() . '=' . session_id() . '" onClick="return confirm(\'Etes-vous sûr de vouloir supprimer cet annonceur ou fournisseur ?\n\nAttention, toutes les fiches produits de cet annonceur ou fournisseur ainsi que ses demandes de contact seront définitivement perdues !\n\nEn cas de doute désactivez-le simplement, ses produits ne seront plus en ligne tout en conservant les informations qui lui sont rattachées.\')">Supprimer</a>';
		}

		$options .= ' - <a href="../actions.php?type=annonceur&id='.$_GET['id'].'&' . session_name() . '=' . session_id() . '">Historique des actions</a> - ';
	}
	else
	{
		$options = '';
	}

}

$options .= '<a href="ve.php?id=' . $_GET['id']. '&' . session_name() . '=' . session_id() . '" target="_blank">Extranet (Bêta !)</a>';


//////////////////////////


    $next = true;

    $newname  = $data[2];
    $newstate = $data[0] ? 'actif' : 'inactif';

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
	
        if(!$error){
		
            if($ok){
                $out = 'Annonceur ou fournisseur édité avec succès.';
				if(isset($_POST['payment_mean'])){
					$sql_update_payment = " UPDATE `advertisers` SET `direct_debit` = '".$_POST['payment_mean']."' 
									WHERE `id` ='".$_GET['id']."' ";
					mysql_query($sql_update_payment);
				}
                $newname = $nom1;
                $newstate = $active ? 'actif' : 'inactif';
				
            }else{
                $out = 'Erreur lors de l\'édition de l\'annonceur ou du fournisseur.<br/>'.$errorstring;
            }

            print(to_entities($newname) . $options . ' - Annonceur ou fournisseur actuellement ' . $newstate . '</div><br><div class="bg">');
            print('<br><div class="confirm">' . $out . '</div><br><br>');

            $next = false;

        }else{   
            print(to_entities($newname) . $options . ' - Annonceur ou fournisseur actuellement ' . $newstate . '</div><br><div class="bg">');
            print('<font color="red">Une ou plusieurs erreurs sont survenues lors de la validation : <br>' . $errorstring  . '</font><br><br>');
            $next = true;
        }

    }else{
        print(to_entities($newname) . $options . ' - Annonceur ou fournisseur actuellement ' . $newstate . '</div><br><div class="bg" style="display:none;">');
    }



    if($next){


?>

</div><br>

<!-- Internal notes and messenger -->
	<div class="module_internal_notes" style="margin-top: -20px;">
		<button id="module_internal_notes_item-cart-show-note" class="btn ui-state-default ui-corner-all fr"><span class="icon note"></span> Laisser une note</button>
		<button id="module_internal_notes_item-cart-add-note" class="btn ui-state-default ui-corner-all fr"><span class="icon note-add"></span> Poster la note</button>
		<button id="module_internal_notes_item-cart-cancel-note" class="btn ui-state-default ui-corner-all fr"><span class="icon note-delete"></span> Annuler</button>
		<div id="module_internal_notes_item-cart-note" class="block note">
			<div>Laisser une note :</div>
			<textarea></textarea>
			<div class="attachments">
				<button id="module_internal_notes_add-msn-attachment" type="button" class="btn ui-state-default ui-corner-all">Ajouter une pièce jointe</button>
				Formats autoris&eacute;s : PDF, Document Word ou image '.jpg'
				<ul id="module_internal_notes_item-cart-attachment-list" class="attachment-list">
				</ul>
			</div>
		</div>
		<div class="zero"></div>
		<div id="module_internal_notes_item-cart-notes">
			<div class="block fold-block folded">
				<div class="title">
					Notes internes liées à ce fournisseur
					<span class="icon-fold folded">+</span>
					<span class="icon-fold unfolded">-</span>
				</div>
				<div class="messages fold-content">
					<ul>
					</ul>
				</div>
			</div>
		</div>
		<br />
	<!-- End Internal notes and messenger -->	

	<!-- attachment dialog box -->
		<div id="module_internal_notes_upload-msn-attachment-db" title="Ajouter une pièce jointe" class="db">
			<form name="loadDoc" method="post" action="" enctype="multipart/form-data">
				<input type="hidden" name="action" value="load-doc" /> 
				<input type="hidden" name="supplier" value="" />
				<!-- <input type="hidden" name="cmdId" value="<?php echo $o['id'] ?>" /> -->
				Nom : <input type="text" name="module_internalnotes_aliasPjMessFileName"	id="module_internalnotes_aliasPjMessFileName" value="" />
				<br />
				<br />
				Sélectionnez le document à lier au message (PDF, Document Word ou image '.jpg')<br />
				<br />
				<input type="file" name="module_internalnotes_pjMessFile"  id="module_internalnotes_pjMessFile" accept="application/pdf, application/msword, image/jpeg" />
				
				<br />
				<img id="module_internal_notes_upload_img_loading" class="loading-gif" src="<?php echo EXTRANET_URL ?>ressources/images/lightbox-ico-loading.gif" />
			</form>
		</div>
	</div>
<!-- End attachment dialog box -->


<div id="adviser_edit_fields" class="bg">





<script language="JavaScript">
<!--

function namesearch()
{
    var handle = document.editAdvertiser;
    if(handle.nom1.value.length < 3)
    {
        alert('Vous devez saisir au moins 3 caractères du nom de l\'annonceur ou du fournisseur avant de lancer la recherche.');
        handle.nom1.focus();
    }
    else
    {
        window.open('search.php?<?php print(session_name() . '=' . session_id()) ?>&name=' + escape(handle.nom1.value) , 'Recherche', 'menubar=no,top=100,left=400,height=500,width=700');
    }
}

<?php

        if($user->rank != CONTRIB)
        {           // traitement cout + annonceurs liés

?>

///////////////////////////////////////////////
  

function cout(val)
{
    if(val == 0)
    {
        testAndWrite('cout', '<br><br><br><br>');

    }
    else if(val == 1)
    {
        // coût variable
        testAndWrite('cout', '<br>&nbsp; &nbsp; &nbsp; &nbsp;Date de début de facturation : <select name="jourf"><?php
        
        for($i = 1; $i <= 31; ++$i)
        {
            if(isset($_POST['jourf']))
            {
                $sel = ($_POST['jourf'] == $i) ? 'selected' : '';
            }
            else
            {
                $sel = ($i == $jourf) ? 'selected' : '';
            }

            print('<option value="' . $i . '" ' . $sel . '>' . $i . '</option>');
        }
        
        ?></select> <select name="moisf"><?php
        
        for($i = 1; $i <= 12; ++$i)
        {
            if(isset($_POST['moisf']))
            {
                $sel = ($_POST['moisf'] == $i) ? 'selected' : '';
            }
            else
            {
                $sel = ($i == $moisf) ? 'selected' : '';
            }
        
            print('<option value="' . $i . '" ' . $sel . '>' . $i . '</option>');
        }
        
        ?></select> <select name="anneef"><option value="<?php

        $date = date('Y');
        
        print($date . '"');
        
        if((isset($_POST['anneef']) && $_POST['anneef'] == $date) || (isset($anneef) && $anneef == $date))
        {
            print(' selected');
        }

        ?>><?php print($date) ?></option><option value="<?php
        
        print($date + 1 . '"');
        
        if((isset($_POST['anneef']) && $_POST['anneef'] == ($date + 1)) || (isset($anneef) && $anneef == ($date + 1)))
        {
            print(' selected');
        }

        ?>"><?php print($date + 1) ?></option></select><br>&nbsp; &nbsp; &nbsp; &nbsp;Coût d\'un contact : <input type="text" name="coutcontact" size="5" maxlength="5" class="champstexte" value="<?php print(to_entities($coutcontact)) ?>"><br><br>');
    }
    else
    {
        // coût fixe
        testAndWrite('cout', '<br>&nbsp; &nbsp; &nbsp; &nbsp;Date de fin d\'abonnement : <select name="joura"><?php
        
        for($i = 1; $i <= 31; ++$i)
        {
            if(isset($_POST['joura']))
            {
                $sel = ($_POST['joura'] == $i) ? 'selected' : '';
            }
            else
            {
                $sel = ($i == $joura) ? 'selected' : '';
            }

            print('<option value="' . $i . '" ' . $sel . '>' . $i . '</option>');
        }
        
        ?></select> <select name="moisa"><?php

        for($i = 1; $i <= 12; ++$i)
        {
            if(isset($_POST['moisa']))
            {
                $sel = ($_POST['moisa'] == $i) ? 'selected' : '';
            }
            else
            {
                $sel = ($i == $moisa) ? 'selected' : '';
            }
        
            print('<option value="' . $i . '" ' . $sel . '>' . $i . '</option>');
        }
        
        ?></select> <select name="anneea"><option value="<?php
        
        $date = date('Y');
        
        print($date . '"');
        
        if((isset($_POST['anneea']) && $_POST['anneea'] == $date) || (isset($anneea) && $anneea == $date))
        {
            print(' selected');
        }

        ?>><?php print($date) ?></option><option value="<?php

        print($date + 1 . '"');
        
        if((!isset($_POST['anneea']) || $_POST['anneea'] == ($date + 1)) && (!isset($anneea) || $anneea == ($date + 1)))
        {
            print(' selected');
        }

        ?>"><?php print($date + 1) ?></option></select><br><br><br>');
    }
}


///////////////////////////////////////////////


var listeLinkedHidden = '<?php print($listeHidden) ?>';
var listeLinked       = '<?php print($listeShown) ?>';

function LinkAdv(val)
{
	if(val == '')
    {
        alert('Merci de sélectionner un annonceur ou un fournisseur avant de le lier.');
        document.editAdvertiser.linksList.focus();
        return;
    }
    

    if(val == '<?php print($_GET['id']) ?>')
    {
        alert('Vous ne pouvez lier un annonceur ou un fournisseur à lui-même.');
        document.editAdvertiser.linksList.focus();
        return;
    }

    if(listeLinkedHidden.indexOf(val + ',', 0) != -1)
    {
        alert('Cet annonceur ou fournisseur est déjà présent dans la liste des annonceurs ou fournisseurs liés.');
        document.editAdvertiser.linksList.focus();
        return;
    }


    document.editAdvertiser.ok.disabled  = true;
    document.editAdvertiser.nok.disabled = true;

    document.editAdvertiser.linker.disabled = true;
    document.editAdvertiser.linker.value = 'Liaison en cours';
    
    /////////////
    
    var uniq = new Date();
        uniq = uniq.getTime();

    var query = '<?php print(session_name() . '=' . session_id()) ?>&time='+ uniq +'&id=' + escape(val);

    var data  = getContent('link.php', query);

    if(data == -1)
    {
        alert('Une erreur est survenue : impossible de récupérer les données de l\'annonceur ou du fournisseur.');
    }
    else
    {
        var tab = data.split('<separator>');

        if(listeLinkedHidden == '')
        {
            listeLinked = '<a href="javascript:remove(\''+tab[0]+'\', \''+tab[1]+'\')">' + tab[1] + '</a>';
        }
        else
        {
            listeLinked += ' - <a href="javascript:remove(\''+tab[0]+'\', \''+tab[1]+'\')">' + tab[1] + '</a>';
        }

        listeLinkedHidden += tab[0] + ',';

        testAndWrite('advertisers', listeLinked + '<input type="hidden" name="listeLinked" value="'+listeLinkedHidden+'">');
    }
    
    document.editAdvertiser.ok.disabled  = false;
    document.editAdvertiser.nok.disabled = false;

    document.editAdvertiser.linker.disabled = false;
    document.editAdvertiser.linker.value = "Lier";

    document.editAdvertiser.linksList.focus();

}


function remove(id, name)
{

    var sentence = '<a href="javascript:remove\\(\\\''+id+'\\\', \\\''+name+'\\\'\\)">'+name+'</a>';

    if(listeLinkedHidden == id + ',')
    {           
        exp = new RegExp(sentence, '');
    }
    else if(listeLinkedHidden.indexOf(id + ',') == 0)
    {
        exp = new RegExp(sentence + ' - ', '');
    }
    else
    {
        exp = new RegExp(' - ' + sentence, '');
    }

    listeLinked = listeLinked.replace(exp, '');

    if(listeLinked == '')
    {
        listeLinked = 'Actuellement aucun annonceur ni fournisseur n\'est lié à celui en cours de création.';
    }
    
    var exp = new RegExp(id + ',', '');
    listeLinkedHidden = listeLinkedHidden.replace(exp, '');

    testAndWrite('advertisers', listeLinked + '<input type="hidden" name="listeLinked" value="'+listeLinkedHidden+'">');


}


<?php } // fin non contrib ?>

function toggle_foptions(value)
{
    if (value == "<?php echo __ADV_CAT_SUPPLIER__ ?>")
    {
        document.getElementById("OptionsFournisseurs").style.display = 'block';
    }
    else
    {
        document.getElementById("OptionsFournisseurs").style.display = 'none';
    }
}
function toggle_margeRemise(value)
{
	if (value == "1")
	{
		document.getElementById("label_remise").style.display = 'inline';
		document.getElementById("label_marge").style.display = 'none';
		document.editAdvertiser.peuChangerTaux.disabled = false;
	}
	else
	{
		document.getElementById("label_marge").style.display = 'inline';
		document.getElementById("label_remise").style.display = 'none';
		document.editAdvertiser.peuChangerTaux.disabled = true;
	}
}

function perform_toggles()
{
	toggle_margeRemise(<?php echo $prixPublic ?>);
	toggle_foptions(<?php echo $category ?>);
}

window.onload = perform_toggles;

//-->
</script>
<?php

if(isset($_GET['a']) && $_GET['a'] == 'unlink' && is_file(ADVERTISERS_LOGOS_INC . $_GET['id'] . '.jpg'))
{
    print('<div class="confirm">');

    if(unlink(ADVERTISERS_LOGOS_INC . $_GET['id'] . '.jpg'))
    {
        print('Logo annonceur ou fournisseur supprimé avec succès.');
    }
    else
    {
        print('Erreur lors de la suppression du logo annonceur ou fournisseur.');
    }
    
    print('</div><br><br>');

}

?>
<form name="editAdvertiser" method="post" action="edit.php?<?php print(session_name() . '=' . session_id() . '&id=' . $_GET['id']); if(isset($_GET['type'])) print('&type=' . $_GET['type']) ?>" class="formulaire" enctype="multipart/form-data">
<table>
	<tr><td class="intitule">Nom 1 :</td><td><input class="champstexte" type="text" size="25" maxlength="255" name="nom1" onBlur="this.value = trim(this.value.toUpperCase())" value="<?php print(to_entities($nom1)) ?>"> * &nbsp; &nbsp; &nbsp; <input type="button" class="bouton" value="Rechercher" onClick="namesearch()"></td></tr>
	<tr><td class="intitule">Nom 2 :</td><td><input class="champstexte" type="text" size="25" maxlength="255" name="nom2" onBlur="this.value = trim(this.value.toUpperCase())" value="<?php print(to_entities($nom2)) ?>"></td></tr>
<?php
if($user->rank == HOOK_NETWORK || $user->rank == COMMADMIN || $user->rank == COMM)
{
?>
	<tr>
		<td class="intitule">Cat&eacute;gorie :</td>
		<td>
			<select name="category" onChange="toggle_foptions(this.options[this.selectedIndex].value)">
<?php
	foreach($adv_cat_list as $adv_cat_id => $adv_cat_data)
		print "\t\t\t\t<option value=\"" . $adv_cat_id . "\"" . ($category == $adv_cat_id ? " selected" : "") . ">" . $adv_cat_data["name"] . "</option>\n";
?>
			</select>
		</td>
	</tr>
<?php
}
?>
 <tr><td class="intitule">Adresse 1 :</td><td><input class="champstexte" type="text" size="35" maxlength="255" name="adresse1" onBlur="this.value = trim(this.value.toUpperCase())" value="<?php print(to_entities($adresse1)) ?>"> *</td></tr>
 <tr><td class="intitule">Adresse 2 :</td><td><input class="champstexte" type="text" size="35" maxlength="255" name="adresse2" onBlur="this.value = trim(this.value.toUpperCase())" value="<?php print(to_entities($adresse2)) ?>"></td></tr>
 <tr><td class="intitule">Ville :</td><td><input class="champstexte" type="text" size="25" maxlength="255" name="ville" onBlur="this.value = trim(this.value.toUpperCase())" value="<?php print(to_entities($ville)) ?>"> *</td></tr>
 <tr><td class="intitule">Code postal :</td><td><input class="champstexte" type="text" size="5" name="cp" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($cp)) ?>"> *</td></tr>
 <tr><td class="intitule">Pays :</td><td><input class="champstexte" type="text" size="15" maxlength="255" name="pays" onBlur="this.value = trim(this.value.toUpperCase())" value="<?php print(to_entities($pays)) ?>"> *</td></tr>
</table>

<!-- Options fournisseurs -->
<script src="<?php echo ADMIN_URL ?>ressources/js/ui.tabs.js" type="text/javascript"></script>
<script type="text/javascript">
$(function() {
	$("#OptionsFournisseurs ul").tabs({  });
	GetPriceMod();
});

var AJAXHandle = {
	type : "GET",
	url: "ModPricesAJAXserver.php",
	dataType: "json",
	error: function (XMLHttpRequest, textStatus, errorThrown) {
		$("#PerfReqLabelModPrices").text(textStatus);
	},
	success: function (data, textStatus) {
		if (data.error) $("#PerfReqLabelModPrices").text(data.error);
		else
		{
			var tbody = $("#modprices-historic tbody");
			tbody.empty();
			for (i = 0; i < data.length; i++)
			{
				var dateo = new Date(); dateo.setTime(data[i].date*1000);
				date = dateo.getDate() + "/" + (dateo.getMonth()+1) + "/" + dateo.getFullYear() + " " + dateo.getHours() + ":" + dateo.getMinutes();
				var val = parseFloat(data[i].val);
				val = val >= 0 ? "+"+val : val;
				var type = data[i].type == "f" ? "€" : "%";
				
				tbody.append(
					"<tr id=\"price-mod-"+i+"\">" +
					"	<td class=\"date\">"+date+"</td>" +
					"	<td class=\"mod\">"+val+type+"</td>" +
					"	<td class=\"undo\">" +
					(i == 0 ? "		<img src=\"<?php echo ADMIN_URL ?>ressources/icons/arrow_undo_red.png\" alt=\"annuler\" title=\"annuler\" onclick=\"UndoPriceMod("+i+")\"/>" : "") +
					"	</td>" +
					"</tr>");
			}
			$("#PerfReqLabelModPrices").text("");
		}
	}
};

function GetPriceMod()
{
	AJAXHandle.data = "action=get&advID=<?php echo $_GET['id'] ?>";
	$.ajax(AJAXHandle);
}
function AddPriceMod()
{
	var as = "action=add";
	as += "&advID=<?php echo $_GET['id'] ?>";
	as += "&pmv=" + escape(document.getElementById("price-mod-val").value);
	as += "&pmt=" + escape(document.getElementById("price-mod-type").value);
	
	AJAXHandle.data = as;
	$.ajax(AJAXHandle);
}
function UndoPriceMod(id)
{
	id = parseInt(id);
	if (document.getElementById("price-mod-"+id))
	{
		if (confirm("Voulez-vous vraiment annuler cette modification ?"))
		{
			AJAXHandle.data = "action=undo&advID=<?php echo $_GET['id'] ?>&id="+id;
			$.ajax(AJAXHandle);
		}
	}
}

</script>

<br/>
<div class="group-opt" id="OptionsFournisseurs">
	<h3>Options fournisseurs</h3>
	<ul>
		<li><a href="#tab-settings">Configuration</a></li>
		<li><a href="#tab-modprices">Modifier les prix</a></li>
	</ul>
	<div id="tab-settings">
		<div class="prop">
			<label>Délai de livraison :</label>
			<input class="champstexte" type="text" size="35" maxlength="255" name="delai_livraison" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($delai_livraison)) ?>"/> *
		</div>
		<?php
			$sql_del  = "SELECT delai_livraison_num,contacts_not_read_notification FROM advertisers WHERE id='".$_GET['id']."' ";
			$req_del  =  mysql_query($sql_del);
			$data_del =  mysql_fetch_object($req_del);
		?>
		<div class="prop">
			<label>Délai en jours :</label>
			<input class="champstexte" type="text" size="35" maxlength="255" name="delai_expedition" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($data_del->delai_livraison_num)) ?>"/>
		</div>
		
		<?php/*<div class="prop">
			<label>Frais de port :</label>
			<input class="champstexte" type="text" size="35" maxlength="255" name="shipping_fee" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($shipping_fee)) ?>"/> *
		</div>*/?>
		<div class="prop">
			<label>Type de prix :</label>
			<select id="adv_price_type" name="prixPublic" class="champstexte" onChange="toggle_margeRemise(this.form.elements['prixPublic'].options[this.form.elements['prixPublic'].selectedIndex].value)">
				<option value="0"<?php if($prixPublic != '1') print(' selected') ?>>Fournisseur</option>
				<option value="1"<?php if($prixPublic == '1') print(' selected') ?>>Public</option>
			</select>
		</div>
		<div class="prop">
			<label id="label_marge">Taux de marge :</label><label id="label_remise">Taux de remise :</label>
			<input id="adv_margin" class="champstexte" type="text" size="8" maxlength="255" name="margeRemise" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($margeRemise)) ?>"/>% *
      <input id="mod-pdt-margin-btn" type="button" value="appliquer à tous les produits"/>
      <div id="mod-pdt-margin-dialog" title="Modifier taux marge/remise">
        <span class="tips"></span>
      </div>
		</div>
		<div class="prop">
			<label> - Modifiable sur l'extranet :</label>
			<input class="champstexte" type="checkbox" name="peuChangerTaux"<?php if($peuChangerTaux == '1') print(' checked')?>/>
		</div>
		<div class="prop">
			<label>Arrondi :</label>
			<select name="arrondi" class="champstexte">
				<option value="0"<?php if($arrondi == '0') print(' selected') ?>>Aucun</option>
				<option value="1"<?php if($arrondi == '1' || $arrondi == '') print(' selected') ?>>Au dixième d'euro supérieur</option>
			</select>&nbsp;&nbsp;
		</div>
		<div class="prop">
			<label>TVA par défaut :</label>
			<select name="idTVA" class="champstexte">
<?php
	foreach ($listeTVAs as $v) print('   <option value="' .$v[0]. '"' . (($idTVA == $v[0]) ? ' selected' : '') . '>' . $v[1] . ' ' . $v[2] . '%</option>' . "\n");
?>			</select>
		</div>
		<div class="prop">
			<label>Contrainte de prix :</label>
			<input class="champstexte" type="text" size="15" maxlength="255" name="contraintePrix" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($contraintePrix)) ?>"/>€
		</div>
              <div class="prop">
			<label>Mise sous devis pas défaut :</label>
			<input class="checkbox" type="checkbox"  name="asEstimate" <?php echo $asEstimate ? 'checked="checked"' : '' ?>/>
		</div>
	</div>
	<div id="tab-modprices">
		<div id="PerfReqLabelModPrices" class="PerfReqLabel"></div>
		<input type="text" class="champstexte" id="price-mod-val" size="20" maxlength="255" value=""/>
		<select id="price-mod-type">
			<option value="v">%</option>
			<option value="f">€</option>
		</select>
		<input type="button" value="modifier" onclick="AddPriceMod()"/>
		<hr/>
		<h4>Historique des dernières modifications</h4>
		<table id="modprices-historic" cellspacing="0" cellpadding="0">
			<thead>
			<tr>
				<th class="date">date</th>
				<th class="mod">modification</th>
				<th class="undo"></th>
			</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
</div>

<br />
<table>
	<tr><td class="intitule">Garantie par défaut :</td><td><input class="champstexte" type="text" size="50" name="warranty" value="<?php echo to_entities($warranty) ?>"/></td></tr>
	<tr><td class="intitule">Afficher le message d'aide :</td><td><input class="champstexte" type="checkbox" name="help_show"<?php if($help_show == '1') print(' checked')?>/></td></tr>
	<tr><td class="intitule">Message d'aide :</td><td><textarea class="champstexte" type="text" rows="5" name="help_msg" style="width: 500px"/><?php print(to_entities($help_msg)) ?></textarea></td></tr>
	<tr><td class="intitule">&nbsp;</td><td>&nbsp;</td></tr>
	<tr><td class="intitule">Personne à contacter :</td><td><input class="champstexte" type="text" size="25" maxlength="255" name="contact" onBlur="this.value = trim(this.value); maj = this.value.charAt(0).toUpperCase(); if(this.value.length > 1) this.value = maj + this.value.substring(1, this.value.length); else this.value = maj" value="<?php print(to_entities($contact)) ?>"></td></tr>
	<tr><td class="intitule">Adresse e-mail :</td><td><input class="champstexte" type="text" size="35" maxlength="255" name="email" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($email)) ?>"></td></tr>
	<tr><td class="intitule">Adresse du site web :</td><td><input class="champstexte" type="text" size="35" maxlength="255" name="url" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($url)) ?>"></td></tr>
	<tr><td class="intitule">Téléphone 1 :</td><td><input class="champstexte" type="text" size="15" name="tel1" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($tel1)) ?>"> * <?php if($tel1){ ?><a  href="tel:<?php echo preg_replace('/[^0-9\+.]?/', '', $tel1) ?>"><?php echo preg_replace('/[^0-9\+.]?/', '', $tel1) ?> <img src="../ressources/icons/telephone.png" alt="Tel" style="vertical-align:middle" /></a><?php } ?></td></tr>
	<tr><td class="intitule">Téléphone 2 :</td><td><input class="champstexte" type="text" size="15" name="tel2" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($tel2)) ?>"> <?php if($tel2){ ?><a href="tel:<?php echo preg_replace('/[^0-9\+.]?/', '', $tel2) ?>"><?php echo preg_replace('/[^0-9\+.]?/', '', $tel2) ?> <img src="../ressources/icons/telephone.png" alt="Tel" style="vertical-align:middle" /></a><?php } ?></td></tr>
	<tr><td class="intitule">Fax 1 :</td><td><input class="champstexte" type="text" size="15" name="fax1" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($fax1)) ?>"> *</td></tr>
	<tr><td class="intitule">Fax 2 :</td><td><input class="champstexte" type="text" size="15" name="fax2" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($fax2)) ?>"></td></tr>
	<tr><td class="intitule">Logo :</td><td style="font-family: Arial, Helvetica, sans-serif; font-size: 12px"><input class="champstexte" type="file" size="35" name="logo"> Image au format JPG <?php if(is_file(ADVERTISERS_LOGOS_INC . $_GET['id'] . '.jpg')){ print('[<a href="' . ADVERTISERS_LOGOS_URL . $_GET['id'] . '.jpg" target="_blank">Voir le logo actuel</a>] [<a href="edit.php?id='.$_GET['id'].'&' . session_name() . '=' . session_id() . '&a=unlink">Supprimer le logo</a>]'); }  ?></td></tr>
 <?php if ($userPerms->has("m-prod--sm-partners-manage-profil","e")) { ?>
	<tr>
    <td class="intitule">Commercial associé :</td>
    <td>
      <select name="commercial">
       <?php foreach($comm_profils as $comm_profil) { ?>
        <option value="<?php echo $comm_profil["id"] ?>"<?php if($comm_profil["id"]==$commercial) { ?> selected="selected"<?php } ?>><?php echo to_entities($comm_profil["name"]) ?></option>
       <?php } ?>
      </select> *
    </td>
  </tr>
 <?php } ?>
  <tr><td class="intitule">ID compte client :</td><td><input class="champstexte" type="text" size="15" name="client_id" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($client_id)) ?>"></td></tr>
</table>
<?php if($user->rank != CONTRIB) { 
	$sql_check = "SELECT direct_link_printable_orders 
				  FROM   advertisers 
				  WHERE  id='".$_GET['id']."' ";
	$req_check =  mysql_query($sql_check);
	$data_check=  mysql_fetch_object($req_check);
	
	if($data_check->direct_link_printable_orders == 1) $checked =' checked="checked" ';
	else $checked =' ';
?>

<input type="checkbox" name="email_order" <?= $checked ?> /> <label for="from_web">Insérer les liens vers les commandes fournisseur dans les mails d'ordre</label><br/>
<input type="checkbox" name="from_web" <?php if ($from_web) { ?>checked="checked"<?php } ?>/> <label for="from_web">Permettre l'accès à l'extranet via un lien par email</label><br/>
<input type="checkbox" name="show_infos_online"<?php if ($show_infos_online) { ?>checked="checked"<?php } ?> /> <label for="show_infos_online">Afficher en ligne les coordonnées de l'annonceur après une demande de lead</label><br/>
<input type="checkbox" name="noLeads2in" <?php if ($noLeads2in) { ?>checked="checked"<?php } ?>/> <label for="noLeads2in">Désactiver la réception des leads secondaires</label><br/>
<input type="checkbox" name="noLeads2out" <?php if ($noLeads2out) { ?>checked="checked"<?php } ?>/> <label for="noLeads2out">Désactiver l'émission de leads secondaires</label><br/>

<?php
	if($data_del->contacts_not_read_notification == 1){ ?>
	<input type="checkbox" name="noAlert2out"  checked="checked" value="0" /> <label for="noAlert2out">Désactiver  les alertes de notification des contacts non lus</label><br/>
	<?php }else{ ?>  
		<input type="checkbox" name="noAlert2out" value="1" /> <label for="noAlert2out">Activer les alertes de notification des contacts non lus</label><br/>
<?php } ?>

<br />
<div id="ContactBlock">
	<div id="ContactMenu">
<?php
for ($i = 0; $i < 10; $i++) print '<a href="contact' . $i. '">Contact ' . ($i+1) . '</a>';
?>
	</div>
	<table id="contact0">
		<tr><td class="intitule">Prénom du contact :</td><td><input class="champstexte" type="text" size="25" maxlength="255" name="prenomcontact" onBlur="this.value = trim(this.value); maj = this.value.charAt(0).toUpperCase(); if(this.value.length > 1) this.value = maj + this.value.substring(1, this.value.length); else this.value = maj" value="<?php print(to_entities($prenomcontact)) ?>" /></td></tr>
		<tr><td class="intitule">Nom du contact :</td><td><input class="champstexte" type="text" size="25" maxlength="255" name="nomcontact" onBlur="this.value = trim(this.value); maj = this.value.charAt(0).toUpperCase(); if(this.value.length > 1) this.value = maj + this.value.substring(1, this.value.length); else this.value = maj" value="<?php print(to_entities($nomcontact)) ?>" /></td></tr>
		<tr><td class="intitule">Adresse e-mail du contact :</td><td><input class="champstexte" type="text" size="35" maxlength="255" name="emailcontact" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($emailcontact)) ?>"></td></tr>
		<tr><td class="intitule">Critère de priorisation :</td>
			<td><select name="critere">
<?php
			for($i = 1; $i <= 10; ++$i)
			{
				$sel = ($i == $critere) ? 'selected' : '';
				print '				<option value="' . $i . '" ' . $sel . '>' . $i . "</option>\n";
			}
?>
			</select></td>
		</tr>
	</table>
<?php
for ($i = 1; $i < 10; $i++)
{
?>
	<table id="contact<?php echo $i ?>" style="display: none">
		<tr><td class="intitule">Prénom du contact :</td><td><input class="champstexte" type="text" size="25" maxlength="255" name="prenomcontact<?php echo $i ?>" onBlur="this.value = trim(this.value); maj = this.value.charAt(0).toUpperCase(); if(this.value.length > 1) this.value = maj + this.value.substring(1, this.value.length); else this.value = maj" value="<?php echo to_entities($contacts[$i]['prenom']) ?>" /></td></tr>
		<tr><td class="intitule">Nom du contact :</td><td><input class="champstexte" type="text" size="25" maxlength="255" name="nomcontact<?php echo $i ?>" onBlur="this.value = trim(this.value); maj = this.value.charAt(0).toUpperCase(); if(this.value.length > 1) this.value = maj + this.value.substring(1, this.value.length); else this.value = maj" value="<?php echo to_entities($contacts[$i]['nom']) ?>" /></td></tr>
		<tr><td class="intitule">Adresse e-mail du contact :</td><td><input class="champstexte" type="text" size="35" maxlength="255" name="emailcontact<?php echo $i ?>" onBlur="this.value = trim(this.value)" value="<?php echo to_entities($contacts[$i]['email']) ?>"></td></tr>
		<tr><td class="intitule">Critère de priorisation :</td>
			<td><select name="critere<?php echo $i ?>">
<?php
			for($j = 1; $j <= 10; ++$j)
			{
				$sel = ($j == to_entities($contacts[$i]['critere'])) ? 'selected' : '';
				print '				<option value="' . $j . '" ' . $sel . '>' . $j . "</option>\n";
			}
?>
			</select></td>
		</tr>
	</table>
<?php
}
?>
<script type="text/javascript">
as = document.getElementById('ContactMenu').getElementsByTagName('a');

for (i = 0; i < as.length; i++)
{
	var link = as[i].href.split('/');
	as[i].contact = link[link.length-1];
	as[i].onclick = function () {
		for (i = 0; i < as.length; i++)
		{
			document.getElementById(as[i].contact).style.display = 'none';
			as[i].className = '';
		}
		this.className = 'selected';
		document.getElementById(this.contact).style.display = 'block';
		
		return false;
	}
}

as[0].className = 'selected';

</script>
</div>
<br />

<div class="section">
	<div class="title">Code HTML en Front Office pour catalogue</div>
	<textarea name="catalog_code" rows="4" style="width: 934px"><?php echo $catalog_code ?></textarea>
</div>


<!-- latest additions -->
<input type="hidden" id="module_internal_notes_hidden_global_id" value="<?php echo $_GET["id"] ?>" />
<input type="hidden" id="module_internal_notes_hidden_attachments_id" value="" />
<script type="text/javascript" src="advertisers.js"></script>
<script type="text/javascript">
	$(function(){ HN.TC.BO.Adv.Init(<?php echo $_GET["id"] ?>); });
	$(function(){ HN.TC.BO.RazCredit.Init(<?php echo $_GET["id"] ?>); });
	module_internal_notes_init_internal_notes(<?php echo $_GET["id"] ?>);
</script>


<?php $notReqFields = array_flip($notRequiredFields) ?>
<div class="section">
	<div class="title">Champs par défaut requis</div>
	<table class="required-field-table" cellspacing="0" cellpadding="0">
	<thead>
		<tr><th class="label">Champs</th><th>Requis</th></tr>
	</thead>
	<tbody>
		<tr><td class="label"><label for="nr_fonction">Fonction</label></td><td class="input"><input type="checkbox" name="nr_fonction"<?php echo (isset($notReqFields["fonction"]) ? "" : " checked=\"checked\"") ?>/></td></tr>
		<tr><td class="label"><label for="nr_societe">Nom société / organisation</label></td><td class="input"><input type="checkbox" name="nr_societe"<?php echo (isset($notReqFields["societe"]) ? "" : " checked=\"checked\"") ?>/></td></tr>
		<tr><td class="label"><label for="nr_adresse">Adresse</label></td><td class="input"><input type="checkbox" name="nr_adresse"<?php echo (isset($notReqFields["adresse"]) ? "" : " checked=\"checked\"") ?>/></td></tr>
		<tr><td class="label"><label for="nr_complement">Complément(ZI,BP,etc)</label></td><td class="input"><input type="checkbox" name="nr_complement"<?php echo (isset($notReqFields["complement"]) ? "" : " checked=\"checked\"") ?>/></td></tr>
		<tr><td class="label"><label for="nr_cp">Code Postal</label></td><td class="input"><input type="checkbox" name="nr_cp"<?php echo (isset($notReqFields["cp"]) ? "" : " checked=\"checked\"") ?>/></td></tr>
		<tr><td class="label"><label for="nr_ville">Ville</label></td><td class="input"><input type="checkbox" name="nr_ville"<?php echo (isset($notReqFields["ville"]) ? "" : " checked=\"checked\"") ?>/></td></tr>
		<tr><td class="label"><label for="nr_pays">Pays</label></td><td class="input"><input type="checkbox" name="nr_pays"<?php echo (isset($notReqFields["pays"]) ? "" : " checked=\"checked\"") ?>/></td></tr>
		<tr><td class="label"><label for="nr_nb_salarie">Taille salariale</label></td><td class="input"><input type="checkbox" name="nr_nb_salarie"<?php echo (isset($notReqFields["nb_salarie"]) ? "" : " checked=\"checked\"") ?>/></td></tr>
		<tr><td class="label"><label for="nr_secteur_activite">Secteur d'activité</label></td><td class="input"><input type="checkbox" name="nr_secteur_activite"<?php echo (isset($notReqFields["secteur_activite"]) ? "" : " checked=\"checked\"") ?>/></td></tr>
		<tr><td class="label"><label for="nr_code_naf">Code NAF</label></td><td class="input"><input type="checkbox" name="nr_code_naf"<?php echo (isset($notReqFields["code_naf"]) ? "" : " checked=\"checked\"") ?>/></td></tr>
		<tr><td class="label"><label for="nr_num_siret">Numéro SIRET</label></td><td class="input"><input type="checkbox" name="nr_num_siret"<?php echo (isset($notReqFields["num_siret"]) ? "" : " checked=\"checked\"") ?>/></td></tr>
	</tbody>
	</table>
</div>

<div id="custom-field" class="section">
	<div class="title">Champs personnalisés</div>
	<table class="custom-field-table" cellspacing="0" cellpadding="0">
	<thead>
		<tr>
			<th class="name">Nom du champ</th>
			<th class="label">Libellé</th>
			<th class="type">Type</th>
			<th class="required">Requis</th>
			<th class="value-list">Liste valeurs</th>
			<th class="value-default">par défaut</th>
			<th class="validation-type">Type de validation</th>
			<th class="length">Taille</th>
			<th class="actions"></th>
		</tr>
	</thead>
	<tbody>
	</tbody>
	</table>
	<div id="btn-add-custom-field"></div>
	<a href="#" class="btn-red" id="btn-save">Sauvegarder les changements</a>
	<div class="zero"></div>
</div>

<?php
	// Préparation liste des fonctions
	$n = $pc = 0; $pl = array(); // Post List
	if ($fh = fopen(MISC_INC . "list_post.csv","r")) {
		while (($datacsv = fgetcsv($fh, 128, ";")) !== false) $pl[$n++] = $datacsv;
		$pc = $n - 1; // Post Count -> La 1ère ligne est l'intitulé des colonnes
		fclose($fh);
	}
	
	// Préparation liste des tailles salariales
	$n = $nec = 0; $nel = array(); // Number of Employee List
	if ($fh = fopen(MISC_INC . "list_number-of-employees.csv","r")) {
		while (($datacsv = fgetcsv($fh, 64, ";")) !== false) $nel[$n++] = $datacsv[0];
		$nec = $n - 1; // Number of Employee Count -> La 1ère ligne est l'intitulé des colonnes
		fclose($fh);
	}

	// Préparation liste des secteurs d'activité
        $activity_sectors = Doctrine_Core::getTable('ActivitySector');
        $activity_sectorsList = $activity_sectors->findAll();

	foreach($ic_fields as &$ic_field)
		if (is_string($ic_field))
			$ic_field = rawurlencode($ic_field);
	unset($ic_field);
?>
<div class="invoicing-customization section">
	<script type="text/javascript">var ic_fields = <?php echo json_encode($ic_fields) ?>;</script>
	<div class="title">Personnalisation des critères de facturation</div>
	<input type="checkbox" name="cc_foreign" <?php if($cc_foreign) { ?>checked="checked"<?php } ?>/> <label for="cc_foreign">Comptabiliser les demandes de contacts de sociétés étrangères</label><br/>
	<input type="checkbox" name="cc_intern" <?php if($cc_intern) { ?>checked="checked"<?php } ?>/> <label for="cc_intern">Comptabiliser les demandes de contacts des stagiaires</label><br/>
	<input type="checkbox" name="cc_noPrivate" <?php if($cc_noPrivate) { ?>checked="checked"<?php } ?>/> <label for="cc_noPrivate">Ne pas comptabiliser les demandes de particulier</label><br/>
	<br/>
	<input type="checkbox" name="ic_reject" <?php if($ic_reject) { ?>checked="checked"<?php } ?>/> <label for="ic_active">Autoriser cet annonceur à rejeter de demandes sur son extranet</label><br/>
  Nombre de rejets successifs avant rejection automatique (0 = réglage global) : <input type="text" name="auto_reject_threshold" value="<?php echo $auto_reject_threshold ?>" size="5"/><br/>
	<br/>
        <div class="credit-settings section">
        <div class="title">Remise à zéro des avoirs en cours</div>
	<table cellspacing="0" cellpadding="0" class="is is-lead"><tbody>
		<tr><td><label>Montant des avoirs en cours :</label></td><td><input type="text" readonly="readonly" name="credit_amount" class="RazCredit-amount" value=""/></td></tr>
	</tbody></table>
<!--        <input type="button" value="Remettre à zéro" onclick="HN.TC.BO.RazCredit.Reset();"/>-->
	<br/>
	<b>Historique :</b>
	<div id="PerfReqLabelResetCredit" class="PerfReqLabel"></div>
	<table cellspacing="0" cellpadding="0" class="RazCredit-historic">
		<thead>
		<tr>
			<th class="date">date</th>
			<th class="type">montant</th>
			<th class="undo"></th>
		</tr>
		</thead>
		<tbody>
                  <tr><td></td><td></td><td></td></tr>
                
		</tbody>
	</table>
        </div>
	<input type="checkbox" name="ic_active" <?php if($ic_active) { ?>checked="checked"<?php } ?>/> <label for="ic_active">Activer la personnalisation</label><br/>
	<input type="hidden" name="ic_fields"/>
	<br/>
	<div class="customization-list">
		<input type="checkbox" name="ic_extranet" <?php if($ic_extranet) { ?>checked="checked"<?php } ?>/> <label for="ic_extranet">Permettre à l'annonceur de modifier sa facturation sur l'extranet</label><br/>
		<br/>
		<fieldset>
			<legend>Fonction</legend>
			<?php for ($i = 1; $i <= $pc; $i++) { ?>
				<?php if (empty($pl[$i][0])) { ?>
					<?php if ($pl[$i][1][0] != "-") { ?>
					<u><?php echo $pl[$i][1] ?></u><br/>
					<?php } else { ?>
					<br/>
					<?php } ?>
				<?php } else { ?>
					<input type="checkbox" name="ic_job" value="<?php echo $pl[$i][0] ?>"/> <?php echo $pl[$i][1] ?><br/>
				<?php } ?>
			<?php }?>
		</fieldset>
		<fieldset>
			<legend>Taille salariale</legend>
			<?php for ($i = 1; $i <= $nec; $i++) { ?>
				<input type="checkbox" name="ic_company_size" value="<?php echo $nel[$i] ?>"/> <?php echo $nel[$i] ?><br/>
			<?php } ?>
		</fieldset>
		<fieldset>
			<legend>Secteur d'activité</legend>
                        <?php
                          if(!empty($activity_sectorsList))
                            foreach ($activity_sectorsList as $activity_sector) { ?>
                                <input type="checkbox" name="ic_activity_sector" value="<?php echo $activity_sector['sector'] ?>"/> <?php echo to_entities($activity_sector['sector']) ?><br/>
                          <?php } ?>
		</fieldset>
		<fieldset>
			<legend>Pays</legend>
			<textarea name="ic_country"></textarea>
		</fieldset>
		<fieldset>
			<legend>Code Postal</legend>
			<textarea name="ic_cp"></textarea>
		</fieldset>
	</div>
</div>
<!-- end latest additions -->

<div class="invoicing-settings section">
	<div class="title">Coût des demandes de contact</div>
	<label for="is_type">Type de facturation</label>
	<select name="is_type">
		<option value="lead">au lead</option>
		<option value="budget">au budget</option>
		<option value="forfeit">au forfait</option>
	</select>
	<table cellspacing="0" cellpadding="0" class="is is-lead"><tbody>
		<tr><td><label>Coût par lead :</label></td><td><input type="text" name="lead_unit_cost" value=""/></td></tr>
	</tbody></table>
	<table cellspacing="0" cellpadding="0" class="is is-budget"><tbody>
		<tr><td><label>Coût par lead :</label></td><td><input type="text" name="budget_unit_cost" value=""/></td></tr>
		<tr><td><label>Nombre maximum de lead facturable :</label></td><td><input type="text" name="budget_max_leads" value=""/></td></tr>
		<tr><td><label>Périodicité du capping :</label></td><td>
			<select name="budget_capping_periodicity">
				<option value="month">mois</option>
				<option value="year">année</option>
			</select>
		</td></tr>
	</tbody></table>
	<table cellspacing="0" cellpadding="0" class="is is-forfeit"><tbody>
		<tr><td><label>Montant du forfait :</label></td><td><input type="text" name="forfeit_amount" value=""/></td></tr>
		<tr><td><label>Périodicité du forfait :</label></td><td>
			<select name="forfeit_periodicity">
				<option value="month">mois</option>
				<option value="year">année</option>
			</select>
		</td></tr>
	</tbody></table>
	<br/>
	<input type="button" value="Modifier" onclick="HN.TC.BO.Adv.AddInvoicingSetting();"/>
	<br/>
	<b>Historique :</b>
	<div id="PerfReqLabelInvoicingSettings" class="PerfReqLabel"></div>
	<table cellspacing="0" cellpadding="0" class="is-historic">
		<thead>
		<tr>
			<th class="date">date</th>
			<th class="type">type</th>
			<th class="detail">détail</th>
			<th class="undo"></th>
		</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
        <?php if($adv['category'] == __ADV_CAT_ADVERTISER__): ?>
        <?php /*
		<input type="checkbox" <?php echo $direct_debit ? 'checked="checked" ' : ''?>name="direct_debit" id="direct_debit" /> <label for="direct_debit"> Prélèvement</label>
		*/?>
		<br />
		<?php  
			$sql_direct_debit  = "SELECT direct_debit FROM `advertisers` WHERE id='".$_GET['id']."' ";
			$req_direct_debit  =  mysql_query($sql_direct_debit);
			$data_direct_debit =  mysql_fetch_object($req_direct_debit);
		 
		 ?>
		<label for="direct_debit"> Mode de paiment  : </label>
		 <select id="item-cart-payment_mean" name="payment_mean" class="c_i" data-cart-info="payment_mean" >
		 
		   
		   <?php foreach (Order::$paymentMeanList as $k => $v) : 
				if(empty($data_direct_debit->direct_debit)) $selected_mode = 60;
				else $selected_mode = $data_direct_debit->direct_debit;
		   ?>
		   
		   
			<option value="<?php echo $k ?>"<?php echo $selected_mode ==$k ? " selected=\"selected\"": "" ?>><?php echo $v ?></option>
		   <?php endforeach ?>
		  </select>
        <?php endif; ?>
</div>
<?php } ?>

<div class="section">
	<div class="title">Identification Extranet</div>
	<table>
		<tr>
			<td class="intitule">Login extranet : </td>
			<td class="intitule"><input type="text" name="login" value="<?php echo to_entities($login) ?>" maxlength="255" onBlur="this.value = trim(this.value)"> *</td>
		</tr><tr>
			<td class="intitule">Nouveau mot de passe extranet : </td>
			<td class="intitule"><input type="password" name="pass" maxlength="255"> Laissez ces champs vides si vous ne souhaitez pas</td>
		</tr><tr>
			<td class="intitule">Confirmation du nouveau mot de passe : </td>
			<td class="intitule"><input type="password" name="cpass" maxlength="255"> modifier le mot de passe</td>
		</tr>
	</table>
	<?php if ($initpass) { ?>
		Mot de passe initial à communiquer à l'annonceur ou au fournisseur : <b><?php echo $initpass ?></b><br/>
	<?php } ?>
  <input id="send-bop-codes-btn" type="button" value="Envoyer les codes d’accès à l’annonceur"/>
  <div id="send-bop-codes-dialog" title="Email codes partenaire">
    <span class="tips"></span>
  </div>
</div>
<br />
<br />
<?php 
if (!$userPerms->has("m-mark--sm-fiche-active","e")){
	$display = " style='display:none;' ";
}
?>
<div class="commentaire">Note : * signifie que le champ est obligatoire.</div>
<?php if($user->rank != CONTRIB) { ?>
<br/>
<br/>
<div>
<input type="checkbox" name="active"<?php if($active) { ?> checked="checked"<?php } ?>> Cochez la case ci-contre pour activer l'annonceur ou le fournisseur.
</div>
<?php } ?>

<br />
<center><input type="button" class="bouton" value="Valider" name="ok"> &nbsp; <input type="reset" value="Annuler" class="bouton" name="nok"></center>

</form>
<?php
	} // fin affichage form
?>
</div>
<br />
<br />
<div id="adviser_products_listing" class="titreStandard">Liste des produits de l'annonceur ou du fournisseur <?php print(to_entities($data[2])) ?></div>
<br />

<?php
	if($userPerms->has("m-prod--sm-partners", "x")){
	//if (!$userPerms->has("m-prod--sm-partners","x")){
		echo('<div id="adv_export_div_btn">');
			echo('<a href="/fr/manager/export-advertisers/export_products.php?id='.$_GET['id'].'" target="_blank" class="bouton">Exporter les produits</a>');
		echo('</div>');
		echo('<br />');
		echo('<br />');
	}
?>

<div class="bg">
<?php
require(ADMIN."products.php");

$db = DBHandle::get_instance();

define("MAX_RESULTS", 1000);

// Filter vars
$page       = isset($_GET["page"])       ? (int)(trim($_GET["page"])) : 1;
$lastpage   = isset($_GET["lastpage"])   ? (int)(trim($_GET["lastpage"])) : 1;
$sort       = isset($_GET["sort"])       ? trim($_GET["sort"]) : "";
$lastsort   = isset($_GET["lastsort"])   ? trim($_GET["lastsort"]) : "";
$sortway    = isset($_GET["sortway"])    ? trim($_GET["sortway"]) : "";
$deleteList = isset($_GET["deleteList"]) ? explode("|",trim($_GET["deleteList"])) : array();
$activeList = isset($_GET["activeList"]) ? explode("|",trim($_GET["activeList"])) : array();
$selectedList = isset($_GET["selectList"]) ? explode("|",trim($_GET["selectList"])) : array();
$selectedFamily = isset($_GET["selectedFamily"]) ? $_GET["selectedFamily"] : '';

// Processing products deletion
if ($userPerms->has("m-prod--sm-partners","d")) {
  if(!empty($_GET["deleteList"]))
    foreach($deleteList as $pdtID) {
      $pdtID = (int)$pdtID;
      delProduct($handle, $pdtID, $pdtID, $user->id);
    }
}

if ($userPerms->has("m-prod--sm-partners","e")) {
  // Processing products activation
  if(!empty($_GET["activeList"]))
    foreach($activeList as $activeElem) {
      list($pdtID, $active) = explode(",",$activeElem);
      $pdtID = (int)$pdtID;
      $active = ((int)$active) > 0 ? 1 : 0;
      $db->query("UPDATE products_fr SET active = ".$active." WHERE id = ".$pdtID, __FILE__, __LINE__);
    }
}

if ($userPerms->has("m-prod--sm-products","e")) {
  // Processing products family change
   if(!empty($_GET["selectList"]) && !empty($selectedFamily))
    foreach($selectedList as $selectedElem) {
      list($pdtID) = explode(",",$selectedElem);
      $pdtID = (int)$pdtID;
      $selectedFamily = ((int)$selectedFamily) > 0 ? (int)$selectedFamily : 0;
      $res = $db->query("select count(idProduct) FROM products_families WHERE idProduct = ".$pdtID, __FILE__, __LINE__);
      $nbFamiliesPerProduct = $db->fetch($res);
      if($nbFamiliesPerProduct[0] > 1){
        $db->query("delete FROM products_families  WHERE idProduct = ".$pdtID, __FILE__, __LINE__);
        $db->query("INSERT INTO products_families (idProduct, idFamily) VALUES ( ".$pdtID.", ".$selectedFamily.")", __FILE__, __LINE__);
      }elseif($nbFamiliesPerProduct[0] == 1)
        $db->query("UPDATE products_families SET idFamily = ".$selectedFamily." WHERE idProduct = ".$pdtID, __FILE__, __LINE__);
      else
        $db->query("INSERT INTO products_families (idProduct, idFamily) VALUES ( ".$pdtID.", ".$selectedFamily.")", __FILE__, __LINE__);
    }
}

if ($page < 1) $page = 1;
if ($lastpage < 1) $lastpage = 1;

define("NB", 20);
if (($page-1) * NB >= $pdtList["count"]) $page = ($pdtList["count"] - $pdtList["count"]%NB) / NB + 1;
if (($lastpage-1) * NB >= $pdtList["count"]) $lastpage = ($pdtList["count"] - $pdtList["count"]%NB) / NB + 1;

if ($sort == $lastsort && $sort != '') {
	if ($lastpage == $page) $sortway = ($sortway == 'asc' ? 'desc' : 'asc');
	else $sortway = ($sortway == 'asc' ? 'asc' : 'desc');
}
else $sortway = 'asc';

$sortway_const = $sortway == "asc" ? SORT_ASC : SORT_DESC;
$sortwayi_const = $sortway_const == SORT_ASC ? SORT_DESC : SORT_ASC;

// Product list var
$pdtList = array("data_row" => array(), "data_col" => array(), "count" => 0, "start_time" => microtime(true), "end_time" => 0);
$pdtList["start_time"] = microtime(true);
$res = $db->query("
	SELECT
		p.id, p.price as pdt_price, p.timestamp,
		pfr.name, pfr.ref_name, pfr.fastdesc, pfr.active, pfr.locked,
		pf.idFamily as catID,
		ps.hits, ps.orders, ps.leads,
		rc.id as ref_idtc, rc.refSupplier as ref_refSupplier, rc.price as ref_price
	FROM products p
	INNER JOIN products_stats ps ON p.id = ps.id
	INNER JOIN products_fr pfr ON p.id = pfr.id
	INNER JOIN products_families pf ON p.id = pf.idProduct
	LEFT JOIN
		references_content rc ON p.id = rc.idProduct AND rc.classement = 1
	WHERE p.idAdvertiser = ".$_GET["id"]."
          AND pfr.deleted != 1
	GROUP BY p.id
	ORDER BY p.timestamp DESC limit 0,1000");
$pdtList["end_time"] = microtime(true);

$hits2leads_w = 0.2;	// weight of products leads
$hits2orders_w = 0.8;	// weight of products orders

while ($pdt = $db->fetchAssoc($res)) {
	$pdt["hits2leads"] = $pdt["hits"] > 0 ? $pdt["leads"] / $pdt["hits"] : 0;
	$pdt["hits2orders"] = $pdt["hits"] > 0 ? $pdt["orders"] / $pdt["hits"] : 0;
	$pdt["transfo"] = round(($pdt["hits2leads"] + $pdt["hits2orders"])*100, 2);
	
	if ($pdt["pdt_price"] == "ref")
		$pdt["price"] = $pdt["ref_price"];
	else
		$pdt["price"] = $pdt["pdt_price"];

	if (!preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', $pdt["price"]))
		$pdt["price"] = -1;

	$pdtList["data_row"][] = $pdt;
	foreach ($pdt as $k => $v)
		$pdtList["data_col"][$k][] = $v;
	
	$pdtList["count"]++;
	if ($pdtList["count"] >= MAX_RESULTS) break;
}

if ($pdtList["count"] > 0) {
	
	switch ($sort) {
		case "name"      : array_multisort($pdtList["data_col"]["name"], $sortway_const, $pdtList["data_col"]["timestamp"], SORT_DESC, $pdtList["data_row"]); break;
		case "catID"      : array_multisort($pdtList["data_col"]["catID"], $sortway_const, $pdtList["data_col"]["timestamp"], SORT_DESC, $pdtList["data_row"]); break;
		case "fastdesc"  : array_multisort($pdtList["data_col"]["fastdesc"], $sortway_const, $pdtList["data_col"]["timestamp"], SORT_DESC, $pdtList["data_row"]); break;
		case "price"     : array_multisort($pdtList["data_col"]["price"], $sortway_const, $pdtList["data_col"]["timestamp"], SORT_DESC, $pdtList["data_row"]); break;
		case "timestamp" : array_multisort($pdtList["data_col"]["timestamp"], $sortwayi_const, SORT_NUMERIC, $pdtList["data_col"]["name"], SORT_ASC, $pdtList["data_row"]); break;
		case "hits" : array_multisort($pdtList["data_col"]["hits"], $sortwayi_const, SORT_NUMERIC, $pdtList["data_col"]["name"], SORT_ASC, $pdtList["data_row"]); break;
		case "leads" : array_multisort($pdtList["data_col"]["leads"], $sortwayi_const, SORT_NUMERIC, $pdtList["data_col"]["name"], SORT_ASC, $pdtList["data_row"]); break;
		case "orders" : array_multisort($pdtList["data_col"]["orders"], $sortwayi_const, SORT_NUMERIC, $pdtList["data_col"]["name"], SORT_ASC, $pdtList["data_row"]); break;
		case "transfo" : array_multisort($pdtList["data_col"]["transfo"], $sortwayi_const, SORT_NUMERIC, $pdtList["data_col"]["name"], SORT_ASC, $pdtList["data_row"]); break;
		default : array_multisort($pdtList["data_col"]["timestamp"], $sortwayi_const, SORT_NUMERIC, $pdtList["data_col"]["name"], SORT_ASC, $pdtList["data_row"]); break;
	}
	$lastsort = $sort;
	$lastpage = $page;
?>
<script type="text/javascript">
$(function(){
	$pdtList = $("form[name='pdtList']");
	$("#delete-pdtList").click(function(){
		var deleteList = [];
		$("table.php-list input[name='delete']:checked").each(function(){
			deleteList.push($(this).parent().parent().find("input[name='pdtID']").val());
		});
		$pdtList.find("input[name='deleteList']").val(deleteList.join("|"));
		$pdtList.submit();
	});
	$("#active-pdtList").click(function(){
		var activeList = [];
		$("table.php-list input[name='active']").each(function(){
			var active = $(this).attr("checked")?1:0;
			var $tr = $(this).parent().parent()
			var pdtID = parseInt($tr.find("input[name='pdtID']").val());
			var activated = parseInt($tr.find("input[name='activated']").val())?1:0;
			if (active ^ activated) // XOR bitwise - > 1 if state changed, 0 if not
				activeList.push(pdtID+","+active);
		});
		$pdtList.find("input[name='activeList']").val(activeList.join("|"));
		$pdtList.submit();
	});
        $("#families-pdtList").click(function(){

            PMP.fsw.Show();
            PMP.fb.Build();
            PMP.fb.mod='add';
            $("#FamilySelectionWindow").css('top', ($("#families-pdtList").offset().top - 600));
            $("#FamilySelectionWindowShad").css('top', ($("#families-pdtList").offset().top - 594));
	});
        
        $('#toggleSelectFamilies').click(function(){
          var text = $(this).text()
           $(this).text(text == 'Aucun' ? 'Tout': 'Aucun');
           if(text == 'Aucun'){
              $('input[name=select]:checked').removeAttr('checked');
           }else{
              $('input[name=select]').attr('checked', 'checked');
           }
        });
        $('#FamilyChangeWrap').draggable({ handle: '.window_title_bar',  containment: '#page-content'});

});
function hideFamilyChangeWindow(){
  $("#FamilyChangeWrap").hide();
  $("#FamilyChangeConfirm").hide();
  $("#FamilyChangeConfirmShad").hide();
}

</script>
	<div id="search-results">
		
		<form name="pdtList" method="get" action="edit.php#adviser_products_listing">
			<div>
				<input type="hidden" name="id" value="<?php echo $_GET["id"] ?>" />
				<input type="hidden" name="page" value="<?php echo $page?>; " />
				<input type="hidden" name="lastpage" value="<?php echo $lastpage?>; " />
				<input type="hidden" name="sort" value="<?php echo $sort?>; " />
				<input type="hidden" name="lastsort" value="<?php echo $lastsort?>; " />
				<input type="hidden" name="sortway" value="<?php echo $sortway?>; " />
				<input type="hidden" name="deleteList" value="" />
				<input type="hidden" name="activeList" value="" />
                                <input type="hidden" name="selectList" value="" />
                                <input type="hidden" id="selectedFamily" name="selectedFamily" value="" />
			</div>
		</form>
		<table class="php-list" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th style="width: 8%">Image</th>
					<th style="width: 18%"><a href="javascript: document.pdtList.sort.value = 'name'; document.pdtList.submit();">Nom</a></th>
					<th style="width: 18%"><a href="javascript: document.pdtList.sort.value = 'catID'; document.pdtList.submit();">Famille</a></th>
					<th style="width: 15%"><a href="javascript: document.pdtList.sort.value = 'fastdesc'; document.pdtList.submit();">Description rapide</a></th>
					<th style="width: 10%">Réf. Four. 1</th>
					<th style="width: 7%"><a href="javascript: document.pdtList.sort.value = 'price'; document.pdtList.submit();">Prix</a></th>
					<th style="width: 10%"><a href="javascript: document.pdtList.sort.value = 'timestamp'; document.pdtList.submit();">Date dernière mise à jour/création</a></th>
					<th style="width: 5%"><a href="javascript: document.pdtList.sort.value = 'hits'; document.pdtList.submit();">Vues 60 derniers jours</a></th>
					<th style="width: 5%"><a href="javascript: document.pdtList.sort.value = 'leads'; document.pdtList.submit();">Nombre de lead</a></th>
					<th style="width: 5%"><a href="javascript: document.pdtList.sort.value = 'orders'; document.pdtList.submit();">Nombre de commandes</a></th>
					<th style="width: 5%"><a href="javascript: document.pdtList.sort.value = 'transfo'; document.pdtList.submit();">Taux de transfo</a></th>
					<?php if ($userPerms->has("m-mark--sm-fiche-active","e")) {  ?>
					<th style="width: 3%">Actif</th>
					<?php } ?>
					<?php if ($userPerms->has("m-mark--sm-fiche-delete","e")) {  ?>
					<th style="width: 3%">Supprimer</th>
					<?php } ?>
					<th style="width: 3%">Cht.&nbsp;fam<div id="toggleSelectFamilies">Tout</div></th>
					<th style="width: 3%"></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($pdtList["data_row"] as $pdt) {
			

	
					 		$sql_hits2 = "SELECT COUNT(sh.idProduct) as total 
							FROM stats_hit as sh WHERE
							FROM_UNIXTIME(timestamp) BETWEEN DATE_SUB(CURDATE(), INTERVAL 60 DAY) AND CURDATE()
							AND sh.idProduct = '".$pdt["id"]."'
							AND adresse_ip !='46.218.144.64'
							AND adresse_ip !='124.244.241.251'
							AND adresse_ip !='41.141.250.175'
							GROUP BY sh.idProduct";
							$req_hits = mysql_query($sql_hits2);
							$data_hits= mysql_fetch_assoc($req_hits);
							//echo 'sql : '.$sql_hits2.'<br>';
							
							$fo_pdt_url = URL."produits/".$pdt["catID"]."-".$pdt["id"]."-".$pdt["ref_name"].".html";
							$fo_pdt_pic_url = is_file(PRODUCTS_IMAGE_INC."thumb_small/".$pdt["id"]."-1.jpg") ? PRODUCTS_IMAGE_SECURE_URL."thumb_small/".$pdt["id"]."-1.jpg" : PRODUCTS_IMAGE_SECURE_URL."no-pic-thumb_small.gif";
							$bo_pdt_url = ADMIN_URL."products/edit.php?id=".$pdt["id"] ?>
							
				<?php

				if(strcmp($pdt['locked'],'1')=='0'){
					echo('<tr class="adv_prod_blocked_one">');
				}else{
					echo('<tr>');
				}
				
				?>
				<!-- <tr> -->
					<td><a href="<?php echo $bo_pdt_url ?>" title="Voir la fiche produit"><img src="<?php echo $fo_pdt_pic_url ?>" alt=""></a></td>
					<td class="title"><input type="hidden" name="pdtID" value="<?php echo $pdt["id"] ?>"/><a href="<?php echo $bo_pdt_url ?>"><?php echo $pdt["name"] ?></a></td>
					<td class="title_3rd_familly">
						<?php
						
							//ID Product is $pdt["id"]
							//ID 		=> recherche sur la table "families_fr" Pour avoir le 3eme niveau 
							//ID Parent => recherche sur la table "families_fr" pour avoir le "2eme" niveau
							//ID Parent => recherche à partir de "ID Parent" du "2eme" niveau sur la table "families" pour avoir le "1er" niveau

							//Get get the id of families
							
							$id_product_families_array = array();
							$res_id_product_families 	= $db->query("	SELECT 
																			idFamily
																		FROM  `products_families` 
																		WHERE  `idProduct` =".$pdt["id"]."
																	");

																	
							while($id_product_families_array = $db->fetchAssoc($res_id_product_families)){
							//if(!empty($id_product_families_array['idFamily'])){
							
								//To get the id of 
								$id_third_product_families_array = array();
								
								$res_id_third_product_families 	= $db->query("	SELECT 
																					id, idParent,
																					pdt_overwrite, rank
																				FROM  `families` 
																				WHERE  `id` =".$id_product_families_array['idFamily']."
																				ORDER BY RANK ASC
																			");


								while($id_third_product_families_array = $db->fetchAssoc($res_id_third_product_families)){						

									$name_third_product_families_array 	= array();
									$res_name_third_product_families 	= $db->query("	SELECT 
																							name
																						FROM  `families_fr` 
																						WHERE  `id` =".$id_third_product_families_array['id']."
																					");

									$name_third_product_families_array 	= $db->fetchAssoc($res_name_third_product_families);
									
									$bo_famille_search_url_before	= str_replace(' ','+',$name_third_product_families_array['name']);
									$bo_famille_search_url	= ADMIN_URL."search.php?search_type=2&search=".$bo_famille_search_url_before;
									echo('<a href="'.$bo_famille_search_url.'" target="_blank">'.$name_third_product_families_array['name'].'</a><br />');
								}
		
							}
							
						?>
		
						
					</td>
					<td><?php echo $pdt["fastdesc"]?></td>
					<td>
						<?php if (!empty($pdt["ref_idtc"])) { ?>
							<?php echo $pdt["ref_refSupplier"] ?>
						<?php } else { ?>
							N.A.
						<?php } ?>
					</td>
					<td>
						<?php if ($pdt["price"] == -1) { ?>
							sur devis
						<?php } else { ?>
							<?php echo sprintf("%.2f",$pdt["price"])."€ HT" ?>
						<?php } ?>
					</td>
					<td><?php echo date("Y/m/d h:i:s",$pdt["timestamp"]) ?></td>
					<?php
					if(empty($data_hits["total"])){
						echo '<td>0</td>';
					}else {
						echo '<td>'.$data_hits["total"].'</td>';
					}
					?>
					<td><?php echo $pdt["leads"]?></td>
					<td><?php echo $pdt["orders"]?></td>
					<td><?php echo $pdt["transfo"]?>%</td>
					<?php if ($userPerms->has("m-mark--sm-fiche-active","e")) {  ?>
					<td><input type="hidden" name="activated" value="<?php echo $pdt["active"] ?>"/><input type="checkbox" name="active"<?php if($pdt["active"]) { ?> checked="checked"<?php } ?>/></td>
					<?php } ?>
					<?php if ($userPerms->has("m-mark--sm-fiche-delete","e")) {  ?>
					<td><input name="delete" type="checkbox" value=""/></td>
					<?php } ?>
                                        <td><input type="checkbox" name="select" /></td>
					<td><a href="<?php echo $fo_pdt_url ?>" target="_blank"><img src="<?php echo ADMIN_URL ?>ressources/icons/monitor_go.png" alt="" title="Voir la fiche en ligne"></a></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<?php if ($userPerms->has("m-mark--sm-fiche-delete","e")) {  ?>
		<input id="delete-pdtList" type="button" value="Supprimer les produits sélectionnés" />
		 <?php } ?>
		<?php if ($userPerms->has("m-mark--sm-fiche-active","e")) {  ?>
		<input id="active-pdtList" type="button" value="Activer/Désactiver les produits" />
        <?php } ?>
		<input id="families-pdtList" type="button" value="Transfert vers famille" />
	</div>
	
<?php }else{ ?>
	<div class="confirm">Aucun produit.</div>
<?php } ?>
</div>
<br/>

<!-- Start Elements to make the navigation easier ! -->
<div id="advertiser_navigation_absolute_bottom_button_container">
	<div id="products_button">
		<a href="#adviser_products_listing">
			Voir produits
		</a>
	</div>
	
	<div id="parametrage_button">
		<a href="#adviser_edit_fields">
			Voir parametrages
		</a>
	</div>

</div>
<!-- End Elements to make the navigation easier ! -->
<?php
} // fin id ok
require(ADMIN . 'tail.php');
?>