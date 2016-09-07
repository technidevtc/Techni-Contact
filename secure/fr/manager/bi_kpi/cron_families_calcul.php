<?php
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}
$db = DBHandle::get_instance();
	$mois  = '01';
	$annee = '2014';
	
	
	$date_debut = '01-'.$mois.'-'.$annee.'';
	$date_fin = '30-'.$mois.'-'.$annee.'';
	
	$timestamp_debut = strtotime($date_debut);
	$timestamp_fin   = strtotime($date_fin);
	
	$sql_families = "SELECT id FROM families_fr";
	$req_families = mysql_query($sql_families);
	while($data_families = mysql_fetch_object($req_families)){
		
		$sql_revenu_moyen_sum   = "SELECT  SUM(income_total) as income_total 
											   FROM contacts  
											   WHERE parent = 0 
											   AND idFamily ='".$data_search->id."' 
											   AND create_time BETWEEN $timestamp_debut AND $timestamp_fin";
		$req_revenu_moyen_sum   = mysql_query($sql_revenu_moyen_sum);
		$data_revenu_moyen_sum  = mysql_fetch_object($req_revenu_moyen_sum);
				
				
		$sql_revenu_moyen_count   = "SELECT COUNT(distinct cc.id) as dis_id 
											 FROM contacts cc , advertisers aa 
											 WHERE cc.idAdvertiser = aa.id
											 AND cc.parent = 0 
											 AND aa.category != 1 
											 AND cc.create_time BETWEEN $timestamp_debut AND $timestamp_fin	
											 AND cc.idFamily ='".$data_search->id."'";
		$req_revenu_moyen_count   = mysql_query($sql_revenu_moyen_count); 
		$data_revenu_moyen_count  = mysql_fetch_object($req_revenu_moyen_count);
		$num_revenu_moyen   = $data_revenu_moyen_sum->income_total / $data_revenu_moyen_count->dis_id;
		
		
		/******* Debut Nb leads primaires annonceur  ***************/
		$sql_nb_leads_annon  = "SELECT COUNT(distinct cc.id) as nb_leads_annon 
								FROM contacts cc , advertisers aa  
								WHERE cc.idAdvertiser = aa.id
								AND cc.parent = 0 
								AND aa.category != 1
								AND cc.idFamily ='".$data_search->id."'
								AND cc.create_time BETWEEN $timestamp_debut AND $timestamp_fin	";
		$req_nb_leads_annon  = mysql_query($sql_nb_leads_annon);
		$data_nb_leads_annon = mysql_fetch_object($req_nb_leads_annon);
				
		/******* Fin Nb leads primaires annonceur  ***************/	
		
		/******* Debut  leads fournisseur  ***************/
		$sql_nb_leads_fourn  = "SELECT COUNT(distinct cc.id) as  leads_fourn
							FROM   contacts cc , advertisers aa
							WHERE  cc.idAdvertiser = aa.id
							AND    cc.parent ='0' AND aa.category ='1'
							AND    cc.idFamily ='".$data_search->id."'
							AND    cc.create_time BETWEEN $timestamp_debut AND $timestamp_fin ";
		$req_nb_leads_fourn  = mysql_query($sql_nb_leads_fourn);
		$data_nb_leads_fourn = mysql_fetch_object($req_nb_leads_fourn);
		/******* Fin Nb leads fournisseur  ***************/
		
		echo 'id_family : '.$data_families->id.' period : '.$mois.'Annee : '.$annee.'  lead_revenue :  '.$num_revenu_moyen.' nb_advertisers_p_lead '.$data_nb_leads_annon->nb_leads_annon.'  nb_suppliers_p_lead :  '.$data_nb_leads_fourn->leads_fourn.'<br /><br />';
		
	}


?>
