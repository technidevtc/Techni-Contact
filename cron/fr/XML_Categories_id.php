<?php

if ($res = $db->query("show table status from `technico-test` like 'families'", __FILE__, __LINE__)) {
	$status = $db->fetchAssoc($res);

	//print "DB update Time = " . strtotime($status["Update_time"]) . "\n";
	//print XML_CATEGORIES_ALL . " last mod = " . filemtime(XML_CATEGORIES_ALL). "\n";
	
	if (!file_exists(XML_CATEGORIES_ALL) || filemtime(XML_CATEGORIES_ALL) < (strtotime($status["Update_time"])+60000000)) {
		$categories = array();
		
		// Root Family, necessary to get the main families
		$categories[0] = array();
		
		// ID = 0, Name = 1, Ref Name = 2, Parent ID = 3
		$res = $db->query("
			SELECT
				f.id, f.idParent,
				fr.name, fr.ref_name, fr.pdt_pic, fr.pdt_selection, fr.pdt_favourite, fr.pdt_viewed, fr.pdt_latest,
				count(pf.idProduct) as pdt_count,
				(" . mktime(0,0,0) . " - avg(ps.first_hit_time)) as age_avg, sum(ps.hits) as hits, sum(ps.leads) as leads, sum(ps.orders) as orders, sum(ps.estimates) as estimates
			FROM
				families f
			INNER JOIN families_fr fr ON f.id = fr.id
			LEFT JOIN products_families pf ON f.id = pf.idFamily
			LEFT JOIN products_stats ps ON pf.idProduct = ps.id
			GROUP BY f.id
			ORDER BY
				fr.name", __FILE__, __LINE__);
		//print $db->get_last_query();
		
		while ($row = $db->fetchAssoc($res)) {
			$categories[$row["id"]]["id"] = $row["id"];
			$categories[$row["id"]]["name"] = htmlspecialchars($row["name"]);
			$categories[$row["id"]]["ref_name"] = $row["ref_name"];
			$categories[$row["id"]]["idParent"] = $row["idParent"];
			$categories[$row["id"]]["pdt_count"] = (int)$row["pdt_count"];
			$categories[$row["id"]]["hits"] = (int)$row["hits"];
			$categories[$row["id"]]["leads"] = (int)$row["leads"];
			$categories[$row["id"]]["orders"] = (int)$row["orders"];
			$categories[$row["id"]]["estimates"] = (int)$row["estimates"];
			$categories[$row["id"]]["age_avg"] = (int)$row["age_avg"];
			$categories[$row["id"]]["pdt_pic"] = $row["pdt_pic"];
			$categories[$row["id"]]["pdt_selection"] = $row["pdt_selection"];
			$categories[$row["id"]]["pdt_favourite"] = $row["pdt_favourite"];
			$categories[$row["id"]]["pdt_viewed"] = $row["pdt_viewed"];
			$categories[$row["id"]]["pdt_latest"] = $row["pdt_latest"];
			$categories[$row["idParent"]]["children"][] = $row["id"];
		}
		
		mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $categories);
		$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		$xml .= "<!DOCTYPE categories SYSTEM \"categories.dtd\">\n";
		
		$cat0ID = 0;
		if (isset($categories[0]["children"])) {
			$cat0_inner_xml = "";
			$cat0 = array(
				"pdt_count" => 0,
				"hits" => 0,
				"leads" => 0,
				"orders" => 0,
				"estimates" => 0,
				"age_avg" => 0,
				"cumul_age" => 0,
				"cat3_count" => 0,
				"cat2_count" => 0,
				"cat1_count" => 0);
			foreach($categories[0]["children"] as $cat1ID) {
				
				if (isset($categories[$cat1ID]["children"])) {
					$cat1_inner_xml = "";
					$cat1 = array(
						"pdt_count" => 0,
						"hits" => 0,
						"leads" => 0,
						"orders" => 0,
						"estimates" => 0,
						"age_avg" => 0,
						"cumul_age" => 0,
						"cat3_count" => 0,
						"cat2_count" => 0);
					foreach ($categories[$cat1ID]["children"] as $cat2ID) {
						
						if (isset($categories[$cat2ID]["children"])) {
							$cat2_inner_xml = "";
							$cat2 = array(
								"pdt_count" => 0,
								"hits" => 0,
								"leads" => 0,
								"orders" => 0,
								"estimates" => 0,
								"age_avg" => 0,
								"cumul_age" => 0,
								"cat3_count" => 0);
							foreach ($categories[$cat2ID]["children"] as $cat3ID) {
								$cat3 = array(
									"pdt_count" => $categories[$cat3ID]["pdt_count"],
									"hits" => $categories[$cat3ID]["hits"],
									"leads" => $categories[$cat3ID]["leads"],
									"orders" => $categories[$cat3ID]["orders"],
									"estimates" => $categories[$cat3ID]["estimates"],
									"age_avg" => (int)$categories[$cat3ID]["age_avg"]);
								if ($cat3["pdt_count"] > 0) {
									$cat2_inner_xml .=
										"   <category key=\"".XML_KEY_PREFIX.$cat3ID."\" id=\"".$cat3ID."\" name=\"".$categories[$cat3ID]["name"]."\" ref_name=\"".$categories[$cat3ID]["ref_name"]."\">\n".
										"    <stats>".implode("|",$cat3)."</stats>\n".
										(!empty($categories[$cat3ID]["pdt_selection"]) ?
										"    <pdt_selection>".$categories[$cat3ID]["pdt_selection"]."</pdt_selection>\n" : "");
									$cat2_inner_xml .= "   </category>\n";
									$cat2["pdt_count"] += $cat3["pdt_count"];
									$cat2["hits"] += $cat3["hits"];
									$cat2["leads"] += $cat3["leads"];
									$cat2["orders"] += $cat3["orders"];
									$cat2["estimates"] += $cat3["estimates"];
									$cat2["cumul_age"] += $cat3["age_avg"] * $cat3["pdt_count"];
									$cat2["cat3_count"]++;
								}
							}
							
							if ($cat2["pdt_count"] > 0) {
								$cat2["age_avg"] = (int)($cat2["cumul_age"] / $cat2["pdt_count"]);
								unset($cat2["cumul_age"]);
								$cat1_inner_xml .=
									"  <category key=\"".XML_KEY_PREFIX.$cat2ID."\" name=\"".$cat2ID."\" id=\"".$categories[$cat2ID]["name"]."\" ref_name=\"".$categories[$cat2ID]["ref_name"]."\">\n".
									"   <stats>".implode("|",$cat2)."</stats>\n".
									(!empty($categories[$cat2ID]["pdt_pic"]) ?
									"   <pdt_pic>".$categories[$cat2ID]["pdt_pic"]."</pdt_pic>\n" : "").
									(!empty($categories[$cat2ID]["pdt_selection"]) ?
									"   <pdt_selection>".$categories[$cat2ID]["pdt_selection"]."</pdt_selection>\n" : "").
									(!empty($categories[$cat2ID]["pdt_favourite"]) ?
									"   <pdt_favourite>".$categories[$cat2ID]["pdt_favourite"]."</pdt_favourite>\n" : "").
									(!empty($categories[$cat2ID]["pdt_viewed"]) ?
									"   <pdt_viewed>".$categories[$cat2ID]["pdt_viewed"]."</pdt_viewed>\n" : "").
									(!empty($categories[$cat2ID]["pdt_latest"]) ?
									"   <pdt_latest>".$categories[$cat2ID]["pdt_latest"]."</pdt_latest>\n" : "");
								$cat1_inner_xml .= $cat2_inner_xml;
								$cat1_inner_xml .= "  </category>\n";
								$cat1["pdt_count"] += $cat2["pdt_count"];
								$cat1["hits"] += $cat2["hits"];
								$cat1["leads"] += $cat2["leads"];
								$cat1["orders"] += $cat2["orders"];
								$cat1["estimates"] += $cat2["estimates"];
								$cat1["cumul_age"] += $cat2["age_avg"] * $cat2["pdt_count"];
								$cat1["cat2_count"]++;
								$cat1["cat3_count"] += $cat2["cat3_count"];
							}
						}
					}
					
					if ($cat1["pdt_count"] > 0) {
						$cat1["age_avg"] = (int)($cat1["cumul_age"] / $cat1["pdt_count"]);
						unset($cat1["cumul_age"]);
						$cat0_inner_xml .= 
							" <category key=\"".XML_KEY_PREFIX.$cat1ID."\" id=\"".$cat1ID."\" name=\"".$categories[$cat1ID]["name"]."\" ref_name=\"".$categories[$cat1ID]["ref_name"]."\">\n".
							"  <stats>".implode("|",$cat1)."</stats>\n".
							(!empty($categories[$cat1ID]["pdt_pic"]) ?
							"  <pdt_pic>".$categories[$cat1ID]["pdt_pic"]."</pdt_pic>\n" : "").
							(!empty($categories[$cat1ID]["pdt_selection"]) ?
							"  <pdt_selection>".$categories[$cat1ID]["pdt_selection"]."</pdt_selection>\n" : "").
							(!empty($categories[$cat1ID]["pdt_favourite"]) ?
							"  <pdt_favourite>".$categories[$cat1ID]["pdt_favourite"]."</pdt_favourite>\n" : "").
							(!empty($categories[$cat1ID]["pdt_viewed"]) ?
							"  <pdt_viewed>".$categories[$cat1ID]["pdt_viewed"]."</pdt_viewed>\n" : "").
							(!empty($categories[$cat1ID]["pdt_latest"]) ?
							"  <pdt_latest>".$categories[$cat1ID]["pdt_latest"]."</pdt_latest>\n" : "");
						$cat0_inner_xml .= $cat1_inner_xml;
						$cat0_inner_xml .= " </category>\n";
						$cat0["pdt_count"] += $cat1["pdt_count"];
						$cat0["hits"] += $cat1["hits"];
						$cat0["leads"] += $cat1["leads"];
						$cat0["orders"] += $cat1["orders"];
						$cat0["estimates"] += $cat1["estimates"];
						$cat0["cumul_age"] += $cat1["age_avg"] * $cat1["pdt_count"];
						$cat0["cat1_count"]++;
						$cat0["cat2_count"] += $cat1["cat2_count"];
						$cat0["cat3_count"] += $cat1["cat3_count"];
					}
				}
			}
			
			if ($cat0["pdt_count"] > 0) {
				$cat0["age_avg"] = (int)($cat0["cumul_age"] / $cat0["pdt_count"]);
				unset($cat0["cumul_age"]);
				$xml .= 
					"<categories key=\"".XML_KEY_PREFIX.$cat0ID."\" id=\"".$cat0ID."\">\n".
					" <stats_key>".implode("|",array_keys($cat0))."</stats_key>\n".
					" <stats>".implode("|",$cat0)."</stats>\n";
				$xml .= $cat0_inner_xml;
				$xml .= "</categories>\n";
			}
		}
		
		$fh = fopen(XML_CATEGORIES_ALL, "w+");
		fwrite($fh, $xml);
		fclose($fh);
		
	}
}

?>