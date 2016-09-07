 <?php
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}


$db = DBHandle::get_instance();

$title = $navBar = "Fiches Q&A ";
require(ADMIN."head.php");

echo '
	  <script type="text/javascript" src="../js/script.js"></script>
	  <link rel="stylesheet" href="css/reveal.css">	
	  	<script type="text/javascript" src="http://code.jquery.com/jquery-1.6.min.js"></script>
		<script type="text/javascript" src="js/jquery.reveal.js"></script>';
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

<link rel="stylesheet"  type="text/css" href="style.css" />


	<?php
		$id_products = $_GET['id_product'];
		$sql_products = "SELECT id,name 
						 FROM products_fr 
						 WHERE id='$id_products' ";
		$req_products = mysql_query($sql_products);
		$data_product = mysql_fetch_object($req_products);
	?>
<div class="titreStandard">Fiche Q&A - <?= $data_product->name ?> </div>
<br/>
<div class="section">
  <div style="color: #FF0000" id="show_error_message"></div>
  <div id="filtering-options" class="block filtering">
    
    <div class="text">
      <div id="content" style="margin-bottom: 50px;">
	  <?php
		$fo_pdt_pic_url = is_file(PRODUCTS_IMAGE_INC."thumb_small/".$data_product->id."-1.jpg") ? PRODUCTS_IMAGE_SECURE_URL."thumb_small/".$data_product->id."-1.jpg" : PRODUCTS_IMAGE_SECURE_URL."no-pic-thumb_small.gif";
		
		$sql_products_url = "SELECT pf.idProduct , ffr.ref_name,ffr.id AS cat_id
									FROM families_fr ffr , products_families pf
									WHERE ffr.id = pf.idFamily
									AND pf.idProduct='".$data_product->id."'"; 
		$req_products_url = mysql_query($sql_products_url);
		$data_products_url= mysql_fetch_assoc($req_products_url);
		$fo_pdt_url = URL."produits/".$data_products_url["cat_id"]."-".$data_products_url["idProduct"]."-".$data_products_url["ref_name"].".html";		
	  ?>
	  <div  class="content-left">
		<div class="left_div">
			<a href="<?= $fo_pdt_url ?>" target="_blank"><img src="<?= $fo_pdt_pic_url ?>" style="border:1px solid #ddd;" /><br /><?= $data_product->name ?></a>
		</div>
	    <div>
		<input type="hidden" id="name_products" value="<?= $data_product->name ?>" />
		<?php 
			$sql_count_quest = "SELECT COUNT(id) as total FROM q_a_questions WHERE id_produit='".$data_product->id."' ";
			$req_count_quest = mysql_query($sql_count_quest);
			$data_count_quest= mysql_fetch_object($req_count_quest);
			
			$sql_pro_ques = "SELECT pp.name,pp.id,qq.id as id_question
						     FROM q_a_questions qq, products_fr pp
						    WHERE qq.id_produit = pp.id
							AND pp.id ='".$data_product->id."' ";
		    $req_pro_ques = mysql_query($sql_pro_ques);
			$data_pro_ques = mysql_fetch_object($req_pro_ques);
			
			$sql_count_repons = "SELECT COUNT(id) as total FROM q_a_reponses WHERE id_question='".$data_pro_ques->id_question."' ";
			$req_count_repons = mysql_query($sql_count_repons);
			$data_count_repons= mysql_fetch_object($req_count_repons);
			
			$sql_last_quest = "SELECT date_create,pseudo ,question
							   FROM q_a_questions 
							   WHERE id_produit='".$data_pro_ques->id."'
							   ORDER BY `date_create` DESC LIMIT 1 ";
			$req_last_quest = mysql_query($sql_last_quest);
			$data_last_quest = mysql_fetch_object($req_last_quest);
			
			$sql_last_res = "SELECT pseudo ,id_user
							   FROM q_a_reponses 
							   WHERE id_question='".$data_pro_ques->id_question."'
							   ORDER BY `date_create` DESC LIMIT 1 ";
			$req_last_res = mysql_query($sql_last_res);
			$data_last_res = mysql_fetch_object($req_last_res);
			
		?>
		    <div style="overflow: hidden; margin-bottom: 20px;font-size: 13px;">
			<div><strong> Nb de questions : <?= $data_count_quest->total ?> </strong></div>
			<div><strong> Nb de r&eacute;ponses : <?= $data_count_repons->total ?> </strong></div>
			</div>
			<?php 
			 
			
			$sql_client = "SELECT email FROM clients WHERE id='".$data_last_res->id_user."' ";
			$req_client = mysql_query($sql_client);
			$data_client= mysql_fetch_object($req_client);
			
			
			if(!empty($data_last_quest->date_create)) $date_create = date("d/m/Y à H:i:s",strtotime($data_last_quest->date_create));
			else $date_create = 'pas d\'activit&eacute;';
			
			if(!empty($data_last_quest->pseudo)) $pseudo = $data_last_res->pseudo;
			else $pseudo = '0';
			?>
			<div> Date derni&egrave;re activit&eacute; : <?= $date_create ?></div>
			
			<div>Dernier r&eacute;pondant :  <a href="../clients/?email=<?= $data_client->email ?>"><?= $pseudo ?></a> </div>
		</div>
		
	  </div>
	  <div class="content-right">
		<div class="btn-quest"><a href="#" class="big-link" data-reveal-id="question_create">Ajouter une question</a> </div>
	  </div>
	  <div id="question_create" class="reveal-modal">
	    <input type="hidden" id="id_product_create" value="<?= $id_products ?>" />
			<div> 
					<div>
						<label><strong>Votre pseudo : </strong></label>
						<input type="text" name="pseudo" id="pseudo_create" value="" class="form-question" />
					</div>
					
					<div>
						<label><strong>Votre question : </strong></label><br />
						<textarea cols="60" rows="5" name="question" id="question_create_qq" ></textarea>
					</div>
						
					<div>
		                <center><input type="submit" value="Envoyer la question"  id="create_question" class="bouton" /></center>
				    </div>
			</div>
			<a class="close-reveal-modal">&#215;</a>
		</div>
      <div class="zero"></div>
    </div>
	<input type="hidden" value="<?= $id_products ?>" id="id_product" />
	<?php
		$sql_last_quest2 = "SELECT date_create,pseudo ,question,etat,id,id_user
							FROM q_a_questions 
							WHERE id_produit='".$data_pro_ques->id."'
							AND id NOT IN (SELECT id_question  FROM q_a_reponses)
							ORDER BY `date_create` DESC  ";
		$req_last_quest2 = mysql_query($sql_last_quest2);
		while($data_last_quest2 = mysql_fetch_object($req_last_quest2)){
			$id_quest_recent = $data_last_quest2->id;
	?>
	
	<div style="margin-bottom:45px;font-size: 13px;border-bottom:1px dashed #000 ">
		<div style="margin-bottom: 10px;">
		 <?php
			if($data_last_quest2->etat == 0){
				echo '<span style="font-weight: bold; color: red;">QUESTION A ACTIVER</span>';
				$style_border =  'style="border:1px solid red !important"';
			}else $style_border =  '';
		 ?>
		 <div style="overflow: hidden;">
		 
		  <div class="left_div title_question" <?= $style_border ?>> 
			<span id="ajax_question<?= $data_last_quest2->id ?>"><strong><?= trim(stripslashes(nl2br($data_last_quest2->question))); ?></strong></span>
		  </div>
		  <?php
			if($data_last_quest2->etat == 1) $etat_question = "<a href='#' onclick=desactiver_reponse('".$data_last_quest2->id."','desactiver')>D&eacute;sactiver </a>";
			else $etat_question = "<a href='#' onclick=desactiver_reponse('".$data_last_quest2->id."','activer')>Activer </a>";
		  
		    $sql_permiss  = "SELECT permissions  
						     FROM   `bo_users_permissions` 
						     WHERE  `id_user` ='".$_SESSION['id']."'
						     AND    `id_functionality` =13478";
		    $req_permiss  = mysql_query($sql_permiss);
			$data_permiss = mysql_fetch_object($req_permiss);
		  ?>
		  		  
		  <div class="q_a_btn">
			[<a href="#" class="big-link" data-reveal-id="repondre_recent">Répondre</a>] - 
			[<a href="#" class="big-link" data-reveal-id="question_recent">Modifier</a>] - 
			[<span id="etat_question<?= $data_last_quest2->id ?>"><?= $etat_question ?></span>] 
			<?php
			if($data_permiss->permissions == 'red'){ ?>
			- [<span id="delete_ques" onclick="delete_question(<?= $data_last_quest2->id ?>)" class="big-link" data-reveal-id="question_recent">Supprimer</span>]
			<?php } ?>
		  </div>
		 </div>
		  <?php
			$sql_send_mail  = "SELECT id,date_send,id_user_send,number_of_send FROM q_a_send_mail WHERE id_question='".$data_last_quest2->id."' ";
			$req_send_mail  =  mysql_query($sql_send_mail);
			$rows_send_mail =  mysql_num_rows($req_send_mail);
			 
			if($rows_send_mail > 0){
				$data_send_mail =  mysql_fetch_object($req_send_mail);
				$date_send   = date('d/m/Y H:i', strtotime($data_send_mail->date_send));
				$sql_bo_usr  = "SELECT name FROM bo_users WHERE id='".$data_send_mail->id_user_send."' ";
				$req_bo_usr  =  mysql_query($sql_bo_usr);
				$data_no_usr =  mysql_fetch_object($req_bo_usr); 
				echo '
					
					 <div id="q_a_send_mail" class="result-ajax-send'.$data_last_quest2->id.'">
						<div  style="float: left; margin-right: 15px;">
						<span>Envoyée le <strong>'.$date_send.'</strong> par <strong>'.$data_no_usr->name.'</strong></span>
						</div>
						<div>
						<span class="img_send"><a href="javascript:send_mail('.$data_last_quest2->id.')" >[Envoyer au fournisseur ('.$data_send_mail->number_of_send.') ]</a></span>
						<span><img src="images/wait.gif" id="img-send-mail'.$data_last_quest2->id.'" style="display:none;" /></span>
						</div>
					 </div>';
			}else{
				echo '<div id="q_a_send_mail" class="result-ajax-send'.$data_last_quest2->id.'">
						<span class="img_send">
						<a href="javascript:send_mail('.$data_last_quest2->id.')" >[Envoyer au fournisseur (0) ]</a>
						</span>
						<span><img src="images/wait.gif" id="img-send-mail'.$data_last_quest2->id.'" style="display:none;" /></span>
					  </div>';
			}
		  ?>
		 <div style="margin-bottom: 30px;">
		  <?php 
			$sql_client = "SELECT email,tel1 FROM clients WHERE id='".$data_last_quest2->id_user."' ";
			$req_client = mysql_query($sql_client);
			$data_client= mysql_fetch_object($req_client);
			
			?>
			Postée le <?= date("d/m/Y à H:i:s",strtotime($data_last_quest2->date_create)); ?> par <a href="../clients/?email=<?= $data_client->email ?>"><?= $data_last_quest2->pseudo ?></a>
			<?php
			if(!empty($data_client->tel1)){
				echo ' - '.$data_client->tel1;
			}
			?>
		  </div> 
		<div id="repondre_recent" class="reveal-modal">
	     	<div >
				<div class="title_question"  style="font-size: 12px; margin-bottom: 15px;"><?= $data_last_quest2->question ?></div>
					<div>   
						<label><strong>Votre pseudo : </strong></label>
						<input type="text" name="pseudo" id="pseudo_repondre_recent" value="Techni-Contact" class="form-question" />
					</div>
					
					<div>
						<label><strong>Votre r&eacute;ponse : </strong></label><br />
						<textarea cols="60" rows="5" name="question" id="question_repondre_recent" ></textarea>
					</div>
						
					<div>
		                <center>
							<input type="submit" value="Répondre à la question"  id="<?= $data_last_quest2->id ?>" class="bouton create_repondre_recent" />
						</center>
				    </div>
			</div>
			<a class="close-reveal-modal">&#215;</a>
		</div>
		  
		  
		  <div id="question_recent" class="reveal-modal">
			<h1>Modifier la question </h1>
			<?php
				$question_decode = ereg_replace("<br />","\r\n",$data_last_quest2->question); 
			?>
			<p><textarea id="question_<?= $id_quest_recent ?>" rows="8" cols="60" class=""><?= $question_decode ?></textarea></p>
			<a class="close-reveal-modal">&#215;</a>
			<center><input type="submit" value="Modifier"  id="<?= $id_quest_recent ?>" class="bouton update_question" /></center>
		  </div>
		  
		</div>
		<?php
			$sql_count_repons2 = "SELECT date_create,pseudo,reponse,id,id_user
								  FROM   q_a_reponses 
								  WHERE  id_question='".$data_last_quest2->id."' ";
			$req_count_repons2 = mysql_query($sql_count_repons2);
			$rows_count_repons2 = mysql_num_rows($req_count_repons2);
			if($rows_count_repons2 > 0){
			while($data_count_repons2= mysql_fetch_object($req_count_repons2)){
				
			$sql_client = "SELECT email FROM clients WHERE id='".$data_count_repons2->id_user."' ";
			$req_client = mysql_query($sql_client);
			$data_client= mysql_fetch_object($req_client);
		?>
		
		<?php /*
		<div style="margin-bottom: 20px;font-size: 13px;">
		Postée le <?= date("d/m/Y à H:i:s",strtotime($data_count_repons2->date_create)); ?> par <?= $data_last_quest2->pseudo ?>
		</div>
		*/ ?>
		
		<div style="margin-bottom: 40px;">
			<div class="left-reponse"><?=  date("d/m/Y à H:i:s",strtotime($data_count_repons2->date_create)); ?></div>
			<div style="overflow: hidden;">
				<div style="margin-bottom: 10px;">R&eacute;ponse de  <a href="../clients/?email=<?= $data_client->email ?>"> <?= $data_count_repons2->pseudo ?>
				[<a href="#" onclick="delete_reponse(<?= $data_count_repons2->id ?>)">Supprimer</a>] - 
				[<a href="#" class="big-link" data-reveal-id="reponse_modal_recent<?= $data_count_repons2->id ?>">Modifier</a>] </div>
				
				<div style="float: left; margin-right: 6px;"><img src="images/arrow_right.png"></div>
				<div id="ajax_reponse<?= $data_count_repons2->id ?>" class="respense_bord"> <?= $data_count_repons2->reponse ?></div>
			</div>
		</div>
		<div id="reponse_modal_recent<?= $data_count_repons2->id ?>" class="reveal-modal">
			<h1>Modifier la r&eacute;ponse </h1>
			<?php
				$reponse = ereg_replace("<br />","\r\n",$data_count_repons2->reponse);
			?>
			<p><textarea id="reponse_<?= $data_count_repons2->id ?>" rows="8" cols="60" class=""><?= $reponse ?></textarea></p>
			<a class="close-reveal-modal">&#215;</a>
			<center><input type="submit" value="Modifier"  id="<?= $data_count_repons2->id ?>" class="bouton update_reponse" /></center>
		</div>
			<?php 
			}
			}else {
				echo '<div style="margin-left: 40px;">0 R&eacute;ponse trouv&eacute;e</div>';
			} ?>
	  </div>
	  <?php } ?>
	  
	  
	  <?php
		$sql_last_quest2 = "SELECT date_create,pseudo ,question,etat,id,id_user
							FROM q_a_questions 
							WHERE id_produit='".$data_pro_ques->id."'
							AND  id  IN (SELECT id_question  FROM q_a_reponses)
							ORDER BY `date_create` DESC";
		$req_last_quest2 = mysql_query($sql_last_quest2);
		while($data_last_quest2 = mysql_fetch_object($req_last_quest2)){
			if($id_quest_recent != $data_last_quest2->id){
			
			$sql_client = "SELECT email FROM clients WHERE id='".$data_last_quest2->id_user."' ";
			$req_client = mysql_query($sql_client);
			$data_client= mysql_fetch_object($req_client);
	?>
	<div style="margin-bottom: 30px;font-size: 13px;border-bottom:1px dashed #000">
		<div style="margin-bottom: 10px;">
		 
		 <?php
			if($data_last_quest2->etat == 0){
				echo '<span style="font-weight: bold; color: red;">QUESTION A ACTIVER</span>';
				$style_border =  'style="border:1px solid red !important"';
			}else $style_border =  '';
		 ?>
		 
		 <div style="overflow: hidden;">
		  <div class="left_div title_question" <?= $style_border ?> id="ajax_question<?= $data_last_quest2->id ?>"><strong><?= $data_last_quest2->question ?></strong></div>
		  <?php
			if($data_last_quest2->etat == 1) $etat_question = "<a href='#' onclick=desactiver_reponse('".$data_last_quest2->id."','desactiver')>D&eacute;sactiver </a>";
			else $etat_question = "<a href='#' onclick=desactiver_reponse('".$data_last_quest2->id."','activer')>Activer </a>";
		  ?>
		  <div style="margin-bottom: 5px;">
			[<a href="#" class="big-link" data-reveal-id="repondre_recent<?= $data_last_quest2->id ?>">Répondre</a>] - 
			[<a href="#" class="big-link" data-reveal-id="myModal_<?= $data_last_quest2->id ?>">Modifier</a>] - 
			[<span id="etat_question<?= $data_last_quest2->id ?>"><?= $etat_question ?></span>] 
			<?php
			if($data_permiss->permissions == 'red'){ ?>
			- [<span id="delete_ques" onclick="delete_question(<?= $data_last_quest2->id ?>)" class="big-link" data-reveal-id="question_recent">Supprimer</span>]
			<?php } ?>
		  </div>
		 </div>
		  <?php
			$sql_send_mail  = "SELECT id,date_send,id_user_send,number_of_send FROM q_a_send_mail WHERE id_question='".$data_last_quest2->id."' ";
			$req_send_mail  =  mysql_query($sql_send_mail);
			$rows_send_mail =  mysql_num_rows($req_send_mail);
		  
			if($rows_send_mail > 0){
				$data_send_mail =  mysql_fetch_object($req_send_mail);
				$date_send   = date('d/m/Y H:i', strtotime($data_send_mail->date_send));
				$sql_bo_usr  = "SELECT name FROM bo_users WHERE id='".$data_send_mail->id_user_send."' ";
				$req_bo_usr  =  mysql_query($sql_bo_usr);
				$data_no_usr =  mysql_fetch_object($req_bo_usr); 
				echo '
					 <div id="q_a_send_mail" class="result-ajax-send'.$data_last_quest2->id.'">
						<div  style="float: left; margin-right: 15px;">
						<span>Envoyée le <strong>'.$date_send.'</strong> par <strong>'.$data_no_usr->name.'</strong></span>
						</div>
						<div>
						<span class="img_send"><a href="javascript:send_mail('.$data_last_quest2->id.')" >[Envoyer au fournisseur ('.$data_send_mail->number_of_send.') ]</a></span>
						<span><img src="images/wait.gif" id="img-send-mail'.$data_last_quest2->id.'" style="display:none;" /></span>
						</div>
					 </div>';
			}else{
				echo '<div id="q_a_send_mail" class="result-ajax-send'.$data_last_quest2->id.'">
						<span class="img_send">
						<a href="javascript:send_mail('.$data_last_quest2->id.')" >[Envoyer au fournisseur (0) ]</a>
						</span>
						<span><img src="images/wait.gif" id="img-send-mail'.$data_last_quest2->id.'" style="display:none;" /></span>
					  </div>';
			}
		  ?>
		  
		  <div>
			Postée le <?= date("d/m/Y à H:i:s",strtotime($data_last_quest2->date_create)); ?> par  
			<a href="../clients/?email=<?= $data_client->email ?>"><strong><?= $data_last_quest2->pseudo ?></strong>
			<?php
			$sql_tel  = "SELECT tel1 FROM clients WHERE id='".$data_last_quest2->id_user."' ";
			$req_tel  =  mysql_query($sql_tel);
			$data_tel =  mysql_fetch_object($req_tel);
			if(!empty($data_tel->tel1)){
				echo ' - '.$data_tel->tel1;
			}
			?>
			</a>
		  </div>
		  
		  <div id="repondre_recent<?= $data_last_quest2->id ?>" class="reveal-modal">
	     	<div> 
				<div class="title_question"  style="font-size: 12px; margin-bottom: 15px;"><?= $data_last_quest2->question ?></div>
					<div>
						<label><strong>Votre pseudo : </strong></label>
						<input type="text" name="pseudo" id="pseudo_repondre<?= $data_last_quest2->id  ?>" value="Techni-Contact" class="form-question" />
					</div>
					
					<div>
						<label><strong>Votre r&eacute;ponse : </strong></label><br />
						<textarea cols="60" rows="5" name="question" id="question_repondre<?= $data_last_quest2->id ?>" ></textarea>
					</div>
						
					<div>
		                <center>
							<input type="submit" value="Répondre à la question"  id="<?= $data_last_quest2->id ?>" class="bouton create_repondre_all" />
						</center>
				    </div>
			</div>
			<a class="close-reveal-modal">&#215;</a>
		</div>		  
		  
		  
		  <div id="myModal_<?= $data_last_quest2->id ?>" class="reveal-modal">
			<h1>Modifier la question </h1>
			<p>
			<?php
				$question_update = ereg_replace("<br />","\r\n",$data_last_quest2->question);
			?>
			<center><textarea id="question_<?= $data_last_quest2->id ?>" class="" rows="8" cols="60"><?= $question_update ?></textarea></center>
			<center><input type="submit" value="Modifier" id="<?= $data_last_quest2->id  ?>" class="bouton update_question" /></center>
			</p>
			
			<a class="close-reveal-modal">&#215;</a>
		  </div>
		  
		</div>
		<?php
			$sql_count_repons2 = "SELECT date_create,pseudo,reponse,id,id_user
								  FROM q_a_reponses 
								  WHERE id_question='".$data_last_quest2->id."' ";
			$req_count_repons2 = mysql_query($sql_count_repons2);
			$rows_count_repons2 = mysql_num_rows($req_count_repons2);
			if($rows_count_repons2 > 0){
			while($data_count_repons2= mysql_fetch_object($req_count_repons2)){
			
			
			$sql_client = "SELECT email,tel1 FROM clients WHERE id='".$data_count_repons2->id_user."' ";
			$req_client = mysql_query($sql_client);
			$data_client= mysql_fetch_object($req_client);
		?>
		
		
		<div>
		
			<div class="left-reponse"><?=  date("d/m/Y à H:i:s",strtotime($data_count_repons2->date_create)); ?></div>
			<div style="overflow: hidden;">
				<div style="margin-bottom: 10px;" class="stl_psudo">R&eacute;ponse de  <a href="../clients/?email=<?= $data_client->email ?>"><strong><?= $data_count_repons2->pseudo ?> </strong></a>
				<?php
				
				if(!empty($data_client->tel1)){
					echo ' - '.$data_client->tel1;
				}
				?>
				[<a href="#" onclick="delete_reponse(<?= $data_count_repons2->id ?>)">Supprimer</a> - 
				<a href="#" class="big-link" data-reveal-id="reponse_modal<?= $data_count_repons2->id ?>">Modifier</a>] 
				</div>
				<div style="float: left; margin-right: 6px;"><img src="images/arrow_right.png"></div>
				<div style="overflow: hidden; width: 350px;" class="response_des" id="ajax_reponse<?= $data_count_repons2->id ?>"> <?= $data_count_repons2->reponse ?></div>
			</div>
		</div>
		
		<div id="reponse_modal<?= $data_count_repons2->id ?>" class="reveal-modal">
			<h1>Modifier la r&eacute;ponse </h1>
			<?php
				$reponse2 = ereg_replace("<br />","\r\n",$data_count_repons2->reponse);
			?>
			<p><textarea id="reponse_<?= $data_count_repons2->id ?>" rows="8" cols="60" class=""><?= $reponse2 ?></textarea></p>
			<a class="close-reveal-modal">&#215;</a>
			<center><input type="submit" value="Modifier"  id="<?= $data_count_repons2->id ?>" class="bouton update_reponse" /></center>
		</div>
			<?php 
			}
			}else {
				echo '<div style="margin-left: 40px;">0 R&eacute;ponse trouv&eacute;e</div>';
			} ?>
			<br />
	 </div>
		<?php } 
		}?>
	  
	  </div>
		
		
  </div>

  <br />
  <div id="msg-tooltip" class="tooltip"></div>
<script>

</script>
  
  </div>
<script type="text/javascript" src="js/q_a_script.js"></script>
<?php } ?>
<?php require(ADMIN."tail.php") ?>


