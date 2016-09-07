<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ICLASS . 'ManagerUser.php');

$handle = DBHandle::get_instance();
$user   = & new ManagerUser($handle);

$db = DBHandle::get_instance();

$path_parts = pathinfo(CONTENT_PATH."local_constants/*");
$dir = $path_parts['dirname']."/";
$files = scandir($path_parts['dirname']);
$files_count = count($files);

if(!$user->login()) {
	header('Location: ' . ADMIN_URL . 'login.html');
	exit();
}

?>
<html>
<head>
<title>Création de fichiers de constantes PHP à partir de documents xls</title>
</head>
<body>
<?php
foreach($files as $file) {
	if (!is_file($dir.$file) || !preg_match('/.xls$/', $file)) {
		print "Erreur fatale lors de la lecture du fichier " . $file . "<br/>\n";
	}
	else {
		require_once SECURE_PATH."manager/import/Excel/reader.php";
			
		// ExcelFile($filename, $encoding);
		$data = new Spreadsheet_Excel_Reader();
		$data->setOutputEncoding("CP1252");
		$data->read($dir.$file);
		
		error_reporting(E_ALL ^ E_NOTICE);
		
		print "Fichier " . $file . " :<br/>\n";
		
		$hi = array(); // Header Index
		for ($i = 1; $i <= $data->sheets[0]['numCols']; $i++) {
			$header = Utils::toDashAz09($data->sheets[0]['cells'][1][$i]);
			switch($header) {
				case "constants" :
				case "constant-list" :
				case "constantes" :
				case "liste-des-constantes" :
				case "liste-constantes" : $hi['cst'] = $i; break;
				
				/*case "anglais" :
				case "english" :
				case "allemand" :
				case "deutch" :
				case "italien" :
				case "italian" :
				case "espagnol" :
				case "espanol" :*/
				case "francais" :
				case "french" : $hi['trad'] = $i; break;
				
				default : $hi['mixed_data_entitle'][$header] = $i; break;
			}
		}

		if (!isset($hi['cst']) || !isset($hi['trad'])) {
			print "- Libellé colonnes incorrect : Abandon<br/>\n";
		}
		else {
			print "- Libellé colonnes OK<br/>\n";
			
			$fh = fopen(substr($dir.$file, 0, -3) . "php", "wb");
			
			fwrite($fh, "<?php\n");
			
			$start_ok = false;
			for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
				$cst = $data->sheets[0]['cells'][$i][$hi['cst']];
				$trad = $data->sheets[0]['cells'][$i][$hi['trad']];
				
				if (!$start_ok && preg_match('/^[[:space:]]*\/\*.*\*\/[[:space:]]*$/', $cst)) $start_ok = true;
				
				if (preg_match('/^[[:space:]]*\/\*.*\*\/[[:space:]]*$/', $cst)) {
					fwrite($fh, $cst . "\n");
				}
				elseif($start_ok) {
					if (empty($cst) || empty($trad)) {
						fwrite($fh, "\n");
					}
					else {
						$cst = mb_strtoupper($cst);
						//$trad = to_entities($trad, ENT_NOQUOTES);
						$trad = str_replace('"', '\"', $trad);
						fwrite($fh, 'define("' . $cst . '", "' . $trad . '");' . "\n");
					}
				}
				
			}
			
			fwrite($fh, "\n\n?>");
			fclose($fh);
			
			print "-> Ecriture effectuée avec succés<br/>\n";
		}
	}
	print "<br/>\n";
}
?>
</body>
</html>
