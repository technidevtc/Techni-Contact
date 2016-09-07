<?php 
	require_once('functions.php');
?>
<link rel="stylesheet" type="text/css" href="ressources/css/jquery.dataTables.css">
<script type="text/javascript" language="javascript" src="ressources/js/jquery.dataTables.js"></script>
<script type="text/javascript" language="javascript" src="ressources/js/dataTables.fixedHeader.js"></script>
<script type="text/javascript" language="javascript" class="init">
		$("#rdvDb").remove();		
		$(document).ready(function () {
			var table = $('#example').DataTable(
		);
			
		//new $.fn.dataTable.FixedHeader(table);
		});
</script>
<div class="form-right" style="  margin-bottom: 15px;">
			<input type="button" class="btn btn-primary" onclick="javascript:open_link_blank_export('messages_external_formid', 'stats-campagnes-export.php', '_blank');" value="Exporter" id="messages_btn_export">
</div>
<center><div id="campaign_actions_ask"></div></center>
<table id="example" class="display item-list" cellspacing="0" width="100%">
    <thead>
      <tr>
	  <?php
	 		echo '<th>ID</th>';
			echo '<th>Type de campagne   </th>';
			echo '<th>Nom de la campagne </th>';
			echo '<th>Messages  </th>
				  <th>Segment   </th>
				  <th>D. Envoi  </th>
				  <th>@ Bruts   </th>
				  <th>@ Envoyés </th>
				  <th>Etat      </th>
				  <th>Actions   </th>
				  ';
	  ?>
    </tr>
    </thead>
    <tbody>
	
	<?php
	$sql_compagn  = "SELECT id,name ,type ,date_last_sent,id_message ,emails_brut,etat
					 FROM   marketing_campaigns
					 WHERE  id='".$_GET['id_campagne']."'";
	
	$req_compagn  = mysql_query($sql_compagn);	
	
		while($data_compagn = mysql_fetch_object($req_compagn)){
			
			$sql_count_email_send  = "SELECT COUNT(id) as total_send
									  FROM marketing_check_send_mail 
									  WHERE id_campaign='".$data_compagn->id."' ";
			$req_count_email_send  = mysql_query($sql_count_email_send);
			$data_count_email_send = mysql_fetch_object($req_count_email_send);
			
			$sql_message_segement  = "SELECT ms.name as name_segement , mm.name as name_message
									 FROM   marketing_messages mm, marketing_segment ms
									 WHERE  mm.id_segment = ms.id
									 AND    mm.id='".$data_compagn->id_message."' ";
			$req_message_segement  = mysql_query($sql_message_segement);
			$data_message_segement = mysql_fetch_object($req_message_segement); 
			
			$date_last_send = date("d/m/Y H:i", strtotime($data_compagn->date_last_sent));
			
			
			echo '<tr id="inactive">';
				echo '<td><center>'.$data_compagn->id.'</center></td>';
				echo '<td><center>'.ucfirst($data_compagn->type).'</center></td>';
				echo '<td><center>'.$data_compagn->name.'</center></td>';
				echo '<td><center>'.$data_message_segement->name_message.'</center></td>';
				echo '<td><center>'.$data_message_segement->name_segement.'</center></td>';
				echo '<td><center>'.$date_last_send.'</center></td>';
				echo '<td><center>'.$data_compagn->emails_brut.' </center> </td>';
				echo '<td><center>'.$data_count_email_send->total_send.'</center></td>';
				if($data_compagn->etat == 'Programmed') $etat = "Programmée";
				if($data_compagn->etat == 'Processing') $etat = "Traitement";
				if($data_compagn->etat == 'Finalized')  $etat = "Terminé";
				
				echo '<td><center>'.$etat.'</center></td>';
				echo '<td>
						<center>
						<a href="/fr/marketing/edit-campaign.php?id='.$data_compagn->id.'&stats=ok" title="Modifier"><i class="fa fa-pencil"></i></a>
						<a title="Supprimer" onclick="javascript:campaign_ask_delete_display_modal('.$data_compagn->id.');" href="javascript:void(0);"><i class="fa fa-trash-o"></i></a>
						</center>
					  </td>';
			echo ' </tr>';
		}						 
	?>	 
    </tbody>
  </table>
  
  <style>
	#example{
		border: 1px solid #4697ce;
		border-radius: 5px 5px 3px 3px;
	}
	
	#example tr th {
		background-color: #4697ce;
		border-left: 1px solid #80b5cf;
		color: #ffffff;
		font-size: 12px;
		font-weight: bold;
		padding: 0px 15px;
	}
	
	#example_length{
		margin-bottom: 15px;
	}
  </style>