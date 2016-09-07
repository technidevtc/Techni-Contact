<?php

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = DBHandle::get_instance();

define("STATS_LOG" , STATS_PATH."stats-".DB_LANGUAGE."-technico-access_log");

function unparse_url($parsed_url) { 
  $campaignID   = isset($parsed_url['campaignID']) ? $parsed_url['campaignID'] . '://' : '';  
  return "$campaignID"; 
}

/*function microtime_float()
{
  list($usec, $sec) = explode(" ", microtime());
  return ((float)$usec + (float)$sec%1000);
}*/

//$data      = array();
$yesterday = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
$pap       = 0;

//$start1 = microtime_float();

if ($f = fopen(STATS_LOG, 'r')) {
	
	
	//print "Désactivation des clés de la table...<br />\n";
	//$db->query("alter table stats_hit disable keys", __FILE__, __LINE__);
	//$end10 = microtime_float();
	//print "temps partiel d'execution : " . (($end10-$start1)*1000) . "ms<br />\n";
	
	while ($l = fgets($f, 4096)) {
		
		//Ligne type : ident - - [DD/mmm/YYYY:HH:MM:SS +0100] "GET /?idProduct-timestamp-idAdvertiser-idFamily-idTC HTTP/1.1" 200 0 "referer_url" "type_browser"
		$tab = explode('GET /?', $l); // idProduct-timestamp-idAdvertiser-idFamily-idTC HTTP/1.1" 200 0 "referer_url" "type_browser"
		$ip = explode('.', $l);
		$ip1 =$ip[0]; 
		$ip2 =$ip[1]; 
		$ip3 =$ip[2]; 
		$ip4 =$ip[3]; 
		
		
		$ip_4 		= explode(' ',$ip4);		
		$ip_final   = $ip1.'.'.$ip2.'.'.$ip3.'.'.$ip_4[0];
		$urli = explode('http://www.techni-contact.com/produits/',$l);
		$urli_finaly = explode('"',$urli[1]);
		//echo $urli_finaly[0].'  ';
		
		$url = "http://www.techni-contact.com/produits/".$urli_finaly[0];
		$query_str = parse_url($url, PHP_URL_QUERY);
		parse_str($query_str, $query_params);
				
		/*
		$exploded = array();
		parse_str($urli_finaly[0], $exploded);
		$finaly_id_com = str_replace('#product-desc','',$exploded['campaignID']);
		if(!empty($finaly_id_com)){
		$campaignID_final =  $finaly_id_com;
		}
		 */
		$tab2 = explode(' ', $tab[1]); // idProduct-timestamp-idAdvertiser-idFamily-idTC
		
		
		if (!preg_match('/^[0-9]{1,8}\-[0-9]{9,10}\-[0-9]{1,5}\-[0-9]{1,4}\-[0-9]{1,10}$/', $tab2[0])) continue;
		$pap++;
		$pdt = explode('-', $tab2[0]);
		$sql_insert = "insert into stats_hit (idProduct, idTC, idAdvertiser, idFamily, timestamp,adresse_ip,campaignID) 
						values (" . $pdt[0] . ", " . $pdt[4] . ", " . $pdt[2] . ", " . $pdt[3] . ", " . $pdt[1] . ",'".$ip_final."','".$query_params['campaignID']."' ) ";		
		mysql_query($sql_insert);
		//if (isset($data[$pdt[0]])) $data[$pdt[0]]++;
		//else $data[$pdt[0]] = 1;
	}
	
	fclose($f);
	
	//print "pseudo stats générées !<br />\n";
	//$end11 = microtime_float();
	//print "temps partiel d'execution : " . (($end11-$start1)*1000) . "ms<br />\n";
	
	//print "Réactivation des clés de la table...<br />\n";
	//$db->query("alter table stats_hit enable keys", __FILE__, __LINE__);
	//$end12 = microtime_float();
	//print "temps total d'execution : " . (($end12-$start1)*1000) . "ms<br />\n";
	
	//print "Optimisation de la table...<br />";
	$db->query("optimize table stats_hit", __FILE__, __LINE__);
	//$end13 = microtime_float();
	//print "temps total d'execution : " . (($end13-$start1)*1000) . "ms<br />\n";
}

//print "Lecture fichier log terminée ! $pap lignes prises en compte<br />\n";
//$end1 = microtime_float();
//print "temps partiel d'execution : " . (($end1-$start1)*1000) . "ms<br />\n";
/*
// Anciennes stats V2
if ($result = & $db->query("select day from stats_pap where day = " . $yesterday, __FILE__, __LINE__))
{
	if ($db->numrows($result, __FILE__, __LINE__)) // Pour les tests
		$query = "update stats_pap set number = " . $pap . " where day = " . $yesterday;
	else
		$query = "insert into stats_pap (day, number) values(" . $yesterday . ", " . $pap . ")";

	$db->query($query, __FILE__, __LINE__);
}

//print "Mise à jour stats_pap terminée !<br />";
//$end2 = microtime_float();
//print("temps partiel d'execution : " . (($end2-$start1)*1000) . 'ms<br />');

$n = 0;
foreach($data as $pdtID => $nblp)
{
	if ($result = & $db->query("select data from stats_products where id = " . $pdtID, __FILE__, __LINE__))
	{
		if ($db->numrows($result, __FILE__, __LINE__))
		{
			$tab = & $db->fetch($result);
			$tab = unserialize($tab[0]);
			$tab[$yesterday] = $nblp;
			$query = "update stats_products set data = '" . $db->escape(serialize($tab)) . "' where id = " . $pdtID;
		}
		else
		{
			$tab = array($yesterday => $nblp);
			$query = "insert into stats_products (id, data) values(" . $pdtID . ", '" . $db->escape(serialize($tab)) . "')";
		}
		$db->query($query, __FILE__, __LINE__);
		$n++;
	}
}
*/
//print "Mise à jour stats_products terminée ! $n mise à jour effectuées<br />";
//$end3 = microtime_float();
//print("temps partiel d'execution : " . (($end3-$start1)*1000) . 'ms<br />');

?>

