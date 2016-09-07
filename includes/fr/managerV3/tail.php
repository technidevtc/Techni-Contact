<?php

/*================================================================/

	Techni-Contact V2 - MD2I SAS
	http://www.techni-contact.com

	Auteur : Hook Network SARL - http://www.hook-network.com
	Date de création : 20 décembre 2004
	
	Mises à jour :
		29 octobre 2007 : Nettoyage code

	Fichier : /includes/managerV2/tail.php
	Description : Fichier générique pied de page administration de l'application Web

/=================================================================*/
	$sql_verify  = "SELECT id,url_action 
					FROM   current_action_vpc 
					WHERE  id_user_bo='".$_SESSION["id"]."' ";
	$req_verify  = mysql_query($sql_verify);
	$rows_verify = mysql_num_rows($req_verify);
	
	if($rows_verify > 0){ 
		$params = $_GET['params'];
		
	   	if(empty($params)){
		/*	if($_SERVER['PHP_SELF'] == "/fr/manager/pile_appels_commerciaux/pile_appels_VPC.php"){
			
			}else{*/
		$data_verify = mysql_fetch_object($req_verify);
	?>
		<div id="bottomBar">
			<div style="visibility: visible; margin-top: -50px;" id="callBar">
				<div style="visibility: visible; padding: 15px; width: 300px; margin-top: 0px ! important;" id="inCallbar">
				<a class="btn ui-state-default ui-corner-all" href="<?= $data_verify->url_action ?>" target="_blink"> Vous avez toujours une action a traiter </a>
				</div>
			</div>
		</div>
		  
	
	<?php 
			/*}*/
		}
		} ?>
        <br/>
        <br/>
      </div><!-- End #page-content-wrapper -->      
	  
	  <div id="cookieChoiceInfo" style="position: fixed; width: 100%; margin: 0px; left: 0px; bottom: 0px; padding: 4px; z-index: 1000; text-align: center; ;">	  	  
		<div class="bottom_search_bg" style="opacity: 0.5; font-weight: bold; font-size: 13px;">
		<div id="search-bar-new" class="left_bar">
			<div style="float:left; margin-right: 10px;">
			<div style="overflow: hidden; width: 220px;">
                <input type="text" id="search_client" class="search inputText" autocomplete="off" placeholder="Recherche client" />
              </div>
			</div>
			<?php if (($userPerms->has($fntByName["m-comm--sm-pile-appels-complete"], "r")) || ($userPerms->has($fntByName["m-comm--sm-pile-appel-personaliser"], "r"))){?>
			<div class="kpi_stats">
			<div class="div-float">Accès pile d'appels</div>
			<div class="img-appel div-float"><a href="<?= ADMIN_URL ?>pile_appels_commerciaux/pile_appels_VPC.php"><img src="<?= ADMIN_URL ?>/ressources/icons/telephone_go.png"></a></div>
			
			<?php
				$sql_relance  = "SELECT COUNT(client_id) as total
								 FROM call_spool_vpc
								 WHERE campaign_name='Relance devis'
								 AND assigned_operator='".$_SESSION["id"]."'
								 AND call_result IN ('absence','not_called')
								 AND calls_count < 3
								 GROUP BY client_id";
				$req_relance  =  mysql_query($sql_relance);
				$rows_relance =  mysql_num_rows($req_relance);				
				/*if(empty($rows_relance->total)) $total_relance = "0";
				else $total_relance = $rows_relance->total;*/
			?>
			<div class="div-relance">A traiter : Relance : <?= $rows_relance ?> </div>
			<div style="float: left; margin-right: 7px;"> | </div>
			<?php
				$sql_feed  = "SELECT COUNT(client_id) as total
								 FROM call_spool_vpc
								 WHERE campaign_name='Feedback livraison'
								 AND assigned_operator='".$_SESSION["id"]."'
								 AND call_result IN ('absence','not_called')
								 AND calls_count < 3
								 GROUP BY client_id";
				$req_feed  =  mysql_query($sql_feed);
				$rows_feed =  mysql_num_rows($req_feed);
				
				if(empty($rows_relance->total)) $total_relance = "0";
				else $total_relance = $rows_relance->total;
			?>
			<div class="div-relance"> Feedback : <?= $rows_feed  ?> </div>
			<div style="float: left; margin-right: 7px;"> | </div>
			<?php
				$sql_camp  = "SELECT COUNT(client_id) as total
								 FROM call_spool_vpc
								 WHERE campaign_name NOT IN('Relance devis','Feedback commande','Feedback livraison','RDV devis','RDV client','Requalif lead')
								 AND assigned_operator='".$_SESSION["id"]."'
								 AND call_result IN ('absence','not_called')
								 AND calls_count < 3
								 GROUP BY client_id";
				$req_camp  =  mysql_query($sql_camp);
				$rows_camp =  mysql_num_rows($req_camp);
				
				if(empty($rows_camp->total)) $total_camp = "0";
				else $total_camp = $rows_camp->total;
			?>
			<div class="div-relance"> Campagne : <?= $rows_camp ?> </div>
			<div style="float: left;margin-right: 7px;"> | </div>
			<?php
				$date_now	  	   = date('Y-m-d');
				$yesterday_start   = $date_now.' 00:00:00';
				$yesterday_end     = $date_now.' 23:59:59';
				$sql_rdv  = "SELECT COUNT(client_id) as total
								 FROM call_spool_vpc
								 WHERE campaign_name IN('RDV devis','RDV client')
								 AND assigned_operator='".$_SESSION["id"]."'
								 AND call_result IN ('absence','not_called')
								 AND timestamp_rdv BETWEEN '".$yesterday_start."' AND '".$yesterday_end."'
								 AND calls_count < 3
								 GROUP BY client_id";
				
				$req_rdv  =  mysql_query($sql_rdv);
				$rows_rdv =  mysql_num_rows($req_rdv);				
			?>
			<div class="div-relance"> RDV : <?= $rows_rdv ?> </div>
			<div style="float: left;margin-right: 7px;"> | </div>
			
			<?php
			
				$sql_requalif  = "SELECT COUNT(client_id) as total
								 FROM call_spool_vpc
								 WHERE campaign_name ='Requalif lead'
								 AND assigned_operator='".$_SESSION["id"]."'
								 AND call_result IN ('absence','not_called')
								 AND calls_count < 3
								 GROUP BY client_id";
				 
				$req_requalif  =  mysql_query($sql_requalif);
				$rows_requalif =  mysql_fetch_object($req_requalif);
				if(empty($rows_requalif->total)){
					$rows_requalif_total = '0';
				}else{
					$rows_requalif_total = $rows_requalif->total;
				}
			?>
			<div class="div-relance"> Requalif : <?= $rows_requalif_total; ?> </div>
			</div>
			<?php } ?>
		</div>
		
		
		<div id="search-bar-new" class="right_bar" style="float: right; margin-right: 70px; padding: 10px;color: #fff;">
			<form action="<?php echo ADMIN_URL ?>search.php" method="get" style="right: 0 !important;">
			 <div style="float: left; margin-right: 10px;">
             <?php if ($userPerms->has($fntByName["m-prod--sm-products"], "r")) { ?>
              <input type="radio" name="search_type" value="1" checked="checked" /><label>Pr</label>
             <?php } ?>
			 
             <?php if (($userPerms->has($fntByName["m-comm--sm-pile-appels-complete"], "r")) || ($userPerms->has($fntByName["m-comm--sm-pile-appel-personaliser"], "r"))){
					}else{
				  if ($userPerms->has($fntByName["m-prod--sm-categories"], "r")) { ?>
              <input type="radio" name="search_type" value="2" /><label>Fam</label>
             <?php } ?>
             <?php if ($userPerms->has($fntByName["m-prod--sm-partners"], "r")) { ?>
              <input type="radio" name="search_type" value="3" /><label>Par</label>	  
             <?php } 
			 }?>
              </div>
              <div style="overflow: hidden; width: 220px;">
                <input type="text" name="search" class="search inputText" title="Entrez ici votre recherche" autocomplete="off" placeholder="Recherche catalogue" />
              </div>
			</form>	
		</div>
	  </div>
	  
	 </div> 
	
	  
      <div class="copyright">Manager Techni Contact v<?php echo VERSION ?> - &copy; 2004-<?php echo date('Y') ?> <a href="http://www.techni-contact.com" target="_blank">Techni Contact</a> &nbsp; &nbsp; &nbsp; &nbsp; [<a href="#">Haut de page</a>]</div>
    
    </div><!-- End #page-content -->
  
  </div><!-- End #page-layout -->
  
  <div id="rdvLayer">
    <div class="search">
      <label>Filtrer par date</label>
      <input type="text" id="rdvLayerFilterInput" />
      <button id="rdvLayerFilterBtn" class="btn ui-state-default ui-corner-all"><span class="ui-icon ui-icon-search"></span></button>
    </div>
    <div id="rdvLayerList"><ul></ul></div>
    <div id="rdvLayerOnglet">Liste RDV</div>
    <div class="zero"></div>
  </div>
  <div id="rdvHelperLayer"></div>
  
</div><!-- End #page_wrapper -->
<script>
function Test_adresse_email(email){
    var reg = new RegExp('^[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*@[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*[\.]{1}[a-z]{2,6}$', 'i'); 
    if(reg.test(email)) {
		return(true);
    }else{
		return(false);
    }
}
$('#search_client').keyup(function(e){
    if(e.keyCode == 13){
		var val_input  = $("#search_client").val();
		var test_email = Test_adresse_email(val_input); 
		
		if(test_email == true){
			document.location.href="<?php echo ADMIN_URL ?>clients/?email="+val_input;
		}else {
			document.location.href="<?php echo ADMIN_URL ?>clients/?idClient="+val_input;
		}
		 
		
        
    }
}); 
</script>
<style>
#inCallbar {
    height: 60px;
    margin-left: auto;
    margin-right: auto;
    padding-left: 125px;
    width: 250px;
}
</style>
</body>
</html>
