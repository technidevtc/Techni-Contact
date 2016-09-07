<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$handle = DBHandle::get_instance();

require("../../../../includes/fr/classV3/phpmailer_2014/PHPMailerAutoload.php");
require("../../../../includes/fr/classV3/phpmailer_2014/Email_functions_external_mail_send.php");

$etat 		= $_GET['value_radio'];
$action 	= $_GET['action'];
$id_client 	= $_GET['id_client'];
 
if($action == 'params_produit'){
	$sql="UPDATE  `annuaire_questionnaire` SET  `etat` =  '$etat' WHERE id_client=$id_client";
	mysql_query($sql);	
}

if($action == 'params_soceite'){
	$sql="UPDATE  `annuaire_client` SET  `etat` =  '$etat' WHERE client_id=$id_client";
	mysql_query($sql);	
}

if($action == 'valider_change'){
	$id = $_GET['id'];
	$count      = $_GET['count'];
	$update_txt_equp = mysql_real_escape_string($_GET['update_txt_equp']);
	$chaine = str_replace('<br />','\n',$update_txt_equp);
	//$update_txt_equp = nl2br($update_txt_equp);
	

	$sql="UPDATE  `annuaire_questionnaire` SET  `txt_equipement` =  '$chaine' WHERE id=$id ";
	mysql_query($sql);
	
	$sql_select  = "SELECT txt_equipement FROM `annuaire_questionnaire` WHERE id=$id  ";
	$req_select  = mysql_query($sql_select);
	$data_select = mysql_fetch_object($req_select);

	echo '<span class="txt_dur_equip'.$count.'">'.nl2br($data_select->txt_equipement).'</span>
			 <center>
				<div class="edit icon page-white-edit" id="update_equip'.$count.'" onclick="update_equip'.$count.'('.$data_coment->id.')" ></div>
			 </center>
		 
			 <div id="txt_equp'.$count.'" style="display:none">
			 <textarea id="update_txt_equp'.$count.'">'.$update_txt_equp.'</textarea>
			 <center><span id="valider_modif'.$count.'" onclick="valider_equip('.$id.','.$count.')">[Valider] </span> <span id="annuler_modif'.$count.'" > [Annuler]</span></center>
		 </div>';	
	
}



if($action == 'valide_change_activite'){
	$id_quest = $_GET['id_quest'];
	$question_activite = mysql_real_escape_string($_GET['question_activite']);
	$chaine = str_replace('<br />','\n',$question_activite);
	$sql="UPDATE  `annuaire_client` SET  `question_activite` =  '$chaine',`date_update` = NOW() WHERE id=$id_quest ";
	mysql_query($sql);
	
	echo '<div class="epace_bottom" style="padding: 10px;">
			<label><strong>Activit&eacute;   : </strong></label>
			<span class="margin-form_" id="activite_txt">
				'.$question_activite.'
			</span>
			<span id="update_forms_activite" style="display:none;">
				<textarea type="text" class="margin-form_" id="activite_txt_get">'.$question_activite.'</textarea>
			</span>
			</div>
			<div id="change_text_activite" class="btn-update">[ Modifier ] </div>
			<div id="btn-valider-active" class="btn-update" style="display:none"> [ Valider ]</div>
			<div id="btn-annuler-active" class="btn-update" style="display:none"> [ Annuler ]</div>
		  ';
}

if($action == 'delete_image_logo'){
	$id 		= $_GET['id'];
	$sql_delete = "UPDATE `annuaire_client` SET `logo`=''  WHERE id='".$id."' ";
	mysql_query($sql_delete);
}

if($action == 'send_mail'){
	$id_send    = $_GET['id_send']; 
	$id_client  = $_GET['id_client']; 
	$type 		= $_GET['type'];
	$bo 		= $_GET['bo'];
	$id_produit	= $_GET['id_produit'];
	
	$sql_client  =  "SELECT login FROM clients WHERE id='".$id_client."' ";
	$req_client  =   mysql_query($sql_client);
	$data_client =   mysql_fetch_object($req_client);
	
	
	
	if($type == 'lead'){
	$url_send = URL.'fiche-utilisateur-survey.html?lead_id='.$id_send.'&client_id='.$id_client.'&action_type=lead&bo='.$bo;
	}else{
	$url_send = URL.'fiche-utilisateur-survey.html?order_id='.$id_send.'&client_id='.$id_client.'&action_type=order&bo='.$bo.'&id_produit='.$id_produit;

					
	}
					$header_from_name_new	= "Techni-Contact - Service client";
					$header_from_email	= 'ugc@techni-contact.com';

					$header_send1_name	= '';
					//$header_send1_email	= 'ugc@techni-contact.com';
					$header_send1_email	= $data_client->login;
					//$header_send1_email	= 'outarocht.zakaria@gmail.com';
					
					$header_send2_name	= 'ugc@techni-contact.com';
					$header_send2_email	= '';
					
					$header_reply1_name	= '';
					$header_reply1_email= '';
					
					$header_copy2_name	= '';
					$header_copy2_email	= '';
					
					$header_copy1_email	= 'outarocht.zakaria@gmail.com';
					$header_copy1_name	= '';
					$header_copy1_email	= '';
					
					
					//$url = ''.URL.'produits/'.$pdt["cat3_id"].'-'.$pdt["id"].'-'.$pdt["ref_name"].'.html';
					
				
					
					$sql_societe  = "SELECT societe FROM annuaire_client WHERE client_id='".$id_client."' ";
					$req_societe  =  mysql_query($sql_societe);
					$data_societe =  mysql_fetch_object($req_societe);			
					
					$societe = htmlentities($data_societe->societe, ENT_QUOTES) ;
					
					//$subject_envoi = $data_cliens->nom.' '.$data_cliens->prenom.' a publi&eacute; ou modifi&eacute; sa fiche sur Techni-Contact';
					$subject_envoi = 'Parlez de '.$societe.' gratuitement sur Techni-Contact';
					$subject = utf8_decode($subject_envoi) ;
					
					$message_header = "<html><head>
						<meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
						</head>
						<body bgcolor=#FFFFFF>";
					$message_text	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg">';
					$message_text	.= '<p>';
						$message_text	.= 'Bonjour,';
						$message_text	.= '<br /><br />';
						$message_text	.= 'Suite &agrave; notre conversation t&eacute;l&eacute;phonique, j\'ai le plaisir de vous communiquer un lien permettant de vous inscrire gratuitement dans l\'annuaire utilisateurs Techni-Contact.';
						$message_text	.= '<br /><br />';
						$message_text	.= 'Comment faire pour valoriser votre soci&eacute;t&eacute; sur Techni-Contact ?';
						$message_text	.= '<br /><br />';
						$message_text	.= 'Rien de plus simple: <strong>d&eacute;crivez votre activit&eacute;</strong> et pr&eacute;sentez le besoin auquel r&eacute;pond le produit 
											que vous avez command&eacute;.<br />
											Vous pouvez <strong>ins&eacute;rer votre logo</strong> et m&ecirc;me poster une <strong>photo de votre nouveau mat&eacute;riel !</strong>';
						$message_text	.= '<br /><br />';
						$message_text	.= '<a href="'.$url_send.'">Pr&eacute;sentez d&egrave;s maintenant votre activit&eacute; sur Techni-Contact.</a>';
						$message_text	.= '<br /><br />';
						$message_text	.= 'Ce service est bien s&ucirc;r <strong>totalement gratuit.</strong>';
						$message_text	.= '<br /><br />';
						$message_text	.= 'Vous apparaitrez dans notre annuaire clients et sur la fiche du produit command&eacute;.';
						$message_text	.= '<br /><br />';
						$message_text	.= 'Merci encore pour votre confiance.';
						$message_text	.= '<br /><br />';
						$message_text	.= 'Excellente journ&eacute;e &agrave; vous.';
						$message_text	.= '<br /><br />';
						$message_text	.= '<strong>Le service client</strong> <br /> 01 55 60 29 29';
						$message_text	.= '<br /><br />';
						$message_text	.= '<strong>Techni-Contact est &eacute;dit&eacute; par</strong><br />
											Md2i<br />
											253 rue Gallieni<br />
											F-92774 BOULOGNE BILLANCOURT cedex<br />
											Tel : 01 55 60 29 29 (appel local)<br />
											Fax: 01 83 62 36 12<br />
											http://www.techni-contact.com';
						$message_text	.= '<br /><br />';
						$message_text	.= 'SAS au capital de 160.000 Euro<br />
											SIRET : 392 772 497 000 39<br />
											NAF : 4791B<br />
											TVA n° FR12 392 772 497<br />
											R.C. NANTERRE B 392 772 497';
						$message_text	.= '<br /><br />';
						
						$message_text	.= '</p>';

					$message_bottom = "</body></html>";
					$message_to_send = $message_header . $message_text . $message_bottom;	
					
					
					$mail_send_etat	= php_mailer_external_send($header_from_name_new, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send);
					

}

?>

<script>

	$('#change_text_activite').click(function() {
		$("#activite_txt").hide();
		$("#change_text_activite").hide();
		$("#btn-annuler-active").show();
		$("#btn-valider-active").show();
		$("#update_forms_activite").show();
	});
	
	$('#btn-annuler-active').click(function() {
		$("#activite_txt").show();
		$("#change_text_activite").show();
		$("#btn-annuler-active").hide();
		$("#btn-valider-active").hide();
		$("#update_forms_activite").hide();
		
    });
	
	$('#btn-valider-active').click(function() {
		var id_quest = $("#id_quest").val();
		var question_activite = $("#activite_txt_get").val();
		$.ajax({
				url: 'annuaire_ajax.php?id_quest='+id_quest+'&action=valide_change_activite&question_activite='+question_activite,
				type: 'GET',
				success:function(data){
					$("#result_valide").html(data);
				}
		});
	});

  function update_equip0(id){
	 $(".txt_dur_equip0").hide();
	 $("#update_equip0").hide();
	 $("#txt_equp0").show(); 
  }
  
  
    
  function update_equip1(id){
	 $(".txt_dur_equip1").hide();
	 $("#update_equip1").hide();
	 $("#txt_equp1").show(); 
  }
  
    
  function update_equip2(id){
	 $(".txt_dur_equip2").hide();
	 $("#update_equip2").hide();
	 $("#txt_equp2").show(); 
  }
  
    
  function update_equip3(id){
	 $(".txt_dur_equip3").hide();
	 $("#update_equip3").hide();
	 $("#txt_equp3").show(); 
  }
  
  function update_equip4(id){
	 $(".txt_dur_equip4").hide();
	 $("#update_equip4").hide();
	 $("#txt_equp4").show(); 
  }
  
  function update_equip5(id){
	 $(".txt_dur_equip5").hide();
	 $("#update_equip5").hide();
	 $("#txt_equp5").show(); 
  }
  
  function update_equip6(id){
	 $(".txt_dur_equip6").hide();
	 $("#update_equip6").hide();
	 $("#txt_equp6").show(); 
  }
  
  function update_equip7(id){
	 $(".txt_dur_equip7").hide();
	 $("#update_equip7").hide();
	 $("#txt_equp7").show(); 
  }
  
  function update_equip8(id){
	 $(".txt_dur_equip8").hide();
	 $("#update_equip8").hide();
	 $("#txt_equp8").show(); 
  }
  
  
  $('#annuler_modif0').click(function() {
	 $(".txt_dur_equip0").show();
	 $("#update_equip0").show();
	 $("#txt_equp0").hide();
  });
  
  $('#annuler_modif1').click(function() {
	 $(".txt_dur_equip1").show();
	 $("#update_equip1").show();
	 $("#txt_equp1").hide();
  });
  
  $('#annuler_modif2').click(function() {
	 $(".txt_dur_equip2").show();
	 $("#update_equip2").show();
	 $("#txt_equp2").hide();
  });
  
    $('#annuler_modif3').click(function() {
	 $(".txt_dur_equip3").show();
	 $("#update_equip3").show();
	 $("#txt_equp3").hide();
  });
  
    $('#annuler_modif4').click(function() {
	 $(".txt_dur_equip4").show();
	 $("#update_equip4").show();
	 $("#txt_equp4").hide();
  });
  
    $('#annuler_modif5').click(function() {
	 $(".txt_dur_equip5").show();
	 $("#update_equip5").show();
	 $("#txt_equp5").hide();
  });
  
    $('#annuler_modif6').click(function() {
	 $(".txt_dur_equip6").show();
	 $("#update_equip6").show();
	 $("#txt_equp6").hide();
  });
  
    $('#annuler_modif7').click(function() {
	 $(".txt_dur_equip7").show();
	 $("#update_equip7").show();
	 $("#txt_equp7").hide();
  });
  
    $('#annuler_modif8').click(function() {
	 $(".txt_dur_equip8").show();
	 $("#update_equip8").show();
	 $("#txt_equp8").hide();
  });
  
  
  
    function valider_equip(id,count){	
	var update_txt_equp = $("textarea#update_txt_equp"+count).val();
	$.ajax({
			url: 'annuaire_ajax.php?update_txt_equp='+update_txt_equp+'&id='+id+'&action=valider_change&count='+count',
			type: 'GET',
			success:function(data){
				$("#result_equip"+count).html(data);
			}
	});	
    }
</script>