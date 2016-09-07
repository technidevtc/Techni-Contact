<?php

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$db = DBHandle::get_instance();

$id = isset($_GET['id']) ? (int)trim($_GET['id']) : 0;
if ($id == 0) {
	header("Location: ".ADMIN_URL."contacts/leads.php");
	exit();
}

$title = $navBar = "Détail de la demande de contact n°".$id;
require(ADMIN."head.php");
require(ADMIN."statut.php");

$res = $db->query("
	SELECT
		c.id, c.idFamily as cat_id, c.timestamp AS date, c.nom, c.prenom, c.fonction, c.societe, c.salaries, c.secteur, c.naf, c.siret, c.adresse, c.cadresse,
		c.cp, c.ville, c.pays, c.tel, c.fax, c.email, c.url, c.precisions, c.type, c.campaignID,
		c.customFields, c.invoice_status, c.income, c.income_total, c.parent, c.reject_timestamp, c.credited_on,
		pfr.name AS pdt_name, pfr.fastdesc AS pdt_fastdesc, pfr.id AS pdt_id, pfr.ref_name AS pdt_ref_name,
		ffr.name AS cat_name, ffr.ref_name as cat_ref_name,
		a.id AS adv_id, a.nom1 AS adv_name, a.is_fields AS adv_is_fields
	FROM contacts c
	LEFT JOIN products_fr pfr ON c.idProduct = pfr.id
	LEFT JOIN families_fr ffr ON c.idFamily = ffr.id
	LEFT JOIN advertisers a ON c.idAdvertiser = a.id
	WHERE c.id = ".$id, __FILE__, __LINE__);
$lead = $db->fetchAssoc($res);

$customFields = mb_unserialize($lead['customFields']);
if (empty($customFields))
	$customFields = array();

// single lead cost
if ($lead["adv_is_fields"] != "") $lead["adv_is_fields"] = mb_unserialize($lead["adv_is_fields"]);
else $lead["adv_is_fields"] = array();

if (!empty($lead["adv_is_fields"]))
	$is_fields = $lead["adv_is_fields"][0];

if (isset($_POST["invoice_status"])) {
	$invoice_status = (int)$_POST["invoice_status"];
	if (isset($lead_invoice_status_list[$invoice_status])) {
		if ($invoice_status == __LEAD_INVOICE_STATUS_CHARGED__) {
			switch($is_fields["type"]) {
				case "lead": $income = $is_fields["fields"]->lead_unit_cost; break;
				case "budget": $income = $is_fields["fields"]->budget_unit_cost; break;
				case "forfeit": $income = 0.00; break;
				default: $income = 0.00; break;
			}
		}
		else
			$income = 0.00;
		
		if ($lead["parent"] == 0) {
			$income_total = $lead["income_total"] - $lead["income"] + $income;
			$db->query("UPDATE contacts SET invoice_status = ".$invoice_status.", income = '".$income."', income_total = '".$income_total."' WHERE id = ".$id, __FILE__, __LINE__);
			$lead["invoice_status"] = $invoice_status;
			$lead["income"] = $income;
			$lead["income_total"] = $income_total;
		}
		else {
			$db->query("UPDATE contacts SET invoice_status = ".$invoice_status.", income = '".$income."' WHERE id = ".$id, __FILE__, __LINE__);
			$res = $db->query("SELECT income_total FROM contacts WHERE id = ".$lead["parent"], __FILE__, __LINE__);
			if ($db->numrows($res, __FILE__, __LINE__) == 1) {
				list($income_total) = $db->fetch($res);
				$income_total = $income_total - $lead["income"] + $income;
				$db->query("UPDATE contacts SET income_total = '".$income_total."' WHERE id = ".$lead["parent"], __FILE__, __LINE__);
			}
			$lead["invoice_status"] = $invoice_status;
			$lead["income"] = $income;
		}
	}
}

$fo_pdt_url = URL."produits/".$lead["cat_id"]."-".$lead["pdt_id"]."-".$lead["pdt_ref_name"].".html";
$fo_cat_url = URL."familles/".$lead["cat_ref_name"].".html";
$bo_pdt_url = ADMIN_URL."products/edit.php?id=".$lead["pdt_id"];
//$bo_cat_url = $lead["cat_name"];
$bo_adv_url = ADMIN_URL."advertisers/edit.php?id=".$lead["adv_id"];
?>

<link rel="stylesheet" type="text/css" href="leads.css" />
<form name="charge_form" method="post" action="">
<div class="lead-section">
	<a href="leads.php">&lt;&lt; Retourner à la liste des demandes de contact</a><br/>
	<br/>
	<div class="block">
		<div class="title">Détails de la demande de contact n°<?php echo $id ?> du <?php echo date("d/m/Y à H:i", $lead["date"]) ?></div>
		<div class="text">
			<div class="label">Nom du produit :</div>
			<div class="value">
			<?php if(!empty($lead["pdt_name"])) { ?>
				<a href="<?php echo $bo_pdt_url ?>" target="_blank"><?php echo $lead["pdt_name"] ?></a>
				<a href="<?php echo $fo_pdt_url ?>" target="_blank"><img src="../ressources/icons/monitor_go.png" alt="" class="view-fo" title="Voir la fiche en ligne"/></a>
			<?php } else { ?>
				<i class="lightgray">lead secondaire</i>
			<?php } ?>
			</div>
			<div class="zero"></div>
			<div class="label">Famille du produit :</div>
			<div class="value">
				<?php echo $lead["cat_name"] ?>
				<a href="<?php echo $fo_cat_url ?>" target="_blank"><img src="../ressources/icons/monitor_go.png" alt="" class="view-fo" title="Voir la famille du produit en ligne"></a>
			</div>
			<div class="zero"></div>
			<div class="label">Description rapide du produit :</div>
			<div class="value"><?php if(!empty($lead["pdt_fastdesc"])) { echo $lead["pdt_fastdesc"]; } else { ?><i class="lightgray">lead secondaire</i><?php } ?></div>
			<div class="zero"></div>
			<div class="label">Annonceur :</div>
			<div class="value"><a href="<?php echo $bo_adv_url ?>" target="_blank"><?php echo $lead["adv_name"] ?> (<?php echo $lead["adv_id"] ?>)</a></div>
			<div class="zero"></div>
			<div class="label">Type :</div>
			<div class="value"><?php if($lead["parent"] == 0) { ?>primaire<?php } else { ?>secondaire (primaire = <a href="<?php echo ADMIN_URL."contacts/lead-detail.php?id=".$lead["parent"] ?>" target="_blank"><?php echo $lead["parent"] ?></a>)<?php } ?></div>
			<div class="zero"></div>
			<div class="label">Etat de facturation :</div>
			<div class="value">
				<input type="hidden" name="invoice_status"/>
				<?php echo $lead_invoice_status_list[$lead["invoice_status"]].getCreditMonth($lead) ?>
			<?php if ($lead["invoice_status"] & __LEAD_CHARGED__) { ?>
				- <a href="#" onclick="document.charge_form.invoice_status.value=<?php echo __LEAD_INVOICE_STATUS_NOT_CHARGED__ ?>; document.charge_form.submit(); return false;">ne plus facturer</a>
			<?php } else { ?>
				- <a href="#" onclick="document.charge_form.invoice_status.value=<?php echo __LEAD_INVOICE_STATUS_CHARGED__ ?>; document.charge_form.submit(); return false;">facturer</a>
			<?php } ?>
			</div>
			<div class="zero"></div>
			<div class="label">Revenu :</div>
			<div class="value"><?php echo sprintf("%.02f", $lead["income"]) ?>€ <?php if($is_fields["type"] == "forfeit") { ?>(forfait)<?php } ?></div>
			<div class="zero"></div>
			<div class="label">Message facultatif :</div>
			<div class="value"><?php echo (!empty($lead["precisions"]) ? $lead["precisions"] : "N/C") ?></div>
			<div class="zero"></div>
		</div>
	</div>
	<br/>
	<br/>
	<div class="block">
		<div class="title">Informations personnelles de l'internaute</div>
		<div class="text">
			<div class="label">Nom :</div>
			<div class="value"><?php echo $lead["nom"] ?></div>
			<div class="zero"></div>
			<div class="label">Prénom :</div>
			<div class="value"><?php echo ucwords(strtolower($lead["prenom"])) ?></div>
			<div class="zero"></div>
			<div class="label">Fonction :</div>
			<div class="value"><?php echo ucwords(strtolower($lead["fonction"])) ?></div>
			<div class="zero"></div>
			<div class="label">Email :</div> 
			<div class="value"><?php echo strtolower($lead["email"]) ?></div>
			<div class="zero"></div>
			<div class="label">Site Internet :</div>
			<div class="value"><?php echo (!empty($lead["url"]) ? $lead["url"] : "N/C") ?></div>
			<div class="zero"></div>
			<div class="label">Téléphone :  </div>
                        <div class="value"><a href="tel:<?php echo preg_replace('/[^0-9\+.]?/', '', $lead["tel"]) ?>"><?php echo preg_replace('/[^0-9\+.]?/', '', $lead["tel"]) ?> <img src="../ressources/icons/telephone.png" alt="tel"  style="vertical-align:middle" /></a></div>
			<div class="zero"></div>
			<div class="label">Fax :</div>
			<div class="value"><?php echo (!empty($lead["fax"]) ? $lead["fax"] : "N/C") ?></div>
			<div class="zero"></div>
		</div>
	</div>
	<br/>
	<br/>
	<div class="block">
		<div class="title">Informations sur son entreprise</div>
		<div class="text">
			<div class="label">Nom de la société :</div>
			<div class="value"><?php echo $lead["societe"] ?></div>
			<div class="zero"></div>
			<div class="label">Nb de salarié :</div>
			<div class="value"><?php echo (!empty($lead["salaries"]) ? $lead["salaries"] : "N/C") ?></div>
			<div class="zero"></div>
			<div class="label">Secteur d'activité :</div>
			<div class="value"><?php echo ucwords(strtolower($lead["secteur"])) ?></div>
			<div class="zero"></div>
			<div class="label">Code NAF :</div>
			<div class="value"><?php echo (!empty($lead["naf"]) ? $lead["naf"] : "N/C") ?> </div>
			<div class="zero"></div>
			<div class="label">SIRET :</div>
			<div class="value"><?php echo (!empty($lead["siret"]) ? $lead["siret"] : "N/C") ?></div>
			<div class="zero"></div>
			<div class="label">Adresse :</div>
			<div class="value"><?php echo ucwords(strtolower($lead["adresse"])) ?><?php if(!empty($lead["cadresse"])) { ?><br/><?php echo ucwords(strtolower($lead["cadresse"])) ?><?php } ?></div>
			<div class="zero"></div>
			<div class="label">Code Postal :</div>
			<div class="value"><?php echo $lead["cp"] ?></div>
			<div class="zero"></div>
			<div class="label">Ville :</div>
			<div class="value"><?php echo $lead["ville"] ?></div>
			<div class="zero"></div>
			<div class="label">Pays :</div>
			<div class="value"><?php echo $lead["pays"] ?></div>
			<div class="zero"></div>
		<?php foreach($customFields as $fieldName => $fieldData) { ?>
			<div class="label"><?php echo $fieldName ?> :</div>
			<div class="value"><?php echo to_entities($fieldData) ?></div>
			<div class="zero"></div>
		<?php } ?>
		</div>
	</div>
</div>
</form>

<?php require(ADMIN."tail.php") ?>

