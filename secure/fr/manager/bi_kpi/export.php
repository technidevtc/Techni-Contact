<?php
	if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
	}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
	}

	$db = DBHandle::get_instance();
	
	if(isset($_GET['families_send'])){
		$annee 		= $_GET['annee'];
		$mois_debut = $_GET['mois_debut'];
		$mois_fin	= $_GET['mois_fin'];
		$periode 	= 	$mois_fin - $mois_debut;
		$periode_final 	= $periode +1;
		$date_debut = '01-'.$mois_debut.'-'.$_GET['annee'].'';
		$date_fin = '30-'.$mois_fin.'-'.$_GET['annee'].'';	
		$timestamp_debut = strtotime($date_debut);
		$timestamp_fin = strtotime($date_fin);	
		$families_send = $_GET['families_send'];
		$s = explode(" ",$families_send);
		$sql_search = "SELECT id,name  FROM families_fr  ";
		$i = 0;
		
		foreach($s as $mot){
			if(strlen($mot) > 3 ){
				if($i == 0){
					$sql_search .= " WHERE ";
				}else {
					$sql_search .= " OR ";
				}			
				$sql_search .= " name LIKE '%$mot%' ";
				$sql_search .=" ORDER BY name ASC ";
				$i++;
			}
		}
		$req_search = mysql_query($sql_search);
	
		$rows       = mysql_num_rows($req_search);
		if($rows > 0){
			$csv_output = "Nom de la famille;Revenu moyen par lead au sein de la famille;Nb leads primaires annonceur;date-creation;Nb leads fournisseur;Nb leads par mois;Nb de produits;Nb de partenaires;Nb d'annonceurs non facturé;Nb de fournisseurs;Nb autres;";
			$csv_output .= "\n";
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
				
				if(empty($data_total_annonceurs->total_annonceurs)){				
					$total_annonceurs = 0;
				}else {
					$total_annonceurs  = $data_total_annonceurs->total_annonceurs;
				}
				
				if(empty($data_total_nn_facture->nn_facture)){
					$nn_facture = 0;
				}else {
					$nn_facture = $data_total_nn_facture->nn_facture;					
				}
				
				if(empty($data_total_fourniss->nn_fourniss)){				
					$nn_fourniss = 0;
				}else {
					$nn_fourniss = $data_total_fourniss->nn_fourniss;
				}
				
				if(empty($data_total_autre->nn_autre)){				
					$nn_autre = 0;
				}else {
					$nn_autre = $data_total_autre->nn_autre;
				}
				
				$csv_output .= " ".$data_search->name." ;".$num_revenu_moyen_final." Euro;".$data_nb_leads_annon->nb_leads_annon." ;".$data_nb_leads_fourn->leads_fourn." ; ".substr($leads_par_mois, 0, 4)." ; ".$data_total_products->total_products." ; ".$data_total_partenaires->total_partenaires." ; ".$total_annonceurs." ; ".$nn_facture." ; ".$nn_fourniss." ; ".$nn_autre." \n ";
			}
			header("content-type:application/csv;charset=UTF-8");
			header("Content-type: application/vnd.ms-excel");
			header("Content-disposition: attachment; filename=export_bi_kpi_" . date("Ymd").".csv");
			
			echo mb_convert_encoding($csv_output, 'UTF-16LE', 'UTF-8');
		}
	
	}
?>
