<?php

/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 13 février 2006

 Fichier : /secure/manager/families/FamiliesSearch.php
 Description : Fichier interface de recherche des familles AJAX

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN."logs.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

if(!$user->login())
{
	header('Location: ' . ADMIN_URL . 'login.html');
	exit();
}
if (!$user->get_permissions()->has("m-prod--sm-export","r")) {
  header('Location: export.php?error=permissions');
  exit();
}

require('_ClassExport.php');

$idexport = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (isset($_GET['flow_type']))
{
	$flow_type = strtolower($_GET['flow_type']);
	switch($flow_type)
	{
		case "xml" :
		case "csv" :
		case "txt" : break;
		default : $flow_type = "xml";
	}
}
else $flow_type = "xml";

if ($idexport != 0)
{
	$exp = & new Export($handle, $_GET['id']);
	if ($exp->exist)
	{
		if ($exp->LoadAllProducts())
		{
			$cf_keys = array_merge($exp->c_keys, $exp->f_keys);
			switch($flow_type)
			{
				case "xml" :
					$os = "<" . '?xml version="1.0" encoding="ISO-8859-1"?' . ">\n";
					$os .= "<produits>\n";
					foreach($exp->products as $pdt)
					{
						// TODO intégrer dans les réglages du partenaire un arbre type (nom des tag)
						$os .= " <produit>\n";
						foreach($cf_keys as $key)
						{
							$os .= "  <" . $key . ">";
							if (is_array($pdt[$key]))
							{
								foreach($pdt[$key] as $ckey => $cval)
								{
									$os .= "\n";
									$os .= "   <" . $ckey . ">";
									$os .= (!preg_match("/^[0-9]+(\,|\.[0-9]+)?$/", $cval) ? "<![CDATA[" . substr($cval,0,980) . "]]>" : $cval);
									$os .= "</" . $ckey . ">";
								}
								$os .= "\n";
								$os .= "  ";
							}
							else $os .= (!preg_match("/^[0-9]+(\,|\.[0-9]+)?$/", $pdt[$key]) ? "<![CDATA[" . substr($pdt[$key],0,980) . "]]>" : $pdt[$key]);
							$os .= "</" . $key . ">\n";
						}
						$os .= " </produit>\n";
					}
					$os .= "</produits>\n";
					
					break;
				
				case "csv" : if (!isset($separator)) $separator = ";";
				case "txt" : if (!isset($separator)) $separator = "	";
					$os = "";
					$keylist = array();
					
					// Getting the statics fields to preserve the order
					foreach($cf_keys as $key)
					{
						$keylist[$key] = true;
					}
					
					// Parsing all products to find all custom/dynamic fields
					foreach($exp->products as $pdt)
					{
						foreach($cf_keys as $key)
						{
							if (is_array($pdt[$key])) // has custom/dynamic fields
							{
								if (!is_array($keylist[$key])) $keylist[$key] = array();
								foreach($pdt[$key] as $ckey => $cvalue) $keylist[$key][$ckey] = true;
							}
						}
					}
					
					$k = 0;
					foreach($keylist as $key => $val)
					{
						if (is_array($val))
							foreach($val as $ckey => $cval)
								$os .= (($k > 0) ? $separator : "") . '"' . str_replace('"', '""', $ckey) . '"';
						else
							$os .= (($k > 0) ? $separator : "") . '"' . str_replace('"', '""', $key) . '"';
						$k++;
					}
					$os .= "\n";
					
					foreach($exp->products as $pdt)
					{
						$k = 0;
						foreach($keylist as $key => $val)
						{
							if (is_array($val))
								foreach($val as $ckey => $cval)
									$os .= (($k > 0) ? $separator : "") . '"' . str_replace('"', '""', isset($pdt[$key][$ckey]) ? $pdt[$key][$ckey] : "") . '"';
							else
								$os .= (($k > 0) ? $separator : "") . '"' . str_replace('"', '""', $pdt[$key]) . '"';
							$k++;
						}
						$os .= "\n";
					}
					break;
			}
			
			$exp->generate_time = time();
			$exp->Save();
			header("Content-disposition: attachment; filename=export_" . $_GET['id'] . "." . $flow_type);
			header("Content-Type: application/force-download");
			header("Content-Transfer-Encoding: text/plain\n"); // Surtout ne pas enlever le \n
			header("Content-Length: " . strlen($os));
			header("Pragma: no-cache, public");
			header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0, public, maxage=3600");
			//header("Cache-Control: maxage=3600"); //Adjust maxage appropriately
			//header("Pragma: public");
			header("Expires: 0");
			
			print $os;
		}
		else
		{
			header('Location: export.php?error=loadproducts');
			exit();
		}
	}
	else
	{
		header('Location: export.php');
		exit();
	}
}
else
{
	header('Location: export.php');
	exit();
}

?>
