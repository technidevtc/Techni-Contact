<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Mises à jour :

	7 juin 2005 : + accès commerciaux à tous les éléments
	28 mars 2006 : + options fournisseurs

 Fichier : /secure/manager/advertisers/index.php
 Description : Accueil gestion des annonceurs

/=================================================================*/

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

require(ADMIN . 'advertisers.php');
require(ADMIN . 'users.php');
require(ADMIN . 'tva.php');

$title = $navBar = 'Base de données des annonceurs';
require(ADMIN . 'head.php');

///////////////////////////////////////////////////////////////////////////

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
      $errorstring .= '- Vous n\'avez pas saisi le nom de l\'annonceur<br/>';
    }

    if(!isAUnique($handle, 'nom1', $nom1)) {
      $error = true;
      $errorstring .= '- Un annonceur porte déjà ce nom<br/>';
    }

    $nom2 = isset($_POST['nom2']) ? substr(trim($_POST['nom2']), 0, 255) : '';

    if ($user->rank == HOOK_NETWORK || $user->rank == COMMADMIN || $user->rank == COMM) {
      $category = isset($_POST['category']) ? $_POST['category'] : __ADV_CAT_ADVERTISER__;
      if (!isset($adv_cat_list[$category])) $category = __ADV_CAT_ADVERTISER__;
    }
    else {
      $category = __ADV_CAT_ADVERTISER__;
    }

    $adresse1 = isset($_POST['adresse1']) ? substr(trim($_POST['adresse1']), 0, 255) : '';

    if($adresse1 == '') {
      $error = true;
      $errorstring .= '- Vous n\'avez pas saisi l\'adresse<br/>';
    }

    $adresse2 = isset($_POST['adresse2']) ? substr(trim($_POST['adresse2']), 0, 255) : '';

    $ville = isset($_POST['ville']) ? substr(trim($_POST['ville']), 0, 255) : '';

    if($ville == '') {
      $error = true;
      $errorstring .= '- Vous n\'avez pas saisi le nom de la ville<br/>';
    }

    $cp = isset($_POST['cp']) ? substr(trim($_POST['cp']), 0, 255) : '';

    if($cp == '') {
      $error = true;
      $errorstring .= '- Vous n\'avez pas saisi le code postal<br/>';
    }

    $pays = isset($_POST['pays']) ? substr(trim($_POST['pays']), 0, 255) : '';

    if($pays == '') {
      $error = true;
      $errorstring .= '- Vous n\'avez pas saisi le nom du pays<br/>';
    }

    $warranty = isset($_POST['warranty']) ? substr(trim($_POST['warranty']), 0, 255) : '';
    $catalog_code = isset($_POST['catalog_code']) ? trim($_POST['catalog_code']) : "";

    /* help message */
    $help_show = isset($_POST['help_show']) ? ($_POST['help_show'] != '' ? '1' : '0') : '0';
    $help_msg = isset($_POST['help_msg']) ? trim($_POST['help_msg']) : '';

    $contact = isset($_POST['contact']) ? substr(trim($_POST['contact']), 0, 255) : '';

    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
/*	
    if($email != '' && !preg_match("`^'[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$`", $email)) {
      $error = true;
      $errorstring .= '- Adresse email invalide<br/>';
    }
*/	
	if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	  $error = true;
      $errorstring .= '- Adresse email invalide<br/>';
	}
		

    /*if($email != '' && !isAUnique($handle, 'email', $email))
    {
    $error = true;
    $errorstring .= '- Cette adresse email est déjà utilisée<br/>';
    }*/

    $url = isset($_POST['url']) ? trim($_POST['url']) : '';
    if($url == 'http://') $url = '';
      
    if($url != '' && strpos($url, '/', 8) === false) {
      $url .= '/';
    }

    if($url != '' && !preg_match('/^(http|https|ftp):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\//i', $url)) {
      $error = true;
      $errorstring .= '- Adresse du site web invalide<br/>';
    }


    $tel1 = isset($_POST['tel1']) ? trim($_POST['tel1']) : '';

    if($tel1 == '') {
      $error = true;
      $errorstring .= '- Vous n\'avez pas saisi le numéro de téléphone <br/>';
    }

    $tel2 = isset($_POST['tel2']) ? trim($_POST['tel2']) : '';

    $fax1 = isset($_POST['fax1']) ? trim($_POST['fax1']) : '';

    if($fax1 == '') {
      $error = true;
      $errorstring .= '- Vous n\'avez pas saisi le numéro de fax <br/>';
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
    
    $client_id = isset($_POST['client_id']) ? trim($_POST['client_id']) : '';
    
    $from_web = isset($_POST['from_web']) ? ($_POST['from_web'] != '' ? '1' : '0') : '0';
    $cc_foreign = isset($_POST['cc_foreign']) ? ($_POST['cc_foreign'] != '' ? '1' : '0') : '0';
    $cc_intern = isset($_POST['cc_intern']) ? ($_POST['cc_intern'] != '' ? '1' : '0') : '0';
    $cc_noPrivate = isset($_POST['cc_noPrivate']) ? ($_POST['cc_noPrivate'] != '' ? '1' : '0') : '0';
    $show_infos_online = isset($_POST['show_infos_online']) ? ($_POST['show_infos_online'] != '' ? '1' : '0') : '0';
    
  // Options fournisseurs
    if ($category == __ADV_CAT_SUPPLIER__) {
      $delai_livraison 	   = isset($_POST['delai_livraison']) ? substr(trim($_POST['delai_livraison']), 0, 255) : '';
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
        $errorstring .= '- Vous n\'avez pas saisi de délai de livraison <br/>';
      }
	  
	  if(!empty($delai_expedition)){
		  if(!is_numeric($delai_expedition)){
			$error = true;
			$errorstring .= '- Délai d\'expédition : Ce champs ne peux contenir que des valeurs numériques <br/>';		  
		  }
	  }
	  
      /*if ($shipping_fee == '') {
        $error = true;
        $errorstring .= '- Vous n\'avez pas saisi de frais de port <br/>';
      }*/
      
      if($prixPublic != '1')
      {
        $prixPublic = '0';
      }

      if ($margeRemise == '')
      {
        $error = true;
        $errorstring .= '- Vous n\'avez pas saisi le taux de ' . ($prixPublic == '1' ? 'remise' : 'marge') . ' <br/>';
      }
      elseif(!preg_match('/^[0-9]*((\.|\,)[0-9]{0,5})?$/',$margeRemise))
      {
        $error = true;
        $errorstring .= '- Le taux de '. ($prixPublic == '1' ? 'remise' : 'marge') . ' saisi est invalide <br/>';
      }

      $idTVAexist = false;
      foreach ($listeTVAs as $v)
      {
        if ($idTVA == $v[0])
        {
          $idTVAexist = true;
          break;
        }
      }
      if (!$idTVAexist)
      {
        $error = true;
        $errorstring .= '- Le taux de TVA choisi n\'existe pas<br/>';
      }

      if (!preg_match('/^[0-9]*((\.|\,)[0-9]{0,2})?$/',$contraintePrix))
      {
        $error = true;
        $errorstring .= '- La contrainte de prix saisie est invalide <br/>';
      }
    }
    else
    {
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
    $ic_fields = isset($_POST["ic_fields"]) ? json_decode(mb_convert_encoding($_POST["ic_fields"],"UTF-8","ISO-8859-1")) : array();
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
          $listeShown = 'Actuellement aucun annonceur n\\\'est lié à celui en cours de création.';
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
                  $errorstring .= '- Date de début de facturation invalide<br/>';

              }
              

              $coutcontact = isset($_POST['coutcontact']) ? str_replace(',', '.', $_POST['coutcontact']) : 0.0;
              if(!preg_match('/^[0-9]+(\.[0-9]+){0,1}$/', $coutcontact))
              {
                  $error = true;
                  $errorstring .= '- Le format du coût du contact est invalide<br/>';

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
                  $errorstring .= '- Date de fin d\'abonnement invalide<br/>';

              }

          }
      }
      else
      {
          $ctype       = 0;
          $datef       = $datea = '0000-00-00';
          $coutcontact = 0;
      }


      if(!$error)
      {
          $ok = addAdvertiser($handle, $commercial, $nom1, $nom2, $adresse1, $adresse2, $ville, $cp, $pays, $delai_livraison,$delai_expedition, $shipping_fee, $warranty, $catalog_code, $prixPublic, $margeRemise, $peuChangerTaux, $arrondi, $idTVA, $contraintePrix, $contact, $email, $url, $tel1, $tel2, $fax1, $fax2, $client_id, $prenomcontact, $nomcontact, $emailcontact, $critere, $ctype, $datef, $coutcontact, $datea, $listeHidden, $user->login, $category, $contacts, $from_web, $cc_foreign, $cc_intern, $show_infos_online, $help_show, $help_msg, implode(",", $notRequiredFields), $cc_noPrivate, $ic_reject, $ic_active, serialize($ic_fields), $ic_extranet, $noLeads2in, $noLeads2out, $auto_reject_threshold, $asEstimate);
		  
		  
		  $sql_id  = "SELECT id FROM `advertisers` WHERE email='".$email."' ";
		  $req_id  =  mysql_query($sql_id);
		  $data_id =  mysql_fetch_object($req_id);
		  
		  $sql_update = "UPDATE `advertisers` SET `contacts_not_read_notification` = '".$noAlert2out."' 
					     WHERE `id` ='".$data_id->id."' ";
		  mysql_query($sql_update);
		  
    }

  }
}
else
{
	$ctype = $coutcontact = $commercial = 0;
	$critere = 1;
	$listeHidden = $nom1 = $nom2 = $adresse1 = $adresse2 = $ville = $cp = $pays = $delai_livraison = $shipping_fee = $warranty = $catalog_code = $prixPublic = $margeRemise = $peuChangerTaux = $arrondi = $contraintePrix = $contact = $email = $tel1 = $tel2 = $fax1 = $fax2 = $client_id = $nomcontact = $prenomcontact = $emailcontact = '';
	$cc_foreign = $cc_intern = 1;
	$cc_noPrivate = 0;
	$notRequiredFields = array("complement", "code_naf", "num_siret");
	$ic_reject = 0;
	$ic_active = 1;
  $auto_reject_threshold = 0;
	$ic_fields = array("ic_job" => array(
		"Service achats / services généraux - Stagiaire",
		"Service administratif et financier - Stagiaire",
		"Service commercial - Stagiaire",
		"Service communication / marketing - Stagiaire",
		"Service informatique - Stagiaire",
		"Service logistique / production - Stagiaire",
		"Service maintenance / sécurité - Stagiaire",
		"Service ressources humaines - Stagiaire",
		"Autres services - Stagiaire"));
	mb_convert_variables("UTF-8","ASCII,UTF-8,ISO-8859-1",$ic_fields);
	$ic_extranet = 0;
	$noLeads2in = 0;
	$noLeads2out = 0;
	$help_show = 1;
	$help_msg = "Besoin d’aide ? Contactez le 00 00 00 00 00";
	$idTVA = $idTVAdft;
	$listeShown = 'Actuellement aucun annonceur n\\\'est lié à celui en cours de création.';
	$url = 'http://';
	$category = __ADV_CAT_ADVERTISER__;
	$contacts = array();
	for ($i = 1; $i < 10; $i++) $contacts[$i] = array('prenom' => '', 'nom' => '', 'email' => '', 'critere' => 1);
}

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

<br/><br/>Afficher les <select onChange="goTo('index.php?nb=' + this.options[this.selectedIndex].value + '&<?php print(session_name() . '=' . session_id() . '&filter=' . $filter) ?>')">
<?php

foreach($liste as $k => $v)
{

    $sel = ($nb == $v) ? ' selected' : '';
    print('<option value="' . $v . '"' . $sel . '>' . $v . '</option>');

}

?></select> derniers annonceurs ou fournisseurs ajoutés ou mis à jour. <br/><br/><?php

if($type == 0)
{
    print('<b>Liste des annonceurs dont le nom commence par ');
    if($lettre == '0')
    {
        $pattern = 'REGEXP(\'^[0-9]\')';
        print('un chiffre :</b><br/><br/>');
    }
    else
    {
        $pattern = 'like \'' . $lettre . '%\'';
        print('la lettre ' . strtoupper($lettre) . ' :</b><br/><br/>');
    }

    if($user->rank == COMM && $filter == 1)
    {
        $a = & displayAdvertisers($handle, 'and a.nom1 ' . $pattern . ' AND a.deleted != 1 order by a.nom1', $user->id);
    }
    else
    {
        $a = & displayAdvertisers($handle, 'where a.nom1 ' . $pattern . ' AND a.deleted != 1 order by a.nom1');
    }

}
else
{
    print('<b>Liste des '.$nb.' derniers annonceurs ou fournisseurs ajoutés ou mis à jour : </b><br/><br/>');
   
    if($user->rank == COMM && $filter == 1)
    {
        $a = & displayAdvertisers($handle, '  WHERE a.deleted != 1 order by a.timestamp desc limit ' . $nb, $user->id);
    }
    else
    {
        $a = & displayAdvertisers($handle, '  WHERE a.deleted != 1 order by a.timestamp desc limit ' . $nb);
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


?></div>
<br/><br/><div class="titreStandard">Ajouter un nouvel annonceur ou fournisseur</div><br/>
<div class="bg"><?php

$next = true;

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if(!$error)
    {
        if($ok)
        {
            $out = 'Annonceur créé avec succès.';
        }
        else
        {
            $out = 'Erreur lors de la création de l\'annonceur.<br/>'.$errorstring;
        }
  
        print('<div class="confirm">' . $out . '</div><br/><br/>');
        
        $next = false;
        
    }
    else
    {
        print('<font color="red">Une ou plusieurs erreurs sont survenues lors de la validation : <br/>' . $errorstring  . '</font><br/><br/>');
        $next = true;
    }

}

if($next)
{


?><script language="JavaScript">
<!--

function namesearch()
{
    var handle = document.addAdvertiser;
    if(handle.nom1.value.length < 3)
    {
        alert('Vous devez saisir au moins 3 caractères du nom de l\'annonceur avant de lancer la recherche.');
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
        testAndWrite('cout', '<br/><br/><br/><br/>');

    }
    else if(val == 1)
    {
        // coût variable
        testAndWrite('cout', '<br/>&nbsp; &nbsp; &nbsp; &nbsp;Date de début de facturation : <select name="jourf"><?php
        
        for($i = 1; $i <= 31; ++$i)
        {
            if(isset($_POST['jourf']))
            {
                $sel = ($_POST['jourf'] == $i) ? 'selected' : '';
            }
            else
            {
                $sel = ($i == date('d')) ? 'selected' : '';
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
                $sel = ($i == date('m')) ? 'selected' : '';
            }
        
            print('<option value="' . $i . '" ' . $sel . '>' . $i . '</option>');
        }
        
        ?></select> <select name="anneef"><option value="<?php

        $date = date('Y');
        
        print($date . '"');
        
        if(isset($_POST['anneef']) && $_POST['anneef'] == $date)
        {
            print(' selected');
        }

        ?>><?php print($date) ?></option><option value="<?php
        
        print($date + 1 . '"');
        
        if(isset($_POST['anneef']) && $_POST['anneef'] == ($date + 1))
        {
            print(' selected');
        }

        ?>"><?php print($date + 1) ?></option></select><br/>&nbsp; &nbsp; &nbsp; &nbsp;Coût d\'un contact : <input type="text" name="coutcontact" size="5" maxlength="5" class="champstexte" value="<?php print(to_entities($coutcontact)) ?>"><br/><br/>');
    }
    else
    {
        // coût fixe
        testAndWrite('cout', '<br/>&nbsp; &nbsp; &nbsp; &nbsp;Date de fin d\'abonnement : <select name="joura"><?php
        
        for($i = 1; $i <= 31; ++$i)
        {
            if(isset($_POST['joura']))
            {
                $sel = ($_POST['joura'] == $i) ? 'selected' : '';
            }
            else
            {
                $sel = ($i == date('d')) ? 'selected' : '';
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
                $sel = ($i == date('m')) ? 'selected' : '';
            }
        
            print('<option value="' . $i . '" ' . $sel . '>' . $i . '</option>');
        }
        
        ?></select> <select name="anneea"><option value="<?php
        
        $date = date('Y');
        
        print($date . '"');
        
        if(isset($_POST['anneea']) && $_POST['anneea'] == $date)
        {
            print(' selected');
        }

        ?>><?php print($date) ?></option><option value="<?php
        
        print($date + 1 . '"');
        
        if(!isset($_POST['anneea']) || $_POST['anneea'] == ($date + 1))
        {
            print(' selected');
        }

        ?>"><?php print($date + 1) ?></option></select><br/><br/><br/>');
    }
}


///////////////////////////////////////////////


var listeLinkedHidden = '<?php print($listeHidden) ?>';
var listeLinked       = '<?php print($listeShown) ?>';

function LinkAdv(val)
{
    if(val == '')
    {
        alert('Merci de sélectionner un annonceur avant de le lier.');
        document.addAdvertiser.linksList.focus();
        return;
    }

    if(listeLinkedHidden.indexOf(val + ',', 0) != -1)
    {
        alert('Cet annonceur est déjà présent dans la liste des annonceurs liés.');
        document.addAdvertiser.linksList.focus();
        return;
    }


    document.addAdvertiser.ok.disabled  = true;
    document.addAdvertiser.nok.disabled = true;

    document.addAdvertiser.linker.disabled = true;
    document.addAdvertiser.linker.value = 'Liaison en cours';
    
    /////////////
    
    var uniq = new Date();
        uniq = uniq.getTime();

    var query = '<?php print(session_name() . '=' . session_id()) ?>&time='+ uniq +'&id=' + escape(val);

    var data  = getContent('link.php', query);

    if(data == -1)
    {
        alert('Une erreur est survenue : impossible de récupérer les données de l\'annonceur.');
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
    
    document.addAdvertiser.ok.disabled  = false;
    document.addAdvertiser.nok.disabled = false;

    document.addAdvertiser.linker.disabled = false;
    document.addAdvertiser.linker.value = "Lier";

    document.addAdvertiser.linksList.focus();

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
        listeLinked = 'Actuellement aucun annonceur n\'est lié à celui en cours de création.';
    }
    
    var exp = new RegExp(id + ',', '');
    listeLinkedHidden = listeLinkedHidden.replace(exp, '');

    testAndWrite('advertisers', listeLinked + '<input type="hidden" name="listeLinked" value="'+listeLinkedHidden+'">');


}


<?php

    } // fin non contrib

?>

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
	}
	else
	{
		document.getElementById("label_marge").style.display = 'inline';
		document.getElementById("label_remise").style.display = 'none';
	}
}

function perform_toggles()
{
	toggle_margeRemise(<?php echo $prixPublic ?>);
	toggle_foptions(<?php echo $category ?>);
}

window.onload = perform_toggles;

//-->
</script><form name="addAdvertiser" method="post" action="index.php?<?php print(session_name() . '=' . session_id()) ?>" class="formulaire" enctype="multipart/form-data">
<table cellspacing="0" cellpadding="0">
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
		
		<div class="prop">
			<label>Délai en jours :</label>
			<input class="champstexte" type="text" size="35" maxlength="255" name="delai_expedition" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($delai_expedition)) ?>"/>
		</div>
		
		<?php/*<div class="prop">
			<label>Frais de port :</label>
			<input class="champstexte" type="text" size="35" maxlength="255" name="shipping_fee" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($shipping_fee)) ?>"/> *
		</div>*/?>
		<div class="prop">
			<label>Type de prix :</label>
			<select name="prixPublic" class="champstexte" onChange="toggle_margeRemise(this.form.elements['prixPublic'].options[this.form.elements['prixPublic'].selectedIndex].value)">
				<option value="0"<?php if($prixPublic != '1') print(' selected') ?>>Fournisseur</option>
				<option value="1"<?php if($prixPublic == '1') print(' selected') ?>>Public</option>
			</select>
		</div>
		<div class="prop">
			<label id="label_marge">Taux de marge :</label><label id="label_remise">Taux de remise :</label>
			<input class="champstexte" type="text" size="8" maxlength="255" name="margeRemise" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($margeRemise)) ?>"/>% *
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

<table>
	<tr><td class="intitule">Garantie par défaut :</td><td><input class="champstexte" type="text" size="50" name="warranty" value="<?php echo to_entities($warranty) ?>"/></td></tr>
	<tr><td class="intitule">Afficher le message d'aide :</td><td><input class="champstexte" type="checkbox" name="help_show"<?php if($help_show == '1') print(' checked')?>/></td></tr>
	<tr><td class="intitule">Message d'aide :</td><td><textarea class="champstexte" type="text" rows="5" name="help_msg" style="width: 500px"/><?php print(to_entities($help_msg)) ?></textarea></td></tr>
	<tr><td class="intitule">&nbsp;</td><td>&nbsp;</td></tr>
	<tr><td class="intitule">Personne à contacter :</td><td><input class="champstexte" type="text" size="25" maxlength="255" name="contact" onBlur="this.value = trim(this.value); maj = this.value.charAt(0).toUpperCase(); if(this.value.length > 1) this.value = maj + this.value.substring(1, this.value.length); else this.value = maj" value="<?php print(to_entities($contact)) ?>"></td></tr>
	<tr><td class="intitule">Adresse e-mail :</td><td><input class="champstexte" type="text" size="35" maxlength="255" name="email" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($email)) ?>"></td></tr>
	<tr><td class="intitule">Adresse du site web :</td><td><input class="champstexte" type="text" size="35" maxlength="255" name="url" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($url)) ?>"></td></tr>
	<tr><td class="intitule">Téléphone 1 :</td><td><input class="champstexte" type="text" size="15" name="tel1" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($tel1)) ?>"> *</td></tr>
	<tr><td class="intitule">Téléphone 2 :</td><td><input class="champstexte" type="text" size="15" name="tel2" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($tel2)) ?>"></td></tr>
	<tr><td class="intitule">Fax 1 :</td><td><input class="champstexte" type="text" size="15" name="fax1" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($fax1)) ?>"> *</td></tr>
	<tr><td class="intitule">Fax 2 :</td><td><input class="champstexte" type="text" size="15" name="fax2" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($fax2)) ?>"></td></tr>
	<tr><td class="intitule">Logo :</td><td class="intitule"><input class="champstexte" type="file" size="35" name="logo"> Image au format JPG</td></tr>
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
<?php if($user->rank != CONTRIB) { ?>
<input type="checkbox" name="from_web" <?php if ($from_web) { ?>checked="checked"<?php } ?>/> <label for="from_web">Permettre l'accès à l'extranet via un lien par email</label><br/>
<input type="checkbox" name="show_infos_online"<?php if ($show_infos_online) { ?>checked="checked"<?php } ?> /> <label for="show_infos_online">Afficher en ligne les coordonnées de l'annonceur après une demande de lead</label><br/>
<input type="checkbox" name="noLeads2in" <?php if ($noLeads2in) { ?>checked="checked"<?php } ?>/> <label for="noLeads2in">Désactiver la réception des leads secondaires</label><br/>
<input type="checkbox" name="noLeads2out" <?php if ($noLeads2out) { ?>checked="checked"<?php } ?>/> <label for="noLeads2out">Désactiver l'émission de leads secondaires</label><br/>
<input type="checkbox" name="noAlert2out" /> <label for="noAlert2out">Activer les alertes de notification des contacts non lus</label><br/>
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
<script type="text/javascript" src="advertisers.js"></script>
<script type="text/javascript">$(function(){ HN.TC.BO.Adv.Init(0); });</script>
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

<?php } ?>
<br/>
<br/>
<div class="commentaire">Note : * signifie que le champ est obligatoire.</div>
<br/>
<center><input type="button" class="bouton" value="Valider" name="ok"> &nbsp; <input type="reset" value="Annuler" class="bouton" name="nok"></center>
</form>

<?php

} // fin affichage form

print('</div>');

require(ADMIN . 'tail.php');

?>
