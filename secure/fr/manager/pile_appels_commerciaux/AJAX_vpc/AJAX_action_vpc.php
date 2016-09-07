<?php
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}
$db = DBHandle::get_instance();

$action = $_GET['action'];

	if($action == 'client_joint'){
		$id_ligne = $_GET['id_ligne'];
		
		$sql_count  = "SELECT calls_count,timestamp_first_call,timestamp_second_call,timestamp_third_call
					   FROM call_spool_vpc  WHERE id='".$id_ligne."' ";
		$req_count  = mysql_query($sql_count);
		$data_count = mysql_fetch_object($req_count);
		$count_total= $data_count->calls_count +1;
		
		if($data_count->timestamp_first_call == '0000-00-00 00:00:00'){
			$sql_date = ' timestamp_first_call=NOW() ';
		}else if($data_count->timestamp_second_call == '0000-00-00 00:00:00'){
			$sql_date = ' timestamp_second_call=NOW() ';
		}else if($data_count->timestamp_third_call == '0000-00-00 00:00:00'){
			$sql_date = ' timestamp_third_call=NOW() ';
		}
		
		$sql_update = "UPDATE call_spool_vpc SET ligne_active = '0', 
												 call_result  = 'call_ok',
												 calls_count  = '$count_total',
												 $sql_date
					   WHERE id='".$id_ligne."' ";
		mysql_query($sql_update);
		
		$sql_rdv  = "SELECT rdv_id,campaign_name,calls_count FROM call_spool_vpc WHERE id='".$id_ligne."'  ";
		$req_rdv  =  mysql_query($sql_rdv);
		$data_rdv =  mysql_fetch_object($req_rdv);
		
		if($data_rdv->campaign_name == 'RDV client'){
			$sql_delete_rdv = "DELETE FROM rdv WHERE id='".$data_rdv->rdv_id."' ";
			mysql_query($sql_delete_rdv);
		}
		
		if($data_rdv->campaign_name == 'RDV devis'){
			$sql_delete_rdv = "DELETE FROM rdv WHERE id='".$data_rdv->rdv_id."' ";
			mysql_query($sql_delete_rdv);
		}
		
		$sql_delete = "DELETE FROM current_action_vpc WHERE id_ligne_vpc='".$id_ligne."' ";
		mysql_query($sql_delete);
		
	}
	
	if($action == 'client_en_absence'){
		$id_ligne = $_GET['id_ligne'];
		
		$sql_count  = "SELECT calls_count,timestamp_first_call,timestamp_second_call,timestamp_third_call
					   FROM call_spool_vpc  WHERE id='".$id_ligne."' ";
		$req_count  = mysql_query($sql_count);
		$data_count = mysql_fetch_object($req_count);
		$count_total= $data_count->calls_count +1;
		
		if($data_count->timestamp_first_call == '0000-00-00 00:00:00'){
			$sql_date = ' timestamp_first_call=NOW() ';
		}else if($data_count->timestamp_second_call == '0000-00-00 00:00:00'){
			$sql_date = ' timestamp_second_call=NOW() ';
		}else if($data_count->timestamp_third_call == '0000-00-00 00:00:00'){
			$sql_date = ' timestamp_third_call=NOW() ';
		}
		
		$sql_update = "UPDATE call_spool_vpc SET ligne_active = '0', 
												 call_result  = 'absence',
												 calls_count  = '$count_total',
												 $sql_date
					   WHERE id='".$id_ligne."' ";
		mysql_query($sql_update);
		
		$sql_rdv  = "SELECT rdv_id,campaign_name,calls_count FROM call_spool_vpc WHERE id='".$id_ligne."'  ";
		$req_rdv  =  mysql_query($sql_rdv);
		$data_rdv =  mysql_fetch_object($req_rdv);
		
		if($data_rdv->campaign_name == 'RDV client'){
			if($data_rdv->calls_count == 3){
				$sql_delete_rdv = "DELETE FROM rdv WHERE id='".$data_rdv->rdv_id."' ";
				mysql_query($sql_delete_rdv);
			}
		}
		
		if($data_rdv->campaign_name == 'RDV devis'){
			if($data_rdv->calls_count == 3){
				$sql_delete_rdv = "DELETE FROM rdv WHERE id='".$data_rdv->rdv_id."' ";
				mysql_query($sql_delete_rdv);
			}
		}
		
		$sql_delete = "DELETE FROM current_action_vpc WHERE id_ligne_vpc='".$id_ligne."' ";
		mysql_query($sql_delete);
	}
	
	if($action == 'action_sur_client'){
		$id_ligne = $_GET['id_ligne'];
		
		$sql_count  = "SELECT calls_count,timestamp_first_call,timestamp_second_call,timestamp_third_call
					   FROM call_spool_vpc  WHERE id='".$id_ligne."' ";
		$req_count  = mysql_query($sql_count);
		$data_count = mysql_fetch_object($req_count);
		$count_total= $data_count->calls_count +1;
		
		if($data_count->timestamp_first_call == '0000-00-00 00:00:00'){
			$sql_date = ' timestamp_first_call=NOW() ';
		}else if($data_count->timestamp_second_call == '0000-00-00 00:00:00'){
			$sql_date = ' timestamp_second_call=NOW() ';
		}else if($data_count->timestamp_third_call == '0000-00-00 00:00:00'){
			$sql_date = ' timestamp_third_call=NOW() ';
		}
		
		$sql_update = "UPDATE call_spool_vpc SET ligne_active = '0', 
												 call_result  = 'call_ok_conversion',
												 calls_count  = '$count_total',
												 $sql_date
					   WHERE id='".$id_ligne."' ";
		mysql_query($sql_update);
		
		$sql_rdv  = "SELECT rdv_id,campaign_name,calls_count FROM call_spool_vpc WHERE id='".$id_ligne."'  ";
		$req_rdv  =  mysql_query($sql_rdv);
		$data_rdv =  mysql_fetch_object($req_rdv);
		
		if($data_rdv->campaign_name == 'RDV client'){
			$sql_delete_rdv = "DELETE FROM rdv WHERE id='".$data_rdv->rdv_id."' ";
			mysql_query($sql_delete_rdv);
		}
		
		if($data_rdv->campaign_name == 'RDV devis'){
			$sql_delete_rdv = "DELETE FROM rdv WHERE id='".$data_rdv->rdv_id."' ";
			mysql_query($sql_delete_rdv);
		}
		
		$sql_delete = "DELETE FROM current_action_vpc WHERE id_ligne_vpc='".$id_ligne."' ";
		mysql_query($sql_delete);
	}
	
	if($action == 'performance'){
		$access_droit = $_GET['access_droit'];
		$id_users 	  = $_GET['id_users'];
		$sql_ajoint  = "SELECT COUNT(id) as total 
							FROM call_spool_vpc 
							WHERE call_result='not_called' ";
							
		if ($access_droit =='personaliser') {
			$sql_ajoint .= " AND assigned_operator='".$id_users."'";
		}
		
		
		$req_ajoint  = mysql_query($sql_ajoint);
		$data_ajoint = mysql_fetch_object($req_ajoint); 
		
		$sql_absence  = "SELECT COUNT(id) as total 
						 FROM call_spool_vpc 
						 WHERE call_result='absence' AND calls_count < 3 ";
        if ($access_droit =='personaliser') {
			$sql_absence .= " AND assigned_operator='".$id_users."'";
		}

		$date_now	  	   = date('Y-m-d');
		$yesterday_start   = strtotime($date_now.' 00:00:00');
		$yesterday_end     = strtotime($date_now.' 23:59:59');		
		
		$sql_compagn_rdv  = "SELECT COUNT(csv.id) as total
						     FROM  call_spool_vpc csv, rdv rr
						     WHERE csv.rdv_id = rr.id
						     AND rr.timestamp_call NOT BETWEEN '".$yesterday_start."' AND '".$yesterday_end."' ";
		if ($access_droit =='personaliser') {
			$sql_compagn_rdv .= " AND csv.assigned_operator='".$id_users."'";
		}					 
		$req_rdv          =  mysql_query($sql_compagn_rdv);
		$data_rdv		  =  mysql_fetch_object($req_rdv);
		
		
		$total_ajoindre_final = $data_ajoint->total - $data_rdv->total;
		
		$req_absence  = mysql_query($sql_absence);
		$data_absence = mysql_fetch_object($req_absence); 
		$total_joindre = $data_absence->total+$total_ajoindre_final;
		echo '<div>
			<div class="title-vpc-call">Performances</div>
			<div class="global-perfor">
				<div>Reste à appeler :  <span>'.$total_ajoindre_final.'</span></div>
				<div>Appels en absence :<span>'.$data_absence->total.'</span></div>
				<div>Total à joindre :  <span>'.$total_joindre.'</span> </div>
			</div>
		  </div>';
	}
	

?>
