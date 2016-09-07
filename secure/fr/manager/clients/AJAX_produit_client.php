<?php
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$handle = DBHandle::get_instance();
$client_id = $_GET['client_id'];

function string_to_url($string) {
     $search = array('à', 'ä', 'â', 'é', 'è', 'ë', 'ê', 'ï', 'ì', 'î', 'ù', 'û', 'ü', 'ô', 'ö', '&', ' ', '?', '!', 'ç', ';', '/');
     $replace = array('a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'u', 'u', 'u', 'o', 'o', '', '-', '', '', 'c', '', '-');
     return urlencode(str_replace($search, $replace, strtolower($string)));
}

$sql_verify = "SELECT id,id_produit ,lead_id
			   FROM annuaire_questionnaire 
			   WHERE id_client='".$client_id."' ";

$req_verify = mysql_query($sql_verify);
$rows_verfiy= mysql_num_rows($req_verify);

	if($rows_verfiy  > 0){
		
	echo '<div class="bg" style="overflow:hidden;">
		
				<div class="block" style="overflow:hidden;">
				<div class="title">Commentaires sur produits Lead / commandés </div>
				<div style="padding:10px">';
				echo' <div class="epace_bottom">
					<div>Afficher les produits sur Techni-Contact</div>
					<input type="hidden" id="id_client" value="'.$client_id.'" />';
			
					$sql_verify_ptd = "SELECT etat FROM annuaire_questionnaire WHERE id_client='".$client_id."' ";
					$req_verify_ptd = mysql_query($sql_verify_ptd);
					$data_verify_ptd = mysql_fetch_object($req_verify_ptd);
					
					if($data_verify_ptd->etat == 1){
						echo 'Oui : <input type="radio" checked name="affiche_pdt" id="affiche_pdt_on" value="1"  />
							  Non : <input type="radio" name="affiche_pdt" id="affiche_pdt_off" value="0"/>';
					}else{
						echo 'Oui : <input type="radio"  name="affiche_pdt" id="affiche_pdt_on" value="1" />
							  Non : <input type="radio" checked name="affiche_pdt" id="affiche_pdt_off" value="0" />';
					}				
				echo '</div>';

		$sql_check  = "SELECT lead_id,type,order_id FROM annuaire_client WHERE client_id='".$client_id."'";
		$req_check  = mysql_query($sql_check);
		$data_check = mysql_fetch_object($req_check);		
		echo'<ul class="comment_poste">';
		$i=0;
		while($data_verify = mysql_fetch_object($req_verify)){
			$sql_pdt  = "SELECT DISTINCT(pp.id),pp.ref_name , pf.idFamily,pp.name
									 FROM products_fr pp,products_families pf , families_fr ff
									 WHERE pp.id = pf.idProduct
									 AND   ff.id = pf.idFamily
									 AND  pp.id='".$data_verify->id_produit."' 
									 GROUP BY pp.id";
			$req_pdt  = mysql_query($sql_pdt);
			$rows_pdt= mysql_num_rows($req_pdt);
			if($rows_pdt > 0){
				$data_pdt = mysql_fetch_object($req_pdt);
				$sql_comment = "SELECT id,txt_equipement,photo_equipement 
								FROM annuaire_questionnaire 
								WHERE id_client='".$client_id."' AND id_produit='".$data_verify->id_produit."' ";
				$req_comment = mysql_query($sql_comment);
				$data_coment = mysql_fetch_object($req_comment);
				
				$sql_client  = "SELECT id,societe,lead_id,email FROM annuaire_client WHERE client_id='".$client_id."' ";
				$req_client  = mysql_query($sql_client);
				$data_client = mysql_fetch_object($req_client);
								
			?>	
						<li class="entry " data-id="10497879">
							
							<div style="float: left; margin-right: 10px;">
							<a href="<?php echo URL.'produits/'.$data_pdt->idFamily.'-'.$data_verify->id_produit.'-'.$data_pdt->ref_name.'.html' ?>" target="_blink">
								<center><img alt="" src="<?= URL ?>ressources/images/produits/thumb_small/<?= $data_pdt->ref_name ?>-<?= $data_verify->id_produit ?>-1.jpg" class="vmaib" /></center><br />
								<?= $data_pdt->name ?>
							</a>
							</div>
							
							<div id="result_equip<?= $i ?>" style="float: left; margin-right: 10px;">
							 <div style="margin-bottom: 60px;">
								<span class="txt_dur_equip<?= $i ?>"><?= nl2br($data_coment->txt_equipement) ?></span>
							 </div>
							 
							 <div>
								<center>
									<div class="edit icon page-white-edit" id="update_equip<?= $i ?>" onclick="update_equip<?= $i ?>('<?= $data_coment->id ?>')" ></div>
								</center>
							 </div>
							 
							 <div id="txt_equp<?= $i ?>" style="display: none; margin-top: -70px;">
								<textarea id="update_txt_equp<?= $i ?>"><?= $data_coment->txt_equipement ?></textarea>
								<center>
									<span id="valider_modif" onclick="valider_equip('<?= $data_coment->id ?>','<?= $i ?>')">[Valider] </span> 
									<span id="annuler_modif<?= $i ?>" > [Annuler]</span>
								</center>
							 </div>
							 
							</div>
							<?php
							$url_final = string_to_url($data_client->societe);
							?>
							<div style="float: left; margin-bottom: 6px;">
								<a href="<?= URL ?>utilisateurs/<?=$data_client->id ?>-<?= $client_id ?>-<?= $url_final ?>.html" target="_blink">
								<?php
								
									if(empty($data_coment->photo_equipement)){
										echo '<img alt="" src="'.URL.'ressources/produits/no-pic-thumb_big.gif"  style="width: 100px;" class="vmaib" />';
									}else {
										echo '<img alt="" src="'.URL.$data_coment->photo_equipement.'"  style="width: 100px;" class="vmaib" />';
									}
								?>
								
								</a>
							</div>
						<?php	
							if(!empty($data_coment->photo_equipement)){ ?>
							<div style="float: right; cursor: pointer;" onclick="delete_image('<?= $data_coment->id ?>','<?= $client_id ?>')">
								<img alt="" src="../images/DeleteRed.png" width="20px" />
							</div>
						<?php } ?>
						</li>	
	<?php	}
		
		
			$sql_order  = "SELECT DISTINCT(pdt_id) 
						   FROM order_line 
						   WHERE pdt_id='".$data_verify->id_produit."'";
			$req_order  =  mysql_query($sql_order);
			$rows_order = mysql_num_rows($req_order);
			if($rows_order > 0){
				echo'<div style="overflow: hidden; width: 80px; float: left;">
				<a href="'.URL.'fiche-utilisateur-survey.html?order_id='.$data_check->order_id.'&client_id='.$client_id.'&action_type=order&bo='.$data_coment->id.'&id_produit='.$data_verify->id_produit.'" target="_blink">Voir formulaire d\'inscription</a>
				</div>
				<div>
				<span style="cursor: pointer;" onclick="send_mail(\''.$data_check->order_id.'\',\''.$client_id.'\',\'order\',\''.$data_coment->id.'\',\''.$data_verify->id_produit.'\' )">Envoyer un mail à l\'utilisateur</span>
				</div>
				';
			}else{
			$sql_contact  = "SELECT DISTINCT(id) FROM contacts WHERE idProduct='".$data_verify->id_produit."' AND email='".$data_client->email."' ";
			$req_contact  =  mysql_query($sql_contact);
			$data_contact = mysql_fetch_object($req_contact);
			echo'<div style="overflow: hidden; width: 80px; float: left;">
				<a href="'.URL.'fiche-utilisateur-survey.html?lead_id='.$data_contact->id.'&client_id='.$client_id.'&action_type=lead&bo='.$data_coment->id.'" target="_blink">Voir formulaire d\'inscription </a>
				</div>
				<div>
				<span style="cursor: pointer;" onclick="send_mail(\''.$data_contact->id.'\',\''.$client_id.'\',\'lead\',\''.$data_coment->id.'\',\'0\' )">Envoyer un mail à l\'utilisateur</span>
				</div>
				';	
			}
				
		$i++;
		}
		echo '<input type="hidden" id="total_update" value="'.$i.'" />';
		
		
		echo'</ul>
				</div>';
		echo	'</div>';
		echo '</div>';
	}else {
		
		echo '<div class="bg" style="overflow:hidden;">
				<div class="block" style="overflow:hidden;">
				<div class="title">Commentaires sur produits Lead / commandés </div>
				<div style="padding:10px">';
				echo' <div class="epace_bottom">';
					 //<div>Afficher les produits sur Techni-Contact</div>';
						
		/******************************Leads sans fiche Produit ******************/
		$email 		  =  $_GET['email'];
		$sql_contact  =  "SELECT id,idProduct FROM contacts WHERE email='".$email."' AND parent='0' ";
		$req_contact  =   mysql_query($sql_contact);
		$rows_contact =   mysql_num_rows($req_contact);
		
		if($rows_contact > 0){
			echo '<div style="margin-bottom: 5px;"><b>Lead(s)</b></div>';
		while($data_contact =   mysql_fetch_object($req_contact)){
		
		$sql_pdt  = "SELECT DISTINCT(pp.id),pp.ref_name , pf.idFamily,pp.name
					 FROM products_fr pp,products_families pf , families_fr ff
					 WHERE pp.id = pf.idProduct
					 AND   ff.id = pf.idFamily
					 AND  pp.id='".$data_contact->idProduct."' 
					 GROUP BY pp.id";
		
		$req_pdt  =  mysql_query($sql_pdt);
		$data_pdt =  mysql_fetch_object($req_pdt);
		?>
		<div style="overflow: hidden; margin-bottom: 20px;">
		<div style="float: left; margin-right: 10px; overflow: hidden;">
		<a href="<?php echo URL.'produits/'.$data_pdt->idFamily.'-'.$data_contact->idProduct.'-'.$data_pdt->ref_name.'.html' ?>" target="_blink">
			<center>
				<img alt="" src="<?= URL ?>ressources/images/produits/thumb_small/<?= $data_pdt->ref_name ?>-<?= $data_contact->idProduct ?>-1.jpg" class="vmaib" />
			</center><br />
			<?= $data_pdt->name ?>
		</a>
		</div>
		<?php
		echo'<div style="overflow: hidden; width: 200px; margin-top: 30px;"><a href="'.URL.'fiche-utilisateur-survey.html?lead_id='.$data_contact->id.'&client_id='.$client_id.'&action_type=lead" target="_blink">Voir formulaire d\'inscription</a></div>';	
		echo '</div>';
		echo '<div>
				<span style="cursor: pointer;" onclick="send_mail(\''.$data_contact->id.'\',\''.$client_id.'\',\'lead\',\'0\',\'0\' )">Envoyer un mail à l\'utilisateur</span>
			  </div>';
		}
		
		}
					

		/******************************Order sans fiche Produit ******************/
		$sql_order  = "SELECT DISTINCT(oo.id),ol.pdt_id
						   FROM `order` oo, order_line ol
						   WHERE oo.id = ol.order_id
						   AND oo.client_id = '".$client_id."' ";		 
		$req_order  =  mysql_query($sql_order);
		$rows_order =  mysql_num_rows($req_order);
		
		if($rows_order > 0){
			echo '<div style="margin-bottom: 5px;"><b>Commande(s)</b></div>';
		while($data_order =  mysql_fetch_object($req_order)){
				
		$sql_pdt    = "SELECT DISTINCT(pp.id),pp.ref_name , pf.idFamily,pp.name
					   FROM  products_fr pp,products_families pf , families_fr ff
					   WHERE pp.id = pf.idProduct
					   AND   ff.id = pf.idFamily
					   AND   pp.id='".$data_order->pdt_id."' 
					   GROUP BY pp.id";
				
		$req_pdt  =  mysql_query($sql_pdt);
		$data_pdt =  mysql_fetch_object($req_pdt); ?>
		<div style="overflow: hidden; margin-bottom: 20px;">
		<div style="float: left; margin-right: 10px; overflow: hidden;">
		<a href="<?php echo URL.'produits/'.$data_pdt->idFamily.'-'.$data_order->pdt_id.'-'.$data_pdt->ref_name.'.html' ?>" target="_blink">
			<center>
				<img alt="" src="<?= URL ?>ressources/images/produits/thumb_small/<?= $data_pdt->ref_name ?>-<?= $data_order->pdt_id ?>-1.jpg" class="vmaib" />
			</center><br />
			<?= $data_pdt->name ?>
		</a>
		</div>
		
		<?php
		echo'<div style="overflow: hidden; width: 200px; margin-top: 30px;"><a href="'.URL.'fiche-utilisateur-survey.html?order_id='.$data_order->id.'&client_id='.$client_id.'&action_type=order" target="_blink">Voir formulaire d\'inscription</a></div></div>';	
		
		echo '<div>
				<span style="cursor: pointer;" 	onclick="send_mail(\''.$data_order->id.'\',\''.$client_id.'\',\'order\',\'0\',\'0\' )">Envoyer un mail à l\'utilisateur</span>
			  </div>';
		}
		}		
		
		}
		?>  
		</div>
		</div>
		</div>
		</div>
	

<script>
function delete_image(id,client_id){
	if(confirm("Etes vous sur de supprimer cet image !")){
	 $.ajax({
			url: 'delete_images_ajax.php?id='+id+"&client_id="+client_id,
			type: 'GET',
			success:function(data){
				$("#produit_clients").html(data);
			}
			});
	}
  }
  
  
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
	var update_txt_equp = $("#update_txt_equp"+count).val();
	
	var textAreaString = update_txt_equp.replace(/\n\r/g,"<br />");
	var textAreaString = update_txt_equp.replace(/\n/g,"<br />");
	
	//alert(textAreaString);
	$.ajax({
			url: 'annuaire_ajax.php?update_txt_equp='+textAreaString+'&id='+id+'&action=valider_change&count='+count,
			type: 'GET',
			success:function(data){
				//alert(data);
				$("#result_equip"+count).html(data);
			}
	});	
  }
  
  $('#affiche_pdt_on').click(function() {
	 var val = $('input:radio[name=affiche_pdt]:checked').val();
     var id_client = $("#id_client").val();
	 	$.ajax({
				url: 'annuaire_ajax.php?value_radio='+val+'&id_client='+id_client+'&action=params_produit',
				type: 'GET',
				success:function(data){
				
				}
		});
  });
  
    $('#affiche_pdt_off').click(function() {
	 var val = $('input:radio[name=affiche_pdt]:checked').val();
	  var id_client = $("#id_client").val();
	 	$.ajax({
				url: 'annuaire_ajax.php?value_radio='+val+'&id_client='+id_client+'&action=params_produit',
				type: 'GET',
				success:function(data){
				
				}
		});
  });
  
  function send_mail(id_send,id_client,type,bo,id_produit){
	  $.ajax({
		url: 'annuaire_ajax.php?id_send='+id_send+'&id_client='+id_client+'&type='+type+'&action=send_mail&bo='+bo+'&id_produit='+id_produit,
		type: 'GET',
		success:function(data){
			alert('Emai envoyé avec succès! ');			
		}
		});
  }
  </script>
  
  <style>
	  #update_txt_equp0,
	  #update_txt_equp1,
	  #update_txt_equp2,
	  #update_txt_equp3,
	  #update_txt_equp4,
	  #update_txt_equp5 {
		width: 300px; 
		height: 155px; 
	  }
	  
	  
#valider_modif0,#valider_modif1,#valider_modif2,#valider_modif3,#valider_modif4,#valider_modif5, {
    color: #0071bc;
    cursor: pointer;
    font-weight: bold;
    margin-bottom: 10px;
    overflow: hidden;
}

#annuler_modif0,#annuler_modif1,#annuler_modif2,#annuler_modif3,#annuler_modif4,#annuler_modif5 {
    color: #0071bc;
    cursor: pointer;
    font-weight: bold;
    margin-bottom: 10px;
    overflow: hidden;
}
  </style>