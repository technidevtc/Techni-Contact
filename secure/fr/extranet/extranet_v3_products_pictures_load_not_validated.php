<?php
	require_once('extranet_v3_functions.php'); 
	require_once(ADMIN.'logo.php');

	$id_fiche		= mysql_escape_string($_POST['idf']);
	$message 		= 'Erreur, merci de r&eacute;essayer !';
	
	if(!empty($_SESSION['extranet_user_id']) && !empty($id_fiche)){

		$dir_INCLUDES 	= PRODUCTS_IMAGE_ADV_INC;
		
		//Dir url Linux && Windows
		if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
			//$dir_URL 		= 'file:///C:/data/technico/includes/fr/files/images/products_adv/';
			$dir_URL 		= 'http://local-pictures-products-adv.techni-contact.com/';
			
			//$dir_URL 		= '../../../includes/fr/files/images/products_adv /thumb_small/839923-1.jpg';
		}else{
			$dir_URL 		= PRODUCTS_IMAGE_ADV_URL;
		}

		
		$num	= 1;
		
		echo('<div id="product_pictures_uploaded_change_order_msg"></div>');
		echo('<span id="product_pictures_uploaded_change_flag" style="display: none;">1</span>');
		echo('<ul id="product_pictures_uploaded_ul">');
			while (is_file($dir_INCLUDES."thumb_small/".$id_fiche."-".$num.".jpg")) {
		
				echo('<li id="ppictures_list_'.$num.'" ');
					if($num==1){
					
						echo('class="product_pictures_first">');
							echo('<div class="picture_add_title">Photo principale</div>');
							echo('<a rel="gallery" class="fancybox" href="'.$dir_URL.'zoom/'.$id_fiche.'-'.$num.'.jpg?r='.time().'" title="Photo principale">');
								echo('<img src="'.$dir_URL.'thumb_big/'.$id_fiche.'-'.$num.'.jpg?r='.time().'" alt="Photo principale" title="Photo principale" />');
							echo('</a>');
							
					}else{
					
						echo('class="product_pictures_other">');
							echo('<div class="picture_add_title">Photo num&eacute;ro '.$num.'</div>');
							
							echo('<a rel="gallery" class="fancybox" href="'.$dir_URL.'zoom/'.$id_fiche.'-'.$num.'.jpg?r='.time().'" title="Photo num&eacute;ro '.$num.'">');
								echo('<img src="'.$dir_URL.'thumb_small/'.$id_fiche.'-'.$num.'.jpg?r='.time().'" alt="Photo num&eacute;ro '.$num.'" title="Photo num&eacute;ro '.$num.'" style="float:left;"/>');
							echo('</a>');
							
					}
					//echo('<img src="'.$dir_URL.'thumb_small/'.$id_fiche.'-'.$num.'.jpg?r='.time().'" alt="Image num&eacute;ro '.$num.'" title="Image num&eacute;ro '.$num.'" />');
					
					echo('<img src="ressourcesv3/icons/cross.png" alt="Supprimer" title="Supprimer" class="delete_listner" onclick="javascript:products_delete_this_picture(\''.$id_fiche.'\',\''.$num.'\', \'new\');" />');
				echo('</li>');	
			
				$num++;
			}//end while
		
		echo('</ul>');
		

	}else{
		echo('-1');
	}//end if empty
	
	//echo('<br />End loading !');
	
?>