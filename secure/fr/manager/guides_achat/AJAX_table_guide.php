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
			echo '<th>Famille 1 d’appartenance </th>';
			echo '<th>Nom thématique du guide d’achat  </th>';
			echo '<th>Nb familles associées  </th>';
			echo '<th>Date de création  </th>';
			echo '<th>Date de mise à jour  </th>
				  <th>Action  </th>';
	  ?>
    </tr>
    </thead>
    <tbody>	
	<?php
	$sql_guides  = "SELECT `id`, `id_famille_parent`, `guide_name`, `title_h`, 
						   `title_meta`, `desc_meta`, `quide_content`, `ref_name`, 
						   `create_date`, `update_date`, `user_bo_create`, `user_bo_update` 
				    FROM `guides_achat`  ";
    $req_guides  =  mysql_query($sql_guides);
	while($data_guides  =  mysql_fetch_object($req_guides)){
		$sql_familie  =  "SELECT name FROM families_fr WHERE id='".$data_guides->id_famille_parent."' ";
		$req_families =   mysql_query($sql_familie);
		$data_families=   mysql_fetch_object($req_families);
		
		$sql_count  = "SELECT id 
					   FROM guides_linked_familles
					   WHERE id_guide='".$data_guides->id."' ";
		$req_count  =  mysql_query($sql_count);
		$rows_count =  mysql_num_rows($req_count);
		
		echo '<tr>';		
			echo '<td>'.$data_families->name.'</td>';
			echo '<td>'.$data_guides->guide_name.'</td>';
			echo '<td><span style="font-weight: bold;" data-popup-target_close="#charger-visuel" onclick="charger_familie_popup(\''.$data_guides->id.'\')">'.$rows_count.'</span></td>';
			echo '<td>'.date("d/m/Y H:i", strtotime($data_guides->create_date)).'</td>';
			if($data_guides->update_date == "0000-00-00 00:00:00"){
				echo '<td> - </td>';
			}else{
			echo '<td>'.date("d-m-Y H:i", strtotime($data_guides->update_date)).'</td>';
			}
			echo '<td>
			
			<span><a href="'.URL.'guides-achat/'.$data_guides->id.'-'.$data_guides->ref_name.'.html" target="_blink">Voir</a></span> |
			<span><a href="edit-guide.php?id='.$data_guides->id.'">Modifier</a></span> | 
			<span onclick="delete_guide('.$data_guides->id.')">Supprimer</span></td>';
		echo ' </tr>';	
    }
	?>	   
    </tbody>
  </table>

<div id="charger-visuel" class="popup">
    <div class="popup-body">	
	    <div class="popup-content">
			<div id="result_families"></div>		
		</div>
		
		<div class="popup-exit"  style="overflow: hidden; width: 65px;">
			<button type="button" class="btn ui-state-default ui-corner-all">Fermer</button>
		</div>
		<br />
	</div>
</div>


  
