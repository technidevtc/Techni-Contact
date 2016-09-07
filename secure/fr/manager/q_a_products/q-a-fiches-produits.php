<?php
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}


$db = DBHandle::get_instance();

$title = $navBar = "Q&A fiches produits";
require(ADMIN."head.php");
?>
    <link rel="stylesheet" href="css/BeatPicker.min.css"/>
    <script src="js/jquery-1.11.0.min.js"></script>
    <script src="js/BeatPicker.min.js"></script>
<?php
echo '
	  <script type="text/javascript" src="../js/script.js"></script>';
if (!$userPerms->has($fntByName["m-mark--qa-fiches-produits"], "re")) {
?>

<div class="bg">
  <div class="fatalerror">Vous n'avez pas les droits ad&eacute;quats pour r&eacute;aliser cette op&eacute;ration.</div>
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
		<form method="POST" action="class.controller.php?action=verify_id_products">
			<div style="float: left; width: 275px;">
			<input type="text" class="champstexte" placeholder="ID fiche produit" name="id_fiche_products" id="id_fiche_products"  value="" style="width: 150%;" required=""/>
			</div>
			
			<div style="overflow: hidden;">
			<center><input type="submit" name="ok" value="Chercher" class="bouton"> &nbsp; 
			<input type="reset"  class="bouton" value="Annuler" name="nok"></center>
			</div>
		</form>
      </fieldset>
	  
      <div class="zero"></div>
    </div>
  </div>

  <br />
  <div class="block" style="height:150px;">
    <?php
		/*	if(isset($_GET['quest_repon'] )){
				if($_GET['quest_repon']=='0' ){
					$checked = 'checked value="1" ';
				}else {
					$checked = 'value="0" ';
				}
			}else {
				$checked = 'value="0" ';
			}
			*/
	?>
    <div class="title">Options de filtrage</div>
    
    <form action="" method="GET">
	
	<div  style="margin-left: 180px;">
	<fieldset class="selector" style="height: 64px;">
        <legend>Questions non répondues :</legend>
		<br />
		  <input type="hidden" value="0" id="quest_repon_in" />
		  <input type="button" value="Afficher questions non r&eacute;pondues" name="quest_repon" id="quest_repon" <?= $checked ?> onclick="upadte_tab();" />
    </fieldset>
    
	<fieldset class="selector" style="width: 30%; ">
        <legend>Date de la question :</legend>
        <div style="width: 100%; margin: auto; margin-bottom: 10px;">
				<div class="sample" style="float: left; margin-right: 50px; width: 30%;">
					<span>Date du : </span>
					<input name="date_du_question" class="form-control" value="<?= $_GET['date_du_question']; ?>" type="text" data-beatpicker="true" required/>
				</div>
	
				<div class="sample" style="overflow: hidden; width: 40%;">
					<span>Date au : </span>
					<input name="date_au_question" class="form-control" type="text" value="<?= $_GET['date_au_question']; ?>"  data-beatpicker="true" required/>
				</div>
				</div>
      </fieldset>
	
	<fieldset class="selector" style="width: 30%;float: none;">
        <legend>Date de la réponse :</legend>       
				<div style="width: 100%; margin: auto; margin-bottom: 10px;">
				<div class="sample" style="float: left; margin-right: 50px; width: 30%;">
					<span>Date du : </span>
					<input name="date_du" class="form-control" value="<?= $_GET['date_du']; ?>" type="text" data-beatpicker="true" required/>
				</div>
	
				<div class="sample" style="overflow: hidden; width: 40%;">
					<span>Date au : </span>
					<input name="date_au" class="form-control" type="text" value="<?= $_GET['date_au']; ?>"  data-beatpicker="true" required/>
				</div>
				</div>	
    </fieldset>
    
	
	</div>
      
	 <div  style="margin: auto; width: 265px;"><input type="submit" class="bouton" value="Filtrer" /> <input id="reset_all_form" type="reset" class="bouton" value="Annuler" /> </div> 
	 
    </form>
  </div>
  
  <div id="msg-tooltip" class="tooltip"></div>

  <table id="example" class="display item-list" cellspacing="0" width="100%">
    <thead>
      <tr>
	  <?php
			echo '<th>Commercial</th>';
			echo '<th>Image produit </th>';
			echo '<th>Nom produit  </th>';
			echo '<th>Fournisseur  </th>';
			echo '<th>Questions </th>';
			echo '<th>R&eacute;ponses </th>
				  <th>Derni&egrave;re question</th>
				  <th>Derni&egrave;re r&eacute;ponse</th>
				  <th>Dernier r&eacute;pondant </th>
				  <th>R&eacute;ponses en attente</th>
				  <th>A activer</th>
				  <th>Lien</th>';
	  ?>
    </tr>
    </thead>
    <tbody>
	
	
	
	<?php
		
		if(!empty($_GET['date_du']) && (!empty($_GET['date_au']) )){
			$date_du_rep = $_GET['date_du'].' 00:00:00';
			$date_au_rep = $_GET['date_au'].' 23:59:59';
			$sql_pro_ques = "SELECT DISTINCT(qq.id_produit),qq.id as id_question,qq.etat
							 FROM q_a_questions qq , q_a_reponses rr
							 WHERE qq.id = rr.id_question
							 AND  rr.date_create BETWEEN '$date_du_rep' AND '$date_au_rep'
							 GROUP BY id_produit
							 ORDER BY date_create DESC
						    ";
			
		}else if(!empty($_GET['date_du'])){
			$date_du_rep = $_GET['date_du'].' 00:00:00';
			$date_au_rep = $_GET['date_du'].' 23:59:59';
			$sql_pro_ques = "SELECT DISTINCT(qq.id_produit),qq.id as id_question,qq.etat
							 FROM q_a_questions qq , q_a_reponses rr
							 WHERE qq.id = rr.id_question
							 AND  rr.date_create BETWEEN '$date_du_rep' AND '$date_au_rep'
							 GROUP BY id_produit
							 ORDER BY date_create DESC
						    ";
			
		}else if($_GET['quest_repon'] == '1'){
			$sql_pro_ques = "SELECT DISTINCT(id_produit),id as id_question,etat,date_create
						     FROM q_a_questions
								 WHERE id NOT IN (SELECT id_question  FROM q_a_reponses)
						     GROUP BY id_produit 
						     ORDER BY date_create DESC
						    ";
			//echo $sql_pro_ques.'<br />';
		}else if(!empty($_GET['date_du_question']) && (!empty($_GET['date_au_question']) )){
			$date_du_ques = $_GET['date_du_question'].' 00:00:00';
			$date_au_ques = $_GET['date_au_question'].' 23:59:59';
			
			$sql_pro_ques = "SELECT DISTINCT(id_produit),id as id_question,qq.etat
							 FROM q_a_questions
							 WHERE date_create BETWEEN '$date_du_ques' AND '$date_au_ques'
							 GROUP BY id_produit 
							 ORDER BY date_create DESC";
			
			
		}else if (!empty($_GET['date_du_question'])){
			$date_du_rep = $_GET['date_du_question'].' 00:00:00';
			$date_au_rep = $_GET['date_du_question'].' 23:59:59';
			$sql_pro_ques = "SELECT DISTINCT(id_produit),id as id_question,qq.etat
							 FROM q_a_questions
							 WHERE date_create BETWEEN '$date_du_rep' AND '$date_au_rep'
							 GROUP BY id_produit 
							 ORDER BY date_create DESC";
		}else{
			$sql_pro_ques = "SELECT DISTINCT(id_produit),id as id_question,etat,MAX(date_create) as date_create
						     FROM q_a_questions
						     GROUP BY id_produit 
						     ORDER BY MAX(date_create) DESC";
		}
		 
		
		$req_pro_ques = mysql_query($sql_pro_ques);
		while($data_pro_ques = mysql_fetch_object($req_pro_ques)){
			$sql_product = "SELECT pfr.name,pfr.id,bu.name as comme,aa.nom1
							FROM   products_fr pfr , advertisers aa, bo_users bu
							WHERE  pfr.idAdvertiser = aa.id
							AND    aa.idCommercial  = bu.id
							AND    pfr.id='".$data_pro_ques->id_produit."' ";
			$req_product = mysql_query($sql_product);
			$data_product= mysql_fetch_object($req_product);
			
			$sql_products_url = "SELECT pf.idProduct , ffr.ref_name,ffr.id AS cat_id
									FROM families_fr ffr , products_families pf
									WHERE ffr.id = pf.idFamily
									AND pf.idProduct='".$data_product->id."'"; 
			$req_products_url = mysql_query($sql_products_url);
			$data_products_url= mysql_fetch_assoc($req_products_url);
			
			$fo_pdt_pic_url = is_file(PRODUCTS_IMAGE_INC."thumb_small/".$data_product->id."-1.jpg") ? PRODUCTS_IMAGE_SECURE_URL."thumb_small/".$data_product->id."-1.jpg" : PRODUCTS_IMAGE_SECURE_URL."no-pic-thumb_small.gif";
			$fo_pdt_url = URL."produits/".$data_products_url["cat_id"]."-".$data_products_url["idProduct"]."-".$data_products_url["ref_name"].".html";
			
			$sql_count_quest = "SELECT COUNT(id) as total FROM q_a_questions WHERE id_produit='".$data_product->id."' ";
			$req_count_quest = mysql_query($sql_count_quest);
			$data_count_quest= mysql_fetch_object($req_count_quest);
			
		    $sql_count_repons = "SELECT COUNT(qar.id) as total 
								 FROM q_a_reponses qar, q_a_questions qaq
								 WHERE qar.id_question = qaq.id
								 AND qaq.id_produit='".$data_product->id."'
								 GROUP BY qaq.id_produit";
			$req_count_repons = mysql_query($sql_count_repons);
			$data_count_repons= mysql_fetch_object($req_count_repons);
			
			
			$sql_last_quest = "SELECT date_create 
							   FROM q_a_questions 
							   WHERE id_produit='".$data_product->id."'
							   ORDER BY `date_create` DESC LIMIT 1 ";
			$req_last_quest = mysql_query($sql_last_quest);
			$data_last_quest = mysql_fetch_object($req_last_quest);
			
			$sql_last_repo  = "SELECT reponse ,pseudo,date_create
							   FROM   q_a_reponses 
							   WHERE  id_question='".$data_pro_ques->id_question."'
							   ORDER  BY `date_create` DESC LIMIT 1 ";							   
			$req_last_repo  = mysql_query($sql_last_repo);
			$data_last_repo = mysql_fetch_object($req_last_repo);
			
			$sql_last_repo_atten  = " SELECT COUNT(id) as total
									  FROM q_a_questions
									  WHERE id NOT IN (SELECT id_question  FROM q_a_reponses)
									  AND id_produit='".$data_product->id."'
									  ";
			$req_last_repo_atten  = mysql_query($sql_last_repo_atten);
			$data_last_repo_atten = mysql_fetch_object($req_last_repo_atten);
			$last_quest = date("d/m/Y",strtotime($data_pro_ques->date_create));	
			$last_repo  = date("d/m/Y",strtotime($data_last_repo->date_create));	
		echo '<tr>';
			echo '<td>'.$data_product->comme.'</td>';
			echo '<td><a href="'.ADMIN_URL.'q_a_products/fiche_q_a.php?id_product='.$data_pro_ques->id_produit.'" target="_blink"><img src="'.$fo_pdt_pic_url.'" alt=""></a></td>';
			echo '<td><a href="'.ADMIN_URL.'q_a_products/fiche_q_a.php?id_product='.$data_pro_ques->id_produit.'" target="_blink">'.$data_product->name.'</a></td>';
			echo '<td>'.$data_product->nom1.'</td>';
			echo '<td>'.$data_count_quest->total.'</td>';
			if(empty($data_count_repons->total)) echo '<td>0</td>';
			else echo '<td>'.$data_count_repons->total.'</td>';
			
			
			if($last_quest == "01/01/1970") echo '<td>--</td>';
			else echo '<td>'.$last_quest.'</td>';
			
			if($last_repo == "01/01/1970") echo '<td>--</td>';
			else echo '<td>'.$last_repo.'</td>';
			
			echo '<td>'.$data_last_repo->pseudo.'</td>';
			echo '<td>'.$data_last_repo_atten->total.'</td>';
			if($data_pro_ques->etat == '0'){
				echo '<td><img src="images/warning.png" style="width: 34px;" /></td>';
			}else echo '<td> - </td>';
			echo '<td><a href="'.$fo_pdt_url.'" target="_blank"><img src="'.ADMIN_URL.'ressources/icons/monitor_go.png" alt="" title="Voir la fiche en ligne"></a></td>';
		echo ' </tr>';
	}
	?>
	 
    </tbody>
  </table>
  
</div>
<script>
	
	$("#reset_all_form").click(function(){
		var currentLocation = window.location.hostname;
		document.location.href= "https://"+currentLocation+"/fr/manager/q_a_products/q-a-fiches-produits.php";
    });
	
	function upadte_tab(){
		var currentLocation = window.location.hostname;
		var quest_repon = $("#quest_repon_in").val();
		if(quest_repon != "0"){
			filtre_rep=quest_repon;
		}else {
			filtre_rep="1";
		}
		
		document.location.href= "https://"+currentLocation+"/fr/manager/q_a_products/q-a-fiches-produits.php?quest_repon="+filtre_rep;
		
	}
</script>
<?php } ?>
<?php require(ADMIN."tail.php") ?>


