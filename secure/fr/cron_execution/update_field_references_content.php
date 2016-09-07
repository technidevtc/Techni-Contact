<?php

require_once('../../../config.php');

	//Instantiate the call of the DB
	$db = DBHandle::get_instance();
	
echo('Start on '.date('d/m/Y H:i:s').'<br />');
echo('<br />Start From '.$start_from);

	$start_from					= mysql_escape_string($_GET['start_from']);
	$count_update				= 0;

	if(empty($start_from)){
		$start_from = 0;
	}
	
echo('Start on '.date('d/m/Y H:i:s'));
echo('<br />Start From <strong>'.$start_from.'</strong>');
	
//Get the updates
	$res_get_elements_req		= "SELECT 
										id,
										field,
										value,
										id_tc,
										date_operation
										
									FROM
										_operation_update_references_content
									WHERE
										id>".$start_from."";
											
		$res_get_elements 		= $db->query($res_get_elements_req, __FILE__, __LINE__);

echo('<br /><strong>'.mysql_num_rows($res_get_elements).'</strong> elements found.');		

		while($content_get_elements	= $db->fetchAssoc($res_get_elements)){
		
			try{
			$res_update_element_req		= "UPDATE 
												references_content
											SET
												".$content_get_elements['field']."='".$content_get_elements['value']."'
											WHERE
												id=".$content_get_elements['id_tc']."";
//echo('<br />##'.$res_update_element_req.'<br />');			
			$res_update_element 		= $db->query($res_update_element_req, __FILE__, __LINE__);			
		
				$count_update++;
				
			}catch(Exception $e){
				$count_update++;
				echo('Error '.$e.' on the row '.$count_update);
				echo('If you have this kind of Message error: "<strong>#126 - incorrect key file for table try to repair it</strong>" => Try to repair table from the PhpMyAdmin !');
			}
			
		}//end while


echo('<br /><strong>'.$count_update.'</strong> elements updated.');
echo('<br /><br />End on '.date('d/m/Y H:i:s'));

?>