<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004
 
     3 juillet 2005 :  + gestion nom simplifié annonceur

 Fichier : /includes/managerV2/advertisers.php
 Description : Fonction manipulation annonceurs

/=================================================================*/

require_once(ADMIN . 'generator.php');
require_once(ADMIN . 'actions.php');
require_once(ADMIN . 'logo.php');
require_once(ADMIN . 'logs.php');


/* Vérifier l'unicité d'un champ
   i : référence handle connexion
   i : champ à tester
   i : référence valeur à tester
   i : nom table optionnel
   o : true si unique false sinon */
function isAUnique(& $handle, $field, & $value, $table = 'advertisers')
{
    $ret = false;

    if(($result = & $handle->query('select id from ' . $table . ' where ' . $field . ' = \'' . $handle->escape($value) . '\'', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 0)
    {
        $ret = true;
    }

    return $ret;
}



/* Retourner un tableau d'annonceurs
   i : référence handle connexion
   i : fin requete
   i : id utilisateur pour filtre optionnel
   o : référence tableau annonceurs */
function & displayAdvertisers(& $handle, $exp, $idUser = '')
{
    $ret = array();

    if($idUser == '')
    {
        $query = 'select a.id, a.nom1 from advertisers a ' . $exp ;
    }
    else
    {
        $query = 'select a.id, a.nom1 from advertisers a, usersV2 u where u.id = \'' . $handle->escape($idUser) . '\' and u.id = a.idCommercial ' . $exp;
    }

    if($result = & $handle->query($query, __FILE__, __LINE__))
    {
        if($handle->numrows($result, __FILE__, __LINE__) == 0)
        {
            print('<center><b>Aucun résultat</b></center>');
        }
        else
        {

            while($record = & $handle->fetch($result))
            {
                $ret[$record[0]] = & $record[1];
            }

        }
    }


    return $ret;
}



/* Retourner un tableau d'annonceurs avec infos utiles pour les produits
   i : référence handle connexion
   i : fin requete
   i : id utilisateur pour filtre optionnel
   o : référence tableau annonceurs */
function & GetSuppliersInfos(& $handle, $exp, $idUser = '')
{
	$ret = array();
	if($idUser == '') $query = 'select a.id, a.nom1, a.prixPublic, a.margeRemise, a.arrondi, a.idTVA from advertisers a where a.parent = \'61049\' ' . $exp;
	else $query = 'select a.id, a.nom1, a.prixPublic, a.margeRemise, a.arrondi, a.idTVA from advertisers a, usersV2 u where a.parent = \'61049\' and u.id = \'' . $handle->escape($idUser) . '\' and u.id = a.idCommercial ' . $exp;

	if($result = & $handle->query($query, __FILE__, __LINE__))
	{
		if($handle->numrows($result, __FILE__, __LINE__) == 0)
			print('<center><b>Aucun résultat</b></center>');
		else
		{
			while($record = & $handle->fetch($result))
			{
				$ret[$record[0]][0] = & $record[1];
				$ret[$record[0]][1] = & $record[2];
				$ret[$record[0]][2] = & $record[3];
				$ret[$record[0]][3] = & $record[4];
				$ret[$record[0]][4] = & $record[5];
			}
		}
	}
	return $ret;
}



/* Recherche
   i : réf handle connexion
   i : réf pattern recherché
   o : réfé tableau résultats */
function & searchAdvertiser(& $handle, & $pattern)
{
    $ret = array();

    $tab = explode(' ', $handle->escape($pattern));
    $query = 'select nom1 from advertisers where ';
    
    for($i = 0; $i < count($tab); ++$i)
    {
        if($i > 0)
        {
            $query .= 'and ';
        }

        $query .= 'nom1 like \'%' . $tab[$i] . '%\'';
    }
    
    $query .= ' order by nom1';
    
    if($result = & $handle->query($query,  __FILE__, __LINE__))
    {
        while($record = & $handle->fetch($result))
        {
            $ret[] = & $record[0];
        }
    
    }


    return $ret;

}



/* Ajouter un annonceur
   i : réf handle connexion
   i : id commercial
   i : réf nom 1
   i : réf nom 2
   i : réf adresse 1
   i : réf adresse 2
   i : réf ville
   i : cp
   i : réf pays
   i : réf delai_livraison
   i : prixPublic
   i : margeRemise
   i : peuChangerTaux
   i : arrondi
   i : idTVA
   i : contraintePrix
   i : réf contact
   i : réf email
   i : réf url
   i : réf tel 1
   i : réf tel 2
   i : réf fax 1
   i : réf fax 2
   i : réf prénom contact
   i : réf nom contact
   i : réf email contact
   i : critère
   i : type cout
   i : réf date début facturation
   i : cout contact
   i : réf date fin abonnement
   i : réf liste annonceurs liés
   i : nom user action
   o : true si ok, false si erreur */
function addAdvertiser(& $handle, $idCommercial, & $nom1, & $nom2, & $adresse1, & $adresse2, & $ville, $cp, & $pays, & $delai_livraison, & $delai_expedition, $shipping_fee, $warranty, $catalog_code, $prixPublic, $margeRemise, $peuChangerTaux, $arrondi, $idTVA, $contraintePrix, & $contact, & $email, & $url, & $tel1, & $tel2, & $fax1, & $fax2, $client_id, & $prenomcontact, & $nomcontact, & $emailcontact, $critere, $type, & $datef, $coutcontact, & $datea, & $liste, $username, $category, & $contacts, $from_web, $cc_foreign, $cc_intern, $show_infos_online, $help_show, $help_msg, $notRequiredFields, $cc_noPrivate, $ic_reject, $ic_active, $ic_fields, $ic_extranet, $noLeads2in, $noLeads2out, $auto_reject_threshold, $asEstimate)
{
    $ret = false;
    if(($idAdvertiser = generateID(1, 65535, 'id', 'advertisers', $handle)) &&
		$handle->query("insert into advertisers (id, idCommercial, timestamp, nom1, nom2, adresse1, adresse2, ville, cp, pays, delai_livraison,delai_livraison_num, shipping_fee, warranty, catalog_code, prixPublic, margeRemise, peuChangerTaux, arrondi, " .
		"idTVA, contraintePrix, contact, email, url, tel1, tel2, fax1, fax2, pcontact, ncontact, econtact, critere, typecout, debfacturation, cout, finabonnement, ref_name, parent, category, create_time, contacts, from_web, " .
		"cc_foreign, cc_intern, cc_noPrivate, show_infos_online, help_show, help_msg, notRequiredFields, ic_reject, ic_active, ic_fields, ic_extranet, noLeads2in, noLeads2out, auto_reject_threshold, as_estimate, client_id) " .
		"values ('" . $idAdvertiser . "', '" . $handle->escape($idCommercial) . "', '" . time() . "', '" . $handle->escape($nom1) . "', '" . $handle->escape($nom2) . "', '" .
		$handle->escape($adresse1) . "', '" . $handle->escape($adresse2) . "', '" . $handle->escape($ville) . "', '" . $handle->escape($cp) . "', '" . $handle->escape($pays) . "', '" .
		$handle->escape($delai_livraison) . "','".$handle->escape($delai_expedition) ."', '" . $handle->escape($shipping_fee) . "', '" . $handle->escape($warranty) . "', '" . $handle->escape($catalog_code) . "', '" . $handle->escape($prixPublic) . "', '" .
		$handle->escape($margeRemise) . "', '" . $handle->escape($peuChangerTaux) . "', '" .
		$handle->escape($arrondi) . "', '" . $handle->escape($idTVA) . "', '" . $handle->escape($contraintePrix) . "', '" . $handle->escape($contact) . "', '" . $handle->escape($email) .
		"', '" . $handle->escape($url) . "', '" . $handle->escape($tel1) . "', '" . $handle->escape($tel2) . "', '" . $handle->escape($fax1) . "', '" . $handle->escape($fax2) . "', '" .
		$handle->escape($prenomcontact) . "', '" . $handle->escape($nomcontact) . "', '" . $handle->escape($emailcontact) . "', '" . $handle->escape($critere) . "', '" .
		$handle->escape($type) . "', '" . $handle->escape($datef) . "', '" . $handle->escape($coutcontact) . "', '" . $handle->escape($datea) . "', '" . $handle->escape(Utils::toDashAz09($nom1)) .
		"', '" . ($category == __ADV_CAT_SUPPLIER__ ? __ID_TECHNI_CONTACT__ : 0) . "', '" . $handle->escape($category) . "', '" . time() . "', '" . $handle->escape(serialize($contacts)) .
		"', " . $from_web . ", " . $cc_foreign . ", " . $cc_intern . ", " . $cc_noPrivate . ", " . $show_infos_online . ", " . $help_show . ", '" . $handle->escape($help_msg) . "', '" .
		$handle->escape($notRequiredFields) . "', '" . $handle->escape($ic_reject) . "', '" . $handle->escape($ic_active) . "', '" . $handle->escape($ic_fields) . "', '" . $handle->escape($ic_extranet) . "', '" .
		$handle->escape($noLeads2in) . "', '" . $handle->escape($noLeads2out) . "', '" . $handle->escape($auto_reject_threshold) . "', '" . $handle->escape($asEstimate) . "', '". $handle->escape($client_id) ."')"))
    {
        // Création de l'accès
        $extranetlogin = str_replace(' ', '', strtolower($nom1));
        $extranetpass  = rand(100000, 999999);
        
		$webpass = '';
		$all = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$len = strlen($all);
		for($i = 0; $i < 32; $i++) $webpass .= $all[mt_rand(0,$len)];
		$handle->query("insert into extranetusers (id, login, pass, webpass, c) values(" . $idAdvertiser . ", '" . $handle->escape($extranetlogin) . "', '" . $extranetpass . "', '" . $webpass. "', 0)", __FILE__, __LINE__);

        // Fin création accès

        if ($category == __ADV_CAT_SUPPLIER__)
		{
			notify($handle, 'Création du fournisseur ' . $nom1 . ' [ID : ' . $idAdvertiser . ']', $username);
			$MLog = "Création du fournisseur " . $nom1 . ' - ' . $nom2 . " [ID : " . $idAdvertiser . "] - Commercial : " . $idCommercial .
			" | Adresse : " . $adresse1 . ' - ' . $adresse2 . ' - ' . $ville . ' - ' . $cp . ' - ' . $pays .
			" | Délai de livraison : " . $delai_livraison .
			" | Frais de port : " . $shipping_fee .
			" | Garantie : " . $warranty .
			" | Vidéo : " . (empty($catalog_code)?"Non":"Oui") .
			" | Prix public : " . $prixPublic .
			" | Marge ou remise : " . $margeRemise .
			" | Changement sur l'extranet autorisé : " . $peuChangerTaux .
			" | Arrondi : " . $arrondi .
			" | TVA par défaut : " . $idTVA .
			" | Contrainte de prix : " . $contraintePrix .
      " | Mise sous devis pas défaut : " . $asEstimate .
			" | Contact : " . $contact . ' - ' . $email . ' - ' . $url .
			" | Tel & Fax : " . $tel1 . ' - ' . $tel2 . ' - ' . $fax1 . ' - ' . $fax2 .
			" | Infos comp contact : " . $prenomcontact . ' - ' . $nomcontact . ' - ' . $emailcontact .
			" | Priorité : " . $critere .
      " | ID compte client : " . $client_id;
			$i = 1;
			while (!empty($contacts[$i]['nom']))
			{
				$MLog .= " | contact " . ($i+1) . " : " . $contacts[$i]['prenom'] . " - " . $contacts[$i]['nom'] . " - " . $contacts[$i]['email'] . " | Priorité : " . $contacts[$i]['critere'];
				$i++;
			}
			$MLog .= " | Extranet accessible depuis mail : " . ($from_web == '1' ? 'Oui' : 'Non') .
			" | Comptabiliser les demandes de contacts de sociétés étrangères : " . ($cc_foreign == '1' ? 'Oui' : 'Non') .
			" | Comptabiliser les demandes de contacts des stagiaires : " . ($cc_intern == '1' ? 'Oui' : 'Non') .
			" | Ne pas comptabiliser les demandes de particulier : " . ($cc_noPrivate == '1' ? 'Oui' : 'Non') .
			" | Reçoit les demandes de leads secondaires : " . ($noLeads2in ? 'Non' : 'Oui') .
			" | Emet les demandes de leads secondaires : " . ($noLeads2out ? 'Non' : 'Oui') .
			" | Autoriser l'annonceur à rejeter des demandes sur son extranet : " . ($ic_reject ? 'Oui' : 'Non') .
			" | Activer la personnalisation de la facturation : " . ($ic_active ? 'Oui' : 'Non') .
			" | Permettre à l'annonceur de modifier sa facturation sur l'extranet : " . ($ic_extranet ? 'Oui' : 'Non') .
			" | Nombre de rejets successifs avant rejection automatique : " . ($auto_reject_threshold ? $auto_reject_threshold : 'Réglage global') .
			" | Afficher en ligne les coordonnées de l'annonceur après une demande de lead : " . ($show_infos_online == '1' ? 'Oui' : 'Non') .
			" | Afficher le message d'aide en FO : " . ($help_show == '1' ? 'Oui' : 'Non') .
			" | Message d'aide en FO : " . $help_msg .
			" | Champs par défaut non obligatoire : " . $notRequiredFields .
			" | Type abonnement : " . $type . ", Date début facturation : " . $datef . ", Coût contact : " . $coutcontact . ", Date fin abonnement " . $datea .
			" | Annonceurs liés : " . $liste .
			" | Catégorie : " . $adv_cat_list[$category]["desc"];
			
			ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'], $MLog);
		}
		else
		{
			notify($handle, "Création de l'annonceur " . $nom1 . " [ID : " . $idAdvertiser . "]", $username);
	        $MLog = "Création de l'annonceur " . $nom1 . ' - ' . $nom2 . " [ID : " . $idAdvertiser . "] - Commercial : " . $idCommercial .
			" | Adresse : " . $adresse1 . ' - ' . $adresse2 . ' - ' . $ville . ' - ' . $cp . ' - ' . $pays .
			" | Contact : " . $contact . ' - ' . $email . ' - ' . $url .
			" | Tel & Fax : " . $tel1 . ' - ' . $tel2 . ' - ' . $fax1 . ' - ' . $fax2 .
			" | Infos comp contact : " . $prenomcontact . ' - ' . $nomcontact . ' - ' . $emailcontact .
			" | Priorité : " . $critere .
      " | ID compte client : " . $client_id .
			$i = 1;
			while (!empty($contacts[$i]['nom']))
			{
				$MLog .= " | contact " . ($i+1) . " : " . $contacts[$i]['prenom'] . " - " . $contacts[$i]['nom'] . " - " . $contacts[$i]['email'] . " | Priorité : " . $contacts[$i]['critere'];
				$i++;
			}
			$MLog .= " | Type abonnement : " . $type . ", Date début facturation : " . $datef . ", Coût contact : " . $coutcontact . ", Date fin abonnement " . $datea .
			" | Annonceurs liés : " . $liste .
			" | Catégorie : " . $adv_cat_list[$category]["desc"];
			
			ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'], $MLog);
		}

		
        // upload du logo
        upload('logo', 'jpg', $idAdvertiser, 100, 100, ADVERTISERS_LOGOS_INC);


        // Annonceurs liés
        if(!empty($liste))
        {
            $listeTab = explode(',', $liste);

            for($i = 0; $i < count($listeTab); ++$i)
            {
                if(preg_match('/^[0-9]+$/', $listeTab[$i]))
                {
                    if(($result = & $handle->query('select nom1 from advertisers where id = \'' . $handle->escape($listeTab[$i]). '\'', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
                    {
                        $handle->query('insert into advertiserslinks (idAdvertiser, idAdvertiserLinked) values(\'' . $idAdvertiser . "', '" . $handle->escape($listeTab[$i]) . '\')');
                    }
                }
            }
			
        }


        $ret = true;
    }

    return $ret;

}


/* Chargement annonceur
   i : réf handle connexion
   i : id annonceur
   o : réf tableau élément ou false si erreur */
function & loadAdvertiser(& $handle, $id, $extranet = false)
{
	$ret = false;
	$query = "
		select
			actif, idCommercial, nom1, nom2, adresse1,
			adresse2, ville, cp, pays, delai_livraison,
			prixPublic, margeRemise, peuChangerTaux, arrondi, idTVA,
			contraintePrix, contact, email, url, tel1,
			tel2, fax1, fax2, pcontact, ncontact,
			econtact, critere, typecout, debfacturation, cout,
			finabonnement, category, contacts, from_web, cc_foreign,
			cc_intern, show_infos_online, help_show, help_msg, shipping_fee,
			warranty, catalog_code, notRequiredFields, cc_noPrivate, ic_reject,
      ic_active, ic_fields, ic_extranet, noLeads2in, noLeads2out,
      auto_reject_threshold, as_estimate, client_id, litigation_time, direct_debit
		from
			advertisers
		where
			id = " . $handle->escape($id)." AND deleted != 1";
	if(($result = & $handle->query($query, __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
	{
		$record = & $handle->fetch($result);
		
		if($extranet)
		{
			if(($result2 = $handle->query("
				select
					adresse1, adresse2, ville, cp, pays,
					delai_livraison, margeRemise, contraintePrix, contact, email,
					url, tel1, tel2, fax1, fax2,
					shipping_fee, warranty
				from advertisers_adv
				where id = '".$handle->escape($id)."'", __FILE__, __LINE__)) && $handle->numrows($result2, __FILE__, __LINE__) == 1) {
				
				$record2 = & $handle->fetch($result2);
				$record[4]  = $record2[0]; // adresse1
				$record[5]  = $record2[1]; // adresse2
				$record[6]  = $record2[2]; // ville
				$record[7]  = $record2[3]; // cp
				$record[8]  = $record2[4]; // pays
				$record[9]  = $record2[5]; // delai_livraison
				$record[11] = $record2[6]; // margeRemise
				$record[15] = $record2[7]; // contraintePrix
				$record[16] = $record2[8]; // contact
				$record[17] = $record2[9]; // email
				$record[18] = $record2[10]; // url
				$record[19] = $record2[11]; // tel1
				$record[20] = $record2[12]; // tel2
				$record[21] = $record2[13]; // fax1
				$record[22] = $record2[14]; // fax2
				$record[39] = $record2[15]; // shipping_fee
				$record[40] = $record2[16]; // warranty
			}
			else
			{
				return false;
			}		
		}
		
		
		$liste = '';
		
		// Annonceurs liés
		if(($result = & $handle->query('select idAdvertiserLinked from advertiserslinks where idAdvertiser = \'' . $handle->escape($id) . '\'', __FILE__, __LINE__)))
		{
			while($row = & $handle->fetch($result))
			{
				$liste .= $row[0] . ',';
			}
		}

		$record[] = & $liste; // 55
		$ret      = & $record;
	}
  
	return $ret;

}


function getParentAdvertiser(& $handle, $id)
{
    $ret = -1;

    if(($result = & $handle->query('select parent from advertisers where id = \'' . $handle->escape($id) . '\'', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
    {
        $record = & $handle->fetch($result);

		$ret = $record[0];
    }

    return $ret;

}


function displayWaitAdvExt(& $handle)
{
	$ret = array();
	
	if($res = $handle->query('select a.id, a.nom1, adv.timestamp from advertisers a, advertisers_adv adv where a.id = adv.id order by adv.timestamp desc', __FILE__, __LINE__))
	{
		while($data = & $handle->fetch($res))
		{
			$ret[] = $data;
		}
	}
	
	return $ret;
}



/* Obtenir login et pass annonceur
   i : référence handle connexion
   i : id annonceur
   o : réf tableau données ou false si erreur */
function & getExtranetData(& $handle, $id)
{
    $ret = false;
    
    if(($result = & $handle->query('select login, pass from extranetusers where id = \'' . $handle->escape($id) . '\'', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
    {
        $ret = & $handle->fetchAssoc($result);
        
        // Hash de mot de passe = mot de passe déjà modifié
        if(strlen($ret[1]) == 32)
        {
            $ret[1] = '';
        }
    }
    
    
    return $ret;



}




/* Produit annonceur donné
   i : réf handle connexion
   i : id annonceur
   o : réf tableau produits */
function & displayProducts(& $handle, $id)
{
    $ret = array();
  
    if($result = & $handle->query('select p.id, p.name, p.ref_name, pf.idFamily, p.fastdesc from products_fr p, products_families pf where p.idAdvertiser = \'' . $handle->escape($id) . '\' and p.active = 1 and p.id = pf.idProduct group by p.id order by p.name', __FILE__, __LINE__ ))
    {
        while($record = & $handle->fetch($result))
        {
            $ret[] = & $record;
        }

    }


    return $ret;

}



/* Produit annonceur liés à l'annonceur donné (tableau vide entre chaque annonceur lié)
   i : réf handle connexion
   i : id annonceur
   o : réf tableau produits */
function & displayProductsAdvertisersLinked(& $handle, $id)
{
    $ret = array();

    if($result = & $handle->query('select a.nom1, pl.id, pl.name, pl.ref_name, pf.idFamily, pl.fastdesc from advertiserslinks al, advertisers a, products_fr pl, products_families pf where al.idAdvertiser = \'' . $handle->escape($id) . '\' and a.id = pl.idAdvertiser and pl.idAdvertiser = al.idAdvertiserLinked and pl.active = 1 and pl.id = pf.idProduct group by pl.id order by a.nom1, pl.name', __FILE__, __LINE__))
    {
        $oldAdvertiser = '';

        while($record = & $handle->fetch($result))
        {
            if($record[0] != $oldAdvertiser)
            {
                if($oldAdvertiser != '')
                {
                    $ret[] = array();
                }

                $oldAdvertiser = $record[0];

            }

            $ret[] = & $record;
            
        }

    }

    
    return $ret;
    
}


/* Supprimer un annonceur
   i : réf handle connexion
   i : id annonceur
   i : réf nom annonceur */
function delAdvertiser(& $handle, $id, & $nom)
{
//    $handle->query('delete from products where idAdvertiser = \'' . $handle->escape($id) . '\'', __FILE__, __LINE__);
    $handle->query('UPDATE products_fr SET deleted=1 WHERE idAdvertiser = \'' . $handle->escape($id) . '\'', __FILE__, __LINE__);

//    if($result = & $handle->query('select id from products_fr where idAdvertiser = \'' . $handle->escape($id) . '\'', __FILE__, __LINE__))
//    {
//        while($row = & $handle->fetch($result))
//        {
//            $handle->query('delete from products_families where idProduct = \'' . $row[0] . '\'', __FILE__, __LINE__);
//            //$handle->query('delete from contacts where idProduct = \'' . $row[0] . '\'', __FILE__, __LINE__);
//            $handle->query('delete from productslinks where idProduct = \'' . $row[0] . '\' or idProductLinked = \'' . $row[0] . '\'', __FILE__, __LINE__);
//            $handle->query('delete from actions where action like \'%de la fiche produit%\' and action like \'%[ID : ' . $row[0] . ']%\'', __FILE__, __LINE__);
//            $handle->query('delete from stats_products where id = \'' . $row[0] . '\'', __FILE__, __LINE__);
//            $handle->query('delete from sup_requests where idProduct = \'' . $row[0] . '\'', __FILE__, __LINE__);
//
//			// Effacer références produit donné
//			$handle->query('delete from references_content where idProduct = \'' . $row[0] . '\'', __FILE__, __LINE__);
//			$handle->query('delete from references_cols    where idProduct = \'' . $row[0] . '\'', __FILE__, __LINE__);
//
//
//
//            for($i = 1; $i <= 3; ++$i)
//            {
//                @unlink(PRODUCTS_FILES_INC . $row[0] . '-' . $i . '.doc');
//                @unlink(PRODUCTS_FILES_INC . $row[0] . '-' . $i . '.pdf');
//            }
//
//
//            @unlink(PRODUCTS_IMAGE_INC . 'zoom/' . $row[0] . '.jpg');
//            @unlink(PRODUCTS_IMAGE_INC . $row[0] . '.jpg');
//        }
//    }

//    $handle->query('delete from products_fr  where idAdvertiser = \'' . $handle->escape($id) . '\'', __FILE__, __LINE__);
//    $handle->query('delete from products_add where idAdvertiser = \'' . $handle->escape($id) . '\'', __FILE__, __LINE__);
//    $handle->query('delete from advertiserslinks where idAdvertiser = \'' . $handle->escape($id) . '\' or idAdvertiserLinked = \'' . $handle->escape($id) . '\'', __FILE__, __LINE__);

//    $handle->query('delete from advertisers where id = \'' . $handle->escape($id) . '\' limit 1', __FILE__, __LINE__);
    $handle->query('UPDATE advertisers SET deleted=1 WHERE id = \'' . $handle->escape($id) . '\' limit 1', __FILE__, __LINE__);
    
	// redondant, les produits sont effaces dans la boucle
//	$handle->query('delete from contacts where idAdvertiser = \'' . $handle->escape($id) . '\'', __FILE__, __LINE__);

//    $handle->query('delete from actions where action like \'%de l\\\'annonceur%\' and action like \'%[ID : ' . $handle->escape($id) . ']%\'', __FILE__, __LINE__);

    // Suppression de l'accès
//    $handle->query('delete from extranetusers where id = \'' . $handle->escape($id) . '\' limit 1', __FILE__, __LINE__);


//    @unlink(ADVERTISERS_LOGOS_INC . $id . '.gif');

    ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],  'Suppression de l\'annonceur ou du fournisseur ' . $nom . ' [ID : ' . $id . ']');

}


/* Mettre à jour un annonceur
   i : réf handle connexion
   i : id annonceur
   i : id commercial
   i : réf nom 1
   i : réf nom 2
   i : réf adresse 1
   i : réf adresse 2
   i : réf ville
   i : cp
   i : réf pays
   i : réf delai_livraison
   i : prixPublic
   i : margeRemise
   i : peuChangerTaux
   i : arrondi
   i : idTVA
   i : contraintePrix
   i : réf contact
   i : réf email
   i : réf url
   i : réf tel 1
   i : réf tel 2
   i : réf fax 1
   i : réf fax 2
   i : réf prénom contact
   i : réf nom contact
   i : réf email contact
   i : critère
   i : type cout
   i : réf date début facturation
   i : cout contact
   i : réf date fin abonnement
   i : réf liste annonceurs liés
   i : annonceur actif ?
   i : nom user action
   i : réf ancien nom de l'annonceur
   i : booléen maj éléments comm
   i : nouveau login facultatif
   i : nouveau pass facultatif
   i : nouveau parent
   i : ancien parent
   o : true si ok, false si erreur
   i : prélèvement par défaut */
function updateAdvertiser(& $handle, $id, $idCommercial, & $nom1, & $nom2, & $adresse1, & $adresse2, & $ville, $cp, & $pays, & $delai_livraison, & $delai_expedition ,$shipping_fee, $warranty, $catalog_code, $prixPublic, $margeRemise, $peuChangerTaux, $arrondi, $idTVA, $contraintePrix, $asEstimate, & $contact, & $email, & $url, & $tel1, & $tel2, & $fax1, & $fax2, $client_id, & $prenomcontact, & $nomcontact, & $emailcontact, $critere, $type, & $datef, $coutcontact, & $datea, & $liste, $active, $username, & $oldn, $upall, $newlogin = '', $newpass = '', $category, $oldcategory, & $contacts, $from_web, $cc_foreign, $cc_intern, $show_infos_online, $help_show, $help_msg, $notRequiredFields, $cc_noPrivate, $ic_reject, $ic_active, $ic_fields, $ic_extranet, $noLeads2in, $noLeads2out, $auto_reject_threshold, $timestamp_litigation, $direct_debit)
{
    $ret = false;
    $EOQuery = $EOLog = $extranet = '';

    $timestamp_litigation_query = !empty ($timestamp_litigation) ? ', litigation_time = \'' . $handle->escape($timestamp_litigation) . '\'' : '';
    
    if($upall)
    {     // Elements up uniquement si comm
        $EOQuery = ", pcontact = '" . $handle->escape($prenomcontact) . "', ncontact = '" . $handle->escape($nomcontact) . "', econtact = '" . $handle->escape($emailcontact) .
			"', critere = '" . $handle->escape($critere) . "', typecout = '" . $handle->escape($type) . "', debfacturation = '" . $handle->escape($datef) .
			"', cout = '" . $handle->escape($coutcontact) . "', finabonnement = '" . $handle->escape($datea) . "', actif = '" . $handle->escape($active) .
			"', contacts = '" . $handle->escape(serialize($contacts)) . "', from_web = " . $from_web . ", cc_foreign = " . $cc_foreign . ", cc_intern = " . $cc_intern . ", cc_noPrivate = " . $cc_noPrivate .
			", show_infos_online = " . $show_infos_online . ", help_show = " . $help_show . ", help_msg = '" . $handle->escape($help_msg) . "', notRequiredFields = '" . $handle->escape($notRequiredFields) .
			"', ic_reject = '" . $handle->escape($ic_reject) . "', ic_active = '" . $handle->escape($ic_active) . "', ic_fields = '" . $handle->escape($ic_fields) . "', ic_extranet = '" . $handle->escape($ic_extranet) .
			"', noLeads2in = '" . $handle->escape($noLeads2in) . "', noLeads2out = '" . $handle->escape($noLeads2out) . "', auto_reject_threshold = '" . $handle->escape($auto_reject_threshold) . "'";
        $EOLog = " | Infos comp contact : " . $prenomcontact . " - " . $nomcontact . " - " . $emailcontact . " | Priorité : " . $critere;
		$i = 1;
		while (!empty($contacts[$i]['nom']))
		{
			$EOLog .= " | contact " . ($i+1) . " : " . $contacts[$i]['prenom'] . " - " . $contacts[$i]['nom'] . " - " . $contacts[$i]['email'] . " | Priorité : " . $contacts[$i]['critere'];
			$i++;
		}
		$EOLog .= " | Extranet accessible depuis mail : " . ($from_web == '1' ? "Oui" : "Non") .
		" | Comptabiliser les demandes de contacts de sociétés étrangères : " . ($cc_foreign == '1' ? 'Oui' : 'Non') .
		" | Comptabiliser les demandes de contacts des stagiaires : " . ($cc_intern == '1' ? 'Oui' : 'Non') .
		" | Ne pas comptabiliser les demandes de particulier : " . ($cc_noprivate == '1' ? 'Oui' : 'Non') .
		" | Reçoit les demandes de leads secondaires : " . ($noLeads2in ? 'Non' : 'Oui') .
		" | Emet les demandes de leads secondaires : " . ($noLeads2out ? 'Non' : 'Oui') .
		" | Autoriser l'annonceur à rejeter des demandes sur son extranet : " . ($ic_reject ? 'Oui' : 'Non') .
		" | Activer la personnalisation de la facturation : " . ($ic_active ? 'Oui' : 'Non') .
		" | Permettre à l'annonceur de modifier sa facturation sur l'extranet : " . ($ic_extranet ? 'Oui' : 'Non') .
		" | Nombre de rejets successifs avant rejection automatique : " . ($auto_reject_threshold ? $auto_reject_threshold : 'Réglage global') .
		" | Afficher en ligne les coordonnées de l'annonceur après une demande de lead : " . ($show_infos_online == '1' ? 'Oui' : 'Non') .
		" | Afficher le message d'aide en FO : " . ($help_show == '1' ? 'Oui' : 'Non') .
		" | Message d'aide en FO : " . $help_msg .
		" | Champs par défaut non obligatoire : " . $notRequiredFields .
		" | Type abonnement : " . $type . ", Date début facturation : " . $datef . ", Coût contact : " . $coutcontact . ", Date fin abonnement " . $datea .
		" | Annonceurs liés : " . $liste;
    }
    
	$extranet = "login = '" . $handle->escape($newlogin) . "'";
    $EOLog   .= '| Login extranet : ' . $newlogin;

    // Si nouveau mot de passe proposé
    if($newpass != '')
    {
        $extranet .= ", pass = '" . $handle->escape($newpass) . "'";
        $EOLog    .= '| Changement du mot de passe extranet';
    }

    if($handle->query('update advertisers set idCommercial = \'' . $handle->escape($idCommercial) . '\', timestamp = \'' . time() . '\', nom1 = \'' . $handle->escape($nom1) . '\', nom2 = \'' . $handle->escape($nom2) . '\', adresse1 = \'' . $handle->escape($adresse1) . '\', adresse2 = \'' . $handle->escape($adresse2) . '\', ville = \'' . $handle->escape($ville) . '\', cp = \'' . $handle->escape($cp) . '\', pays = \'' . $handle->escape($pays) . '\', delai_livraison = \'' . $handle->escape($delai_livraison) . '\', delai_livraison_num = \'' . $handle->escape($delai_expedition) . '\', shipping_fee = \'' . $handle->escape($shipping_fee) . '\', warranty = \'' . $handle->escape($warranty) . '\', catalog_code = \'' . $handle->escape($catalog_code) . '\', prixPublic = \'' . $handle->escape($prixPublic) . '\', margeRemise = \'' . $handle->escape($margeRemise) . '\', peuChangerTaux = \'' . $handle->escape($peuChangerTaux) . '\', arrondi = \'' . $handle->escape($arrondi) . '\', idTVA = \'' . $handle->escape($idTVA) . '\', contraintePrix = \'' . $handle->escape($contraintePrix) . '\', as_estimate = \'' . $handle->escape($asEstimate) . '\', contact = \'' . $handle->escape($contact) . '\', email = \'' . $handle->escape($email) . '\', url = \'' . $handle->escape($url) . '\', tel1 = \'' . $handle->escape($tel1) . '\', tel2 = \'' . $handle->escape($tel2) . '\', fax1 = \'' . $handle->escape($fax1) . '\', fax2 = \'' . $handle->escape($fax2) . '\', client_id = \'' . $handle->escape($client_id) . '\', ref_name = \'' . $handle->escape(Utils::toDashAz09($nom1)) . '\', parent = \'' . ($category == __ADV_CAT_SUPPLIER__ ? __ID_TECHNI_CONTACT__ : 0) . '\', category = \'' . $handle->escape($category) . '\', direct_debit = \'' . $handle->escape($direct_debit) . '\' ' . $timestamp_litigation_query . $EOQuery . ' where id = \'' . $handle->escape($id) . '\'') && $handle->affected() == 1)
    {
        // Nouveau login et/ou nouveau mot de passe
		$handle->query('update extranetusers set ' . $extranet . ' where id = \'' . $handle->escape($id) . '\' limit 1', __FILE__, __LINE__);
		
		if ($category == $oldcategory)
		{
			notify($handle, "Modification " . $adv_cat_list[$category]["pre"] . " " . $nom1 . " [ID : " . $idAdvertiser . "]", $username);
			ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'], "Edition " . $adv_cat_list[$category]["pre"] . " " . $oldn . " / Nouvelles données : " . $nom1 . " - " . $nom2 . " [ID : " . $id . "] - Commercial : " . $idCommercial . " | Adresse : " . $adresse1 . " - " . $adresse2 . " - " . $ville . " - " . $cp . " - " . $pays . " | Délai de livraison : " . $delai_livraison . " | Frais de port : " . $shipping_fee . " | Garantie : " . $warranty . " | Vidéo : " . (empty($catalog_code)?"Non":"Oui") . " | Prix public : " . $prixPublic . " | Marge ou remise : " . $margeRemise . " | Changement sur l'extranet autorisé : " . $peuChangerTaux . " | Arrondi : " . $arrondi . " | TVA par défaut : " . $idTVA . " | Contrainte de prix : " . $contraintePrix . " | Mise sous devis par défaut : " . $asEstimate . " | Contact : " . $contact . " - " . $email . " - " . $url . " | Tel & Fax : " . $tel1 . " - " . $tel2 . " - " . $fax1 . " - " . $fax2 . " | ID compte client : " . $client_id . " - " . $EOLog);
		}
		else
		{
			notify($handle, "Modification " . $adv_cat_list[$oldcategory]["pre"] . " " . $nom1 . " en " . $adv_cat_list[$category]["name"] . " [ID : " . $idAdvertiser . "]", $username);
			ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'], "Edition " . $adv_cat_list[$oldcategory]["pre"] . " " . $oldn . " en " . $adv_cat_list[$category]["name"] . " / Nouvelles données : " . $nom1 . " - " . $nom2 . " [ID : " . $id . "] - Commercial : " . $idCommercial . " | Adresse : " . $adresse1 . " - " . $adresse2 . " - " . $ville . " - " . $cp . " - " . $pays . " | Délai de livraison : " . $delai_livraison . " | Frais de port : " . $shipping_fee . " | Garantie : " . $warranty . " | Vidéo : " . (empty($catalog_code)?"Non":"Oui") . " | Prix public : " . $prixPublic . " | Marge ou remise : " . $margeRemise . " | Changement sur l'extranet autorisé : " . $peuChangerTaux . " | Arrondi : " . $arrondi . " | TVA par défaut : " . $idTVA . " | Contrainte de prix : " . $contraintePrix . " | Mise sous devis par défaut : " . $asEstimate . " | Contact : " . $contact . " - " . $email . " - " . $url . " | Tel & Fax : " . $tel1 . " - " . $tel2 . " - " . $fax1 . " - " . $fax2 . " | ID compte client : " . $client_id . " - " . $EOLog);
		}

        // upload du logo
        upload('logo', 'jpg', $id, 100, 75, ADVERTISERS_LOGOS_INC);

        // Maj liste uniquement si comm
        if($upall)
        {
            // Supprimer les anciens liens
            $handle->query('delete from advertiserslinks where idAdvertiser = \'' . $handle->escape($id) . '\'');

            // Annonceurs liés
            if(!empty($liste))
            {
                $listeTab = explode(',', $liste);

                for($i = 0; $i < count($listeTab); ++$i)
                {
                    if(preg_match('/^[0-9]+$/', $listeTab[$i]))
                    {
                        if(($result = & $handle->query('select nom1 from advertisers where id = \'' . $handle->escape($listeTab[$i]). '\'', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
                        {
                            $handle->query('insert into advertiserslinks (idAdvertiser, idAdvertiserLinked) values(\'' . $handle->escape($id) . "', '" . $handle->escape($listeTab[$i]) . '\')');
                        }
                    }
                }
            }
            
        } // fin up all


        $ret = true;
    }

    return $ret;

}

?>
