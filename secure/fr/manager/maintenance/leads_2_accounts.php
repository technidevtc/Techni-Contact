<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require_once(ICLASS."ManagerUser.php");

$handle = DBHandle::get_instance();
$user = new ManagerUser($handle);

if (!$user->login()) {
	print "not logged";
	exit();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>Leads To Account creation script</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style type="text/css">
		body pre { font: normal 10px Lucida Console,arial,sans-serif; line-height: 4px; color: grey }
		body b { font-weight: normal; color: blue }
	</style>
</head>
<body>
	<pre>
<?php
$db = DBHandle::get_instance();

$time_current = time();
$time_interval = 86400*30; // if historic, 90 days
$time_origin = mktime(0,0,0,1,1,2000); // if historic, origin = 01/01/2000 00:00:00
$time_start = $time_origin;
$time_end = $time_origin + $time_interval;
$cc = 0; // Contact Count
$acc = 0; // Account Creation Count
$auc = 0; // Account Update Count
$aaec = 0; // Account Already Existing Count

echo "Disabling clients table keys...<br/>\n<br/>\n";
$db->query("ALTER TABLE `clients` DISABLE KEYS", __FILE__, __LINE__);

while ($time_start <= $time_current) {
	// sql query
	$res = $db->query("SELECT * FROM contacts c WHERE c.timestamp >= ".$time_start." AND c.timestamp < ".$time_end, __FILE__, __LINE__);
	
	while($infos = $db->fetchAssoc($res)) {
		echo "Contact <b>".sprintf("%6d",$cc)."</b>: ID=<b>".sprintf("%8d",$infos["id"])."</b> Date=<b>".date("Y-m-d H:i:s",$infos["timestamp"])."</b> Email=<b>".sprintf("%-50s",$infos["email"])."</b>    ";
		$origin = CustomerUser::getCustomerOriginFromLogin($infos["email"], $handle);
		if (!$origin || $origin == 'L') {
			$user = new CustomerUser($handle);
			$accinfos = array(
				"coord_livraison" => 0,
				"login" => $infos["email"],
				"titre" => 1,
				"nom" => $infos["nom"],
				"prenom" => $infos["prenom"],
				"fonction" => $infos["fonction"],
				"societe" => $infos["societe"],
				"nb_salarie" => $infos["salaries"],
				"secteur_activite" => $infos["secteur"],
				"code_naf" => $infos["naf"],
				"num_siret" => $infos["siret"],
				"adresse" => $infos["adresse"],
				"complement" => $infos["cadresse"],
				"ville" => $infos["ville"],
				"cp" => $infos["cp"],
				"pays" => $infos["pays"],
				"infos_sup" => "",
				"tel1" => $infos["telephone"],
				"fax1" => $infos["fax"],
				"titre_l" => 1,
				"nom_l" => $infos["nom"],
				"prenom_l" => $infos["prenom"],
				"societe_l" => $infos["societe"],
				"adresse_l" => $infos["adresse"],
				"complement_l" => $infos["cadresse"],
				"ville_l" => $infos["ville"],
				"cp_l" => $infos["cp"],
				"pays_l" => $infos["pays"],
				"infos_sup_l" => "",
				"tel2" => $infos["telephone"],
				"fax2" => $infos["fax"],
				"url" => $infos["url"],
				"actif" => 0,
				"email" => $infos["email"],
				"origin" => "L");
			if (!$origin) {
				echo "--> No account with this email, creating one...    ";
				$user->create();
				$user->setCoordFromArray($accinfos);
				$user->save($infos["timestamp"]);
				echo "<span style=\"color: green\">--> Account creation Complete !</span> Total : <b>".$acc."</b>";
				$acc++;
			}
			else {
				echo "--> Account already exists but is from lead origin, updating it with latest clients infos...    ";
				$user->id = CustomerUser::getCustomerIdFromLogin($infos["email"], $handle);
				$user->load();
				$user->setCoordFromArray($accinfos);
				$user->save($infos["timestamp"]);
				echo "<span style=\"color: green\">--> Account update Complete !</span> Total : <b>".$auc."</b>";
				$auc++;
			}
		}
		else {
			echo "<span style=\"color: red\">--> Account already exists !</span> Total : <b>".$aaec."</b>";
			$aaec++;
		}
		echo "\n<br/>";
		$cc++;
	}
	
	$time_start += $time_interval;
	$time_end += $time_interval;
}

echo "Enabling clients table keys..<br/>\n<br/>\n";
$db->query("ALTER TABLE `clients` ENABLE KEYS", __FILE__, __LINE__);

?>
	</pre>
</body>
</html>