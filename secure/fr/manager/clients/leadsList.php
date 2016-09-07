<?php

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$db = DBHandle::get_instance();

$user = new BOUser();

if(!$user->login())
{
	print "CustomerError" . __ERRORID_SEPARATOR__ . "Votre session a expirꥬ vous devez vous relogger." . __ERROR_SEPARATOR__ . __MAIN_SEPARATOR__;
	exit();
}
if (!$user->get_permissions()->has("m-comm--sm-customers","e")) {
  print "CustomerError" . __ERRORID_SEPARATOR__ . "Vous n'avez pas les droits ad겵ats pour rꢬiser cette op곡tion." . __ERROR_SEPARATOR__ . __MAIN_SEPARATOR__;
  exit();
}

require(ADMIN."statut.php");
define("__BEGIN_TIME__", mktime(0,0,0,1,1,2004));

$clientID = empty ($clientID) && $_GET['customerID'] ? $_GET['customerID'] : '';
if ($clientID != '') {
  if (preg_match('/^\d+$/',$clientID)) {
    $clientInfos = new CustomerUser($db,$clientID);
    if ($clientInfos === false) {
      $error = true;
      $errorstring .= "- Il n'existe pas de client ayant pour num&eacute;ro identifiant ".$clientID."<br />\n";
      exit;
    } else {
      $clientMail = $clientInfos->login;
    }
  } else {
    $error = true;
    $errorstring .= "- Le num&eacute;ro d'identifiant client est invalide<br />\n";
    exit;
  }
}else
  exit;

$everyLeads = isset($_GET["everyLeads"]) ? true : false;

$findType = isset($_GET['findType']) ? trim($_GET['findType']) : '';
$findText = isset($_GET['findText']) ? trim($_GET['findText']) : '';

$lastpage = isset($_GET['lastpage']) ? trim($_GET['lastpage']) : 1;
settype($lastpage, 'integer'); if ($lastpage < 1) $lastpage = 1;
$page     = isset($_GET['page'])     ? trim($_GET['page']) : 1;
settype($page, 'integer'); if ($page < 1) $page = 1;

$sort     = isset($_GET['sort'])     ? trim($_GET['sort']) : '';
$lastsort = isset($_GET['lastsort']) ? trim($_GET['lastsort']) : '';
$sortway  = isset($_GET['sortway'])  ? trim($_GET['sortway']) : '';

$errorstring = "";

$queryMain = "
	SELECT
		c.id, c.societe AS company, c.timestamp AS date, c.invoice_status, c.income, c.income_total, c.parent, c.reject_timestamp, c.credited_on,
		pfr.id AS pdt_id, pfr.name AS pdt_name,
		a.id AS adv_id, a.nom1 AS adv_name, a.category AS adv_category, a.is_fields AS adv_is_fields
	FROM contacts c
	LEFT JOIN products_fr pfr ON c.idProduct = pfr.id
	LEFT JOIN advertisers a ON c.idAdvertiser = a.id";

$queryWhere = array();
$queryOrder = "";
$queryLimit = "";
$queryWhereTimestampReject = array();

if (!$everyLeads)
	$queryWhere[] = "c.parent = 0";

// time interval
$yearS = isset($_GET['yearS']) ? (int)trim($_GET['yearS']) : date("Y");
$monthS = isset($_GET['monthS']) ? (int)trim($_GET['monthS']) : date("m");
$dayS = isset($_GET['dayS']) ? (int)trim($_GET['dayS']) : date("d");
$yearS2 = isset($_GET['yearS2']) ? (int)trim($_GET['yearS2']) : date("Y");
$monthS2 = isset($_GET['monthS2']) ? (int)trim($_GET['monthS2']) : date("m");
$dayS2 = isset($_GET['dayS2']) ? (int)trim($_GET['dayS2']) : date("d");
$yearE = isset($_GET['yearE']) ? (int)trim($_GET['yearE']) : date("Y");
$monthE = isset($_GET['monthE']) ? (int)trim($_GET['monthE']) : date("m");
$dayE = isset($_GET['dayE']) ? (int)trim($_GET['dayE']) : date("d");

$dateFilterType = isset($_GET["dateFilterType"]) ? ($_GET["dateFilterType"] == "interval" ? "interval" : "simple") : "simple";

if ($dateFilterType == "simple") {
	if ($yearS != 0) {
		if ($monthS == 0)	{ $dateStart = mktime(0,0,0,      1,    1,$yearS);	$dateEnd = mktime(0,0,0,        1,      1,$yearS+1); }
		elseif ($dayS == 0)	{ $dateStart = mktime(0,0,0,$monthS,    1,$yearS);	$dateEnd = mktime(0,0,0,$monthS+1,      1,$yearS  ); }
		else				{ $dateStart = mktime(0,0,0,$monthS,$dayS,$yearS);	$dateEnd = mktime(0,0,0,$monthS  ,$dayS+1,$yearS  ); }
	}
}
elseif ($dateFilterType == "interval") {
	if ($yearS2 != 0 && $yearE != 0) {
		if ($monthS2 == 0)   { $dateStart = mktime(0,0,0,       1,     1,$yearS2); }
		elseif ($dayS2 == 0) { $dateStart = mktime(0,0,0,$monthS2,     1,$yearS2); }
		else                 { $dateStart = mktime(0,0,0,$monthS2,$dayS2,$yearS2); }
		
		if ($monthE == 0)   { $dateEnd = mktime(0,0,0,      1,      1,$yearE); }
		elseif ($dayE == 0) { $dateEnd = mktime(0,0,0,$monthE,      1,$yearE); }
		else                { $dateEnd = mktime(0,0,0,$monthE,$dayE+1,$yearE); }
	}
}

if (!isset($dateStart) || !isset($dateEnd)) {
	$dateStart = __BEGIN_TIME__;
	$dateEnd = mktime(0,0,0)+86400;
}
$dateStart = __BEGIN_TIME__;
$queryWhere[] = "c.email = '".$db->escape($clientMail)."' and ((c.timestamp >= ".$dateStart." AND c.timestamp < ".$dateEnd.") OR (c.reject_timestamp >= ".$dateStart." AND c.reject_timestamp < ".$dateEnd."))";

if ($findText != '') {
	switch($findType) {
		case '0': break;
		case '1': // product id
			if (preg_match("/^[1-9]{1}[0-9]{0,8}$/", $findText))
				$queryWhere[] = "pfr.id = '".$findText."'";
			else
				$errorstring .= "- L'identifiant produit saisi est invalide<br/>\n";
			break;
			
		case '2': // advertiser name
			$queryWhere[] = "a.ref_name = '".$db->escape(Utils::toDashAz09($findText))."'";
			break;
			
		case '3': // lead id
			if (preg_match("/^[1-9]{1}[0-9]{0,8}$/", $findText))
				$queryWhere[] = "c.id = '".$findText."'";
			else
				$errorstring .= "- L'identifiant lead saisi est invalide<br/>\n";
			break;
		
		case '4': // lead email
			$queryWhere[] = "c.email = '".$db->escape($findText)."'";
			break;
		
		default :
			$errorstring .= "- Ce type de recherche n'existe pas.<br/>\n";
	}
}

if (!empty($queryWhere)) {
  $queryWhere = " WHERE ".implode(" AND ", $queryWhere);
}
else
  $queryWhere = "";

// page count and position
$res = $db->query("
	SELECT COUNT(c.id)
	FROM contacts c
	LEFT JOIN products_fr pfr ON c.idProduct = pfr.id
	LEFT JOIN advertisers a ON c.idAdvertiser = a.id
	".$queryWhere, __FILE__, __LINE__);
list($nbLeads) = $db->fetch($res);

define('NB', 100);

if (($page-1) * NB >= $nbLeads) $page = ($nbLeads - $nbLeads%NB) / NB + 1;
if (($lastpage-1) * NB >= $nbLeads) $lastpage = ($nbLeads - $nbLeads%NB) / NB + 1;

// sort
if ($sort == $lastsort && $sort != '') {
	if ($lastpage == $page) $sortway = ($sortway == 'ASC' ? 'DESC' : 'ASC');
	else $sortway = ($sortway == 'ASC' ? 'ASC' : 'DESC');
}
else $sortway = 'ASC';

switch (strtolower($sort)) {
	case 'date': $queryOrder .= "c.timestamp ".($sortway == 'ASC' ? 'DESC' : 'ASC').", c.id"; break;
	case 'id': $queryOrder .= "c.id ".$sortway; break;
	case 'company': $queryOrder .= "c.societe ".$sortway.", c.timestamp DESC, c.id"; break;
	case 'pdt_name': $queryOrder .= "pfr.name ".$sortway.", c.timestamp DESC, c.id"; break;
	case 'adv_name': $queryOrder .= "a.nom1 ".$sortway.", c.timestamp DESC, c.id"; break;
	case 'adv_category': $queryOrder .= "a.category ".$sortway.", c.timestamp DESC, c.id"; break;
	case 'lead_income': $queryOrder .= "c.income ".$sortway.", c.id"; break;
	case 'lead_income_total': $queryOrder .= "c.income_total ".$sortway.", c.timestamp DESC, c.id"; break;
	default : $queryOrder .= "c.timestamp ".($sortway == 'ASC' ? 'DESC' : 'ASC' ).", c.id"; $sort = 'date'; break;
}

$lastsort = $sort;
$lastpage = $page;

if (!empty($queryOrder))
	$queryOrder = " ORDER BY ".$queryOrder;

// getting the leads
$leadsStats = array(
	"primaryCount" => 0,
	"secondaryCount" => 0,
	"invoicedCount" => 0,
        "creditedCount" => 0,
	"CA" => 0
);
//var_dump($queryMain.$queryWhere.$queryOrder.$queryLimit);
$res = $db->query($queryMain.$queryWhere.$queryOrder.$queryLimit, __FILE__, __LINE__);
$leadList = array();
$leadIdList = array();
while($lead = $db->fetchAssoc($res)) {
  
	if ($lead["invoice_status"] & __LEAD_CHARGEABLE__ || $lead["invoice_status"] & __LEAD_CHARGED__ ){
            $leadsStats["invoicedCount"]++;
            $leadsStats["CA"] += $lead["income"];
              
        }elseif ($lead["invoice_status"] & __LEAD_CREDITED__){
            if( $lead['credited_on'] >=  $dateStart && $lead['credited_on'] <=  $dateEnd)
              $leadsStats["creditedCount"]++;
            if( $lead['date'] >=  $dateStart && $lead['date'] <=  $dateEnd)
              $leadsStats["invoicedCount"]++;

            if( $lead['date'] >=  $dateStart && $lead['date'] <=  $dateEnd){
              $leadsStats["CA"] += $lead["income"];
            }
            if( $lead['reject_timestamp'] >=  $dateStart && $lead['reject_timestamp'] <=  $dateEnd){
              $leadsStats["CA"] -= $lead["income"];
            }
        }elseif($lead["invoice_status"] & __LEAD_DISCHARGED__)
            if( $lead['date'] >=  $dateStart && $lead['date'] <=  $dateEnd){
                $leadsStats["CA"] += $lead["income"];
                $leadsStats["invoicedCount"]++;
            }
        if ($everyLeads) {
              if( $lead['date'] >=  $dateStart && $lead['date'] <=  $dateEnd){
                  if ($lead["parent"] == 0)
                          $leadsStats["primaryCount"]++;
                  else
                          $leadsStats["secondaryCount"]++;
              }
          }
          else {
            if( $lead['date'] >=  $dateStart && $lead['date'] <=  $dateEnd)
                  $leadsStats["primaryCount"]++;
                  $leadIdList[] = $lead["id"];
          }
        
	$leadList[] = $lead;
}

if (!$everyLeads) {
	// getting secondary leads
	if (!empty($leadIdList)) {
		$res = $db->query($queryMain." WHERE c.parent IN (".implode(",",$leadIdList).") and c.invoice_status <> ".__LEAD_INVOICE_STATUS_CREDITED__);
		$lead2List = array();
		while($lead2 = $db->fetchAssoc($res)) {
			if ($lead2["invoice_status"] & __LEAD_CHARGEABLE__ || $lead2["invoice_status"] & __LEAD_CHARGED__){
                            $leadsStats["invoicedCount"]++;
                            $leadsStats["CA"] += $lead2["income"];
                        }elseif ($lead2["invoice_status"] & __LEAD_CREDITED__){
                            if( $lead['credited_on'] >=  $dateStart && $lead['credited_on'] <=  $dateEnd)
                              $leadsStats["creditedCount"]++;
                            if( $lead['date'] >=  $dateStart && $lead['date'] <=  $dateEnd)
                              $leadsStats["invoicedCount"]++;

                            if( $lead2['date'] >=  $dateStart && $lead2['date'] <=  $dateEnd){
                              $leadsStats["CA"] += $lead2["income"];
                            }
                            if( $lead2['reject_timestamp'] >=  $dateStart && $lead2['reject_timestamp'] <=  $dateEnd){
                              $leadsStats["CA"] -= $lead2["income"];
                            }
                        }
                        
			$lead2List[$lead2["parent"]][] = $lead2;
                        if( $lead2['date'] >=  $dateStart && $lead2['date'] <=  $dateEnd)
                          $leadsStats["secondaryCount"]++;
		}
	}
	// processing primary leads using secondary ones
	foreach($leadList as &$lead) {
		$lead["clc"] = isset($lead2List[$lead["id"]]) ? count($lead2List[$lead["id"]]) : 0; // children lead count
	}
	unset($lead);
}

// only show leads of the current page
$leadList = array_slice($leadList, ($page-1)*NB, NB);
// only show current leads, erase credited leads in the table (used for stats above)?
foreach($leadList as $k => $lead)
  if($lead['date'] < $dateStart)
    unset($leadList[$k]);

?>
<script type="text/javascript" src="../contacts/leads.js"></script>
<style>
  #lc_ld { display: none }
#lc_pdt_sheet { display: none; width: 800px }
.label { float: left; width: 248px; height: 20px; font-weight: bold }
.layer { position: absolute; left: 80px; top: 160px; z-index: 10; padding: 5px; border: 5px solid #dddddd; background: #ffffff; cursor: move }
.layer .close { position: absolute; right: 15px; top: 17px; z-index: 11; width: 18px; height: 18px; background: url(../ressources/images/close-cross-18x18-orange.png) no-repeat; cursor: pointer }
.value { float: left; margin: 0 20px 0 0 }
.value .lightgray { color: #cccccc }
</style>
<br />
<div class="bg">
  <div class="block">
    <div class="title">Historique leads</div>
  </div>
<br/>
<input type="button" class="button" value="Nouveau Lead" onClick="document.location.href='../contacts/lead-create.php?idClient=<?php echo $clientInfos->login ?>'" />
  <br />
<div class="lead-section">
	<?php if (!empty($errorstring)) { ?>
		<div style="color: #FF0000"><?php echo $errorstring ?></div>
	<?php } ?>
<!--	<div class="block">
		<div class="title">Période étudiée : du <?php echo date("d/m/Y à H:i", $dateStart) ?> au <?php echo date("d/m/Y à H:i", $dateEnd) ?></div>
		<div class="text">
			<div class="label">Nombre de leads primaires total :</div><div class="value"><?php echo $leadsStats["primaryCount"] ?></div><div class="zero"></div>
			<div class="label">Nombre de leads secondaires total :</div><div class="value"><?php echo $leadsStats["secondaryCount"] ?></div><div class="zero"></div>
			<div class="label">Nombre de leads à facturer :</div><div class="value"><?php echo $leadsStats["invoicedCount"]-$leadsStats["creditedCount"] ?></div><div class="zero"></div>
                        <div class="label">Rejets précédents imputés sur la période :</div><div class="value"><?php echo $leadsStats["creditedCount"] ?></div><div class="zero"></div>
			<div class="label">CA généré :</div><div class="value"><?php echo sprintf("%.02f", $leadsStats["CA"]) ?>€</div><div class="zero"></div>
		</div>
	</div>
	<br/>
	<div class="block">
		<div class="title">Options de filtrage</div>
		<div class="text">
			<form action="requests_extract.php" method="post" style="float: right">
				<div>
					<input type="hidden" name="findType" value="<? echo $findType ?>"/>
                    <input type="hidden" name="findText" value="<? echo $findText ?>"/>
                    <input type="hidden" name="DateBegin" value="<? echo date("d/m/Y", $dateStart) ?>" />
					<input type="hidden" name="DateEnd" value="<? echo date("d/m/Y", $dateEnd) ?>" />
					<input type="submit" value="Télécharger l'extract en xls" />
				</div>
			</form>
			<form name="LeadList" action="leads.php" method="get">
				<input type="hidden" name="page" value="<?php echo $page ?>"/>
				<input type="hidden" name="lastpage" value="<?php echo $lastpage ?>"/>
				<input type="hidden" name="sort" value="<?php echo $sort ?>"/>
				<input type="hidden" name="lastsort" value="<?php echo $lastsort ?>"/>
				<input type="hidden" name="sortway" value="<?php echo $sortway ?>"/>
				<input type="hidden" name="dateFilterType" value="<?php echo $dateFilterType ?>"/>
				<input type="checkbox" name="everyLeads" <?php if ($everyLeads) { ?>checked="checked"<?php } ?>/> Afficher tous les leads (non hiérarchisé)<br/>
				<br/>
				<div id="DateFilter">
					<fieldset class="date-picker">
						<legend>Interval prédéfini :</legend>
						Année : <select name="yearS" id="YearID" onchange="FillMonthOptions(this.id, 'MonthID', 'DayID');"></select>
						Mois : <select name="monthS" id="MonthID" onchange="FillDayOptions('YearID', this.id, 'DayID');"></select>
						Jour : <select name="dayS" id="DayID"></select>
						<br/>
					</fieldset>
					<input type="button" value="Choisir un interval de temps" onclick="ShowDateIntervalSection()">
					<div class="zero"></div>
				</div>
				<div id="DateIntervalFilter">
					<fieldset class="date-picker">
						<legend>Date de début :</legend>
						Année : <select name="yearS2" id="YearSID" onchange="FillMonthOptions2(this.id, 'MonthSID', 'DaySID');"></select>
						Mois : <select name="monthS2" id="MonthSID" onchange="FillDayOptions2('YearSID', this.id, 'DaySID');"></select>
						Jour : <select name="dayS2" id="DaySID"></select>
					</fieldset>
					<div class="zero"></div>
					<fieldset class="date-picker">
						<legend>Date de Fin :</legend>
						Année : <select name="yearE" id="YearEID" onchange="FillMonthOptions2(this.id, 'MonthEID', 'DayEID');"></select>
						Mois : <select name="monthE" id="MonthEID" onchange="FillDayOptions2('YearEID', this.id, 'DayEID');"></select>
						Jour : <select name="dayE" id="DayEID"></select>
					</fieldset>
					<input type="button" value="Choisir une date simple" onclick="ShowDateSection()">
					<div class="zero"></div>
				</div>
				<br/>
				<div>
					Rechercher :
					<select name="findType">
						<option value="0">-</option>
						<option value="1"<?php if($findType==1) { ?> selected="selected"<?php } ?>>un ID produit</option>
						<option value="2"<?php if($findType==2) { ?> selected="selected"<?php } ?>>un partenaire</option>
						<option value="3"<?php if($findType==3) { ?> selected="selected"<?php } ?>>un ID lead</option>
						<option value="4"<?php if($findType==4) { ?> selected="selected"<?php } ?>>un email lead</option>
					</select>
					<input type="text" name="findText" value="<?php echo $findText ?>"/>
					<input type="submit" value="OK">
				</div>
				<script type="text/javascript"><?php echo ($dateFilterType == "interval") ? "ShowDateIntervalSection();" : "ShowDateSection();" ?></script>
			</form>
		</div>
	</div>-->
  <?php if ($nbLeads > NB) { $lastpage = ceil($nbLeads/NB) ?>
  <!--	<div class="listing" style="float: right">
                  <span style="visibility: <?php echo $page > 2 ? 'visible' : 'hidden' ?>"><a href="javascript: gotoPage(1)">&lt;&lt;</a></span>
                  <span style="visibility: <?php echo $page > 1 ? 'visible' : 'hidden'?>"><a href="javascript: gotoPage(<?php echo ($page-1)  ?>)">&lt;</a> ... |</span>
                  <span style="visibility: <?php echo $page > 1 ? 'visible' : 'hidden'?>"><a href="javascript: gotoPage(<?php echo ($page-1) ?>)"><?php echo ($page-1)  ?></a> |</span>
                  <span class="listing-current"><?php echo $page ?></span>
                  <span style="visibility: <?php echo $page < $lastpage ? 'visible' : 'hidden'?>">| <a href="javascript: gotoPage(<?php echo ($page+1) ?>)"><?php echo ($page+1)  ?></a></span>
                  <span style="visibility: <?php echo $page < $lastpage ? 'visible' : 'hidden'?>">| ... <a href="javascript: gotoPage(<?php echo ($page+1)  ?>)">&gt;</a></span>
                  <span style="visibility: <?php echo $page < $lastpage-1 ? 'visible' : 'hidden'?>"><a href="javascript: gotoPage(<?php echo $lastpage  ?>)">&gt;&gt;</a></span>
          </div>-->
  <?php } ?>
	<br />
	<table id="lc_lead_history" class="item-list" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				<th class="tree"></th>
				<th class="date">Date/Heure</th><!--<a href="javascript: LeadSort('date')">-->
				<th class="id">ID</th>
				<th>Société</th>
				<th>Nom produit</th>
				<th>ID Produit</th>
				<th>Nom partenaire</th>
				<th>Type partenaire</th>
				<th>Etat lead</th>
				<th>Revenu lead</th>
				<th>Nb lead 2</th>
				<th>Revenu total</th>
			</tr>
		</thead>
		<tbody>
	<?php
        if($leadList){
              foreach ($leadList as $lead) { ?>
			<tr<?php if($lead["clc"] > 0 ) { ?> class="scat1"<?php } ?>>
				<td class="tree"></td>
				<td class="date"><?php echo date("d/m/Y à H:i", $lead["date"]) ?></td>
				<td class="id"><?php echo $lead["id"] ?></td>
				<td><?php echo $lead["company"] ?></td>
				<td><?php echo $lead["pdt_name"] ?></td>
				<td><?php echo $lead["pdt_id"] ?></td>
				<td><?php echo $lead["adv_name"] ?></td>
				<td><?php echo $adv_cat_list[$lead["adv_category"]]["name"] ?></td>
                                <td><?php echo $lead_invoice_status_list[$lead["invoice_status"]].getCreditMonth($lead) ?></td>
				<td><?php echo sprintf("%.02f", $lead["income"]) ?>€</td>
				<td><?php echo $lead["clc"] ?></td>
				<td><?php echo sprintf("%.02f", $lead["income_total"]) ?>€</td>
			</tr>
		<?php if ($lead["clc"] > 0) foreach($lead2List[$lead["id"]] as $lead2) { ?>
			<tr class="selem1">
				<td class="tree"></td>
				<td class="date"><?php echo date("d/m/Y à H:i", $lead2["date"]) ?></td>
				<td class="id"><?php echo $lead2["id"] ?></td>
				<td><?php echo $lead2["company"] ?></td>
                                <td><?php echo $lead2["pdt_name"] ?></td>
				<td><?php echo $lead2["pdt_id"] ?></td>
				<td><?php echo $lead2["adv_name"] ?></td>
				<td><?php echo $adv_cat_list[$lead2["adv_category"]]["name"] ?></td>
				<td><?php echo $lead_invoice_status_list[$lead2["invoice_status"]].getCreditMonth($lead2) ?></td>
				<td><?php echo sprintf("%.02f", $lead2["income"]) ?>€</td>
				<td>-</td>
				<td>-</td>
			</tr>
		<?php } ?>
	<?php }
        }else{
          ?>
               <tr><td colspan="12">Il n'existe aucun contact pour ce client.</td></tr>
          <?php
        }
        ?>
		</tbody>
	</table>
  <?php if ($nbLeads > NB) { ?>
  <!--	<div class="listing">
                  <span style="visibility: <?php echo $page > 2 ? 'visible' : 'hidden' ?>"><a href="javascript: gotoPage(1)">&lt;&lt;</a></span>
                  <span style="visibility: <?php echo $page > 1 ? 'visible' : 'hidden'?>"><a href="javascript: gotoPage(<?php echo ($page-1)  ?>)">&lt;</a> ... |</span>
                  <span style="visibility: <?php echo $page > 1 ? 'visible' : 'hidden'?>"><a href="javascript: gotoPage(<?php echo ($page-1) ?>)"><?php echo ($page-1)  ?></a> |</span>
                  <span class="listing-current"><?php echo $page ?></span>
                  <span style="visibility: <?php echo $page < $lastpage ? 'visible' : 'hidden'?>">| <a href="javascript: gotoPage(<?php echo ($page+1) ?>)"><?php echo ($page+1)  ?></a></span>
                  <span style="visibility: <?php echo $page < $lastpage ? 'visible' : 'hidden'?>">| ... <a href="javascript: gotoPage(<?php echo ($page+1)  ?>)">&gt;</a></span>
                  <span style="visibility: <?php echo $page < $lastpage-1 ? 'visible' : 'hidden'?>"><a href="javascript: gotoPage(<?php echo $lastpage  ?>)">&gt;&gt;</a></span>
          </div>-->
  <?php } ?>
  </div>
</div>
  <div id="lc_ld" class="layer">
    <img class="close" src="../ressources/images/empty.gif" alt=""/>
    <div class="title">Aper&ccedil;u du lead n&deg;<span id="lc_ld_id"></span> du <span id="lc_ld_date"></span></div>
    <div class="text">
      <div class="label">Message facultatif :</div>
      <div class="value"><span id="lc_ld_precisions"></span></div>
      <div class="zero"></div>
      <br/>
      <div class="label">Nom du produit :</div>
      <div class="value"><span id="lc_ld_pdt_name"></span></div>
      <div class="zero"></div>
      <div class="label">Famille du produit :</div>
      <div class="value"><span id="lc_ld_cat_name"></span></div>
      <div class="zero"></div>
      <div class="label"><span id="lc_ld_adv_category_name"></span> :</div>
      <div class="value"><span id="lc_ld_adv_name"></span> (<span id="lc_ld_adv_id"></span>)</div>
      <div class="zero"></div>
      <br/>
      <div class="label">Nom :</div>
      <div class="value"><span id="lc_ld_nom"></span></div>
      <div class="zero"></div>
      <div class="label">Pr&eacute;nom :</div>
      <div class="value"><span id="lc_ld_prenom"></span></div>
      <div class="zero"></div>
      <div class="label">Fonction :</div>
      <div class="value"><span id="lc_ld_fonction"></span></div>
      <div class="zero"></div>
      <div class="label">Email :</div>
      <div class="value"><span id="lc_ld_email"></span></div>
      <div class="zero"></div>
      <div class="label">Site Internet :</div>
      <div class="value"><span id="lc_ld_url"></span></div>
      <div class="zero"></div>
      <div class="label">T&eacute;l&eacute;phone :</div>
      <div class="value"><span id="lc_ld_tel"></span></div>
      <div class="zero"></div>
      <div class="label">Fax :</div>
      <div class="value"><span id="lc_ld_fax"></span></div>
      <div class="zero"></div>
      <div class="label">Nom de la soci&eacute;t&eacute; :</div>
      <div class="value"><span id="lc_ld_societe"></span></div>
      <div class="zero"></div>
      <div class="label">Nb de salari&eacute; :</div>
      <div class="value"><span id="lc_ld_salaries"></span></div>
      <div class="zero"></div>
      <div class="label">Secteur d'activit&eacute; :</div>
      <div class="value"><span id="lc_ld_secteur"></span></div>
      <div class="zero"></div>
      <div class="label">Code NAF :</div>
      <div class="value"><span id="lc_ld_naf"></span></div>
      <div class="zero"></div>
      <div class="label">SIRET :</div>
      <div class="value"><span id="lc_ld_siret"></span></div>
      <div class="zero"></div>
      <div class="label">Adresse :</div>
      <div class="value"><span id="lc_ld_adresse"></span></div>
      <div class="zero"></div>
      <div class="label">Code Postal :</div>
      <div class="value"><span id="lc_ld_cp"></span></div>
      <div class="zero"></div>
      <div class="label">Ville :</div>
      <div class="value"><span id="lc_ld_ville"></span></div>
      <div class="zero"></div>
      <div class="label">Pays :</div>
      <div class="value"><span id="lc_ld_pays"></span></div>
      <div class="zero"></div>
      <div id="lc_ld_customFields"></div>
    </div>
  </div>
<script type="text/javascript">

  
  $("#lc_ld").draggable().find("img.close").click(function(){ $("#lc_ld").hide(); });

  $("#lc_lead_history tbody").find("td:gt(0)").click(function(){
               var lead_id = $(this).closest("tr").find("td.id").html();
                $.ajax({
                  type: "POST",
                  url: "../contacts/AJAX_interface.php",
                  data: {"actions":[{"action":"get_lead_infos","leadId": lead_id}]},
                  dataType: "json",
                  error: function (XMLHttpRequest, textStatus, errorThrown) {},
                  success: function (data, textStatus) {
                    if (!data.error) {
                      var lead = data.data.lead_detail;
                      for (var fieldName in lead)
                        if (!$.isArray(lead[fieldName]))
                          $("#lc_ld_"+fieldName).html(lead[fieldName]);
                      $("#lc_ld").show();

                      var html = "";
                      for (fieldName in lead.customFields) {
                        html += "<div class=\"label\">"+fieldName+" :</div>"+
                          "<div class=\"value\">"+lead.customFields[fieldName]+"</div>"+
                                "<div class=\"zero\"></div>";
                      }
                      $("#lc_ld_customFields").html(html);
                    }
                  }
                });
              });
//	FillYearOptions('YearID', 'MonthID', 'DayID');
//	SetDateOptions('YearID', 'MonthID', 'DayID', '<?php echo $yearS ?>','<?php echo $monthS ?>','<?php echo $dayS ?>');
//	FillYearOptions2('YearSID', 'MonthSID', 'DaySID');
//	FillYearOptions2('YearEID', 'MonthEID', 'DayEID');
//	SetDateOptions('YearSID', 'MonthSID', 'DaySID', '<?php echo $yearS2 ?>','<?php echo $monthS2 ?>','<?php echo $dayS2 ?>');
//	SetDateOptions('YearEID', 'MonthEID', 'DayEID', '<?php echo $yearE ?>','<?php echo $monthE ?>','<?php echo $dayE ?>');
</script>
<?php //require(ADMIN."tail.php") ?>