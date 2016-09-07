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
<table id="example" class="display item-list" cellspacing="0" width="100%">
    <thead>
      <tr>
	  <?php
			echo '<th>Id  </th>';
			echo '<th>Nom de la famille  </th>';
			echo '<th>Statut   </th>';
			echo '<th>Eligible   </th>';
			echo '<th>Date de création  </th>';
			echo '<th>Date de modification  </th>';
			echo '<th>Action  </th>';
	  ?>
    </tr>
    </thead>
    <tbody>	
	<?php
	$sql_guides  = "SELECT flb.id, idFamily , blocked_activated, name, blocked_activated_type,update_timestamp, create_timestamp,eligible
				    FROM `families_leads_blocked_for_tc_sales` flb , families_fr ff
					WHERE flb.idFamily = ff.id";
    $req_guides  =  mysql_query($sql_guides);
	while($data_guides  =  mysql_fetch_object($req_guides)){
		
		echo '<tr>';		
			echo '<td>'.$data_guides->idFamily.'</td>';
			echo '<td>'.$data_guides->name.'</td>';
			if($data_guides->blocked_activated == "0"){
				echo '<td>Désactivé </td>';
			}else{
				echo '<td>Activé</td>';
			}
			
			if($data_guides->eligible == '1'){
				echo '<td>Oui</td>';
			}else{
				echo '<td>Non</td>';
			}
			
			echo '<td>'.date("d/m/Y H:i:s", strtotime($data_guides->create_timestamp)).'</td>';
			if($data_guides->update_timestamp == "0000-00-00 00:00:00") $date_update = " - ";
			else $date_update = date("d/m/Y H:i:s", strtotime($data_guides->update_timestamp));
			echo '<td>'.$date_update.'</td>';
			$name  = str_replace("'","\'",$data_guides->name);
			echo '<td><span><a href="#" onclick="set_item(\''.$name.'\',\''.$data_guides->idFamily.'\')">Voir</a></span> </td>';
		echo ' </tr>';	
    }
	?>	   
    </tbody>
  </table>