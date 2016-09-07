<?php

/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com - OD
 Date de crÃ©ation : 11 janvier 2011

 Fichier : /secure/manager/advertisers/AJAX_reset_credit.php
 Description : Fichier AJAX de remise à 0 des leads en avoirs

/=================================================================*/

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

require(ADMIN."logs.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

function rawurldecodeEuro ($str) { return str_replace("%u20AC", "â‚¬", rawurldecode($str)); }

$o = array();
if(!$user->login()) {
	$o["error"] = "Votre session a expirÃ©e, veuillez rÃ©actualiser la page pour retourner Ã  la page de login";
	print json_encode($o);
	exit();
}

if (!$user->get_permissions()->has("m-prod--sm-partners","ed")) {
        $o["error"] = "Vous n'avez pas les droits adÃ©quats pour rÃ©aliser cette opÃ©ration.";
        print json_encode($o);
        exit();
      }

$thisDay = date('d');
$thisMonth = date('m');
$thisYear = date('Y');
$lastDayNextMonth = mktime(23, 59, 59, $thisMonth+1, date('t', $thisMonth+1), $thisYear);
define("__BEGIN_TIME__", mktime(0,0,0,1,1,2004));

if (isset($_GET['action'])) {
	switch ($_GET['action']) {
		case "getHistory" :
			if (isset($_GET['advID'])) {
				$res = & $handle->query("SELECT is_fields FROM advertisers WHERE id = ".(int)$_GET['advID'], __FILE__, __LINE__);
				if ($handle->numrows($res, __FILE__, __LINE__) > 0) {
					list($is_fields) = $handle->fetch($res);
					if ($is_fields != "") $is_fields = mb_unserialize($is_fields);
					else $is_fields = array();
					$o = $is_fields;
				}
			}
			else $o["error"] = "ID du fournisseur non spécifiée";
			break;
			
		case "add" :
			if (isset($_GET['advID']) && isset($_GET['is_type']) && isset($_GET['is_field'])) {
				if (empty($_GET['is_type']) || empty($_GET['is_field']))
					$o["error"] = "Valeur manquante";
				else {
					$advID = (int)$_GET['advID'];
					$is_type = $_GET['is_type'];
					$is_field = json_decode(rawurldecode($_GET['is_field']));
					$valid = true;
					foreach($is_field as $v) {
						if (empty($v)) {
							$valid = false;
							break;
						}
					}
          if ($valid) {
            $amountToCheck = $is_field->credit_amount;
            $totalCreditAmount = 0;
            $leadsCredited = array();
            $query = "SELECT id, income FROM contacts WHERE idAdvertiser = ".(int)$_GET['advID'].
            " and credited_on > " .__BEGIN_TIME__ . " and credited_on < " . $lastDayNextMonth .
            " and invoice_status = " . __LEAD_INVOICE_STATUS_CREDITED__;
            $res = & $handle->query($query, __FILE__, __LINE__);
            while($leadCredited = $handle->fetchAssoc($res)){
              $leadsConcerned[] = $leadCredited['id'];
              $totalCreditAmount += $leadCredited['income'];
            }
            $amountCheck = sprintf("%.02f", $totalCreditAmount);

            // integration of concerned leads with ids and amount to allow undos
            $is_field->leads_concerned = $leadsConcerned;
            
            if($amountCheck != $amountToCheck){
              $valid = false;
              $o["error"] = "Le montant d'avoirs attendu est incorrect";
            }
          }
					if ($valid) {
						$res = & $handle->query("SELECT is_fields FROM advertisers WHERE id = ".$advID, __FILE__, __LINE__);
						if ($handle->numrows($res, __FILE__, __LINE__) > 0) {
							list($is_fields) = $handle->fetch($res);
							if ($is_fields != "") $is_fields = mb_unserialize($is_fields);
							else $is_fields = array();
							
							array_push($is_fields, array("date" => time(), "type" => $is_type, "fields" => $is_field));
							if (count($is_fields) > 10) array_pop($is_fields);
							$o = $is_fields;
							$is_fields = serialize($is_fields);
							$handle->query("UPDATE advertisers SET is_fields = '".$handle->escape($is_fields)."' WHERE id = ".$advID, __FILE__, __LINE__);
						}
						else $o["error"] = "Erreur fatale : l'ID de l'annonceur spÃ©cifiÃ© n'existe pas";

                                                if(empty ($o["error"])){
                                                  $query = "UPDATE contacts SET invoice_status = " . __LEAD_INVOICE_STATUS_DISCHARGED__ .
                                                  " WHERE idAdvertiser = ".$advID.
                                                    " and credited_on > " .__BEGIN_TIME__ . " and credited_on < " . $lastDayNextMonth;
                                                  $res = & $handle->query($query, __FILE__, __LINE__);
                                                  
                                                }
                                                
					}
					else $o["error"] = "Un ou plusieurs champs sont manquants";
				}
			}
			else $o["error"] = "Un ou plusieurs paramÃ¨tres sont manquants";
			break;
			
		case "undo" :
			if (isset($_GET['advID'])) {
				$advID = (int)$_GET['advID'];
				$res = & $handle->query("SELECT is_fields FROM advertisers WHERE id = ".$advID , __FILE__, __LINE__);
				if ($handle->numrows($res, __FILE__, __LINE__) > 0) {
					list($is_fields) = $handle->fetch($res);
					if ($is_fields != "") $is_fields = mb_unserialize($is_fields);
					else $is_fields = array();
					
					if (!empty($is_fields)) {
                                          $istd = array_shift($is_fields);
                                          $leadsConcerned = $istd['fields']->leads_concerned;
                                            if(!empty ($leadsConcerned)){
                                              $query = "UPDATE contacts SET invoice_status = ". __LEAD_INVOICE_STATUS_CREDITED__ . " where";
                                              $a = 0;
                                              $queryWhereLeads = '';
                                              foreach($leadsConcerned as $leadId){
                                                if($a != 0 )
                                                  $queryWhereLeads .= " OR";
                                                $queryWhereLeads .= " id = ".$leadId;
                                                $a++;
                                              }
                                              $handle->query($query.$queryWhereLeads);
                                            }
                                          $o = $is_fields;
                                          $is_fields = serialize($is_fields);
                                          $handle->query("UPDATE advertisers SET is_fields = '".$handle->escape($is_fields)."' WHERE id = ".$advID , __FILE__, __LINE__);
					}
				}
			}
			else $o["error"] = "ID de l'annonceur non spÃ©cifiÃ©e";
			break;

    case "getAmount" :
			if (isset($_GET['advID'])) {
				$res = & $handle->query("SELECT sum(income) as totalCreditAmount FROM contacts WHERE idAdvertiser = ".(int)$_GET['advID'].
                                        " and credited_on > " .__BEGIN_TIME__ . " and credited_on < " . $lastDayNextMonth .
                                        " and invoice_status = " . __LEAD_INVOICE_STATUS_CREDITED__, __FILE__, __LINE__);
				if ($handle->numrows($res, __FILE__, __LINE__) > 0) {
					$is_fields = $handle->fetchAssoc($res);
					if ($is_fields['totalCreditAmount'] != 0) $is_fields['totalCreditAmount'] = sprintf("%.02f", $is_fields['totalCreditAmount']);
					else $is_fields['totalCreditAmount'] = 0;
					$o = $is_fields;
				}
			}
			else $o["error"] = "ID du fournisseur non spÃ©cifiÃ©e";
			break;
			
		default : break;
	}
}
else $o = "Aucune action n'a Ã©tÃ© spÃ©cifiÃ©e";

print json_encode($o);

exit();

?>
