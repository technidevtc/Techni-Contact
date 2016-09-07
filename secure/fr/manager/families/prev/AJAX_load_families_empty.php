<?php

	if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../../../config.php';
	}else{
		require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
	}
		
	require ADMIN."statut.php";

	$user = new BOUser();

	//header("Content-Type: text/plain; charset=utf-8");

	if (!$user->login()) {
	  $o["error"] = "Votre session a expiré, veuillez vous identifier à nouveau après avoir rafraîchi votre page";
	  mb_convert_variables("UTF-8","ASCII,UTF-8,ISO-8859-1,CP1252",$o);
	  print json_encode($o);
	  exit();
	}

	$db = DBHandle::get_instance();

	$family_empty_results_array = array();
	$res_family_empty = $db->query("SELECT 
											f.id, f.idParent, fr.name, 
											fr.ref_name

										FROM 
											families f, 
											families_fr fr
											

										WHERE 
											f.id = fr.id
										AND
											idParent!=0
										AND
											f.id NOT IN (
													SELECT
														f.idParent
													FROM
														families f
													)
										AND
											f.id NOT IN (
													SELECT
														DISTINCT(products_f.idFamily)
													FROM
														products_families products_f
												)

										ORDER BY f.idParent, fr.name ASC
										");
	echo ('&nbsp;&nbsp;Total ('.$db->numrows($res_family_empty).')');									
	$previous_family_second_level	= '#';
	while($family_empty_results_array = $db->fetchAssoc($res_family_empty)){
	
		if($previous_family_second_level!=$family_empty_results_array['idParent']){
			
			
			//close the last one and open a new one
			//if it's the first one do not close the last one !
			if(strcmp($previous_family_second_level,'#')!='0'){
				echo('</div>');
			}
			
			$previous_family_second_level=$family_empty_results_array['idParent'];
			
			echo('<div class="family_second_level_container">');

				$family_empty_second_level_results_array = array();
				$res_family_empty_second_level = $db->query("SELECT 
																fr.name
															FROM 
																families_fr fr
															WHERE 
																fr.id = ".$family_empty_results_array['idParent']."
													
															");
													
				
				$family_empty_second_level_results_array = $db->fetchAssoc($res_family_empty_second_level);
				
				echo('<div class="family_second_level">');
					echo('<span>');
						echo($family_empty_second_level_results_array['name']);
					echo('</span>');
				echo('</div>');
				
				//$previous_family_second_level	= '';
		}
		
		echo('<div class="family_third_level">');
			echo('<a href="/fr/manager/search.php?search_type=2&search='.$family_empty_results_array['id'].'" target="_blank">');
				echo($family_empty_results_array['name']);
			echo('</a>');
		echo('</div>');
			
			
	}
	
	echo('</div>'); //end .family_second_level
	
	/*
	
	
			
	
	
		$previous_family_second_level	= '';
		while(fetch){
		
			if($previous_family_second_level!=$array_actual_row['id_nd_family']){
			
				SELECT informations of this one and show the name and ID
				
				Open the div of the .family_second
			
			}
			
			Open the div of the .family_third
			
				Show the informations name and ID 
				
			Close the div of the .family_third
			
		
		}
		
		//Close the div of the .family_second
	
	*/
?>