<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
	
	$db = DBHandle::get_instance();
	
	$action = $_GET['action'];
	
	if($action == 'get_message'){
	$id_contact = $_GET['id_contact'];
	$send_val   = $_GET['send_val'];
	$sql = "SELECT precisions_additional FROM contacts WHERE id='$id_contact' ";
	$req = mysql_query($sql);
	$data= mysql_fetch_object($req);
		
	if(!empty($data->precisions_additional)){
		echo '<div > 
				<p><textarea id="precisions_additional" rows="10" cols="45" WRAP="HARD" disabled>'.$data->precisions_additional.'</textarea></p>
				<div style="float: left; margin-left: 70px;"><button class="btn ui-state-default ui-corner-all" id="btn-precisions_additional" style="display:none;margin-right: 110px;" onclick="update_additionall(\''.$id_contact.'\',\''.$send_val.'\')">Enregistrer la note</button></div>
    	      </div>';
			  
		echo '<div style="float: left; margin-left: 90px;"><button class="btn ui-state-default ui-corner-all" id="btn-precisions_additional_modif" onclick="active_modif()">Modifier la note</button>
		</div>';	  
	}else {
		echo '<div >
				<p><textarea id="precisions_additional" rows="10" cols="45" WRAP="HARD"></textarea></p>
					<div style="float: left; margin-left: 70px;">
					<button class="btn ui-state-default ui-corner-all" id="btn-precisions_additional_add" onclick="update_additionall(\''.$id_contact.'\',\''.$send_val.'\')" style="margin-right: 110px;">Enregistrer la note</button>
					</div>
    	      </div>';
	}
	
	}
	

	
	if($action == 'update_message'){
		$id_contact = mysql_real_escape_string($_GET['id_contact']);
		$message = mysql_real_escape_string($_GET['message']);
		$sql_update = "UPDATE `contacts` SET precisions_additional = '$message' 
					   WHERE id=$id_contact ";
		mysql_query($sql_update);
		
	}
	
	
	
	
	

?>
