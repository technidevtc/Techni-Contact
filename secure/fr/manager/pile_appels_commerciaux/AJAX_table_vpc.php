<?php
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}
$user = new BOUser();

require_once(ADMIN."logs.php");

$handle = DBHandle::get_instance();
$db = DBHandle::get_instance();
$user = $userChildScript = new BOUser();
if (!$user->login()) {
	header("Location: ".ADMIN_URL."login.html");
	exit();
}
$userPerms = $user->get_permissions();
// usefull functionalities index ny name
$fntl_tmp = BOFunctionality::get("name, id");
$fntByName = array();
foreach($fntl_tmp as $fnt)
  $fntByName[$fnt["name"]] = $fnt["id"];
?>
	<link rel="stylesheet" type="text/css" href="css/jquery.dataTables.css">
	<script type="text/javascript" language="javascript" src="js/jquery.dataTables.js"></script>
	<script type="text/javascript" language="javascript" src="js/spool_vpc_script.js"></script>
	<!--<script type="text/javascript" language="javascript" src="js/dataTables.fixedHeader.js"></script>-->
	<script type="text/javascript" language="javascript" class="init">
		$("#rdvDb").remove();		
		$(document).ready(function () {
			var table = $('#example').DataTable();
			//new $.fn.dataTable.FixedHeader(table);
		});
	</script>
<?php 
		if( (isset($_GET['joinabilite'])) && (isset($_GET['appel'])) ){
			$action_join  = $_GET['joinabilite'];
			$action_appel = $_GET['appel'];
			
			
			/**********************************************************************/
			
			if( ($action_appel == 'relance') && ($action_join == 'all')){
				$sql_and_and  = " AND call_result IN ('not_called','absence') 
								  AND campaign_name = 'Relance devis' ";		
			}
			
			if( ($action_appel == 'relance') && ($action_join == 'not_called')){
				$sql_and_and  = " AND call_result = 'not_called'
								  AND campaign_name = 'Relance devis' ";		
			}
			
			if( ($action_appel == 'relance') && ($action_join == 'absence')){
				$sql_and_and  = " AND call_result = 'absence'
								  AND campaign_name = 'Relance devis' ";		
			}
			
			/**********************************************************************/
			
			if( ($action_appel == 'livraison') && ($action_join == 'all')){
				$sql_and_and  = " AND call_result IN ('not_called','absence') 
								  AND campaign_name IN('Feedback livraison internet','Feedback livraison') ";		
			}
			
			if( ($action_appel == 'livraison') && ($action_join == 'not_called')){
				$sql_and_and  = " AND call_result = 'not_called'
								  AND campaign_name IN('Feedback livraison internet','Feedback livraison') ";		
			}
			
			if( ($action_appel == 'livraison') && ($action_join == 'absence')){
				$sql_and_and  = " AND call_result = 'absence'
								  AND campaign_name IN('Feedback livraison internet','Feedback livraison') ";		
			}
			
			/**********************************************************************/
			
			if( ($action_appel == 'campagne') && ($action_join == 'all')){
				$sql_and_and  = " AND call_result IN ('not_called','absence') 
								  AND campaign_name NOT IN ('Relance devis','Feedback livraison','Feedback livraison internet','RDV devis','RDV client','Requalif lead') ";		
			}
			
			if( ($action_appel == 'campagne') && ($action_join == 'not_called')){
				$sql_and_and  = " AND call_result = 'not_called'
								  AND campaign_name NOT IN ('Relance devis','Feedback livraison','Feedback livraison internet','RDV devis','RDV client','Requalif lead') ";		
			}
			
			if( ($action_appel == 'campagne') && ($action_join == 'absence')){
				$sql_and_and  = " AND call_result = 'absence'
								  AND campaign_name NOT IN ('Relance devis','Feedback livraison','Feedback livraison internet','RDV devis','RDV client','Requalif lead') ";		
			}
			
			
			/**********************************************************************/
			
			if( ($action_appel == 'rdv') && ($action_join == 'all')){
				$sql_and_and  = " AND call_result IN ('not_called','absence') 
								  AND campaign_name IN ('RDV devis','RDV client')  ";		
			}
			
			if( ($action_appel == 'rdv') && ($action_join == 'not_called')){
				$sql_and_and  = " AND call_result = 'not_called'
								  AND campaign_name IN ('RDV devis','RDV client')  ";		
			}
			
			if( ($action_appel == 'rdv') && ($action_join == 'absence')){
				$sql_and_and  = " AND call_result = 'absence'
								  AND campaign_name IN ('RDV devis','RDV client') ";		
			}
			
			/**********************************************************************/
			
			if( ($action_appel == 'requalif') && ($action_join == 'all')){
				$sql_and_and  = " AND call_result IN ('not_called','absence') 
								  AND campaign_name = 'Requalif lead'  ";		
			}
			
			if( ($action_appel == 'requalif') && ($action_join == 'not_called')){
				$sql_and_and  = " AND call_result = 'not_called'
								  AND campaign_name = 'Requalif lead'  ";		
			}
			
			if( ($action_appel == 'requalif') && ($action_join == 'absence')){
				$sql_and_and  = " AND call_result = 'absence'
								  AND campaign_name ='Requalif lead' ";		
			}
			
			
			if( ($action_join == 'all') && ($action_appel == '')){
				$sql_and_and  = " AND call_result IN ('not_called','absence') ";		
				}
				
				if( ($action_join == 'not_called') && ($action_appel == '') ){
					$sql_and_and  = " AND call_result = 'not_called' ";		
				}
				
				if( ($action_join == 'absence') && ($action_appel == '') ){
					$sql_and_and  = " AND call_result = 'absence' ";		
			}
			
			/**********************************************************************/
			}else{	
				$sql_and_and  = " AND call_result IN ('not_called','absence')";
			} 
		
	if($_GET['users'] != 'all'){
		$sql_and_and .= " AND assigned_operator='".$_GET['users']."'";	
	}
	
	if ($userPerms->has($fntByName["m-comm--sm-pile-appel-personaliser"], "r")) {
		$sql_and_and .= " AND assigned_operator='".$_SESSION["id"]."'";
	}
	

	
	$sql_compagn_relance  = "SELECT id,estimate_id, campaign_name,assigned_operator,calls_count,client_id
						     FROM  call_spool_vpc 
						     WHERE calls_count < 3 
						     $sql_and_and
						     AND campaign_name='Relance devis'
							 ORDER BY timestamp_created ASC ";
	$req_compagn_relance  = mysql_query($sql_compagn_relance);
	
	
	$sql_compagn_diff     = "SELECT id,client_id, campaign_name,assigned_operator,calls_count,timestamp_created
						     FROM  call_spool_vpc 
						     WHERE calls_count < 3 
						     $sql_and_and
						     AND campaign_name NOT IN('Relance devis','Feedback commande','Feedback livraison','Feedback livraison internet','RDV devis','RDV client','Requalif lead') 
							 ORDER BY timestamp_created ASC ";
    $req_compagn_diff     =  mysql_query($sql_compagn_diff);
	
	
	$sql_compagn_feedback = "SELECT id,order_id, campaign_name,assigned_operator,calls_count,client_id,estimate_id
						     FROM  call_spool_vpc 
						     WHERE calls_count < 3 
						     $sql_and_and
						     AND campaign_name='Feedback livraison' 
							 ORDER BY timestamp_created ASC ";
	$req_compagn_feedback =  mysql_query($sql_compagn_feedback);
	
	$sql_compagn_feedback_internet = "SELECT id,order_id, campaign_name,assigned_operator,calls_count,client_id,estimate_id
									  FROM  call_spool_vpc 
									  WHERE calls_count < 3 
									  $sql_and_and
									  AND campaign_name='Feedback livraison internet' 
									  ORDER BY timestamp_created ASC ";
	$req_compagn_feedback_internet =  mysql_query($sql_compagn_feedback_internet);
	
	$date_now	  	   = date('Y-m-d');
	$yesterday_start   = strtotime($date_now.' 00:00:00');
	$yesterday_end     = strtotime($date_now.' 23:59:59');																			    
	
	$sql_compagn_rdv 	  = "SELECT csv.id, campaign_name,assigned_operator,calls_count,client_id,estimate_id,timestamp_rdv
						     FROM  call_spool_vpc csv, rdv rr
						     WHERE csv.rdv_id = rr.id
							 AND rr.timestamp_call BETWEEN '".$yesterday_start."' AND '".$yesterday_end."'  
							 AND calls_count < 3 
						     $sql_and_and
						     AND campaign_name IN ('RDV devis','RDV client') 
							 ORDER BY timestamp_created ASC";
	$req_compagn_rdv 	  =  mysql_query($sql_compagn_rdv);
	
	
	$sql_compagn_Requalif  	  = "SELECT id, campaign_name,assigned_operator,calls_count,
									    id_contact
								 FROM  call_spool_vpc 
								 WHERE calls_count < 3 
								 $sql_and_and
								 AND campaign_name = 'Requalif lead' 
								 ORDER BY timestamp_created ASC ";
	$req_compagn_Requalif  	  =  mysql_query($sql_compagn_Requalif);
	

?>	
<table id="example" class="display item-list" cellspacing="0" width="100%">
    <thead>
      <tr>
	  <?php
	 
	  
			echo '<th>Type contact </th>';
			echo '<th>Date RDV  </th>';
			echo '<th>Ressource  </th>';
			echo '<th>Société  </th>
				  <th>Nom / Prénom  </th>
				  <th>Date d\'action  </th>
				  <th>Nb d’appels en absence  </th>';
	  ?>
    </tr>
    </thead>
    <tbody>
	
	<?php
	$sql_verify  = "SELECT id,url_action 
					FROM   current_action_vpc 
					WHERE  id_user_bo='".$_SESSION["id"]."' ";
	$req_verify  = mysql_query($sql_verify);
	$rows_verify = mysql_num_rows($req_verify);
	
	
		while($data_compagn_relance = mysql_fetch_object($req_compagn_relance)){
			$sql_date  = "SELECT updated_mail_sent_pdf FROM estimate WHERE id='".$data_compagn_relance->estimate_id."' ";
			$req_date  =  mysql_query($sql_date);
			$data_date =  mysql_fetch_object($req_date);
			$validated_relance	  = date('d/m/Y H:m', $data_date->updated_mail_sent_pdf);
			
			$sql_client  = "SELECT societe,nom,prenom,tel1,tel2,login
							FROM clients  WHERE id='".$data_compagn_relance->client_id."' ";
			$req_client  = mysql_query($sql_client);
			$data_client = mysql_fetch_object($req_client);
			
			if(!empty($data_client->tel1)){
				$tel_pass = $data_client->tel1;
			}else{
				$tel_pass = $data_client->tel2;
			}
			
			$sql_commercial = "SELECT name FROM bo_users WHERE id='".$data_compagn_relance->assigned_operator."' ";
			$req_commercial = mysql_query($sql_commercial);
			$data_commercial= mysql_fetch_object($req_commercial);
			if($rows_verify > 0){ 
			echo '<tr id="inactive">';
			}else {
			echo '<tr onclick="isAlreadyPickedVPC_relance(\''.$data_compagn_relance->id.'\',\''.$tel_pass.'\',\''.$data_compagn_relance->estimate_id.'\',\''.$data_client->login.'\',\''.ADMIN_URL.'\')">';
			}
			
			
			
				echo '<td>'.$data_compagn_relance->campaign_name.'</td>';
				echo '<td></td>';
				echo '<td>'.$data_commercial->name.'</td>';
				echo '<td>'.$data_client->societe.'</td>';
				echo '<td>'.$data_client->nom.' '.$data_client->prenom.'</td>';
				echo '<td>'.$validated_relance.'</td>';
				echo '<td>'.$data_compagn_relance->calls_count.'</td>';
			echo ' </tr>';
		}
		
		
		while($data_compagn = mysql_fetch_object($req_compagn_diff)){
			$sql_client  = "SELECT societe,nom,prenom,tel1,tel2,login
							FROM clients  WHERE id='".$data_compagn->client_id."' ";
			$req_client  = mysql_query($sql_client);
			$data_client = mysql_fetch_object($req_client);
			
			$phpdate = strtotime( $data_compagn->timestamp_created );
			$date_created = date( 'd/m/Y H:m', $phpdate );
			
			if(!empty($data_client->tel1)){
				$tel_pass = $data_client->tel1;
			}else{
				$tel_pass = $data_client->tel2;
			}
			
			$sql_commercial = "SELECT name FROM bo_users WHERE id='".$data_compagn->assigned_operator."' ";
			$req_commercial = mysql_query($sql_commercial);
			$data_commercial= mysql_fetch_object($req_commercial);
			if($rows_verify > 0){ 
				echo '<tr id="inactive">';
			}else {				
			echo '<tr onclick="isAlreadyPickedVPC(\''.$data_compagn->id.'\',\''.$tel_pass.'\',\''.$data_compagn->client_id.'\',\''.$data_client->login.'\',\''.ADMIN_URL.'\')">';
			}
				echo '<td>'.$data_compagn->campaign_name.'</td>';
				echo '<td></td>';
				echo '<td>'.$data_commercial->name.'</td>';
				echo '<td>'.$data_client->societe.'</td>';
				echo '<td>'.$data_client->nom.' '.$data_client->prenom.'</td>';
				echo '<td>'.$date_created.'</td>';
				echo '<td>'.$data_compagn->calls_count.'</td>';
			echo ' </tr>';
		}
		
		
		while($data_compagn_feedback = mysql_fetch_object($req_compagn_feedback)){
			$sql_societe  = "SELECT societe,nom,prenom,email,tel,validated,forecasted_ship,tel2
							 FROM   `order`  
							 WHERE  id='".$data_compagn_feedback->order_id."' ";
			$req_societe  = mysql_query($sql_societe);
			$data_societe = mysql_fetch_object($req_societe);
			
			$validated	  = date('d/m/Y H:m', $data_societe->validated);
			$forecasted_ship	  = date('d/m/Y H:m', $data_societe->forecasted_ship);
			
			$sql_estimate_wu = "SELECT name ,tel
								FROM estimate ee, bo_users bu 
								WHERE ee.created_user_id = bu.id  
								AND ee.id='".$data_compagn_feedback->estimate_id."' ";
			$req_estimate_wu = mysql_query($sql_estimate_wu);
			$data_estimate_wu= mysql_fetch_object($req_estimate_wu); 
			
			//echo $sql_societe.'<br /><br />';
			if(!empty($data_societe->tel2)){
				$tel_pass = $data_societe->tel2;
			}else{
				$tel_pass = $data_societe->tel;
			}
			
			$sql_commercial = "SELECT name FROM bo_users WHERE id='".$data_compagn_feedback->assigned_operator."' ";
			$req_commercial = mysql_query($sql_commercial);
			$data_commercial= mysql_fetch_object($req_commercial);
			if($rows_verify > 0){ 
				echo '<tr id="inactive">';
			}else {
			echo '<tr onclick="isAlreadyPickedVPC_feedback(\''.$data_compagn_feedback->id.'\',\''.$tel_pass.'\',\''.$data_compagn_feedback->order_id.'\',\''.$data_societe->email.'\',\''.ADMIN_URL.'\')">';
			}
				echo '<td>'.$data_compagn_feedback->campaign_name.'</td>';
				echo '<td></td>';
				echo '<td>'.$data_estimate_wu->name.'</td>';
				echo '<td>'.$data_societe->societe.'</td>';
				echo '<td>'.$data_societe->nom.' '.$data_societe->prenom.'</td>';
				echo '<td>'.$forecasted_ship.'</td>';
				echo '<td>'.$data_compagn_feedback->calls_count.'</td>';
			echo ' </tr>';
		}

		while($data_compagn_feedback_internet = mysql_fetch_object($req_compagn_feedback_internet)){
			$sql_societe  = "SELECT societe,nom,prenom,email,tel,validated,forecasted_ship,tel2
							 FROM   `order`  
							 WHERE  id='".$data_compagn_feedback_internet->order_id."' ";
			$req_societe  = mysql_query($sql_societe);
			$data_societe = mysql_fetch_object($req_societe);
			 
			$validated	  = date('d/m/Y H:m', $data_societe->validated);
			$forecasted_ship	  = date('d/m/Y H:m', $data_societe->forecasted_ship);
			
			$sql_commercial = "SELECT name FROM bo_users WHERE id='".$data_compagn_relance->assigned_operator."' ";
			$req_commercial = mysql_query($sql_commercial);
			$data_commercial= mysql_fetch_object($req_commercial);

			
			// echo $sql_order_wu.'<br />';
			
			//echo $sql_societe.'<br /><br />';
			if(!empty($data_societe->tel2)){
				$tel_pass = $data_societe->tel2;
			}else{
				$tel_pass = $data_societe->tel;
			}
			
			$sql_commercial = "SELECT name FROM bo_users WHERE id='".$data_compagn_feedback_internet->assigned_operator."' ";
			$req_commercial = mysql_query($sql_commercial);
			$data_commercial= mysql_fetch_object($req_commercial);
			if($rows_verify > 0){ 
				echo '<tr id="inactive">';
			}else {
			echo '<tr onclick="isAlreadyPickedVPC_feedback(\''.$data_compagn_feedback_internet->id.'\',\''.$tel_pass.'\',\''.$data_compagn_feedback_internet->order_id.'\',\''.$data_societe->email.'\',\''.ADMIN_URL.'\')">';
			}
				echo '<td>'.$data_compagn_feedback_internet->campaign_name.'</td>';
				echo '<td></td>';
				echo '<td>'.$data_commercial->name.'</td>';
				echo '<td>'.$data_societe->societe.'</td>';
				echo '<td>'.$data_societe->nom.' '.$data_societe->prenom.'</td>';
				echo '<td>'.$forecasted_ship.'</td>';
				echo '<td>'.$data_compagn_feedback_internet->calls_count.'</td>';
			echo ' </tr>';
		}		
		
		while($data_compagn_rdv = mysql_fetch_object($req_compagn_rdv)){
			$client_id    = $data_compagn_rdv->client_id;
			$estimate_id  = $data_compagn_rdv->estimate_id;
			
			
			if(!empty($client_id)){
			$sql_result  = "SELECT societe,nom,prenom,tel1,tel2,email
							FROM clients  WHERE id='".$client_id."' ";
			$req_result  = mysql_query($sql_result);
			$data_result = mysql_fetch_object($req_result);
			
			if(!empty($data_result->tel1)){
				$tel_pass = $data_result->tel1;
			}else{
				$tel_pass = $data_result->tel2;
			}
			}
			
			
			if(!empty($estimate_id)){
			$sql_result  = "SELECT societe,nom,prenom,tel,tel2,email
							FROM estimate  WHERE id='".$estimate_id."' ";
			$req_result  = mysql_query($sql_result);
			$data_result = mysql_fetch_object($req_result);	
			
			if(!empty($data_result->tel1)){
				$tel_pass = $data_result->tel;
			}else{
				$tel_pass = $data_result->tel2;
			}
			}
			
			$sql_commercial = "SELECT name FROM bo_users WHERE id='".$data_compagn_rdv->assigned_operator."' ";
			$req_commercial = mysql_query($sql_commercial);
			$data_commercial= mysql_fetch_object($req_commercial);
			if($rows_verify > 0){ 
				echo '<tr id="inactive">';
			}else {
			echo '<tr onclick="isAlreadyPickedVPC_rdv(\''.$data_compagn_rdv->id.'\',\''.$tel_pass.'\',\''.$client_id.'\',\''.$estimate_id.'\',\''.$data_result->email.'\',\''.ADMIN_URL.'\')">';
			}
				echo '<td>'.$data_compagn_rdv->campaign_name.'</td>';
				echo '<td>'.date('d/m/Y', strtotime($data_compagn_rdv->timestamp_rdv)).'</td>';
				echo '<td>'.$data_commercial->name.'</td>';
				echo '<td>'.$data_result->societe.'</td>';
				echo '<td>'.$data_result->nom.' '.$data_result->prenom.'</td>';
				echo '<td>--</td>';
				echo '<td>'.$data_compagn_rdv->calls_count.'</td>';
			echo ' </tr>';
		}
		
		
		while($data_compagn_Requalif = mysql_fetch_object($req_compagn_Requalif)){
			$sql_societe  = "SELECT DISTINCT(societe),nom,prenom,email,tel,create_time
							 FROM   `contacts`  
							 WHERE  id='".$data_compagn_Requalif->id_contact."'
							 GROUP BY societe";
			$req_societe  = mysql_query($sql_societe);
			$data_societe = mysql_fetch_object($req_societe);
			
			$create_time	  = date('d/m/Y H:m', $data_societe->create_time);
			
			//echo $sql_societe.'<br /><br />';
			if(!empty($data_societe->tel)){
				$tel_pass = $data_societe->tel;
			}else{
				$tel_pass = $data_societe->tel2;
			}
			
			$sql_commercial = "SELECT name FROM bo_users WHERE id='".$data_compagn_Requalif->assigned_operator."' ";
			$req_commercial = mysql_query($sql_commercial);
			$data_commercial= mysql_fetch_object($req_commercial);
			if($rows_verify > 0){ 
				echo '<tr id="inactive">';
			}else {
			echo '<tr onclick="isAlreadyPickedVPC_Requalif(\''.$data_compagn_Requalif->id.'\',\''.$tel_pass.'\',\''.$data_compagn_Requalif->id_contact.'\',\''.$data_societe->email.'\',\''.ADMIN_URL.'\')">';
			}
				echo '<td>'.$data_compagn_Requalif->campaign_name.'</td>';
				echo '<td></td>';
				echo '<td>'.$data_commercial->name.'</td>';
				echo '<td>'.$data_societe->societe.'</td>';
				echo '<td>'.$data_societe->nom.' '.$data_societe->prenom.'</td>';
				echo '<td>'.$create_time.'</td>';
				echo '<td>'.$data_compagn_Requalif->calls_count.'</td>';
			echo ' </tr>';
		} 
	?>	 
    </tbody>
  </table>
  
  <?php
	if($rows_verify > 0){ 
		echo '<style>';
			echo '.sorting_1 {';
				echo 'background-color : #9a9896 !important';
			echo '}';
		echo '</style>';	
	}
	
  ?>