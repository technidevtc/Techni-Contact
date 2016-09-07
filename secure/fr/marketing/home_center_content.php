<?php
	require_once('functions.php'); 

	if(!empty($_SESSION['marketing_user_id'])){

?>
	<!-- Start one row ! -->
	
	<?php
		
		$res_get_last_sync_query	= "SELECT
											MAX(date_last_synchronisation_start) AS dlast_sync
										FROM
											marketing_last_db_synchronisation
										";
		$res_get_last_sync = $db->query($res_get_last_sync_query, __FILE__, __LINE__);
												
		$content_get_last_sync = $db->fetchAssoc($res_get_last_sync);
			
	?>
	
	<div id="page-heading" class="ng-scope">
		<h1><i class="fa fa-users"></i> Tableau de bord Au <?php echo (date('d/m/Y - H:i:s',strtotime($content_get_last_sync['dlast_sync']))); ?></h1>
	</div>
		
		
	<div class="row">
		<div class="col-md-6" style="width:100%;">
			<panel heading="Performance pour MOIS" class="ng-isolate-scope" ng-bind-html-unsafe="ajaxData">
				<div class="panel ">
					<div class="panel-heading">
						<h4 class="ng-binding">Transactions</h4>
						<div class="options">
							<panel-controls class="ng-scope">
								<a href="" ng-controller="panelControlHomeclick">
									<panel-control-collapse class="fa fa-chevron-down"></panel-control-collapse>
								</a>
							</panel-controls>
						</div>
					</div>
					
					<div class="panel-body" nstyle="display: block;">
					
						<div class="row ng-scope">
						
						   <div class="col-md-3 col-sm-6">
								<a class="info-tiles tiles-danger" href="javascript:void(0);">
									<div class="tiles-heading">
										<div class="">Comptes client</div>
										<!-- <div class="pull-right">+25.8%</div> -->
									</div>
									<div class="tiles-body">
										<!-- <div class="pull-left"><i class="fa fa-download"></i></div> -->
										<div class="pull-right-home">
	<?php
		
		$res_get_custumers_count_query	= "SELECT
												count(*) AS c
											FROM
												clients c
											WHERE
												c.actif=1";
		$res_get_custumers_count = $db->query($res_get_custumers_count_query, __FILE__, __LINE__);
												
		$content_get_custumers_count = $db->fetchAssoc($res_get_custumers_count);
		
		echo(separate_every_three_chars_right_to_left($content_get_custumers_count['c']));
		
	?>
										</div>
									</div>
								</a>
							</div><!-- end div .col-md-3 col-sm-6 -->
							
							<div class="col-md-3 col-sm-6">
								<a class="info-tiles tiles-indigo" href="javascript:void(0);">
									<div class="tiles-heading">
										<div class="">Leads A</div>
										<div class="pull-right"></div>
									</div>
									<div class="tiles-body">
										<div class="pull-left"></div>
										<div class="pull-right-home">
	<?php
		
		$res_get_lead_a_count_query	= "SELECT
											count(cont.id) AS c
										FROM
											contacts cont
												INNER JOIN advertisers AS adv ON cont.idadvertiser=adv.id AND adv.category!=1 AND cont.parent=0";
											
		$res_get_lead_a_count = $db->query($res_get_lead_a_count_query, __FILE__, __LINE__);
												
		$content_get_lead_a_count = $db->fetchAssoc($res_get_lead_a_count);
		
		echo(separate_every_three_chars_right_to_left($content_get_lead_a_count['c']));
		
	?>
										</div>
									</div>
								</a>
							</div><!-- end div .col-md-3 col-sm-6 -->
							
						   <div class="col-md-3 col-sm-6">
								<a class="info-tiles tiles-success" href="javascript:void(0);">
									<div class="tiles-heading">
										<div class="">Leads F</div>
										<div class="pull-right"></div>
									</div>
									<div class="tiles-body">
										<div class="pull-left"></div>
										<div class="pull-right-home">
	<?php
		
		$res_get_lead_f_count_query	= "SELECT
											count(cont.id) AS c
										FROM
											contacts cont
												INNER JOIN advertisers AS adv ON cont.idadvertiser=adv.id AND adv.category=1 AND cont.parent=0";
											
		$res_get_lead_f_count = $db->query($res_get_lead_f_count_query, __FILE__, __LINE__);
												
		$content_get_lead_f_count = $db->fetchAssoc($res_get_lead_f_count);
		
		echo(separate_every_three_chars_right_to_left($content_get_lead_f_count['c']));
		
	?>
										</div>
									</div>
								</a>
							</div><!-- end div .col-md-3 col-sm-6 -->
							
							<div class="col-md-3 col-sm-6">
								<a class="info-tiles tiles-midnightblue" href="javascript:void(0);">
									<div class="tiles-heading">
										<div class="">Commandes</div>
										<div class="pull-right"></div>
									</div>
									<div class="tiles-body">
										<div class="pull-left"></div>
										<div class="pull-right-home">
	<?php
		
		$res_get_validated_orders_query	= "SELECT
												count(*) AS c
											FROM
												`order` o
											WHERE
												o.processing_status!=0
											AND
												o.processing_status!=10
											AND
												o.processing_status!=90
											AND
												o.processing_status!=99";
											
		$res_get_validated_orders = $db->query($res_get_validated_orders_query, __FILE__, __LINE__);
												
		$content_get_validated_orders = $db->fetchAssoc($res_get_validated_orders);
		
		echo(separate_every_three_chars_right_to_left($content_get_validated_orders['c']));
		
	?>
										</div>
									</div>
								</a>
							</div><!-- end div .col-md-3 col-sm-6 -->
							
						</div> <!-- end div .row ng-scope -->
						
                    </div> <!-- end div .panel-body -->
					
			</div><!-- end div .panel -->
		</panel>
	
		</div>
	</div><!-- end div .row -->
	<!-- End one row ! -->
	
	
	<!-- Start one row ! -->
	<div class="row">
		<div class="col-md-6" style="width:100%;">
			<panel heading="Performance pour MOIS" class="ng-isolate-scope">
				<div class="panel ">
					<div class="panel-heading">
						<h4 class="ng-binding">Produits</h4>
						<div class="options">
							<panel-controls class="ng-scope">
								<a href="">
									<panel-control-collapse class="fa fa-chevron-down"></panel-control-collapse>
								</a>
							</panel-controls>
						</div>
					</div>
					
					<div class="panel-body" nstyle="display: block;">
					
						<div class="row ng-scope">
						
						   <div class="col-md-3 col-sm-6">
								<a class="info-tiles tiles-danger" href="javascript:void(0);">
									<div class="tiles-heading">
										<div class="">Produits actifs</div>
										<!-- <div class="pull-right">+25.8%</div> -->
									</div>
									<div class="tiles-body">
										<!-- <div class="pull-left"><i class="fa fa-download"></i></div> -->
										<div class="pull-right-home">
	<?php
		
		$res_get_products_actif_count_query	= "SELECT
												count(*) AS c
											FROM
												products_fr pfr
											WHERE
												pfr.active=1
											AND
												pfr.deleted=0";
		$res_get_products_actif_count = $db->query($res_get_products_actif_count_query, __FILE__, __LINE__);
												
		$content_get_products_actif_count = $db->fetchAssoc($res_get_products_actif_count);
		
		echo(separate_every_three_chars_right_to_left($content_get_products_actif_count['c']));
		
	?>
										</div>
									</div>
								</a>
							</div><!-- end div .col-md-3 col-sm-6 -->
							
							<div class="col-md-3 col-sm-6">
								<a class="info-tiles tiles-indigo" href="javascript:void(0);">
									<div class="tiles-heading">
										<div class="">R&eacute;f&eacute;rences actives</div>
										<div class="pull-right"></div>
									</div>
									<div class="tiles-body">
										<div class="pull-left"></div>
										<div class="pull-right-home">
	<?php
		
		$res_get_references_actives_count_query	= "SELECT
														count(*) AS c
													FROM
														references_content AS ref_c
													WHERE
														ref_c.classement!=0
													AND
														ref_c.deleted!=1";
		$res_get_references_actives_count = $db->query($res_get_references_actives_count_query, __FILE__, __LINE__);
												
		$content_get_references_actives_count = $db->fetchAssoc($res_get_references_actives_count);
		
		echo(separate_every_three_chars_right_to_left($content_get_references_actives_count['c']));
		
	?>
										</div>
									</div>
								</a>
							</div><!-- end div .col-md-3 col-sm-6 -->
							
						   <div class="col-md-3 col-sm-6">
								<a class="info-tiles tiles-success" href="javascript:void(0);">
									<div class="tiles-heading">
										<div class="">Familles 3</div>
										<div class="pull-right"></div>
									</div>
									<div class="tiles-body">
										<div class="pull-left"></div>
										<div class="pull-right-home">
	<?php
		
		$res_get_families3_count_query	= "SELECT 
												COUNT(fr.id) AS c

											FROM 
												families f, 
												families_fr fr 

											WHERE 
												f.id = fr.id
											AND
												idParent!=0
											AND
												f.id NOT IN (
														SELECT
															f.idParent
														FROM
															families f
														)";
		$res_get_families3_count = $db->query($res_get_families3_count_query, __FILE__, __LINE__);
												
		$content_get_families3_count = $db->fetchAssoc($res_get_families3_count);
		
		echo(separate_every_three_chars_right_to_left($content_get_families3_count['c']));
		
	?>
										</div>
									</div>
								</a>
							</div><!-- end div .col-md-3 col-sm-6 -->
						
						</div> <!-- end div .row ng-scope -->
						
                    </div> <!-- end div .panel-body -->
					
			</div><!-- end div .panel -->
		</panel>
	
		</div>
	</div><!-- end div .row -->
	<!-- End one row ! -->

	
	<!-- Start one row ! -->
	<div class="row">
		<div class="col-md-6" style="width:100%;">
			<panel heading="Performance pour MOIS" class="ng-isolate-scope">
				<div class="panel ">
					<div class="panel-heading">
						<h4 class="ng-binding">Partenaires</h4>
						<div class="options">
							<panel-controls class="ng-scope">
								<a href="">
									<panel-control-collapse class="fa fa-chevron-down"></panel-control-collapse>
								</a>
							</panel-controls>
						</div>
					</div>
					
					<div class="panel-body" nstyle="display: block;">
					
						<div class="row ng-scope">
						
						   <div class="col-md-3 col-sm-6">
								<a class="info-tiles tiles-danger" href="javascript:void(0);">
									<div class="tiles-heading">
										<div class="">Annonceurs</div>
										<!-- <div class="pull-right">+25.8%</div> -->
									</div>
									<div class="tiles-body">
										<!-- <div class="pull-left"><i class="fa fa-download"></i></div> -->
										<div class="pull-right-home">
	<?php
		
		$res_get_annonceurs_actifs_count_query	= "SELECT
														count(*) AS c
													FROM
														advertisers adv
													WHERE
														adv.category=0
													AND
														adv.actif=1
													AND
														adv.deleted=0";
		$res_get_annonceurs_actifs_count = $db->query($res_get_annonceurs_actifs_count_query, __FILE__, __LINE__);
												
		$content_get_annonceurs_actifs_count = $db->fetchAssoc($res_get_annonceurs_actifs_count);
		
		echo(separate_every_three_chars_right_to_left($content_get_annonceurs_actifs_count['c']));
		
	?>
										</div>
									</div>
								</a>
							</div><!-- end div .col-md-3 col-sm-6 -->
							
							<div class="col-md-3 col-sm-6">
								<a class="info-tiles tiles-indigo" href="javascript:void(0);">
									<div class="tiles-heading">
										<div class="">Fournisseurs</div>
										<div class="pull-right"></div>
									</div>
									<div class="tiles-body">
										<div class="pull-left"></div>
										<div class="pull-right-home">
	<?php
		
		$res_get_fournisseurs_actifs_count_query	= "SELECT
														count(*) AS c
													FROM
														advertisers adv
													WHERE
														adv.category=1
													AND
														adv.actif=1
													AND
														adv.deleted=0";
		$res_get_fournisseurs_actifs_count = $db->query($res_get_fournisseurs_actifs_count_query, __FILE__, __LINE__);
												
		$content_get_fournisseurs_actifs_count = $db->fetchAssoc($res_get_fournisseurs_actifs_count);
		
		echo(separate_every_three_chars_right_to_left($content_get_fournisseurs_actifs_count['c']));
		
	?>
										</div>
									</div>
								</a>
							</div><!-- end div .col-md-3 col-sm-6 -->
							
						   <div class="col-md-3 col-sm-6">
								<a class="info-tiles tiles-success" href="javascript:void(0);">
									<div class="tiles-heading">
										<div class="">Annonceur Non factur&eacute;s</div>
										<div class="pull-right"></div>
									</div>
									<div class="tiles-body">
										<div class="pull-left"></div>
										<div class="pull-right-home">
	<?php
		
		$res_get_annonceurs_nofact_count_query	= "SELECT
														count(*) AS c
													FROM
														advertisers adv
													WHERE
														adv.category=2
													AND
														adv.actif=1
													AND
														adv.deleted=0";
		$res_get_annonceurs_nofact_count = $db->query($res_get_annonceurs_nofact_count_query, __FILE__, __LINE__);
												
		$content_get_annonceurs_nofact_count = $db->fetchAssoc($res_get_annonceurs_nofact_count);
		
		echo(separate_every_three_chars_right_to_left($content_get_annonceurs_nofact_count['c']));
		
	?>
										</div>
									</div>
								</a>
							</div><!-- end div .col-md-3 col-sm-6 -->
							
							
							<div class="col-md-3 col-sm-6" style="clear: both;">
								<a class="info-tiles tiles-midnightblue" href="javascript:void(0);">
									<div class="tiles-heading">
										<div class="">Prospects</div>
										<div class="pull-right"></div>
									</div>
									<div class="tiles-body">
										<div class="pull-left"></div>
										<div class="pull-right-home">
	<?php
		
		$res_get_prospects_count_query	= "SELECT
														count(*) AS c
													FROM
														advertisers adv
													WHERE
														adv.category=3
													AND
														adv.actif=1
													AND
														adv.deleted=0";
		$res_get_prospects_count = $db->query($res_get_prospects_count_query, __FILE__, __LINE__);
												
		$content_get_prospects_count = $db->fetchAssoc($res_get_prospects_count);
		
		echo(separate_every_three_chars_right_to_left($content_get_prospects_count['c']));
		
	?>
										</div>
									</div>
								</a>
							</div><!-- end div .col-md-3 col-sm-6 -->
							
							<div class="col-md-3 col-sm-6">
								<a class="info-tiles tiles-midnightblue" href="javascript:void(0);">
									<div class="tiles-heading">
										<div class="">Annonceur bloqu&eacute;s </div>
										<div class="pull-right"></div>
									</div>
									<div class="tiles-body">
										<div class="pull-left"></div>
										<div class="pull-right-home">
	<?php
		
		$res_get_advertisers_blocked_count_query	= "SELECT
														count(*) AS c
													FROM
														advertisers adv
													WHERE
														adv.category=4
													AND
														adv.actif=1
													AND
														adv.deleted=0";
		$res_get_advertisers_blocked_count = $db->query($res_get_advertisers_blocked_count_query, __FILE__, __LINE__);
												
		$content_get_advertisers_blocked_count = $db->fetchAssoc($res_get_advertisers_blocked_count);
		
		echo(separate_every_three_chars_right_to_left($content_get_advertisers_blocked_count['c']));
		
	?>
										</div>
									</div>
								</a>
							</div><!-- end div .col-md-3 col-sm-6 -->
							
							<div class="col-md-3 col-sm-6">
								<a class="info-tiles tiles-midnightblue" href="javascript:void(0);">
									<div class="tiles-heading">
										<div class="">Annonceurs litige de paiement</div>
										<div class="pull-right"></div>
									</div>
									<div class="tiles-body">
										<div class="pull-left"></div>
										<div class="pull-right-home">
	<?php
		
		$res_get_advertisers_litige_count_query	= "SELECT
														count(*) AS c
													FROM
														advertisers adv
													WHERE
														adv.category=5
													AND
														adv.actif=1
													AND
														adv.deleted=0";
		$res_get_advertisers_litige_count = $db->query($res_get_advertisers_litige_count_query, __FILE__, __LINE__);
												
		$content_get_advertisers_litige_count = $db->fetchAssoc($res_get_advertisers_litige_count);
		
		echo(separate_every_three_chars_right_to_left($content_get_advertisers_litige_count['c']));
		
	?>
										</div>
									</div>
								</a>
							</div><!-- end div .col-md-3 col-sm-6 -->
						
						</div> <!-- end div .row ng-scope -->
						
                    </div> <!-- end div .panel-body -->
					
			</div><!-- end div .panel -->
		</panel>
	
		</div>
	</div><!-- end div .row -->
	<!-- End one row ! -->
	
<?php
	}
?>