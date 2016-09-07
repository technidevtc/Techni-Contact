<?php
/*
range	->	range of time to take into account
	barunit	->	unit of a bar
		avgunit	->	unit used for average (determine the graph range)
			filter	->	General, for one advertiser, for one supplier, for one product...
				source	->	pages vues/marge/...

range list
	Everything possible (All time, 2 years, 3month, 6hour and 15mn ...

barunit list
	if (range_begin = beginning of a year and range_end = end of a year)
		year
	if (range_begin = beginning of a month and range_end = end of a month)
		month
	if (range_begin = beginning of a day and range_end = end of a day)
		day
	if (range > 7 days)
		week
	if (range_begin = beginning of a hour and range_end = end of a hour)
		hour
	if (range_begin = mn%5 = 0 and range_end = mn%5 = 0)
		5mn
	
avgunit list
	if ( !(range > 2 year and func <= total/day) AND !(range > 2 monthes and func <= total/hour) AND !(range > 2 days and func <= total/5mn) )
		none
	if (range_begin = beginning of a year and range_end = end of a year AND !(range > 2 monthes and func <= total/hour) AND !(range > 2 days and func <= total/5mn) )
		year
	if (range_begin = beginning of a month and range_end = end of a month AND !(range > 2 days and func <= total/5mn) )
		month
	if (range_begin = beginning of a day and range_end = end of a day)
		day
	if (range > 7 days)
		week
	if (range_begin = beginning of a hour and range_end = end of a hour)
		hour

filter list
	General
	
	All Advertisers
	One Advertiser (All products)
	(One Advertiser) One product
	
	All Suppliers
	One Supplier (All products)
	(One Supplier) One product
	
	One main family (All products)
	One sub_family (All products)
	One sub_sub_family (All products)
	
source list
	if filter = General
		nombre de produits créés
		pages vues produit
		ajouts aux panier produit
		nb compte client créés
		nb devis générés
		nb commandes
		nb devis -> commande
		marge totale
		CA
	
	if filter = "All Advertisers" or "One Advertiser (All products)"
		nombre de produits créés
		pages vues produit
		nombres de demande de contact différentes
		nombres de demande de contact à comptabiliser (suivant réglage fiche annonceur)
	if filter = "(One Advertiser) One product"
		pages vues produit
		nombres de demande de contact différentes pour ce produit
		nombres de demande de contact à comptabiliser (suivant réglage fiche annonceur) pour ce produit
	
	if filter = "All Suppliers" or "One Supplier (All products)"
		nombres de produits créés
		pages vues produit
		ajouts au panier produit
		nb de devis ayant au moins une ref produit de ce fournisseur
		nb de commandes ayant au moins une ref produit de ce fournisseur
		nb de devis contenant au moins une ref produit de ce fournisseur ayant donné lieu à une commande
		marge totale des produits vendus par ce fournisseur
		CA des produits vendus par ce fournisseur
		nombres de demande de contact différentes
		nombres de demande de contact à comptabiliser (suivant réglage fiche annonceur)
	if filter = "(One Supplier) One product"
		pages vues produit
		ajouts au paniers produit
		nb de devis ayant au moins une ref de ce produit
		nb de commandes ayant au moins une ref de ce produit
		nb de devis contenant au moins une ref de ce produit ayant donné lieu à une commande
		marge totale généré par les ventes de ce produit
		CA généré par les ventes de ce produit
		nombres de demande de contact différentes pour ce produit
		nombres de demande de contact à comptabiliser (suivant réglage fiche annonceur) pour ce produit

	if filter = "One main family (All products)" or "One sub_family (All products)" or "One sub_sub_family (All products)"
		nombres de produits créés
		pages vues produit
		ajouts au panier
		nombre de devis ayant au moins une ref produit de cette famille
		nombre de commandes ayant au moins une ref produit de cette famille
		nombre de devis contenant au moins une ref produit de cette famille ayant donné lieu à une commande
		marge totale des produits vendus faisant parti de cette famille
		CA total des produits vendus faisant parti de cette famille
		nombres de demande de contact différentes pour cette famille
		nombres de demande de contact à comptabiliser (suivant réglage fiche annonceur) pour cette famille


--------------------
Exemples :
--------------------

range = début 2006 à fin 2006 (= 2006)
	barunit = hour
		avgunit = day
			filter = General
				source = pages vues
-> nombre de pages vues en moyenne par heure  moyenné par jour  pour l'année 2006

range = mars 2007
	barunit = hour
		avgunit = day -> graph range = one day
			filter = Fournisseur DUPONT
				source = pages vues
-> nombre de page vues par heure  moyenné par jour  pour le mois de mars 2007 pour le fournisseur DUPONT


Select : 
range : year, month=func(year), day=func(year, month), hour
	barunit : year, month, day, hour, 5mn





*/

//GetStats.php?manager=1ea2f92b496ef5ba256c6d3d7bba1a8c&Type=S&ID=42367&Source=0&Year=0&Month=0&Day=0
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ICLASS . 'ManagerUser.php');
require(ADMIN  . 'generator.php');
require(ADMIN  . 'logs.php');

$handle = DBHandle::get_instance();
$user   = & new ManagerUser($handle);

function microtime_float()
{
  list($usec, $sec) = explode(" ", microtime());
  return ((float)$usec + (float)$sec%1000);
}

if(!$user->login())
{
	header('Content-type: image/jpg');
	@imagepng(imagecreatefrompng('SessionExpired.png'));
	exit();
}

include("src/jpgraph.php");
include("src/jpgraph_bar.php");

define("__FIRST_YEAR__", 2004);
define("__CURRENT_YEAR_", date('Y'));

$es = '';
$Type = isset($_GET['Type']) ? trim($_GET['Type']) : 'G';
$ID = isset($_GET['ID']) ? trim($_GET['ID']) : '';
settype($ID, 'integer');

$Source = isset($_GET['Source']) ? trim($_GET['Source']) : '0';
settype($Source, 'integer'); if ($Source < 0 || $Source > 8) $Source = 0;

$Year = isset($_GET['Year']) ? trim($_GET['Year']) : '0';
settype($Year, 'integer'); if ($Year != 0 && ($Year < __FIRST_YEAR__ || $Year > __CURRENT_YEAR_)) $Year = __CURRENT_YEAR_;

$Month = isset($_GET['Month']) ? trim($_GET['Month']) : '0';
settype($Month, 'integer'); if ($Month < 0 || $Month > 12) $Month = 0;

$Day = isset($_GET['Day']) ? trim($_GET['Day']) : '0';
settype($Day, 'integer'); if ($Day < 0 || $Day > 31) $Day = 0;

//$start1 = microtime_float();
//$end1 = microtime_float();
//print("temps total d'execution : " . (($end1-$start1)*1000) . 'ms<br />');

$YearLabels = array();
for($i = __FIRST_YEAR__; $i <= __CURRENT_YEAR_; $i++) $YearLabels[] = $i;
$DayLabels = array();
$MonthLabels = array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre');
$HourLabels = range(0,23);

$databary = array();

switch($Type)
{
	case 'G' : 
		$typedesc = "au total";
		$GraphWidth = 970; $GraphHeight = 475;
		switch ($Source)
		{
			case  0 :
				$sourcedesc = "pages vues";
				$query = "select count(idProduct) from stats_hit where";
				$format = '%d';
				break;
			case 1 :
				$sourcedesc = "ajouts aux paniers";
				$query = "select count(idAdvertiser) from stats_cart where";
				$format = '%d';
				break;
			case 2 :
				$sourcedesc = "nb compte client créés (lead)";
				$query = "select count(id) from clients where (origin = 'L' or origin = 'A') and";
				$format = '%d';
				break;
			case 3 :
				$sourcedesc = "nb compte client créés (commande)";
				$query = "select count(id) from clients where origin = 'O' and";
				$format = '%d';
				break;
			case 4 :
				$sourcedesc = "nb devis générés";
				$query = "select count(idAdvertiser) from stats_esti where";
				$format = '%d';
				break;
			case 5 :
				$sourcedesc = "nb commandes";
				$query = "select count(distinct idCommand) from stats_cmd where";
				$format = '%d';
				break;
			case 6 :
				$sourcedesc = "nb devis -> commande";
				$query = "select count(idAdvertiser) from stats_esti2cmd where";
				$format = '%d';
				break;
			case 7 :
				$sourcedesc = "marge totale";
				$query = "select sum((price-price2) * quantity) from stats_cmd where";
				$format = '%.2f';
				break;
			case 8 :
				$sourcedesc = "CA";
				$query = "select sum(price * quantity) from stats_cmd where";
				$format = '%.2f';
				break;
			default : break;
		}
		break;
		
	case 'S' :
		if ($ID != 0)
		{
			$typedesc = "du fournisseur n°" . $ID;
			$GraphWidth = 815; $GraphHeight = 455;
			switch ($Source)
			{
				case  0 :
					$sourcedesc = "pages vues";
					$query = "select count(idAdvertiser) from stats_hit where idAdvertiser = " . $ID . " and";
					$format = '%d';
					break;
				case 1 :
					$sourcedesc = "ajouts aux paniers";
					$query = "select count(idAdvertiser) from stats_cart where idAdvertiser = " . $ID . " and";
					$format = '%d';
					break;
				case 2 :
					$sourcedesc = "nb devis générés";
					$query = "select count(idAdvertiser) from stats_esti where idAdvertiser = " . $ID . " and";
					$format = '%d';
					break;
				case 3 :
					$sourcedesc = "nb commandes";
					$query = "select count(distinct idCommand) from stats_cmd where idAdvertiser = " . $ID . " and";
					$format = '%d';
					break;
				case 4 :
					$sourcedesc = "nb devis -> commande";
					$query = "select count(idAdvertiser) from stats_esti2cmd where idAdvertiser = " . $ID . " and";
					$format = '%d';
					break;
				case 5 :
					$sourcedesc = "marge totale";
					$query = "select sum((price-price2) * quantity) from stats_cmd where idAdvertiser = " . $ID . " and";
					$format = '%.2f';
					break;
				case 6 :
					$sourcedesc = "CA";
					$query = "select sum(price * quantity) from stats_cmd where idAdvertiser = " . $ID . " and";
					$format = '%.2f';
					break;
				case 7 :
					$sourcedesc = "nb contact total";
					$query = "select count(societe) from contacts where idAdvertiser = " . $ID . " and";
					$format = '%d';
					break;
				case 8 :
					$result = & $handle->query("select cc_foreign, cc_intern, cc_noPrivate from advertisers where id = " . $ID, __FILE__, __LINE__, false);
					$ccs = & $handle->fetchAssoc($result);
					$sourcedesc = "nb contact à comptabiliser";
					$query = "select count(distinct societe) from contacts where idAdvertiser = " . $ID .
						($ccs['cc_foreign'] != 1 ? ' and (pays = "FRANCE" or pays = "INCONNU")' : '') .
						($ccs['cc_intern'] != 1 ? ' and fonction not like "%tagiair%"' : '') .
						($ccs['cc_noPrivate'] != 0 ? ' and fonction not like "%particulier%" and secteur not like "%particulier%"' : '') .
						" and";
					$format = '%d';
					break;
				default : break;
			}
		}
		else $es = "Veuillez choisir un fournisseur";
		break;
		
	case 'A' :
		if ($ID != 0)
		{
			$typedesc = "de l'annonceur n°" . $ID;
			$GraphWidth = 815; $GraphHeight = 455;
			switch ($Source)
			{
				case  0 :
					$sourcedesc = "pages vues";
					$query = "select count(idAdvertiser) from stats_hit where idAdvertiser = " . $ID . " and";
					$format = '%d';
					break;
				case 1 :
					$sourcedesc = "nb contact total";
					$query = "select count(societe) from contacts where idAdvertiser = " . $ID . " and";
					$format = '%d';
					break;
				case 2 :
					$result = & $handle->query("select cc_foreign, cc_intern, cc_noPrivate from advertisers where id = " . $ID, __FILE__, __LINE__, false);
					$ccs = & $handle->fetchAssoc($result);
					$sourcedesc = "nb contact à comptabiliser";
					$query = "select count(distinct societe) from contacts where idAdvertiser = " . $ID .
						($ccs['cc_foreign'] != 1 ? ' and (pays = "FRANCE" or pays = "INCONNU")' : '') .
						($ccs['cc_intern'] != 1 ? ' and fonction not like "%tagiair%"' : '') .
						($ccs['cc_noPrivate'] != 0 ? ' and fonction not like "%particulier%" and secteur not like "%particulier%"' : '') .
						" and";
					$format = '%d';
					break;
				default : break;
			}
		}
		else $es = "Veuillez choisir un fournisseur";
		break;
		
	case 'P' :
		if ($ID != 0)
		{
			$typedesc = "du produit n°" . $ID;
			$GraphWidth = 815; $GraphHeight = 455;
			switch ($Source)
			{
				case  0 :
					$sourcedesc = "pages vues";
					$query = "select count(idProduct) from stats_hit where idProduct = " . $ID . " and";
					$format = '%d';
					break;
				case 1 :
					$sourcedesc = "ajouts aux paniers";
					$query = "select count(idProduct) from stats_cart where idProduct = " . $ID . " and";
					$format = '%d';
					break;
				case 2 :
					$sourcedesc = "nb devis générés";
					$query = "select count(idProduct) from stats_esti where idProduct = " . $ID . " and";
					$format = '%d';
					break;
				case 3 :
					$sourcedesc = "nb commandes";
					$query = "select count(distinct idCommand) from stats_cmd where idProduct = " . $ID . " and";
					$format = '%d';
					break;
				case 4 :
					$sourcedesc = "nb devis -> commande";
					$query = "select count(idProduct) from stats_esti2cmd where idProduct = " . $ID . " and";
					$format = '%d';
					break;
				case 5 :
					$sourcedesc = "marge totale";
					$query = "select sum((price-price2) * quantity) from stats_cmd where idProduct = " . $ID . " and";
					$format = '%.2f';
					break;
				case 6 :
					$sourcedesc = "CA";
					$query = "select sum(price * quantity) from stats_cmd where idProduct = " . $ID . " and";
					$format = '%.2f';
					break;
				default : break;
			}
		}
		else $es = "Veuillez choisir un produit";
		break;
		
	default : $es = "Impossible de déterminer le graphique demandé (Général, Fournisseur ...)";
}

if ($es == '')
{
	if ($Year == 0)
	{
		for($i = __FIRST_YEAR__; $i <= __CURRENT_YEAR_; $i++)
		{
			$begin = mktime(0,0,0,1,1,$i); $end = mktime(0,0,0,1,1,$i+1);
			$result = & $handle->query($query . " timestamp >= " . $begin . " and timestamp < " . $end, __FILE__, __LINE__, false);
			$c = & $handle->fetch($result);
			$databary[] = $c[0];
		}
$end1 = microtime_float();
//print("temps total d'execution : " . (($end1-$start1)*1000) . 'ms<br />');
		include("StatsPerYears.php");
	}
	else
	{
		if ($Month == 0)
		{
$start1 = microtime_float();
			for($i = 1; $i <= 12; $i++)
			{
				$begin = mktime(0,0,0,$i,1,$Year); $end = mktime(0,0,0,$i+1,1,$Year);
				$result = & $handle->query($query . " timestamp >= " . $begin . " and timestamp < " . $end, __FILE__, __LINE__);
				$c = & $handle->fetch($result);
				$databary[] = $c[0];
			}
$end1 = microtime_float();
//print("temps total d'execution : " . (($end1-$start1)*1000) . 'ms<br />');
			include("StatsPerMonths.php");
		}
		else
		{
			if ($Day == 0)
			{
				$nbdays = (mktime(0,0,0,$Month+1,1,$Year) - mktime(0,0,0,$Month,1,$Year))/86400;
				$DayLabels = range(1, $nbdays);
$start1 = microtime_float();
				for($i = 1; $i <= $nbdays; $i++)
				{
					$begin = mktime(0,0,0,$Month,$i,$Year); $end = mktime(0,0,0,$Month,$i+1,$Year);
					$result = & $handle->query($query . " timestamp >= " . $begin . " and timestamp < " . $end, __FILE__, __LINE__);
					$c = & $handle->fetch($result);
					$databary[] = $c[0];
				}
$end1 = microtime_float();
//print("temps total d'execution : " . (($end1-$start1)*1000) . 'ms<br />');
				include("StatsPerDays.php");
			}
			else
			{
$start1 = microtime_float();
				for($i = 0; $i < 24; $i++)
				{
					$begin = mktime($i,0,0,$Month,$Day,$Year); $end = mktime($i+1,0,0,$Month,$Day,$Year);
					$result = & $handle->query($query . " timestamp >= " . $begin . " and timestamp < " . $end, __FILE__, __LINE__);
					$c = & $handle->fetch($result);
					$databary[] = $c[0];
				}
$end1 = microtime_float();
//print("temps total d'execution : " . (($end1-$start1)*1000) . 'ms<br />');
				include("StatsPerHours.php");
			}
		}
	}

	$graph->Stroke();

}


?>