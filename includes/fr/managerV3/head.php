<?php

/*================================================================/

	Techni-Contact V2 - MD2I SAS
	http://www.techni-contact.com

	Auteur : Hook Network SARL - http://www.hook-network.com
	Date de création : 20 décembre 2004

	Mises à jour :
		31 mai 2005 : + nouveau processus d'identification
		29 octobre 2007 : Nettoyage code + ajout de style css

	Fichier : /includes/managerV2/head.php
	Description : Fichier générique entête administration de l'application Web

/=================================================================*/

require_once(ADMIN."logs.php");

$handle = DBHandle::get_instance();
$db = DBHandle::get_instance();
$user = $userChildScript = new BOUser();
if (!$user->login()) {
	header("Location: ".ADMIN_URL."login.html");
	exit();
}
$userPerms = $user->get_permissions();

// usefull functionalities index ny name
$fntl_tmp = BOFunctionality::get("name, id");
$fntByName = array();
foreach($fntl_tmp as $fnt)
  $fntByName[$fnt["name"]] = $fnt["id"];

$res_url = ADMIN_URL."ressources/";

$hStats = array(
	"primaryLeadCount" => 0,
	"secondaryLeadCount" => 0,
	"leadCA" => 0,
	"orderCount" => 0,
	"orderCA" => 0,
        "leadCARejectedToday" => 0
);

// leads stats
$morning = mktime(0,0,0,date("m"),date("d"),date("Y"));
$evening = mktime(0,0,0,date("m"),date("d")+1,date("Y"));

$yesterdayMorning = mktime(0,0,0,date("m"),date("d")-1,date("Y"));
$res = $handle->query("
	SELECT
		c.id, c.societe as company, c.timestamp as date, c.invoice_status, c.income, c.income_total, c.income_init, c.parent, c.reject_timestamp, c.id_user, c.origin,
		pfr.id as pdt_id, pfr.name as pdt_name,
		a.id as adv_id, a.nom1 as adv_name, a.category as adv_category, a.is_fields as adv_is_fields
	FROM contacts c
	LEFT JOIN products_fr pfr ON c.idProduct = pfr.id
	LEFT JOIN advertisers a ON c.idAdvertiser = a.id
	WHERE c.timestamp >= ".$morning." AND c.timestamp < ".$evening."
          OR c.reject_timestamp >= ".$morning." AND c.reject_timestamp < ".$evening."
	ORDER BY c.timestamp DESC", __FILE__, __LINE__);
$hleadList = array();
$nb_leadPerso = 0;
while($hlead = $handle->fetchAssoc($res)) {
  if ($hlead["invoice_status"] & __LEAD_CHARGED__ | $hlead["invoice_status"] & __LEAD_CHARGEABLE__) {
	$hStats["invoicedCount"]++;
        $hStats["leadCA"] += $hlead["income"];
  }
  elseif ($hlead["invoice_status"] & __LEAD_REJECTED__){
    $hStats["leadCA"] -= $hlead["income"];
    if($hlead['reject_timestamp'] >= $morning && $hlead['reject_timestamp'] < $evening) // let's take the sum of leads rejected today to substact it from total CA of the day (03/03/2011)
      $hStats["leadCARejectedToday"] += $hlead["income_init"];
  }
  elseif ($hlead["invoice_status"] & __LEAD_CREDITED__)
    $hStats["leadCA"] -= $hlead["income"];

  if ($hlead['date'] >= $morning && $hlead['date'] < $evening)
    if ($hlead["parent"] == 0)
      $hStats["primaryLeadCount"]++;
    else
      $hStats["secondaryLeadCount"]++;
  $hleadList[] = $hlead;

  if($hlead['id_user'] == $user->id && $hlead['parent'] == 0 && !empty ($hlead['origin']) && $hlead['origin'] != 'Internaute' && $hlead['date'] >= $morning && $hlead['date'] < $evening){
    $nb_leadPerso++;
  }
}

//leads-perfs
$currentUser = new BOUser($user->id);
$yesterArgs = array('timestamp >= '.$yesterdayMorning,'timestamp < '.$morning, 'id_user = '.$currentUser->id);
$yesterdaysLeads = Lead::get($yesterArgs);
$nb_leadHier = !empty ($yesterdaysLeads) ? count($yesterdaysLeads) : 0;
$maxLeadDay  = $currentUser->leads_best_score ? ($currentUser->leads_best_score >= $nb_leadPerso ? $currentUser->leads_best_score : $nb_leadPerso) : 0;

if($nb_leadPerso > $currentUser->leads_best_score){
  $currentUser->leads_best_score = $nb_leadPerso;
  $currentUser->altered = true;
  $currentUser->save();
}

// order stats
$o_day_stats = Doctrine_Query::create()
  ->select('count(id) AS day_count, SUM(total_ht) AS day_ca')
  ->from('Order')
  ->where('validated >= ? AND validated <= ?', array(mktime(0,0,0), mktime(23,59,59)))
  ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
$hStats["orderCount"] = $o_day_stats['day_count'];
$hStats["orderCA"] = $o_day_stats["day_ca"];

// force include of some classes
require_once(DOCTRINE_MODEL_PATH.'InternalNotes.php');

// getting Doctrine Class specific constants and static vars using Reflection, for passing them to js

$includes = get_included_files();
$js_vars = array();
$js_ns = "HN.TC."; // Javascript Base Namespace


foreach($includes as $include) {

  if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	$include = str_replace('\\','/',$include);
  }

  if (preg_match('`^'.DOCTRINE_MODEL_PATH.'\b(\w+)(?<!Table)\b\.php`i', $include, $matches)) {
    if (isset($reflectable_classes[$matches[1]])) {
      $refl = new ReflectionClass($matches[1]);
      $r_consts = $refl->getConstants();
      $r_static_vars = $refl->getStaticProperties();
      $js_vars[$js_ns.$matches[1]] = $js_ns.$matches[1]." || {}";
      foreach ($r_consts as $k => $v)
        if (!preg_match('/^STATE_[A-Z]+$/', $k)) // filter out Doctrine Record States
          $js_vars[$js_ns.$matches[1].'.'.$k] = $v;
      foreach ($r_static_vars as $k => $v)
        if (is_array($v) && !preg_match('/^_+/', $k)) // filter out Doctrine Static Vars
          $js_vars[$js_ns.$matches[1].'.'.$k] = json_encode($v);
    }
  }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr" xml:lang="fr">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Manager Techni-Contact | <?php echo $title ?></title>
 <?php if(isset($baseHref)) : ?>
  <base href="<?php echo $baseHref ?>">
 <?php endif ?>
  <link href="<?php echo ADMIN_URL ?>css/ui/ui.base.css" rel="stylesheet" media="all" />

	<link href="<?php echo ADMIN_URL ?>css/themes/apple_pie/ui.css" rel="stylesheet" title="style" media="all" />

	<!--[if IE 6]>
	<link href="<?php echo ADMIN_URL ?>css/ie6.css" rel="stylesheet" media="all" />

	<script src="<?php echo ADMIN_URL ?>js/pngfix.js"></script>
	<script>
	  /* Fix IE6 Transparent PNG */
	  DD_belatedPNG.fix(".logo, ul#dashboard-buttons li a, .response-msg, #search-bar input");

	</script>
	<![endif]-->

  <link rel="shortcut icon" href="/favicon.ico">
	<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/global.css">
  <link type="text/css" rel="stylesheet" title="style" media="all" href="<?php echo ADMIN_URL ?>css/ui/ui.datepicker.css" />
  <link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>css/rdv.css">

<!--
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.7.3/jquery-ui.min.js"></script>
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.7.3/i18n/ui.datepicker-fr.min.js"></script>
-->


  	<script type="text/javascript" src="<?php echo ADMIN_URL ?>ressources/js/jquery/jquery_1.7.2.min.js"></script>
  	<script type="text/javascript" src="<?php echo ADMIN_URL ?>ressources/js/jqueryui/1/jquery-ui.min.js"></script>
	<!-- <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.7.3/jquery-ui.min.js"></script> -->
  	<script type="text/javascript" src="<?php echo ADMIN_URL ?>ressources/js/jqueryui/ui.datepicker-fr.min.js"></script>


	<script type="text/javascript" src="<?php echo ADMIN_URL ?>js/superfish.js"></script>
	<script type="text/javascript" src="<?php echo ADMIN_URL ?>js/live_search.js"></script>
	<script type="text/javascript" src="<?php echo ADMIN_URL ?>js/tooltip.js"></script>
	<script type="text/javascript" src="<?php echo ADMIN_URL ?>js/cookie.js"></script>
	<!--<script type="text/javascript" src="<?php echo ADMIN_URL ?>js/ui/ui.core.js"></script>-->
	<!--<script type="text/javascript" src="<?php echo ADMIN_URL ?>js/ui/ui.sortable.js"></script>-->
	<!--<script type="text/javascript" src="<?php echo ADMIN_URL ?>js/ui/ui.draggable.js"></script>-->
	<!--<script type="text/javascript" src="<?php echo ADMIN_URL ?>js/ui/ui.resizable.js"></script>-->
	<!--<script type="text/javascript" src="<?php echo ADMIN_URL ?>js/ui/ui.dialog.js"></script>-->
	<script type="text/javascript" src="<?php echo ADMIN_URL ?>js/custom.js"></script>
	<script type="text/javascript" src="<?php echo $res_url ?>scripts/HN.TC.js"></script>
  <script type="text/javascript">
    $.extend(HN.TC, {
      DOMAIN: "<?php echo DOMAIN ?>",
      URL: "<?php echo URL ?>",
      SECURE_URL: "<?php echo SECURE_URL ?>",
      ADMIN_URL: "<?php echo ADMIN_URL ?>",
      EXTRANET_URL: "<?php echo EXTRANET_URL ?>",
      COMPTE_URL: "<?php echo COMPTE_URL ?>",
      COMMANDE_URL: "<?php echo COMMANDE_URL ?>",
      SECURE_RESSOURCES_URL: "<?php echo SECURE_RESSOURCES_URL ?>",
      PDF_URL: "<?php echo PDF_URL ?>",
      PDF_URL_ARC: '<?php echo PDF_URL_ARC ?>',
      PRODUCTS_IMAGE_SECURE_URL: "<?php echo PRODUCTS_IMAGE_SECURE_URL ?>",
      __ADV_CAT_ADVERTISER__: <?php echo __ADV_CAT_ADVERTISER__ ?>,
      __ADV_CAT_SUPPLIER__: <?php echo __ADV_CAT_SUPPLIER__ ?>,
      __ADV_CAT_ADVERTISER_NOT_CHARGED__: <?php echo __ADV_CAT_ADVERTISER_NOT_CHARGED__ ?>,
      __ADV_CAT_PROSPECT__: <?php echo __ADV_CAT_PROSPECT__ ?>,
      __ADV_CAT_BLOCKED__: <?php echo __ADV_CAT_BLOCKED__ ?>,
      __ADV_CAT_LITIGATION__: <?php echo __ADV_CAT_LITIGATION__ ?>,
      adv_cat_list: <?php echo json_encode($adv_cat_list) ?>,
      __MSGR_CTXT_SUPPLIER_TC_ORDER__: <?php echo __MSGR_CTXT_SUPPLIER_TC_ORDER__ ?>,
      __MSGR_CTXT_SUPPLIER_TC_LEAD__: <?php echo __MSGR_CTXT_SUPPLIER_TC_LEAD__ ?>,
      __MSGR_CTXT_CUSTOMER_TC_LEAD__: <?php echo __MSGR_CTXT_CUSTOMER_TC_LEAD__ ?>,
      __MSGR_CTXT_CUSTOMER_TC_CMD__: <?php echo __MSGR_CTXT_CUSTOMER_TC_CMD__ ?>,
      __MSGR_CTXT_CUSTOMER_TC_DEVIS_PDF__: <?php echo __MSGR_CTXT_CUSTOMER_TC_DEVIS_PDF__ ?>,
      __MSGR_CTXT_CUSTOMER_ADVERTISER_LEAD__: <?php echo __MSGR_CTXT_CUSTOMER_ADVERTISER_LEAD__ ?>,
      __MSGR_CTXT_CUSTOMER_TC_ESTIMATE__: <?php echo __MSGR_CTXT_CUSTOMER_TC_ESTIMATE__ ?>,
      __MSGR_CTXT_CUSTOMER_TC_INVOICE__: <?php echo __MSGR_CTXT_CUSTOMER_TC_INVOICE__ ?>,
      __MSGR_CTXT_ORDER_CMD__: <?php echo __MSGR_CTXT_ORDER_CMD__ ?>,
      __MSGR_USR_TYPE_ADV__: <?php echo __MSGR_USR_TYPE_ADV__ ?>,
      __MSGR_USR_TYPE_INT__: <?php echo __MSGR_USR_TYPE_INT__ ?>,
      __MSGR_USR_TYPE_BOU__: <?php echo __MSGR_USR_TYPE_BOU__ ?>,
      // old constants
      Locals: {
        URL: "<?php echo URL ?>",
        AccountURL: "<?php echo COMPTE_URL ?>",
        OrderURL: "<?php echo COMMANDE_URL ?>",
        AdminURL: "<?php echo ADMIN_URL ?>",
        RessourcesURL: "<?php echo ADMIN_URL ?>ressources/"
      }
    });
   <?php foreach ($js_vars as $k => $v) { ?>
     <?php echo $k." = ".$v ?>;
   <?php } ?>
  </script>
	<script type="text/javascript" src="<?php echo ADMIN_URL ?>js/global.js"></script>
	<script type="text/javascript" src="<?php echo $res_url ?>ajax/AJAX_search.js"></script>
	<script type="text/javascript" src="<?php echo ADMIN_URL ?>js/search-layer.js"></script>
	<script type="text/javascript">
$(function(){
	searchAC = new HN.UI.AutoCompletion($("#search-bar .search-box .search").get(0));
  var time_offset = <?php echo time() ?>*1000 - (new Date()).getTime();
  setInterval(function(){
    var today = new Date();
    today.setTime(today.getTime()+time_offset);
    $("#ddate").html(sprintf("%02d/%02d/%04d - %02dh%02dm%02ds",today.getDate(),today.getMonth()+1,today.getFullYear(),today.getHours(),today.getMinutes(),today.getSeconds()));
  },1000);
});
	</script>
	  
	
	
</head>

<body>
  <div id="page_wrapper">
    <?php	if (TEST) include MAINTENANCE_PATH."head.php" ?>
    <div id="page-header">
      <div id="page-header-wrapper">
        <div id="top">
          <h1 style="font-size: 20px; color: #FFFFFF; padding-top: 2px;"><a href="<?php echo ADMIN_URL."?"?>" target="_top"><img src="<?php echo ADMIN_URL  ?>images/md2i.png" alt="Techni-Contact" border="0"/></a></h1>

          <div id="headerTime">
            <img src="<?php echo ADMIN_URL ?>images/<?php echo DB_LANGUAGE ?>.gif" alt="<?php echo $language_list[DB_LANGUAGE]["name_fr"] ?>" border="0" style="float: left; margin-right: 10px;" />
            <span id="ddate"><?php echo date("d/m/Y - H\hi\ms\s") ?></span>
          </div>

          <div id="languages">
           <?php foreach($language_list as $lang => $data) if ($lang != DB_LANGUAGE) { ?>
            <a href="<?php echo ADMIN_URL."../../".$lang."/manager" ?>"><img src="<?php echo ADMIN_URL."images/".$lang.".gif" ?>" alt="<?php echo $data["name_fr"] ?>"/></a>
           <?php } ?>
          </div>

         <?php if ($userPerms->has($fntByName["m-comm--sm-leads"], "r")) { ?>
          <div id="stats-leads">
            Nombre leads total du jour : <?php echo ($hStats["primaryLeadCount"]+$hStats["secondaryLeadCount"]) ?><br/>
            Nombre leads uniques du jour : <?php echo $hStats["primaryLeadCount"] ?><br/>
			<?php
				$yesterday_start		= strtotime($yesterday_date.' 00:00:00');
				$yesterday_end			= strtotime($yesterday_date.' 23:59:59');

				$sql_income_today   = "SELECT SUM(income) total
									   FROM   contacts
									   WHERE  create_time
													BETWEEN '".$yesterday_start."' AND '".$yesterday_end."' ";
				$req_income_today   =  mysql_query($sql_income_today);
				$data_income_today  =  mysql_fetch_object($req_income_today);
			    if(empty($data_income_today->total)) $income_today = 0;
				else $income_today  = $data_income_today->total;

				$sql_income_today_or = "SELECT SUM(income) total
									    FROM   contacts
									    WHERE  create_time
													BETWEEN '".$yesterday_start."' AND '".$yesterday_end."'
													AND invoice_status IN('27','154')";
				$req_income_today_or =  mysql_query($sql_income_today_or);
				$data_income_today_or=  mysql_fetch_object($req_income_today_or);
				if(empty($data_income_today_or->total)) $income_today_or = 0;
				else $income_today_or  = $data_income_today_or->total;


				$ca_lead_today  = $income_today - $income_today_or;
				//$hStats["leadCA"]-$hStats["leadCARejectedToday"]
			?>
		   CA leads du jour : <?= $ca_lead_today ?> €
          </div>
         <?php } ?>
         <?php if ($userPerms->has($fntByName["m-comm--sm-orders"], "r")) { ?>
          <div id="stats-orders">
            Nb de commandes du jour : <?php echo $hStats["orderCount"] ?><br/>
            CA VPC du jour : <?php echo $hStats["orderCA"] ?> €
          </div>
         <?php } ?>
         <?php
         if ($nb_leadPerso || $nb_leadHier || $maxLeadDay) { ?>
          <div id="leads-perfs">
            Opérateur : <?php echo $user->name ?><br/>
            Nb lead du jour : <?php echo $nb_leadPerso ?>, hier : <?php echo $nb_leadHier ?><br/>
            Meilleure performance  : <?php echo $maxLeadDay ?>
          </div>
         <?php } ?>

        <div id="search-bar">
            <form action="<?php echo ADMIN_URL ?>search.php" method="get">
               <div class="search-boxBis">
             <?php if ($userPerms->has($fntByName["m-prod--sm-products"], "r")) { ?>
              <input type="radio" name="search_type" value="1" checked="checked" /><label>Pr</label>
             <?php } ?>
             <?php if ($userPerms->has($fntByName["m-prod--sm-categories"], "r")) { ?>
              <input type="radio" name="search_type" value="2" /><label>Fam</label>
             <?php } ?>
             <?php if ($userPerms->has($fntByName["m-prod--sm-partners"], "r")) { ?>
              <input type="radio" name="search_type" value="3" /><label>Par</label>
             <?php } ?>
              </div>
              <div class="search-box">
                <input type="text" name="search" class="search inputText" title="Entrez ici votre recherche" autocomplete="off"/>
              </div>

            </form>
		</div>


          <div id="fastAccess" class="welcome">
            <?php if ($userPerms->has($fntByName["m-admin--sm-users"], "r")) { ?><a href="<?php echo ADMIN_URL ?>users/user.php?id=<?php echo $user->id ?>" class="btn ui-state-default ui-corner-all"><span class="ui-icon ui-icon-person"></span>Mon compte</a><?php } ?>
            <a href="<?php echo ADMIN_URL ?>logout.php" class="btn ui-state-default ui-corner-all"><span class="ui-icon ui-icon-power"></span>D&eacute;connexion</a>
          </div>
        </div>
        <!-- End #top -->

        <?php $start = microtime(true) ?>
        <ul id="navigation">
         <?php if ($userPerms->has($fntByName["m-prod"], "r")) { ?>
          <li>
            <a href="#" class="sf-with-ul">Production</a>
            <ul>
              <?php if ($userPerms->has($fntByName["m-prod--sm-products"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>products/">Gestion catalogue produits</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-prod--sm-partners"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>advertisers/">Gestion partenaires</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-prod--sm-categories"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>families/">Gestion familles</a> </li><?php } ?>
							<?php if ($userPerms->has($fntByName["m-prod--sm-categories"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>families/#/attributes">Gestion attributs</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-prod--sm-import"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>import/imports.php">Module d'importation produits</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-prod--sm-export"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>export/exports.php">Module d'exportation produits</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-prod--sm-import"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>import/suppliers.php">Mise à jour fournisseur</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-prod--sm-extranet"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>extranetinfo.php">Demandes extranet</a> </li><?php } ?>
			  <?php if ($userPerms->has($fntByName["m-prod--sm-maj-fournisseurs"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>supplier-update/index.php">MAJ fournisseurs</a> </li><?php } ?>
            </ul>
          </li>
         <?php } ?>
         <?php if ($userPerms->has($fntByName["m-comm"], "r")) { ?>
          <li>
            <a href="#" class="sf-with-ul">Commercial</a>
            <ul>
              <?php if ($userPerms->has($fntByName["m-comm--sm-estimates"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>estimates/estimates.php">Gestion des devis</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-comm--sm-orders"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>orders/orders.php">Gestion des commandes</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-comm--sm-partners-orders"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>supplier-orders/supplier-orders.php">Gestion des ordres fournisseurs</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-comm--sm-invoices"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>invoices/invoices.php">Gestion des factures et avoirs</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-comm--sm-pdf-estimates"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>devis-pdf/">Gestion des devis PDF</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-comm--sm-supplier-leads"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>supplier-leads/leads.php">Pile leads fournisseurs</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-comm--sm-leads"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>contacts/leads.php">Gestion des contacts</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-comm--sm-invoices-partners"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>advertisers/invoices_list.php">Facturation annonceurs</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-comm--sm-customers"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>clients/">Rechercher un client</a> </li><?php } ?>
			  <?php if ($userPerms->has($fntByName["m-comm--sm-pile-appels-complete"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>pile_appels_commerciaux/pile_appels_VPC.php">Pile d’appels VPC</a> </li><?php } ?>
			  <?php if ($userPerms->has($fntByName["m-comm--sm-pile-appel-personaliser"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>pile_appels_commerciaux/pile_appels_VPC.php">Pile d’appels VPC</a> </li><?php } ?>
			  <?php if ($userPerms->has($fntByName["m-comm--sm-gestion-sav"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>gestion_sav/index.php">Gestion des SAV</a> </li><?php } ?>
			  
            </ul>
          </li>
         <?php } ?>
         <?php if ($userPerms->has($fntByName["m-mark"], "r")) { ?>
          <li>
            <a href="#" class="sf-with-ul">Marketing</a>
            <ul>
              <?php if ($userPerms->has($fntByName["m-mark--sm-flagship-products"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>products-flagship-v2/index.php">Gestion des produits phares</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-mark--sm-products-priorities"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>families/pdt-overwrite.php">Priorité familles/produits</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-mark--sm-mini-stores"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>mini-stores/mini-stores.php?">Mini boutiques</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-mark--sm-leads-export"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>export/contacts.php">Export demandes de contact</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-mark--sm-discounts-promotions"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>discounts-constraints/index.php">Remises/promotions</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-mark--sm-watch"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>veille.php">Veille</a> </li><?php } ?>
			  <?php if ($userPerms->has($fntByName["m-mark--qa-fiches-produits"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>/q_a_products/q-a-fiches-produits.php">Q&A fiches produits</a> </li><?php } ?>
			  <?php if ($userPerms->has($fntByName["m-mark--sm-gestion-achat"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>guides_achat/index.php">Gestion guides d'achat</a> </li><?php } ?>
			  <?php if ($userPerms->has($fntByName["m-mark--sm-gestion-familles-bloquees"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>familles-bloquees/index.php">Familles bloquées récupérables</a> </li><?php } ?>
			  <?php if ($userPerms->has($fntByName["m-mark--sm-gestion-blog"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>blog/index.php">Gestion du blog</a> </li><?php } ?>
			  

            </ul>
          </li>
         <?php } ?>
         <?php if ($userPerms->has($fntByName["m-stat"], "r")) { ?>
          <li>
            <a href="<?php echo ADMIN_URL ?>stats/" class="sf-with-ul">Statistiques</a>
          </li>
         <?php } ?>
          <?php if ($userPerms->has($fntByName["m-smpo"], "r")) { ?>
          <li>
            <a href="#" class="sf-with-ul">SMPO</a>
            <ul>
              <?php if ($userPerms->has($fntByName["m-smpo--sm-lead-create"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>contacts/lead-create.php">Générer un lead</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-smpo--sm-call-list"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>smpo/calls_list.php">Pile d'appels</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-smpo--sm-campaign"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>smpo/load_campaign.php">Campagne d'appels</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-smpo--sm-script-product"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>smpo/script_product.php">Gestion des scripts</a> </li><?php } ?>
            </ul>
          </li>
         <?php } ?>
		 <?php if ($userPerms->has($fntByName["bi-kpi"], "r")) { ?>
          <li>
            <a href="#" class="sf-with-ul">BI – KPI</a>
            <ul>
              <li> <a href="<?php echo ADMIN_URL ?>bi_kpi_v2/search_kpi.php">KPI Familles 3</a> </li>
			</ul>
          </li>
         <?php } ?>
        <?php if ($userPerms->has($fntByName["m-reporting"], "r")) { ?>
          <li>
            <a href="#" class="sf-with-ul">Reporting</a>
            <ul>
              <?php if ($userPerms->has($fntByName["m-reporting--sm-roi-campaign"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>reporting/roi-campaign.php">ROI Campagne</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-reporting--sm-production"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>reporting/production.php">Production</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-reporting--sm-call-chat"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>reporting/call-chat.php">Call / SMPO</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-reporting--sm-supplier-leads"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>reporting/supplier-leads.php">Devis VPC</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-reporting--sm-orders"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>reporting/orders.php">Commandes</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-reporting--sm-rejected-leads"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>reporting/rejected-leads.php">Taux de rejets</a> </li><?php } ?>
			  <?php if ($userPerms->has($fntByName["m-reporting--sm-call-vpc"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>reporting/call_vpc.php">Call / VPC</a> </li><?php } ?>
			  <?php if ($userPerms->has($fntByName["m-reporting--sm-notation-operateurs"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>reporting/notation_operateurs.php">Notation opérateurs</a> </li><?php } ?>
			  <?php if ($userPerms->has($fntByName["m-reporting--sm-sav"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>reporting/reporting_sav.php">SAV</a> </li><?php } ?>
            </ul>
          </li>
         <?php } ?>
         <?php if ($userPerms->has($fntByName["m-admin"], "r")) { ?>
          <li>
            <a href="#" class="sf-with-ul">Administration</a>
            <ul>
              <?php if ($userPerms->has($fntByName["m-admin--sm-contact-form"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>form-content/index.php">Formulaire de contact g&eacute;n&eacute;ral</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-admin--sm-misc-page-edit"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>files/">Edition de pages diverses</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-admin--sm-default-options"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>config/">Options par défaut</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-admin--sm-tva"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>tva/">Gestion TVA</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-admin--sm-competition-filtering"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>demandes/s.php">Filtrage concurrence catalogue</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-admin--sm-users"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>users/users.php">Gestion des utilisateurs</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-admin--sm-campaign-mkt"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>reporting/campaign-mkt.php">Gestion campagnes mkt</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-admin--sm-activity-sector"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>config/activity-sector.php">Gestion secteurs qualifiés</a> </li><?php } ?>
              <?php if ($userPerms->has($fntByName["m-admin--sm-cegid-export"], "r")) { ?><li> <a href="<?php echo ADMIN_URL ?>export/cegid.php">Export CEGID</a> </li><?php } ?>
            </ul>
          </li>
         <?php } ?>
         <?php $end = microtime(true) ?>
		  <li>
		    <a href="<?php echo URL ?>" target="_blank">Acc&egrave;s au site</a>
		  </li>
        </ul>
        <!-- End #navigation -->

      </div>
      <!-- End #page-header-wrapper -->
    </div>
    <!-- End #page-header -->

    <div id="sub-nav">
      <div class="page-title">
        <h1><?php echo $title ?></h1>
	  </div>

	 <div id="top-buttons">
       <?php if ($userPerms->has($fntByName["m-prod--sm-products"], "e")) { ?>
        <a class="btn ui-state-default ui-corner-all" href="<?php echo ADMIN_URL ?>products/">
          <span class="ui-icon ui-icon-plus"></span>
          Ajouter fiche produit
        </a>
       <?php } ?>
       <?php if($userPerms->has($fntByName["m-prod--sm-partners"], "e")){ ?>
        <a class="btn ui-state-default ui-corner-all" href="<?php echo ADMIN_URL ?>advertisers/">
          <span class="ui-icon ui-icon-plus"></span>
          Ajouter annonceur
        </a>
       <?php } ?>
       <?php if($userPerms->has($fntByName["m-comm--sm-orders"], "r")) { ?>
        <a class="btn ui-state-default ui-corner-all" href="<?php echo ADMIN_URL ?>orders/orders.php">
          <span class="ui-icon ui-icon-folder-collapsed"></span>
          Gestion des commandes
        </a>
       <?php } ?>
      </div>

     <div id="logo-website">
		<?php
		if(!empty($website_origin) && $website_origin == 'mobaneo' ){ ?>
		<img class="top-website-logo" src="../ressources/images/logo-website-<?php echo strtolower($website_origin) ?>.jpg" alt="<?php echo strtolower($website_origin) ?>" />
		<?php }
		if(!empty($website_origin) && $website_origin == 'mercateo' ){ ?>
		<img class="top-website-logo" src="../ressources/images/logo-website-<?php echo strtolower($website_origin) ?>.jpg" alt="<?php echo strtolower($website_origin) ?>" />
		<?php } ?>
	  </div>
	 <?php /*
	 echo $website_origin;
		if (!empty($website_origin) && strtolower($website_origin) != 'tc') : ?>

     <?php endif */?>


    </div>
	<style>
		#server-test, #rdvHelperLayer{
			display:none !important;
		}
	</style>


    <div id="page-layout">
  <div id="page-content">
    <div id="page-content-wrapper">
<?php require_once(ADMIN."rdv.php") ?>
<?php require_once(ADMIN."tabbed_search.php") ?>
<?php require_once(ADMIN."tabbed_commande.php") ?>
