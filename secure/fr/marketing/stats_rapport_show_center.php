<div class="za">
	<div id="page-heading" class="ng-scope">
		<h1 class="campaigns_h1_left"><i class="fa fa-server"></i> Rapport de campagne</h1>
		
		<div id="campaigns_add_btn">
			<input type="button" id="campaigns_btn_lst_create" value="Cr&eacute;er une campagne" onclick="javascript:document.location='create-campaign.php'" class="btn btn-primary" style="margin-top:0;"> 
		</div>
		
    </div>
	
	
	<div class="row">
		<div class="col-md-12" style="width:100%;">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<?php
					
					$sql_campgne  = "SELECT name,date_last_sent, DATE_ADD(date_last_sent, INTERVAL 1 DAY) as date_plus,
											id_message
								     FROM   marketing_campaigns 
									 WHERE  id='".$_GET['id_campagne']."' ";
					$req_campgne  =  mysql_query($sql_campgne);
					$rows_campgne =  mysql_num_rows($req_campgne);
					
					if($rows_campgne > 0){
						$data_campgne = mysql_fetch_object($req_campgne);
						$date_du_send = date("d/m/Y", strtotime($data_campgne->date_last_sent));
						$date_au_send = date("d/m/Y", strtotime($data_campgne->date_plus));
						echo '<h4>Campagne '.$data_campgne->name.'</h4>';
					?>
					<script type="text/javascript">
						updateListe_rapports();
					</script>
					<?php }else {
						echo '<div style="color:red">
								<center><strong>
									ID : '.$_GET['id_campagne'].' de la campagne n\'existe pas !
								</strong></center>
							  </div>';
					}
					
				?>
				
					
				</div><!-- end div .panel-heading -->
				<?php
					$sql_message  = "SELECT name,object,name_sender FROM marketing_messages WHERE id='".$data_campgne->id_message."' ";
					$req_message  =  mysql_query($sql_message);
					$data_message =  mysql_fetch_object($req_message);
				?>
				<div class="panel-body">
					<div class="title-rapports">Rapport du <?= $date_du_send ?> au <?= $date_au_send ?></div>
					<br />
					<div id="rapport-details">
						<div class="rapports-left">
							<div>Message : <?= $data_message->name ?> </div>
							<div>Sujet :   <?= $data_message->object ?></div>
							<div>Ep&eacute;diteur : <?= $data_message->name_sender ?>  </div>
							<br />
							<div>Date 1er envoi :  </div>
						</div>
						
						<div class="rapports-right">
							<div class="blog_stats">
								
								<div class="blog-envoyer tow-blog">
									<div class="title-blog-light"><span>@ Envoy&eacute;s</span></div>
									<?php 
														 
										$sql_count_email_send  = "SELECT COUNT(id) as total_send
																  FROM marketing_check_send_mail 
																  WHERE id_campaign='".$_GET['id_campagne']."' ";
										$req_count_email_send  = mysql_query($sql_count_email_send);
										$data_count_email_send = mysql_fetch_object($req_count_email_send);
									?>
								<div class="count-result"><?= $data_count_email_send->total_send ?></div>	
								</div>
								
								<div class="blog-deliverabilite tow-blog">
									<div class="title-blog-light"><span>Tx D&eacute;liverabilit&eacute;</span></div> 
								<?php
								$sql_aboutis   =  "SELECT COUNT(id) as total_aboutis  
												   FROM marketing_check_send_mail
												   WHERE id_campaign = '".$_GET['id_campagne']."'
												   AND   delivery='1' ";
								$req_aboutis   =   mysql_query($sql_aboutis);
								$data_aboutis  =   mysql_fetch_object($req_aboutis);
								
								$deliverabilite   = ($data_aboutis->total_aboutis / $data_count_email_send->total_send) *100;
								?>
								<div class="count-result"><?= number_format($deliverabilite,1,',','') ?> %</div>	
								</div>
								
								<div class="blog-tx-ouverture tow-blog-tow">
									<div class="title-blog-light"><span>Tx ouverture</span></div>
								<div class="count-result"><?= $data_count_email_send->total_send ?></div>
								</div>
								
								<div class="blog-tx-ouverture tow-blog-tow">
									<div class="title-blog-light"><span>Tx ouverture</span></div>
								<div class="count-result"><?= $data_count_email_send->total_send ?></div>
								</div>
								
							</div>
						</div>
					</div>
					
					
					<div class="panel-form">
						<?php //require_once('campaigns_search_form.php'); ?>
					</div>
					
					<div id="loader_table">
						<center><img id="img-upload" src="<?php echo MARKETING_URL.'ressources/images/lightbox-ico-loading.gif'; ?>" /></center>
					</div>
					
					<div id="panel-table">
						&nbsp;
					</div>
					
					
					
					
					
					<!-- To open a link in a new window ! -->
					<form id="campaigns_external_formid" method="POST" action="#" target="_blank">
						<input type="hidden" id="campaign_preview_hidden_id" name="campaign_preview_hidden_id" value="" />
					</form>
					
				</div><!-- end div .panel-body -->
				
				
				<div id="campaign_actions_ask"></div>
				
			</div><!-- end div .panel panel-primary -->
			
	
		</div><!-- end div .col-md-12 -->
	</div><!-- end div .row -->
	
	

</div><!-- end div .za -->