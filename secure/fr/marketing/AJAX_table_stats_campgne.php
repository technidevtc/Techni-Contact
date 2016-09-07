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
<table id="example" class="display item-list" cellspacing="0" width="100%">
    <thead>
      <tr>
	  <?php
	 
	  
			echo '<th>ID campagne </th>';
			echo '<th>Nom de la campagne  </th>';
			echo '<th>Type de campagne   </th>';
			echo '<th>Date dernier envoi  </th>
				  <th>Nb envoyés   </th>
				  <th>Nb aboutis   </th>
				  <th>Déliverabilité   </th>
				  <th>Nb ouvertures uniques    </th>
				  <th>Tx 1ère ouverture     </th>
				  <th>Nb clics uniques    </th>
				  <th>Tx de clic       </th>
				  <th>Tx réactivité        </th>
				 
				  <th>Action          </th>
				  ';
			// <th>Tx désabonnement         </th>
	  ?>
    </tr>
    </thead>
    <tbody>
	
	<?php
	$sql_compagn  = "SELECT id,name ,type ,date_last_sent
					 FROM  marketing_campaigns ";
	$req_compagn  = mysql_query($sql_compagn);	
	
		while($data_compagn = mysql_fetch_object($req_compagn)){
			
			
			$sql_count_email_send  = "SELECT COUNT(id) as total_send
									  FROM marketing_check_send_mail 
									  WHERE id_campaign='".$data_compagn->id."' ";
			$req_count_email_send  = mysql_query($sql_count_email_send);
			$data_count_email_send = mysql_fetch_object($req_count_email_send);
			
			$sql_aboutis   =  "SELECT COUNT(id) as total_aboutis  
							   FROM marketing_check_send_mail
							   WHERE id_campaign = '".$data_compagn->id."'
							   AND   delivery='1' ";
			$req_aboutis   =   mysql_query($sql_aboutis);
			$data_aboutis  =   mysql_fetch_object($req_aboutis);
			
			
			$sql_first_views   =  "SELECT COUNT(id) as total_first_views  
							       FROM marketing_check_send_mail
							       WHERE id_campaign = '".$data_compagn->id."'
							       AND   first_views='1' ";
			$req_first_views   =   mysql_query($sql_first_views);
			$data_first_views  =   mysql_fetch_object($req_first_views);
			
			
			$sql_clicked  =  "SELECT COUNT(id) as total_clicks
							       FROM marketing_check_send_mail
							       WHERE id_campaign = '".$data_compagn->id."'
							       AND   clicks='1' ";
			$req_clicked  =   mysql_query($sql_clicked);
			$data_clicked =   mysql_fetch_object($req_clicked);
			
			
			$deliverabilite   = ($data_aboutis->total_aboutis / $data_count_email_send->total_send) *100;
			$taux_first_views = ($data_first_views->total_first_views / $data_aboutis->total_aboutis) *100;
			$taux_clicked     = ($data_clicked->total_clicks / $data_aboutis->total_aboutis) *100;
			$taux_reactives     = ($data_clicked->total_clicks / $data_first_views->total_first_views) *100;
			
			$date_last_send = date("d/m/Y H:i", strtotime($data_compagn->date_last_sent));
			echo '<tr id="inactive">';
				echo '<td>'.$data_compagn->id.'</td>';
				echo '<td>'.$data_compagn->name.'</td>';
				echo '<td>'.ucfirst($data_compagn->type).'</td>';
				echo '<td>'.$date_last_send.'</td>';
				echo '<td><center>'.$data_count_email_send->total_send.'</center></td>';
				echo '<td><center>'.$data_aboutis->total_aboutis.'</center></td>';
				echo '<td><center>'. number_format($deliverabilite,2,',','').' %</center></td>';
				echo '<td><center>'.$data_first_views->total_first_views.'</center></td>';
				echo '<td><center>'. number_format($taux_first_views,2,',','').' %</center></td>';
				echo '<td><center>'.$data_clicked->total_clicks.'</center></td>';
				echo '<td><center>'. number_format($taux_clicked,2,',','').' %</center></td>';
				echo '<td><center>'. number_format($taux_reactives,2,',','').' %</center></td>';
				//echo '<td></td>';
				echo '<td>
						<center><a title="Voir" href="rapport_campagne.php?id_campagne='.$data_compagn->id.'"><i class="fa fa-eye"></i></a></center>
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