<?php

if ($res = $db->query("show table status from `technico-test` like 'families'", __FILE__, __LINE__)) {
	$status = $db->fetchAssoc($res);

	//print "DB update Time = " . strtotime($status["Update_time"]) . "\n";
	//print XML_CATEGORIES_SIMPLE . " last mod = " . filemtime(XML_CATEGORIES_SIMPLE). "\n";
	
	if (!file_exists(XML_CATEGORIES_SIMPLE) || filemtime(XML_CATEGORIES_SIMPLE) < (strtotime($status["Update_time"])+60000000)) {
		$categories = array();
		
		// Root Family, necessary to get the main families
		$categories[0] = array();
		
		// ID = 0, Name = 1, Ref Name = 2, Parent ID = 3
		$res = $db->query("
			SELECT
				f.id, f.idParent,
				fr.name, fr.ref_name,
				count(pf.idProduct) as pdtCount
			FROM
				families f
			INNER JOIN families_fr fr ON f.id = fr.id
			LEFT JOIN products_families pf ON f.id = pf.idFamily
			GROUP BY f.id
			ORDER BY
				fr.name", __FILE__, __LINE__);
		while ($row = $db->fetchAssoc($res)) {
			$categories[$row["id"]]["id"] = $row["id"];
			$categories[$row["id"]]["name"] = htmlspecialchars($row["name"]);
			$categories[$row["id"]]["ref_name"] = $row["ref_name"];
			$categories[$row["id"]]["idParent"] = $row["idParent"];
			$categories[$row["id"]]["pdtCount"] = $row["pdtCount"];
			$categories[$row["idParent"]]["children"][] = $row["id"];
		}
		
		mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $categories);
		$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		
		if (isset($categories[0]["children"])) {
			$cat0_inner_xml = "";
			$cat0_pdtCount = 0;
			foreach($categories[0]["children"] as $cat1ID) {
				
				if (isset($categories[$cat1ID]["children"])) {
					$cat1_inner_xml = "";
					$cat1_pdtCount = 0;
					foreach ($categories[$cat1ID]["children"] as $cat2ID) {
						
						if (isset($categories[$cat2ID]["children"])) {
							$cat2_inner_xml = "";
							$cat2_pdtCount = 0;
							foreach ($categories[$cat2ID]["children"] as $cat3ID) {
								$cat3_pdtCount = (int)$categories[$cat3ID]["pdtCount"];
								//$res = $db->query("select count(idProduct) from products_families where idFamily = " . $cat3ID, __FILE__, __LINE__);
								//list($cat3_pdtCount) = $db->fetch($res);
								if ($cat3_pdtCount > 0) {
									$cat2_inner_xml .= "   <category id=\"" . $cat3ID . "\" name=\"" . $categories[$cat3ID]["name"] . "\" ref_name=\"" . $categories[$cat3ID]["ref_name"] . "\" pdt_count=\"" . $cat3_pdtCount . "\"/>\n";
									$cat2_pdtCount += $cat3_pdtCount;
								}
							}
							
							if ($cat2_pdtCount > 0) {
								$cat1_inner_xml .= "  <category id=\"" . $cat2ID . "\" name=\"" . $categories[$cat2ID]["name"] . "\" ref_name=\"" . $categories[$cat2ID]["ref_name"] . "\" pdt_count=\"" . $cat2_pdtCount . "\">\n";
								$cat1_inner_xml .= $cat2_inner_xml;
								$cat1_inner_xml .= "  </category>\n";
								$cat1_pdtCount += $cat2_pdtCount;
							}
						}
					}
					
					if ($cat1_pdtCount > 0) {
						$cat0_inner_xml .= " <category id=\"" . $cat1ID . "\" name=\"" . $categories[$cat1ID]["name"] . "\" ref_name=\"" . $categories[$cat1ID]["ref_name"] . "\" pdt_count=\"" . $cat1_pdtCount . "\">\n";
						$cat0_inner_xml .= $cat1_inner_xml;
						$cat0_inner_xml .= " </category>\n";
						$cat0_pdtCount += $cat1_pdtCount;
					}
				}
			}
			
			if ($cat0_pdtCount > 0) {
				$xml .= "<categories pdt_count=\"" . $cat0_pdtCount . "\">\n";
				$xml .= $cat0_inner_xml;
				$xml .= "</categories>\n";
			}
		}
		
		$fh = fopen(XML_CATEGORIES_SIMPLE, "w+");
		fwrite($fh, $xml);
		fclose($fh);
		
	}
}

?>