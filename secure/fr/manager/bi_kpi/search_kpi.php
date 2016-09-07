<?php
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}


$db = DBHandle::get_instance();

$title = $navBar = "KPI Familles 3";
require(ADMIN."head.php");

echo '
	  <script type="text/javascript" src="../js/script.js"></script>';
if (!$userPerms->has($fntByName["bi-kpi-familles"], "re")) {
?>

<div class="bg">
  <div class="fatalerror">Vous n'avez pas les droits ad&eacute;quats pour r&eacute;aliser cette opération.</div>
</div>
<?php
}
else {
  $f = BOFunctionality::get("id","name='bi-kpi'");
  if (!empty($f)) {
    $ups = BOUserPermission::get("id_user","id_functionality=".$f[0]["id"]);
    foreach($ups as $up)
      $comIdList[] = $up["id_user"];
    if (!empty($comIdList)) {
      $comList = BOUser::get("id, name, login, email, phone","id in (".implode(",",$comIdList).")");
    }
  }
?>
<link rel="stylesheet" type="text/css" href="leads.css" />
<script src="../js/ManagerFunctions.js" type="text/javascript"></script>
<script type="text/javascript" src="leads.js"></script>
<link rel="stylesheet"  type="text/css" href="style.css" />

	<link rel="stylesheet" type="text/css" href="css/jquery.dataTables.css">

	<script type="text/javascript" language="javascript" src="js/jquery.dataTables.js"></script>
	<script type="text/javascript" language="javascript" src="js/dataTables.fixedHeader.js"></script>
	<script type="text/javascript" language="javascript" class="init">
		$("#rdvDb").remove();
		/*$(document).ready(function() {
			var table = $('#example').DataTable();
			$('#example tbody').on( 'click', 'tr', function () {
				$(this).toggleClass('selected');
			});
		});
		*/
		$(document).ready(function () {
			var table = $('#example').DataTable();
			new $.fn.dataTable.FixedHeader(table);
			
			$('#example tbody').on( 'click', 'tr', function () {
				$(this).toggleClass('selected');
			});
		});
	</script>


<div class="titreStandard">Moteur de recherche + Liste (contacts,partenaires,produits au sein de ces familles  )</div>
<br/>
<div class="section">
  <div style="color: #FF0000" id="show_error_message"></div>
  <div id="filtering-options" class="block filtering">
    <div class="title">Moteur de recherche</div>
    <div class="text">
      
      
      <fieldset style="margin: auto; float: none; width: 85%;">
		<legend>Moteur de recherche :</legend>
		<form method="get" action="search_kpi.php">
		<input type="hidden" name="search_exact" id="search_exact" value="0" />
		<div style="margin-bottom: 30px;">
        <div style="float: left; width: 85%; margin-right: -440px;">
		<input type="text" class="champstexte" placeholder="de rechercher un mot, ou une expression de plusieurs mots" name="families_send" id="families_send" onkeyup="autocomplet_kpi()" value="<?php echo $_GET['families_send'] ?>"  style="width: 45%;" required=""/>
		<ul id="country_list_id" style="width: 300px;border: 1px solid #eaeaea;position: absolute;z-index:9;background: #f3f3f3;list-style: none;"></ul>
		</div>
		
		
		<div  style="overflow: hidden;padding: 6px;">
		D&eacute;but [Mois] : <select data-placeholder="Mois" name="mois_debut" id="mois_debut" >
							<option value=""></option>
							<option value="1" selected>Janvier</option>
							<option value="2">F&eacute;vrier</option>
							<option value="3">Mars</option>
							<option value="4">Avril</option>
							<option value="5">Mai</option>
							<option value="6">Juin</option>
							<option value="7">Juillet</option>
							<option value="8">Ao&ucirc;t</option>
							<option value="9">Semptebre</option>
							<option value="10">October</option>
							<option value="11">November</option>
							<option value="12">December</option>
                      </select>
		[Ann&eacute;e] : <select data-placeholder="Année" name="annee_departs" id="annee_departs" >
							<option value="2010">2010</option>
							<option value="2011">2011</option>
							<option value="2012">2012</option>
							<option value="2013">2013</option>
							<option value="2014">2014</option>
							<option value="2015">2015</option>
							<option value="2016" selected>2016</option>
				      </select>
		
		Fin [Mois] : <select data-placeholder="Mois" name="mois_fin" id="mois_fin" >
							<option value=""></option>
							<option value="1" selected>Janvier</option>
							<option value="2">F&eacute;vrier</option>
							<option value="3">Mars</option>
							<option value="4">Avril</option>
							<option value="5">Mai</option>
							<option value="6">Juin</option>
							<option value="7">Juillet</option>
							<option value="8">Ao&ucirc;t</option>
							<option value="9">Semptebre</option>
							<option value="10">October</option>
							<option value="11">November</option>
							<option value="12">December</option>
                      </select>	
		 [Ann&eacute;e] : <select data-placeholder="Année" name="annee" id="annee" >
							<option value="2010">2010</option>
							<option value="2011">2011</option>
							<option value="2012">2012</option>
							<option value="2013">2013</option>
							<option value="2014">2014</option>
							<option value="2015">2015</option>
							<option value="2016" selected>2016</option>
				      </select>
					  
		</div>
						
		
		
		</div>
		
		<center><input type="submit" name="ok" value="Chercher" class="bouton"> &nbsp; 
		<input type="reset" class="bouton" value="Annuler" name="nok"></center>
		</form>
      </fieldset>
	  
      <div class="zero"></div>
    </div>
  </div>

  <br />
  <div id="msg-tooltip" class="tooltip"></div>

  <table id="example" class="display item-list" cellspacing="0" width="100%">
    <thead>
      <tr>
	  <?php
		if(!empty($_GET['families_send'])){
			
			echo '<th>Nom de la famille</th>';
			echo '<th>Revenu moyen par lead<br /> au sein de la famille </th>';
			echo '<th class="date">Nb leads primaires annonceur</th>';
			echo '<th>Nb leads fournisseur</th>
				  <th>Nb leads par mois</th>
				  <th>Nb de produits</th>
				  <th>Nb de partenaires</th>
				  <th>Nb d\'annonceurs</th>
				  <th>Nb d\'annonceurs non factur&eacute;</th>
				  <th>Nb de fournisseurs</th>
				  <th>Nb autres</th>';
		}else {
			echo '<th>Nom de la famille</th>
				  <th>Revenu moyen par lead<br /> au sein de la famille </th>
				  <th class="date">Nb leads primaires annonceur</th>
				  <th>Nb leads fournisseur</th>
				  <th>Nb leads par mois</th>
				  <th>Nb de produits</th>
				  <th>Nb de partenaires</th>
				  <th>Nb d\'annonceurs</th>
				  <th>Nb d\'annonceurs non factur&eacute;</th>
				  <th>Nb de fournisseurs</th>
				  <th>Nb autres</th>';
		}
	  ?>
      
	 
    </tr>
    </thead>
    <tbody>
	<?php

	if(isset($_GET['families_send'])){	
	$annee_departs 	= $_GET['annee_departs'];
	$annee 			= $_GET['annee'];
	
	
	$mois_debut 	= $_GET['mois_debut'];
	$mois_fin		= $_GET['mois_fin'];
	
	$periode 		= $mois_fin - $mois_debut;
	$periode_final 	= $periode +1;
	
	$search_exact	= $_GET['search_exact'];
	
	$date_debut = '01-'.$mois_debut.'-'.$annee_departs.'';
	$date_fin = '30-'.$mois_fin.'-'.$_GET['annee'].'';	
	$timestamp_debut = strtotime($date_debut);
	$timestamp_fin = strtotime($date_fin);	
	
	
		$datetime1 = date_create($date_debut);
		$datetime2 = date_create($date_fin);
		$interval = date_diff($datetime1, $datetime2);
		$periode = $interval->format('%a');
		$periode_dev = $periode / 30;
		$periode_explode = explode('.',$periode_dev);
		$periode_final = $periode_explode[0];
		
	$families_send = $_GET['families_send'];
	$s = explode(" ",$families_send);
	$sql_search = "SELECT id,name  FROM families_fr  ";
	$i = 0;
	
	if($search_exact == 0 ){
	foreach($s as $mot){
		if(strlen($mot) > 3 ){
			if($i == 0){
				$sql_search .= " WHERE ";
			}else {
				$sql_search .= " OR ";
			}			
			$sql_search .= " name LIKE '%$mot%' ";
			
			$i++;
		}
	}
	}else {
		$sql_search .= " WHERE name LIKE '%$families_send%' ";
	}
	
	
	$req_search = mysql_query($sql_search);
	$rows       = mysql_num_rows($req_search);
	echo '<div id="search-results" style="margin-bottom: 15px;float:left;"><h1><strong>'.$rows.' r&eacute;sultats</strong> pour `<strong>'.$families_send.'</strong>`</h1></div>';
	
	echo '<div style="float: right;"><a href="export.php?families_send='.$families_send.'&mois_debut='.$_GET['mois_debut'].'&mois_fin='.$_GET['mois_fin'].'&annee='.$_GET['annee'].'" class="btn ui-state-default ui-corner-all fr" id="get-orders-extract">Exporter au format Excel</a></div>';
	
		if($rows > 0){
			while($data_search = mysql_fetch_object($req_search)){

				/******* Debut Revenu moyen par lead au sein de la famille  ***************/
				$sql_revenu_moyen_sum   = "SELECT  SUM(income_total) as income_total 
											   FROM contacts  
											   WHERE parent = 0 
											   AND idFamily ='".$data_search->id."' 
											   AND create_time BETWEEN $timestamp_debut AND $timestamp_fin";
				$req_revenu_moyen_sum   = mysql_query($sql_revenu_moyen_sum);
				$data_revenu_moyen_sum  = mysql_fetch_object($req_revenu_moyen_sum);
				
				
				$sql_revenu_moyen_count   = "SELECT COUNT(distinct cc.id) as dis_id 
											 FROM contacts cc , advertisers aa 
											 WHERE cc.idAdvertiser = aa.id
											 AND cc.parent = 0 
											 AND aa.category != 1 
											 AND cc.create_time BETWEEN $timestamp_debut AND $timestamp_fin	
											 AND cc.idFamily ='".$data_search->id."'";
				$req_revenu_moyen_count   = mysql_query($sql_revenu_moyen_count); 
				$data_revenu_moyen_count  = mysql_fetch_object($req_revenu_moyen_count);
				
				$num_revenu_moyen   = $data_revenu_moyen_sum->income_total / $data_revenu_moyen_count->dis_id;
				
				$num_revenu_moyen_final   = number_format($num_revenu_moyen, 2, '.', '');
				/******* Fin Revenu moyen par lead au sein de la famille  ***************/
				
				
				/******* Debut Nb leads primaires annonceur  ***************/
				$sql_nb_leads_annon  = "SELECT COUNT(distinct cc.id) as nb_leads_annon 
										FROM contacts cc , advertisers aa 
										WHERE cc.idAdvertiser = aa.id
										
										AND   cc.parent = 0 
										AND   aa.category != 1
										AND   cc.idFamily ='".$data_search->id."' 
										AND   cc.create_time BETWEEN $timestamp_debut AND $timestamp_fin";
										
				$req_nb_leads_annon  = mysql_query($sql_nb_leads_annon);
				$data_nb_leads_annon = mysql_fetch_object($req_nb_leads_annon);
				
				// echo $sql_nb_leads_annon.'<br />';
				/******* Fin Nb leads primaires annonceur  ***************/	
				
				
				/******* Debut  leads fournisseur  ***************/
				$sql_nb_leads_fourn  = "SELECT COUNT(distinct cc.id) as  leads_fourn
											FROM   contacts cc , advertisers aa
											WHERE  cc.idAdvertiser = aa.id
											AND    cc.parent ='0' AND aa.category ='1'
											AND    cc.idFamily ='".$data_search->id."'
											AND    cc.create_time BETWEEN $timestamp_debut AND $timestamp_fin ";
				$req_nb_leads_fourn  = mysql_query($sql_nb_leads_fourn);
				$data_nb_leads_fourn = mysql_fetch_object($req_nb_leads_fourn);
				/******* Fin Nb leads fournisseur  ***************/
				
				/******* Debut Nb leads par mois  ***************/
				$leads_par_mois = $data_nb_leads_annon->nb_leads_annon / $periode_final ;
				
				/******* Fin  Nb leads par mois  ***************/
				
				
				
				/******* Debut Nb de produits  ***************/
				$sql_total_products  = "SELECT  COUNT(pf.idProduct) as total_products
									 FROM products_families pf , products_fr pp 
									 WHERE pf.idProduct = pp.id
									 AND pf.idFamily = '".$data_search->id."'
									 AND pp.deleted = '0'
									 AND pp.active  = '1'";
				$req_total_products  = mysql_query($sql_total_products);
				$data_total_products = mysql_fetch_object($req_total_products);
				/******* Fin  Nb de produits  ***************/
				
				
				
				
				/******* Debut Nb de partenaires  ***************/
				
				$sql_total_partenaires  = " SELECT  COUNT(DISTINCT(pp.idAdvertiser)) as total_partenaires
											 FROM  products_families pf , families_fr ffr , products_fr pp
											 WHERE pf.idFamily = ffr.id 
											 AND   pf.idProduct = pp.id 
											 AND   pf.idFamily = '".$data_search->id."'
											 AND   pp.deleted = '0'
											 AND   pp.active  = '1'";
				$req_total_partenaires  = mysql_query($sql_total_partenaires);
				$data_total_partenaires = mysql_fetch_object($req_total_partenaires);
						
				/******* Fin  Nb de partenaires  ***************/	
				
				/******* Debut Nb d'annonceurs 	  ***************/
				$sql_total_annonceurs  = "SELECT  COUNT( DISTINCT advertisers.id ) as total_annonceurs
											FROM advertisers as advertisers ,
												 products_fr as products_fr ,
												 products_families as products_families
											WHERE products_families.idProduct = products_fr.id
											AND   products_fr.idAdvertiser  =  advertisers.id
											AND   products_families.idFamily= '".$data_search->id."'
											AND   advertisers.category =0
											AND   products_fr.deleted = '0'
											AND   products_fr.active  = '1'
											";
						
				$req_total_annonceurs  = mysql_query($sql_total_annonceurs);
				$data_total_annonceurs = mysql_fetch_object($req_total_annonceurs);
				/******* Fin  Nb d'annonceurs 	  ***************/
				
				
				/******* Debut Nb d'annonceurs non facturé 	  ***************/
				$sql_total_nn_facture  = "SELECT  COUNT( DISTINCT advertisers.id ) as nn_facture
											FROM advertisers as advertisers ,
												 products_fr as products_fr ,
												 products_families as products_families
											WHERE products_families.idProduct = products_fr.id
											AND   products_fr.idAdvertiser  =  advertisers.id
											AND   products_families.idFamily= '".$data_search->id."'
											AND   advertisers.category =2
											AND   products_fr.deleted = '0'
											AND   products_fr.active  = '1'";
											
										
				
				$req_total_nn_facture  = mysql_query($sql_total_nn_facture);
				$data_total_nn_facture = mysql_fetch_object($req_total_nn_facture);
				/******* Fin  Nb d'annonceurs non facturé	  ***************/				

				/******* Debut Nb de fournisseurs 	  ***************/
				$sql_total_fourniss  = "SELECT  COUNT( DISTINCT advertisers.id ) as nn_fourniss
											FROM advertisers as advertisers ,
												 products_fr as products_fr ,
												 products_families as products_families
											WHERE products_families.idProduct = products_fr.id
											AND   products_fr.idAdvertiser  =  advertisers.id
											AND   products_families.idFamily= '".$data_search->id."'
											AND   advertisers.category =1
											AND   products_fr.deleted = '0'
											AND   products_fr.active  = '1'";
									
									
				
				$req_total_fourniss  = mysql_query($sql_total_fourniss);
				$data_total_fourniss = mysql_fetch_object($req_total_fourniss);
				/******* Fin  Nb de fournisseurs 	  ***************/	

				/******* Debut Nb autres 	  ***************/
				$sql_total_autre  = "SELECT  COUNT( DISTINCT advertisers.id ) as nn_autre
											FROM advertisers as advertisers ,
												 products_fr as products_fr ,
												 products_families as products_families
											WHERE products_families.idProduct = products_fr.id
											AND   products_fr.idAdvertiser  =  advertisers.id
											AND   products_families.idFamily= '".$data_search->id."'
											AND   advertisers.category in(3,4,5)
											AND   products_fr.deleted = '0'
											AND   products_fr.active  = '1'";
									
				
				$req_total_autre  = mysql_query($sql_total_autre);
				$data_total_autre = mysql_fetch_object($req_total_autre);
				/******* Fin  Nb autres 	  ***************/				
			
				echo '<tr>';
					echo '<td><a href="'.ADMIN_URL.'search.php?search_type=2&search='.$data_search->name.'" target="_blank">'.$data_search->name.'</td>';
					
					if($num_revenu_moyen_final == '0.00'){				
					echo '<td style="color:#c4c2c2">'.$num_revenu_moyen_final.' &euro;</td>';
					}else {
					echo '<td>'.$num_revenu_moyen_final.' &euro;</td>';
					}
					
					if($data_nb_leads_annon->nb_leads_annon == '0'){				
					echo '<td style="color:#c4c2c2">'.$data_nb_leads_annon->nb_leads_annon.'</td>';
					}else {
					echo '<td>'.$data_nb_leads_annon->nb_leads_annon.'</td>';
					}
									
					
					if($data_nb_leads_fourn->leads_fourn == '0'){				
					echo '<td style="color:#c4c2c2">'.$data_nb_leads_fourn->leads_fourn.'</td>';
					}else {
					echo '<td>'.$data_nb_leads_fourn->leads_fourn.'</td>';
					}
					
					if($leads_par_mois == '0'){				
					echo '<td style="color:#c4c2c2">'.$leads_par_mois.'</td>';
					}else {
					echo '<td>'.substr($leads_par_mois, 0, 4).'</td>';
					}
					
					
					if($data_total_products->total_products == '0'){				
					echo '<td style="color:#c4c2c2">'.$data_total_products->total_products.'</td>';
					}else {
					echo '<td>'.$data_total_products->total_products.'</td>';
					}
					
					if($data_total_partenaires->total_partenaires == '0'){				
					echo '<td style="color:#c4c2c2">'.$data_total_partenaires->total_partenaires.'</td>';
					}else {
					echo '<td>'.$data_total_partenaires->total_partenaires.'</td>';
					}
					
				
				
				if(empty($data_total_annonceurs->total_annonceurs)){				
					echo '<td style="color:#c4c2c2">0</td>';
				}else {
					echo '<td>'.$data_total_annonceurs->total_annonceurs.'</td>';
				}
				
				if(empty($data_total_nn_facture->nn_facture)){				
					echo '<td style="color:#c4c2c2">0</td>';
				}else {
					echo '<td>'.$data_total_nn_facture->nn_facture.'</td>';
				}
				
				if(empty($data_total_fourniss->nn_fourniss)){				
					echo '<td style="color:#c4c2c2">0</td>';
				}else {
					echo '<td>'.$data_total_fourniss->nn_fourniss.'</td>';
				}
				
				if(empty($data_total_autre->nn_autre)){				
					echo '<td style="color:#c4c2c2">0</td>';
				}else {
					echo '<td>'.$data_total_autre->nn_autre.'</td>';
				}
				echo '</tr>';
			}
		
		}
	}
	?>
	  </tr>
    </tbody>
  </table>
  
</div>

<script>
jQuery(document).ready(function ($) {	
	<?php 
		$date_mois  = date(m);
		$date_debut =  $date_mois - 3;
		$date_fin =  $date_mois - 1;
		if(empty($_GET['mois_debut'])){
	?>
		$("#mois_debut option[value='<?= $date_debut ?>']").attr('selected', 'selected');
		$("#mois_fin option[value='<?= $date_fin ?>']").attr('selected', 'selected');
	<?php
		}else {
	?>
	$("#mois_debut option[value='<?= $_GET['mois_debut'] ?>']").attr('selected', 'selected');	
	$("#mois_fin option[value='<?= $_GET['mois_fin'] ?>']").attr('selected', 'selected');	
	$("#annee option[value='<?= $_GET['annee'] ?>']").attr('selected', 'selected');
	$("#annee_departs option[value='<?= $_GET['annee_departs'] ?>']").attr('selected', 'selected');
	
		<?php } ?>
	
});
</script>
<?php } ?>
<?php require(ADMIN."tail.php") ?>


