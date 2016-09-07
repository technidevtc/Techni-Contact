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
			echo '<th>Photo</th>';
			echo '<th>Nom de la famille 1  </th>';
			echo '<th>Nombre de guides d’achats   </th>';
			echo '<th>Action  </th>';
	  ?>
    </tr>
    </thead>
    <tbody>	
	<?php
	$sql_famile_first  = " SELECT fr.name,ff.id
						        FROM  families ff , families_fr fr
						   WHERE ff.id = fr.id
								AND ff.idParent ='0'
						   ORDER BY fr.name ASC  ";
	$req_famile_first  =   mysql_query($sql_famile_first);
	while($data_famile_first =   mysql_fetch_object($req_famile_first)){
		
		$sql_visuel  =  "SELECT id,path_img FROM guides_visuel WHERE id_famille='".$data_famile_first->id."' ";
		$req_visuel  =   mysql_query($sql_visuel);
		$data_visuel =   mysql_fetch_object($req_visuel);
		
		$sql_count   =  "SELECT COUNT(id) as total
							FROM guides_linked_familles
						 WHERE id_familles_first='".$data_famile_first->id."' ";
		$req_count   =   mysql_query($sql_count);
		$data_count  =   mysql_fetch_object($req_count);
		
		echo '<tr>';	
			if(empty($data_visuel->path_img)){				
				echo '<td><img src="images/imgres.jpg" style="width: 150px;" /></td>';
			}else{
				echo '<td><img src="'.URL.'ressources/images/guides/'.$data_visuel->path_img.'" style="width: 120px;" /></td>';	
			}
			echo '<td style="vertical-align: middle;">'.$data_famile_first->name.'</td>';
			
			echo '<td style="vertical-align: middle;">'.$data_count->total.'</td>';
			
			echo '<td style="vertical-align: middle;">
					<span style="font-weight: bold;" data-popup-target_close="#charger-visuel" onclick="charger_visuel_popup(\''.$data_famile_first->name.'\',\''.$data_famile_first->id.'\')">Charger un visuel(140x175)</span>';
			if(!empty($data_visuel->path_img)){
				echo'| 
					<span style="font-weight: bold;" data-popup-target_close="#charger-visuel" onclick="charger_visuel_popup(\''.$data_famile_first->name.'\',\''.$data_famile_first->id.'\')"> Modifier  </span>
					| 
					<span style="font-weight: bold;" onclick="delete_img(\''.$data_famile_first->name.'\',\''.$data_famile_first->id.'\')"> Supprimer </span> ';
			}
			
					
			echo'</td>';			
		echo ' </tr>';
	}
	?>	 
    </tbody>
  </table>
  
   <div id="charger-visuel" class="popup">
    <div class="popup-body">	
	    <div class="popup-content">
			<form action="gestion-familles.php" method="POST" enctype="multipart/form-data">
			
			<div id="result_families"></div>
			<br /><br />
			<div class="tow_button">
			   <div style="float: left;">
					<input type="submit" class="btn ui-state-default ui-corner-all" id="charger"></button>
			   </div>
			   <div class="popup-exit"  style="overflow: hidden; width: 65px;"><button type="button" class="btn ui-state-default ui-corner-all">Fermer</button></div>
			   <br />
			</div>
			</form>
			
		</div>
	</div>
   </div>
	
 <script type='text/javascript'>//<![CDATA[ 
$(window).load(function(){
jQuery(document).ready(function ($) {
    function clearPopup() {
		$('.popup.visible').addClass('transitioning').removeClass('visible');
        $('html').removeClass('overlay');
        setTimeout(function () {
            $('.popup').removeClass('transitioning');
        }, 200);
    }
	
	$('[data-popup-target]').click(function () {
        $('html').addClass('overlay');
        var activePopup = $(this).attr('data-popup-target');
        $(activePopup).addClass('visible');
    });
    $(document).keyup(function (e) {
        if (e.keyCode == 27 && $('html').hasClass('overlay')) {
            clearPopup();
        }
    });
    
    $('.popup-overlay').click(function () {
        clearPopup();
    });
	
	$('.popup-exit').click(function () {
        clearPopup();
    });
});
});//]]> 

</script>
<style>
#title_families{
	font-weight: bold;
}
</style>