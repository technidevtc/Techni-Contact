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
		$(document).ready(function () {
			var table = $('#example').DataTable();
			//new $.fn.dataTable.FixedHeader(table);
		});
	</script>

<table id="example" class="display item-list" cellspacing="0" width="100%">
    <thead>
      <tr>
	  <?php	  
			echo '<th>Nom de la ressource </th>';
			echo '<th>Type d’événement  </th>';
			echo '<th>Nombre de notes   </th>';			
			echo '<th>Moyennes des notes    </th>';			
			echo '<th>Illustration de la note    </th>';			
	  ?>
    </tr>
    </thead>
    <tbody>
		<?php
			
			
			$views_date_interval = $_GET['views_date_interval'];
			$views_date_not_in_visible = $_GET['views_date_not_in_visible'];
			if($views_date_interval == 0){
					if($views_date_not_in_visible == 0){
				$sql_ago_days  = "SELECT DATE_SUB(NOW(), INTERVAL 30 day) as ago_days";
				$req_ago_days  =  mysql_query($sql_ago_days);
				$data_ago_days =  mysql_fetch_object($req_ago_days);
				
				$days_explode  = explode(' ',$data_ago_days->ago_days);
				$date_now      = date('Y-m-d');
				$date_du	   = $days_explode[0].' 00:00:00';
				$date_au 	   = $date_now.' 23:59:59';
				}else{
					$date_du = $_GET['date_start'].' 00:00:00';
					$date_au = $_GET['date_end'].' 23:59:59';
				}
				
			}else{
				$date_du = $_GET['date_start'].' 00:00:00';
				$date_au =   $_GET['date_end'].' 23:59:59';
			}
			
			$sql_notation  =  "SELECT id,interaction_type ,noted_operator, id_event ,note
							   FROM feedback_u_operators_note 
							   WHERE timestamp_note between '$date_du' AND '$date_au' 
							   GROUP BY noted_operator";
			$req_notation  =   mysql_query($sql_notation);
			// echo $sql_notation;
			while($data_notation = mysql_fetch_object($req_notation)){
				$sql_user  = "SELECT id,name FROM bo_users WHERE id='".$data_notation->noted_operator."' ";
				$req_user  =  mysql_query($sql_user);
				$data_user =  mysql_fetch_object($req_user);
				
				$sql_count_note  = "SELECT COUNT(note) as total FROM feedback_u_operators_note 
									WHERE noted_operator='".$data_notation->noted_operator."'
									AND  timestamp_note between '$date_du' AND '$date_au'
									AND note !='0' ";
				$req_count_note  = mysql_query($sql_count_note);
				$data_count_note = mysql_fetch_object($req_count_note);
				
				$sql_sum_note  =  "SELECT SUM(note) as sum_total FROM feedback_u_operators_note 
								   WHERE noted_operator='".$data_notation->noted_operator."'
								   AND timestamp_note between '$date_du' AND '$date_au'";
				$req_sum_note  =   mysql_query($sql_sum_note);
				$data_sum_note =   mysql_fetch_object($req_sum_note);
				
				$moyanne_note  = $data_sum_note->sum_total / $data_count_note->total;
				
				echo '<tr>';
					echo '<td style="vertical-align: middle;">'.$data_user->name.'</td>';
					if($data_notation->interaction_type == '1' ) $type= " Leads";
					else $type= " Devis";
					echo '<td style="vertical-align: middle;">'.$type.'</td>';
					echo '<td style="vertical-align: middle;">'.$data_count_note->total.'</td>';
					echo '<td style="vertical-align: middle;">'.$moyanne_note.'</td>';
					
					if(($moyanne_note >= 0) && ($moyanne_note <= 5.9) ){
						echo '<td style="vertical-align: middle;"><img src="images/smiley-red.jpg" /></td>';
					}
					
					if(($moyanne_note >= 6) && ($moyanne_note <= 7.9) ){
						echo '<td style="vertical-align: middle;"><img src="images/smiley-yellow.jpg" /></td>';
					}
					
					if(($moyanne_note >= 8) && ($moyanne_note <= 10) ){
						echo '<td style="vertical-align: middle;"><img src="images/smiley-green.jpg" /></td>';
					}
					
				echo '</tr>';	
			}
		?>	 
    </tbody>
  </table>
  <style>
  .dataTables_wrapper .dataTables_length{
	     margin-bottom: 15px;
  }
  </style>
