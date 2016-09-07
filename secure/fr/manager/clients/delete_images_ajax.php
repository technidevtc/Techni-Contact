<?php
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$handle = DBHandle::get_instance();
$client_id = $_GET['client_id'];
$id	       = $_GET['id'];


$sql_img = "SELECT photo_equipement FROM annuaire_questionnaire WHERE `id` =$id";
$req_img = mysql_query($sql_img);
$data_img= mysql_fetch_object($req_img);
$img_src = URL.$data_img->photo_equipement;
unlink($img_src);

$sql_update = "UPDATE `annuaire_questionnaire` SET `photo_equipement` = '' WHERE `id` =$id";
mysql_query($sql_update);


$sql_verify = "SELECT id_produit FROM annuaire_questionnaire WHERE id_client='".$client_id."' ";
$req_verify = mysql_query($sql_verify);
$rows_verfiy= mysql_num_rows($req_verify);

	if($rows_verfiy  > 0){
	echo '<div class="bg" style="overflow:hidden;">
				<div class="block" style="overflow:hidden;">
				<div class="title">Commentaires ont été postés  </div>
				<div style="padding:10px">
					<ul class="comment_poste">';
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
				$sql_comment = "SELECT id,txt_equipement,photo_equipement FROM annuaire_questionnaire WHERE id_client='".$client_id."' AND id_produit='".$data_verify->id_produit."' ";
				$req_comment = mysql_query($sql_comment);
				$data_coment = mysql_fetch_object($req_comment);
			?>	
						<li class="entry " data-id="10497879">
						<?php /*
							<img alt="" src="<?= URL ?>ressources/images/produits/thumb_small/<?= $data_pdt->ref_name ?>-<?= $data_verify->id_produit ?>-1.jpg" class="vmaib" />
						*/ ?>	
							<img alt="" src="<?= URL ?><?= $data_coment->photo_equipement ?>"  style="width: 100px;" class="vmaib" />
						<?php	
							if(!empty($data_coment->photo_equipement)){ ?>
							<div style="float: right; cursor: pointer;" onclick="delete_image('<?= $data_coment->id ?>','<?= $client_id ?>')"><img alt="" src="../images/DeleteRed.png" width="20px" /></div>
						<?php } ?>
						<div><?= $data_coment->txt_equipement ?></div>
						</li>
	<?php	}
		
		
		}
		echo'</ul>
				</div>
				</div>';
			echo' <div class="epace_bottom">
				<div>Afficher les produits sur Techni-Contact</div>
				<input type="hidden" id="id_client" value="'.$client_id.'" />
				';
			
			
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
			
			echo '</div>';
	
	}
?>
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
  </script>